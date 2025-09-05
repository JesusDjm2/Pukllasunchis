<?php

namespace App\Exports;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Docente;
use App\Models\Curso;
use App\Models\User;
use App\Models\Competencia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CalificacionesExport implements FromCollection, WithHeadings, WithCustomCsvSettings, WithEvents
{
    protected $docenteId;
    protected $cursoId;
    protected $competencias;
    protected $rowMergeMap = [];
    public function __construct($docenteId, $cursoId, $competenciasSeleccionadas)
    {
        $this->docenteId = $docenteId;
        $this->cursoId = $cursoId;
        $this->competencias = $competenciasSeleccionadas;
    }
    public function collection()
    {
        $curso = Curso::find($this->cursoId);

        $alumnosCiclo = $curso->ciclo->alumnos()->whereHas('user', function ($query) {
            $query->whereDoesntHave('roles', fn($q) => $q->where('name', 'inhabilitado'));
        })->orderBy('apellidos')->get();

        $alumnosRelacionados = $curso->alumnos()->whereHas('user', function ($query) {
            $query->whereDoesntHave('roles', fn($q) => $q->where('name', 'inhabilitado'));
        })->orderBy('apellidos')->get();

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
            $periodos = [
                'Parcial 1' => $alumno->periodos()->where('curso_id', $curso->id)->first(),
                'Parcial 2' => $alumno->periododos()->where('curso_id', $curso->id)->first(),
                'Promedio' => $alumno->periodotres()->where('curso_id', $curso->id)->first(),
            ];

            foreach ($periodos as $etiqueta => $periodo) {
                $row = [
                    $index,
                    "{$alumno->apellidos}, {$alumno->nombres}",
                    $etiqueta,
                ];

                foreach ($this->competencias as $i => $competencia) {
                    $key = 'valoracion_' . ($i + 1);
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
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                foreach ($this->rowMergeMap as $rowStart) {
                    $rowEnd = $rowStart + 2; // rowspan = 3
                    $event->sheet->mergeCells("A{$rowStart}:A{$rowEnd}");
                    $event->sheet->mergeCells("B{$rowStart}:B{$rowEnd}");
                    $event->sheet->getStyle("A{$rowStart}:B{$rowEnd}")->getAlignment()->setVertical('center')->setHorizontal('center');
                }
            }
        ];
    }
    public function headings(): array
    {
        $curso = Curso::find($this->cursoId);
        $docente = Docente::find($this->docenteId);

        return [
            ["Curso: {$curso->nombre} - Docente: {$docente->nombre}"],
            array_merge(
                ['#', 'Alumno', 'Periodo'],
                $this->competencias->pluck('nombre')->toArray(),
                ['Valoración del Curso', 'Calificación del Curso', 'Calificación para el Sistema']
            ),
        ];
    }
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
        ];
    }

}
