<?php

namespace App\Exports;

use App\Services\EstadoMatricula;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exportación del directorio de estudiantes (/Administrador): exporta exactamente lo que
 * se ve (o lo seleccionado), con uno de estos conjuntos de columnas:
 *  - basico     identificación y ubicación académica
 *  - matricula  + estado de matrícula del periodo, voucher y envío de ficha
 *  - contacto   correo y teléfono
 * La "ficha completa" (socioeconómica) usa AlumnosFidExport / AlumnosPpdExport.
 */
class DirectorioEstudiantesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, ShouldAutoSize
{
    public const PRESETS = [
        'basico' => 'Básico',
        'matricula' => 'Matrícula',
        'contacto' => 'Contacto',
        'ficha' => 'Ficha completa',
    ];

    public function __construct(
        private readonly Collection $usuarios,
        private readonly string $preset,
        private readonly ?string $periodo = null,
    ) {}

    public function collection(): Collection
    {
        return $this->usuarios;
    }

    public function headings(): array
    {
        $base = ['Apellidos', 'Nombres', 'DNI', 'Programa', 'Ciclo'];

        return match ($this->preset) {
            'matricula' => [...$base, 'Periodo', 'Estado de matrícula', 'N° de voucher', 'Voucher verificado', 'Ficha enviada', 'Fecha de matrícula'],
            'contacto' => [...$base, 'Correo', 'Teléfono', 'Perfil'],
            default => [...$base, 'N° / Código', 'Condición', 'Perfil', 'Beca'],
        };
    }

    public function map($u): array
    {
        $fila = [$u->apellidos, $u->name, $u->dni, $u->programa->nombre ?? '', $u->ciclo->nombre ?? ''];
        $m = EstadoMatricula::de($u);

        return match ($this->preset) {
            'matricula' => [...$fila,
                $this->periodo,
                $m->etiqueta,
                $m->voucher ?? '',
                $m->estado === 'matriculado' ? ($m->voucher_verificado ? 'Sí' : 'No') : '',
                $m->ficha_enviada_at ? $m->ficha_enviada_at->format('d/m/Y') : ($m->estado === 'matriculado' ? 'No' : ''),
                $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') : '',
            ],
            'contacto' => [...$fila, $u->email, $u->telefono, $u->perfil],
            default => [...$fila, $m->ficha?->numero ?? '', $u->condicion, $u->perfil, $u->beca == 1 ? 'Sí' : 'No'],
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $e->sheet->getDelegate()->freezePane('A2');
                $e->sheet->getDelegate()->setAutoFilter($e->sheet->getDelegate()->calculateWorksheetDimension());
            },
        ];
    }
}
