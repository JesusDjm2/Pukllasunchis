<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Exportación de calificaciones archivadas de un período (actual o ya cerrado),
 * agrupada visualmente por Programa → Ciclo, con un bloque de resumen al final.
 * Misma paleta que CalificacionesExport para mantener una identidad visual única
 * entre los distintos exportables del sistema.
 */
class RegistrosExport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    private const COLOR_PROGRAMA_FONDO = '1E3A5F';

    private const COLOR_CICLO_FONDO = '2E5C8A';

    private const COLOR_ENCABEZADO_FONDO = '2E5C8A';

    private const COLOR_SUBTITULO_FONDO = 'EDF1F7';

    private const COLOR_BORDE = 'D9E2EC';

    private const COLOR_ZEBRA = 'F4F7FB';

    private const COLOR_APROBADO = '0B954E';

    private const COLOR_DESAPROBADO = 'DC3545';

    private const COLOR_SIN_DATOS = '9CA3AF';

    private const COLOR_RESUMEN_FONDO = 'F4F7FB';

    // Misma regla que la vista web (admin/periodos/calificaciones/show.blade.php):
    // calificacion_sistema > 11 se pinta como aprobado.
    private const UMBRAL_APROBADO = 11;

    private const TOTAL_COLUMNAS = 9; // A..I

    protected Collection $filas;

    protected string $periodoNombre;

    protected ?string $filtroCiclo;

    protected bool $soloBecas;

    protected array $bannerProgramaRows = [];

    protected array $bannerCicloRows = [];

    protected array $alumnoMergeMap = []; // filaInicio => filaFin

    protected array $zebraBlocks = []; // [filaInicio, filaFin] por bloque de alumno, para alternar color

    protected int $totalAlumnos = 0;

    protected int $totalRegistros = 0;

    protected int $totalAprobados = 0;

    protected int $totalDesaprobados = 0;

    protected int $totalSinDatos = 0;

    protected float $sumaCalificaciones = 0.0;

    protected int $countCalificacionesNumericas = 0;

    protected int $filaFinDatos = 3;

    public function __construct($filas, string $periodoNombre = '', ?string $filtroCiclo = null, bool $soloBecas = false)
    {
        $this->filas = collect($filas);
        $this->periodoNombre = $periodoNombre;
        $this->filtroCiclo = $filtroCiclo;
        $this->soloBecas = $soloBecas;
    }

    public function collection()
    {
        // 1) Aplanar a filas alumno-curso y ordenar por Programa → Ciclo → Alumno → Curso.
        $planas = collect();

        foreach ($this->filas as $entry) {
            $alumno = $entry['alumno'];
            $cursos = collect($entry['cursos'])
                ->filter(fn ($curso) => $curso && ! str_contains(strtolower($curso->cc ?? ''), 'extracurricular'));
            $periodosAlumno = $entry['periodos'];

            foreach ($cursos as $curso) {
                $periodo = $periodosAlumno instanceof Collection
                    ? $periodosAlumno->firstWhere('curso_id', $curso->id)
                    : ($periodosAlumno[$curso->id] ?? null);

                $planas->push([
                    'programa' => optional($alumno->programa)->nombre ?? 'Sin programa asignado',
                    'ciclo' => optional($curso->ciclo)->nombre ?? 'Sin ciclo asignado',
                    'ordenCiclo' => $curso->ciclo?->ordenCiclo() ?? 999,
                    'alumno' => trim(($alumno->apellidos ?? '').', '.($alumno->nombres ?? '')),
                    'alumnoId' => $alumno->id,
                    'dni' => $alumno->dni ?: '—',
                    'beca' => optional($alumno->user)->beca == 1 ? 'Sí' : 'No',
                    'curso' => $curso->nombre,
                    'valoracion' => $periodo->valoracion_curso ?? '—',
                    'calCurso' => $periodo->calificacion_curso ?? '—',
                    'calSistema' => $periodo->calificacion_sistema ?? null,
                ]);
            }
        }

        $planas = $planas->sortBy([
            ['programa', 'asc'],
            ['ordenCiclo', 'asc'],
            ['alumno', 'asc'],
            ['curso', 'asc'],
        ])->values();

        $this->totalAlumnos = $planas->pluck('alumnoId')->unique()->count();

        // 2) Recorrer y armar las filas de salida, insertando banners cuando cambia
        // el Programa o el Ciclo, y registrando los bloques a fusionar por alumno.
        $data = collect();
        $rowNumber = 4; // fila 1=título, 2=subtítulo, 3=encabezados
        $indiceAlumno = 0;
        $programaActual = null;
        $cicloActual = null;
        $alumnoActualId = null;
        $filaInicioAlumno = null;

        $cerrarBloqueAlumno = function () use (&$filaInicioAlumno, &$rowNumber) {
            if ($filaInicioAlumno !== null) {
                $filaFin = $rowNumber - 1;
                if ($filaFin > $filaInicioAlumno) {
                    $this->alumnoMergeMap[$filaInicioAlumno] = $filaFin;
                }
                $this->zebraBlocks[] = [$filaInicioAlumno, $filaFin];
            }
        };

        $filaVacia = fn ($valor) => array_pad([$valor], self::TOTAL_COLUMNAS, '');

        foreach ($planas as $fila) {
            if ($fila['programa'] !== $programaActual) {
                $cerrarBloqueAlumno();
                $filaInicioAlumno = null;
                $alumnoActualId = null;

                $data->push($filaVacia('PROGRAMA:  '.$fila['programa']));
                $this->bannerProgramaRows[] = $rowNumber;
                $rowNumber++;

                $programaActual = $fila['programa'];
                $cicloActual = null; // fuerza también el banner de ciclo
            }

            if ($fila['ciclo'] !== $cicloActual) {
                $cerrarBloqueAlumno();
                $filaInicioAlumno = null;
                $alumnoActualId = null;

                $data->push($filaVacia('CICLO:  '.$fila['ciclo']));
                $this->bannerCicloRows[] = $rowNumber;
                $rowNumber++;

                $cicloActual = $fila['ciclo'];
            }

            if ($fila['alumnoId'] !== $alumnoActualId) {
                $cerrarBloqueAlumno();
                $filaInicioAlumno = $rowNumber;
                $alumnoActualId = $fila['alumnoId'];
                $indiceAlumno++;
            }

            $calSistema = $fila['calSistema'];
            $esNumerico = $calSistema !== null && $calSistema !== '' && is_numeric($calSistema);

            if ($esNumerico) {
                $estado = ((float) $calSistema) > self::UMBRAL_APROBADO ? 'Aprobado' : 'Desaprobado';
                $this->sumaCalificaciones += (float) $calSistema;
                $this->countCalificacionesNumericas++;
                $estado === 'Aprobado' ? $this->totalAprobados++ : $this->totalDesaprobados++;
            } else {
                $estado = 'Sin datos';
                $this->totalSinDatos++;
            }

            $this->totalRegistros++;

            $data->push([
                $indiceAlumno,
                $fila['alumno'],
                $fila['dni'],
                $fila['beca'],
                $fila['curso'],
                $fila['valoracion'],
                $fila['calCurso'],
                $esNumerico ? (float) $calSistema : '—',
                $estado,
            ]);
            $rowNumber++;
        }

        $cerrarBloqueAlumno();

        $this->filaFinDatos = $rowNumber - 1;

        return $data;
    }

    public function headings(): array
    {
        $subtitulo = 'Filtro de ciclo: '.($this->filtroCiclo ?: 'Todos');
        $subtitulo .= '  |  Solo becados: '.($this->soloBecas ? 'Sí' : 'No');
        $subtitulo .= '  |  Generado: '.now()->format('d/m/Y H:i');

        return [
            ['Registro de Calificaciones — Período: '.($this->periodoNombre ?: '—')],
            [$subtitulo],
            ['#', 'Alumno', 'DNI', 'Beca', 'Curso', 'Valoración Curso', 'Calificación Curso', 'Calificación Sistema', 'Estado'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 34,
            'C' => 12,
            'D' => 8,
            'E' => 28,
            'F' => 18,
            'G' => 20,
            'H' => 20,
            'I' => 16,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ultimaColumna = 'I';
                $ultimaFilaDatos = max($this->filaFinDatos, 3);

                // ── Título (fila 1) ──
                $sheet->mergeCells("A1:{$ultimaColumna}1");
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle("A1:{$ultimaColumna}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PROGRAMA_FONDO]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);

                // ── Subtítulo con filtros aplicados (fila 2) ──
                $sheet->mergeCells("A2:{$ultimaColumna}2");
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getStyle("A2:{$ultimaColumna}2")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9.5, 'color' => ['rgb' => '4A5568']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_SUBTITULO_FONDO]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);

                // ── Encabezados de columnas (fila 3) ──
                $sheet->getRowDimension(3)->setRowHeight(28);
                $sheet->getStyle("A3:{$ultimaColumna}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_ENCABEZADO_FONDO]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                if ($ultimaFilaDatos >= 4) {
                    // ── Bordes y alineación base de todas las filas de datos ──
                    $sheet->getStyle("A4:{$ultimaColumna}{$ultimaFilaDatos}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDE]]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'font' => ['size' => 10],
                    ]);
                    $sheet->getStyle("A4:A{$ultimaFilaDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C4:D{$ultimaFilaDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F4:{$ultimaColumna}{$ultimaFilaDatos}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H4:H{$ultimaFilaDatos}")->getNumberFormat()->setFormatCode('0.00');

                    // ── Franjas alternadas por bloque de alumno ──
                    foreach ($this->zebraBlocks as $posicion => [$filaInicio, $filaFin]) {
                        if ($posicion % 2 === 1) {
                            $sheet->getStyle("A{$filaInicio}:{$ultimaColumna}{$filaFin}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_ZEBRA]],
                            ]);
                        }
                    }

                    // ── Fusionar #/Alumno/DNI/Beca por cada alumno con más de un curso ──
                    foreach ($this->alumnoMergeMap as $filaInicio => $filaFin) {
                        foreach (['A', 'B', 'C', 'D'] as $col) {
                            $sheet->mergeCells("{$col}{$filaInicio}:{$col}{$filaFin}");
                        }
                        $sheet->getStyle("A{$filaInicio}:D{$filaFin}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    }

                    // ── Banners de Programa (más oscuro, jerarquía superior) ──
                    foreach ($this->bannerProgramaRows as $fila) {
                        $sheet->mergeCells("A{$fila}:{$ultimaColumna}{$fila}");
                        $sheet->getRowDimension($fila)->setRowHeight(24);
                        $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PROGRAMA_FONDO]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                        ]);
                    }

                    // ── Banners de Ciclo (segundo nivel de jerarquía) ──
                    foreach ($this->bannerCicloRows as $fila) {
                        $sheet->mergeCells("A{$fila}:{$ultimaColumna}{$fila}");
                        $sheet->getRowDimension($fila)->setRowHeight(20);
                        $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_CICLO_FONDO]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 2],
                        ]);
                    }

                    // ── Color según Estado (columna I) y Calificación Sistema (columna H) ──
                    $coloresEstado = [
                        'Aprobado' => self::COLOR_APROBADO,
                        'Desaprobado' => self::COLOR_DESAPROBADO,
                    ];

                    foreach (range(4, $ultimaFilaDatos) as $fila) {
                        $valorEstado = trim((string) $sheet->getCell("I{$fila}")->getValue());

                        if (isset($coloresEstado[$valorEstado])) {
                            $sheet->getStyle("I{$fila}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $coloresEstado[$valorEstado]]],
                            ]);
                            $sheet->getStyle("H{$fila}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $coloresEstado[$valorEstado]]],
                            ]);
                        } elseif ($valorEstado === 'Sin datos') {
                            $sheet->getStyle("H{$fila}:I{$fila}")->getFont()->setItalic(true)->getColor()->setRGB(self::COLOR_SIN_DATOS);
                        }
                    }
                }

                // ── Bloque de resumen del período, al final ──
                $filaResumen = $ultimaFilaDatos + 2;
                $promedio = $this->countCalificacionesNumericas > 0
                    ? round($this->sumaCalificaciones / $this->countCalificacionesNumericas, 2)
                    : null;

                $resumen = [
                    'RESUMEN DEL PERÍODO',
                    ['Alumnos evaluados', $this->totalAlumnos],
                    ['Registros alumno-curso', $this->totalRegistros],
                    ['Aprobados', $this->totalAprobados],
                    ['Desaprobados', $this->totalDesaprobados],
                    ['Sin calificación registrada', $this->totalSinDatos],
                    ['Promedio general', $promedio ?? '—'],
                ];

                $sheet->mergeCells("A{$filaResumen}:{$ultimaColumna}{$filaResumen}");
                $sheet->getStyle("A{$filaResumen}:{$ultimaColumna}{$filaResumen}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PROGRAMA_FONDO]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
                $sheet->setCellValue("A{$filaResumen}", $resumen[0]);

                $filaActual = $filaResumen + 1;
                foreach (array_slice($resumen, 1) as [$etiqueta, $valor]) {
                    $sheet->mergeCells("A{$filaActual}:E{$filaActual}");
                    $sheet->mergeCells("F{$filaActual}:{$ultimaColumna}{$filaActual}");
                    $sheet->setCellValue("A{$filaActual}", $etiqueta);
                    $sheet->setCellValue("F{$filaActual}", $valor);
                    $sheet->getStyle("A{$filaActual}:{$ultimaColumna}{$filaActual}")->applyFromArray([
                        'font' => ['size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_RESUMEN_FONDO]],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDE]]],
                    ]);
                    $sheet->getStyle("A{$filaActual}")->getFont()->setBold(true);
                    $sheet->getStyle("F{$filaActual}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    if ($etiqueta === 'Aprobados') {
                        $sheet->getStyle("F{$filaActual}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_APROBADO);
                    } elseif ($etiqueta === 'Desaprobados') {
                        $sheet->getStyle("F{$filaActual}")->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_DESAPROBADO);
                    }
                    $filaActual++;
                }

                // ── Encabezados siempre visibles al desplazarse ──
                $sheet->freezePane('A4');

                // ── Borde exterior alrededor de toda la tabla de datos ──
                if ($ultimaFilaDatos >= 4) {
                    $sheet->getStyle("A1:{$ultimaColumna}{$ultimaFilaDatos}")->applyFromArray([
                        'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::COLOR_PROGRAMA_FONDO]]],
                    ]);
                }
            },
        ];
    }
}
