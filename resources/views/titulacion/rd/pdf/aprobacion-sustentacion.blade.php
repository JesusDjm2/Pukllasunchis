{{-- RD de Aprobación de la sustentación de tesis (título profesional, FID y PPD). Texto del
     modelo oficial; variables desde ResolucionDirectoralController::datosPdf(). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Aprobación de sustentación')

@php
    $quien = $g['art'].' '.$g['bachiller'];
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        @if ($expedientes !== '')
            {{ $variosExpedientes ? 'Los expedientes' : 'El expediente' }} {{ $expedientes }}, presentado{{ $variosExpedientes ? 's' : '' }} por
        @else
            La solicitud presentada por
        @endif
        {{ $quien }} <strong>{{ $nombres }}</strong>, del Programa de Estudios de <strong>{{ $programa }}</strong> de la
        Escuela de Educación Superior Pedagógica Privada “Pukllasunchis”, referidos a la sustentación de la tesis
        titulada: <strong>“</strong><em>{{ $titulo }}</em><strong>”</strong> para la obtención del título profesional y;
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que, conforme a lo establecido en el artículo 3.2.4.1 del Lineamiento Académico General aprobado mediante
        <strong>R.M. N.º 441-2019-MINEDU</strong>, así como en concordancia con lo dispuesto en los artículos 47 y 48 de la
        <strong>Ley N.º 30512</strong> y su Reglamento aprobado por <strong>D.S. N.º 010-2017-MINEDU</strong>, la obtención
        del título profesional de Licenciado en Educación requiere, entre otros requisitos, la sustentación y
        aprobación de una tesis u otra modalidad autorizada ante un jurado evaluador debidamente designado por la
        institución;
    </p>
    <p>
        Que, mediante <strong>Resolución Directoral {{ $rdReferida }}-DG/EESP-PUKLLASUNCHIS-CUSCO</strong>, se designó al
        jurado evaluador encargado de calificar la sustentación de tesis;
    </p>
    <p>
        Que, de acuerdo con el Acta de Sustentación N.º {{ $acta }}, de fecha {{ $fechaActo?->format('d/m/Y') }}, el jurado
        calificador otorgó el resultado de la sustentación como <strong>“APTA”</strong>, presentada por {{ $quien }}
        <strong>{{ $nombres }}</strong>, en reconocimiento al cumplimiento de los criterios establecidos por la normativa
        institucional y nacional;
    </p>
    <p>
        De conformidad con las atribuciones conferidas al Director General por el Reglamento de Organización y
        Funciones de la EESP Pukllasunchis;
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO: APROBAR la sustentación de tesis</strong> titulada “<em>{{ $titulo }}</em>”, presentada por
        {{ $quien }} <strong>{{ $nombres }}</strong>, como parte del proceso para la <strong>obtención del título profesional
        de {{ $g['licenciado'] }} en {{ $programa }}</strong>, habiendo obtenido el calificativo de <strong>“APTO”</strong> por
        parte del jurado evaluador.
    </p>
    <p>
        <strong>SEGUNDO: DISPONER</strong> la transcripción de la presente resolución a la Unidad Académica y su
        notificación {{ str_replace(['la ', 'el ', 'las ', 'los '], ['a la ', 'al ', 'a las ', 'a los '], $g['interesado'].' ') }}para
        los fines administrativos correspondientes.
    </p>
@endsection
