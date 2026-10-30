<?php

namespace App\Exports;

use App\Services\EstadoMatricula;
use App\Support\Telefono;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exportación del directorio de estudiantes (/Administrador): exporta exactamente lo que
 * se ve (o lo seleccionado), con uno de estos conjuntos de columnas:
 *  - basico     identificación, ubicación académica y situación
 *  - matricula  estado de matrícula del periodo, voucher y envío de ficha
 *  - contacto   correo, celular (con enlace a WhatsApp) y n.º de referencia
 * La "ficha completa" (socioeconómica) usa AlumnosFidExport / AlumnosPpdExport.
 *
 * Hoja 1: título, ficha del reporte (programa, ciclo, periodo y filtros con que se exportó),
 * indicadores y la tabla. Hoja 2: resumen por programa y ciclo.
 * Si todos los estudiantes son del mismo programa (o ciclo) esa columna no se repite en la
 * tabla: ya figura en la ficha.
 *
 * Los teléfonos salen de la ficha (alumnos.numero / ppds.numero), igual que en la pantalla:
 * users.telefono casi nunca está lleno y solo se usa si la ficha no trae número.
 * Paleta: la misma del Excel de calificaciones del docente (Docente\HojaBase).
 */
class DirectorioEstudiantesExport implements FromArray, WithEvents, WithTitle, WithProperties
{
    public const PRESETS = [
        'basico' => 'Básico',
        'matricula' => 'Matrícula',
        'contacto' => 'Contacto',
        'ficha' => 'Ficha completa',
    ];

    private const PESTANAS = [
        'fid' => 'Formación Inicial Docente (FID)',
        'ppd' => 'Profesionalización Docente (PPD)',
        'inhabilitados' => 'Estudiantes inhabilitados',
    ];

    private const AZUL_OSCURO = '1E3A5F';
    private const AZUL = '2E5C8A';
    private const AZUL_SUAVE = 'E8EEF6';
    private const BORDE = 'D9E2EC';
    private const ZEBRA = 'F7F9FC';
    private const GRIS = '6B7280';
    private const GRIS_CLARO = '9CA3AF';
    private const VERDE = '0B7A43';
    private const VERDE_FONDO = 'E6F4EC';
    private const AMBAR = '9A6700';
    private const AMBAR_FONDO = 'FFF4D6';
    private const ROJO = 'C0392B';
    private const ROJO_FONDO = 'FDECEA';
    private const WHATSAPP = '128C7E';

    /** Colores del estado de matrícula: [texto, fondo]. */
    private const ESTADOS = [
        'matriculado' => [self::VERDE, self::VERDE_FONDO],
        'sin_matricula' => [self::AMBAR, self::AMBAR_FONDO],
        'sin_ficha' => [self::ROJO, self::ROJO_FONDO],
        'no_aplica' => [self::GRIS, null],
    ];

    /** Filas ya calculadas: ['u' => User, 'm' => EstadoMatricula, 'celular' => ?string, 'referencia' => ?string]. */
    private Collection $filas;

    /**
     * @param  array  $contexto  filtros activos en la pantalla al exportar:
     *                           programa, ciclo, estado, chips (lista), busqueda, alcance
     */
    public function __construct(
        private readonly Collection $usuarios,
        private readonly string $preset,
        private readonly ?string $periodo = null,
        private readonly string $tab = 'fid',
        private readonly array $contexto = [],
        private readonly ?string $generadoPor = null,
    ) {
        $this->filas = $usuarios->values()->map(function ($u) {
            $m = EstadoMatricula::de($u);

            return [
                'u' => $u,
                'm' => $m,
                'celular' => $this->limpio($m->ficha?->numero) ?? $this->limpio($u->telefono),
                'referencia' => $this->limpio($m->ficha?->numero_referencia),
            ];
        });
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Directorio';
    }

