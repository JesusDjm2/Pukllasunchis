<?php

namespace App\Exports;

use App\Models\Alumno;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumnosFidExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /** Columnas de `alumnos` ya cubiertas al inicio del Excel (orden fijo). */
    private const LEADING_DB_COLUMNS = [
        'programa_id',
        'ciclo_id',
        'nombres',
        'apellidos',
        'dni',
        'email',
        'numero',
        'numero_referencia',
        'fecha_nacimiento',
    ];

    /** @var array<int, string> */
    private array $remainingColumns;

    public function __construct(
        private readonly Collection $alumnos,
    ) {
        $all = Schema::getColumnListing((new Alumno)->getTable());
        $skip = array_flip(self::LEADING_DB_COLUMNS);
        $this->remainingColumns = array_values(array_filter($all, fn (string $c) => ! isset($skip[$c])));
    }

    public function collection(): Collection
    {
        return $this->alumnos;
    }

    public function headings(): array
    {
        $leading = [
            'Programa',
            'Ciclo',
            'Nombre',
            'DNI',
            'Email',
            'Número',
            'Número de referencia',
            'Fecha de nacimiento',
        ];

        $middle = array_map(fn (string $c) => $this->headingForColumn($c), $this->remainingColumns);

        return array_merge($leading, $middle, [
            'Usuario ID',
            'Correo usuario',
            'Roles usuario',
        ]);
    }

    /**
     * @param  Alumno  $alumno
     */
    public function map($alumno): array
    {
        $fechaNac = $alumno->fechaNacimientoResueltaFormateada('d/m/Y');

        $ap = trim((string) ($alumno->apellidos ?? ''));
        $nom = trim((string) ($alumno->nombres ?? ''));
        $nombre = implode(', ', array_filter([$ap, $nom]));

        $row = [
            optional($alumno->programa)->nombre ?? '',
            optional($alumno->ciclo)->nombre ?? '',
            $nombre,
            $this->cellValue($alumno->dni),
            $this->cellValue($alumno->email),
            $this->cellValue($alumno->numero),
            $this->cellValue($alumno->numero_referencia),
            $fechaNac,
        ];

        foreach ($this->remainingColumns as $col) {
            $row[] = $this->cellValue($alumno->getAttribute($col));
        }

        $user = $alumno->user;
        $roles = $user ? $user->roles->pluck('name')->implode(', ') : '';

        $row[] = $user?->id;
        $row[] = $user?->email;
        $row[] = $roles;

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E6F41'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    private function headingForColumn(string $column): string
    {
        return match ($column) {
            default => Str::title(str_replace('_', ' ', $column)),
        };
    }

    private function cellValue(mixed $value): mixed
    {
        if ($value === null) {
            return '';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }
}
