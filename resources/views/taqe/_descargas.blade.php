{{--
    Botón "Descargar" (ZIP) de un grupo Taq'e para docentes a cargo, Investigador y Super Admin.
    Menú propio con <details> (sin Bootstrap): el dropdown anterior quedaba recortado por la tarjeta.
    Parámetros: $grupo (con evidencias y finales cargados), $rutaZip (name de ruta con {grupo}/{que}).
--}}
@php
    $nEvidencias = $grupo->evidencias->count();
    $nFinales = $grupo->finales->count();
@endphp
@if ($nEvidencias || $nFinales)
    <details class="tq-menu">
        <summary class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-file-archive"></i> Descargar <i class="fas fa-chevron-down"></i></summary>
        <div class="tq-menu-lista">
            @if ($nEvidencias)
                <a href="{{ route($rutaZip, [$grupo->id, 'evidencias']) }}"><i class="fas fa-images" style="color: var(--al-oro);"></i> Evidencias <small>{{ $nEvidencias }} · ZIP</small></a>
            @else
                <span><i class="fas fa-images"></i> Evidencias <small>vacío</small></span>
            @endif
            @if ($nFinales)
                <a href="{{ route($rutaZip, [$grupo->id, 'final']) }}"><i class="fas fa-flag-checkered" style="color: var(--al-ok);"></i> Trabajo final <small>{{ $nFinales }} · ZIP</small></a>
            @else
                <span><i class="fas fa-flag-checkered"></i> Trabajo final <small>vacío</small></span>
            @endif
            <hr>
            <a href="{{ route($rutaZip, [$grupo->id, 'todo']) }}"><i class="fas fa-file-archive" style="color: var(--al-petroleo);"></i> Todo el grupo <small>ZIP</small></a>
        </div>
    </details>
@else
    <button type="button" class="al-btn al-btn-borde al-btn-sm" disabled title="El grupo aún no tiene archivos"><i class="fas fa-file-archive"></i> Descargar</button>
@endif

@once
    @push('scripts')
        <script>
            // Cierra el menú "Descargar" al hacer clic fuera, con Escape o al elegir una opción.
            document.addEventListener('click', function (e) {
                document.querySelectorAll('details.tq-menu[open]').forEach(function (m) {
                    if (!m.contains(e.target) || e.target.closest('.tq-menu-lista a')) m.removeAttribute('open');
                });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') document.querySelectorAll('details.tq-menu[open]').forEach(function (m) { m.removeAttribute('open'); });
            });
        </script>
    @endpush
@endonce
