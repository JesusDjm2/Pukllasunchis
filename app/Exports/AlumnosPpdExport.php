<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumnosPpdExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly Collection $alumnos,
    ) {}

    public function collection(): Collection
    {
        return $this->alumnos;
    }

    public function headings(): array
    {
        return [
            'Programa',
            'Ciclo',
            'Apellidos',
            'Nombres',
            'DNI',
            'Email',
            'Estado civil',
            'Fecha nacimiento',
            'Domicilio',
            'Lengua 1',
            'Lengua 2',
            'Número (matrícula)',
            'Número de referencia (matrícula)',
        ];
    }

    /**
     * @param  User  $alumno
     */
    public function map($alumno): array
    {
        $ppd = $alumno->alumnoB;

        return [
            optional($alumno->programa)->nombre ?? optional(optional($alumno->ciclo)->programa)->nombre ?? '',
            optional($alumno->ciclo)->nombre ?? '',
            (string) ($alumno->apellidos ?? ''),
            (string) ($alumno->name ?? ''),
            (string) ($alumno->dni ?? ''),
            (string) ($alumno->email ?? ''),
            $ppd ? (string) ($ppd->estado_civil ?? '') : (string) ($alumno->estadoCivil ?? ''),
            $alumno->fecha_nacimiento ? (string) $alumno->fecha_nacimiento : '',
            $ppd ? (string) ($ppd->direccion ?? '') : (string) ($alumno->domicilio ?? ''),
            $ppd ? (string) ($ppd->lengua_1 ?? '') : (string) ($alumno->lengua_1 ?? ''),
            $ppd ? (string) ($ppd->lengua_2 ?? '') : (string) ($alumno->lengua_2 ?? ''),
            $ppd ? (string) ($ppd->numero ?? '') : '',
            $ppd ? (string) ($ppd->numero_referencia ?? '') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A6F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }
}
