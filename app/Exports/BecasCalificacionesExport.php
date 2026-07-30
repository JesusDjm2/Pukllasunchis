<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BecasCalificacionesExport implements FromCollection, WithCustomCsvSettings, WithHeadings
{
    protected $filas;

    public function __construct($filas)
    {
        $this->filas = $filas;
    }

    public function collection()
    {
        return $this->filas
            ->map(function ($fila) {
                $alumno = $fila['alumno'];
                $curso = $fila['curso'];
                $p1 = $fila['parcial1'];
                $p2 = $fila['parcial2'];
                $p3 = $fila['desempeno'];

                return [
                    'Alumno' => $alumno->apellidos.' '.$alumno->nombres,
                    'DNI' => $alumno->dni,
                    'Programa' => optional($alumno->programa)->nombre,
                    'Curso' => $curso->nombre,
                    'Ciclo' => optional($curso->ciclo)->nombre,
                    'OrdenCiclo' => $curso->ciclo?->ordenCiclo() ?? 999, // 👈 campo oculto para ordenar
                    'Parcial 1 - Valoración' => optional($p1)->valoracion_curso ?? 'N/A',
                    'Parcial 1 - Calificación Curso' => optional($p1)->calificacion_curso ?? 'N/A',
                    'Parcial 1 - Calificación Sistema' => optional($p1)->calificacion_sistema ?? 'N/A',
                    'Parcial 2 - Valoración' => optional($p2)->valoracion_curso ?? 'N/A',
                    'Parcial 2 - Calificación Curso' => optional($p2)->calificacion_curso ?? 'N/A',
                    'Parcial 2 - Calificación Sistema' => optional($p2)->calificacion_sistema ?? 'N/A',
                    'Desempeño - Valoración' => optional($p3)->valoracion_curso ?? 'N/A',
                    'Desempeño - Calificación Curso' => optional($p3)->calificacion_curso ?? 'N/A',
                    'Desempeño - Calificación Sistema' => optional($p3)->calificacion_sistema ?? 'N/A',
                ];
            })
            ->sortBy([
                ['OrdenCiclo', 'asc'],
                ['Alumno', 'asc'],
            ])
            ->map(function ($row) {
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
            'Parcial 1 - Valoración',
            'Parcial 1 - Calificación Curso',
            'Parcial 1 - Calificación Sistema',
            'Parcial 2 - Valoración',
            'Parcial 2 - Calificación Curso',
            'Parcial 2 - Calificación Sistema',
            'Desempeño - Valoración',
            'Desempeño - Calificación Curso',
            'Desempeño - Calificación Sistema',
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
