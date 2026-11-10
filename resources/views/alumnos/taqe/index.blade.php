@extends('layouts.alumno')
@section('titulo', "Investigaciones")
@section('contenido')
    @include('alumnos.partials.ui')
    @include('taqe._estilos')
    @php
        $finalizados = $misGrupos->where('estado', 'finalizado')->count();
        $totalEvidencias = $misGrupos->sum(fn ($g) => $g->evidencias->count());
        $iconoTipo = [
            'imagen' => ['fa-image', '#0e9467'], 'pdf' => ['fa-file-pdf', '#c0392b'], 'word' => ['fa-file-word', '#2563eb'],
            'audio' => ['fa-file-audio', '#8b5cf6'], 'video' => ['fa-file-video', '#d97706'], 'otro' => ['fa-file', '#64748b'],
        ];
    @endphp

    @include('partials.flash-swal')

    <div class="al-ui">
        {{-- ── Hero ── --}}
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-flask"></i> Investigaciones · Ferias de Investigación</div>
                    <h1>Mis grupos de investigación</h1>
                    <p class="al-hero-sub">Forma tu equipo, sube las evidencias de tu proceso y presenta tu trabajo final.</p>
                    @if ($misGrupos->isNotEmpty())
                        <div class="al-chips">
                            <span class="al-chip"><i class="fas fa-users"></i>{{ $misGrupos->count() }} {{ $misGrupos->count() === 1 ? 'grupo' : 'grupos' }}</span>
                            <span class="al-chip"><i class="fas fa-images"></i>{{ $totalEvidencias }} {{ $totalEvidencias === 1 ? 'evidencia' : 'evidencias' }}</span>
                            <span class="al-chip {{ $finalizados ? 'is-ok' : '' }}"><i class="fas fa-flag-checkered"></i>{{ $finalizados }} {{ $finalizados === 1 ? 'finalizado' : 'finalizados' }}</span>
                        </div>
                    @endif
                </div>
                <div class="al-hero-acciones">
                    @if ($cursosSinGrupo->isNotEmpty())
                        <button type="button" class="al-btn al-btn-oro" data-toggle="modal" data-target="#modalCrearGrupo">
                            <i class="fas fa-plus"></i> {{ $misGrupos->isEmpty() ? 'Crear mi primer grupo' : 'Nuevo grupo' }}
                        </button>
                    @endif
                    <a href="javascript:history.go(-1)" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </section>

        {{-- ── Invitaciones pendientes ── --}}
        @foreach ($invitacionesPendientes as $inv)
            <div class="tq-invitacion">
                <span class="tq-invitacion-icono"><i class="fas fa-envelope-open-text"></i></span>
                <div class="tq-invitacion-texto">
                    <div class="tq-invitacion-titulo">Te invitaron al grupo <strong>{{ $inv->nombre }}</strong></div>
                    <small>{{ $inv->curso->nombre }} · Invitación de {{ optional($inv->creador)->nombres }} {{ optional($inv->creador)->apellidos }}</small>
                </div>
                <div class="tq-invitacion-acciones">
                    <form action="{{ route('taqe.responder', $inv->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="respuesta" value="aceptado">
                        <button type="submit" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-check"></i> Aceptar</button>
                    </form>
                    <form action="{{ route('taqe.responder', $inv->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="respuesta" value="rechazado">
                        <button type="submit" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-times"></i> Rechazar</button>
                    </form>
                </div>
            </div>
        @endforeach

        {{-- ── Proyecto Integrador del ciclo (el mismo del sílabo): resumen corto; el detalle va en un popup ── --}}
        <div class="al-card">
            <div class="tq-franja">
                <span class="tq-franja-icono"><i class="fas fa-project-diagram"></i></span>
                <div class="tq-franja-texto">
                    <small>Tu Proyecto Integrador · {{ $alumno->ciclo ? 'Ciclo '.$alumno->ciclo->nombre : 'Sin ciclo' }}</small>
                    @if ($proyectoAlumno)
                        <strong>{{ $proyectoAlumno->nombre }}</strong>
                        @if ($proyectoAlumno->producto)
                            <span><i class="fas fa-box-open mr-1" style="color: var(--al-oro);"></i>{{ $proyectoAlumno->producto }}</span>
                        @endif
                    @else
                        <strong>Sin Proyecto Integrador asignado</strong>
                        <span>Por eso aún no puedes crear grupos. Avisa a la coordinación.</span>
                    @endif
                </div>
                <div class="tq-franja-acciones">
                    @if ($proyectoAlumno)
                        <button type="button" class="al-btn al-btn-petroleo al-btn-sm" data-toggle="modal" data-target="#modalProyecto">
                            <i class="fas fa-book-open"></i> Ver proyecto
                        </button>
                    @endif
                    <button type="button" class="al-btn al-btn-borde al-btn-sm" data-toggle="modal" data-target="#modalGuia">
                        <i class="fas fa-route"></i> ¿Cómo funciona?
                    </button>
                </div>
            </div>
        </div>

        <div>
            {{-- ── Mis grupos (a todo el ancho) ── --}}
            <div>
                @if ($misGrupos->isEmpty())
                    <div class="al-card">
                        <div class="al-vacio py-5">
                            <i class="fas fa-flask"></i>
                            <div class="font-weight-bold mb-1" style="color: var(--al-texto);">Aún no perteneces a ningún grupo de Investigaciones este periodo</div>
                            <div class="small mb-3">Crea uno para un curso y suma a tus compañeros; ellos aceptan la invitación desde aquí mismo.</div>
                            @if ($cursosSinGrupo->isNotEmpty())
                                <button type="button" class="al-btn al-btn-oro" data-toggle="modal" data-target="#modalCrearGrupo">
                                    <i class="fas fa-plus"></i> Crear mi primer grupo de Investigaciones
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach ($misGrupos as $grupo)
                    @php
                        $esOrganizador = $grupo->creador_alumno_id === $alumno->id;
                        // Organizador primero, luego aceptados y pendientes; los rechazados solo los ve el organizador (modal).
                        $ordenEstado = ['aceptado' => 1, 'pendiente' => 2];
                        $integrantesVisibles = $grupo->alumnos
                            ->reject(fn ($i) => $i->pivot->estado === 'rechazado')
                            ->sort(function ($a, $b) use ($grupo, $ordenEstado) {
                                $aOrg = $a->id === $grupo->creador_alumno_id;
                                $bOrg = $b->id === $grupo->creador_alumno_id;
                                if ($aOrg !== $bOrg) {
                                    return $aOrg ? -1 : 1;
                                }

                                return ($ordenEstado[$a->pivot->estado] ?? 9) <=> ($ordenEstado[$b->pivot->estado] ?? 9);
                            })
                            ->values();
                    @endphp

                    <article class="al-card tq-grupo" data-tono="{{ $grupo->proyecto_id ? $grupo->proyecto_id % 6 : '' }}">
                        <div class="al-card-cab">
                            <span class="al-card-cab-icono"><i class="fas fa-flask"></i></span>
                            <div style="flex:1; min-width:0;">
                                <h2>{{ $grupo->nombre }}</h2>
                                <p>{{ $grupo->curso->nombre }}</p>
                                @if ($grupo->proyecto)
                                    <div class="tq-proyecto"><i class="fas fa-project-diagram"></i> {{ $grupo->proyecto->producto ?: $grupo->proyecto->nombre }}</div>
                                @endif
                            </div>
                            <span class="al-badge {{ $grupo->estado === 'finalizado' ? 'is-ok' : 'is-info' }}">
                                <i class="fas {{ $grupo->estado === 'finalizado' ? 'fa-flag-checkered' : 'fa-spinner' }}"></i>
                                {{ $grupo->estado === 'finalizado' ? 'Finalizado' : 'En proceso' }}
                            </span>
                        </div>

                        {{-- Avance en 3 pasos --}}
                        @include('taqe._pasos')

                        {{-- Equipo a todo el ancho, con las acciones a la derecha --}}
                        <section class="tq-equipo">
                            <div class="tq-equipo-cab">
                                <div class="tq-bloque-titulo"><i class="fas fa-users"></i> Equipo · {{ $integrantesVisibles->where('pivot.estado', 'aceptado')->count() }} {{ $integrantesVisibles->where('pivot.estado', 'aceptado')->count() === 1 ? 'integrante' : 'integrantes' }}</div>
                                <div class="tq-grupo-acciones">
                                    @if ($esOrganizador)
                                        <button type="button" class="al-btn al-btn-borde al-btn-sm" data-toggle="modal" data-target="#modalEditarGrupo{{ $grupo->id }}">
                                            <i class="fas fa-user-cog"></i> Integrantes
                                        </button>
                                    @endif
                                    <button type="button" class="al-btn al-btn-borde al-btn-sm" data-toggle="modal" data-target="#modalAyuda{{ $grupo->id }}">
                                        <i class="fas fa-life-ring"></i> Pedir ayuda
                                    </button>
                                    <form action="{{ route('taqe.salir', $grupo->id) }}" method="POST"
                                        data-confirm="¿Seguro que quieres salir del grupo &quot;{{ $grupo->nombre }}&quot;?" data-confirm-danger>
                                        @csrf
                                        <button type="submit" class="al-btn al-btn-sm tq-btn-salir"><i class="fas fa-door-open"></i> Salir</button>
                                    </form>
                                </div>
                            </div>
                            <div class="taqe-team tq-equipo-lista">
                                @foreach ($integrantesVisibles as $integrante)
                                    @include('alumnos.taqe._integrante-row', [
                                        'integrante' => $integrante,
                                        'grupo' => $grupo,
                                        'alumnoActual' => $alumno,
                                        'mostrarAcciones' => false,
                                    ])
                                @endforeach
                            </div>
                        </section>

                        @php $entregado = $grupo->estaEntregado(); @endphp
                        {{-- Evidencias (izquierda) | Trabajo final (derecha) — mismas columnas que taqe/_archivos --}}
                        <div class="tq-archivos">
                            <section class="tq-columna">
                                <div class="tq-columna-cab">
                                    <h3><i class="fas fa-images"></i> Evidencias del proceso</h3>
                                    <span class="al-badge is-muted">{{ $grupo->evidencias->count() }}</span>
                                </div>
                                <div class="tq-columna-sub">Fotos, audios, videos, documentos o enlaces del trabajo de campo. Pasa al trabajo final lo que vayan a presentar con <i class="fas fa-arrow-up"></i>.</div>
                                <div class="tq-columna-lista">
                                    @forelse ($grupo->evidencias as $trabajo)
                                        @include('taqe._fila-archivo', [
                                            'trabajo' => $trabajo,
                                            'rutaVer' => 'taqe.trabajos.ver',
                                            'urlMover' => $entregado ? null : route('taqe.trabajos.mover', $trabajo->id),
                                            'urlEliminar' => ! $entregado && ($esOrganizador || $trabajo->subido_por_alumno_id === $alumno->id) ? route('taqe.trabajos.destroy', $trabajo->id) : null,
                                        ])
                                    @empty
                                        <p class="tq-nota">Todavía no suben evidencias a este grupo.</p>
                                    @endforelse
                                </div>
                                @if (! $entregado)
                                    @include('taqe._form-subir', ['grupo' => $grupo, 'destino' => 'evidencia'])
                                @endif
                            </section>

                            {{-- Trabajo final: varios archivos/enlaces, subidos directo o pasados desde evidencias --}}
                            <section class="tq-columna is-final">
                                <div class="tq-columna-cab">
                                    <h3><i class="fas fa-flag-checkered"></i> Trabajo final</h3>
                                    @if ($entregado)
                                        <span class="al-badge is-ok"><i class="fas fa-check"></i> Entregado</span>
                                    @else
                                        <span class="al-badge is-muted">{{ $grupo->finales->count() }}</span>
                                    @endif
                                </div>
                                <div class="tq-columna-sub">Lo que presentarán en la feria: puede tener varios archivos y enlaces.</div>
                                <div class="tq-columna-lista">
                                    @forelse ($grupo->finales as $trabajo)
                                        @include('taqe._fila-archivo', [
                                            'trabajo' => $trabajo,
                                            'rutaVer' => 'taqe.trabajos.ver',
                                            'urlMover' => $entregado ? null : route('taqe.trabajos.mover', $trabajo->id),
                                            'urlEliminar' => ! $entregado && ($esOrganizador || $trabajo->subido_por_alumno_id === $alumno->id) ? route('taqe.trabajos.destroy', $trabajo->id) : null,
                                        ])
                                    @empty
                                        <p class="tq-nota">Todavía está vacío. Sube aquí los archivos o enlaces finales, o pasa una evidencia con la flecha <i class="fas fa-arrow-up"></i>.</p>
                                    @endforelse
                                </div>

                                @if (! $entregado)
                                    @include('taqe._form-subir', ['grupo' => $grupo, 'destino' => 'final'])
                                @endif

                                {{-- Entrega: la hace el organizador; mientras no entregue, el equipo puede seguir armándolo --}}
                                <div class="tq-entrega {{ $entregado ? 'is-entregado' : '' }}">
                                    @if ($entregado)
                                        <i class="fas fa-check-circle" style="color: var(--al-ok);"></i>
                                        <span><strong>Entregado</strong> el {{ optional($grupo->entregado_at)->format('d/m/Y H:i') }}. Ya no se pueden modificar los archivos.</span>
                                        @if ($esOrganizador)
                                            <form action="{{ route('taqe.reabrir', $grupo->id) }}" method="POST"
                                                data-confirm="¿Reabrir la entrega? El grupo vuelve a En proceso para corregir, y luego tendrás que entregar de nuevo.">
                                                @csrf
                                                <button type="submit" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-lock-open"></i> Reabrir entrega</button>
                                            </form>
                                        @endif
                                    @elseif ($esOrganizador)
                                        <i class="fas fa-info-circle" style="color: var(--al-info);"></i>
                                        <span>Cuando el trabajo final esté completo, entrégalo. Después no se podrá modificar (salvo que lo reabras).</span>
                                        <form action="{{ route('taqe.entregar', $grupo->id) }}" method="POST"
                                            data-confirm="¿Entregar el trabajo final con {{ $grupo->finales->count() }} {{ $grupo->finales->count() === 1 ? 'elemento' : 'elementos' }}? El grupo quedará Finalizado.">
                                            @csrf
                                            <button type="submit" class="al-btn al-btn-oro al-btn-sm" @disabled($grupo->finales->isEmpty())><i class="fas fa-paper-plane"></i> Entregar trabajo final</button>
                                        </form>
                                    @else
                                        <i class="fas fa-info-circle" style="color: var(--al-info);"></i>
                                        <span>Todo el equipo puede armar el trabajo final; el organizador lo entrega.</span>
                                    @endif
                                </div>
                            </section>
                        </div>
                    </article>

                    @if ($esOrganizador)
                        {{-- ── Editar integrantes (solo organizador) ── --}}
                        <div class="modal fade" id="modalEditarGrupo{{ $grupo->id }}" tabindex="-1" role="dialog"
                            aria-labelledby="modalEditarGrupoLabel{{ $grupo->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content tq-modal">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditarGrupoLabel{{ $grupo->id }}">
                                            <i class="fas fa-user-cog mr-1"></i> Integrantes de "{{ $grupo->nombre }}"
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="tq-modal-etiqueta">Integrantes</p>
                                        <div class="taqe-team">
                                            @foreach ($grupo->alumnos->reject(fn ($i) => $i->pivot->estado === 'rechazado') as $integrante)
                                                @include('alumnos.taqe._integrante-row', [
                                                    'integrante' => $integrante,
                                                    'grupo' => $grupo,
                                                    'alumnoActual' => $alumno,
                                                    'mostrarAcciones' => true,
                                                    'rutaCambiarOrganizador' => 'taqe.organizador.cambiar',
                                                ])
                                            @endforeach
                                        </div>

                                        @php $rechazaronAntes = $grupo->alumnos->where('pivot.estado', 'rechazado'); @endphp
                                        @if ($rechazaronAntes->isNotEmpty())
                                            <p class="tq-modal-etiqueta mt-3">Rechazaron antes</p>
                                            @foreach ($rechazaronAntes as $integrante)
                                                <div class="taqe-team-reinvite">
                                                    <span>{{ $integrante->apellidos }}, {{ $integrante->nombres }}</span>
                                                    <form action="{{ route('taqe.integrantes.agregar', $grupo->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="integrantes[]" value="{{ $integrante->id }}">
                                                        <button type="submit" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-redo"></i> Reinvitar</button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        @endif

                                        <p class="tq-modal-etiqueta mt-3">Invitar a nuevos compañeros</p>
                                        <form action="{{ route('taqe.integrantes.agregar', $grupo->id) }}" method="POST">
                                            @csrf
                                            @if (($companerosPorGrupo[$grupo->id] ?? collect())->isEmpty())
                                                <p class="tq-nota">No hay más compañeros disponibles en este curso para invitar.</p>
                                            @else
                                                <div class="taqe-companion-list mb-2">
                                                    @foreach ($companerosPorGrupo[$grupo->id] as $c)
                                                        <label><input type="checkbox" name="integrantes[]" value="{{ $c->id }}"> {{ $c->apellidos }}, {{ $c->nombres }}</label>
                                                    @endforeach
                                                </div>
                                                <button type="submit" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-user-plus"></i> Invitar seleccionados</button>
                                            @endif
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="al-btn al-btn-borde al-btn-sm" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ── Solicitar ayuda al Investigador (cualquier integrante aceptado) ── --}}
                    <div class="modal fade" id="modalAyuda{{ $grupo->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="modalAyudaLabel{{ $grupo->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content tq-modal">
                                <form action="{{ route('taqe.solicitarAyuda', $grupo->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalAyudaLabel{{ $grupo->id }}">
                                            <i class="fas fa-life-ring mr-1"></i> Pedir ayuda — "{{ $grupo->nombre }}"
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="tq-nota mb-3">El Investigador verá tu solicitud y se pondrá en contacto para ayudarte.</p>
                                        <div class="form-group">
                                            <label class="tq-modal-etiqueta">Motivo</label>
                                            <select name="motivo" class="form-control form-control-sm taqe-ayuda-motivo" data-target-otro="#taqeAyudaOtro{{ $grupo->id }}" required>
                                                <option value="" selected disabled>— Elige un motivo —</option>
                                                <option value="cambiar_organizador">Cambiar de organizador</option>
                                                <option value="disolver_grupo">Disolver el grupo</option>
                                                <option value="otro">Otro</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0" id="taqeAyudaOtro{{ $grupo->id }}" style="display:none;">
                                            <label class="tq-modal-etiqueta">Cuéntanos qué pasa</label>
                                            <textarea name="mensaje" class="form-control form-control-sm" rows="3" maxlength="1000" placeholder="Describe brevemente el problema…"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="al-btn al-btn-borde al-btn-sm" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-paper-plane"></i> Enviar solicitud</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Periodos anteriores: archivo de solo lectura (un grupo por periodo) ── --}}
        @if ($gruposAnteriores->isNotEmpty())
            <div class="tq-anteriores-titulo">
                <span><i class="fas fa-archive"></i> Mis grupos de periodos anteriores</span>
                <small>Solo lectura: puedes ver y descargar sus evidencias y trabajos finales.</small>
            </div>
            @foreach ($gruposAnteriores as $grupo)
                @include('taqe._grupo-gestion', [
                    'grupo' => $grupo,
                    'rutaVer' => 'taqe.trabajos.ver',
                    'soloLectura' => true,
                ])
            @endforeach
        @endif
    </div>

    {{-- ── Popup: Proyecto Integrador completo (texto enriquecido del CRUD de admin, saneado con HTMLPurifier) ── --}}
    @if ($proyectoAlumno)
        <div class="modal fade al-ui" id="modalProyecto" tabindex="-1" role="dialog" aria-labelledby="modalProyectoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content tq-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalProyectoLabel"><i class="fas fa-project-diagram mr-1"></i> {{ $proyectoAlumno->nombre }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap mb-3" style="gap:.4rem;">
                            @if ($alumno->ciclo)
                                <span class="al-badge is-muted"><i class="fas fa-layer-group"></i> Ciclo {{ $alumno->ciclo->nombre }}</span>
                            @endif
                            @if ($proyectoAlumno->producto)
                                <span class="al-badge is-info" style="white-space: normal;"><i class="fas fa-box-open"></i> {{ $proyectoAlumno->producto }}</span>
                            @endif
                        </div>
                        @if (filled(strip_tags((string) $proyectoAlumno->descripcion)))
                            <div class="tq-proyecto-descripcion">{!! $proyectoAlumno->descripcionHtml() !!}</div>
                        @else
                            <p class="tq-nota mb-0">La coordinación aún no ha escrito la descripción de este proyecto.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="al-btn al-btn-borde al-btn-sm" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Popup: cómo funciona + Investigador(a) ── --}}
    <div class="modal fade al-ui" id="modalGuia" tabindex="-1" role="dialog" aria-labelledby="modalGuiaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content tq-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGuiaLabel"><i class="fas fa-route mr-1"></i> Cómo funciona Investigaciones</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <ol class="tq-guia">
                        <li><b>Crea tu grupo</b><span>Elige un curso de tu ciclo y ponle un nombre. Tú quedas como organizador(a). Solo puedes estar en <strong>un grupo por periodo</strong>.</span></li>
                        <li><b>Invita a tu equipo</b><span>Solo compañeros de tu mismo Proyecto Integrador. Aceptan la invitación desde su intranet.</span></li>
                        <li><b>Sube evidencias</b><span>Fotos, audios, videos, documentos o enlaces del proceso. Cualquier integrante puede subirlas.</span></li>
                        <li><b>Arma y entrega el trabajo final</b><span>Todo el equipo puede subir archivos o pasar evidencias al final; el organizador lo entrega y el grupo queda Finalizado.</span></li>
                    </ol>
                    <div class="px-4 pb-3">
                        <p class="tq-modal-etiqueta"><i class="fas fa-user-shield mr-1"></i> Investigador(a) — para lo que el organizador no pueda resolver</p>
                        @forelse ($investigadores as $inv)
                            <div class="tq-contacto">
                                <div class="tq-contacto-nombre">{{ optional($inv->docente)->nombre ?? $inv->name }}</div>
                                <a href="mailto:{{ $inv->email }}"><i class="fas fa-envelope"></i> {{ $inv->email }}</a>
                                @if (optional($inv->docente)->telefono)
                                    <span><i class="fas fa-phone"></i> {{ $inv->docente->telefono }}</span>
                                @endif
                            </div>
                        @empty
                            <p class="tq-nota mb-0">No hay un Investigador asignado por ahora. Usa igual "Pedir ayuda" desde tu grupo: quedará pendiente hasta que se asigne uno.</p>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('taqe.guia.pdf') }}" target="_blank" class="al-btn al-btn-oro al-btn-sm">
                        <i class="fas fa-file-pdf"></i> Descargar Guía PDF
                    </a>
                    <button type="button" class="al-btn al-btn-borde al-btn-sm" data-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    @include('taqe._visor')

    {{-- ── Crear nuevo grupo (popup) ── --}}
    @if ($cursosSinGrupo->isNotEmpty())
        <div class="modal fade al-ui" id="modalCrearGrupo" tabindex="-1" role="dialog" aria-labelledby="modalCrearGrupoLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content tq-modal">
                    <form action="{{ route('taqe.store') }}" method="POST" id="formCrearGrupo">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCrearGrupoLabel"><i class="fas fa-flask mr-1"></i> Crear grupo de Investigaciones</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <p class="tq-nota mb-3">Elige el curso, ponle un nombre a tu grupo y suma a tus compañeros: ellos deberán aceptar para quedar integrados.</p>
                            <div class="form-group">
                                <label class="tq-modal-etiqueta">Curso</label>
                                <select name="curso_id" id="taqe-curso-select" class="form-control form-control-sm" required>
                                    <option value="" selected disabled>— Selecciona un curso —</option>
                                    @foreach ($cursosSinGrupo as $curso)
                                        <option value="{{ $curso->id }}" {{ old('curso_id') == $curso->id ? 'selected' : '' }}>{{ $curso->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="tq-modal-etiqueta">Nombre del grupo</label>
                                <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control form-control-sm" maxlength="255" required placeholder="Ej. Los Exploradores">
                            </div>
                            <div class="form-group mb-0">
                                <label class="tq-modal-etiqueta">Integrantes (opcional)</label>
                                <div class="taqe-companion-list" id="taqe-companion-list">
                                    <span class="tq-nota">Elige un curso para ver a tus compañeros.</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="al-btn al-btn-borde al-btn-sm" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="al-btn al-btn-oro al-btn-sm"><i class="fas fa-flask"></i> Crear grupo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>

    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('taqe-curso-select');
        var lista = document.getElementById('taqe-companion-list');

        // "Solicitar ayuda": el detalle solo es obligatorio (y visible) con el motivo "Otro".
        document.querySelectorAll('.taqe-ayuda-motivo').forEach(function (motivo) {
            var bloque = document.querySelector(motivo.dataset.targetOtro);
            if (!bloque) return;
            var campoMensaje = bloque.querySelector('textarea');
            motivo.addEventListener('change', function () {
                var esOtro = motivo.value === 'otro';
                bloque.style.display = esOtro ? '' : 'none';
                if (campoMensaje) campoMensaje.required = esOtro;
            });
        });

        if (!select) return;

        select.addEventListener('change', function () {
            var cursoId = this.value;
            if (!cursoId) return;
            lista.innerHTML = '<span class="tq-nota">Cargando…</span>';

            fetch('{{ url('taqe/curso') }}/' + cursoId + '/alumnos', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (companeros) {
                    if (!companeros.length) {
                        lista.innerHTML = '<span class="tq-nota">No hay más alumnos matriculados en este curso.</span>';
                        return;
                    }
                    lista.innerHTML = companeros.map(function (c) {
                        var nombre = String(c.nombre).replace(/[&<>"]/g, function (ch) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch]; });
                        return '<label><input type="checkbox" name="integrantes[]" value="' + parseInt(c.id, 10) + '"> ' + nombre + '</label>';
                    }).join('');
                })
                .catch(function () {
                    lista.innerHTML = '<span class="text-danger small">No se pudo cargar la lista. Intenta de nuevo.</span>';
                });
        });

        // Si volvió con errores, reabrir el modal y recargar los compañeros del curso elegido.
        // (Antes el "change" se disparaba ANTES de registrar el listener y la lista no cargaba.)
        @if ($errors->any() || old('curso_id'))
            $('#modalCrearGrupo').modal('show');
            @if (old('curso_id'))
                select.dispatchEvent(new Event('change'));
            @endif
        @endif
    });
</script>
@endpush
