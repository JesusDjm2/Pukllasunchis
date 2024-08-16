<?php

namespace App\Http\Controllers;

use App\Models\Postulante;
use Illuminate\Http\Request;

class EnlacesController extends Controller
{
    public function nosotros()
    {
        return view('nosotros');
    }
    //programas
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
        return view('programas.profesionalizacion-docente');
    }
    //Ordinario
    public function ordinario()
    {
        return view('admision.ingreso-ordinario');
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

    //Titulacion
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

    //Tramites
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

    //Líneas
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

    //Información
    public function novedades()
    {
        return view('informacion.informacion-novedades');
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
    public function bolsa()
    {
        $postulantes = Postulante::all();
        $postulantes1 = Postulante::where('programa_id', 1)->get();
        $postulantes2 = Postulante::where('programa_id', 2)->get();
        return view('informacion.bolsa-de-trabajo', compact('postulantes', 'postulantes1', 'postulantes2'));
    }

    //Foot
    public function informacion()
    {
        return view('foot.informacion-institucional');
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
