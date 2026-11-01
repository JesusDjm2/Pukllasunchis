@php
    $pub = $pub ?? $publicacion;
    $autor = $pub->autor;
    $nombreAutor = trim(($autor->name ?? '') . ' ' . ($autor->apellidos ?? '')) ?: 'Integrante Pukllasunchis';
    $iniciales = strtoupper(mb_substr($autor->name ?? 'P', 0, 1) . mb_substr($autor->apellidos ?? 'K', 0, 1));
    $esAutorDocente = $autor && ($autor->hasAnyRole(['docente', 'tutor', 'investigador', 'admin', 'super-admin']) || $autor->docente);
    $badgeTipo = $pub->etiquetaTipo();
@endphp
<article class="ayllu-card">
    <div class="ayllu-card-header">
        <div class="d-flex align-items-center" style="gap: 0.75rem;">
            <a href="{{ $autor ? route('ayllu.perfil', $autor->id) : '#' }}" class="ayllu-avatar text-decoration-none" title="Ver perfil intercultural">
                {{ $iniciales }}
            </a>
            <div>
                <div class="d-flex align-items-center flex-wrap" style="gap: 0.4rem;">
                    <a href="{{ $autor ? route('ayllu.perfil', $autor->id) : '#' }}" class="font-weight-bold text-dark" style="font-size: 0.94rem;">
                        {{ $nombreAutor }}
                    </a>
                    @if($esAutorDocente)
                        <span class="badge badge-primary" style="background:#33445a; font-size:0.68rem;">Docente / Formador(a)</span>
                    @elseif($autor?->condicion === 'Egresado')
                        <span class="badge badge-info" style="font-size:0.68rem;">Egresado(a)</span>
                    @else
                        <span class="badge badge-secondary" style="font-size:0.68rem;">Estudiante</span>
                    @endif
                    @if($pub->fijado)
                        <span class="badge badge-warning" style="font-size:0.68rem;"><i class="fas fa-thumbtack mr-1"></i>Fijado</span>
                    @endif
                </div>
                <small class="text-muted d-block" style="font-size: 0.76rem;">
                    {{ $pub->created_at?->diffForHumans() }}
                    @if($pub->ambito === 'curso' && $pub->curso)
                        &middot; en <a href="{{ route('ayllu.cursos.show', $pub->curso->id) }}" class="font-weight-bold" style="color:#9c6b37;"><i class="fas fa-book mr-1"></i>{{ $pub->curso->nombre }}</a>
                    @elseif($pub->ambito === 'grupo' && $pub->grupo)
                        &middot; en <a href="{{ route('ayllu.grupos', ['grupo' => $pub->grupo->id]) }}" class="font-weight-bold" style="color:#0e9467;"><i class="fas fa-users mr-1"></i>{{ $pub->grupo->nombre }}</a>
                    @elseif($pub->ambito === 'ciclo' && $pub->ciclo)
                        &middot; Ciclo {{ $pub->ciclo->nombre }}
                    @else
                        &middot; Comunidad EESP Pukllasunchis
                    @endif
                </small>
            </div>
        </div>

        <div class="d-flex align-items-center" style="gap: 0.4rem;">
            <span class="badge text-white px-2 py-1" style="background: {{ $badgeTipo['color'] }}; border-radius: 999px; font-size: 0.72rem;">
                <i class="{{ $badgeTipo['icono'] }} mr-1"></i> {{ $badgeTipo['texto'] }}
            </span>

            @if(($puedeModerar ?? false) || $pub->user_id === auth()->id())
                <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-circle" type="button" data-toggle="dropdown" aria-label="Opciones de publicación">
                        <i class="fas fa-ellipsis-v text-muted"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm">
                        @if($puedeModerar ?? false)
                            <form action="{{ route('ayllu.publicaciones.destacar', $pub->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item small">
                                    <i class="fas fa-star mr-1 text-warning"></i>
                                    {{ $pub->destacado_inicio ? 'Quitar de destacados de Inicio' : 'Destacar en muro de Inicio' }}
                                </button>
                            </form>
                        @endif
                        <button type="button" class="dropdown-item small" data-toggle="collapse" data-target="#editar-pub-{{ $pub->id }}">
                            <i class="fas fa-edit mr-1 text-primary"></i> Editar publicación
                        </button>
                        <form action="{{ route('ayllu.publicaciones.eliminar', $pub->id) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar esta publicación?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item small text-danger">
                                <i class="fas fa-trash-alt mr-1"></i> Eliminar publicación
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="ayllu-card-body">
        @if(($puedeModerar ?? false) || $pub->user_id === auth()->id())
            <div class="collapse mb-3 p-3 rounded border" id="editar-pub-{{ $pub->id }}" style="background: var(--ayllu-surface-soft);">
                <form action="{{ route('ayllu.publicaciones.update', $pub->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-2">
                        <input type="text" name="titulo" class="form-control form-control-sm" value="{{ $pub->titulo }}" placeholder="Título (opcional)" maxlength="180">
                    </div>
                    <div class="form-group mb-2">
                        <textarea name="contenido" rows="3" class="form-control form-control-sm" required maxlength="5000">{{ $pub->contenido }}</textarea>
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" name="resumen_quechua" class="form-control form-control-sm" value="{{ $pub->resumen_quechua }}" placeholder="Síntesis en Quechua (opcional)" maxlength="1000">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3" style="border-radius: 999px;">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        @endif
        @if($pub->titulo)
            <h5 class="font-weight-bold mb-2" style="font-size: 1.05rem; color: var(--ayllu-text);">
                {{ $pub->titulo }}
            </h5>
        @endif

        <div class="mb-2" style="font-size: 0.92rem; line-height: 1.58; white-space: pre-line;">{{ $pub->contenido }}</div>

        @if($pub->resumen_quechua)
            <div class="p-2 mb-2 rounded" style="background: var(--ayllu-oro-suave); border-left: 3px solid #cd9244; font-size: 0.85rem;">
                <strong style="color:#9c6b37;"><i class="fas fa-language mr-1"></i>Runasimipi / Quechua:</strong>
                <span class="font-italic">{{ $pub->resumen_quechua }}</span>
            </div>
        @endif

        @if($pub->enlace_externo)
            <div class="p-2 mb-2 rounded border d-flex align-items-center justify-content-between flex-wrap" style="background: var(--ayllu-surface-soft); gap: 0.5rem;">
                <div class="text-truncate" style="max-width: 75%;">
                    <i class="fas fa-link text-primary mr-1"></i>
                    <strong>{{ $pub->enlace_titulo ?: 'Recurso / Enlace compartido' }}</strong>
                    <small class="d-block text-muted text-truncate">{{ $pub->enlace_externo }}</small>
                </div>
                <a href="{{ $pub->enlace_externo }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius:999px;">
                    Abrir recurso <i class="fas fa-external-link-alt ml-1"></i>
                </a>
            </div>
        @endif

        @if(!empty($pub->etiquetas))
            <div class="mt-2">
                @foreach($pub->etiquetas as $tag)
                    <span class="ayllu-tag">#{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        {{-- Barra de Reacciones Interculturales --}}
        <hr class="my-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.5rem;">
            <div class="d-flex flex-wrap align-items-center" style="gap: 0.35rem;">
                @foreach(($catalogoReacciones ?? \App\Models\Ayllu\AylluReaccion::catalogo()) as $key => $info)
                    @php
                        $conteo = $pub->reacciones->where('tipo', $key)->count();
                        $yoReaccione = $pub->reacciones->where('user_id', auth()->id())->where('tipo', $key)->isNotEmpty();
                    @endphp
                    <form action="{{ route('ayllu.publicaciones.reaccionar', $pub->id) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $key }}">
                        <button type="submit" class="ayllu-reaccion-btn {{ $yoReaccione ? 'is-active' : '' }}" title="{{ $info['label'] }} ({{ $info['sub'] }})">
                            <i class="{{ $info['icono'] }}"></i>
                            <span>{{ $info['label'] }}</span>
                            @if($conteo > 0)
                                <span class="badge badge-pill badge-light ml-1">{{ $conteo }}</span>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>

            <button class="btn btn-sm btn-link text-muted font-weight-bold p-0" type="button" data-toggle="collapse" data-target="#comentarios-pub-{{ $pub->id }}">
                <i class="far fa-comment-dots mr-1"></i>
                {{ $pub->comentarios->count() }} {{ $pub->comentarios->count() === 1 ? 'aporte' : 'aportes' }}
            </button>
        </div>

        {{-- Comentarios y Diálogo de Saberes --}}
        <div class="collapse {{ $pub->comentarios->count() > 0 ? 'show' : '' }} mt-3" id="comentarios-pub-{{ $pub->id }}">
            @if($pub->comentarios->isNotEmpty())
                <div class="mb-2 pl-2" style="border-left: 2px solid var(--ayllu-border);">
                    @foreach($pub->comentarios as $com)
                        <div class="p-2 mb-2 rounded" style="background: var(--ayllu-surface-soft); font-size: 0.85rem;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div>
                                    <strong>{{ $com->autor?->name }} {{ $com->autor?->apellidos }}</strong>
                                    @if($com->es_aporte_docente)
                                        <span class="badge badge-info ml-1" style="font-size:0.65rem;">Orientación Docente</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $com->created_at?->diffForHumans() }}</small>
                            </div>
                            <div>{{ $com->contenido }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('ayllu.publicaciones.comentar', $pub->id) }}" method="POST" class="d-flex align-items-center" style="gap: 0.5rem;">
                @csrf
                <input type="text" name="contenido" class="form-control form-control-sm" placeholder="Comparte tu reflexión o diálogo de saberes (en castellano o quechua)…" required maxlength="2000" style="border-radius: 999px;">
                <button type="submit" class="btn btn-sm text-white font-weight-bold px-3" style="background: #33445a; border-radius: 999px;">
                    Enviar
                </button>
            </form>
        </div>
    </div>
</article>
