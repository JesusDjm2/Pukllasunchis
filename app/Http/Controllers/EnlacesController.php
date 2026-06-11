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
                'nombre'       => 'Dr. Richard Suárez Sánchez',
                'cargo'        => 'Director General',
                'especialidad' => 'Gestión Educativa',
                'descripcion'  => 'Doctor en Educación con amplia trayectoria en gestión institucional y liderazgo pedagógico. Comprometido con la formación de docentes interculturales de excelencia.',
                'foto'         => 'img/docentes/Richard-Suarez.png',
                'cv'           => null,
            ],
            [
                'nombre'       => 'Mg. Cecilia Maria Eguiluz Duffy',
                'cargo'        => 'Jefa de Unidad Académica',
                'especialidad' => 'Educación Intercultural',
                'descripcion'  => 'Especialista en educación intercultural bilingüe con amplia trayectoria en gestión pedagógica y formación docente en contextos de diversidad cultural.',
                'foto'         => 'img/docentes/Cecilia-Eguiluz.png',
                'cv'           => null,
            ],
            [
                'nombre'       => 'Mg. Cintya Marmanillo Valer',
                'cargo'        => 'Docente',
                'especialidad' => 'Formación Docente',
                'descripcion'  => 'Magíster con experiencia en innovación pedagógica y diseño de estrategias didácticas para la educación intercultural bilingüe.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Mg. Carlos Andres Guevara Zanbrano',
                'cargo'        => 'Coord. de Bienestar y Empleabilidad',
                'especialidad' => 'Orientación Educativa',
                'descripcion'  => 'Especialista en bienestar estudiantil y empleabilidad docente, con experiencia en programas de tutoría y acompañamiento integral al estudiante.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Mg. Maria Graciela Guevara Valdivia',
                'cargo'        => 'Coordinadora de Calidad',
                'especialidad' => 'Gestión de Calidad Educativa',
                'descripcion'  => 'Especialista en procesos de aseguramiento de la calidad educativa, autoevaluación institucional y mejora continua de programas de formación docente.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Bacilio Zea Sanchez',
                'cargo'        => 'Docente',
                'especialidad' => 'Educación Intercultural',
                'descripcion'  => 'Docente con vocación pedagógica y compromiso con la educación intercultural, aportando experiencia comunitaria y conocimiento del contexto andino.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Marly Amanda Gonzalez Bejar',
                'cargo'        => 'Docente',
                'especialidad' => 'Formación Docente',
                'descripcion'  => 'Profesional de la educación con experiencia en aula y en el desarrollo de competencias comunicativas para la formación inicial docente.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Hilda Cañari Loayza',
                'cargo'        => 'Docente',
                'especialidad' => 'Educación Inicial',
                'descripcion'  => 'Especialista en educación inicial con enfoque intercultural, comprometida con el desarrollo integral de la primera infancia en contextos pluriculturales.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Nancy Quispe Becerra',
                'cargo'        => 'Docente',
                'especialidad' => 'Lengua y Literatura',
                'descripcion'  => 'Docente con experiencia en enseñanza de comunicación y lengua materna, con énfasis en estrategias de lectura comprensiva y producción textual.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Sonia Quispe Quispe',
                'cargo'        => 'Docente',
                'especialidad' => 'Educación Intercultural Bilingüe',
                'descripcion'  => 'Especialista en educación intercultural bilingüe con enfoque en la revitalización lingüística y la enseñanza del quechua como primera lengua.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Tanya Villavicencio Callo',
                'cargo'        => 'Docente',
                'especialidad' => 'Psicología Educativa',
                'descripcion'  => 'Profesional de la educación con experiencia en orientación psicopedagógica y desarrollo de habilidades socioemocionales en la formación docente.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Farah Soraya Milena Mora Leython',
                'cargo'        => 'Docente',
                'especialidad' => 'Currículo y Evaluación',
                'descripcion'  => 'Especialista en diseño curricular y evaluación de aprendizajes, con amplia experiencia en la construcción de programas de formación docente.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Lourdes Cecilia Mar Salgado',
                'cargo'        => 'Coordinadora de Práctica Pre Profesional',
                'especialidad' => 'Práctica Pedagógica',
                'descripcion'  => 'Experta en acompañamiento y supervisión de prácticas pre profesionales, con enfoque en el desarrollo de competencias pedagógicas en contextos reales.',
                'foto'         => 'img/docentes/Cecilia-Mar-foto.jpg',
                'cv'           => 'docentes/cv/Hoja-de-Vida-Cecilia-Mar-2026.pdf',
            ],
            [
                'nombre'       => 'Luz Teresa Quispe Quispe',
                'cargo'        => 'Docente',
                'especialidad' => 'Matemática Educativa',
                'descripcion'  => 'Docente con experiencia en la enseñanza de matemáticas con enfoque intercultural, vinculando saberes andinos con el pensamiento lógico-matemático.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Raisa Saavedra Martinez',
                'cargo'        => 'Docente',
                'especialidad' => 'Investigación Educativa',
                'descripcion'  => 'Especialista en metodología de la investigación educativa, con experiencia en el acompañamiento de trabajos de titulación y proyectos de innovación pedagógica.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Melva María Flores Olavarria',
                'cargo'        => 'Docente',
                'especialidad' => 'Gestión Pedagógica',
                'descripcion'  => 'Profesional con experiencia en gestión pedagógica y liderazgo educativo, comprometida con la mejora continua de los procesos de enseñanza-aprendizaje.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Marta Climent Pérez',
                'cargo'        => 'Docente',
                'especialidad' => 'Educación Internacional',
                'descripcion'  => 'Educadora con perspectiva internacional y experiencia en proyectos de cooperación educativa, aportando una visión comparada de los sistemas pedagógicos.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Pratrick Anderson Ramos Chosec',
                'cargo'        => 'Docente',
                'especialidad' => 'Tecnología Educativa',
                'descripcion'  => 'Especialista en integración de tecnologías digitales en el aula, con experiencia en diseño de entornos virtuales de aprendizaje y recursos educativos digitales.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Robert Allexander Gomez Macero',
                'cargo'        => 'Docente',
                'especialidad' => 'Ciencias Sociales',
                'descripcion'  => 'Docente de ciencias sociales con enfoque crítico e intercultural, aportando reflexión histórica y ciudadana a la formación de futuros educadores.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Benjamin Camacho Vargas',
                'cargo'        => 'Docente',
                'especialidad' => 'Educación Física y Salud',
                'descripcion'  => 'Especialista en educación física con enfoque holístico, integrando juegos tradicionales y prácticas corporales andinas en la formación docente.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Marcel Bösch',
                'cargo'        => 'Docente',
                'especialidad' => 'Cooperación Internacional',
                'descripcion'  => 'Profesional internacional con experiencia en proyectos de desarrollo educativo intercultural y cooperación técnica en contextos de diversidad cultural.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Javier Ilich Oros Vengoa',
                'cargo'        => 'Docente',
                'especialidad' => 'Filosofía de la Educación',
                'descripcion'  => 'Especialista en filosofía y ética de la educación, con experiencia en el análisis crítico de los fundamentos teóricos de la práctica pedagógica intercultural.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Fabricio Enrique Rivas Marmanillo',
                'cargo'        => 'Docente',
                'especialidad' => 'Comunicación Educativa',
                'descripcion'  => 'Docente con experiencia en comunicación educativa y producción de materiales pedagógicos para contextos interculturales y bilingües.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Hernan Sullca Tito',
                'cargo'        => 'Docente',
                'especialidad' => 'Lengua Quechua',
                'descripcion'  => 'Hablante nativo y especialista en lengua quechua, con experiencia en la enseñanza y revitalización lingüística en comunidades andinas y en contextos formales de educación.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Sofía Juana Kancha Latorre',
                'cargo'        => 'Docente',
                'especialidad' => 'Educación Intercultural',
                'descripcion'  => 'Docente con raíces andinas y experiencia en educación intercultural, aportando saberes comunitarios y perspectivas de género a la formación inicial docente.',
                'foto'         => null,
                'cv'           => null,
            ],
            [
                'nombre'       => 'Jhon Arthur Silva Peralta',
                'cargo'        => 'Docente',
                'especialidad' => 'Arte y Cultura',
                'descripcion'  => 'Especialista en arte, cultura y expresión creativa, con experiencia en la integración de manifestaciones artísticas andinas en los procesos de enseñanza-aprendizaje.',
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
