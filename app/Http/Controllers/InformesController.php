<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudInformesMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Formulario "Solicitar informes" de la página de inicio.
 */
class InformesController extends Controller
{
    public const DESTINO = 'davidmiranda.eesp@pukllavirtual.edu.pe';

    public const PROGRAMAS = [
        'AdmisionOrdinario'         => 'Admisión Ordinario',
        'AdmisionTraslado'          => 'Admisión Traslado',
        'AdmisionBeca'              => 'Admisión Beca',
        'ProfesionalizacionDocente' => 'Profesionalización Docente',
        'Otro'                      => 'Otra consulta',
    ];

    public function enviar(Request $request)
    {
        // Honeypot: los bots llenan el campo oculto; respondemos como si todo fuera bien.
        if (filled($request->input('sitio_web'))) {
            return redirect()->to(route('index').'#solicitar-informes')
                ->with('informes_ok', true);
        }

        $datos = $request->validate([
            'nombre'   => 'required|string|max:120',
            'email'    => 'required|email|max:150',
            'telefono' => ['required', 'regex:/^\+?[0-9\s]{7,15}$/'],
            'programa' => 'required|in:'.implode(',', array_keys(self::PROGRAMAS)),
            'mensaje'  => 'nullable|string|max:2000',
            'terminos' => 'accepted',
        ], [
            'nombre.required'   => 'Ingrese su nombre.',
            'email.required'    => 'Ingrese su correo electrónico.',
            'email.email'       => 'El correo electrónico no es válido.',
            'telefono.required' => 'Ingrese su número de WhatsApp.',
            'telefono.regex'    => 'El número de WhatsApp no es válido.',
            'programa.required' => 'Seleccione un programa.',
            'programa.in'       => 'Seleccione un programa válido.',
            'mensaje.max'       => 'La consulta no debe superar los 2000 caracteres.',
            'terminos.accepted' => 'Debe aceptar el Reglamento de Protección de Datos Personales.',
        ]);

        $datos['programa_texto'] = self::PROGRAMAS[$datos['programa']];

        try {
            Mail::to(self::DESTINO)->send(new SolicitudInformesMail($datos));
        } catch (\Throwable $e) {
            Log::error('Solicitud de informes (inicio): '.$e->getMessage());

            return redirect()->to(route('index').'#solicitar-informes')
                ->withInput()
                ->with('informes_error', 'No pudimos enviar su mensaje en este momento. Inténtelo nuevamente en unos minutos.');
        }

        return redirect()->to(route('index').'#solicitar-informes')
            ->with('informes_ok', true);
    }
}
