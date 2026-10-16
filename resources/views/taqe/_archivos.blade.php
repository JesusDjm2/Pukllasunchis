{{--
    Evidencias (izquierda) y trabajo final (derecha) de un grupo Taq'e para docente, Investigador y Super Admin:
    las mismas dos columnas y filas (taqe/_fila-archivo) que ve el alumno, con el visor emergente (taqe/_visor).
    Parámetros: $grupo (con finales.subidoPor y evidencias.subidoPor cargados),
    $rutaVer (name de ruta {trabajo}/ver; null = solo nombres y enlaces, p. ej. docente),
    $rutaEliminar (opcional, solo Super Admin).
--}}
@php
    $rutaVer = $rutaVer ?? null;
    $rutaEliminar = $rutaEliminar ?? null;
@endphp

<div class="tq-archivos">
    <section class="tq-columna">
        <div class="tq-columna-cab">
            <h3><i class="fas fa-images"></i> Evidencias del proceso</h3>
            <span class="al-badge is-muted">{{ $grupo->evidencias->count() }}</span>
        </div>
        <div class="tq-columna-sub">Fotos, audios, videos, documentos y enlaces del trabajo de campo.</div>
        <div class="tq-columna-lista">
            @forelse ($grupo->evidencias as $trabajo)
                @include('taqe._fila-archivo', ['trabajo' => $trabajo, 'rutaVer' => $rutaVer, 'urlEliminar' => $rutaEliminar ? route($rutaEliminar, $trabajo->id) : null])
            @empty
                <p class="tq-nota mb-0">Sin evidencias subidas todavía.</p>
            @endforelse
        </div>
    </section>

    <section class="tq-columna is-final">
        <div class="tq-columna-cab">
            <h3><i class="fas fa-flag-checkered"></i> Trabajo final</h3>
            @if ($grupo->estaEntregado())
                <span class="al-badge is-ok"><i class="fas fa-check"></i> Entregado {{ optional($grupo->entregado_at)->format('d/m/Y') }}</span>
            @else
                <span class="al-badge is-muted">{{ $grupo->finales->count() }} · sin entregar</span>
            @endif
        </div>
        <div class="tq-columna-sub">Lo que el grupo presenta en la feria.</div>
        <div class="tq-columna-lista">
            @forelse ($grupo->finales as $trabajo)
                @include('taqe._fila-archivo', ['trabajo' => $trabajo, 'rutaVer' => $rutaVer, 'urlEliminar' => $rutaEliminar ? route($rutaEliminar, $trabajo->id) : null])
            @empty
                <p class="tq-nota mb-0">Aún no hay nada en el trabajo final.</p>
            @endforelse
        </div>
    </section>
</div>
