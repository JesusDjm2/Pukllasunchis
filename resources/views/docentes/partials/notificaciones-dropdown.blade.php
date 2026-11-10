{{-- Campanita de notificaciones in-app — compartida por admin, superadmin,
     docente/tutor y alumno (FID y PPD). Un solo partial para que un tipo de
     notificación nuevo (icono, color, texto) se agregue en un único lugar. --}}
@php
    $notiUser = auth()->user();
    // Cuentas de área (p. ej. Secretaría, que además tiene rol admin): solo los avisos de su área.
    $notiFiltro = fn ($q) => \App\Support\NotificacionesPorRol::filtrar($q, $notiUser);
    $notiNoLeidasTotal = $notiUser ? $notiFiltro($notiUser->unreadNotifications())->count() : 0;

    // Prioriza siempre las no leídas (hasta 30, tope razonable para un
    // dropdown) y solo completa con leídas recientes el espacio que sobre
    // hasta 5 en total — así una notificación vieja sin atender nunca queda
    // tapada por otras ya vistas, y las leídas de hace meses dejan de
    // aparecer. La lista tiene alto fijo (~5 filas, ver .noti-scroll) con
    // scroll propio para ver el resto sin atender cuando hay más de 5.
    $notiNoLeidas = $notiUser ? $notiFiltro($notiUser->unreadNotifications())->latest()->limit(30)->get() : collect();
    $cupoLeidas = max(0, 5 - $notiNoLeidas->count());
    $notiLeidasRecientes = $cupoLeidas > 0 && $notiUser
        ? $notiFiltro($notiUser->readNotifications())->latest()->limit($cupoLeidas)->get()
        : collect();
    $notiRecientes = $notiNoLeidas->concat($notiLeidasRecientes);
@endphp

<style>
    /* Distinción leída/no-leída que funciona igual en claro, oscuro y tenue:
       en vez de un bg-light (invisible en claro, y sin override en dark/dim,
       ver public/admin/css/darkmode.css) se usa un tinte semitransparente +
       barra de acento + punto — se nota por color Y por forma, no solo por
       una diferencia de brillo que algunos temas anulaban. */
    .noti-item {
        border-left: 3px solid transparent;
        transition: background-color .15s ease;
    }

    .noti-item.is-unread {
        background-color: rgba(78, 115, 223, .12);
        border-left-color: #4e73df;
    }

    .noti-item:not(.is-unread) {
        opacity: .7;
    }

    .noti-item:not(.is-unread):hover {
        opacity: 1;
    }

    .noti-texto.is-unread-text {
        font-weight: 700;
    }

    .noti-dot {
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        background: #e74a3b;
        display: inline-block;
        margin-left: .4rem;
        flex-shrink: 0;
    }

    /* Alto fijo para ~5 notificaciones con scroll propio para el resto —
       antes la lista crecía sin límite (hasta 8 ítems) y podía salirse de
       la pantalla sin forma de ver lo que quedaba más abajo. */
    .noti-scroll {
        max-height: 340px;
        overflow-y: auto;
    }

    .noti-scroll::-webkit-scrollbar {
        width: .4rem;
    }

    .noti-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, .18);
        border-radius: 10px;
    }
</style>

