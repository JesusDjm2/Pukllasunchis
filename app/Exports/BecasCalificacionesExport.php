<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BecasCalificacionesExport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    // Misma paleta que el resto de reportes de calificaciones.
    private const COLOR_ENCABEZADO_FONDO = '2E5C8A';

    private const COLOR_BORDE = 'D9E2EC';

    private const COLOR_ZEBRA = 'F4F7FB';

    private const COLORES_VALORACION = [
        'destacado' => '103B86',
        'logrado' => '0B954E',
        'en proceso' => 'C1AC0F',
        'inicio' => 'DC3545',
        'previo al inicio' => 'DC3545',
    ];

    protected $filas;

    public function __construct($filas)
    {
        $this->filas = $filas;
    }

    public function collection()
    {
        return $this->filas
            ->map(function ($fila) {
                $alumno = $fila['alumno'];
                $curso = $fila['curso'];
                $p1 = $fila['parcial1'];
                $p2 = $fila['parcial2'];
                $p3 = $fila['desempeno'];

                return [
                    'Alumno' => $alumno->apellidos.' '.$alumno->nombres,
                    'DNI' => $alumno->dni,
                    'Programa' => optional($alumno->programa)->nombre,
                    'Curso' => $curso->nombre,
                    'Ciclo' => optional($curso->ciclo)->nombre,
                    'OrdenCiclo' => $curso->ciclo?->ordenCiclo() ?? 999, // 👈 campo oculto para ordenar
                    'Parcial 1 - Valoración' => optional($p1)->valoracion_curso ?? 'N/A',
                    'Parcial 1 - Calificación Curso' => optional($p1)->calificacion_curso ?? 'N/A',
                    'Parcial 1 - Calificación Sistema' => optional($p1)->calificacion_sistema ?? 'N/A',
                    'Parcial 2 - Valoración' => optional($p2)->valoracion_curso ?? 'N/A',
                    'Parcial 2 - Calificación Curso' => optional($p2)->calificacion_curso ?? 'N/A',
                    'Parcial 2 - Calificación Sistema' => optional($p2)->calificacion_sistema ?? 'N/A',
                    'Desempeño - Valoración' => optional($p3)->valoracion_curso ?? 'N/A',
                    'Desempeño - Calificación Curso' => optional($p3)->calificacion_curso ?? 'N/A',
                    'Desempeño - Calificación Sistema' => optional($p3)->calificacion_sistema ?? 'N/A',
                ];
            })
            ->sortBy([
                ['OrdenCiclo', 'asc'],
                ['Alumno', 'asc'],
            ])
            ->map(function ($row) {
                // 🔴 Remover la columna auxiliar antes de exportar
                unset($row['OrdenCiclo']);

                return $row;
            });
    }

    public function headings(): array
    {
        return [
            'Alumno',
            'DNI',
            'Programa',
            'Curso',
            'Ciclo',
            'Parcial 1 - Valoración',
            'Parcial 1 - Calificación Curso',
            'Parcial 1 - Calificación Sistema',
            'Parcial 2 - Valoración',
            'Parcial 2 - Calificación Curso',
            'Parcial 2 - Calificación Sistema',
            'Desempeño - Valoración',
            'Desempeño - Calificación Curso',
            'Desempeño - Calificación Sistema',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // Alumno
            'B' => 12, // DNI
            'C' => 22, // Programa
            'D' => 26, // Curso
            'E' => 10, // Ciclo
            'F' => 14, 'G' => 20, 'H' => 20, // Parcial 1
            'I' => 14, 'J' => 20, 'K' => 20, // Parcial 2
            'L' => 14, 'M' => 20, 'N' => 20, // Desempeño
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ultimaColumna = 'N';
                $ultimaFila = $sheet->getHighestRow();

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle("A1:{$ultimaColumna}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_ENCABEZADO_FONDO]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->freezePane('A2');

                if ($ultimaFila < 2) {
                    return;
                }

                $sheet->getStyle("A2:{$ultimaColumna}{$ultimaFila}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDE]]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'font' => ['size' => 10],
                ]);
                $sheet->getStyle("B2:B{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E2:{$ultimaColumna}{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                    if ($fila % 2 === 1) {
                        $sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_ZEBRA]],
                        ]);
                    }

                    // Columnas de "Calificación Curso" (G, K) y "Valoración" con texto: resaltar según su significado.
                    foreach (['F', 'G', 'I', 'J', 'L', 'M'] as $colLetra) {
                        $valor = strtolower(trim((string) $sheet->getCell("{$colLetra}{$fila}")->getValue()));

                        if (isset(self::COLORES_VALORACION[$valor])) {
                            $sheet->getStyle("{$colLetra}{$fila}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => self::COLORES_VALORACION[$valor]]],
                            ]);
                        } elseif ($valor === 'n/a') {
                            $sheet->getStyle("{$colLetra}{$fila}")->getFont()->setItalic(true)->getColor()->setRGB('9CA3AF');
                        }
                    }
                }

                $sheet->getStyle("A1:{$ultimaColumna}{$ultimaFila}")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::COLOR_ENCABEZADO_FONDO]]],
                ]);
            },
        ];
    }

}
