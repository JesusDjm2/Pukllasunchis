<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use Illuminate\Http\Request;

class vistasAlumnosController extends Controller
{
    public function form()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $user = auth()->user();
        return view('alumnos.vistasAlumnos.formulario', compact('programas', 'ciclos', 'user'));
    }
    public function obtenerCiclos($programaId)
    {
        $ciclos = Ciclo::where('programa_id', $programaId)->get();
        return response()->json($ciclos);
    }
}
