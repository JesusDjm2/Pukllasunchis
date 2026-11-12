@extends('layouts.alumno')
@section('titulo', 'Mi cuenta de egresado')
@section('contenido')
    @include('alumnos.partials.ui')
    @php
        // Egresado FID: misma intranet que el alumno FID (menú lateral de egresado, diseño .al-ui), centrada en lo que le
        // sirve ahora: su titulación, el historial de lo que estudió (notas oficiales archivadas), su trayectoria y
        // oportunidades (bolsa de trabajo, comunicados). Sin matrícula, sílabos ni horario. Ver EgresadoIntranetController.
        $iniciales = mb_strtoupper(mb_substr(trim((string) $user->name), 0, 1) . mb_substr(trim((string) $user->apellidos), 0, 1));
        $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
        $nombreCiclo = fn ($c) => $c ? (preg_match('/^ciclo\s/i', $c->nombre) ? $c->nombre : 'Ciclo ' . $c->nombre) : 'Ciclo no registrado';

        $r = $historial['resumen'];
        $cursos = $periodos->flatMap(fn ($p) => $p->cursos)->reject(fn ($c) => $c->en_curso);
        $aprobados = $cursos->filter(fn ($c) => $c->nota !== null && ! $c->desaprobado);
        $estadoTitulacion = $seguimiento ? $seguimiento->avance()->estadoNombre() : null;
        $vigentes = $ofertas->filter(fn ($o) => $o->vigente)->count();
    @endphp

    <div class="al-ui">
        @if (session('success'))
            <div class="al-aviso is-info" role="status"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
        @endif
        @if (session('info'))
            <div class="al-aviso is-info" role="status"><i class="fas fa-info-circle"></i><div>{{ session('info') }}</div></div>
        @endif

        {{-- ── Hero de egresado: mismo componente, con el acento dorado de "cierre de etapa" ── --}}
        <section class="al-hero is-egresado">
            <div class="al-hero-fila">
                @if ($user->foto)
                    <img src="{{ asset('img/estudiantes/' . $user->foto) }}" alt="Foto de {{ $user->name }}" class="al-hero-foto">
                @else
                    <div class="al-hero-foto al-hero-foto-vacia" aria-hidden="true">{{ $iniciales }}</div>
                @endif
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-user-graduate"></i> Egresado(a) · Formación Inicial Docente</div>
                    <h1>{{ $user->name }} {{ $user->apellidos }}</h1>
                    <div class="al-chips">
                        @if ($user->programa)
                            <span class="al-chip"><i class="fas fa-graduation-cap"></i>{{ $user->programa->nombre }}</span>
                        @endif
                        @if ($promocion)
                            <span class="al-chip is-oro"><i class="fas fa-award"></i>Promoción {{ $promocion }}</span>
                        @endif
                        @if ($user->dni || $alumno?->dni)
                            <span class="al-chip"><i class="fas fa-id-card"></i>DNI {{ $user->dni ?: $alumno->dni }}</span>
                        @endif
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a class="al-btn al-btn-oro" href="#titulacion" data-al-ir><i class="fas fa-route"></i> Mi titulación</a>
                </div>
            </div>
        </section>

        {{-- ── Indicadores ── --}}
        <div class="al-stats">
            <a class="al-stat text-decoration-none" href="#titulacion" data-al-ir>
                <span class="al-stat-icono is-info"><i class="fas fa-user-graduate"></i></span>
                <div><div class="al-stat-valor" style="font-size:.95rem;">{{ $estadoTitulacion ?? 'Puedes iniciar' }}</div><div class="al-stat-etiqueta">Titulación</div></div>
            </a>
            <div class="al-stat">
                <span class="al-stat-icono is-oro"><i class="fas fa-star"></i></span>
                <div><div class="al-stat-valor">{{ $r['promedio'] !== null ? number_format($r['promedio'], 2) : '—' }}</div><div class="al-stat-etiqueta">Promedio registrado</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-ok"><i class="fas fa-check"></i></span>
                <div><div class="al-stat-valor">{{ $aprobados->count() }} <small style="font-size:.8rem;font-weight:700;">/ {{ $cursos->count() }}</small></div><div class="al-stat-etiqueta">Cursos aprobados (registrados)</div></div>
            </div>
            <a class="al-stat text-decoration-none" href="#oportunidades" data-al-ir>
                <span class="al-stat-icono"><i class="fas fa-briefcase"></i></span>
                <div><div class="al-stat-valor">{{ $vigentes }}</div><div class="al-stat-etiqueta">Ofertas vigentes</div></div>
            </a>
        </div>

        {{-- ── Pestañas: abre en Titulación (lo que hoy le importa a un egresado) ── --}}
        <div>
            <div class="al-tabs" role="tablist" aria-label="Secciones de mi cuenta" data-al-tabs>
                <button type="button" class="al-tab is-activa" role="tab" data-panel="titulacion">
                    <i class="fas fa-route"></i> Titulación
                    @unless ($seguimiento)<span class="al-tab-marca">Inicia aquí</span>@endunless
                </button>
                <button type="button" class="al-tab" role="tab" data-panel="estudios">
                    <i class="fas fa-history"></i> Mis estudios <span class="al-badge is-muted">{{ $cursos->count() }}</span>
                </button>
                <button type="button" class="al-tab" role="tab" data-panel="oportunidades">
                    <i class="fas fa-briefcase"></i> Oportunidades
                    @if ($vigentes)<span class="al-tab-marca is-ok">{{ $vigentes }}</span>@endif
                </button>
                <button type="button" class="al-tab" role="tab" data-panel="datos">
                    <i class="fas fa-user"></i> Mis datos
                </button>
            </div>

            <section class="al-panel" role="tabpanel" data-panel="titulacion">
                @if ($seguimiento)
                    @include('titulacion._avance_alumno', ['seguimiento' => $seguimiento])
                @else
                    @include('titulacion._guia_inicio')
                @endif
            </section>

            {{-- Historial: periodo por periodo, con su ciclo y la nota oficial de cada curso; luego la trayectoria --}}
            <section class="al-panel" role="tabpanel" data-panel="estudios">
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-history"></i></span>
                        <div><h2>Historial de estudios</h2><p>{{ $user->programa->nombre ?? 'Formación Inicial Docente' }}{{ $promocion ? ' · Promoción ' . $promocion : '' }} · notas finales oficiales</p></div>
                    </div>
                    {{-- Un acordeón por periodo (el más reciente abierto): menos scroll con varios periodos. --}}
                    @forelse ($periodos->reverse()->values() as $i => $p)
                        <details class="al-acc" @if ($i === 0) open @endif>
                            <summary class="al-curso">
                                <span class="al-curso-num {{ $p->desaprobados ? 'is-mal' : 'is-ok' }}" title="{{ $p->desaprobados ? 'Cursos desaprobados' : 'Sin cursos desaprobados' }}">{{ $p->desaprobados ?: '✓' }}</span>
                                <span class="al-curso-texto">
                                    <span class="al-curso-nombre">{{ $p->periodo }} · {{ $nombreCiclo($p->ciclo) }}</span>
                                    <span class="al-curso-meta d-block">
                                        {{ $p->cursos->count() }} {{ $p->cursos->count() === 1 ? 'curso' : 'cursos' }}
                                        @if ($p->desaprobados) · {{ $p->desaprobados }} {{ $p->desaprobados === 1 ? 'desaprobado' : 'desaprobados' }}@endif
                                        @if ($p->repite) · <span class="al-badge is-warn">Repitió el ciclo</span>@endif
                                    </span>
                                </span>
                                <span class="al-curso-accion">
                                    <span class="al-badge is-muted">Promedio {{ $p->promedio !== null ? number_format($p->promedio, 2) : '—' }}</span>
                                    <i class="fas fa-chevron-down al-acc-chev"></i>
                                </span>
                            </summary>
                            <div class="al-acc-cuerpo">
                                @foreach ($p->cursos as $c)
                                    <div class="al-acc-fila">
                                        @if ($c->nota !== null)
                                            <span class="cal-nota is-{{ $c->desaprobado ? 'mal' : 'ok' }}" title="Nota final oficial {{ $p->periodo }}">{{ $fmt($c->nota) }}</span>
                                        @else
                                            <span class="cal-nota" title="Sin nota registrada">—</span>
                                        @endif
                                        <span>{{ $c->curso->nombre ?? 'Curso' }}@if ($c->curso?->creditos) <small>· {{ $fmt((float) $c->curso->creditos) }} créditos</small>@endif</span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @empty
                        <div class="al-vacio"><i class="fas fa-info-circle"></i>El sistema guarda las notas oficiales desde 2024-II: tus estudios anteriores no están registrados aquí.</div>
                    @endforelse
                </div>
                @if ($periodos->isNotEmpty())
                    <p class="small text-muted mb-3"><i class="fas fa-info-circle mr-1"></i>El sistema guarda las notas oficiales desde 2024-II; los periodos anteriores no aparecen aquí.</p>
                @endif

                @include('egresado._trayectoria')

                <div class="al-aviso is-info mb-4"><i class="fas fa-file-alt"></i><div>
                    ¿Necesitas tu <strong>certificado de estudios</strong>, tu <strong>constancia de egresado</strong> u otro documento oficial? Se tramitan en Secretaría Académica:
                    revisa los conceptos y montos en <a href="{{ route('tramiteTitulacion') }}">Trámites</a>.
                </div></div>
            </section>

            <section class="al-panel" role="tabpanel" data-panel="oportunidades">
                @include('egresado._oportunidades')
            </section>

            <section class="al-panel" role="tabpanel" data-panel="datos">
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-user"></i></span>
                        <div><h2>Datos personales</h2><p>Si algo está mal o cambió (por ejemplo tu celular o tu correo), avisa a Secretaría Académica.</p></div>
                    </div>
                    <div class="al-card-cuerpo pt-1">
                        <dl class="al-datos">
                            <div class="al-dato"><dt>Nombre completo</dt><dd>{{ $user->name }} {{ $user->apellidos }}</dd></div>
                            <div class="al-dato"><dt>DNI</dt><dd>{{ $user->dni ?: ($alumno?->dni ?: '—') }}</dd></div>
                            <div class="al-dato"><dt>Correo</dt><dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd></div>
                            <div class="al-dato"><dt>Celular</dt><dd>{{ $alumno?->numero ?: ($user->telefono ?: '—') }}</dd></div>
                            <div class="al-dato"><dt>Programa</dt><dd>{{ $user->programa->nombre ?? '—' }}</dd></div>
                            <div class="al-dato"><dt>Promoción</dt><dd>{{ $promocion ?? '—' }}</dd></div>
                            @if ($alumno)
                                <div class="al-dato" style="grid-column: 1 / -1;"><dt>Domicilio</dt>
                                    <dd>{{ $alumno->direccion ?: '—' }}<br><small class="text-muted">{{ collect([$alumno->distrito, $alumno->provincia, $alumno->departamento])->filter()->implode(' · ') }}</small></dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
