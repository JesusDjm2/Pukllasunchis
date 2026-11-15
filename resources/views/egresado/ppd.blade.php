@extends('layouts.profesionalizacion')
@section('titulo', 'Mi cuenta de egresado')
@section('contenido')
    @include('alumnos.partials.ui')
    @php
        // Egresado PPD: misma intranet que el alumno activo, pero centrada en lo que le sirve ahora:
        // su titulación y el historial de lo que estudió (notas oficiales archivadas), sin matrícula ni sílabos.
        $iniciales = mb_strtoupper(mb_substr(trim((string) $user->name), 0, 1) . mb_substr(trim((string) $user->apellidos), 0, 1));
        $nombreCiclo = fn ($c) => preg_match('/^ciclo\s/i', $c->nombre) ? $c->nombre : 'Ciclo ' . $c->nombre;
        $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');

        $todos = $cursosPpd->flatMap(fn ($b) => $b->cursos);
        $conNota = $todos->filter(fn ($c) => $c->nota !== null);
        $aprobados = $conNota->where('desaprobado', false);
        $promedio = $conNota->isNotEmpty() ? $conNota->avg('nota') : null;
        $creditos = $aprobados->sum(fn ($c) => (float) $c->curso->creditos);
        // Promoción: el periodo PPD al que pertenecen sus notas oficiales.
        $promocion = $todos->pluck('periodo')->filter()->countBy()->sortDesc()->keys()->first();

        $hayTitulacion = ! empty($seguimiento) || ! empty($guiaTitulacion);
        $estadoTitulacion = ! empty($seguimiento) ? $seguimiento->avance()->estadoNombre() : null;
    @endphp

    <div class="al-ui">
        @if (session('success'))
            <div class="al-aviso is-info" role="status"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
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
                    <div class="al-kicker"><i class="fas fa-user-graduate"></i> Egresado(a) · Profesionalización docente</div>
                    <h1>{{ $user->name }} {{ $user->apellidos }}</h1>
                    <div class="al-chips">
                        @if ($user->programa)
                            <span class="al-chip"><i class="fas fa-graduation-cap"></i>{{ $user->programa->nombre }}</span>
                        @endif
                        @if ($promocion)
                            <span class="al-chip is-oro"><i class="fas fa-award"></i>Promoción {{ $promocion }}</span>
                        @endif
                        <span class="al-chip"><i class="fas fa-id-card"></i>DNI {{ $user->dni ?: $alumno->dni }}</span>
                    </div>
                </div>
                @if ($hayTitulacion)
                    <div class="al-hero-acciones">
                        <a class="al-btn al-btn-oro" href="#titulacion" data-al-ir><i class="fas fa-route"></i> Mi titulación</a>
                    </div>
                @endif
            </div>
        </section>

        {{-- ── Indicadores del programa cursado ── --}}
        <div class="al-stats">
            <div class="al-stat">
                <span class="al-stat-icono is-oro"><i class="fas fa-star"></i></span>
                <div><div class="al-stat-valor">{{ $promedio !== null ? number_format($promedio, 2) : '—' }}</div><div class="al-stat-etiqueta">Promedio final</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-ok"><i class="fas fa-check"></i></span>
                <div><div class="al-stat-valor">{{ $aprobados->count() }} <small style="font-size:.8rem;font-weight:700;">/ {{ $todos->count() }}</small></div><div class="al-stat-etiqueta">Cursos aprobados</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono"><i class="fas fa-layer-group"></i></span>
                <div><div class="al-stat-valor">{{ $creditos ? $fmt($creditos) : '—' }}</div><div class="al-stat-etiqueta">Créditos aprobados</div></div>
            </div>
            @if ($hayTitulacion)
                <a class="al-stat text-decoration-none" href="#titulacion" data-al-ir>
                    <span class="al-stat-icono is-info"><i class="fas fa-user-graduate"></i></span>
                    <div><div class="al-stat-valor" style="font-size:.95rem;">{{ $estadoTitulacion ?? 'Puedes iniciar' }}</div><div class="al-stat-etiqueta">Titulación</div></div>
                </a>
            @endif
        </div>

        {{-- ── Pestañas: abre en Titulación (lo que hoy le importa a un egresado) ── --}}
        <div>
            <div class="al-tabs" role="tablist" aria-label="Secciones de mi cuenta" data-al-tabs>
                @if ($hayTitulacion)
                    <button type="button" class="al-tab is-activa" role="tab" data-panel="titulacion">
                        <i class="fas fa-route"></i> Titulación
                        @if (! empty($guiaTitulacion))<span class="al-tab-marca">Inicia aquí</span>@endif
                    </button>
                @endif
                <button type="button" class="al-tab {{ $hayTitulacion ? '' : 'is-activa' }}" role="tab" data-panel="estudios">
                    <i class="fas fa-history"></i> Mis estudios <span class="al-badge is-muted">{{ $todos->count() }}</span>
                </button>
                <button type="button" class="al-tab" role="tab" data-panel="oportunidades">
                    <i class="fas fa-briefcase"></i> Oportunidades
                    @php $vigentes = $ofertas->filter(fn ($o) => $o->vigente)->count(); @endphp
                    @if ($vigentes)<span class="al-tab-marca is-ok">{{ $vigentes }}</span>@endif
                </button>
                <button type="button" class="al-tab" role="tab" data-panel="datos">
                    <i class="fas fa-user"></i> Mis datos
                </button>
            </div>

            @if ($hayTitulacion)
                <section class="al-panel" role="tabpanel" data-panel="titulacion">
                    @if (! empty($seguimiento))
                        @include('titulacion._avance_alumno', ['seguimiento' => $seguimiento])
                    @else
                        @include('titulacion._guia_inicio')
                    @endif
                </section>
            @endif

            {{-- Historial: el plan que cursó, por ciclo, con su nota oficial --}}
            <section class="al-panel" role="tabpanel" data-panel="estudios">
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-history"></i></span>
                        <div><h2>Historial de estudios</h2><p>{{ $user->programa->nombre ?? 'Programa PPD' }}{{ $promocion ? ' · ' . $promocion : '' }} · notas finales oficiales</p></div>
                    </div>
                    {{-- Un acordeón por ciclo (el primero abierto): menos scroll. --}}
                    @forelse ($cursosPpd as $bloque)
                        @php
                            $notasCiclo = $bloque->cursos->pluck('nota')->filter(fn ($n) => $n !== null);
                            $malCiclo = $bloque->cursos->where('desaprobado', true)->count();
                        @endphp
                        <details class="al-acc" @if ($loop->first) open @endif>
                            <summary class="al-curso">
                                <span class="al-curso-num {{ $malCiclo ? 'is-mal' : 'is-ok' }}" title="{{ $malCiclo ? 'Cursos desaprobados' : 'Sin cursos desaprobados' }}">{{ $malCiclo ?: '✓' }}</span>
                                <span class="al-curso-texto">
                                    <span class="al-curso-nombre">{{ $nombreCiclo($bloque->ciclo) }}</span>
                                    <span class="al-curso-meta d-block">{{ $bloque->cursos->count() }} {{ $bloque->cursos->count() === 1 ? 'curso' : 'cursos' }}@if ($malCiclo) · {{ $malCiclo }} {{ $malCiclo === 1 ? 'desaprobado' : 'desaprobados' }}@endif</span>
                                </span>
                                <span class="al-curso-accion">
                                    <span class="al-badge is-muted">Promedio {{ $notasCiclo->isNotEmpty() ? number_format($notasCiclo->avg(), 2) : '—' }}</span>
                                    <i class="fas fa-chevron-down al-acc-chev"></i>
                                </span>
                            </summary>
                            <div class="al-acc-cuerpo">
                                @foreach ($bloque->cursos as $c)
                                    <div class="al-acc-fila">
                                        @if ($c->nota !== null)
                                            <span class="cal-nota is-{{ $c->desaprobado ? 'mal' : 'ok' }}" title="{{ $c->periodo ? 'Nota oficial ' . $c->periodo : 'Nota final' }}">{{ $fmt($c->nota) }}</span>
                                        @else
                                            <span class="cal-nota" title="Sin nota registrada">—</span>
                                        @endif
                                        <span>{{ $c->curso->nombre }} <small>· {{ $c->docentes ?: 'Docente no registrado' }}@if ($c->curso->creditos) · {{ $fmt((float) $c->curso->creditos) }} créditos @endif</small></span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @empty
                        <div class="al-vacio"><i class="fas fa-info-circle"></i>No encontramos cursos registrados de tu programa.</div>
                    @endforelse
                </div>
                @include('egresado._trayectoria')

                <div class="al-aviso is-info mb-4"><i class="fas fa-file-alt"></i><div>
                    ¿Necesitas tu <strong>certificado de estudios</strong>, tu <strong>constancia de egresado</strong> u otro documento oficial, o algún dato no coincide?
                    Comunícate con Secretaría Académica; revisa los conceptos y montos en <a href="{{ route('tramiteTitulacion') }}">Trámites</a>.
                </div></div>
            </section>

            <section class="al-panel" role="tabpanel" data-panel="oportunidades">
                @include('egresado._oportunidades')
            </section>

            <section class="al-panel" role="tabpanel" data-panel="datos">
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-user"></i></span>
                        <div><h2>Datos personales</h2><p>Si algo está mal, avisa a Secretaría Académica.</p></div>
                    </div>
                    <div class="al-card-cuerpo pt-1">
                        <dl class="al-datos">
                            <div class="al-dato"><dt>Nombre completo</dt><dd>{{ $user->name }} {{ $user->apellidos }}</dd></div>
                            <div class="al-dato"><dt>DNI</dt><dd>{{ $user->dni ?: $alumno->dni }}</dd></div>
                            <div class="al-dato"><dt>Correo</dt><dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd></div>
                            <div class="al-dato"><dt>Teléfono</dt><dd>{{ $alumno->numero ?: '—' }}</dd></div>
                            <div class="al-dato" style="grid-column: 1 / -1;"><dt>Domicilio</dt>
                                <dd>{{ $alumno->direccion ?: '—' }}<br><small class="text-muted">{{ collect([$alumno->distrito, $alumno->provincia, $alumno->departamento])->filter()->implode(' · ') }}</small></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
