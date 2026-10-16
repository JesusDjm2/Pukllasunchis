@extends('layouts.alumno')
@section('titulo', 'Ficha técnica')
@section('contenido')
    @include('alumnos.partials.ui')
    @php
        $alumno = auth()->user()->alumno ?? auth()->user()->alumnoB;
        $esFid = (bool) auth()->user()->alumno;
        // En producción el controlador aún no envía "Mis solicitudes" (función solo local): sin datos, no se muestra.
        $misTutorias = $misTutorias ?? collect();
        $misSugerencias = $misSugerencias ?? collect();
        $matriculaAbierta = $esFid && isset($periodoActual) && $periodoActual && $periodoActual->formulario_habilitado && isset($yaMatriculado) && !$yaMatriculado;
    @endphp

    <div class="al-ui">
        @if (Session::has('success'))
            <div class="al-aviso is-info" role="status"><i class="fas fa-check-circle"></i><div>{{ Session::get('success') }}</div></div>
        @endif

        @if ($esFid)
            @php
                $cursosAMostrar = $cursosDelAlumno ?? collect();
                $sinSilabosAun = isset($periodoActual) && $periodoActual && !$periodoActual->silabosVisibles();

                // Estado del sílabo de cada curso (misma lógica de siempre, calculada una vez).
                $estadoSilabo = $cursosAMostrar->mapWithKeys(function ($curso) use ($periodoActual) {
                    if (str_contains($curso->cc ?? '', 'Extracurricular')) {
                        return [$curso->id => ['tipo' => 'na', 'texto' => 'No aplica']];
                    }
                    $silaboObj = $curso->relacionsilabo ?? null;
                    if (! $silaboObj && ! $curso->silabo) {
                        return [$curso->id => ['tipo' => 'sin', 'texto' => 'Sin sílabo']];
                    }
                    if (($silaboObj->periodo ?? null) !== ($periodoActual->nombre ?? null)) {
                        return [$curso->id => ['tipo' => 'sin', 'texto' => 'Sin sílabo disponible']];
                    }
                    if (! ($periodoActual && $periodoActual->silabosVisibles())) {
                        return [$curso->id => ['tipo' => 'pronto', 'texto' => 'Disponible pronto']];
                    }

                    return [$curso->id => ['tipo' => 'ok', 'url' => $silaboObj ? route('silabos.show', $silaboObj->id) : asset('docentes/silabo/'.$curso->silabo)]];
                });
                $silabosListos = $estadoSilabo->where('tipo', 'ok')->count();
                $pendientesCurso = $alumno->user ? collect($alumno->user->cursosPendientes()) : collect();
                $solicitudesAbiertas = $misTutorias->where('estado', '!=', 'atendida')->count() + $misSugerencias->where('estado', '!=', 'revisada')->count();
                $iniciales = mb_strtoupper(mb_substr(trim($alumno->nombres), 0, 1).mb_substr(trim($alumno->apellidos), 0, 1));
            @endphp

            {{-- ── Hero: identidad del estudiante + estado de matrícula ── --}}
            <section class="al-hero">
                <div class="al-hero-fila">
                    @if ($alumno->user && $alumno->user->foto)
                        <img src="{{ asset('img/estudiantes/' . $alumno->user->foto) }}" alt="Foto de {{ $alumno->nombres }}" class="al-hero-foto">
                    @else
                        <div class="al-hero-foto al-hero-foto-vacia" aria-hidden="true">{{ $iniciales }}</div>
                    @endif
                    <div class="al-hero-texto">
                        <div class="al-kicker">Ficha técnica</div>
                        <h1>{{ $alumno->nombres }} {{ $alumno->apellidos }}</h1>
                        <div class="al-chips">
                            <span class="al-chip"><i class="fas fa-graduation-cap"></i>{{ $alumno->programa->nombre }}</span>
                            @if ($alumno->ciclo)
                                <span class="al-chip"><i class="fas fa-layer-group"></i>Ciclo {{ $alumno->ciclo->nombre }}</span>
                            @endif
                            <span class="al-chip"><i class="fas fa-id-card"></i>DNI {{ $alumno->dni }}</span>
                            @if (isset($yaMatriculado) && $yaMatriculado)
                                <span class="al-chip is-ok"><i class="fas fa-check-circle"></i>Matriculado · {{ optional($periodoActual)->nombre }}</span>
                            @elseif ($matriculaAbierta)
                                <span class="al-chip is-warn"><i class="fas fa-exclamation-circle"></i>Matrícula pendiente</span>
                            @endif
                        </div>
                    </div>
                    <div class="al-hero-acciones">
                        @if ($matriculaAbierta)
                            <a class="al-btn al-btn-oro" href="{{ route('alumnos.editarDatos') }}"><i class="fas fa-graduation-cap"></i> Completar matrícula</a>
                        @endif
                        <a class="al-btn al-btn-claro" href="{{ route('ficha-matricula', ['alumno' => $alumno->id]) }}">
                            <i class="fas fa-file-pdf"></i> Ficha de matrícula
                        </a>
                    </div>
                </div>
            </section>

            {{-- ── Avisos ── --}}
            @if ($matriculaAbierta)
                <div class="al-aviso is-warn"><i class="fas fa-exclamation-circle"></i>
                    <div>El período <strong>{{ $periodoActual->nombre }}</strong> está abierto. Completa tu ficha de matrícula con el botón <strong>Completar matrícula</strong>.</div>
                </div>
            @endif
            @if ($sinSilabosAun)
                <div class="al-aviso is-info"><i class="fas fa-info-circle"></i>
                    <div>Los sílabos de <strong>{{ $periodoActual->nombre }}</strong> estarán disponibles a partir del
                        <strong>{{ $periodoActual->fechaSilabosVisibles()->format('d/m/Y') }}</strong>. Los docentes están trabajando en ellos.</div>
                </div>
            @endif

            {{-- ── Indicadores ── --}}
            <div class="al-stats">
                <div class="al-stat">
                    <span class="al-stat-icono"><i class="fas fa-book-open"></i></span>
                    <div><div class="al-stat-valor">{{ $cursosAMostrar->count() }}</div><div class="al-stat-etiqueta">Cursos del semestre</div></div>
                </div>
                <div class="al-stat">
                    <span class="al-stat-icono is-oro"><i class="fas fa-file-alt"></i></span>
                    <div><div class="al-stat-valor">{{ $silabosListos }}</div><div class="al-stat-etiqueta">Sílabos disponibles</div></div>
                </div>
                <div class="al-stat">
                    <span class="al-stat-icono {{ $pendientesCurso->isNotEmpty() ? 'is-mal' : 'is-ok' }}"><i class="fas {{ $pendientesCurso->isNotEmpty() ? 'fa-exclamation-triangle' : 'fa-check' }}"></i></span>
                    <div><div class="al-stat-valor">{{ $pendientesCurso->count() }}</div><div class="al-stat-etiqueta">Cursos a cargo</div></div>
                </div>
                <a class="al-stat text-decoration-none" href="{{ Route::has('alumno.atencion') ? route('alumno.atencion') : '#solicitudes' }}">
                    <span class="al-stat-icono is-info"><i class="fas fa-comment-dots"></i></span>
                    <div><div class="al-stat-valor">{{ $solicitudesAbiertas }}</div><div class="al-stat-etiqueta">Solicitudes en curso</div></div>
                </a>
            </div>

            {{-- ── Cursos a cargo (lo urgente va arriba) ── --}}
            @if ($pendientesCurso->isNotEmpty())
                <div class="al-aviso is-mal">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Tienes {{ $pendientesCurso->count() === 1 ? 'un curso' : $pendientesCurso->count().' cursos' }} a cargo</strong>
                        <div class="small mb-1">Es responsabilidad del estudiante solicitar la subsanación de los cursos pendientes.</div>
                        <div class="d-flex flex-wrap" style="gap:.35rem;">
                            @foreach ($pendientesCurso as $cp)
                                <span class="al-badge is-mal">{{ trim($cp) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                {{-- ── Cursos del semestre ── --}}
                <div class="col-xl-7">
                    <div class="al-card">
                        <div class="al-card-cab">
                            <span class="al-card-cab-icono"><i class="fas fa-book-open"></i></span>
                            <div><h2>Cursos del semestre</h2><p>{{ optional($periodoActual)->nombre ? 'Periodo '.$periodoActual->nombre : 'Periodo actual' }}</p></div>
                        </div>
                        <ul class="al-cursos">
                            @forelse ($cursosAMostrar as $curso)
                                @php $es = $estadoSilabo[$curso->id]; @endphp
                                <li class="al-curso">
                                    <span class="al-curso-num">{{ $loop->iteration }}</span>
                                    <div class="al-curso-texto">
                                        <a href="{{ route('curso.show', $curso->id) }}" class="al-curso-nombre">{{ $curso->nombre }}</a>
                                        <div class="al-curso-meta">
                                            {{ $curso->cc ?? '' }}
                                            @if ($curso->ciclo_id != $alumno->ciclo_id)
                                                <span class="al-badge is-info ml-1">Ciclo {{ $curso->ciclo->nombre ?? '–' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($es['tipo'] === 'ok')
                                        <a href="{{ $es['url'] }}" target="_blank" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-eye"></i> Sílabo</a>
                                    @else
                                        <span class="al-badge {{ $es['tipo'] === 'pronto' ? 'is-warn' : 'is-muted' }}">{{ $es['texto'] }}</span>
                                    @endif
                                </li>
                            @empty
                                <li class="al-vacio"><i class="fas fa-info-circle"></i>No hay cursos registrados para este período.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- ── Datos personales ── --}}
                <div class="col-xl-5">
                    <div class="al-card">
                        <div class="al-card-cab">
                            <span class="al-card-cab-icono"><i class="fas fa-user"></i></span>
                            <div><h2>Datos personales</h2><p>Si algo está mal, avisa a Secretaría Académica.</p></div>
                        </div>
                        <div class="al-card-cuerpo pt-1">
                            <dl class="al-datos">
                                <div class="al-dato"><dt>Correo</dt><dd><a href="mailto:{{ $alumno->email }}">{{ $alumno->email }}</a></dd></div>
                                <div class="al-dato"><dt>Teléfono</dt><dd>{{ $alumno->numero ?: '—' }}</dd></div>
                                <div class="al-dato"><dt>Núm. de referencia</dt><dd>{{ $alumno->numero_referencia ?: '—' }}</dd></div>
                                <div class="al-dato"><dt>DNI</dt><dd>{{ $alumno->dni }}</dd></div>
                                <div class="al-dato" style="grid-column: 1 / -1;"><dt>Domicilio</dt>
                                    <dd>{{ $alumno->direccion }}<br><small class="text-muted">{{ $alumno->distrito }} · {{ $alumno->provincia }} · {{ $alumno->departamento }}</small></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Avance de titulación (solo si ya se inició su proceso, Ciclo VIII+) ── --}}
            @if (! empty($seguimientoTitulacion))
                @include('titulacion._avance_alumno', ['seguimiento' => $seguimientoTitulacion])
            @elseif (! empty($guiaTitulacion))
                @include('titulacion._guia_inicio')
            @endif

            {{-- ── Mis solicitudes (tutorías y sugerencias, con la respuesta del tutor) ── --}}
            @if ($misTutorias->isNotEmpty() || $misSugerencias->isNotEmpty())
                <div class="al-card" id="solicitudes">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-comment-dots"></i></span>
                        <div><h2>Mis solicitudes</h2><p>Tutorías y sugerencias que enviaste, con la respuesta de tu tutor(a).</p></div>
                        @if (Route::has('alumno.atencion'))
                            <div class="al-card-cab-extra"><a href="{{ route('alumno.atencion') }}" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-plus"></i> Nueva</a></div>
                        @endif
                    </div>
                    <ul class="al-solicitudes">
                        @foreach ($misTutorias as $t)
                            <li>
                                <span class="al-badge is-info">Tutoría</span>
                                <div class="al-solicitud-texto">
                                    <div>{{ \Illuminate\Support\Str::limit($t->motivo, 110) }}</div>
                                    <small>{{ $t->created_at->format('d/m/Y') }} · Prioridad {{ mb_strtolower($t->urgencia) }}</small>
                                    @if ($t->estado === 'atendida' && $t->notas_atencion)
                                        <div class="al-solicitud-respuesta"><i class="fas fa-reply"></i> {{ $t->notas_atencion }}</div>
                                    @endif
                                </div>
                                <span class="al-badge {{ $t->estado === 'atendida' ? 'is-ok' : 'is-warn' }}">{{ $t->estado === 'atendida' ? 'Atendida' : 'Pendiente' }}</span>
                            </li>
                        @endforeach
                        @foreach ($misSugerencias as $s)
                            <li>
                                <span class="al-badge is-muted">Sugerencia</span>
                                <div class="al-solicitud-texto">
                                    <div>{{ \Illuminate\Support\Str::limit($s->mensaje, 110) }}</div>
                                    <small>{{ $s->created_at->format('d/m/Y') }}</small>
                                    @if ($s->estado === 'revisada' && $s->notas_atencion)
                                        <div class="al-solicitud-respuesta"><i class="fas fa-reply"></i> {{ $s->notas_atencion }}</div>
                                    @endif
                                </div>
                                <span class="al-badge {{ $s->estado === 'revisada' ? 'is-ok' : 'is-warn' }}">{{ $s->estado === 'revisada' ? 'Revisada' : 'Pendiente' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @else
            <section class="al-hero">
                <div class="al-hero-fila">
                    <div class="al-hero-texto">
                        <div class="al-kicker">Ficha técnica</div>
                        <h1>Completa tu ficha de estudiante</h1>
                        <p class="al-hero-sub">Aún no tienes ficha FID registrada en el sistema. Llénala para ver tus cursos, sílabos y calificaciones.</p>
                    </div>
                    <div class="al-hero-acciones">
                        <a class="al-btn al-btn-oro" href="{{ route('vistAlumno') }}"><i class="fas fa-edit"></i> Completar formulario de datos</a>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <style>
        .al-cursos, .al-solicitudes { list-style: none; margin: 0; padding: 0; }
        .al-curso { display: flex; align-items: center; gap: .85rem; padding: .8rem 1.25rem; border-bottom: 1px solid var(--al-borde); }
        .al-curso:last-child { border-bottom: 0; }
        .al-curso:hover { background: var(--al-fondo-2); }
        .al-curso-num {
            width: 30px; height: 30px; border-radius: 9px; flex: none; display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 800; background: var(--al-fondo-2); color: var(--al-suave); border: 1px solid var(--al-borde);
        }
        .al-curso-texto { flex: 1; min-width: 0; }
        .al-curso-nombre { font-weight: 700; color: var(--al-texto) !important; font-size: .9rem; text-decoration: none !important; }
        .al-curso-nombre:hover { color: var(--al-oro-2) !important; }
        .al-curso-meta { font-size: .74rem; color: var(--al-suave); margin-top: .1rem; }
        .al-solicitudes li { display: flex; align-items: flex-start; gap: .85rem; padding: .85rem 1.25rem; border-bottom: 1px solid var(--al-borde); }
        .al-solicitudes li:last-child { border-bottom: 0; }
        .al-solicitud-texto { flex: 1; min-width: 0; font-size: .88rem; color: var(--al-texto); }
        .al-solicitud-texto small { color: var(--al-suave); }
        .al-solicitud-respuesta { margin-top: .35rem; font-size: .82rem; color: var(--al-ok); background: var(--al-ok-f); border-radius: 8px; padding: .35rem .6rem; }
        @media (max-width: 575.98px) { .al-curso, .al-solicitudes li { flex-wrap: wrap; } }
    </style>
@endsection

@push('scripts')
    @if (request('tab') === 'solicitudes')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('solicitudes');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
    @endif
@endpush

@if (auth()->user()->alumno && isset($periodoActual) && $periodoActual && $periodoActual->formulario_habilitado && isset($yaMatriculado) && !$yaMatriculado)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (sessionStorage.getItem('matriculaAlertVista')) return;

                Swal.fire({
                    icon: 'warning',
                    title: '¡Aún no te has matriculado!',
                    html: 'El período <strong>{{ $periodoActual->nombre }}</strong> ya está abierto. Completa tu ficha de matrícula lo antes posible.',
                    showCancelButton: true,
                    confirmButtonText: 'Completar matrícula ahora',
                    cancelButtonText: 'Recordarme después',
                    confirmButtonColor: '#4e73df',
                    cancelButtonColor: '#858796',
                    reverseButtons: true,
                }).then(function (resultado) {
                    sessionStorage.setItem('matriculaAlertVista', '1');
                    if (resultado.isConfirmed) {
                        window.location.href = '{{ route('alumnos.editarDatos') }}';
                    }
                });
            });
        </script>
    @endpush
@endif
