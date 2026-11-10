{{-- RD de Extensión de plazo de Plan de Trabajo de Investigación (PTI).
     Texto base preparado a la espera del modelo oficial definitivo;
     variables provistas desde ResolucionDirectoralController::datosPdf(). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Extensión de plazo de PTI')

@section('estilos')
    body { line-height: 1.25; }
    .lema, .titulo-rd, .fecha-rd { margin-bottom: .45em; }
    .seccion { margin: .35em 0 .2em; }
    p { margin-bottom: .3em; }
    .firma { margin-top: .45em; }
@endsection

@php
    $art = $g['art'];
    $quien = $art.' '.$g['estudiante'];
    $de = match ($art) { 'el' => 'del', 'la' => 'de la', 'los' => 'de los', default => 'de las' };
    $deQuien = $de.' '.$g['estudiante'];
    $rd = 'Resolución Directoral N.° '.$rdReferida.'-DG/EESP-“PUKLLASUNCHIS”-CUSCO';
    $rdCorta = 'RD Nº '.$rdReferida.'-DG/EESP-“PUKLLASUNCHIS”-CUSCO';
    $femenino = in_array($art, ['la', 'las'], true);
    $alumnos = $g['plural'] ? ($femenino ? 'las estudiantes' : 'los estudiantes') : ($femenino ? 'la estudiante' : 'el estudiante');
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        La solicitud
        @if ($expedientes !== '') y {{ $variosExpedientes ? 'expedientes' : 'expediente' }} Nº {{ $expedientes }} @endif
        presentado{{ $variosExpedientes ? 's' : '' }} por {{ $quien }} <strong>{{ $nombres }}</strong> de la Escuela de Educación Superior
        Pedagógica Pukllasunchis, solicitando la extensión de plazo para el desarrollo y culminación del Plan de Trabajo de Investigación (PTI)
        aprobado mediante Resolución Directoral Nº {{ $rdReferida }}-DG/EESP-“PUKLLASUNCHIS”-CUSCO;
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que mediante {{ $rdCorta }}@if ($fechaRdReferida), de fecha {{ \App\Services\Titulacion\PlantillasRd::fechaLarga($fechaRdReferida) }} del {{ $fechaRdReferida->year }}@endif,
        se aprobó el Plan de Trabajo de Investigación titulado “<strong><em>{{ $titulo }}</em></strong>” {{ $deQuien }} <strong>{{ $nombres }}</strong>;
    </p>
    <p>
        Que, mediante solicitud formal de visto, {{ $quien }} {{ $nombres }} sustenta{{ $g['n'] }} la solicitud de ampliación de plazo para la
        ejecución del Plan de Trabajo de Investigación por razones académicas justificadas, contando con la conformidad del docente asesor y la opinión favorable del área de investigación;
    </p>
    <p>
        Que, de conformidad con los Lineamientos Académicos Generales (LAG) para las Escuelas de Educación Superior
        Pedagógica Públicas y Privadas, la Ley N.° 30512, su Reglamento, el Reglamento de Investigación institucional y demás
        normas pertinentes;
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO: CONCEDER LA EXTENSIÓN DE PLAZO</strong>
        @if ($plazoAmpliacion !== '') por el término de <strong>{{ $plazoAmpliacion }}</strong> @endif
        para la ejecución y culminación del Plan de Trabajo de Investigación (PTI) titulado “<strong><em>{{ $titulo }}</em></strong>”,
        {{ $deQuien }} <strong>{{ $nombres }}</strong>@if ($nuevaFechaLimite), estableciéndose como nueva fecha de vencimiento el {{ \App\Services\Titulacion\PlantillasRd::fechaLarga($nuevaFechaLimite) }} del {{ $nuevaFechaLimite->year }}@endif.
    </p>
    <p>
        <strong>SEGUNDO: RATIFICAR</strong> e inalterables todos los demás extremos de la {{ $rd }}, manteniendo plenamente
        su vigencia las demás disposiciones, responsabilidades y la designación del docente asesor.
    </p>
    <p>
        <strong>TERCERO: DISPONER</strong> que la Jefatura de la Unidad de Investigación dé a conocer la presente resolución
        al docente asesor y {{ $g['a_art'] === 'al' ? 'al estudiante' : 'a '.$alumnos }} para su debido cumplimiento.
    </p>
@endsection
