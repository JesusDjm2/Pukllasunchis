{{-- RD de Aprobación del Plan de Trabajo de Investigación (FID y PPD). Texto del modelo
     oficial de la institución; variables desde ResolucionDirectoralController::datosPdf()
     ($g = palabras que concuerdan con los integrantes, ver PlantillasRd::gramatica()). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Aprobación de PTI')

@php
    $quien = $g['art'].' '.$g['estudiante'];
    $esAsesora = $asesorCargo === 'Asesora';
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        El informe Nº {{ $informe }}-{{ $anio }}-CI-EESP-PUKLLASUNCHIS de la Coordinación de Investigación
        @if ($expedientes !== '')
            y {{ $variosExpedientes ? 'los expedientes' : 'el expediente' }} {{ $expedientes }}
        @endif
        presentado por {{ $quien }} <strong>{{ $nombres }}</strong> del Programa de Estudios de
        {{ $programa }} y demás documentos que se acompañan.
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que habiendo informado la Coordinación de Investigación del Plan de Trabajo de Investigación
        denominado "<em>{{ $titulo }}</em>", presentado por {{ $quien }} <strong>{{ $nombres }}</strong>
        del Programa de Estudios de {{ $programa }} con opinión APTO para aprobar el Plan de Trabajo de
        Investigación y solicitar asignación de Asesor para trabajo de investigación;
    </p>
    <p>
        Que, de conformidad con los Lineamientos Académicos Generales (LAG) para las Escuelas de Educación
        Superior Pedagógica Públicas y Privadas, la Ley N° 30512, su Reglamento, la Resolución Viceministerial
        N° 178-2018-MINEDU, el Reglamento de Investigación actualizado con Resolución Directoral
        N° 035-2025-DG/EESP-PUKLLASUNCHIS y demás normas conferidas;
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO: APROBAR</strong> el Plan de Trabajo de Investigación denominado
        <strong><em>“{{ $titulo }}”</em></strong> presentado por {{ $quien }} <strong>{{ $nombres }}</strong>.
    </p>
    <p>
        <strong>SEGUNDO: DESIGNACIÓN</strong> como {{ $asesorCargo }} para trabajo de investigación
        {{ $esAsesora ? 'a la' : 'al' }} <strong>{{ $asesorNombre }}</strong>; quien brindará orientación
        académica y metodológica durante el desarrollo del trabajo de investigación hasta su culminación.
        @if ($textoAdicional !== '') {{ $textoAdicional }} @endif
    </p>
    <p>
        <strong>TERCERO:</strong> ESTABLECER que la presente Resolución tiene una vigencia de dos (2) años a
        partir de la fecha de su aprobación. Durante dicho período, {{ $quien }} deberá{{ $g['n'] }}
        culminar el desarrollo del Trabajo de Investigación para la obtención del grado de Bachiller y de la
        Tesis para la obtención del Título de Licenciatura. Vencido dicho plazo, el Plan de Trabajo perderá
        vigencia automáticamente.
    </p>
    <p>
        <strong>CUARTO: DISPONER,</strong> que la Jefatura de la Unidad de Investigación dé a conocer
        {{ $esAsesora ? 'a la docente asesora' : 'al docente asesor' }} y {{ $g['a_art'] }} {{ $g['estudiante'] }} para que den
        cumplimiento a la presente resolución.
    </p>
@endsection
