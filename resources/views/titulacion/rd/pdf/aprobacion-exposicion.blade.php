{{-- RD de Aprobación del Trabajo de Investigación (exposición del TI, grado de Bachiller).
     Texto del modelo oficial; variables desde ResolucionDirectoralController::datosPdf(). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Aprobación de Exposición')

@php
    $quien = $g['art'].' '.$g['egresado'];
    $mayus = fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)).mb_substr($s, 1);
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        @if ($expedientes !== '')
            {{ $variosExpedientes ? 'Los expedientes' : 'El expediente' }} {{ $expedientes }}, presentado{{ $variosExpedientes ? 's' : '' }} por
        @else
            La solicitud presentada por
        @endif
        {{ $quien }} <strong>{{ $nombres }}</strong>, del Programa de Estudios de {{ $programa }},
        {{ $g['quien'] }} solicita{{ $g['n'] }} la aprobación de su Trabajo de Investigación para la obtención del grado
        académico de BACHILLER en EDUCACIÓN.
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que, mediante la Resolución Directoral N° 039-2025-DG/EESPP-PUKLLASUNCHIS, se aprobó la actualización del
        Reglamento de Investigación y Titulación de esta casa superior de estudios, el cual en el título primero,
        capítulo II, artículo 10, establece que el trabajo de investigación es la continuación del Plan de Trabajo
        de Investigación (PTI), y permite a los egresados de los programas de estudios obtener el grado de
        bachiller, conforme a lo estipulado en el numeral 3.2.3.4 de los Lineamientos Académicos Generales (LAG)
        establecidos por el Ministerio de Educación (MINEDU).
    </p>
    <p>
        Que, el trabajo de investigación titulado “<em>{{ $titulo }}</em>”, ha sido presentado de conformidad con
        las normas y requisitos establecidos por la EESP Pukllasunchis, y de acuerdo al acta de exposición
        registrada bajo el número de página {{ $acta }}, con fecha {{ $fechaActo?->format('d/m/Y') }}, obtuvo la
        calificación de <strong>APTA</strong> para la obtención del grado de <strong>Bachiller en Educación</strong>.
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO: APROBAR</strong> el Trabajo de Investigación titulado “<strong><em>{{ $titulo }}</em></strong>”,
        presentado por {{ $quien }} <strong>{{ $nombres }}</strong> para la obtención del grado académico de
        <strong>BACHILLER EN EDUCACIÓN.</strong>
    </p>
    <p>
        <strong>SEGUNDO:</strong> Disponer la transcripción de la presente resolución a la Unidad Académica, así como
        {{ str_replace(['la ', 'el ', 'las ', 'los '], ['a la ', 'al ', 'a las ', 'a los '], $g['interesado'].' ') }}para su
        conocimiento y fines correspondientes.
    </p>
@endsection
