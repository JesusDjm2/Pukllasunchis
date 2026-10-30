{{-- Resultado de la última carga masiva: cifras, actas cargadas (con enlace) y filas con error. $r viene de la sesión. --}}
@php
    $actasCargadas = \App\Models\RicardoPalma\RpActa::with(['periodo', 'programa'])
        ->whereIn('id', $r['actas'] ?? [])->get();
    $conErrores = ($r['fallidos'] ?? 0) > 0;
    $nadaCargado = ($r['exitosos'] ?? 0) === 0;
@endphp
<article class="al-card rp-resultado {{ $nadaCargado ? 'is-mal' : ($conErrores ? 'is-warn' : 'is-ok') }}" role="status">
    <div class="al-card-cab">
        <span class="al-card-cab-icono">
            <i class="fas {{ $nadaCargado ? 'fa-times' : ($conErrores ? 'fa-exclamation-triangle' : 'fa-check') }}"></i>
        </span>
        <div>
            <h2>
                @if ($nadaCargado)
                    No se cargó ninguna nota
                @elseif ($conErrores)
                    Carga completada con observaciones
                @else
                    Carga completada correctamente
                @endif
            </h2>
            <p><i class="far fa-file-excel mr-1"></i>{{ $r['archivo'] ?? 'Archivo' }}</p>
        </div>
    </div>

    <div class="al-card-cuerpo">
        <div class="al-stats mb-3">
            <div class="al-stat">
                <span class="al-stat-icono is-ok"><i class="fas fa-plus"></i></span>
                <div><div class="al-stat-valor">{{ $r['notas_nuevas'] ?? 0 }}</div><div class="al-stat-etiqueta">Notas registradas</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-info"><i class="fas fa-sync-alt"></i></span>
                <div><div class="al-stat-valor">{{ $r['notas_actualizadas'] ?? 0 }}</div><div class="al-stat-etiqueta">Notas actualizadas</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-oro"><i class="fas fa-user-graduate"></i></span>
                <div>
                    <div class="al-stat-valor">{{ $r['estudiantes'] ?? 0 }}</div>
                    <div class="al-stat-etiqueta">Estudiantes{{ ($r['estudiantes_nuevos'] ?? 0) ? ' · '.$r['estudiantes_nuevos'].' nuevos' : '' }}</div>
                </div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono {{ $conErrores ? 'is-mal' : '' }}"><i class="fas fa-exclamation-circle"></i></span>
                <div><div class="al-stat-valor">{{ $r['fallidos'] ?? 0 }}</div><div class="al-stat-etiqueta">Filas con error</div></div>
            </div>
        </div>

        @if ($actasCargadas->isNotEmpty())
            <div class="rp-resultado-titulo">Actas cargadas · {{ $actasCargadas->count() }}</div>
            <div class="rp-resultado-actas">
                @foreach ($actasCargadas as $a)
                    <a class="al-btn al-btn-borde al-btn-sm" href="{{ route('rp.actas.show', $a) }}">
                        <i class="fas fa-file-invoice"></i>
                        {{ $a->numero_acta ? 'Acta N° '.$a->numero_acta : 'Acta sin número' }}
                        · {{ $a->periodo?->codigo }} · {{ $a->programa?->codigo }} · Sem. {{ $a->numero_semestre }}
                        @unless ($a->fecha_emision)
                            <span class="al-badge is-warn" title="El archivo no traía FECHA_VISACION">Sin fecha de visación</span>
                        @endunless
                    </a>
                @endforeach
            </div>
        @endif

        @if ($conErrores)
            <details class="rp-resultado-errores" {{ $nadaCargado ? 'open' : '' }}>
                <summary>Ver las {{ count($r['errores'] ?? []) }} filas que no se cargaron</summary>
                <ul>
                    @foreach ($r['errores'] ?? [] as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <p class="al-suave mb-0">Corrige esas filas en el Excel y vuelve a subirlo: lo que ya se cargó no se duplica, solo se actualiza.</p>
            </details>
        @endif
    </div>
</article>
