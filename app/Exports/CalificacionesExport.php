<?php

namespace App\Exports;

use App\Models\Docente;
use App\Models\Curso;
use App\Models\PeriodoActual;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CalificacionesExport implements FromCollection, WithHeadings, WithCustomCsvSettings, WithColumnWidths, WithEvents
{
    // Misma paleta que la leyenda de la pantalla de calificar (Destacado/Logrado/En proceso/Inicio).
    private const COLOR_TITULO_FONDO = '1E3A5F';

    private const COLOR_ENCABEZADO_FONDO = '2E5C8A';

    private const COLOR_BORDE = 'D9E2EC';

    private const COLOR_ZEBRA = 'F4F7FB';

    private const COLORES_VALORACION = [
        'Destacado' => '103B86',
        'Logrado' => '0B954E',
        'En Proceso' => 'C1AC0F',
        'Inicio' => 'DC3545',
        'Previo al Inicio' => 'DC3545',
    ];

    private const COLOR_INHABILITADO = 'DC3545';

    private const COLOR_NO_MATRICULADO = '997404';

    protected $docenteId;

    protected $cursoId;

    protected $competencias;

    protected $rowMergeMap = [];

    protected $estadoPorFila = [];

    public function __construct($docenteId, $cursoId, $competenciasSeleccionadas)
    {
        $this->docenteId = $docenteId;
        $this->cursoId = $cursoId;
        $this->competencias = $competenciasSeleccionadas;
    }

    public function collection()
    {
        $curso = Curso::find($this->cursoId);
        $periodoActual = PeriodoActual::where('actual', true)->first();

        $alumnosCiclo = $curso->ciclo->alumnos()->orderBy('apellidos')->get();

        $alumnosRelacionados = $curso->alumnos()->orderBy('apellidos')->get();

        $alumnos = $alumnosCiclo->merge($alumnosRelacionados)->unique('id')->values();

        $valoracionTextos = [
            1 => 'Previo al Inicio',
            2 => 'Inicio',
            3 => 'En Proceso',
            4 => 'Logrado',
            5 => 'Destacado',
            0 => '-',
            null => '-',
        ];

        $data = [];
        $index = 1;
        $rowNumber = 3;

        foreach ($alumnos as $alumno) {
            $this->rowMergeMap[] = $rowNumber;

            $esInhabilitado = $alumno->user && $alumno->user->hasRole('inhabilitado');
            $noMatriculado = $periodoActual && ! $esInhabilitado && ! $alumno->matriculaEnPeriodo($periodoActual->id);

            $estado = $esInhabilitado ? ' — INHABILITADO' : ($noMatriculado ? ' — NO MATRICULADO' : '');
            $this->estadoPorFila[$rowNumber] = $esInhabilitado ? 'inhabilitado' : ($noMatriculado ? 'no_matriculado' : null);

            $periodos = [
                'Parcial 1' => $alumno->periodos()->where('curso_id', $curso->id)->first(),
                'Parcial 2' => $alumno->periododos()->where('curso_id', $curso->id)->first(),
                'Promedio' => $alumno->periodotres()->where('curso_id', $curso->id)->first(),
            ];

            foreach ($periodos as $etiqueta => $periodo) {
                $row = [
                    $index,
                    "{$alumno->apellidos}, {$alumno->nombres}{$estado}",
                    $etiqueta,
                ];

                foreach ($this->competencias as $i => $competencia) {
                    $key = 'valoracion_'.($i + 1);
                    $valor = $periodo->$key ?? null;
                    $row[] = $valoracionTextos[$valor] ?? '-';
                }

                $row[] = $periodo->valoracion_curso ?? '-';
                $row[] = $periodo->calificacion_curso ?? '-';
                $row[] = $periodo->calificacion_sistema ?? '-';

                $data[] = $row;
                $rowNumber++;
            }

            $index++;
        }

        return collect($data);
    }

    public function headings(): array
    {
        $curso = Curso::find($this->cursoId);
        $docente = Docente::find($this->docenteId);

        return [
            ["Curso: {$curso->nombre}  |  Docente: {$docente->nombre}  |  Programa: ".
                ($curso->ciclo->programa->nombre ?? '-')."  |  Ciclo: ".($curso->ciclo->nombre ?? '-')],
            array_merge(
                ['#', 'Alumno', 'Periodo'],
                $this->competencias->pluck('nombre')->toArray(),
                ['Valoración del Curso', 'Calificación del Curso', 'Calificación para el Sistema']
            ),
        ];
    }

    public function columnWidths(): array
    {
        $anchos = ['A' => 5, 'B' => 32, 'C' => 12];
        $col = 4; // D en adelante: competencias
        foreach ($this->competencias as $competencia) {
            $anchos[Coordinate::stringFromColumnIndex($col)] = 24;
            $col++;
        }
        $anchos[Coordinate::stringFromColumnIndex($col++)] = 16; // Valoración del Curso
        $anchos[Coordinate::stringFromColumnIndex($col++)] = 20; // Calificación del Curso
        $anchos[Coordinate::stringFromColumnIndex($col)] = 22; // Calificación para el Sistema

        return $anchos;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalColumnas = 3 + $this->competencias->count() + 3;
                $ultimaColumna = Coordinate::stringFromColumnIndex($totalColumnas);
                $ultimaFila = $sheet->getHighestRow();
                $primeraColCompetencia = 4;

                // ── Título (fila 1) ──
                $sheet->mergeCells("A1:{$ultimaColumna}1");
                $sheet->getRowDimension(1)->setRowHeight(26);
                $sheet->getStyle("A1:{$ultimaColumna}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_TITULO_FONDO]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);

                // ── Encabezados de columnas (fila 2) ──
                $sheet->getRowDimension(2)->setRowHeight(32);
                $sheet->getStyle("A2:{$ultimaColumna}2")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_ENCABEZADO_FONDO]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                if ($ultimaFila >= 3) {
                    // ── Bordes y alineación base de todas las celdas de datos ──
                    $sheet->getStyle("A3:{$ultimaColumna}{$ultimaFila}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDE]]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'font' => ['size' => 10],
                    ]);

                    // Centrado: #, Periodo, competencias y las 3 columnas finales. Alumno queda alineado a la izquierda.
                    $sheet->getStyle("A3:A{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C3:{$ultimaColumna}{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B3:B{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // ── Combinar #/Alumno por cada alumno (3 filas: Parcial 1, Parcial 2, Promedio) ──
                    foreach ($this->rowMergeMap as $filaInicio) {
                        $filaFin = $filaInicio + 2;
                        $sheet->mergeCells("A{$filaInicio}:A{$filaFin}");
                        $sheet->mergeCells("B{$filaInicio}:B{$filaFin}");
                        $sheet->getStyle("A{$filaInicio}:B{$filaFin}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("A{$filaInicio}:A{$filaFin}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // ── Color de la celda Alumno cuando está inhabilitado o no matriculado ──
                    foreach ($this->rowMergeMap as $filaInicio) {
                        $estadoTipo = $this->estadoPorFila[$filaInicio] ?? null;
                        if ($estadoTipo === 'inhabilitado') {
                            $sheet->getStyle("B{$filaInicio}")->getFont()->getColor()->setRGB(self::COLOR_INHABILITADO);
                        } elseif ($estadoTipo === 'no_matriculado') {
                            $sheet->getStyle("B{$filaInicio}")->getFont()->getColor()->setRGB(self::COLOR_NO_MATRICULADO);
                        }
                    }

                    // ── Franjas alternadas por alumno, para que sea fácil seguir cada fila con la vista ──
                    foreach ($this->rowMergeMap as $posicion => $filaInicio) {
                        if ($posicion % 2 === 1) {
                            $filaFin = $filaInicio + 2;
                            $sheet->getStyle("A{$filaInicio}:{$ultimaColumna}{$filaFin}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_ZEBRA]],
                            ]);
                        }
                    }

                    // ── Color de texto según la valoración (mismos colores que la leyenda del panel de calificar) ──
                    $ultimaColCompetencia = $primeraColCompetencia + $this->competencias->count() - 1;
                    $colCalificacionCurso = $primeraColCompetencia + $this->competencias->count() + 1;

                    foreach (range(3, $ultimaFila) as $fila) {
                        foreach (array_merge(range($primeraColCompetencia, $ultimaColCompetencia), [$colCalificacionCurso]) as $col) {
                            $colLetra = Coordinate::stringFromColumnIndex($col);
                            $valor = trim((string) $sheet->getCell("{$colLetra}{$fila}")->getValue());

                            if (isset(self::COLORES_VALORACION[$valor])) {
                                $sheet->getStyle("{$colLetra}{$fila}")->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => self::COLORES_VALORACION[$valor]]],
                                ]);
                            } elseif ($valor === '-') {
                                $sheet->getStyle("{$colLetra}{$fila}")->getFont()->setItalic(true)->getColor()->setRGB('9CA3AF');
                            }
                        }
                    }
                }

                // ── Encabezados siempre visibles al desplazarse ──
                $sheet->freezePane('A3');

                // ── Borde exterior alrededor de toda la tabla ──
                $sheet->getStyle("A1:{$ultimaColumna}{$ultimaFila}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::COLOR_TITULO_FONDO]]],
                ]);
            },
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
        ];
    }
}
