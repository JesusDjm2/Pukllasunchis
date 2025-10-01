<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RegistrosExport implements FromCollection, WithCustomCsvSettings, WithHeadings
{
    protected $ciclos;

    protected $filas;

    public function __construct($ciclos, $filas)
    {
        $this->ciclos = $ciclos;
        $this->filas = $filas;
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->filas as $entry) {
            $alumno = $entry['alumno'];
            $cursos = $entry['cursos'];
            $periodosAlumno = $entry['periodos'];

            foreach ($cursos as $curso) {
                $periodo = $periodosAlumno->firstWhere('curso_id', $curso->id);

                $rows->push([
                    'Alumno' => $alumno->apellidos.' '.$alumno->nombres,
                    'DNI' => $alumno->dni,
                    'Programa' => optional($alumno->programa)->nombre,
                    'Curso' => $curso->nombre,
                    'Ciclo' => optional($curso->ciclo)->nombre,
                    'OrdenCiclo' => $curso->ciclo?->ordenCiclo() ?? 999, // 👈 campo oculto para ordenar
                    'Valoración Curso' => $periodo->valoracion_curso ?? 'N/A',
                    'Calificación Curso' => $periodo->calificacion_curso ?? 'N/A',
                    'Calificación Sistema' => $periodo->calificacion_sistema ?? 'N/A',
                ]);
            }
        }
        // 👇 Ordenar por ciclo primero, luego por alumno
        return $rows->sortBy([
            ['OrdenCiclo', 'asc'],
            ['Alumno', 'asc'],
        ])->map(function ($row) {
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
            'Valoración Curso',
            'Calificación Curso',
            'Calificación Sistema',
        ];
    }
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'enclosure' => '"',
            'line_ending' => "\n",
            'use_bom' => true,
        ];
    }
}
