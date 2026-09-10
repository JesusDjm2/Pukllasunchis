<?php

namespace App\Http\Controllers;

use App\Mail\ReclamoMail;
use App\Models\Programa;
use App\Models\Reclamo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReclamoController extends Controller
{
    public function publicCreate()
    {
        $programas = Programa::orderBy('nombre')->get();
        $areas = config('libro_reclamaciones.areas');

        return view('reclamos.public-create', compact('programas', 'areas'));
    }

    public function adminAll()
    {
        $reclamos = Reclamo::orderByDesc('created_at')->paginate(20);

        return view('admin.reclamos.index', compact('reclamos'));
    }

    public function adminShow(Reclamo $reclamo)
    {
        return view('admin.reclamos.show', compact('reclamo'));
    }

    public function publicStore(Request $request)
    {
        // Honeypot: campo invisible para humanos. Si viene lleno, es un bot.
        // Se responde como si el envío hubiera sido exitoso para no delatar el mecanismo.
        if ($request->filled('sitio_web')) {
            return redirect()->route('reclamos.public.create')
                ->with('success', sprintf('%06d-%d', random_int(100000, 999999), now()->year));
        }

        // Time-trap: un envío en menos de 2s desde que se cargó el formulario es casi
        // siempre un bot. Aquí sí se informa el error real para no perder un reclamo legítimo.
        $tsForm = (int) $request->input('ts_form', 0);
        if ($tsForm > 0 && (now()->timestamp - $tsForm) < 2) {
            return redirect()->route('reclamos.public.create')
                ->withInput()
                ->with('error', 'Hubo un problema al procesar tu solicitud. Por favor, inténtalo nuevamente.');
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'dni' => 'required|string|max:20',
            'domicilio' => 'required|string|max:255',
            'telefono' => 'required|string|max:30',
            'correo' => 'required|email|max:255',
            'condicion_reclamante' => 'required|in:Estudiante,Egresado,Postulante,Padre de familia,Público en general',

            'programa' => 'nullable|string|max:150',
            'ciclo' => 'nullable|string|max:100',
            'codigo_estudiante' => 'nullable|string|max:50',

            'tipo_servicio' => 'required|in:Servicio educativo,Trámite administrativo,Otro servicio',
            'descripcion_servicio' => 'nullable|string',
            'area_involucrada' => 'required|string|max:150',
            'servicio_contratado' => 'nullable|string',

            'tipo_reclamacion' => 'required|in:Reclamo,Queja',
            'descripcion_hechos' => 'required|string|min:10',
            'pedido' => 'required|string|min:5',

            'adjunto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',

            'declara_informacion_verdadera' => 'required|accepted',
            'autoriza_tratamiento_datos' => 'required|accepted',
            'confirma_lectura_libro' => 'required|accepted',
        ]);

        $data['declara_informacion_verdadera'] = true;
        $data['autoriza_tratamiento_datos'] = true;
        $data['confirma_lectura_libro'] = true;

        if (! in_array($data['condicion_reclamante'], ['Estudiante', 'Egresado', 'Postulante'])) {
            $data['programa'] = null;
            $data['ciclo'] = null;
            $data['codigo_estudiante'] = null;
        }

        if ($request->hasFile('adjunto')) {
            $file = $request->file('adjunto');
            $name = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('reclamos'), $name);
            $data['adjunto'] = $name;
        }

        $reclamo = Reclamo::create($data);
        $reclamo->numero_reclamo = sprintf('%06d-%d', $reclamo->id, $reclamo->created_at->year);
        $reclamo->save();

        $this->enviarConstancia($reclamo);

        return redirect()->route('reclamos.public.create')
            ->with('success', $reclamo->numero_reclamo);
    }

    private function enviarConstancia(Reclamo $reclamo): void
    {
        try {
            Mail::to($reclamo->correo)->send(new ReclamoMail($reclamo));
        } catch (\Throwable $e) {
            Log::warning("Email constancia de reclamo {$reclamo->id} al reclamante: ".$e->getMessage());
        }

        $correosInstitucionales = collect(config('libro_reclamaciones.correos_institucionales', []))
            ->filter()
            ->unique()
            ->reject(fn ($correo) => $correo === $reclamo->correo);

        foreach ($correosInstitucionales as $correo) {
            try {
                Mail::to($correo)->send(new ReclamoMail($reclamo, paraInstitucion: true));
            } catch (\Throwable $e) {
                Log::warning("Email constancia de reclamo {$reclamo->id} a institución ({$correo}): ".$e->getMessage());
            }
        }
    }
}
