{{-- Cabecera unificada del panel docente: $title (requerido), $kicker, $subtitle, $backUrl, $backLabel --}}
@php
    $kicker = $kicker ?? 'Área docente';
    $backLabel = $backLabel ?? 'Volver';
@endphp
<div
    class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between mb-3 pt-3 docente-ui-toolbar">
    <div class="mb-2 mb-md-0 pr-md-3 flex-grow-1 min-w-0">
        <p class="docente-ui-kicker mb-1">{{ $kicker }}</p>
        <h1 class="docente-ui-title mb-0">{{ $title }}</h1>
        @if (!empty($subtitle))
            <p class="docente-ui-subtitle mb-0 mt-2">{{ $subtitle }}</p>
        @endif
    </div>
    @if (!empty($backUrl))
        <div class="flex-shrink-0">
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm btn-block d-md-inline-block">
                <i class="fas fa-arrow-left mr-1"></i> {{ $backLabel }}
            </a>
        </div>
    @endif
</div>
