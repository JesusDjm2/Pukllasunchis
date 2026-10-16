{{-- RD de Anulación del Plan de Trabajo de Investigación. Texto del modelo oficial;
     variables desde ResolucionDirectoralController::datosPdf(). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Anulación de PTI')

@php
    $quien = $g['art'].' '.$g['estudiante'];
    $rd = 'Resolución Directoral N.º '.$rdReferida.'-DG/EESP-“Pukllasunchis”';
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        La solicitud presentada por {{ $quien }} <strong>{{ $nombres }}</strong>, del Programa de Estudios de
        <strong>{{ $programa }}</strong>, mediante la cual solicita{{ $g['n'] }} la <strong>anulación de la {{ $rd }}</strong>,
        que aprobó el Plan de Trabajo de Investigación titulado <strong>“{{ $titulo }}”</strong>, y demás documentos:
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que, mediante <strong>{{ $rd }}</strong>, se aprobó el Plan de Trabajo de Investigación titulado
        <strong><em>“{{ $titulo }}”</em></strong>, presentado por {{ $quien }} <strong>{{ $nombres }}</strong>
        del programa de estudios de {{ $programa }};
    </p>
    <p>
        Que, posteriormente, {{ $quien }} {{ $g['ha'] }} solicitado la <strong>anulación de dicha resolución</strong>
        por motivos académicos debidamente justificados, que implican la necesidad de modificar el Plan de
        Trabajo de Investigación previamente aprobado;
    </p>
    <p>
        Que, de acuerdo con los <strong>Lineamientos Académicos Generales (LAG)</strong> para las Escuelas de
        Educación Superior Pedagógica Públicas y Privadas, la <strong>Ley N.º 30512</strong>, su Reglamento, la
        <strong>Resolución Viceministerial N.º 178-2018-MINEDU</strong>, el <strong>Reglamento de Investigación</strong>
        vigente aprobado mediante <strong>Resolución Directoral N.º 035-2025-DG/EESP-“Pukllasunchis”</strong>, y demás
        normas complementarias, toda modificación sustancial de un plan de investigación requiere una nueva
        aprobación mediante resolución independiente;
    </p>
    <p>
        Estando a lo recomendado por la Coordinación de Investigación y en uso de las atribuciones conferidas a
        esta Dirección General;
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO: DEJAR SIN EFECTO</strong> la <strong>{{ $rd }}</strong>, que aprobó el Plan de Trabajo de
        Investigación titulado <strong><em>“{{ $titulo }}”</em></strong>, presentado por {{ $quien }}
        <strong>{{ $nombres }}</strong> del Programa de Estudios de <strong>{{ $programa }}</strong>.
    </p>
    <p>
        <strong>SEGUNDO: DISPONER</strong> que la Coordinación de Investigación tome conocimiento y proceda al
        archivo del expediente correspondiente, dejando constancia de la presente anulación.
    </p>
    <p>
        <strong>TERCERO: AUTORIZAR</strong> {{ $g['a_art'] }} {{ $g['estudiante'] }} a presentar un nuevo expediente de
        Plan de Trabajo de Investigación actualizado, conforme a la normativa vigente.
    </p>
@endsection
