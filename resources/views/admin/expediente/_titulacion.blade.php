{{--
    Pestaña Titulación del expediente (solo gestor-titulacion). $t viene de
    ExpedienteController::titulacion(): seguimiento, avance (AvanceTitulacion) y url de la ficha.
    Solo lectura: la gestión se hace en el módulo de Titulación.
--}}
@php
    $seg = $t['seguimiento'];
    $avance = $t['avance'];
    $situacion = [
        'logrado' => ['green', 'fa-check', 'Logrado'],
        'actual' => ['amber', 'fa-arrow-right', 'Siguiente paso'],
        'pendiente' => ['slate', 'fa-circle', 'Pendiente'],
        'sin_registro' => ['slate', 'fa-minus', 'Sin registro'],
    ];
@endphp
<section data-pane="titulacion" hidden>
    @if (! $seg)
        <div class="ex-card ex-empty">
            <i class="fas fa-graduation-cap d-block mb-2" style="font-size:1.5rem;opacity:.3;"></i>
            Este estudiante aún no tiene seguimiento de titulación.
        </div>
    @else
        <div class="ex-card" style="padding:1rem 1.2rem;">
            <div class="d-flex flex-wrap align-items-center" style="gap:.6rem;">
                <div>
                    <div class="ex-kpi-label">Estado de titulación</div>
                    <div style="font-size:1.15rem;font-weight:800;color:var(--ex-text);">{{ $avance?->estadoNombre() ?? '—' }}</div>
                </div>
                @php $siguiente = $avance?->siguientePaso(); @endphp
                @if ($siguiente)
                    <span class="chip is-amber ml-md-3"><i class="fas fa-arrow-right"></i> {{ $siguiente['falta'] ?? $siguiente['label'] ?? '' }}</span>
                @endif
                @if ($t['url'])
                    <a href="{{ $t['url'] }}" class="ex-btn ml-auto"><i class="fas fa-external-link-alt fa-xs"></i> Abrir ficha de titulación</a>
                @endif
            </div>
        </div>

        @if ($avance)
            @foreach ($avance->lineaDeTiempo() as $etapa)
                <div class="ex-card mt-2" style="padding:.8rem 1.1rem;">
                    <div class="d-flex align-items-center mb-2" style="gap:.5rem;">
                        <span style="font-weight:800;font-size:.9rem;color:var(--ex-text);">{{ $etapa['titulo'] }}</span>
                        <span class="chip {{ $etapa['estado'] === 'completo' ? 'is-green' : ($etapa['estado'] === 'en_proceso' ? 'is-amber' : 'is-slate') }}">
                            {{ ['completo' => 'Completo', 'en_proceso' => 'En proceso', 'pendiente' => 'Pendiente'][$etapa['estado']] ?? $etapa['estado'] }}
                        </span>
                    </div>
                    @foreach ($etapa['hitos'] as $h)
                        @php [$color, $icono, $texto] = $situacion[$h['situacion']] ?? $situacion['pendiente']; @endphp
                        <div class="d-flex align-items-center" style="gap:.55rem;padding:.25rem 0;font-size:.82rem;">
                            <span class="chip is-{{ $color }}" style="min-width:26px;justify-content:center;"><i class="fas {{ $icono }}"></i></span>
                            <span style="color:var(--ex-text);font-weight:{{ $h['situacion'] === 'actual' ? 800 : 600 }};">{{ $h['label'] }}</span>
                            @if ($h['fecha'])<span class="ex-tl-detail mt-0">{{ $h['fecha']->format('d/m/Y') }}</span>@endif
                            @if ($h['detalle'])<span class="ex-tl-detail mt-0">· {{ $h['detalle'] }}</span>@endif
                            @if ($h['situacion'] === 'actual')<span class="ex-tl-detail mt-0">— {{ $h['falta'] }}</span>@endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif

        @if ($seg->relationLoaded('ptis') && $seg->ptis->isNotEmpty())
            <div class="ex-subtitle">Historial de PTI</div>
            <div class="ex-card">
                <div class="table-responsive">
                    <table class="ex-table">
                        <thead><tr><th>N°</th><th>Etapa</th><th>Título</th><th>Asesor</th><th>RD</th><th>Vence</th><th>Estado</th></tr></thead>
                        <tbody>
                            @foreach ($seg->ptis as $pti)
                                <tr>
                                    <td>{{ $pti->numero ?? 1 }}</td>
                                    <td>{{ ucfirst($pti->etapa ?? '') }}</td>
                                    <td>{{ $pti->titulo ?: '—' }}</td>
                                    <td>{{ method_exists($pti, 'asesorNombre') ? ($pti->asesorNombre() ?: '—') : '—' }}</td>
                                    <td>{{ $pti->rd_pti ?: '—' }}{{ $pti->rd_ampliacion ? ' · amp. ' . $pti->rd_ampliacion : '' }}</td>
                                    <td>{{ ($vence = $pti->vencimiento_ampliado ?: $pti->fecha_vencimiento) ? \Carbon\Carbon::parse($vence)->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $pti->estado ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</section>
