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
        $categorias = [
            'EQUIPO DIRECTIVO' => [
                [
                    'nombre' => 'Richard Suarez Sanchez',
                    'cargo' => 'Director General',
                    'grado' => 'Doctorado',
                    'programas' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e / EducaciÃ³n Primaria',
                    'cursos_dicta' => 'Fundamentos para la EducaciÃ³n Primaria Intercultural BilingÃ¼e<br>PrÃ¡ctica e InvestigaciÃ³n IX',
                    'cursos_investigacion' => 'GestiÃ³n Educativa<br>PolÃticas Educativas y diseÃ±o curricular<br>Historia de la EducaciÃ³n<br>Procesos globales',
                    'cv' => 'https://drive.google.com/file/d/1YpF5JJwlUkMGVIYgx9HW76erg2T214h1/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Eguiluz Duffy, Cecilia MarÃa',
                    'cargo' => 'Jefatura AcadÃ©mica',
                    'grado' => 'Doctorado',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e / EducaciÃ³n Primaria',
                    'cursos_dicta' => 'Desarrollo Personal I<br>PrÃ¡ctica e InvestigaciÃ³n IX<br>Artes Integradas para el Aprendizaje en la Diversidad',
                    'cursos_investigacion' => 'Competencias Interculturales y PlanificaciÃ³n Educativa<br>GestiÃ³n y DiseÃ±o de Programas de EducaciÃ³n Superior<br>DidÃ¡ctica de la EducaciÃ³n Inicial y EducaciÃ³n Intercultural BilingÃ¼e<br>CurrÃculo intercultural y formaciÃ³n superior<br>Oralidad y didÃ¡ctica de la enseÃ±anza de la lengua escrita<br>Estudios de Lengua Originaria',
                    'cv' => 'https://drive.google.com/file/d/1tOI9S-dJXtWjDEIJVhOV5AfMiZErs9FY/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Marmanillo Valer, Cintya',
                    'cargo' => 'Coordinadora de la Unidad de FormaciÃ³n Inicial y Continua',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial / EducaciÃ³n Primaria',
                    'cursos_dicta' => 'PrÃ¡ctica e InvestigaciÃ³n IX<br>PrÃ¡ctica e InvestigaciÃ³n III<br>Desarrollo Personal y Social en la Primera Infancia',
                    'cursos_investigacion' => 'GestiÃ³n Educativa, especialista en EducaciÃ³n Inicial<br>AtenciÃ³n a la Diversidad y EducaciÃ³n Inclusiva<br>NeuropedagogÃa en el Ã¡mbito educativo<br>DidÃ¡cticas de la EducaciÃ³n Inicial',
                    'cv' => 'https://drive.google.com/file/d/1Tp1la1mXxI5e7NHxrV7Nrm-w8fivyCvh/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Guevara Zambrano, Carlos AndrÃ©s',
                    'cargo' => 'Coordinador de la Unidad de InvestigaciÃ³n y PrÃ¡ctica Preprofesional',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e / EducaciÃ³n Primaria',
                    'cursos_dicta' => 'PrÃ¡ctica e InvestigaciÃ³n VII<br>PrÃ¡ctica e InvestigaciÃ³n IX',
                    'cursos_investigacion' => 'InvestigaciÃ³n Educativa<br>EducaciÃ³n popular y comunitaria<br>NeuropsicologÃa en el Ã¡mbito educativo<br>ParticipÃ³ en programas innovadores de EducaciÃ³n Waldorf',
                    'cv' => 'https://drive.google.com/file/d/1pmbBNzNaCISfR69QjNw0K4r967W5ixvU/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Guevara Valdivia, MarÃa Graciela',
                    'cargo' => 'Coordinadora de Unidad de Bienestar y Empleabilidad',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e / EducaciÃ³n Primaria',
                    'cursos_dicta' => 'GestiÃ³n de la AtenciÃ³n y Cuidado Infantil - I<br>InclusiÃ³n Educativa 2 y AtenciÃ³n a las Necesidades Educativas Especiales II<br>Desarrollo Personal II<br>Desarrollo Personal II',
                    'cursos_investigacion' => 'PsicologÃa Educativa<br>AtenciÃ³n a la diversidad y EducaciÃ³n Inclusiva, especialista en el diseÃ±o de Programas de FormaciÃ³n a Docentes en EducaciÃ³n Inclusiva<br>NeuropedagogÃa en el Ã¡mbito educativo',
                    'cv' => 'https://drive.google.com/file/d/1nHceABAWA9Oe46MQBzvwWij-_fXEBezK/view?usp=drive_link',
                ],
            ],
            'DOCENTES DE EDUCACIÃN INICIAL' => [
                [
                    'nombre' => 'Lourdes Cecilia Mar Salgado',
                    'cargo' => 'Docente formador / Representante del Programa de EducaciÃ³n Inicial',
                    'grado' => 'Profesora de EducaciÃ³n Inicial',
                    'programas' => 'EducaciÃ³n Inicial / EducaciÃ³n Primaria EIB / EducaciÃ³n Primaria',
                    'cursos_dicta' => 'PrÃ¡ctica e InvestigaciÃ³n IX<br>PrÃ¡ctica e InvestigaciÃ³n VII<br>PrÃ¡ctica e InvestigaciÃ³n V<br>Aprendizaje y EnseÃ±anza de la Ciencia en Ciclo II<br>Desarrollo del Pensamiento',
                    'cursos_investigacion' => 'GestiÃ³n Educativa en EducaciÃ³n Inicial y Primaria<br>DidÃ¡ctica de la EducaciÃ³n Inicial, especialista en las Ã¡reas de MatemÃ¡tica y Desarrollo de las Ciencias en la infancia<br>GestiÃ³n de PrÃ¡ctica Preprofesional',
                    'cv' => 'https://drive.google.com/file/d/1ddvODaQWgx31o8XFcUD50-4ERap7Entu/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Luz Teresa Quispe Quispe',
                    'cargo' => 'Docente formador',
                    'grado' => 'Licenciada en EducaciÃ³n Primaria / Segunda Especialidad en EducaciÃ³n Inicial',
                    'programas' => 'EducaciÃ³n Inicial / EducaciÃ³n Primaria EIB',
                    'cursos_dicta' => 'PrÃ¡ctica e InvestigaciÃ³n VII<br>PrÃ¡ctica e InvestigaciÃ³n IX<br>GestiÃ³n de los Servicios educativos en educaciÃ³n inicial',
                    'cursos_investigacion' => 'EducaciÃ³n Inicial y Primaria<br>Desarrollo infantil en contextos diversos<br>InvestigaciÃ³n Educativa<br>Manejo de Lengua Originaria',
                    'cv' => 'https://drive.google.com/file/d/1bBbu6apZ4WYDVuSHux7zSSrRjvk0Ac9z/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Raisa Saavedra Martinez',
                    'cargo' => 'Docente formador',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial / EducaciÃ³n Primaria',
                    'cursos_dicta' => 'Corporeidad I y Motricidad para el Aprendizaje y la AutonomÃa<br>Desarrollo de la Psicomotricidad en la Primera Infancia<br>Arte, Creatividad y Aprendizaje<br>Arte, creatividad y Aprendizaje',
                    'cursos_investigacion' => 'DidÃ¡ctica del Arte, Creatividad y EducaciÃ³n Psicomotriz en EducaciÃ³n Inicial y Primaria<br>Juego y desarrollo en la primera infancia<br>GestiÃ³n Educativa y Docencia en EducaciÃ³n Inicial<br>DidÃ¡ctica y orientaciÃ³n de la prÃ¡ctica preprofesional',
                    'cv' => 'https://drive.google.com/file/d/1GVOgrOoS9Lt0Af0nzx3iJMyey9Ntglv_/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Melva MarÃa Flores Olavarria',
                    'cargo' => 'Docente formador',
                    'grado' => 'Profesora de EducaciÃ³n Inicial',
                    'programas' => 'EducaciÃ³n Inicial',
                    'cursos_dicta' => 'PrÃ¡ctica e InvestigaciÃ³n IX<br>PrÃ¡ctica e InvestigaciÃ³n I',
                    'cursos_investigacion' => 'DidÃ¡ctica de la EducaciÃ³n Inicial<br>DidÃ¡ctica y orientaciÃ³n de la PrÃ¡ctica Preprofesional',
                    'cv' => 'https://drive.google.com/file/d/1veZ9acgoC0HignbiHW97fjjASaDsSJH-/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Marta Climent PÃ©rez',
                    'cargo' => 'Docente formador',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial',
                    'cursos_dicta' => 'Desarrollo de la Creatividad en la Primera Infancia<br>Juego I, Desarrollo y Aprendizaje en la Primera Infancia<br>PrÃ¡ctica e InvestigaciÃ³n IX',
                    'cursos_investigacion' => 'AdministraciÃ³n y Liderazgo en EducaciÃ³n<br>GestiÃ³n de la EducaciÃ³n Inicial y PedagogÃa Waldorf en la Primera Infancia<br>DidÃ¡ctica de la ComunicaciÃ³n, Arte y Creatividad en la Primera Infancia',
                    'cv' => 'https://drive.google.com/file/d/1FQk7W1_hp_DelCFZvA4T1BqsnUBRphrh/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Patrick Anderson Ramos Chosec',
                    'cargo' => 'Docente formador',
                    'grado' => 'Licenciado en EducaciÃ³n Inicial',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'Desarrollo Personal II<br>Desarrollo Personal II<br>Fundamentos de la EducaciÃ³n Inicial<br>PrÃ¡ctica e InvestigaciÃ³n IX<br>Interacciones de Calidad y Desarrollo en la Primera Infancia II<br>Desarrollo y Aprendizaje en Contextos Diversos I',
                    'cursos_investigacion' => 'EducaciÃ³n Inicial<br>PsicologÃa Educativa<br>AcompaÃ±amiento a programas de ComunicaciÃ³n en EducaciÃ³n Inicial<br>Manejo de Lengua Originaria',
                    'cv' => 'https://drive.google.com/file/d/17cY378b9uRjnK4f5pKDCI8gDeprHKgtw/view?usp=drive_link',
                ],
            ],
            'DOCENTES DE EDUCACIÃN PRIMARIA' => [
                [
                    'nombre' => 'Farah Soraya Milena Mora Leython',
                    'cargo' => 'Docente formador / Representante del Programa de EducaciÃ³n Primaria',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'ComunicaciÃ³n en Castellano I<br>Lectura y escritura en la escritura superior<br>Lectura y escritura en la escritura superior<br>PrÃ¡ctica e InvestigaciÃ³n IX<br>PrÃ¡ctica e InvestigaciÃ³n VII',
                    'cursos_investigacion' => 'DidÃ¡ctica de la ComunicaciÃ³n en EducaciÃ³n Primaria y Secundaria<br>Literatura y reflexiÃ³n en Estudios Culturales<br>InnovaciÃ³n e InvestigaciÃ³n Educativa',
                    'cv' => 'https://drive.google.com/file/d/1YU4TtA-6E-W7QX7OmvwK9D5jsk5iKiCS/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Robert Allexander Gomez Macero',
                    'cargo' => 'Docente formador',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'PrÃ¡ctica e InvestigaciÃ³n V',
                    'cursos_investigacion' => 'GestiÃ³n Educativa<br>Ciencias de la EducaciÃ³n<br>TutorÃa de jÃ³venes y adolescentes',
                    'cv' => 'https://drive.google.com/file/d/1lQoUNDEQyPkZF_-IeAJccgZJq0tC5EAm/view?usp=drive_link',
                ],
            ],
            'DOCENTES DE EDUCACIÃN PRIMARIA INTERCULTURAL BILINGÃE' => [
                [
                    'nombre' => 'Bacilio Zea Sanchez',
                    'cargo' => 'Docente formador / Representante del Programa de EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e / EducaciÃ³n Primaria',
                    'cursos_dicta' => 'Desarrollo Personal I<br>Lengua IndÃgena u Originaria I<br>PrÃ¡ctica e investigaciÃ³n I<br>PrÃ¡ctica e InvestigaciÃ³n IX<br>Espiritualidad e Interculturalidad',
                    'cursos_investigacion' => 'EducaciÃ³n comunitaria, especialista en Lenguas Originarias<br>EducaciÃ³n Intercultural e Inclusiva<br>DidÃ¡ctica de la EducaciÃ³n Primaria<br>CosmovisiÃ³n y Espiritualidad<br>Promotor Cultural / Manejo del quechua',
                    'cv' => 'https://drive.google.com/file/d/1JD6q2VF9Y9GFLKg_M1KCXeLGCawjfk19/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Marly Amanda Gonzalez Bejar',
                    'cargo' => 'Docente formador',
                    'grado' => 'Profesora de EducaciÃ³n Primaria / MaestrÃa Concluida',
                    'programas' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'Desarrollo del BilingÃ¼ismo V<br>PrÃ¡ctica e InvestigaciÃ³n IX<br>PrÃ¡ctica e InvestigaciÃ³n III',
                    'cursos_investigacion' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e<br>DidÃ¡ctica de Segundas Lenguas, BilingÃ¼ismo, oficios comunitarios y Saberes Ancestrales de crianza<br>PolÃticas y GestiÃ³n Educativa<br>PlanificaciÃ³n Curricular EIB<br>Quechua hablante',
                    'cv' => 'https://drive.google.com/file/d/1n81lcDc4OVDgbEqGn9xoRQr3NDTAhZTd/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Hilda CaÃ±ari Loayza',
                    'cargo' => 'Docente formador',
                    'grado' => 'Licenciada en EducaciÃ³n, especialidad Historia / MaestrÃa Concluida',
                    'programas' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'Lengua IndÃgena u Originaria III<br>Lengua IndÃgena u Originaria VII<br>Lengua IndÃgena u Originaria V<br>Lengua originaria VIII',
                    'cursos_investigacion' => 'Especialista de Lenguas Originarias (Quechua)<br>Traductora intÃ©rprete<br>DidÃ¡ctica de enseÃ±anza de segundas lenguas<br>GramÃ¡tica quechua<br>Quechua hablante',
                    'cv' => 'https://drive.google.com/file/d/1Ebj3QKMfHhnOf-HpHAfi2vJTDrnQG0kh/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Nancy Quispe Becerra',
                    'cargo' => 'Docente formador',
                    'grado' => 'Profesora de EducaciÃ³n Primaria / MaestrÃa Concluida',
                    'programas' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'Aprendizaje de las MatemÃ¡ticas III<br>Aprendizaje de las MatemÃ¡ticas II<br>PrÃ¡ctica e InvestigaciÃ³n IX',
                    'cursos_investigacion' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e<br>DidÃ¡ctica de Segundas Lenguas y BilingÃ¼ismo<br>PolÃticas y GestiÃ³n Educativa<br>PlanificaciÃ³n Curricular EIB y didÃ¡ctica de la matemÃ¡tica<br>DidÃ¡ctica y orientaciÃ³n de la prÃ¡ctica preprofesional<br>Quechua hablante',
                    'cv' => 'https://drive.google.com/file/d/154_4DKL78NQ3rbAOadsS19G1Jqt41bvg/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Sonia Quispe Quispe',
                    'cargo' => 'Docente formador',
                    'grado' => 'Profesora de EducaciÃ³n Primaria / MaestrÃa Concluida',
                    'programas' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'PolÃticas y GestiÃ³n Educativa en EIB<br>PrÃ¡ctica e InvestigaciÃ³n IX',
                    'cursos_investigacion' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e<br>DidÃ¡ctica de Segundas Lenguas, BilingÃ¼ismo, oficios comunitarios y Saberes Ancestrales<br>PolÃticas y GestiÃ³n Educativa<br>PlanificaciÃ³n Curricular EIB<br>DidÃ¡ctica y orientaciÃ³n de la prÃ¡ctica preprofesional<br>Quechua hablante',
                    'cv' => 'https://drive.google.com/file/d/18HTNvhqD-oF_QYoMUWSnG6F3fRzkzCk7/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Tanya Villavicencio Callo',
                    'cargo' => 'Docente formador',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'PrÃ¡ctica e InvestigaciÃ³n V<br>PrÃ¡ctica e InvestigaciÃ³n VII',
                    'cursos_investigacion' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e<br>DidÃ¡ctica de Segundas Lenguas y BilingÃ¼ismo<br>PolÃticas y GestiÃ³n Educativa<br>PlanificaciÃ³n Curricular EIB<br>Aprendizaje Basado en Proyectos<br>DidÃ¡ctica y orientaciÃ³n de la prÃ¡ctica preprofesional<br>Quechua hablante',
                    'cv' => 'https://drive.google.com/file/d/1modGCt1o85z14KwBr3mSL4mZcvpBiyPx/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Benjamin Camacho Vargas',
                    'cargo' => 'Docente formador',
                    'grado' => 'Licenciado en Literatura y LingÃ¼Ãstica',
                    'programas' => 'EducaciÃ³n Inicial y Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'Literatura y Sociedad en Contextos Diversos<br>Literatura y Sociedad en Contextos Diversos<br>Electivo: CiudadanÃa Intercultural: Imagen y PedagogÃa<br>Electivo: CiudadanÃa Intercultural: Imagen y PedagogÃa',
                    'cursos_investigacion' => 'Literatura y LingÃ¼Ãstica<br>EducaciÃ³n comunitaria y pueblos indÃgenas<br>Proyectos de innovaciÃ³n educativa comunitaria<br>OrientaciÃ³n juvenil<br>Desarrollo de capacidades comunicativas en jÃ³venes indÃgenas',
                    'cv' => 'https://drive.google.com/file/d/16UeClCg2w5Kh2B_nOt8ZkfHH5rh9qihh/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Marcel BÃ¶sch',
                    'cargo' => 'Docente formador',
                    'grado' => 'MaestrÃa',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'ResoluciÃ³n de Problemas MatemÃ¡ticos I<br>ResoluciÃ³n de Problemas MatemÃ¡ticos I<br>Aprendizaje de las MatemÃ¡ticas I',
                    'cursos_investigacion' => 'Corrientes Educativas y Proyectos de InnovaciÃ³n Educativa<br>DidÃ¡cticas de las MatemÃ¡ticas',
                    'cv' => 'https://drive.google.com/file/d/1zo-Tt495eL7AW4zoyAQHfeKb_npM1mKR/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Javier Ilich Oros Vengoa',
                    'cargo' => 'Docente formador',
                    'grado' => 'Licenciado en ComunicaciÃ³n Social / MaestrÃa concluida',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'Electivo: Tics y Recursos de Aprendizaje para el Aula<br>Electivo: TecnologÃas en la educaciÃ³n superior',
                    'cursos_investigacion' => 'Proyectos Educativos basados en TIC<br>ComunicaciÃ³n para el desarrollo y medios audiovisuales<br>Radio como estrategia educativa. Lenguajes audiovisuales.',
                    'cv' => 'https://drive.google.com/file/d/1hLARwCegcayJVnw0eODFTes_JQKJCdK0/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Fabricio Enrique Rivas Marmanillo',
                    'cargo' => 'Docente formador',
                    'grado' => 'Profesor de Lengua Extranjera: InglÃ©s',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'InglÃ©s para Principiantes I / Beginner English I (A1)<br>InglÃ©s para principiantes III / Beginner English III (A1)<br>InglÃ©s para Principiantes / Beginner English I A1<br>InglÃ©s para Principiantes / Beginner English III A2',
                    'cursos_investigacion' => 'EnseÃ±anza de Lengua Extranjera (InglÃ©s)<br>DiseÃ±o de recursos educomunicativos (comics)',
                    'cv' => 'https://drive.google.com/file/d/1ZySGKbNDBzzxik4h_0R7HDGNLeGEtPmf/view?usp=drive_link',
                ],
                [
                    'nombre' => 'Hernan Sullca Tito',
                    'cargo' => 'Docente formador',
                    'grado' => 'Licenciado en Historia / MaestrÃa concluida',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'ConstrucciÃ³n de la Identidad y Ejercicio de la CiudadanÃa.<br>Ãtica y FilosofÃa para el Pensamiento CrÃtico<br>Ãtica y FilosofÃa para el Pensamiento CrÃtico',
                    'cursos_investigacion' => 'EducaciÃ³n comunitaria, antropologÃa y estudios andinos<br>Radio como estrategia educativa<br>Territorio, identidad y educaciÃ³n<br>EducaciÃ³n Primaria Intercultural BilingÃ¼e<br>Quechua hablante',
                    'cv' => 'https://drive.google.com/file/d/13kV07n0Pv24HnDDG8ST_EpslYWsT7hdB/view?usp=drive_link',
                ],
                [
                    'nombre' => 'SofÃa Juana Kancha Latorre',
                    'cargo' => 'Docente formador',
                    'grado' => 'Profesora de EducaciÃ³n Primaria',
                    'programas' => 'EducaciÃ³n Inicial y EducaciÃ³n Primaria Intercultural BilingÃ¼e',
                    'cursos_dicta' => 'Electivo I: Identidad e interculturalidad a travÃ©s del estudio de actividades tradicionales<br>Electivo III: Identidad e interculturalidad a travÃ©s del estudio de actividades tradicionales',
                    'cursos_investigacion' => 'EducaciÃ³n Primaria Intercultural BilingÃ¼e<br>DidÃ¡ctica de Segundas Lenguas, BilingÃ¼ismo, oficios comunitarios y Saberes Ancestrales<br>PolÃticas y GestiÃ³n Educativa<br>Traductora intÃ©rprete<br>PlanificaciÃ³n Curricular EIB<br>DidÃ¡ctica y orientaciÃ³n de la prÃ¡ctica preprofesional<br>Quechua hablante',
                    'cv' => 'https://drive.google.com/file/d/1pAYCpnr-HYb7_mjXHXRZQ1JlrmXWqXL4/view?usp=drive_link',
                ],
            ],
        ];

        return view('foot.plana-docente', compact('categorias'));
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
