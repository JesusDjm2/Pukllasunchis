{{-- Solicitudes de ayuda pendientes (Investigador y Super Admin). Espera: $solicitudes, $rutaAtender. --}}
@if ($solicitudes->isNotEmpty())
    <div class="al-card">
        <div class="al-card-cab">
            <span class="al-card-cab-icono"><i class="fas fa-life-ring"></i></span>
            <div><h2>Solicitudes de ayuda</h2><p>Pedidos de los grupos que esperan una respuesta.</p></div>
            <div class="al-card-cab-extra"><span class="al-badge is-warn">{{ $solicitudes->count() }} pendiente{{ $solicitudes->count() === 1 ? '' : 's' }}</span></div>
        </div>
        <div class="al-card-cuerpo">
            @foreach ($solicitudes as $solicitud)
                <div class="tq-invitacion mb-2">
                    <span class="tq-invitacion-icono"><i class="fas fa-life-ring"></i></span>
                    <div class="tq-invitacion-texto">
                        <div class="tq-invitacion-titulo"><strong>{{ $solicitud->motivoLabel() }}</strong> — {{ $solicitud->grupo_nombre }}</div>
                        <small>De {{ $solicitud->alumno->nombres }} {{ $solicitud->alumno->apellidos }} · {{ $solicitud->created_at->diffForHumans() }}</small>
                        @if ($solicitud->mensaje)
                            <div class="tq-solicitud-mensaje">"{{ $solicitud->mensaje }}"</div>
                        @endif
                    </div>
                    <div class="tq-invitacion-acciones">
                        <form action="{{ route($rutaAtender, $solicitud->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-check"></i> Marcar atendida</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
