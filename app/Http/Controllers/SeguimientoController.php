<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Incidencia;
use App\Models\Sugerencia;
use App\Models\Tutoria;

class SeguimientoController extends Controller
{
    /**
     * Vista consolidada de admin: Incidencias, Tutorías y Sugerencias en tabs,
     * mostrando si cada registro ya fue atendido.
     */
    public function index()
    {
        $incidencias = Incidencia::with(['alumno.ciclo.programa', 'ciclo.programa', 'docente', 'atendidoPor'])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'incidencias_page');

        $tutorias = Tutoria::with(['alumno.ciclo.programa', 'ciclo.programa', 'atendidoPor'])
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'tutorias_page');

        $sugerencias = Sugerencia::with(['alumno.ciclo.programa', 'ciclo.programa', 'atendidoPor'])
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'sugerencias_page');

        return view('admin.seguimiento.index', compact('incidencias', 'tutorias', 'sugerencias'));
    }

    /**
     * Lista todos los ciclos que tienen al menos un tutor asignado, con acceso
     * al QR de Tutoría y de Sugerencias de cada uno (cada QR notifica solo a
     * los tutores de ese ciclo específico).
     */
    public function qrCiclos()
    {
        $ciclos = Ciclo::with(['programa', 'tutores'])
            ->whereHas('tutores')
            ->get()
            ->sortBy([
                fn ($c) => optional($c->programa)->nombre ?? '',
                fn ($c) => $c->nombre,
            ]);

        return view('admin.seguimiento.qr-ciclos', compact('ciclos'));
    }
}
