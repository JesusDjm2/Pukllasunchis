<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Programa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Str;

class vistasAlumnosController extends Controller
{
    public function form()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $user = auth()->user();
        if ($user) {
            return view('alumnos.vistasAlumnos.formulario', compact('programas', 'ciclos', 'user'));
        }
        return view('alumnos.vistasAlumnos.postulantes');
    }
    public function obtenerCiclos($programaId)
    {
        $ciclos = Ciclo::where('programa_id', $programaId)->get();
        return response()->json($ciclos);
    }
    /* public function getCursos($cicloId)
    {
        $cursos = Curso::where('ciclo_id', $cicloId)->get();
        return response()->json($cursos);
    } */
    public function getCursos($cicloId)
    {
        $cursos = Curso::with('ciclo') // 👈 importante
            ->where('ciclo_id', $cicloId)
            ->get();

        return response()->json($cursos);
    }
    public function exportarFichaPDF(Alumno $alumno)
    { 
        $alumno->load(['ciclo.cursos', 'cursos', 'programa']); 
        $pdf = Pdf::loadView('alumnos.vistasAlumnos.pdf', compact('alumno'));
        return $pdf->download('ficha_' . Str::slug($alumno->apellidos . '_' . $alumno->nombres) . '.pdf');
    }
}
