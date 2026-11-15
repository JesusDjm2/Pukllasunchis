{{--
    Tarjeta de un grupo de Investigaciones para docente, Investigador y Super Admin — misma estructura que la del
    alumno (alumnos/taqe/index): cabecera con el tono de su Proyecto Integrador, avance en 3 pasos, equipo a
    todo el ancho y debajo Evidencias (izquierda) | Trabajo final (derecha).
    Espera: $grupo; opcionales:
      $rutaVer       name de ruta {trabajo}/ver (null = sin enlaces a archivos)
      $rutaEliminar  name de ruta DELETE de trabajos (solo Super Admin)
      $rutaZip       name de ruta de descargas ZIP {grupo}/{que}
      $superAdmin    bool: muestra gestionar integrantes / eliminar grupo (rutas admin.taqe.*)
      $mostrarContexto bool: chips de periodo, programa y ciclo (vistas institucionales)
      $soloLectura   bool: etiqueta "Solo lectura" (grupos del alumno de periodos anteriores)
--}}
@php
    $superAdmin = $superAdmin ?? false;
    $mostrarContexto = $mostrarContexto ?? true;
    $soloLectura = $soloLectura ?? false;
    $integrantes = $grupo->alumnos->reject(fn ($i) => $i->pivot->estado === 'rechazado')
        ->sortBy(fn ($i) => [$i->id === $grupo->creador_alumno_id ? 0 : 1, $i->pivot->estado === 'aceptado' ? 0 : 1]);
@endphp
<article class="al-card tq-grupo" data-tono="{{ $grupo->proyecto_id ? $grupo->proyecto_id % 6 : '' }}">
    <div class="al-card-cab">
        <span class="al-card-cab-icono"><i class="fas fa-flask"></i></span>
        <div style="flex:1; min-width:0;">
            <h2>{{ $grupo->nombre }}</h2>
            <p>{{ optional($grupo->curso)->nombre }}</p>
            @if ($grupo->proyecto)
                <div class="tq-proyecto"><i class="fas fa-project-diagram"></i> {{ $grupo->proyecto->producto ?: $grupo->proyecto->nombre }}</div>
            @endif
            @if ($mostrarContexto)
                <div class="tq-contexto">
                    @if ($grupo->periodoActual)<span><i class="far fa-calendar-alt"></i>{{ $grupo->periodoActual->nombre }}</span>@endif
                    @if ($grupo->programa)<span><i class="fas fa-graduation-cap"></i>{{ $grupo->programa->nombre }}</span>@endif
                    @if ($grupo->ciclo)<span><i class="fas fa-layer-group"></i>Ciclo {{ $grupo->ciclo->nombre }}</span>@endif
                </div>
            @endif
        </div>
        @if ($soloLectura)
            <span class="al-badge is-muted"><i class="fas fa-lock"></i> Solo lectura</span>
        @endif
        <span class="al-badge {{ $grupo->estado === 'finalizado' ? 'is-ok' : 'is-info' }}">
            <i class="fas {{ $grupo->estado === 'finalizado' ? 'fa-flag-checkered' : 'fa-spinner' }}"></i>
            {{ $grupo->estado === 'finalizado' ? 'Finalizado' : 'En proceso' }}
        </span>
    </div>

    @include('taqe._pasos')

    {{-- Equipo a todo el ancho, con las acciones a la derecha --}}
    <section class="tq-equipo">
        <div class="tq-equipo-cab">
            <div class="tq-bloque-titulo"><i class="fas fa-users"></i> Equipo · {{ $integrantes->where('pivot.estado', 'aceptado')->count() }} {{ $integrantes->where('pivot.estado', 'aceptado')->count() === 1 ? 'integrante' : 'integrantes' }}</div>
            @if ($superAdmin || ! empty($rutaZip))
                <div class="tq-grupo-acciones">
                    @if ($superAdmin)
                        <button type="button" class="al-btn al-btn-borde al-btn-sm" data-toggle="modal" data-target="#modalGestionar{{ $grupo->id }}">
                            <i class="fas fa-user-cog"></i> Integrantes
                        </button>
                    @endif
                    @if (! empty($rutaZip))
                        @include('taqe._descargas', ['grupo' => $grupo, 'rutaZip' => $rutaZip])
                    @endif
                    @if ($superAdmin)
                        <form action="{{ route('admin.taqe.destroy', $grupo->id) }}" method="POST"
                            data-confirm="¿Eliminar el grupo &quot;{{ $grupo->nombre }}&quot; por completo? Se borran sus integrantes y todos sus archivos (también de Drive), y se avisa a los aceptados." data-confirm-danger>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="al-btn al-btn-sm tq-btn-salir"><i class="fas fa-trash-alt"></i> Eliminar grupo</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
        <div class="taqe-team tq-equipo-lista">
            @forelse ($integrantes as $integrante)
                @include('alumnos.taqe._integrante-row', [
                    'integrante' => $integrante,
                    'grupo' => $grupo,
                    'alumnoActual' => null,
                    'mostrarAcciones' => $superAdmin,
                    'rutaRetirar' => 'admin.taqe.integrantes.retirar',
                ])
            @empty
                <p class="tq-nota p-2 mb-0">Sin integrantes.</p>
            @endforelse
        </div>
    </section>

    {{-- Evidencias (izquierda) | Trabajo final (derecha) --}}
    @include('taqe._archivos', ['grupo' => $grupo, 'rutaVer' => $rutaVer ?? null, 'rutaEliminar' => $rutaEliminar ?? null])
</article>

@if ($superAdmin)
    {{-- ── Gestionar integrantes (Super Admin): la lista de compañeros se carga al abrir ── --}}
    <div class="modal fade al-ui" id="modalGestionar{{ $grupo->id }}" tabindex="-1" role="dialog"
        aria-labelledby="modalGestionarLabel{{ $grupo->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content tq-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGestionarLabel{{ $grupo->id }}">
                        <i class="fas fa-user-cog mr-1"></i> Integrantes de "{{ $grupo->nombre }}"
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    @php $rechazaronAntes = $grupo->alumnos->where('pivot.estado', 'rechazado'); @endphp
                    @if ($rechazaronAntes->isNotEmpty())
                        <p class="tq-modal-etiqueta">Rechazaron antes</p>
                        @foreach ($rechazaronAntes as $integrante)
                            <div class="taqe-team-reinvite">
                                <span>{{ $integrante->apellidos }}, {{ $integrante->nombres }}</span>
                                <form action="{{ route('admin.taqe.integrantes.agregar', $grupo->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="integrantes[]" value="{{ $integrante->id }}">
                                    <button type="submit" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-redo"></i> Reinvitar</button>
                                </form>
                            </div>
                        @endforeach
                    @endif

                    <p class="tq-modal-etiqueta mt-3">Invitar a compañeros del mismo curso</p>
                    <form action="{{ route('admin.taqe.integrantes.agregar', $grupo->id) }}" method="POST" class="taqe-sa-form-invitar" data-grupo="{{ $grupo->id }}">
                        @csrf
                        <div class="taqe-companion-list mb-2" data-lista-companeros>
                            <span class="tq-nota">Cargando…</span>
                        </div>
                        <button type="submit" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-user-plus"></i> Invitar seleccionados</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="al-btn al-btn-borde al-btn-sm" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endif
