@extends('layouts.alumno')
@section('titulo', 'Mi trayectoria y titulación')
@section('contenido')
    @include('alumnos.partials.ui')
    @php
        $iniciales = mb_strtoupper(mb_substr(trim((string) $user->name), 0, 1) . mb_substr(trim((string) $user->apellidos), 0, 1));
        $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');

        $periodosFid = collect($historialFid['periodos'] ?? []);
        $todosCursos = $periodosFid->flatMap(fn ($p) => $p->cursos)->reject(fn ($c) => $c->en_curso);
        $conNota = $todosCursos->filter(fn ($c) => $c->nota !== null);
        $aprobados = $conNota->where('desaprobado', false);
        $promedio = $conNota->isNotEmpty() ? $conNota->avg('nota') : null;
        $creditos = $aprobados->sum(fn ($c) => (float) ($c->curso?->creditos ?? 0));
        $promocion = $periodosFid->pluck('periodo')->filter()->last()
            ?? ($hitosOficiales['egreso']['fechas'][0] ?? null);

        $hayTitulacion = ! empty($seguimiento) || ! empty($guiaTitulacion);
        $estadoTitulacion = ! empty($seguimiento) ? $seguimiento->avance()->estadoNombre() : null;
        $etiquetaGrupo = $clasificacion['etiqueta'] ?? 'FID';
    @endphp

    <div class="al-ui">
        @if (session('success'))
            <div class="al-aviso is-info" role="status"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
        @endif

        {{-- ── Hero institucional de egresado (paridad con PPD) ── --}}
        <section class="al-hero is-egresado">
            <div class="al-hero-fila">
                @if ($user->foto)
                    <img src="{{ asset('img/estudiantes/' . $user->foto) }}" alt="Foto de {{ $user->name }}" class="al-hero-foto">
                @else
                    <div class="al-hero-foto al-hero-foto-vacia" aria-hidden="true">{{ $iniciales }}</div>
                @endif
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-user-graduate"></i> Egresado(a) · Trayectoria {{ $etiquetaGrupo }}</div>
                    <h1>{{ $user->name }} {{ $user->apellidos }}</h1>
                    <div class="al-chips">
                        @if ($user->programa || $alumno?->programa)
                            <span class="al-chip"><i class="fas fa-graduation-cap"></i>{{ $user->programa->nombre ?? $alumno?->programa?->nombre }}</span>
                        @endif
                        @if ($promocion)
                            <span class="al-chip is-oro"><i class="fas fa-award"></i>Egreso / Cohorte {{ $promocion }}</span>
                        @endif
                        <span class="al-chip"><i class="fas fa-id-card"></i>DNI {{ $user->dni ?: ($alumno?->dni ?: '—') }}</span>
                    </div>
                </div>
                <div class="al-hero-acciones" style="display:flex;gap:8px;flex-wrap:wrap;">
                    @if ($hayTitulacion)
                        <a class="al-btn al-btn-oro" href="#titulacion" data-al-ir><i class="fas fa-route"></i> Mi titulación</a>
                    @endif
                    <a class="al-btn" href="{{ route('seguimiento.mi-espacio') }}"><i class="fas fa-user-check"></i> Actualizar mis datos y solicitar apoyo</a>
                    @if (Route::has('red.inicio') && app(\App\Services\RedPuklla\AccesoRed::class)->entrar($user))
                        <a class="al-btn" href="{{ route('red.inicio') }}"><i class="fas fa-users"></i> Red Puklla de Egresados</a>
                    @endif
                    @if (Route::has('ayllu.inicio'))
                        <a class="al-btn" href="{{ route('ayllu.inicio') }}"><i class="fas fa-home"></i> Ayllu Puklla</a>
                    @endif
                </div>
            </div>
        </section>

        {{-- ── Indicadores del programa cursado ── --}}
        <div class="al-stats">
            <div class="al-stat">
                <span class="al-stat-icono is-oro"><i class="fas fa-star"></i></span>
                <div><div class="al-stat-valor">{{ $promedio !== null ? number_format($promedio, 2) : '—' }}</div><div class="al-stat-etiqueta">Promedio oficial</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-ok"><i class="fas fa-check"></i></span>
                <div>
                    <div class="al-stat-valor">
                        @if ($todosCursos->isNotEmpty())
                            {{ $aprobados->count() }} <small style="font-size:.8rem;font-weight:700;">/ {{ $todosCursos->count() }}</small>
                        @else
                            {{ ($hitosOficiales['egreso']['acreditado'] ?? false) ? 'Acreditado' : 'Por verificar' }}
                        @endif
                    </div>
                    <div class="al-stat-etiqueta">{{ $todosCursos->isNotEmpty() ? 'Cursos aprobados' : 'Egreso institucional' }}</div>
                </div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono"><i class="fas fa-layer-group"></i></span>
                <div><div class="al-stat-valor">{{ $creditos ? $fmt($creditos) : '—' }}</div><div class="al-stat-etiqueta">Créditos registrados</div></div>
            </div>
            <a class="al-stat text-decoration-none" href="#titulacion" data-al-ir>
                <span class="al-stat-icono is-info"><i class="fas fa-user-graduate"></i></span>
                <div><div class="al-stat-valor" style="font-size:.95rem;">{{ $estadoTitulacion ?? ($hitosOficiales['titulacion']['detalle'] ?? 'Por verificar') }}</div><div class="al-stat-etiqueta">Titulación</div></div>
            </a>
        </div>

        {{-- ── Pestañas: Titulación, Mis estudios y Mis datos ── --}}
        <div>
            <div class="al-tabs" role="tablist" aria-label="Secciones de mi cuenta" data-al-tabs>
                <button type="button" class="al-tab is-activa" role="tab" data-panel="titulacion">
                    <i class="fas fa-route"></i> Titulación
                    @if (! empty($guiaTitulacion))<span class="al-tab-marca">Inicia aquí</span>@endif
                </button>
                <button type="button" class="al-tab" role="tab" data-panel="estudios">
                    <i class="fas fa-history"></i> Mis estudios <span class="al-badge is-muted">{{ $todosCursos->count() }}</span>
                </button>
                <button type="button" class="al-tab" role="tab" data-panel="datos">
                    <i class="fas fa-user"></i> Mis datos
                </button>
            </div>

            <section class="al-panel" role="tabpanel" data-panel="titulacion">
                @if (! empty($seguimiento))
                    @include('titulacion._avance_alumno', ['seguimiento' => $seguimiento])
                @elseif (! empty($guiaTitulacion))
                    @include('titulacion._guia_inicio')
                @else
                    <div class="al-card">
                        <div class="al-card-cab">
                            <span class="al-card-cab-icono"><i class="fas fa-graduation-cap"></i></span>
                            <div>
                                <h2>Estado de egreso y titulación</h2>
                                <p>Registro oficial en el sistema de la EESP Pukllasunchis</p>
                            </div>
                        </div>
                        <div class="al-card-cuerpo pt-2">
                            <dl class="al-datos">
                                <div class="al-dato"><dt>Egreso institucional</dt><dd>{{ ($hitosOficiales['egreso']['acreditado'] ?? false) ? 'Acreditado (' . implode(', ', $hitosOficiales['egreso']['fechas'] ?? []) . ')' : ($hitosOficiales['egreso']['fuente'] ?? 'Por verificar en secretaría académica') }}</dd></div>
                                <div class="al-dato"><dt>Grado / Bachiller</dt><dd>{{ $hitosOficiales['grado_bachiller']['detalle'] ?? 'No verificado en el sistema' }}</dd></div>
                                <div class="al-dato"><dt>Título / Licenciatura</dt><dd>{{ $hitosOficiales['titulacion']['detalle'] ?? 'Aún no hay expediente de titulación abierto en el sistema. Comunícate con la coordinación de titulación.' }}</dd></div>
                            </dl>
                        </div>
                    </div>
                @endif
            </section>

            <section class="al-panel" role="tabpanel" data-panel="estudios">
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-history"></i></span>
                        <div><h2>Historial de estudios</h2><p>{{ $user->programa->nombre ?? ($alumno?->programa?->nombre ?? 'Formación Inicial Docente') }} · notas finales oficiales registradas</p></div>
                    </div>
                    @forelse ($periodosFid as $bloque)
                        <div class="al-grupo">
                            <i class="fas fa-layer-group"></i> {{ $bloque->periodo }} {{ $bloque->ciclo ? '· Ciclo ' . $bloque->ciclo->nombre : '' }}
                            <span class="al-badge is-muted">Promedio {{ $bloque->promedio !== null ? number_format($bloque->promedio, 2) : '—' }}</span>
                        </div>
                        <ul class="al-cursos">
                            @foreach ($bloque->cursos as $c)
                                <li class="al-curso">
                                    <span class="al-curso-num">{{ $loop->iteration }}</span>
                                    <div class="al-curso-texto">
                                        <div class="al-curso-nombre">{{ $c->curso?->nombre ?? 'Curso #' . $loop->iteration }}</div>
                                        <div class="al-curso-meta">
                                            @if ($c->curso?->creditos) {{ $fmt((float) $c->curso->creditos) }} créditos @endif
                                            @if ($c->logro) · Logro: {{ $c->logro }} @endif
                                        </div>
                                    </div>
                                    <div class="al-curso-accion">
                                        @if ($c->nota !== null)
                                            <b class="cal-nota is-{{ $c->desaprobado ? 'mal' : 'ok' }}">{{ $fmt($c->nota) }}</b>
                                        @else
                                            <span class="al-badge is-muted">Sin nota registrada</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @empty
                        <div class="al-vacio">
                            <i class="fas fa-info-circle"></i>
                            Tu egreso corresponde a un periodo anterior a la digitalización por curso (2024-II) o está pendiente de carga detallada en Secretaría Académica.
                        </div>
                    @endforelse
                </div>
                <p class="small text-muted mb-4"><i class="fas fa-info-circle mr-1"></i>
                    Si necesitas un certificado de estudios oficial o algún dato no coincide, puedes reportar la discrepancia desde <a href="{{ route('seguimiento.mi-espacio') }}">Actualizar mis datos y solicitar apoyo</a> o comunicarte con Secretaría Académica.</p>
            </section>

            <section class="al-panel" role="tabpanel" data-panel="datos">
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-user"></i></span>
                        <div><h2>Datos personales y seguimiento</h2><p>Información registrada en tu cuenta institucional.</p></div>
                    </div>
                    <div class="al-card-cuerpo pt-1">
                        <dl class="al-datos">
                            <div class="al-dato"><dt>Nombre completo</dt><dd>{{ $user->name }} {{ $user->apellidos }}</dd></div>
                            <div class="al-dato"><dt>DNI</dt><dd>{{ $user->dni ?: ($alumno?->dni ?: '—') }}</dd></div>
                            <div class="al-dato"><dt>Correo</dt><dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd></div>
                            <div class="al-dato"><dt>Teléfono</dt><dd>{{ $alumno?->numero ?: ($user->telefono ?: '—') }}</dd></div>
                            <div class="al-dato" style="grid-column: 1 / -1;"><dt>Domicilio</dt>
                                <dd>{{ $alumno?->direccion ?: ($user->domicilio ?: '—') }}<br><small class="text-muted">{{ collect([$alumno?->distrito, $alumno?->provincia, $alumno?->departamento])->filter()->implode(' · ') }}</small></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
