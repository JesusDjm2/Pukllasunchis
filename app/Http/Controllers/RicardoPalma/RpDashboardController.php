<?php

namespace App\Http\Controllers\RicardoPalma;

use App\Http\Controllers\Controller;
use App\Models\RicardoPalma\RpActa;
use App\Models\RicardoPalma\RpEstudiante;
use App\Models\RicardoPalma\RpPeriodo;
use App\Models\RicardoPalma\RpPrograma;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RpDashboardController extends Controller
{
    /**
     * Dashboard general del módulo IESP Ricardo Palma.
     * Accesible solo por admin y super-admin.
     */
    public function index(?Request $request = null): View
    {
        $totalProgramas = RpPrograma::where('activo', true)->count();
        $totalPeriodos = RpPeriodo::count();
        $totalEstudiantes = RpEstudiante::count();
        $totalActas = RpActa::count();

        // Programas con conteo de cursos y estudiantes
        $programas = RpPrograma::withCount(['cursos', 'estudiantes', 'actas'])
            ->where('activo', true)
            ->get();

        // Últimas actas consolidadas registradas
        $ultimasActas = RpActa::with(['periodo', 'programa'])
            ->withCount('calificaciones')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.ricardo-palma.dashboard', compact(
            'totalProgramas',
            'totalPeriodos',
            'totalEstudiantes',
            'totalActas',
            'programas',
            'ultimasActas'
        ));
    }
}
