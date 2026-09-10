{{-- Campanita de notificaciones in-app (docente/tutor comparten este layout) --}}
@php
    $notiUser = auth()->user();
    $notiNoLeidas = $notiUser?->unreadNotifications ?? collect();
    $notiRecientes = $notiUser?->notifications()->latest()->limit(8)->get() ?? collect();
@endphp
<li class="nav-item dropdown no-arrow mx-1">
    <a class="nav-link dropdown-toggle" href="#" id="notificacionesDropdown" role="button"
        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Notificaciones">
        <i class="fas fa-bell fa-fw"></i>
        @if ($notiNoLeidas->count() > 0)
            <span class="badge badge-danger badge-counter">{{ $notiNoLeidas->count() > 9 ? '9+' : $notiNoLeidas->count() }}</span>
        @endif
    </a>
    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
        aria-labelledby="notificacionesDropdown">
        <h6 class="dropdown-header d-flex justify-content-between align-items-center">
            Notificaciones
            @if ($notiNoLeidas->count() > 0)
                <form action="{{ route('notificaciones.marcarTodasLeidas') }}" method="POST" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0" style="font-size:.72rem;">
                        Marcar todas leídas
                    </button>
                </form>
            @endif
        </h6>
        @forelse ($notiRecientes as $n)
            @php
                $notiTipo = match ($n->type) {
                    \App\Notifications\IncidenciaCreada::class => ['Incidencia', 'fa-clipboard-list', 'bg-warning'],
                    \App\Notifications\TutoriaCreada::class    => ['Tutoría', 'fa-hands-helping', 'bg-danger'],
                    \App\Notifications\SugerenciaCreada::class => ['Sugerencia', 'fa-comment-dots', 'bg-success'],
                    default => ['Notificación', 'fa-bell', 'bg-primary'],
                };
                [$notiLabel, $notiIcono, $notiColor] = $notiTipo;
            @endphp
            <a class="dropdown-item d-flex align-items-center {{ is_null($n->read_at) ? 'bg-light' : '' }}"
                href="{{ route('notificaciones.ir', $n->id) }}">
                <div class="mr-3">
                    <div class="icon-circle {{ $notiColor }}">
                        <i class="fas {{ $notiIcono }} text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500">{{ $n->created_at->diffForHumans() }}</div>
                    <span class="{{ is_null($n->read_at) ? 'font-weight-bold' : '' }}">
                        {{ $notiLabel }} — {{ $n->data['alumno_nombre'] ?? 'Alumno' }}
                    </span>
                </div>
            </a>
        @empty
            <div class="dropdown-item text-center text-muted small py-3">Sin notificaciones</div>
        @endforelse
    </div>
</li>
