{{-- Tarjeta de solo lectura con el avance de titulación del propio
     alumno/egresado. Solo se incluye si ya tiene seguimiento_titulacion
     (alumno FID de Ciclo VIII+ al que se le inició el proceso, o egresado).
     Espera $seguimiento con pti.asesor, tesis.asesor, resoluciones y
     estadoTitulacion cargados. --}}
@php
    $pti   = $seguimiento->pti;
    $tesis = $seguimiento->tesis;
    $fmt   = fn ($f) => $f ? $f->format('d/m/Y') : null;
@endphp
<div class="card shadow-sm mb-3" id="titulacion">
    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem;">
        <span class="font-weight-bold"><i class="fas fa-user-graduate mr-2 text-primary"></i>Mi proceso de titulación</span>
        <span class="badge badge-info">{{ $seguimiento->estadoTitulacion->nombre ?? 'En proceso' }}</span>
    </div>
    <div class="card-body">
        @include('titulacion._stepper')

        <div class="row small">
            <div class="col-md-4 mb-3">
                <div class="text-uppercase text-muted font-weight-bold mb-1" style="font-size:.7rem;">① PTI</div>
                @if ($pti)
                    <div class="font-weight-bold">{{ $pti->titulo ?: 'Sin título registrado' }}</div>
                    @if ($pti->asesor) <div>Asesor(a): {{ $pti->asesor->name }} {{ $pti->asesor->apellidos }}</div> @endif
                    @if ($pti->rd_pti) <div>RD: {{ $pti->rd_pti }}</div> @endif
                    @if ($fmt($pti->fecha_pti)) <div>Fecha: {{ $fmt($pti->fecha_pti) }}</div> @endif
                    @if ($fmt($pti->fecha_vencimiento)) <div>Vence: {{ $fmt($pti->fecha_vencimiento) }}</div> @endif
                @else
                    <span class="text-muted">Aún no registrado.</span>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-uppercase text-muted font-weight-bold mb-1" style="font-size:.7rem;">② TI · Bachiller</div>
                @if ($tesis && ($tesis->fecha_exposicion || $tesis->rd_ti || $tesis->bachiller_sunedu))
                    @if ($fmt($tesis->fecha_exposicion)) <div>Exposición: {{ $fmt($tesis->fecha_exposicion) }}</div> @endif
                    @if ($tesis->rd_ti) <div>RD: {{ $tesis->rd_ti }}</div> @endif
                    @if ($tesis->bachiller_sunedu) <div class="text-success"><i class="fas fa-check mr-1"></i>Grado registrado en SUNEDU</div> @endif
                @else
                    <span class="text-muted">Aún no registrado.</span>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-uppercase text-muted font-weight-bold mb-1" style="font-size:.7rem;">③ Tesis · Título</div>
                @if ($tesis && ($tesis->titulo || $tesis->fecha_sustentacion || $tesis->rd_tesis))
                    @if ($tesis->titulo) <div class="font-weight-bold">{{ $tesis->titulo }}</div> @endif
                    @if ($tesis->asesor) <div>Asesor(a): {{ $tesis->asesor->name }} {{ $tesis->asesor->apellidos }}</div> @endif
                    @if ($fmt($tesis->fecha_sustentacion)) <div>Sustentación: {{ $fmt($tesis->fecha_sustentacion) }}</div> @endif
                    @if ($tesis->rd_tesis) <div>RD: {{ $tesis->rd_tesis }}</div> @endif
                    @if ($tesis->url_repositorio)
                        <a href="{{ $tesis->url_repositorio }}" target="_blank" rel="noopener">Ver en repositorio</a>
                    @endif
                @else
                    <span class="text-muted">Aún no registrado.</span>
                @endif
            </div>
        </div>

        @if ($seguimiento->resoluciones->isNotEmpty())
            <div class="small">
                <span class="text-muted mr-1">Resoluciones:</span>
                @foreach ($seguimiento->resoluciones as $rd)
                    <span class="badge badge-light border mr-1">{{ $rd->tipo }} {{ $rd->numero_rd }}</span>
                @endforeach
            </div>
        @endif

        <div class="small text-muted mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            Si algún dato no es correcto, comunícate con la coordinación de titulación.
        </div>
    </div>
</div>
