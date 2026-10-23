@extends('layouts.profesionalizacion')
@section('titulo', 'Ficha técnica')
@section('contenido')
    @include('alumnos.partials.ui')
    @php
        $matriculaAbierta = $alumno && $formularioHabilitado && $periodoActualPpd && ! $matriculaActual;
    @endphp

    <div class="al-ui">
        @if (Session::has('success'))
            <div class="al-aviso is-info" role="status"><i class="fas fa-check-circle"></i><div>{{ Session::get('success') }}</div></div>
        @endif

        @if ($alumno)
            @php
                $usuario = $alumno->user;
                $programa = $usuario?->programa;
                $iniciales = mb_strtoupper(mb_substr(trim((string) $alumno->nombres), 0, 1) . mb_substr(trim((string) $alumno->apellidos), 0, 1));

                // Sílabo de cada curso: se rige por el periodo FID (único "periodo actual" del sistema de sílabos).
                $silabosVisibles = ! $periodoActual || $periodoActual->silabosVisibles();
                $estadoSilabo = $cursos->mapWithKeys(function ($curso) use ($periodoActual, $silabosVisibles) {
                    $estructurado = $curso->silabos->firstWhere('periodo_actual_id', $periodoActual->id ?? null)
                        ?? $curso->silabos->firstWhere('periodo', $periodoActual->nombre ?? null);
                    $pdf = $curso->silabosPdf->firstWhere('periodo_actual_id', $periodoActual->id ?? null);
                    $url = $estructurado ? route('silabo.pdf', $estructurado->id)
                        : ($pdf ? asset('docentes/silabo/' . $pdf->pdf)
                        : ($curso->silabo ? asset('docentes/silabo/' . $curso->silabo) : null));
                    if (! $url) {
                        return [$curso->id => ['tipo' => 'sin', 'texto' => 'Sin sílabo']];
                    }

                    return [$curso->id => $silabosVisibles ? ['tipo' => 'ok', 'url' => $url] : ['tipo' => 'pronto', 'texto' => 'Disponible pronto']];
                });
                $silabosListos = $estadoSilabo->where('tipo', 'ok')->count();

                // Plan del programa por ciclo (I, II…), cursos en orden alfabético; los sin ciclo al final.
                $porCiclo = $cursos->filter->ciclo->groupBy('ciclo_id')
                    ->sortBy(fn ($grupo) => $grupo->first()->ciclo->ordenCiclo())
                    ->map(fn ($grupo) => $grupo->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values());
                $sinCiclo = $cursos->reject->ciclo->sortBy('nombre')->values();

                $pendientesCurso = $usuario ? collect($usuario->cursosPendientes()) : collect();
                $tieneCalificaciones = $alumno->calificaciones()->exists();
                $hayTitulacion = ! empty($seguimientoTitulacion) || ! empty($guiaTitulacion);
                $estadoTitulacion = ! empty($seguimientoTitulacion) ? $seguimientoTitulacion->avance()->estadoNombre() : null;
                $nombreCiclo = fn ($c) => $c ? (preg_match('/^ciclo\s/i', $c->nombre) ? $c->nombre : 'Ciclo ' . $c->nombre) : null;
            @endphp

            {{-- ── Hero: identidad + estado de matrícula ── --}}
            <section class="al-hero">
                <div class="al-hero-fila">
                    @if ($usuario && $usuario->foto)
                        <img src="{{ asset('img/estudiantes/' . $usuario->foto) }}" alt="Foto de {{ $alumno->nombres }}" class="al-hero-foto">
                    @else
                        <div class="al-hero-foto al-hero-foto-vacia" aria-hidden="true">{{ $iniciales }}</div>
                    @endif
                    <div class="al-hero-texto">
                        <div class="al-kicker">Profesionalización docente · Ficha técnica</div>
                        <h1>{{ $alumno->nombres }} {{ $alumno->apellidos }}</h1>
                        <div class="al-chips">
                            @if ($programa)
                                <span class="al-chip"><i class="fas fa-graduation-cap"></i>{{ $programa->nombre }}</span>
                            @endif
                            @if ($alumno->ciclo)
                                <span class="al-chip"><i class="fas fa-layer-group"></i>{{ $nombreCiclo($alumno->ciclo) }}</span>
                            @endif
                            <span class="al-chip"><i class="fas fa-id-card"></i>DNI {{ $alumno->dni }}</span>
                            @if ($matriculaActual)
                                <span class="al-chip is-ok"><i class="fas fa-check-circle"></i>Matriculado · {{ $periodoActualPpd->nombre }}</span>
                            @elseif ($matriculaAbierta)
                                <span class="al-chip is-warn"><i class="fas fa-exclamation-circle"></i>Matrícula pendiente</span>
                            @endif
                        </div>
                    </div>
                    <div class="al-hero-acciones">
                        @if ($matriculaAbierta)
                            <a class="al-btn al-btn-oro" href="{{ route('ppd.edit', $alumno->id) }}"><i class="fas fa-graduation-cap"></i> Completar matrícula</a>
                        @endif
                        @if ($tieneCalificaciones)
                            <a class="al-btn al-btn-claro" href="{{ route('calificacionesppd', $alumno->id) }}"><i class="fas fa-clipboard-list"></i> Calificaciones</a>
                        @endif
                    </div>
                </div>
            </section>

            {{-- ── Avisos (lo urgente arriba, antes de las pestañas) ── --}}
            @if ($matriculaAbierta)
                <div class="al-aviso is-warn"><i class="fas fa-exclamation-circle"></i>
                    <div>El período <strong>{{ $periodoActualPpd->nombre }}</strong> está abierto. Actualiza tus datos con
                        <strong>Completar matrícula</strong>; no es necesario volver a llenar toda la ficha.</div>
                </div>
            @endif
            @if (! $silabosVisibles)
                <div class="al-aviso is-info"><i class="fas fa-info-circle"></i>
                    <div>Los sílabos de <strong>{{ $periodoActual->nombre }}</strong> estarán disponibles a partir del
                        <strong>{{ $periodoActual->fechaSilabosVisibles()->format('d/m/Y') }}</strong>. Los docentes están trabajando en ellos.</div>
                </div>
            @endif
            @if ($pendientesCurso->isNotEmpty())
                <div class="al-aviso is-mal">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Tienes {{ $pendientesCurso->count() === 1 ? 'un curso' : $pendientesCurso->count() . ' cursos' }} a cargo</strong>
                        <div class="small mb-1">Es responsabilidad del estudiante solicitar la subsanación de los cursos pendientes.</div>
                        <div class="d-flex flex-wrap" style="gap:.35rem;">
                            @foreach ($pendientesCurso as $cp)
                                <span class="al-badge is-mal">{{ trim($cp) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Indicadores ── --}}
            <div class="al-stats">
                <div class="al-stat">
                    <span class="al-stat-icono"><i class="fas fa-book-open"></i></span>
                    <div><div class="al-stat-valor">{{ $cursos->count() }}</div><div class="al-stat-etiqueta">Cursos del programa</div></div>
                </div>
                <div class="al-stat">
                    <span class="al-stat-icono is-oro"><i class="fas fa-file-alt"></i></span>
                    <div><div class="al-stat-valor">{{ $silabosListos }}</div><div class="al-stat-etiqueta">Sílabos disponibles</div></div>
                </div>
                <div class="al-stat">
                    <span class="al-stat-icono {{ $pendientesCurso->isNotEmpty() ? 'is-mal' : 'is-ok' }}"><i class="fas {{ $pendientesCurso->isNotEmpty() ? 'fa-exclamation-triangle' : 'fa-check' }}"></i></span>
                    <div><div class="al-stat-valor">{{ $pendientesCurso->count() }}</div><div class="al-stat-etiqueta">Cursos a cargo</div></div>
                </div>
                @if ($hayTitulacion)
                    <a class="al-stat text-decoration-none" href="#titulacion" data-al-ir>
                        <span class="al-stat-icono is-info"><i class="fas fa-user-graduate"></i></span>
                        <div><div class="al-stat-valor" style="font-size:.95rem;">{{ $estadoTitulacion ?? 'Puedes iniciar' }}</div><div class="al-stat-etiqueta">Titulación</div></div>
                    </a>
                @endif
            </div>

            {{-- ── Pestañas: cada bloque largo en su sección ── --}}
            <div>
                <div class="al-tabs" role="tablist" aria-label="Secciones de la ficha" data-al-tabs>
                    <button type="button" class="al-tab is-activa" role="tab" data-panel="cursos" aria-selected="true">
                        <i class="fas fa-book-open"></i> Mis cursos <span class="al-badge is-muted">{{ $cursos->count() }}</span>
                    </button>
                    @if ($hayTitulacion)
                        <button type="button" class="al-tab" role="tab" data-panel="titulacion" aria-selected="false" tabindex="-1">
                            <i class="fas fa-user-graduate"></i> Titulación
                            @if (! empty($guiaTitulacion))<span class="al-tab-marca">Nuevo</span>@endif
                        </button>
                    @endif
                    <button type="button" class="al-tab" role="tab" data-panel="datos" aria-selected="false" tabindex="-1">
                        <i class="fas fa-user"></i> Mis datos
                    </button>
                </div>

                {{-- Mis cursos: plan completo del programa por ciclo --}}
                <section class="al-panel" role="tabpanel" data-panel="cursos">
                    <div class="al-card">
                        <div class="al-card-cab">
                            <span class="al-card-cab-icono"><i class="fas fa-book-open"></i></span>
                            <div><h2>Cursos del programa</h2><p>{{ $programa->nombre ?? 'Programa PPD' }} · plan de un año, por ciclo</p></div>
                        </div>
                        @forelse ($porCiclo as $grupo)
                            @php $cicloG = $grupo->first()->ciclo; @endphp
                            <div class="al-grupo">
                                <i class="fas fa-layer-group"></i> {{ $nombreCiclo($cicloG) }}
                                @if ($alumno->ciclo_id === $cicloG->id)<span class="al-badge is-info">Tu ciclo</span>@endif
                                <span class="al-badge is-muted">{{ $grupo->count() }} {{ $grupo->count() === 1 ? 'curso' : 'cursos' }}</span>
                            </div>
                            <ul class="al-cursos">
                                @foreach ($grupo as $curso)
                                    @include('alumnos.ppd._curso', ['curso' => $curso, 'es' => $estadoSilabo[$curso->id]])
                                @endforeach
                            </ul>
                        @empty
                            @if ($sinCiclo->isEmpty())
                                <div class="al-vacio"><i class="fas fa-info-circle"></i>Aún no hay cursos registrados para tu programa.</div>
                            @endif
                        @endforelse
                        @if ($sinCiclo->isNotEmpty())
                            <div class="al-grupo"><i class="fas fa-exclamation-triangle"></i> Sin ciclo asignado
                                <span class="al-badge is-muted">{{ $sinCiclo->count() }}</span></div>
                            <ul class="al-cursos">
                                @foreach ($sinCiclo as $curso)
                                    @include('alumnos.ppd._curso', ['curso' => $curso, 'es' => $estadoSilabo[$curso->id]])
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>

                {{-- Titulación: avance del proceso o guía para iniciarlo (parciales compartidos con FID) --}}
                @if ($hayTitulacion)
                    <section class="al-panel" role="tabpanel" data-panel="titulacion" hidden>
                        @if (! empty($seguimientoTitulacion))
                            @include('titulacion._avance_alumno', ['seguimiento' => $seguimientoTitulacion])
                        @else
                            @include('titulacion._guia_inicio')
                        @endif
                    </section>
                @endif

                {{-- Mis datos --}}
                <section class="al-panel" role="tabpanel" data-panel="datos" hidden>
                    <div class="al-card">
                        <div class="al-card-cab">
                            <span class="al-card-cab-icono"><i class="fas fa-user"></i></span>
                            <div><h2>Datos personales</h2><p>Si algo está mal, avisa a Secretaría Académica.</p></div>
                        </div>
                        <div class="al-card-cuerpo pt-1">
                            <dl class="al-datos">
                                <div class="al-dato"><dt>Nombre completo</dt><dd>{{ $alumno->nombres }} {{ $alumno->apellidos }}</dd></div>
                                <div class="al-dato"><dt>DNI</dt><dd>{{ $alumno->dni }}</dd></div>
                                <div class="al-dato"><dt>Correo</dt><dd><a href="mailto:{{ $alumno->email }}">{{ $alumno->email }}</a></dd></div>
                                <div class="al-dato"><dt>Teléfono</dt><dd>{{ $alumno->numero ?: '—' }}</dd></div>
                                <div class="al-dato"><dt>Núm. de referencia</dt><dd>{{ $alumno->numero_referencia ?: '—' }}</dd></div>
                                <div class="al-dato" style="grid-column: 1 / -1;"><dt>Domicilio</dt>
                                    <dd>{{ $alumno->direccion ?: '—' }}<br><small class="text-muted">{{ collect([$alumno->distrito, $alumno->provincia, $alumno->departamento])->filter()->implode(' · ') }}</small></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>
            </div>
        @else
            <section class="al-hero">
                <div class="al-hero-fila">
                    <div class="al-hero-texto">
                        <div class="al-kicker">Profesionalización docente · Ficha técnica</div>
                        <h1>Completa tu ficha de estudiante</h1>
                        <p class="al-hero-sub">Aún no tienes ficha PPD registrada en el sistema. Llénala para ver tus cursos, sílabos y calificaciones.</p>
                    </div>
                    <div class="al-hero-acciones">
                        <a class="al-btn al-btn-oro" href="{{ route('formPPD') }}"><i class="fas fa-edit"></i> Ir al formulario</a>
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    @if (session('mostrar_popup_matricula'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: '¡Formulario enviado!',
                    html: 'Tu formulario fue llenado con éxito.<br>El área de cobranzas verificará tu pago para poder enviarte tu ficha de matrícula.',
                    confirmButtonColor: '#33445a',
                    confirmButtonText: 'Entendido',
                });
            });
        </script>
    @endif

    @if ($alumno && $formularioHabilitado && $periodoActualPpd && ! $matriculaActual)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (sessionStorage.getItem('matriculaAlertVista')) return;

                Swal.fire({
                    icon: 'warning',
                    title: '¡Aún no te has matriculado!',
                    html: 'El período <strong>{{ $periodoActualPpd->nombre }}</strong> ya está abierto. Completa tu matrícula lo antes posible.',
                    showCancelButton: true,
                    confirmButtonText: 'Completar matrícula ahora',
                    cancelButtonText: 'Recordarme después',
                    confirmButtonColor: '#cd9244',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                }).then(function (resultado) {
                    sessionStorage.setItem('matriculaAlertVista', '1');
                    if (resultado.isConfirmed) {
                        window.location.href = '{{ route('ppd.edit', $alumno->id) }}';
                    }
                });
            });
        </script>
    @endif
@endpush
