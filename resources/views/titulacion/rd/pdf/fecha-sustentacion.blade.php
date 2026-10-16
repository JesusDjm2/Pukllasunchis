{{-- RD que declara aptos para sustentar, fija la fecha de sustentación de tesis y conforma
     el jurado. Texto del modelo oficial; variables desde ResolucionDirectoralController::datosPdf(). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Fecha de sustentación')

@php
    $quien = $g['art'].' '.$g['bachiller'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $diaSustentacion = $fechaActo ? str_pad($fechaActo->day, 2, '0', STR_PAD_LEFT).' de '.$meses[$fechaActo->month - 1].' del '.$fechaActo->year : '';
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        @if ($expedientes !== '')
            {{ $variosExpedientes ? 'Los expedientes' : 'El expediente' }} N.º {{ $expedientes }}, así como el último dictamen
            de revisión de tesis y demás documentos.
        @else
            El último dictamen de revisión de tesis y demás documentos.
        @endif
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que, de conformidad con los Lineamientos Académicos Generales (LAG) para las Escuelas de Educación Superior
        Pedagógica Públicas y Privadas, Ley N.º 30512, su Reglamento, la Resolución Viceministerial
        N.º 178-2018-MINEDU, y el Reglamento de Investigación aprobado mediante la Resolución Directoral
        N.º 067-2021-DG/EESP-“Pukllasunchis”;
    </p>
    <p>
        Que, {{ $quien }} <strong>{{ $nombres }}</strong>, del programa de <strong>{{ $programa }}</strong>,
        {{ $g['ha'] }} cumplido con los requisitos establecidos para la sustentación de tesis;
    </p>
    <p>
        Que, la presentación de la tesis titulada "{{ $titulo }}" cumple con los requisitos académicos y normativos
        establecidos por la EESP "Pukllasunchis"; en uso de las facultades conferidas al Director General por la
        normativa vigente.
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO:</strong> Declarar {{ $g['apto'] }} para la sustentación de tesis {{ $g['a_art'] }} {{ $g['bachiller'] }}
        <strong>{{ $nombres }}</strong>, del programa de estudios de {{ $programa }}.
    </p>
    <p>
        <strong>SEGUNDO:</strong> Establecer que la fecha de sustentación de tesis para {{ $quien }} de la EESPP
        "Pukllasunchis" será el día {{ $diaSustentacion }}, a partir de las {{ $hora }} horas, {{ $lugar }}.
    </p>
    <p>
        <strong>TERCERO:</strong> Disponer que se conforme el Jurado Evaluador para la sustentación de tesis:
    </p>
    @foreach ($jurado as [$cargo, $persona])
        <p class="sin-sangria" style="margin-bottom:.15em;"><strong>{{ $cargo }}{{ $cargo !== '' ? ':' : '' }}</strong> {{ $persona }}</p>
    @endforeach
    <p style="margin-top:.45em;">
        <strong>CUARTO:</strong> Solicitar a la Secretaría Académica de la EESPP "Pukllasunchis" que tome las medidas
        necesarias para garantizar la adecuada organización durante el desarrollo de la sustentación de tesis.
    </p>
    <p>
        <strong>QUINTO:</strong> Comunicar la presente resolución a todas las partes interesadas para su conocimiento y
        cumplimiento.
    </p>
@endsection