    public function properties(): array
    {
        return [
            'creator' => $this->generadoPor ?: 'EESP Pukllasunchis',
            'lastModifiedBy' => 'Sistema académico EESP Pukllasunchis',
            'title' => 'Directorio de estudiantes — '.self::PRESETS[$this->preset],
            'subject' => implode(' · ', array_filter([self::PESTANAS[$this->tab] ?? null, $this->programaReporte(), $this->cicloReporte(), $this->periodo])),
            'company' => 'EESP Pukllasunchis',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $hoja = $event->sheet->getDelegate();
                $libro = $hoja->getParent();
                $libro->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);
                $hoja->setShowGridlines(false);
                $this->dibujar($hoja);

                $resumen = $libro->createSheet();
                $resumen->setTitle('Resumen');
                $resumen->setShowGridlines(false);
                $this->dibujarResumen($resumen);
                $libro->setActiveSheetIndex(0);
            },
        ];
    }

    // ───────────────────────── Contexto del reporte ─────────────────────────

    /** Para el nombre del archivo: "programa-inicial_ciclo-iv" si son únicos (o vacío). */
    public function etiquetaArchivo(): string
    {
        return implode('_', array_filter([
            $this->programaUnico() ? \Illuminate\Support\Str::slug($this->programaReporte()) : null,
            $this->cicloUnico() ? \Illuminate\Support\Str::slug($this->cicloReporte()) : null,
        ]));
    }

    private function claveCiclo($u): string
    {
        return ($u->programa->nombre ?? '').'|'.$this->ciclo($u);
    }

    private function programaUnico(): bool
    {
        return $this->filas->isNotEmpty() && $this->filas->map(fn ($f) => $f['u']->programa_id)->unique()->count() === 1;
    }

    private function cicloUnico(): bool
    {
        return $this->filas->isNotEmpty() && $this->filas->map(fn ($f) => $this->claveCiclo($f['u']))->unique()->count() === 1;
    }

    private function programaReporte(): string
    {
        if ($this->programaUnico()) {
            return $this->filas->first()['u']->programa->nombre ?? 'Sin programa';
        }
        $n = $this->filas->map(fn ($f) => $f['u']->programa_id)->unique()->count();

        return $this->ctx('programa') ?: ($n ? "Todos ({$n} programas)" : '—');
    }

    private function cicloReporte(): string
    {
        if ($this->cicloUnico()) {
            return $this->ciclo($this->filas->first()['u']);
        }
        $n = $this->filas->map(fn ($f) => $this->claveCiclo($f['u']))->unique()->count();

        return $this->ctx('ciclo') ?: ($n ? "Todos ({$n} grupos de ciclo)" : '—');
    }

    private function ctx(string $clave): ?string
    {
        $v = $this->contexto[$clave] ?? null;

        return is_string($v) && trim($v) !== '' ? trim($v) : null;
    }

    private function filtrosReporte(): string
    {
        $chips = array_map(fn ($c) => is_string($c) ? trim($c) : null, (array) ($this->contexto['chips'] ?? []));
        $partes = array_filter([
            $this->ctx('estado') ? 'Matrícula: '.$this->ctx('estado') : null,
            ...$chips,
            $this->ctx('busqueda') ? 'Búsqueda: "'.$this->ctx('busqueda').'"' : null,
        ]);

        return $partes ? implode('  ·  ', $partes) : 'Ninguno';
    }

    /** "Ciclo IV", "Egresados 2023-I"… */
    private function ciclo($u): string
    {
        $nombre = trim((string) ($u->ciclo->nombre ?? ''));
        if ($nombre === '') {
            return 'Sin ciclo';
        }

        return preg_match('/^(ciclo|egresad)/i', $nombre) ? $nombre : 'Ciclo '.$nombre;
    }

    // ───────────────────────── Columnas ─────────────────────────

    /** [clave => [título, ancho, alineación]] según el preset y lo que ya dice la ficha. */
    private function columnas(): array
    {
        $cols = ['n' => ['N°', 5, 'c'], 'apellidos' => ['Apellidos', 24, 'l'], 'nombres' => ['Nombres', 22, 'l'], 'dni' => ['DNI', 11, 'c']];
        if (! $this->programaUnico()) {
            $cols['programa'] = ['Programa', 28, 'l'];
        }
        if (! $this->cicloUnico()) {
            $cols['ciclo'] = ['Ciclo', 15, 'c'];
        }

        return $cols + match ($this->preset) {
            'matricula' => [
                'estado' => ['Estado de matrícula', 16, 'c'], 'voucher' => ['N° de voucher', 15, 'c'],
                'verificado' => ['Voucher verificado', 11, 'c'], 'enviada' => ['Ficha enviada', 12, 'c'],
                'fecha' => ['Fecha de matrícula', 12, 'c'], 'celular' => ['Celular', 14, 'c'],
            ],
            'contacto' => [
                'correo' => ['Correo institucional', 34, 'l'], 'celular' => ['Celular', 14, 'c'],
                'whatsapp' => ['WhatsApp', 11, 'c'], 'referencia' => ['N.º de referencia', 16, 'c'],
                'perfil' => ['Perfil', 14, 'l'],
            ],
            default => [
                'genero' => ['Género', 11, 'c'], 'condicion' => ['Condición', 13, 'l'], 'perfil' => ['Perfil', 14, 'l'],
                'beca' => ['Beca', 7, 'c'], 'pendientes' => ['Cursos pendientes', 30, 'l'], 'celular' => ['Celular', 14, 'c'],
                'correo' => ['Correo institucional', 34, 'l'],
            ],
        };
    }

    // ───────────────────────── Hoja 1: Directorio ─────────────────────────

    private function dibujar(Worksheet $hoja): void
    {
        $columnas = $this->columnas();
        $claves = array_keys($columnas);
        $nCols = count($claves);
        $ultimaCol = $this->col($nCols);

        foreach (array_values($columnas) as $i => [, $ancho]) {
            $hoja->getColumnDimension($this->col($i + 1))->setWidth($ancho);
        }

        $fila = $this->encabezado($hoja, $ultimaCol);
        $fila = $this->ficha($hoja, $fila, $nCols);
        $fila = $this->indicadores($hoja, $fila, $nCols);

        // ── Tabla ──
        $cab = $fila;
        foreach (array_values($columnas) as $i => [$titulo]) {
            $hoja->setCellValue($this->col($i + 1).$cab, $titulo);
        }
        $hoja->getRowDimension($cab)->setRowHeight(30);
        $this->estiloCabecera($hoja, "A{$cab}:{$ultimaCol}{$cab}");

        $primera = $cab + 1;
        $f = $primera;
        foreach ($this->filas as $n => $datos) {
            // Cebra primero: las celdas con color propio (estado) lo pisan después.
            if ($n % 2 === 1) {
                $hoja->getStyle("A{$f}:{$ultimaCol}{$f}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::ZEBRA);
            }
            foreach ($claves as $i => $clave) {
                $this->celda($hoja, $this->col($i + 1).$f, $clave, $datos, $n + 1);
            }
            $hoja->getRowDimension($f)->setRowHeight(18);
            $f++;
        }
        $ultima = max($primera, $f - 1);
        if ($this->filas->isEmpty()) {
            $hoja->setCellValue("A{$primera}", 'Sin estudiantes con los filtros aplicados.');
            $hoja->mergeCells("A{$primera}:{$ultimaCol}{$primera}");
        }

        foreach (array_values($columnas) as $i => [, , $alin]) {
            $col = $this->col($i + 1);
            $hoja->getStyle("{$col}{$primera}:{$col}{$ultima}")->getAlignment()
                ->setHorizontal($alin === 'c' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT)
                ->setIndent($alin === 'c' ? 0 : 1);
        }
        $hoja->getStyle("A{$primera}:{$ultimaCol}{$ultima}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDE]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $hoja->getStyle("A{$primera}:A{$ultima}")->getFont()->setSize(9)->getColor()->setRGB(self::GRIS);
        $hoja->getStyle("B{$primera}:B{$ultima}")->getFont()->setBold(true)->getColor()->setRGB(self::AZUL_OSCURO);
        // Línea más marcada para cerrar la tabla.
        $hoja->getStyle("A{$ultima}:{$ultimaCol}{$ultima}")->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB(self::AZUL);

        $nota = $ultima + 2;
        $hoja->setCellValue("A{$nota}", 'Celulares y n.º de referencia tomados de la ficha de matrícula del estudiante. «—» indica dato no registrado. '
            .'Documento de uso interno: contiene datos personales protegidos por la Ley N.º 29733.');
        $hoja->mergeCells("A{$nota}:{$ultimaCol}{$nota}");
        $hoja->getStyle("A{$nota}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 8.5, 'color' => ['rgb' => self::GRIS]],
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP, 'indent' => 1],
        ]);
        $hoja->getRowDimension($nota)->setRowHeight(26);

        $hoja->freezePane('D'.$primera);
        if ($this->filas->isNotEmpty()) {
            $hoja->setAutoFilter("A{$cab}:{$ultimaCol}{$ultima}");
        }
        $hoja->setSelectedCell('A'.$primera);
        $this->impresion($hoja, "A1:{$ultimaCol}{$nota}", $cab);
    }

    /** Filas 1–2: título y subtítulo. Devuelve la siguiente fila libre. */
    private function encabezado(Worksheet $hoja, string $ultimaCol): int
    {
        $hoja->setCellValue('A1', 'Directorio de estudiantes  ·  '.self::PRESETS[$this->preset]);
        $hoja->mergeCells("A1:{$ultimaCol}1");
        $hoja->getRowDimension(1)->setRowHeight(34);
        $hoja->getStyle("A1:{$ultimaCol}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL_OSCURO]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);

        $hoja->setCellValue('A2', 'EESP Pukllasunchis  ·  '.(self::PESTANAS[$this->tab] ?? ''));
        $hoja->mergeCells("A2:{$ultimaCol}2");
        $hoja->getRowDimension(2)->setRowHeight(20);
        $hoja->getStyle("A2:{$ultimaCol}2")->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $hoja->getRowDimension(3)->setRowHeight(8);

        return 4;
    }

    /**
     * Ficha del reporte en tres filas de dos mitades:
     *   Programa | Ciclo
     *   Periodo  | Filtros aplicados
     *   Generado | Alcance
     */
    private function ficha(Worksheet $hoja, int $fila, int $nCols): int
    {
        $total = $this->filas->count();
        $alcance = (($this->contexto['alcance'] ?? '') === 'seleccionados' ? 'Estudiantes seleccionados' : 'Todo lo visible en pantalla')
            .'  ·  '.$total.' estudiante'.($total === 1 ? '' : 's');
        $datos = [
            [['Programa', $this->programaReporte()], ['Ciclo', $this->cicloReporte()]],
            [['Periodo', $this->periodo ?: '—'], ['Filtros', $this->filtrosReporte()]],
            [['Generado', now()->format('d/m/Y H:i').($this->generadoPor ? '  ·  '.$this->generadoPor : '')], ['Alcance', $alcance]],
        ];
        $mitad = max(3, intdiv($nCols, 2));
        foreach ($datos as [[$k1, $v1], [$k2, $v2]]) {
            // Etiqueta y valor en una sola celda combinada: no depende del ancho de las columnas.
            foreach ([[1, $mitad, $k1, $v1], [$mitad + 1, $nCols, $k2, $v2]] as [$desde, $hasta, $k, $v]) {
                $apagado = $k === 'Filtros' && $v === 'Ninguno';
                $texto = new RichText();
                $texto->createTextRun(mb_strtoupper($k).'   ')->getFont()->setBold(true)->setSize(8)->getColor()->setRGB(self::GRIS);
                $run = $texto->createTextRun($v);
                $run->getFont()->setBold(! $apagado)->setItalic($apagado)->setSize(10.5)->getColor()->setRGB($apagado ? self::GRIS_CLARO : self::AZUL_OSCURO);
                $rango = $this->col($desde).$fila.':'.$this->col($hasta).$fila;
                $hoja->setCellValue($this->col($desde).$fila, $texto);
                if ($hasta > $desde) {
                    $hoja->mergeCells($rango);
                }
                $hoja->getStyle($rango)->applyFromArray([
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true],
                    'borders' => ['left' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => self::AZUL_SUAVE]]],
                ]);
            }
            $hoja->getRowDimension($fila)->setRowHeight(mb_strlen($v2) > 60 ? 32 : 21);
            $hoja->getStyle("A{$fila}:".$this->col($nCols).$fila)->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::BORDE);
            $fila++;
        }
        $hoja->getRowDimension($fila)->setRowHeight(10);

        return $fila + 1;
    }

    /** Tarjetas de indicadores (número grande + etiqueta) repartidas a lo ancho. */
    private function indicadores(Worksheet $hoja, int $fila, int $nCols): int
    {
        $total = $this->filas->count();
        $porEstado = $this->filas->countBy(fn ($f) => $f['m']->estado);
        $pct = fn ($n) => $total ? '  ·  '.round($n * 100 / $total).' %' : '';
        $porVerificar = $this->filas->filter(fn ($f) => $f['m']->estado === 'matriculado' && ! $f['m']->voucher_verificado)->count();

        $tarjetas = match ($this->preset) {
            'contacto' => [
                ['Estudiantes', $total, self::AZUL_OSCURO, self::AZUL_SUAVE, ''],
                ['Con celular', $c = $this->filas->whereNotNull('celular')->count(), self::VERDE, self::VERDE_FONDO, $pct($c)],
                ['Sin celular', $total - $c, $total - $c ? self::ROJO : self::GRIS, $total - $c ? self::ROJO_FONDO : self::ZEBRA, $pct($total - $c)],
                ['Con n.º de referencia', $r = $this->filas->whereNotNull('referencia')->count(), self::AZUL, self::AZUL_SUAVE, $pct($r)],
            ],
            'matricula' => [
                ['Matriculados', $mat = $porEstado['matriculado'] ?? 0, self::VERDE, self::VERDE_FONDO, $pct($mat)],
                ['Voucher por verificar', $porVerificar, self::AMBAR, self::AMBAR_FONDO, ''],
                ['Sin matrícula', $porEstado['sin_matricula'] ?? 0, self::AMBAR, self::AMBAR_FONDO, ''],
                ['Sin ficha', $porEstado['sin_ficha'] ?? 0, self::ROJO, self::ROJO_FONDO, ''],
                ['No aplica', $porEstado['no_aplica'] ?? 0, self::GRIS, self::ZEBRA, ''],
            ],
            default => [
                ['Estudiantes', $total, self::AZUL_OSCURO, self::AZUL_SUAVE, ''],
                ['Con beca', $b = $this->filas->filter(fn ($f) => $f['u']->beca == 1)->count(), self::VERDE, self::VERDE_FONDO, $pct($b)],
                ['Con cursos pendientes', $p = $this->filas->filter(fn ($f) => count($f['u']->cursosPendientes()) > 0)->count(), self::AMBAR, self::AMBAR_FONDO, $pct($p)],
                ['Egresados', $e = $this->filas->filter(fn ($f) => $f['u']->condicion === 'Egresado')->count(), self::GRIS, self::ZEBRA, $pct($e)],
            ],
        };

        // Cada tarjeta ocupa un tramo de columnas; la última absorbe el resto.
        $nT = count($tarjetas);
        $ancho = max(1, intdiv($nCols, $nT));
        $desde = 1;
        foreach ($tarjetas as $i => [$etiqueta, $valor, $color, $fondo, $extra]) {
            if ($desde > $nCols) {
                break;
            }
            $hasta = $i === $nT - 1 ? $nCols : min($nCols, $desde + $ancho - 1);
            $r1 = $this->col($desde).$fila.':'.$this->col($hasta).$fila;
            $r2 = $this->col($desde).($fila + 1).':'.$this->col($hasta).($fila + 1);
            $hoja->setCellValue($this->col($desde).$fila, $valor);
            $hoja->setCellValue($this->col($desde).($fila + 1), mb_strtoupper($etiqueta).$extra);
            if ($hasta > $desde) {
                $hoja->mergeCells($r1);
                $hoja->mergeCells($r2);
            }
            $rango = $this->col($desde).$fila.':'.$this->col($hasta).($fila + 1);
            $hoja->getStyle($rango)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fondo]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $hoja->getStyle($r1)->getFont()->setBold(true)->setSize(18)->getColor()->setRGB($color);
            $hoja->getStyle($r2)->getFont()->setBold(true)->setSize(7.5)->getColor()->setRGB(self::GRIS);
            // Franja de color arriba y separación blanca entre tarjetas.
            $hoja->getStyle($r1)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK)->getColor()->setRGB($color);
            $hoja->getStyle($rango)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK)->getColor()->setRGB('FFFFFF');
            $desde = $hasta + 1;
        }
        $hoja->getRowDimension($fila)->setRowHeight(30);
        $hoja->getRowDimension($fila + 1)->setRowHeight(17);
        $hoja->getRowDimension($fila + 2)->setRowHeight(12);

        return $fila + 3;
    }

    /** Escribe una celda de la tabla según su clave de columna. */
    private function celda(Worksheet $hoja, string $c, string $clave, array $d, int $n): void
    {
        $u = $d['u'];
        $m = $d['m'];
        $matriculado = $m->estado === 'matriculado';

        match ($clave) {
            'n' => $hoja->setCellValue($c, $n),
            'apellidos' => $this->texto($hoja, $c, $u->apellidos),
            'nombres' => $this->texto($hoja, $c, $u->name),
            'dni' => $this->texto($hoja, $c, $u->dni),
            'programa' => $this->texto($hoja, $c, $u->programa->nombre ?? null),
            'ciclo' => $this->texto($hoja, $c, trim(str_ireplace('Ciclo', '', $u->ciclo->nombre ?? ''))),
            'correo' => $this->texto($hoja, $c, $u->email ?: $m->ficha?->email),
            'celular' => $this->telefono($hoja, $c, $d['celular']),
            'referencia' => $this->telefono($hoja, $c, $d['referencia']),
            'whatsapp' => $this->whatsapp($hoja, $c, $d['celular']),
            'perfil' => $this->texto($hoja, $c, $u->perfil),
            'genero' => $this->texto($hoja, $c, $u->genero ?: $m->ficha?->genero),
            'condicion' => $this->texto($hoja, $c, $u->condicion),
            'beca' => $this->siNo($hoja, $c, $u->beca == 1),
            'pendientes' => $this->texto($hoja, $c, implode(', ', $u->cursosPendientes())),
            'estado' => $this->estado($hoja, $c, $m),
            'voucher' => $this->texto($hoja, $c, $m->voucher),
            'verificado' => $matriculado ? $this->siNo($hoja, $c, $m->voucher_verificado) : $this->vacio($hoja, $c),
            'enviada' => $m->ficha_enviada_at
                ? $hoja->setCellValue($c, $m->ficha_enviada_at->format('d/m/Y'))
                : ($matriculado ? $this->siNo($hoja, $c, false) : $this->vacio($hoja, $c)),
            'fecha' => $this->texto($hoja, $c, $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') : null),
        };
    }

    // ───────────────────────── Hoja 2: Resumen ─────────────────────────

    /** Métricas por grupo según el preset: [título => fn(Collection $filas): int]. */
    private function metricasResumen(): array
    {
        $porVerificar = fn ($g) => $g->filter(fn ($f) => $f['m']->estado === 'matriculado' && ! $f['m']->voucher_verificado)->count();

        return match ($this->preset) {
            'contacto' => [
                'Con celular' => fn ($g) => $g->whereNotNull('celular')->count(),
                'Sin celular' => fn ($g) => $g->whereNull('celular')->count(),
                'Con n.º de referencia' => fn ($g) => $g->whereNotNull('referencia')->count(),
            ],
            'matricula' => collect(EstadoMatricula::ETIQUETAS)
                ->mapWithKeys(fn ($etq, $clave) => [$etq => fn ($g) => $g->filter(fn ($f) => $f['m']->estado === $clave)->count()])
                ->put('Voucher por verificar', $porVerificar)->all(),
            default => [
                'Con beca' => fn ($g) => $g->filter(fn ($f) => $f['u']->beca == 1)->count(),
                'Con cursos pendientes' => fn ($g) => $g->filter(fn ($f) => count($f['u']->cursosPendientes()) > 0)->count(),
                'Egresados' => fn ($g) => $g->filter(fn ($f) => $f['u']->condicion === 'Egresado')->count(),
            ],
        };
    }

    /** Conteo por programa y ciclo, con las métricas del preset y una fila de total. */
    private function dibujarResumen(Worksheet $hoja): void
    {
        $metricas = $this->metricasResumen();
        $titulos = ['Programa', 'Ciclo', 'Estudiantes', ...array_keys($metricas)];
        $ultimaCol = $this->col(count($titulos));
        foreach ($titulos as $i => $t) {
            $hoja->getColumnDimension($this->col($i + 1))->setWidth($i === 0 ? 34 : ($i === 1 ? 18 : 14));
        }

        $hoja->setCellValue('A1', 'Resumen por programa y ciclo  ·  '.self::PRESETS[$this->preset]);
        $hoja->mergeCells("A1:{$ultimaCol}1");
        $hoja->getRowDimension(1)->setRowHeight(30);
        $hoja->getStyle("A1:{$ultimaCol}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL_OSCURO]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $hoja->setCellValue('A2', implode('  ·  ', array_filter([
            self::PESTANAS[$this->tab] ?? null, $this->periodo ? 'Periodo '.$this->periodo : null, 'Filtros: '.$this->filtrosReporte(),
        ])));
        $hoja->mergeCells("A2:{$ultimaCol}2");
        $hoja->getStyle("A2:{$ultimaCol}2")->applyFromArray([
            'font' => ['size' => 9.5, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true],
        ]);
        $hoja->getRowDimension(2)->setRowHeight(22);

        $cab = 4;
        foreach ($titulos as $i => $t) {
            $hoja->setCellValue($this->col($i + 1).$cab, $t);
        }
        $hoja->getRowDimension($cab)->setRowHeight(30);
        $this->estiloCabecera($hoja, "A{$cab}:{$ultimaCol}{$cab}");

        // Orden: programa y luego ciclo (por id, que sigue el orden I, II, III…; egresados al final).
        $grupos = $this->filas
            ->sortBy(fn ($f) => sprintf('%s|%d|%06d', $f['u']->programa->nombre ?? '~', str_starts_with(mb_strtolower($this->ciclo($f['u'])), 'egresad') ? 1 : 0, $f['u']->ciclo_id ?? 999999))
            ->groupBy(fn ($f) => $this->claveCiclo($f['u']));

        $fila = $cab + 1;
        $programaAnterior = null;
        foreach ($grupos as $clave => $g) {
            [$programa, $ciclo] = explode('|', $clave, 2);
            $programa = $programa !== '' ? $programa : 'Sin programa';
            $nuevo = $programa !== $programaAnterior;
            $hoja->setCellValue("A{$fila}", $nuevo ? $programa : '');
            $hoja->setCellValue("B{$fila}", $ciclo);
            $hoja->setCellValue("C{$fila}", $g->count());
            $i = 4;
            foreach ($metricas as $fn) {
                $v = $fn($g);
                $hoja->setCellValue($this->col($i).$fila, $v);
                if ($v === 0) {
                    $hoja->getStyle($this->col($i).$fila)->getFont()->getColor()->setRGB(self::GRIS_CLARO);
                }
                $i++;
            }
            if ($nuevo && $programaAnterior !== null) {
                $hoja->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getBorders()->getTop()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::AZUL);
            }
            $programaAnterior = $programa;
            $hoja->getRowDimension($fila)->setRowHeight(18);
            $fila++;
        }
        $ultima = $fila - 1;
        if ($ultima > $cab) {
            $hoja->getStyle('A'.($cab + 1).":{$ultimaCol}{$ultima}")->applyFromArray([
                'borders' => ['horizontal' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => self::BORDE]]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $hoja->getStyle('A'.($cab + 1).":A{$ultima}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => self::AZUL_OSCURO]],
                'alignment' => ['indent' => 1],
            ]);
            $hoja->getStyle('B'.($cab + 1).":{$ultimaCol}{$ultima}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle('C'.($cab + 1).":C{$ultima}")->getFont()->setBold(true);
        }

        // Total con fórmulas: si alguien ajusta el detalle, el total se recalcula.
        $hoja->setCellValue("A{$fila}", 'Total');
        $hoja->mergeCells("A{$fila}:B{$fila}");
        for ($i = 3; $i <= count($titulos); $i++) {
            $col = $this->col($i);
            $hoja->setCellValue("{$col}{$fila}", $ultima > $cab ? "=SUM({$col}".($cab + 1).":{$col}{$ultima})" : 0);
        }
        $hoja->getStyle("A{$fila}:{$ultimaCol}{$fila}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL_OSCURO]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $hoja->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
        $hoja->getRowDimension($fila)->setRowHeight(22);

        $hoja->freezePane('A'.($cab + 1));
        $hoja->setSelectedCell('A'.($cab + 1));
        $this->impresion($hoja, "A1:{$ultimaCol}{$fila}", $cab);
    }

    // ───────────────────────── Celdas ─────────────────────────

    private function estado(Worksheet $hoja, string $celda, object $m): void
    {
        [$color, $fondo] = self::ESTADOS[$m->estado];
        $hoja->setCellValue($celda, $m->etiqueta);
        $estilo = $hoja->getStyle($celda);
        $estilo->getFont()->setBold(true)->getColor()->setRGB($color);
        if ($fondo) {
            $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fondo);
        }
    }

    private function whatsapp(Worksheet $hoja, string $celda, ?string $celular): void
    {
        $enlace = Telefono::whatsapp($celular) ?? Telefono::whatsapp($this->celularesPeruanos((string) $celular)[0] ?? null);
        if (! $enlace) {
            $this->vacio($hoja, $celda);

            return;
        }
        $hoja->setCellValue($celda, 'Abrir chat');
        $hoja->getCell($celda)->getHyperlink()->setUrl($enlace)->setTooltip('Abrir WhatsApp con '.$celular);
        $hoja->getStyle($celda)->getFont()->setUnderline(true)->setBold(true)->getColor()->setRGB(self::WHATSAPP);
    }

    /**
     * Celular o n.º de referencia, siempre como texto (que Excel no lo vuelva número ni le
     * quite ceros). Los celulares peruanos se agrupan "984 320 295"; lo que no es un número
     * reconocible (fijos, texto suelto) se deja tal cual vino en la ficha.
     */
    private function telefono(Worksheet $hoja, string $celda, ?string $numero): void
    {
        if ($numero === null) {
            $this->vacio($hoja, $celda);

            return;
        }
        $normalizado = Telefono::normalizar($numero);
        $celulares = $this->celularesPeruanos($numero);
        $texto = match (true) {
            $normalizado && str_starts_with($normalizado, '51') && strlen($normalizado) === 11 => $this->agrupar(substr($normalizado, 2)),
            count($celulares) > 1 => implode(' / ', array_map(fn ($c) => $this->agrupar($c), $celulares)),
            (bool) $normalizado => '+'.$normalizado,
            default => $numero,
        };
        $hoja->setCellValueExplicit($celda, $texto, DataType::TYPE_STRING);
        if (! $normalizado && count($celulares) < 2) {
            $hoja->getStyle($celda)->getFont()->getColor()->setRGB(self::GRIS);
        }
    }

    /** Celulares de 9 dígitos que empiezan en 9 dentro de un texto libre ("910602320-928780378"). */
    private function celularesPeruanos(string $numero): array
    {
        preg_match_all('/(?<!\d)(?:\+?51[\s-]?)?(9\d{2}[\s.-]?\d{3}[\s.-]?\d{3})(?!\d)/', $numero, $m);

        return array_map(fn ($c) => preg_replace('/\D+/', '', $c), $m[1]);
    }

    private function agrupar(string $nueveDigitos): string
    {
        return implode(' ', str_split($nueveDigitos, 3));
    }

    private function texto(Worksheet $hoja, string $celda, $valor): void
    {
        $valor = $this->limpio($valor);
        $valor === null
            ? $this->vacio($hoja, $celda)
            : $hoja->setCellValueExplicit($celda, $valor, DataType::TYPE_STRING);
    }

    private function siNo(Worksheet $hoja, string $celda, bool $si): void
    {
        $hoja->setCellValue($celda, $si ? 'Sí' : 'No');
        $hoja->getStyle($celda)->getFont()->setBold($si)->getColor()->setRGB($si ? self::VERDE : self::GRIS);
    }

    private function vacio(Worksheet $hoja, string $celda): void
    {
        $hoja->setCellValue($celda, '—');
        $hoja->getStyle($celda)->getFont()->setItalic(true)->getColor()->setRGB(self::GRIS_CLARO);
    }

    private function limpio($valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' || $valor === '-' ? null : $valor;
    }

    // ───────────────────────── Formato general ─────────────────────────

    private function estiloCabecera(Worksheet $hoja, string $rango): void
    {
        $hoja->getStyle($rango)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
    }

    /** Horizontal, a lo ancho de una página, cabecera de tabla repetida y pie con paginación. */
    private function impresion(Worksheet $hoja, string $rango, int $filaCabecera): void
    {
        $setup = $hoja->getPageSetup();
        $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4);
        $setup->setFitToWidth(1)->setFitToHeight(0);
        $setup->setPrintArea($rango);
        $setup->setRowsToRepeatAtTopByStartAndEnd($filaCabecera, $filaCabecera);
        $hoja->getPageMargins()->setTop(0.5)->setBottom(0.6)->setLeft(0.4)->setRight(0.4);
        // En el pie, "&" es un código de formato: se duplica para que salga literal.
        $pie = str_replace('&', '&&', 'Directorio de estudiantes · '.self::PRESETS[$this->preset].' · '.$this->programaReporte());
        $hoja->getHeaderFooter()->setOddFooter('&L&8'.$pie.'&C&8Uso interno · EESP Pukllasunchis&R&8Página &P de &N');
    }

    private function col(int $indice): string
    {
        return Coordinate::stringFromColumnIndex($indice);
    }
}
