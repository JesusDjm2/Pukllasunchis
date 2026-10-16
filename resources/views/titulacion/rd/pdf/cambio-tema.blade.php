{{-- RD de Cambio de tema de investigación: anula la aprobación del PTI anterior y aprueba el
     nuevo. Texto del modelo oficial; variables desde ResolucionDirectoralController::datosPdf(). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Cambio de tema')

@php
    $quien = $g['art'].' '.$g['estudiante'];
    $rd = 'Resolución Directoral Nº '.$rdReferida.'-DG/EESP-“PUKLLASUNCHIS”-CUSCO';
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        La solicitud
        @if ($expedientes !== '') y {{ $variosExpedientes ? 'Expedientes' : 'Expediente' }} Nº {{ $expedientes }} @endif
        presentado{{ $variosExpedientes ? 's' : '' }} por {{ $quien }} <strong>{{ $nombres }}</strong> de la Escuela de Educación Superior Pedagógica
        Pukllasunchis, solicitando el cambio de tema del Plan de Trabajo de Investigación (PTI) aprobado mediante
        {{ $rd }}, así como la propuesta de un nuevo tema para su correspondiente aprobación.
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que mediante RD Nº {{ $rdReferida }}-DG/EESP-“PUKLLASUNCHIS”-CUSCO se aprobó el Plan de Trabajo de Investigación
        de {{ $quien }} <strong>{{ $nombres }}</strong> con tema que ahora requiere ser modificado por razones académicas
        y personales justificadas.
    </p>
    <p>
        Que, {{ $quien }} {{ $g['ha'] }} presentado su solicitud formal, sustentando la necesidad de modificar el tema de
        investigación, la cual ha sido revisada y avalada por el asesor asignado y el Comité de Investigación de la
        institución.
    </p>
    <p>
        Que, la Escuela de Educación Superior Pedagógica Pukllasunchis promueve la investigación pertinente y
        contextualizada, garantizando que los temas de investigación respondan a las necesidades académicas y
        formativas de sus estudiantes.
    </p>
    <p>
        Que, de conformidad con los Lineamientos Académicos Generales (LAG) para las Escuelas de Educación Superior
        Pedagógica Públicas y Privadas, Ley Nº 30512, su Reglamento, la Resolución Viceministerial Nº 178-2018-MINEDU,
        y el Reglamento de Investigación RD Nº 067-2021-DG/EESP-PUKLLASUNCHIS y demás normas conferidas;
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO:</strong> Anular la aprobación del Plan de Trabajo de Investigación (PTI) de {{ $quien }}
        <strong>{{ $nombres }}</strong>, correspondiente al tema aprobado mediante RD Nº {{ $rdReferida }}-DG/EESP-“PUKLLASUNCHIS”-CUSCO.
    </p>
    <p>
        <strong>SEGUNDO: APROBAR</strong> el nuevo Plan de Trabajo de Investigación (PTI) de {{ $quien }} titulado:
        <strong>“{{ $tituloNuevo }}”</strong>.
    </p>
    <p>
        <strong>TERCERO:</strong> Disponer que el Comité de Investigación y los asesores asignados brinden el
        acompañamiento necesario para la ejecución del nuevo Plan de Trabajo de Investigación, conforme a los
        lineamientos institucionales y normativos vigentes.
    </p>
    <p>
        <strong>CUARTO:</strong> Encargar a la Unidad de Secretaría Académica la notificación de la presente resolución
        {{ $g['a_art'] }} {{ $g['estudiante'] }}, al Comité de Investigación y a los asesores asignados para los fines
        correspondientes.
    </p>
@endsection
