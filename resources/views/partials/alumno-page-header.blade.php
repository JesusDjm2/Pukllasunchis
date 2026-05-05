{{--
    Encabezado unificado área estudiante.
    Variables: $title (requerido), $subtitle (opcional), $actions (opcional HTML)
--}}
@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
@endphp
<header class="alumno-page-header">
    <div class="alumno-page-header-text">
        <h1 class="alumno-page-title">{{ $title }}</h1>
        @if (!empty($subtitle))
            <p class="alumno-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if (!empty($actions))
        <div class="alumno-page-actions">
            {!! $actions !!}
        </div>
    @endif
</header>
