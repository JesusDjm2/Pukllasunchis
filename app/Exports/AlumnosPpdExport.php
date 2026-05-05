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
            'Teléfono',
            'Género',
            'Estado civil',
            'Fecha nacimiento',
            'Domicilio',
            'Lengua 1',
            'Lengua 2',
            'Beca',
            'Condición',
            'Completó matrícula',
            'Número (matrícula)',
            'Roles',
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
            (string) ($alumno->telefono ?? ''),
            $this->generoLabel($alumno->genero),
            (string) ($alumno->estadoCivil ?? ''),
            $alumno->fecha_nacimiento ? (string) $alumno->fecha_nacimiento : '',
            (string) ($alumno->domicilio ?? ''),
            (string) ($alumno->lengua_1 ?? ''),
            (string) ($alumno->lengua_2 ?? ''),
            $alumno->beca ? 'Sí' : 'No',
            (string) ($alumno->condicion ?? ''),
            $ppd ? 'Sí' : 'No',
            $ppd ? (string) ($ppd->numero ?? '') : '',
            $alumno->roles->pluck('name')->implode(', '),
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

    private function generoLabel(mixed $genero): string
    {
        if ($genero === null || $genero === '') {
            return '';
        }

        return (int) $genero === 1 ? 'Masculino' : 'Femenino';
    }
}
