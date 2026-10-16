<?php

namespace App\Exports;

use App\Http\Controllers\Titulacion\TitulacionController;
use App\Services\Titulacion\Expediente;
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
 * Lista de Titulación tal como se ve con los filtros aplicados (todas las páginas):
 * registrados, pendientes de importación y activos que pueden iniciar. Las filas son
 * los objetos que arma TitulacionController@index.
 */
class TitulacionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(private readonly Collection $filas) {}

    public function collection(): Collection
    {
        return $this->filas;
    }

    public function headings(): array
    {
        return ['N° expediente', 'Apellidos y nombres', 'DNI', 'Modalidad', 'Programa', 'Promoción / ciclo',
            'Estado', 'Siguiente paso', 'Vencimiento del PTI', 'Asesor(a) del PTI', 'Correo'];
    }

    public function map($item): array
    {
        $vigencia = $item->vigencia ?? null;
        $pti = ($item->seguimiento ?? null)?->pti;

        return [
            filled($item->expediente ?? null) ? Expediente::mostrar($item->expediente) : '',
            $item->nombre,
            $item->dni ?? '',
            $item->modalidad ?? '',
            $item->programa ?? '',
            TitulacionController::etiquetaAnio($item->anio ?? null) ?? '',
            $item->estado_label,
            $item->siguiente ?? '',
            $vigencia?->conocida() ? $vigencia->vencimiento->format('d/m/Y').($vigencia->vencido() ? ' (vencido)' : '') : '',
            $pti?->asesorNombre() ?? '',
            ($item->user ?? null)?->email ?? '',
        ];
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
