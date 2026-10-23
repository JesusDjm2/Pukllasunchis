{{-- RD de Cambio de asesor: modifica el artículo SEGUNDO de la RD que aprobó el PTI y designa al
     nuevo asesor. Texto del modelo oficial; variables desde ResolucionDirectoralController::datosPdf(). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Cambio de asesor')

@php
    $quien = $g['art'].' '.$g['estudiante'];
    $femenino = in_array($g['art'], ['la', 'las'], true);
    $involucrados = ($femenino ? 'involucrada' : 'involucrado').($g['plural'] ? 's' : '');
    $rd = 'Resolución Directoral N.º '.$rdReferida.'-DG/EESP-“PUKLLASUNCHIS”-CUSCO';
    $anterior = ($asesorAnteriorCargo === 'Asesora' ? 'de la docente' : 'del docente');
    $comoAnterior = $asesorAnteriorCargo === 'Asesora' ? 'asesora' : 'asesor';
    $esAsesora = $asesorCargo === 'Asesora';
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        @if ($expedientes !== '')
            {{ $variosExpedientes ? 'Los expedientes' : 'El expediente' }} Nº {{ $expedientes }} presentado{{ $variosExpedientes ? 's' : '' }}
        @else
            La solicitud presentada
        @endif
        por {{ $quien }} <strong>{{ $nombres }}</strong> del Programa de Estudios de {{ $programa }}, en el que
        solicita{{ $g['n'] }} la modificación del asesor asignado para su trabajo de investigación aprobado mediante
        {{ $rd }}; y.
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que, mediante {{ $rd }}@if ($fechaRdReferida), de fecha {{ \App\Services\Titulacion\PlantillasRd::fechaLarga($fechaRdReferida) }} del {{ $fechaRdReferida->year }}@endif,
        se aprobó el plan de trabajo de investigación titulado: <strong>{{ $titulo }}</strong>, así como la asignación
        {{ $anterior }} {{ $asesorAnterior }} como {{ $comoAnterior }} de dicho trabajo;
    </p>
    <p>
        Que, por razones académicas y de mutuo acuerdo entre las partes, {{ $quien }} {{ $g['ha'] }} solicitado el cambio
        de asesor, habiendo coordinado previamente con {{ $esAsesora ? 'la nueva asesora propuesta' : 'el nuevo asesor propuesto' }},
        quien ha aceptado asumir dicha función;
    </p>
    <p>
        Que, corresponde emitir el acto resolutivo que formalice dicha modificación, en concordancia con los Lineamientos
        Académicos Generales para las EESP y el Reglamento de Investigación vigente;
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO: MODIFICAR</strong> el artículo SEGUNDO de la {{ $rd }}, en lo referente a la designación del
        asesor de investigación.
    </p>
    <p>
        <strong>SEGUNDO: DESIGNAR</strong> como {{ $esAsesora ? 'nueva asesora' : 'nuevo asesor' }} del trabajo de investigación de
        {{ $quien }} <strong>{{ $nombres }}</strong>, {{ $esAsesora ? 'a la docente' : 'al docente' }} <strong>{{ $asesorNombre }}</strong>;
        quien brindará orientación académica y metodológica durante el desarrollo del trabajo de investigación hasta su
        culminación{{ $acompanante !== '' ? '; '.$acompanante : '' }}.
    </p>
    <p>
        <strong>TERCERO:</strong> MANTENER vigente la aprobación del plan de trabajo de investigación aprobado mediante la
        citada resolución.
    </p>
    <p>
        <strong>CUARTO:</strong> ENCARGAR a la Coordinación de Investigación la notificación correspondiente
        {{ $esAsesora ? 'a la docente asesora' : 'al docente asesor' }} y {{ $g['a_art'] }} {{ $g['estudiante'] }} {{ $involucrados }}
        para el cumplimiento de la presente resolución.
    </p>
@endsection
