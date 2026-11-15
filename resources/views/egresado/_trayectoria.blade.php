{{-- Trayectoria del egresado (TrayectoriaEvento: egreso, cambios de ciclo, licencias, reincorporaciones…), de la más
     antigua a la más reciente. Compartido por los paneles de egresado FID y PPD. Espera $eventos. --}}
@if ($eventos->isNotEmpty())
    @php
        $iconos = ['egreso' => 'fa-user-graduate', 'cambio_ciclo' => 'fa-level-up-alt', 'licencia' => 'fa-pause-circle', 'reincorporacion' => 'fa-play-circle',
            'retiro' => 'fa-door-open', 'cambio_programa' => 'fa-exchange-alt', 'beca' => 'fa-award', 'estado_inicial' => 'fa-flag', 'inhabilitacion' => 'fa-ban', 'sancion' => 'fa-gavel'];
    @endphp
    <div class="al-card">
        <div class="al-card-cab">
            <span class="al-card-cab-icono"><i class="fas fa-route"></i></span>
            <div><h2>Mi trayectoria</h2><p>Los momentos de tu paso por la escuela registrados en el sistema.</p></div>
        </div>
        <ul class="al-cursos">
            @foreach ($eventos as $e)
                @php
                    $fecha = $e->fecha_inicio ?? ($e->origen === 'migracion' ? null : $e->created_at);
                    $cambio = filled($e->valor_anterior) && filled($e->valor_nuevo) ? $e->valor_anterior.' → '.$e->valor_nuevo : $e->valor_nuevo;
                @endphp
                <li class="al-curso">
                    <span class="al-curso-num"><i class="fas {{ $iconos[$e->tipo] ?? 'fa-circle' }}"></i></span>
                    <div class="al-curso-texto">
                        <div class="al-curso-nombre">{{ \App\Models\TrayectoriaEvento::TIPOS[$e->tipo] ?? $e->tipo }}</div>
                        @if ($cambio && $e->tipo !== 'estado_inicial')<div class="al-curso-meta">{{ $cambio }}</div>@endif
                    </div>
                    <div class="al-curso-accion">
                        @if ($fecha)<span class="al-badge is-muted">{{ $fecha->format('d/m/Y') }}</span>@endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
