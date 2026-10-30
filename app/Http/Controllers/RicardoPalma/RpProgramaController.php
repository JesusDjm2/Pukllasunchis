<?php

namespace App\Http\Controllers\RicardoPalma;

use App\Http\Controllers\Controller;
use App\Models\RicardoPalma\RpPrograma;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RpProgramaController extends Controller
{
    public function index(): View
    {
        $programas = RpPrograma::withCount(['cursos', 'estudiantes', 'actas'])->get();
        return view('admin.ricardo-palma.programas.index', compact('programas'));
    }

    public function show(RpPrograma $programa): View
    {
        $programa->load(['cursos' => function ($q) {
            $q->orderBy('numero_semestre')->orderBy('nombre');
        }]);

        // Agrupar cursos por semestre (1 al 10)
        $cursosPorSemestre = $programa->cursos->groupBy('numero_semestre');

        return view('admin.ricardo-palma.programas.show', compact('programa', 'cursosPorSemestre'));
    }
}
