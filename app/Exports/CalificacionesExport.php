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

/* class CalificacionesExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    protected $docenteId;
    protected $cursoId;
    protected $competenciasSeleccionadas;

    public function __construct($docenteId, $cursoId, $competenciasSeleccionadas)
    {
        $this->docenteId = $docenteId;
        $this->cursoId = $cursoId;
        $this->competenciasSeleccionadas = $competenciasSeleccionadas;
    }

    public function collection()
    {
        // Obtener el curso y los alumnos válidos
        $curso = Curso::find($this->cursoId);
        $alumnos = $curso->ciclo->alumnos()
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'inhabilitado');
                });
            })
            ->orderBy('apellidos')
            ->get();

        $data = [];
        foreach ($alumnos as $index => $alumno) {
            // Prepara la fila para cada alumno
            $row = [
                $index + 1, // Número
                "{$alumno->apellidos}, {$alumno->nombres}", // Nombre del alumno
            ];

            // Iterar sobre las competencias seleccionadas y obtener las valoraciones
            $calificacion = $alumno->calificaciones()->where('curso_id', $this->cursoId)->first();
            foreach ($this->competenciasSeleccionadas as $competencia) {
                // Obtener la valoración correspondiente a cada competencia
                $row[] = $calificacion ? $calificacion->{"valoracion_" . $competencia->id} : 'N/A';
            }

            // Agregar las calificaciones del curso
            $row[] = $calificacion ? $calificacion->valoracion_curso : 'N/A';
            $row[] = $calificacion ? $calificacion->calificacion_curso : 'N/A';
            $row[] = $calificacion ? $calificacion->calificacion_sistema : 'N/A';

            $data[] = $row;
        }

        return collect($data);
    }

    public function headings(): array
    {
        // Encabezados de las columnas
        $headers = [
            '#',
            'Alumno',
        ];

        // Encabezados para cada competencia seleccionada
        foreach ($this->competenciasSeleccionadas as $competencia) {
            $headers[] = $competencia->nombre;
        }

        // Encabezados para las calificaciones
        $headers[] = 'Valoración del Curso';
        $headers[] = 'Calificación del Curso';
        $headers[] = 'Calificación para el Sistema';

        return $headers;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';', // Delimitador personalizado
        ];
    }
} */

class CalificacionesExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    protected $docenteId;
    protected $cursoId;
    protected $competencias;

    public function __construct($docenteId, $cursoId, $competenciasSeleccionadas)
    {
        $this->docenteId = $docenteId;
        $this->cursoId = $cursoId;
        $this->competencias = $competenciasSeleccionadas; // Competencias seleccionadas
    }

    public function collection()
    {
        // Obtener el curso y los alumnos válidos
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

    public function headings(): array
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
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
        ];
    }
}