<li class="nav-item dropdown no-arrow mx-1">
    <a class="nav-link dropdown-toggle" href="#" id="notificacionesDropdown" role="button"
        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Notificaciones">
        <i class="fas fa-bell fa-fw"></i>
        @if ($notiNoLeidasTotal > 0)
            <span class="badge badge-danger badge-counter">{{ $notiNoLeidasTotal > 9 ? '9+' : $notiNoLeidasTotal }}</span>
        @endif
    </a>
    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
        aria-labelledby="notificacionesDropdown">
        <h6 class="dropdown-header d-flex justify-content-between align-items-center">
            Notificaciones
            @if ($notiNoLeidasTotal > 0)
                <form action="{{ route('notificaciones.marcarTodasLeidas') }}" method="POST" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0" style="font-size:.72rem;">
                        Marcar todas leídas
                    </button>
                </form>
            @endif
        </h6>
        <div class="noti-scroll">
        @forelse ($notiRecientes as $n)
            @php
                $notiTipo = match ($n->type) {
                    \App\Notifications\IncidenciaCreada::class => [
                        'Incidencia', 'fa-clipboard-list', 'bg-warning',
                        $n->data['alumno_nombre'] ?? 'Alumno',
                    ],
                    \App\Notifications\TutoriaCreada::class => [
                        'Tutoría', 'fa-hands-helping', 'bg-danger',
                        $n->data['alumno_nombre'] ?? 'Alumno',
                    ],
                    \App\Notifications\SugerenciaCreada::class => [
                        'Sugerencia', 'fa-comment-dots', 'bg-success',
                        $n->data['alumno_nombre'] ?? 'Alumno',
                    ],
                    \App\Notifications\TutoriaAtendida::class => [
                        'Tutoría atendida', 'fa-check-circle', 'bg-primary',
                        'Tu solicitud de tutoría ya fue atendida',
                    ],
                    \App\Notifications\SugerenciaRevisada::class => [
                        'Sugerencia revisada', 'fa-check-circle', 'bg-primary',
                        'Tu sugerencia ya fue revisada',
                    ],
                    \App\Notifications\TaqeInvitacionGrupo::class => [
                        "Invitación de Investigaciones", 'fa-flask', 'bg-info',
                        'Te sumaron al grupo "'.($n->data['grupo_nombre'] ?? '').'" — '.($n->data['curso_nombre'] ?? ''),
                    ],
                    \App\Notifications\TaqeMiembroSalio::class => [
                        "Grupo de Investigaciones", 'fa-user-minus', 'bg-secondary',
                        ($n->data['alumno_nombre'] ?? 'Un integrante').' salió de "'.($n->data['grupo_nombre'] ?? '').'"'
                            .(($n->data['nuevo_organizador'] ?? false) ? ' — ahora eres el organizador' : ''),
                    ],
                    \App\Notifications\TaqeMiembroAcepto::class => [
                        "Grupo de Investigaciones", 'fa-user-plus', 'bg-success',
                        ($n->data['alumno_nombre'] ?? 'Un compañero').' se unió a "'.($n->data['grupo_nombre'] ?? '').'"',
                    ],
                    \App\Notifications\TaqeIntegranteRetirado::class => [
                        "Grupo de Investigaciones", 'fa-user-times', 'bg-danger',
                        'El organizador te retiró del grupo "'.($n->data['grupo_nombre'] ?? '').'"',
                    ],
                    \App\Notifications\TaqeOrganizadorCambiado::class => [
                        "Grupo de Investigaciones", 'fa-crown', 'bg-warning',
                        'Ahora eres el organizador del grupo "'.($n->data['grupo_nombre'] ?? '').'"',
                    ],
                    \App\Notifications\TaqeSolicitudAyudaCreada::class => [
                        "Solicitud de ayuda de Investigaciones", 'fa-life-ring', 'bg-warning',
                        ($n->data['alumno_nombre'] ?? 'Un alumno').' pidió ayuda ('.($n->data['motivo'] ?? '').') — "'.($n->data['grupo_nombre'] ?? '').'"',
                    ],
                    \App\Notifications\TaqeSolicitudAtendida::class => [
                        "Solicitud atendida", 'fa-check-circle', 'bg-primary',
                        'Tu solicitud sobre "'.($n->data['grupo_nombre'] ?? '').'" ('.($n->data['motivo'] ?? '').') ya fue atendida',
                    ],
                    \App\Notifications\TaqeGrupoEliminado::class => [
                        "Grupo de Investigaciones", 'fa-trash-alt', 'bg-danger',
                        'El grupo "'.($n->data['grupo_nombre'] ?? '').'" fue eliminado por un administrador',
                    ],
                    \App\Notifications\TitulacionDatoReportado::class => [
                        'Titulación: dato por revisar', 'fa-user-graduate', 'bg-warning',
                        ($n->data['alumno_nombre'] ?? 'Un egresado').' — "'.($n->data['resumen'] ?? '').'"',
                    ],
                    \App\Notifications\TitulacionCartaSolicitada::class => [
                        'Carta de aceptación', 'fa-envelope-open-text', 'bg-info',
                        ($n->data['alumno_nombre'] ?? 'Un estudiante').' la solicita para «'.($n->data['institucion'] ?? '').'»',
                    ],
                    \App\Notifications\TitulacionCartaAtendida::class => match ($n->data['estado'] ?? '') {
                        'emitida' => ['Carta de aceptación', 'fa-check-circle', 'bg-success', 'Tu carta para «'.($n->data['institucion'] ?? '').'» fue aprobada'],
                        'enviada' => ['Carta de aceptación', 'fa-inbox', 'bg-info', 'Secretaría recibió tu solicitud: te responderá en 3 días hábiles'],
                        default => ['Carta de aceptación', 'fa-exclamation-circle', 'bg-warning', 'Tu solicitud fue observada: revísala y envíala de nuevo'],
                    },
                    default => ['Notificación', 'fa-bell', 'bg-secondary', $n->data['alumno_nombre'] ?? ''],
                };
                [$notiLabel, $notiIcono, $notiColor, $notiTexto] = $notiTipo;
                $notiEsNoLeida = is_null($n->read_at);
            @endphp
            <a class="dropdown-item d-flex align-items-center noti-item {{ $notiEsNoLeida ? 'is-unread' : '' }}"
                href="{{ route('notificaciones.ir', $n->id) }}">
                <div class="mr-3">
                    <div class="icon-circle {{ $notiColor }}">
                        <i class="fas {{ $notiIcono }} text-white"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="small text-gray-500">{{ $n->created_at->diffForHumans() }}</div>
                    <span class="noti-texto {{ $notiEsNoLeida ? 'is-unread-text' : '' }}">
                        {{ $notiLabel }}{{ $notiTexto ? ' — '.$notiTexto : '' }}
                    </span>
                </div>
                @if ($notiEsNoLeida)
                    <span class="noti-dot" title="No leída"></span>
                @endif
            </a>
        @empty
            <div class="dropdown-item text-center text-muted small py-3">Sin notificaciones</div>
        @endforelse
        </div>
    </div>
</li>
