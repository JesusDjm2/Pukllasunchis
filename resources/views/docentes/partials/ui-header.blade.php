{{-- Cabecera unificada del panel docente: $title (requerido), $kicker, $subtitle, $backUrl, $backLabel, $competencias (opcional, Collection) --}}
@php
    $kicker = $kicker ?? 'Área docente';
    $backLabel = $backLabel ?? 'Volver';
    $competencias = $competencias ?? null;
    $rightExtra = $rightExtra ?? null;
@endphp
<style>
    .docente-ui-competencias-label {
        display: block;
        font-size: 0.7rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        font-weight: 700;
        color: #858796;
        margin-bottom: 0.4rem;
    }

    .docente-ui-competencias-label .docente-ui-competencias-hint {
        text-transform: none;
        font-weight: 500;
        letter-spacing: normal;
        color: #a7abba;
    }

    .docente-cal-competencias {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .docente-cal-competencias a {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: #eef1fb;
        border: 1px solid rgba(78, 115, 223, 0.25);
        color: #2e3f8f !important;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none !important;
        cursor: pointer;
        transition: transform .15s ease, background-color .15s ease, box-shadow .15s ease;
    }

    .docente-cal-competencias a:hover,
    .docente-cal-competencias a:focus {
        background: #dde3fa;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.28);
        outline: none;
    }

    .docente-cal-competencias a .docente-cal-comp-icon {
        color: #6f80c9;
        font-size: 0.85rem;
    }

    body.dark-mode .docente-ui-competencias-label .docente-ui-competencias-hint,
    body.dim-mode .docente-ui-competencias-label .docente-ui-competencias-hint {
        color: #6b7280;
    }

    .docente-cal-legenda-mini {
        display: inline-block;
        text-align: left;
        font-size: 0.72rem;
        line-height: 1.55;
    }

    .docente-cal-legenda-titulo {
        font-size: 0.65rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 700;
        color: #858796;
        margin-bottom: 0.15rem;
    }

    .docente-cal-legenda-item {
        display: block;
        font-weight: 600;
    }

    .docente-cal-legenda-item strong {
        display: inline-block;
        min-width: 2.6rem;
    }

    @media (max-width: 767.98px) {
        .docente-cal-legenda-mini {
            text-align: left;
        }
    }
</style>
<div class="card docente-ui-card docente-ui-hero mb-3 mb-md-4 docente-ui-toolbar-card">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between docente-ui-toolbar">
            <div class="mb-2 mb-md-0 pr-md-3 flex-grow-1 min-w-0">
                <p class="docente-ui-kicker mb-1">{{ $kicker }}</p>
                <h1 class="docente-ui-title mb-0">{{ $title }}</h1>
                @if (!empty($subtitle))
                    <p class="docente-ui-subtitle mb-0 mt-2">{{ $subtitle }}</p>
                @endif
                @if ($competencias && $competencias->isNotEmpty())
                    <div class="mt-3">
                        <span class="docente-ui-competencias-label">
                            Competencias a calificar
                            <span class="docente-ui-competencias-hint">— toca una para ver el detalle</span>
                        </span>
                        <div class="docente-cal-competencias">
                            @foreach ($competencias as $competencia)
                                <a data-id="{{ $competencia->id }}" data-nombre="{{ $competencia->nombre }}"
                                    data-descripcion="{{ addslashes($competencia->descripcion) }}"
                                    data-capacidades="{!! addslashes($competencia->capacidades) !!}"
                                    onclick="openModal(this)"
                                    title="Toca para ver la descripción completa">
                                    {{ $competencia->nombre }}
                                    <i class="fas fa-question-circle docente-cal-comp-icon"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @if (!empty($backUrl) || !empty($rightExtra))
                <div class="flex-shrink-0 text-md-right">
                    @if (!empty($backUrl))
                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm btn-block d-md-inline-block">
                            <i class="fas fa-arrow-left mr-1"></i> {{ $backLabel }}
                        </a>
                    @endif
                    @if (!empty($rightExtra))
                        <div class="mt-2">{!! $rightExtra !!}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
