@php
    // La campanita vive en los tres paneles, así que la bandeja se adapta al layout
    // del rol con el que se entró.
    $usuario = auth()->user();
    $layout  = match (true) {
        $usuario->hasRole('super-admin') => 'layouts.superadmin',
        $usuario->hasRole('admin')       => 'layouts.admin',
        default                          => 'layouts.docente',
    };

    $tipos = [
        \App\Notifications\IncidenciaCreada::class => ['Incidencia', 'fa-clipboard-list', '#f6c23e'],
        \App\Notifications\TutoriaCreada::class    => ['Tutoría', 'fa-hands-helping', '#e74a3b'],
        \App\Notifications\SugerenciaCreada::class => ['Sugerencia', 'fa-comment-dots', '#1cc88a'],
    ];
@endphp

@extends($layout)

@section('titulo', 'Notificaciones')

@section('contenido')
<style>
    .noti-page { max-width: 940px; margin: 0 auto; }
    .noti-card {
        border: none; border-radius: .75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,.07);
        background: #fff;
    }
    .noti-hero { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-start;
        justify-content: space-between; margin-bottom: 1.25rem; }
    .noti-kicker { font-size: .72rem; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: #858796; margin: 0 0 .2rem; }
    .noti-title { font-size: 1.35rem; font-weight: 700; color: #3a3b45; margin: 0; }
    .noti-sub { font-size: .85rem; color: #858796; margin: .35rem 0 0; }

    .noti-tabs { display: inline-flex; background: #f1f3f9; border-radius: 999px; padding: 3px; gap: 3px; }
    .noti-tab {
        font-size: .78rem; font-weight: 600; color: #6c757d; text-decoration: none !important;
        border-radius: 999px; padding: .3rem .85rem;
    }
    .noti-tab--activa { background: #fff; color: #4e73df; box-shadow: 0 1px 4px rgba(0,0,0,.1); }

    .noti-item {
        display: flex; gap: .85rem; align-items: flex-start;
        padding: .95rem 1.15rem; border-bottom: 1px solid #f0f2f8;
    }
    .noti-item:last-child { border-bottom: none; }
    .noti-item--nueva { background: #fbfcff; }
    .noti-icono {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem;
    }
    .noti-cuerpo { flex: 1 1 260px; min-width: 0; }
    .noti-asunto { font-size: .9rem; font-weight: 700; color: #3a3b45; }
    .noti-meta { font-size: .72rem; color: #858796; margin-top: .1rem; }
    .noti-resumen { font-size: .84rem; color: #5a5c69; margin-top: .3rem; line-height: 1.5;
        overflow-wrap: anywhere; }
    .noti-acciones { display: flex; flex-direction: column; align-items: flex-end; gap: .35rem; flex-shrink: 0; }
    .noti-acciones form { margin: 0; }
    .noti-vacio { padding: 3.5rem 1rem; text-align: center; }
    .noti-vacio i { font-size: 2.75rem; color: #dee2e6; display: block; margin-bottom: .85rem; }

    body.dark-mode .noti-card, body.dim-mode .noti-card { background: #23252f; }
    body.dark-mode .noti-title, body.dim-mode .noti-title,
    body.dark-mode .noti-asunto, body.dim-mode .noti-asunto { color: #e3e6f0; }
    body.dark-mode .noti-resumen, body.dim-mode .noti-resumen { color: #b7bcc9; }
    body.dark-mode .noti-item, body.dim-mode .noti-item { border-color: #333646; }
    body.dark-mode .noti-item--nueva, body.dim-mode .noti-item--nueva { background: #262a37; }
    body.dark-mode .noti-tabs, body.dim-mode .noti-tabs { background: #1b1d26; }
    body.dark-mode .noti-tab--activa, body.dim-mode .noti-tab--activa { background: #333646; color: #9db4ff; }
</style>

<div class="container-fluid noti-page">
    <div class="noti-hero">
        <div>
            <p class="noti-kicker">Mi cuenta</p>
            <h1 class="noti-title">Notificaciones</h1>
            <p class="noti-sub">
                {{ $total }} registradas
                @if ($noLeidas > 0)
                    · {{ $noLeidas }} sin leer
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap justify-content-end" style="gap:.5rem;">
            <div class="noti-tabs">
                <a class="noti-tab {{ $soloNoLeidas ? '' : 'noti-tab--activa' }}"
                   href="{{ route('notificaciones.index') }}">Todas</a>
                <a class="noti-tab {{ $soloNoLeidas ? 'noti-tab--activa' : '' }}"
                   href="{{ route('notificaciones.index', ['filtro' => 'no-leidas']) }}">
                    Sin leer @if ($noLeidas > 0) ({{ $noLeidas }}) @endif
                </a>
            </div>
            @if ($noLeidas > 0)
                <form action="{{ route('notificaciones.marcarTodasLeidas') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-check-double mr-1"></i> Marcar todas
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card noti-card">
        @forelse ($notificaciones as $n)
            @php
                [$etiqueta, $icono, $color] = $tipos[$n->type] ?? ['Notificación', 'fa-bell', '#4e73df'];
                $sinLeer = is_null($n->read_at);
                $data    = (array) $n->data;
                $origen  = collect([
                    'por ' . ($data['reportado_por'] ?? ''),
                    $data['fecha'] ?? null,
                    isset($data['urgencia']) && $data['urgencia'] ? 'urgente' : null,
                ])->filter(fn ($v) => (string) $v !== '')->implode(' · ');
            @endphp
            <div class="noti-item {{ $sinLeer ? 'noti-item--nueva' : '' }}">
                <div class="noti-icono" style="background: {{ $color }};">
                    <i class="fas {{ $icono }}"></i>
                </div>
                <div class="noti-cuerpo">
                    <div class="noti-asunto">
                        {{ $etiqueta }} — {{ $data['alumno_nombre'] ?? 'Sin estudiante' }}
                        @if ($sinLeer)
                            <span class="badge badge-danger ml-1" style="font-size:.6rem;">NUEVA</span>
                        @endif
                    </div>
                    <div class="noti-meta">
                        {{ $n->created_at->diffForHumans() }}
                        @if ($origen) · {{ $origen }} @endif
                    </div>
                    @if (!empty($data['resumen']))
                        <p class="noti-resumen">{{ $data['resumen'] }}</p>
                    @endif
                </div>
                <div class="noti-acciones">
                    <a href="{{ route('notificaciones.ir', $n->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-external-link-alt mr-1"></i> Abrir
                    </a>
                    <div class="d-flex" style="gap:.35rem;">
                        @if ($sinLeer)
                            <form action="{{ route('notificaciones.marcarLeida', $n->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm" title="Marcar como leída">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('notificaciones.eliminar', $n->id) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar esta notificación?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="noti-vacio">
                <i class="fas fa-bell-slash"></i>
                <p class="font-weight-bold text-muted mb-1">
                    {{ $soloNoLeidas ? 'No tienes notificaciones sin leer' : 'Todavía no tienes notificaciones' }}
                </p>
                <p class="small text-muted mb-0">Aquí aparecen las incidencias, tutorías y sugerencias que te notifiquen.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $notificaciones->links() }}
    </div>
</div>
@endsection
