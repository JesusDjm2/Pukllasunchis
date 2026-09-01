<?php

namespace App\Exports;

use App\Models\Alumno;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumnosFidExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
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

    /** Color de relleno por fila según estado de matrícula (mismo criterio que la vista). */
    private const COLOR_MATRICULADO = 'D9F2E3';
    private const COLOR_NO_MATRICULADO = 'FBE2E1';

    /** @var array<int, string> */
    private array $remainingColumns;

    public function __construct(
        private readonly Collection $alumnos,
        private readonly ?int $periodoFiltroId = null,
        private readonly bool $soloImportantes = false,
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
        if ($this->soloImportantes) {
            $h = ['Programa', 'Ciclo', 'Nombre', 'DNI', 'Número', 'Número de referencia'];
            if ($this->periodoFiltroId) {
                $h[] = 'Estado de matrícula';
            }
            $h[] = 'Email';

            return $h;
        }

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

        $matricula = $this->periodoFiltroId ? ['Estado de matrícula', 'Fecha de matrícula'] : [];

        return array_merge($leading, $middle, $matricula, [
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
        $ap = trim((string) ($alumno->apellidos ?? ''));
        $nom = trim((string) ($alumno->nombres ?? ''));
        $nombre = implode(', ', array_filter([$ap, $nom]));

        if ($this->soloImportantes) {
            $row = [
                optional($alumno->programa)->nombre ?? '',
                optional($alumno->ciclo)->nombre ?? '',
                $nombre,
                $this->cellValue($alumno->dni),
                $this->cellValue($alumno->numero),
                $this->cellValue($alumno->numero_referencia),
            ];

            if ($this->periodoFiltroId) {
                $row[] = $alumno->matriculas->isNotEmpty() ? 'Matriculado' : 'No matriculado';
            }

            $row[] = $this->cellValue($alumno->email);

            return $row;
        }

        $fechaNac = $alumno->fechaNacimientoResueltaFormateada('d/m/Y');

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

        if ($this->periodoFiltroId) {
            $matricula = $alumno->matriculas->first();
            $row[] = $matricula ? 'Matriculado' : 'No matriculado';
            $row[] = $matricula ? ($matricula->fecha_completado?->format('d/m/Y') ?? $matricula->created_at?->format('d/m/Y')) : '';
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

    /**
     * Resalta cada fila según el estado de matrícula del alumno, para que los
     * tutores puedan identificar a simple vista a quién le falta matricularse.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if (! $this->periodoFiltroId) {
                    return;
                }

                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $rowIndex = 2;

                foreach ($this->alumnos as $alumno) {
                    $matriculado = $alumno->matriculas->isNotEmpty();
                    $color = $matriculado ? self::COLOR_MATRICULADO : self::COLOR_NO_MATRICULADO;

                    $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);

                    $rowIndex++;
                }
            },
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
