<?php

namespace App\Http\Controllers;

class SitemapController extends Controller
{
    /**
     * Páginas públicas indexables por buscadores.
     * Cada entrada: nombre de ruta => [prioridad 0.0–1.0, frecuencia de cambio].
     * No incluye rutas que requieren autenticación, endpoints AJAX/API,
     * formularios de solo-POST, ni páginas administrativas.
     */
    protected function paginas(): array
    {
        return [
            // Inicio e institución
            'index' => [1.0, 'weekly'],
            'nosotros' => [0.8, 'monthly'],
            'informacion' => [0.8, 'monthly'],
            'planaDocente' => [0.6, 'monthly'],
            'politica' => [0.3, 'yearly'],
            'terminos' => [0.3, 'yearly'],

            // Programas
            'inicial' => [0.8, 'monthly'],
            'primaria' => [0.8, 'monthly'],
            'primariaEIB' => [0.8, 'monthly'],
            'formacion' => [0.8, 'monthly'],
            'profesionalizacion' => [0.8, 'monthly'],

            // Admisión
            'ordinario' => [0.7, 'monthly'],
            'exoneracion' => [0.6, 'monthly'],
            'traslado' => [0.6, 'monthly'],
            'resultados' => [0.6, 'monthly'],
            'formInscripcionRegular' => [0.7, 'monthly'],
            'regulares.create' => [0.6, 'monthly'],
            'postulantes.ppd.create' => [0.7, 'monthly'],

            // Titulación
            'tramiteTitulacion' => [0.6, 'monthly'],
            'plan' => [0.5, 'monthly'],
            'tinvestigacion' => [0.6, 'monthly'],
            'tesis' => [0.6, 'monthly'],

            // Trámites
            'tramites' => [0.5, 'monthly'],
            'matricula' => [0.6, 'monthly'],
            'Ttraslado' => [0.5, 'monthly'],
            'licencia' => [0.5, 'monthly'],
            'partes' => [0.5, 'monthly'],
            'extraordinarios' => [0.5, 'monthly'],

            // Líneas de trabajo
            'tutoria' => [0.5, 'monthly'],
            'bienestar' => [0.5, 'monthly'],
            'investigacion' => [0.5, 'monthly'],
            'preProfesional' => [0.5, 'monthly'],
            'subvenciones' => [0.5, 'monthly'],

            // Información
            'novedades' => [0.7, 'weekly'],
            'articulos' => [0.6, 'weekly'],
            'proyectos' => [0.6, 'monthly'],
            'innovaciones' => [0.6, 'monthly'],
            'bolsa' => [0.7, 'weekly'],
            'conv' => [0.6, 'weekly'],
            'conv2' => [0.6, 'weekly'],

            // Formatos descargables
            'alumno.formatos' => [0.4, 'monthly'],
            'ppd.formatos' => [0.4, 'monthly'],

            // Formularios públicos
            'incidencias.public.create' => [0.5, 'monthly'],
            'reclamos.public.create' => [0.5, 'monthly'],
            'postulante.create' => [0.5, 'monthly'],

            // Videos y tour virtual
            'video1' => [0.3, 'yearly'],
            'video2' => [0.3, 'yearly'],
            'video3' => [0.3, 'yearly'],
            'video4' => [0.3, 'yearly'],
            'tour' => [0.4, 'yearly'],
        ];
    }

    public function index()
    {
        $urls = collect($this->paginas())
            ->map(fn ($datos, $nombreRuta) => [
                'loc' => route($nombreRuta),
                'priority' => number_format($datos[0], 1),
                'changefreq' => $datos[1],
            ])
            ->values();

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}
