{{-- RD de Incorporación de un integrante al grupo: aprueba su inclusión y modifica el artículo PRIMERO de
     la RD que aprobó el PTI. Texto del modelo oficial; variables desde ResolucionDirectoralController::datosPdf()
     ($aparte = quien se incorpora, $resto = el grupo que lo recibe, $todos = ambos). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Incorporación de integrante al grupo')

@php
    $rd = 'Resolución Directoral Nº '.$rdReferida.'-DG/EESP-"PUKLLASUNCHIS"-CUSCO';
    $informeCompleto = $informe.'-'.$anio.'-CI-EESP-PUKLLASUNCHIS';
    $entra = $aparte['g']['art'].' '.$aparte['g']['estudiante'];
    $grupo = $resto['g']['art'].' '.$resto['g']['estudiante'];
    $de = fn (string $quien) => str_starts_with($quien, 'el ') ? 'del '.substr($quien, 3) : 'de '.$quien;
    $femEntra = in_array($aparte['g']['art'], ['la', 'las'], true);
    $varios = $aparte['varios'];
    $esAsesora = $asesorAnteriorCargo === 'Asesora';
    $hayAsesor = $asesorDesignado !== '';
    $delAsesor = $esAsesora ? 'de la docente asesora' : 'del docente asesor';
    // «como tercera integrante»: según cuántos había en el grupo.
    $ordinales = $femEntra ? [2 => 'segunda', 3 => 'tercera', 4 => 'cuarta', 5 => 'quinta'] : [2 => 'segundo', 3 => 'tercer', 4 => 'cuarto', 5 => 'quinto'];
    $lugar = $ordinales[substr_count($resto['nombres'], ',') + 1] ?? ($femEntra ? 'nueva' : 'nuevo');
    $inclusion = $varios
        ? ($femEntra ? 'de estas últimas como nuevas integrantes' : 'de estos últimos como nuevos integrantes')
        : ($femEntra ? 'de esta última' : 'de este último').' como '.$lugar.' integrante';
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        El Informe Nº {{ $informeCompleto }} de la Coordinación de Investigación;
        @if ($expedientes !== '')
            {{ $variosExpedientes ? 'los Expedientes' : 'el Expediente' }} Nº {{ $expedientes }} presentado{{ $variosExpedientes ? 's' : '' }}
        @else
            la solicitud presentada
        @endif
        conjuntamente por {{ $todos['g']['art'] }} {{ $todos['g']['estudiante'] }} <strong>{{ $todos['nombres'] }}</strong>, del Programa de Estudios de
        {{ $programaCompleto }}{{ $esPpd ? ' (PPD)' : '' }}; la {{ $rd }}; y demás documentos que se acompañan.
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que, mediante {{ $rd }}, se aprobó el Plan de Trabajo de Investigación denominado “{{ $titulo }}”, presentado inicialmente por
        {{ $grupo }} {{ $resto['nombres'] }} del Programa de Estudios de {{ $programaCompleto }};
    </p>
    <p>
        Que, mediante expediente del visto, {{ $grupo }} {{ $resto['nombres'] }}, conjuntamente con {{ $entra }} {{ $aparte['nombres'] }},
        solicitan la inclusión formal {{ $inclusion }} del equipo de investigación para el desarrollo del referido Plan de Trabajo{{ $hayAsesor ? ', contando con el visto bueno '.$delAsesor.' '.($esAsesora ? 'asignada' : 'asignado') : '' }};
    </p>
    <p>
        Que, la Coordinación de Investigación, a través del Informe Nº {{ $informeCompleto }}, emite opinión favorable respecto a la
        incorporación solicitada, verificando que se cumple con los requisitos normativos y el límite máximo de integrantes estipulado
        para los trabajos de investigación {{ $esPpd ? 'del Programa de Profesionalización Docente' : 'de la Formación Inicial Docente' }};
    </p>
    <p>
        Que, de conformidad con los Lineamientos Académicos Generales (LAG) para las Escuelas de Educación Superior Pedagógica Públicas
        y Privadas, la Ley Nº 30512, Ley de Institutos y Escuelas de Educación Superior y de la Carrera Pública de sus Docentes, su
        Reglamento, la Resolución Viceministerial Nº 178-2018-MINEDU, el Reglamento de Investigación actualizado con Resolución
        Directoral Nº {{ $reglamento }}, el Texto Único Ordenado de la Ley Nº 27444, Ley del Procedimiento Administrativo General,
        y demás normas conferidas;
    </p>

    <div class="seccion">SE RESUELVE:</div>
    <p>
        <strong>PRIMERO: APROBAR LA INCORPORACIÓN</strong> {{ $de($entra) }} {{ $aparte['mayus'] }}, como {{ $varios ? 'integrantes' : 'integrante' }} del equipo de
        investigación del Plan de Trabajo denominado <em>“{{ $titulo }}”</em>, aprobado mediante <strong>{{ $rd }}</strong>.
    </p>
    <p>
        <strong>SEGUNDO: MODIFICAR el ARTÍCULO PRIMERO</strong> de la {{ $rd }}, el cual quedará redactado en los términos siguientes:
    </p>
    <p style="font-style: italic;">
        "PRIMERO: APROBAR el Plan de Trabajo de Investigación denominado ‘{{ $titulo }}’ presentado por {{ $todos['g']['art'] }} {{ $todos['g']['estudiante'] }}
        {{ $todos['mayus'] }} del Programa de Estudios de {{ $programaCompleto }}."
    </p>
    <p>
        <strong>TERCERO: RATIFICAR</strong> la vigencia de los demás extremos de la {{ $rd }}, manteniéndose @if ($hayAsesor) la designación {{ $delAsesor }} y @endif
        el cómputo de la vigencia establecido en dicha resolución original.
    </p>
    <p>
        <strong>CUARTO: DISPONER</strong> que Jefatura de la Unidad de Investigación y Secretaría Académica tomen conocimiento y notifiquen
        la presente resolución a {{ $todos['g']['art'] }} {{ $todos['g']['estudiante'] }} {{ \Illuminate\Support\Str::after($todos['g']['interesado'], ' ') }}@if ($hayAsesor) y {{ $esAsesora ? 'a la docente asesora' : 'al docente asesor' }}@endif para los fines pertinentes.
    </p>
@endsection
