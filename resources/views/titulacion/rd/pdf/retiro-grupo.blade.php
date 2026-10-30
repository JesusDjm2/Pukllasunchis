{{-- RD de Retiro de un integrante del grupo: acepta su renuncia al PTI y modifica el artículo PRIMERO de
     la RD que lo aprobó. Texto del modelo oficial; variables desde ResolucionDirectoralController::datosPdf()
     ($aparte = quien se retira, $resto = quienes continúan, $todos = ambos). --}}
@extends('titulacion.rd.pdf.membrete')

@section('titulo', 'RD '.$numero.'-'.$anio.' Retiro de integrante del grupo')

@php
    $rd = 'Resolución Directoral Nº '.$rdReferida.'-DG/EESP-"PUKLLASUNCHIS"-CUSCO';
    $informeCompleto = $informe.'-'.$anio.'-CI-EESP-PUKLLASUNCHIS';
    $sale = $aparte['g']['art'].' '.$aparte['g']['estudiante'];
    $queda = $resto['g']['art'].' '.$resto['g']['estudiante'];
    $de = fn (string $quien) => str_starts_with($quien, 'el ') ? 'del '.substr($quien, 3) : 'de '.$quien;
    $femResto = in_array($resto['g']['art'], ['la', 'las'], true);
    $coautor = $resto['varios'] ? ($femResto ? 'sus coautoras' : 'sus coautores') : ($femResto ? 'su coautora' : 'su coautor');
    $esAsesora = $asesorAnteriorCargo === 'Asesora';
    $hayAsesor = $asesorDesignado !== '';
    $delAsesor = $esAsesora ? 'de la docente asesora' : 'del docente asesor';
@endphp

@section('cuerpo')
    <div class="seccion">VISTO:</div>
    <p>
        El Informe Nº {{ $informeCompleto }} de la Coordinación de Investigación; la solicitud presentada por {{ $sale }}
        <strong>{{ $aparte['nombres'] }}</strong>; la conformidad presentada por {{ $queda }} <strong>{{ $resto['nombres'] }}</strong>
        del Programa de Estudios de {{ $programaCompleto }}; la {{ $rd }}; y demás documentos que se acompañan.
    </p>

    <div class="seccion">CONSIDERANDO:</div>
    <p>
        Que, mediante {{ $rd }}@if ($fechaRdReferida), de fecha {{ \App\Services\Titulacion\PlantillasRd::fechaLarga($fechaRdReferida) }} del {{ $fechaRdReferida->year }}@endif,
        se aprobó el Plan de Trabajo de Investigación denominado “{{ $titulo }}”, presentado por {{ $todos['g']['art'] }} {{ $todos['g']['estudiante'] }}
        {{ $todos['nombres'] }}, del Programa de Estudios de {{ $programaCompleto }}@if ($hayAsesor), designándose como
        {{ $esAsesora ? 'docente asesora a la profesora' : 'docente asesor al profesor' }} {{ $asesorDesignado }}@endif;
    </p>
    <p>
        Que, mediante expediente del visto, {{ $sale }} {{ $aparte['nombres'] }} solicita su renuncia voluntaria y desvinculación
        formal del referido Plan de Trabajo de Investigación, manifestando su desistimiento a los derechos sobre el tema y avances
        realizados a favor de {{ $coautor }}, con la debida conformidad {{ $de($queda) }} {{ $resto['nombres'] }}@if ($hayAsesor) y el visto bueno {{ $delAsesor }}@endif;
    </p>
    <p>
        Que, la Coordinación de Investigación, mediante Informe Nº {{ $informeCompleto }}, emite opinión favorable para la exclusión
        solicitada y recomienda la modificación de la {{ $rd }}, a efectos de que el Plan de Trabajo continúe bajo la titularidad
        {{ $resto['varios'] ? '' : 'individual ' }}{{ $de($queda) }} {{ $resto['nombres'] }};
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
        <strong>PRIMERO: ACEPTAR LA RENUNCIA VOLUNTARIA Y EXCLUSIÓN</strong> {{ $de($sale) }} {{ $aparte['mayus'] }}, del Plan de Trabajo
        de Investigación denominado “{{ $titulo }}”, aprobado mediante {{ $rd }}.
    </p>
    <p>
        <strong>SEGUNDO: MODIFICAR el ARTÍCULO PRIMERO</strong> de la {{ $rd }}@if ($fechaRdReferida), de fecha {{ \App\Services\Titulacion\PlantillasRd::fechaLarga($fechaRdReferida) }} del {{ $fechaRdReferida->year }}@endif,
        el cual quedará redactado en los términos siguientes:
    </p>
    <p style="font-style: italic;">
        “PRIMERO: APROBAR el Plan de Trabajo de Investigación denominado ‘{{ $titulo }}’ presentado {{ $resto['varios'] ? '' : 'de manera individual ' }}por
        {{ $queda }} {{ $resto['mayus'] }} del Programa de Estudios de {{ $programaCompleto }}.”
    </p>
    <p>
        <strong>TERCERO: RATIFICAR</strong> la vigencia de los Artículos Segundo, Tercero y Cuarto de la {{ $rd }} en todos sus demás
        extremos, manteniéndose @if ($hayAsesor) la designación {{ $delAsesor }} y @endif el plazo de vigencia establecido a partir de su aprobación original.
    </p>
    <p>
        <strong>CUARTO: DISPONER</strong> que Jefatura de la Unidad de Investigación y Secretaría Académica tomen conocimiento y notifiquen
        la presente resolución a {{ $todos['g']['interesado'] }}@if ($hayAsesor) y {{ $esAsesora ? 'a la docente asesora' : 'al docente asesor' }}@endif para los fines correspondientes.
    </p>
@endsection
