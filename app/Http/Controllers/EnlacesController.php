<?php

namespace App\Http\Controllers;

use App\Models\AdminFid;
use App\Models\AdminPpd;
use App\Models\Comunicado;
use App\Models\Postulante;
use App\Support\BolsaTrabajoListado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnlacesController extends Controller
{
    public function tour()
    {
        return view('tour01');
    }

    public function video1()
    {
        return view('videos.video1');
    }

    public function video2()
    {
        return view('videos.video2');
    }

    public function video3()
    {
        return view('videos.video3');
    }

    public function video4()
    {
        return view('videos.video4');
    }

    public function nosotros()
    {
        return view('nosotros');
    }

    public function inicial()
    {
        return view('programas.educacion-inicial');
    }

    public function primaria()
    {
        return view('programas.educacion-primaria');
    }

    public function primariaEIB()
    {
        return view('programas.educacion-primaria-EIB');
    }

    public function formacion()
    {
        return view('programas.formacion-continua');
    }

    public function profesionalizacion()
    {
        $periodoAdmisionActivo = Schema::hasTable('admin_ppds')
            && AdminPpd::query()->where('estado', true)->exists();

        return view('programas.profesionalizacion-docente', compact('periodoAdmisionActivo'));
    }

    // Ordinario
    public function ordinario()
    {
        $periodoAdmision = AdminFid::where('estado', true)->first();

        return view('admision.ingreso-ordinario', compact('periodoAdmision'));
    }

    public function exoneracion()
    {
        return view('admision.por-exoneracion');
    }

    public function traslado()
    {
        return view('admision.traslado-externo');
    }

    public function resultados()
    {
        return view('admision.resultados');
    }

    // Titulacion
    public function tramiteTitulacion()
    {
        return view('tramites.tramite-titulacion');
    }

    public function plan()
    {
        return view('tramites.titulacion.plan-de-trabajo');
    }

    public function tinvestigacion()
    {
        return view('tramites.titulacion.investigacion');
    }

    public function tesis()
    {
        return view('tramites.titulacion.tesis');
    }

    public function tramites()
    {
        return view('tramites.titulacion.tramite');
    }

    // Tramites
    public function matricula()
    {
        return view('tramites.Matriculas');
    }

    public function Ttraslado()
    {
        return view('tramites.Traslado');
    }

    public function licencia()
    {
        return view('tramites.Licencia-de-estudios');
    }

    public function partes()
    {
        return view('tramites.Mesa-de-partes');
    }

    public function extraordinarios()
    {
        return view('tramites.titulacion.extraordinarios');
    }

    // Líneas
    public function tutoria()
    {
        return view('lineas.lineas-tutoria');
    }

    public function bienestar()
    {
        return view('lineas.lineas-bienestar');
    }

    public function investigacion()
    {
        return view('lineas.lineas-investigacion');
    }

    public function preProfesional()
    {
        return view('lineas.practica-pre-profesional');
    }

    public function subvenciones()
    {
        return view('lineas.subvenciones-y-becas');
    }

    // Información
    public function novedades(Request $request)
    {
        $query = Comunicado::query()
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id');

        if ($request->filled('anio')) {
            $query->where('anio', (int) $request->input('anio'));
        }

        $comunicados = $query->get();
        $anios = Comunicado::query()->select('anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return view('informacion.informacion-novedades', compact('comunicados', 'anios', 'mesesNombres'));
    }

    public function alumnoComunicados()
    {
        $comunicados = Comunicado::query()
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('alumnos.comunicados', compact('comunicados'));
    }

    public function articulos()
    {
        return view('informacion.articulos');
    }

    public function proyectos()
    {
        return view('informacion.proyectos-academicos');
    }

    public function innovaciones()
    {
        return view('informacion.innovaciones');
    }

    public function bolsa(Request $request)
    {
        $postulantes = Postulante::all();
        $postulantes1 = Postulante::where('programa_id', 1)->get();
        $postulantes2 = Postulante::where('programa_id', 2)->get();

        return view('informacion.bolsa-de-trabajo', array_merge(
            compact('postulantes', 'postulantes1', 'postulantes2'),
            BolsaTrabajoListado::datos($request)
        ));
    }

    public function conv()
    {
        return view('informacion.convocatoria');
    }

    public function conv2()
    {
        return view('informacion.convocatoria-2');
    }

    // Foot
    public function informacion()
    {
        return view('foot.informacion-institucional');
    }

    public function planaDocente()
    {
        $docentes = [
            [
                'nombre'       => 'Cecilia Eguiluz D.',
                'cargo'        => 'Jefa de Unidad Académica',
                'especialidad' => 'Educación Intercultural',
                'descripcion'  => 'Especialista en educación intercultural bilingüe con amplia trayectoria en gestión pedagógica y formación docente en contextos de diversidad cultural.',
                'foto'         => 'img/docentes/Cecilia-Eguiluz.png',
                'cv'           => 'pdf/03-TUPA-EESPP-2025-08022024.pdf',
            ],
            [
                'nombre'       => 'Richard Suarez',
                'cargo'        => 'Coordinadora de Formación Continua',
                'especialidad' => 'Formación Docente',
                'descripcion'  => 'Docente comprometida con la innovación pedagógica y el desarrollo profesional continuo, con experiencia en diseño curricular y evaluación educativa.',
                'foto'         => 'img/docentes/Richard-Suarez.png',
                'cv'           => null,
            ],
            [
                'nombre'       => 'Cecilia Mar S.',
                'cargo'        => 'Coordinadora de Práctica Pre Profesional',
                'especialidad' => 'Práctica Pedagógica',
                'descripcion'  => 'Experta en acompañamiento y supervisión de prácticas pre profesionales, con enfoque en el desarrollo de competencias pedagógicas en contextos reales.',
                'foto'         => 'img/docentes/Cecilia-Mar-foto.jpg',
                'cv'           => null,
            ],
            [
                'nombre'       => 'Carlos Andrés Guevara Z.',
                'cargo'        => 'Coord. de Bienestar y Empleabilidad',
                'especialidad' => 'Orientación Educativa',
                'descripcion'  => 'Especialista en bienestar estudiantil y empleabilidad docente, con experiencia en programas de tutoría y acompañamiento integral al estudiante.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'María Graciela Guevara Valdivia',
                'cargo'        => 'Coordinadora de Calidad',
                'especialidad' => 'Gestión de Calidad Educativa',
                'descripcion'  => 'Especialista en procesos de aseguramiento de la calidad educativa, autoevaluación institucional y mejora continua de programas de formación docente.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Carlos Andrés',
                'cargo'        => 'Coord. de Investigación y Práctica',
                'especialidad' => 'Investigación Educativa',
                'descripcion'  => 'Docente investigador con experiencia en metodologías cualitativas aplicadas a contextos educativos interculturales y comunitarios.',
                'foto'         => null,
                'cv'           => null,
            ],
        ];

        return view('foot.plana-docente', compact('docentes'));
    }

    public function politica()
    {
        return view('foot.politica-de-privacidad');
    }

    public function terminos()
    {
        return view('foot.terminos-y-condiciones');
    }

    public function pandero()
    {
        return view('pandero');
    }
}
