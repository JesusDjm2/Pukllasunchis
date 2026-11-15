{{-- RD de Cambio de tema de investigación: modifica ÚNICAMENTE la denominación del PTI aprobado y
     ratifica todo lo demás de esa RD. Texto del modelo oficial (nov. 2026); variables desde
     ResolucionDirectoralController::datosPdf(). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Cambio de tema')

{{-- Esta RD va siempre en UNA hoja: interlineado y espacios algo más ajustados que el resto. --}}
@section('estilos')
    body { line-height: 1.2; }
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
        Pedagógica Pukllasunchis, solicitando el cambio de tema del Plan de Trabajo de Investigación (PTI) aprobado mediante
        Resolución Directoral Nº {{ $rdReferida }}-DG/EESP-“PUKLLASUNCHIS”-CUSCO, así como la propuesta de un nuevo tema para su
        correspondiente aprobación.
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que mediante {{ $rdCorta }}@if ($fechaRdReferida), de fecha {{ \App\Services\Titulacion\PlantillasRd::fechaLarga($fechaRdReferida) }} del {{ $fechaRdReferida->year }}@endif,
        se aprobó el Plan de Trabajo de Investigación {{ $deQuien }} <strong>{{ $nombres }}</strong>;
    </p>
    <p>
        Que, mediante solicitud formal de visto, {{ $quien }} {{ $nombres }} sustenta{{ $g['n'] }} la necesidad de reorientar
        el tema de investigación por razones académicas y metodológicas justificadas; contando con la opinión favorable y aval
        del asesor asignado y la conformidad del Comité de Investigación Institucional;
    </p>
    <p>
        Que, de conformidad con los Lineamientos Académicos Generales (LAG) para las Escuelas de Educación Superior
        Pedagógica Públicas y Privadas, la Ley N.° 30512, su Reglamento, la Resolución Viceministerial N.° 178-2018-MINEDU,
        el Reglamento de Investigación actualizado con Resolución Directoral N.° 035-2025-DG/EESP-PUKLLASUNCHIS y demás
        normas conferidas;
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO: MODIFICAR ÚNICAMENTE</strong> la denominación del Plan de Trabajo de Investigación (PTI)
        {{ $deQuien }} <strong>{{ $nombres }}</strong>, el cual fue aprobado en el artículo primero de la {{ $rd }}; el cual,
        a partir de la emisión de la presente resolución, quedará redactado con el siguiente título:
        “<strong><em>{{ $tituloNuevo }}</em></strong>”.
    </p>
    <p>
        <strong>SEGUNDO: RATIFICAR</strong> e inalterables todos los demás extremos de la {{ $rd }}, manteniendo plenamente
        su vigencia las disposiciones relativas a la designación del docente asesor, los plazos de vigencia estipulados y las
        responsabilidades asignadas.
    </p>
    <p>
        <strong>TERCERO: DISPONER</strong> que la Jefatura de la Unidad de Investigación dé a conocer la presente resolución
        al docente asesor y {{ $g['a_art'] === 'al' ? 'al estudiante' : 'a '.$alumnos }} para que den estricto cumplimiento a la misma.
    </p>
@endsection
