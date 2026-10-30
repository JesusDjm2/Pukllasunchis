<?php

namespace App\Exports\RicardoPalma;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlantillaActaRpExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    public function headings(): array
    {
        return [
            'PERIODO',
            'PROGRAMA',
            'SEMESTRE',
            'NRO_MATRICULA',
            'DNI',
            'APELLIDOS',
            'NOMBRES',
            'SEXO',
            'CURSO',
            'CREDITOS',
            'NOTA',
            'OBSERVACION',
            'NRO_ACTA',
            'TURNO',
            'SECCION',
            'FECHA_VISACION',
            'OBS_ALUMNO',
        ];
    }

    public function array(): array
    {
        // Filas de ejemplo basadas en los escaneos reales de la IESP Ricardo Palma
        return [
            [
                '1996-II',
                'INICIAL',
                4,
                '351',
                '',
                'ACURIO SALAZAR',
                'Yovana',
                'F',
                'EDUCACION FISICA II',
                1,
                15,
                'APROBADO',
                '002906',
                'TARDE',
                'UNICA',
                '24/01/1997',
                '',
            ],
            [
                '1996-II',
                'INICIAL',
                4,
                '351',
                '',
                'ACURIO SALAZAR',
                'Yovana',
                'F',
                'GEOG. DEL PERU Y MUNDO I',
                3,
                16,
                'APROBADO',
                '002906',
                'TARDE',
                'UNICA',
                '24/01/1997',
                '',
            ],
            [
                '2006-I',
                'SEC-COM',
                1,
                '08-06',
                '',
                'FLORES CURI',
                'Rosa',
                'F',
                'COMUNICACIÓN INTEGRAL I',
                10,
                null,
                'RETIRADO',
                '',
                'TARDE',
                'UNICA',
                '',
                'RETIRADA R.D. N° (número de la resolución)',
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, // PERIODO
            'B' => 16, // PROGRAMA
            'C' => 12, // SEMESTRE
            'D' => 18, // NRO_MATRICULA
            'E' => 14, // DNI
            'F' => 26, // APELLIDOS
            'G' => 22, // NOMBRES
            'H' => 10, // SEXO
            'I' => 32, // CURSO
            'J' => 12, // CREDITOS
            'K' => 10, // NOTA
            'L' => 16, // OBSERVACION
            'M' => 14, // NRO_ACTA
            'N' => 12, // TURNO
            'O' => 12, // SECCION
            'P' => 16, // FECHA_VISACION
            'Q' => 34, // OBS_ALUMNO
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
