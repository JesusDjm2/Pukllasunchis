<?php

namespace App\Exports\Docente;

use App\Models\Docente;
use App\Models\PeriodoActual;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Registro de calificaciones de UN curso del docente (botón "Excel" de cada tarjeta del
 * dashboard). Arriba va la ficha —Programa, Ciclo, Docente y Periodo— y al pie un resumen.
 * Los datos ($c) llegan armados desde DocenteCOntroller::datosCalificacionesCurso(); aquí
 * solo se da formato. Paleta: la misma de CalificacionesExport / RegistrosExport.
 *
 * $c = ['curso' => Curso, 'esPpd' => bool, 'competencias' => Collection, 'filas' => array]
 */
abstract class HojaBase implements FromArray, WithEvents, WithTitle, WithProperties
{
    /** Reglamento: aprobado con 11 o más en la escala vigesimal (ver HistorialAcademico). */
    public const NOTA_MINIMA = 11;

    /** Primera fila de la tabla, debajo de la ficha. */
    protected const FILA_TABLA = 6;

    protected const AZUL_OSCURO = '1E3A5F';
    protected const AZUL = '2E5C8A';
    protected const AZUL_SUAVE = 'E8EEF6';
    protected const BORDE = 'D9E2EC';
    protected const ZEBRA = 'F7F9FC';
    protected const GRIS = '6B7280';
    protected const GRIS_CLARO = '9CA3AF';
    protected const ROJO = 'C0392B';
    protected const ROJO_FONDO = 'FDECEA';
    protected const VERDE = '0B7A43';
    protected const VERDE_FONDO = 'E6F4EC';
    protected const AMBAR = '9A6700';
    protected const AMBAR_FONDO = 'FFF4D6';

    /** Mismos colores que la leyenda de la pantalla de calificar. */
    protected const NIVELES = [
        'destacado' => '103B86',
        'logrado' => '0B954E',
        'en proceso' => 'B39A00',
        'inicio' => 'DC3545',
        'previo al inicio' => 'DC3545',
    ];

    public function __construct(protected Docente $docente, protected ?PeriodoActual $periodo, protected array $c) {}

    public function array(): array
    {
        return [];
    }

    /** Nombre de la hoja: máx. 31 caracteres y sin []:*?/\ . */
    public function title(): string
    {
        $limpio = trim(preg_replace('/\s+/', ' ', preg_replace('/[\[\]:*?\/\\\\]/', ' ', $this->c['curso']->nombre)));

        return mb_strlen($limpio) > 31 ? rtrim(mb_substr($limpio, 0, 30)).'…' : $limpio;
    }

    public function properties(): array
    {
        return [
            'creator' => 'EESP Pukllasunchis',
            'lastModifiedBy' => 'Sistema académico EESP Pukllasunchis',
            'title' => 'Calificaciones — '.$this->c['curso']->nombre,
            'subject' => $this->programa().' · Ciclo '.$this->ciclo().' · Periodo '.($this->periodo->nombre ?? ''),
            'company' => 'EESP Pukllasunchis',
        ];
    }

    protected function programa(): string
    {
        return $this->c['curso']->ciclo->programa->nombre ?? '—';
    }

    protected function ciclo(): string
    {
        return trim(str_ireplace('Ciclo', '', $this->c['curso']->ciclo->nombre ?? '—'));
    }

    /** Estadísticas de la nota final (FID: Desempeño; PPD: calificación en el sistema). */
    protected function estadisticas(): array
    {
        $notas = collect($this->c['filas'])->map(fn ($f) => $this->c['esPpd'] ? $f['nota'] : $f['p3']['nota'])
            ->filter(fn ($n) => $n !== null)->values();

        return [
            'total' => count($this->c['filas']),
            'conNota' => $notas->count(),
            'promedio' => $notas->isNotEmpty() ? round($notas->avg(), 2) : null,
            'aprobados' => $notas->filter(fn ($n) => $n >= self::NOTA_MINIMA)->count(),
            'desaprobados' => $notas->filter(fn ($n) => $n < self::NOTA_MINIMA)->count(),
        ];
    }

