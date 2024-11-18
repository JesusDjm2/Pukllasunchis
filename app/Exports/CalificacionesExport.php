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

class CalificacionesExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    protected $docenteId;
    protected $cursoId;
    protected $competencias;

    public function __construct($docenteId, $cursoId, $competenciasSeleccionadas)
    {
        $this->docenteId = $docenteId;
        $this->cursoId = $cursoId;
        $this->competencias = $competenciasSeleccionadas; 
    }

    public function collection()
    {
        $curso = Curso::find($this->cursoId);
        $alumnos = $curso->ciclo->alumnos()
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'inhabilitado');
                });
            })
            ->orderBy('apellidos')
            ->get();
        $valoracionTextos = [
            5 => 'Destacado',
            4 => 'Logrado',
            3 => 'En Proceso',
            2 => 'Inicio',
            1 => 'Previo al Inicio',
            0 => '-' // O cualquier texto que desees para un valor no evaluado
        ];

        $data = [];
        foreach ($alumnos as $index => $alumno) {
            $row = [
                $index + 1, // #
                "{$alumno->apellidos}, {$alumno->nombres}", // Alumno
            ];

            $calificacion = $alumno->calificaciones()->where('curso_id', $this->cursoId)->first();

            foreach ($this->competencias as $index => $competencia) {
                $valoracionKey = 'valoracion_' . ($index + 1); // Clave de la columna
                $valoracion = $calificacion ? $calificacion->$valoracionKey : 0; // Obtén el valor de la valoración

                $row[] = isset($valoracionTextos[$valoracion]) ? $valoracionTextos[$valoracion] : 'N/A';
            }

            // Agregar las calificaciones adicionales
            $row[] = $calificacion ? $calificacion->valoracion_curso : 'N/A';
            $row[] = $calificacion ? $calificacion->calificacion_curso : 'N/A';
            $row[] = $calificacion ? $calificacion->calificacion_sistema : 'N/A';

            $data[] = $row;
        }

        return collect($data);
    }

    /* public function headings(): array
    {
        $headers = [
            '#',
            'Alumno',
        ];

        // Agregar encabezados para las competencias
        foreach ($this->competencias as $competencia) {
            $headers[] = "{$competencia->nombre}"; // Nombre de la competencia
        }

        // Agregar encabezados para calificaciones adicionales
        $headers[] = 'Valoración del Curso';
        $headers[] = 'Calificación del Curso';
        $headers[] = 'Calificación para el Sistema';

        return $headers;
    } */

    public function headings(): array
    {
        $curso = Curso::find($this->cursoId);
        $docente = Docente::find($this->docenteId);

        return [
            // Encabezado adicional con información del curso y docente
            ["Curso: {$curso->nombre} - Docente: {$docente->nombre}"],

            // Encabezados principales
            array_merge(
                ['#', 'Alumno'], // Columnas básicas
                $this->competencias->pluck('nombre')->toArray(), // Competencias
                ['Valoración del Curso', 'Calificación del Curso', 'Calificación para el Sistema'] // Calificaciones adicionales
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
