<?php

namespace App\Exports;

use App\Models\Curso;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CalificacionesPPDExport implements FromCollection, WithHeadings, WithEvents
{
    protected $docenteId;
    protected $cursoId;
    protected $competenciasSeleccionadas;
    protected $alumnos;

    public function __construct($docenteId, $cursoId, $competenciasSeleccionadas, $alumnos)
    {
        $this->docenteId = $docenteId;
        $this->cursoId = $cursoId;
        $this->competenciasSeleccionadas = $competenciasSeleccionadas;
        $this->alumnos = $alumnos;
    }

    public function collection()
    {
        $curso = Curso::with(['competencias', 'calificacionesppd'])->findOrFail($this->cursoId);
        $data = collect();
        foreach ($this->alumnos as $index => $alumno) {
            $calif = $curso->calificacionesppd->where('ppd_id', $alumno->id)->first();

            $row = [
                '#' => $index + 1,
                'Alumno' => $alumno->apellidos . ' ' . $alumno->nombres,
            ];

            // Solo el promedio final por competencia
            for ($c = 1; $c <= 3; $c++) {
                $pp = $calif?->{"pp_c{$c}_4"};
                $pf = $calif?->{"pf_c{$c}_3"};

                $val = (is_numeric($pp) && is_numeric($pf))
                    ? round($pp * 0.4 + $pf * 0.6)
                    : '-';

                $row["Comp. {$c} (Prom.)"] = $val;
            }

            $row['Nivel de Desempeño'] = $calif?->nivel_desempeno ?? 'Sin calificar';
            $row['Calificación Curso'] = $calif?->calificacion_curso ?? 'Sin calificar';
            $row['Calificación Sistema'] = $calif?->calificacion_sistema ?? 'Sin calificar';

            $data->push($row);
        }

        return $data;
    }




    public function headings(): array
    {
        $curso = Curso::with('competencias')->findOrFail($this->cursoId);
        $competencias = $curso->competencias
            ->sortBy(function ($comp) {
                return intval(preg_replace('/\D/', '', $comp->nombre));
            })
            ->values();

        // Fila 1
        $headings1 = ['#', 'Alumno'];
        foreach ($competencias as $compIndex => $competencia) {
            preg_match('/\d+/', $competencia->nombre, $matches);
            $numero = $matches[0] ?? $compIndex + 1;
            $headings1[] = "Comp. {$numero}";
        }
        $headings1[] = 'Nivel de Desempeño';
        $headings1[] = 'Calificación Curso';
        $headings1[] = 'Calificación Sistema';

        return $headings1;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Encabezados en negrita
                $event->sheet->getStyle('A1:Z1')->getFont()->setBold(true);

                // Ajustar ancho automático
                foreach (range('A', $event->sheet->getHighestDataColumn()) as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}