    abstract protected function dibujar(Worksheet $hoja): void;

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $hoja = $event->sheet->getDelegate();
                $hoja->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);
                $hoja->setShowGridlines(false);
                $this->dibujar($hoja);
            },
        ];
    }

    /**
     * Fila 1: nombre del curso. Fila 2: institución y tipo de documento. Filas 3–4: ficha
     * Programa / Ciclo / Docente / Periodo. La tabla empieza en FILA_TABLA.
     */
    protected function encabezado(Worksheet $hoja, int $ultimaColIdx): void
    {
        $ultimaCol = $this->col($ultimaColIdx);

        $hoja->setCellValue('A1', $this->c['curso']->nombre);
        $hoja->mergeCells("A1:{$ultimaCol}1");
        $hoja->getRowDimension(1)->setRowHeight(32);
        $hoja->getStyle("A1:{$ultimaCol}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL_OSCURO]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);

        $hoja->setCellValue('A2', 'Registro de calificaciones  ·  EESP Pukllasunchis  ·  '.($this->c['esPpd'] ? 'Profesionalización Docente (PPD)' : 'Formación Inicial Docente (FID)'));
        $hoja->mergeCells("A2:{$ultimaCol}2");
        $hoja->getRowDimension(2)->setRowHeight(20);
        $hoja->getStyle("A2:{$ultimaCol}2")->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);

        // Ficha en dos filas y dos mitades: [Programa | Ciclo] y [Docente | Periodo].
        $mitad = max(3, intdiv($ultimaColIdx, 2));
        $datos = [
            3 => [['Programa', $this->programa()], ['Ciclo', $this->ciclo()]],
            4 => [['Docente', $this->docente->nombre], ['Periodo', $this->periodo->nombre ?? '—']],
        ];
        foreach ($datos as $fila => [[$k1, $v1], [$k2, $v2]]) {
            foreach ([[1, 2, $mitad, $k1, $v1], [$mitad + 1, $mitad + 2, $ultimaColIdx, $k2, $v2]] as [$cK, $cV, $cFin, $k, $v]) {
                $celdaK = $this->col($cK).$fila;
                $hoja->setCellValue($celdaK, mb_strtoupper($k));
                $hoja->getStyle($celdaK)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8.5, 'color' => ['rgb' => self::GRIS]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL_SUAVE]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
                $rango = $this->col($cV).$fila.':'.$this->col(max($cV, $cFin)).$fila;
                $hoja->setCellValueExplicit($this->col($cV).$fila, (string) $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                if ($cFin > $cV) {
                    $hoja->mergeCells($rango);
                }
                $hoja->getStyle($rango)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10.5, 'color' => ['rgb' => self::AZUL_OSCURO]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
            }
            $hoja->getRowDimension($fila)->setRowHeight(21);
            $hoja->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::BORDE);
        }
    }

    /** Resumen del curso al pie de la tabla; devuelve la fila siguiente libre. */
    protected function resumen(Worksheet $hoja, int $fila, string $ultimaCol): int
    {
        $e = $this->estadisticas();
        $hoja->setCellValue("B{$fila}", 'Resumen del curso');
        $hoja->getStyle("B{$fila}")->getFont()->setBold(true)->getColor()->setRGB(self::AZUL_OSCURO);

        $partes = [
            ['Estudiantes', $e['total'], self::AZUL_OSCURO],
            ['Con nota final', $e['conNota'], self::AZUL_OSCURO],
            ['Promedio', $e['promedio'] !== null ? number_format($e['promedio'], 2) : '—',
                $e['promedio'] !== null && $e['promedio'] < self::NOTA_MINIMA ? self::ROJO : self::AZUL_OSCURO],
            ['Aprobados', $e['aprobados'], self::VERDE],
            ['Desaprobados', $e['desaprobados'], $e['desaprobados'] ? self::ROJO : self::AZUL_OSCURO],
        ];
        $texto = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        foreach ($partes as $i => [$k, $v, $color]) {
            $texto->createTextRun(($i ? '     ' : '').$k.': ')->getFont()->setSize(9.5)->getColor()->setRGB(self::GRIS);
            $run = $texto->createTextRun((string) $v);
            $run->getFont()->setBold(true)->setSize(10)->getColor()->setRGB($color);
        }
        $hoja->setCellValue('B'.($fila + 1), $texto);
        $hoja->mergeCells('B'.($fila + 1).":{$ultimaCol}".($fila + 1));
        $hoja->getStyle('B'.($fila + 1).":{$ultimaCol}".($fila + 1))->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL_SUAVE]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $hoja->getRowDimension($fila + 1)->setRowHeight(22);

        return $fila + 3;
    }

    protected function estiloCabeceraTabla(Worksheet $hoja, string $rango, string $fondo = self::AZUL): void
    {
        $hoja->getStyle($rango)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fondo]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
    }

    protected function estiloCuerpo(Worksheet $hoja, string $rango): void
    {
        $hoja->getStyle($rango)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDE]]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    /** Franjas alternas para seguir cada fila con la vista. */
    protected function cebra(Worksheet $hoja, int $desde, int $hasta, string $ultimaCol): void
    {
        for ($f = $desde; $f <= $hasta; $f++) {
            if (($f - $desde) % 2 === 1) {
                $hoja->getStyle("A{$f}:{$ultimaCol}{$f}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::ZEBRA);
            }
        }
    }

    /** Nota vigesimal: número con un decimal opcional, roja si desaprueba. */
    protected function nota(Worksheet $hoja, string $celda, $valor): void
    {
        if ($valor === null) {
            $this->vacio($hoja, $celda);

            return;
        }
        $hoja->setCellValue($celda, (float) $valor);
        $estilo = $hoja->getStyle($celda);
        $estilo->getNumberFormat()->setFormatCode('0;-0;0');
        $estilo->getFont()->setBold(true)->getColor()->setRGB($valor < self::NOTA_MINIMA ? self::ROJO : self::AZUL_OSCURO);
        if ($valor < self::NOTA_MINIMA) {
            $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::ROJO_FONDO);
        }
    }

    /** Nivel de logro con su color ("Logrado", "En Proceso"…). */
    protected function nivel(Worksheet $hoja, string $celda, ?string $texto): void
    {
        if ($texto === null || trim($texto) === '' || trim($texto) === '-') {
            $this->vacio($hoja, $celda);

            return;
        }
        $hoja->setCellValue($celda, $texto);
        $color = self::NIVELES[mb_strtolower(trim($texto))] ?? self::AZUL_OSCURO;
        $hoja->getStyle($celda)->getFont()->setBold(true)->getColor()->setRGB($color);
    }

    protected function vacio(Worksheet $hoja, string $celda): void
    {
        $hoja->setCellValue($celda, '—');
        $hoja->getStyle($celda)->getFont()->setItalic(true)->getColor()->setRGB(self::GRIS_CLARO);
    }

    protected function col(int $indice): string
    {
        return Coordinate::stringFromColumnIndex($indice);
    }

    /** Impresión: horizontal, a lo ancho de una página, cabecera repetida y pie con paginación. */
    protected function impresion(Worksheet $hoja, string $rango, int $filaCabeceraDesde, int $filaCabeceraHasta): void
    {
        $setup = $hoja->getPageSetup();
        $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4);
        $setup->setFitToWidth(1)->setFitToHeight(0);
        $setup->setPrintArea($rango);
        $setup->setRowsToRepeatAtTopByStartAndEnd($filaCabeceraDesde, $filaCabeceraHasta);
        $hoja->getPageMargins()->setTop(0.5)->setBottom(0.6)->setLeft(0.4)->setRight(0.4);
        // En el pie, "&" es un código de formato: se duplica para que salga literal.
        $pie = str_replace('&', '&&', $this->c['curso']->nombre.' · '.$this->programa().' · Ciclo '.$this->ciclo().' · '.$this->docente->nombre);
        $hoja->getHeaderFooter()->setOddFooter('&L&8'.$pie.'&R&8Página &P de &N');
    }
}
