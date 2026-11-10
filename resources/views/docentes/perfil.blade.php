@extends('layouts.docente')

@section('titulo', 'Mi perfil — Docente')

@section('contenido')
    {{-- Mismo sistema visual que el resto del panel (alumnos/partials/ui). --}}
    @include('alumnos.partials.ui')
    @include('partials.flash-swal')
    @php
        $usuario = $docente->user;
        $telefono = $usuario?->telefono ?: $docente->telefono;
        $iniciales = collect(preg_split('/\s+/', trim($docente->nombre)))->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'D';

        $cursos = $docente->cursos;
        $cursosPpd = $cursos->filter(fn ($c) => $c->esPpd());
        $cursosFid = $cursos->reject(fn ($c) => $c->esPpd());
        $porPrograma = $cursos->sortBy(fn ($c) => [$c->ciclo?->programa?->nombre, $c->ciclo_id, $c->nombre])
            ->groupBy(fn ($c) => $c->ciclo?->programa?->nombre ?? 'Sin programa');

        $proyectos = method_exists($docente, 'proyectosIntegradores') ? $docente->proyectosIntegradores()->orderBy('nombre')->get() : collect();
        $asincronicos = $docente->cursosEspeciales ?? collect();

        $etiquetasRol = ['docente' => 'Docente', 'tutor' => 'Tutor(a)', 'investigador' => 'Investigador(a)', 'gestor-titulacion' => 'Gestor(a) de titulación', 'adminB' => 'Bolsa de trabajo'];
        $roles = $usuario ? $usuario->getRoleNames()->filter(fn ($r) => isset($etiquetasRol[$r]))->map(fn ($r) => $etiquetasRol[$r]) : collect();

        // Qué tan completo está el perfil (lo que ven los estudiantes)
        $pasos = [
            ['hecho' => (bool) $docente->foto, 'texto' => 'Foto de perfil'],
            ['hecho' => filled($docente->descripcion), 'texto' => 'Descripción para tus estudiantes'],
            ['hecho' => filled($telefono), 'texto' => 'Teléfono de contacto'],
        ];
        $hechos = collect($pasos)->where('hecho', true)->count();
        $avance = (int) round($hechos / count($pasos) * 100);
    @endphp

    <div class="container-fluid docente-ui-page al-ui pf-page">
        {{-- ── Cabecera ── --}}
        <section class="al-hero pf-hero">
            <div class="al-hero-fila">
                <div class="pf-identidad">
                    @if ($docente->foto)
                        <img class="pf-foto" src="{{ asset('docentes/fotos/' . $docente->foto) }}" alt="Foto de {{ $docente->nombre }}">
                    @else
                        <span class="pf-foto pf-foto-iniciales" aria-label="Sin foto de perfil">{{ $iniciales }}</span>
                    @endif
                    <div class="al-hero-texto">
                        <div class="al-kicker"><i class="fas fa-id-badge"></i> Mi perfil</div>
                        <h1>{{ $docente->nombre }}</h1>
                        <div class="al-chips">
                            <span class="al-chip"><i class="fas fa-envelope"></i>{{ $docente->email }}</span>
                            @if ($telefono)
                                <span class="al-chip"><i class="fas fa-phone"></i>{{ $telefono }}</span>
                            @endif
                            @if (isset($periodoActual) && $periodoActual)
                                <span class="al-chip"><i class="far fa-calendar-alt"></i>Periodo {{ $periodoActual->nombre }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('docente.edit', $docente->id) }}" class="al-btn al-btn-oro"><i class="fas fa-pen"></i> Editar perfil</a>
                    <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}" class="al-btn al-btn-claro"><i class="fas fa-book"></i> Mis cursos</a>
                </div>
            </div>
        </section>

        {{-- ── Indicadores ── --}}
        <div class="al-stats">
            <div class="al-stat"><span class="al-stat-icono is-oro"><i class="fas fa-graduation-cap"></i></span>
                <div><div class="al-stat-valor">{{ $cursosFid->count() }}</div><div class="al-stat-etiqueta">Cursos FID</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-info"><i class="fas fa-chalkboard-teacher"></i></span>
                <div><div class="al-stat-valor">{{ $cursosPpd->count() }}</div><div class="al-stat-etiqueta">Cursos PPD</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-ok"><i class="fas fa-project-diagram"></i></span>
                <div><div class="al-stat-valor">{{ $proyectos->count() }}</div><div class="al-stat-etiqueta">Proyectos Integradores a cargo</div></div></div>
            <div class="al-stat"><span class="al-stat-icono"><i class="fas fa-laptop"></i></span>
                <div><div class="al-stat-valor">{{ $asincronicos->count() }}</div><div class="al-stat-etiqueta">Cursos asincrónicos</div></div></div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                {{-- ── Sobre mí ── --}}
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-user"></i></span>
                        <div><h2>Sobre mí</h2><p>Así te presentas ante tus estudiantes.</p></div>
                        <div class="al-card-cab-extra">
                            <a href="{{ route('docente.edit', $docente->id) }}" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-pen"></i> Editar</a>
                        </div>
                    </div>
                    <div class="al-card-cuerpo">
                        @if (filled($docente->descripcion))
                            <div class="pf-descripcion">{!! nl2br(e($docente->descripcion)) !!}</div>
                        @else
                            <div class="al-vacio py-3">
                                <i class="fas fa-feather-alt"></i>
                                Aún no escribes una descripción. Cuéntales a tus estudiantes tu formación, tus áreas de trabajo o cómo contactarte.
                                <div class="mt-3"><a href="{{ route('docente.edit', $docente->id) }}" class="al-btn al-btn-oro al-btn-sm"><i class="fas fa-plus"></i> Escribir descripción</a></div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── Mis cursos ── --}}
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-book"></i></span>
                        <div><h2>Mis cursos</h2><p>{{ $cursos->count() }} {{ $cursos->count() === 1 ? 'curso asignado' : 'cursos asignados' }}, agrupados por programa.</p></div>
                        <div class="al-card-cab-extra">
                            <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}" class="al-btn al-btn-borde al-btn-sm">Ir a mis cursos <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="al-card-cuerpo">
                        @forelse ($porPrograma as $programa => $lista)
                            <div class="pf-programa">
                                <div class="pf-programa-titulo">
                                    {{ $programa }}
                                    <span class="al-badge {{ $lista->first()->esPpd() ? 'is-info' : 'is-warn' }}">{{ $lista->first()->esPpd() ? 'PPD' : 'FID' }}</span>
                                </div>
                                <div class="pf-cursos">
                                    @foreach ($lista as $curso)
                                        <div class="pf-curso">
                                            <span class="pf-curso-ciclo">{{ $curso->ciclo?->nombre ?? '—' }}</span>
                                            <span class="pf-curso-nombre">{{ $curso->nombre }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="al-vacio py-3"><i class="fas fa-inbox"></i>No tienes cursos asignados por ahora.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                {{-- ── Perfil completo ── --}}
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-tasks"></i></span>
                        <div><h2>Tu perfil está al {{ $avance }}%</h2><p>Lo que ven tus estudiantes.</p></div>
                    </div>
                    <div class="al-card-cuerpo">
                        <div class="pf-barra" role="progressbar" aria-valuenow="{{ $avance }}" aria-valuemin="0" aria-valuemax="100">
                            <span style="width: {{ $avance }}%"></span>
                        </div>
                        <ul class="pf-pasos">
                            @foreach ($pasos as $paso)
                                <li class="{{ $paso['hecho'] ? 'is-hecho' : '' }}">
                                    <i class="fas {{ $paso['hecho'] ? 'fa-check-circle' : 'fa-circle' }}"></i> {{ $paso['texto'] }}
                                </li>
                            @endforeach
                        </ul>
                        @if ($avance < 100)
                            <a href="{{ route('docente.edit', $docente->id) }}" class="al-btn al-btn-borde al-btn-sm mt-2"><i class="fas fa-pen"></i> Completar perfil</a>
                        @endif
                    </div>
                </div>

                {{-- ── Datos de la cuenta ── --}}
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-user-shield"></i></span>
                        <div><h2>Mi cuenta</h2><p>Datos de acceso y contacto.</p></div>
                    </div>
                    <div class="al-card-cuerpo">
                        <dl class="pf-datos">
                            <dt>Correo</dt>
                            <dd><a href="mailto:{{ $docente->email }}">{{ $docente->email }}</a></dd>
                            <dt>DNI</dt>
                            <dd>{{ $docente->dni ?: '—' }}</dd>
                            <dt>Teléfono</dt>
                            <dd>{{ $telefono ?: '—' }}</dd>
                            @if ($roles->isNotEmpty())
                                <dt>Roles</dt>
                                <dd class="pf-roles">
                                    @foreach ($roles as $rol)
                                        <span class="al-badge is-muted">{{ $rol }}</span>
                                    @endforeach
                                </dd>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- ── Proyectos Integradores a cargo ── --}}
                @if ($proyectos->isNotEmpty())
                    <div class="al-card">
                        <div class="al-card-cab">
                            <span class="al-card-cab-icono"><i class="fas fa-project-diagram"></i></span>
                            <div><h2>Proyectos Integradores</h2><p>Donde eres encargado(a) u organizador(a).</p></div>
                        </div>
                        <div class="al-card-cuerpo pf-lista-enlaces">
                            @foreach ($proyectos as $pi)
                                @php $url = Route::has('taqe.proyecto.index') ? route('taqe.proyecto.index', $pi) : null; @endphp
                                @if ($url)
                                    <a href="{{ $url }}" class="pf-enlace" title="Ver los grupos de Investigaciones de este proyecto">
                                @else
                                    <div class="pf-enlace">
                                @endif
                                    <span class="pf-enlace-icono"><i class="fas fa-flask"></i></span>
                                    <span class="pf-enlace-texto">
                                        <strong>{{ $pi->nombre }}</strong>
                                        <small>{{ $pi->pivot->rol === 'encargado' ? 'Encargado(a)' : 'Organizador(a)' }}{{ $pi->producto ? ' · '.$pi->producto : '' }}</small>
                                    </span>
                                @if ($url)
                                        <i class="fas fa-chevron-right pf-enlace-flecha"></i>
                                    </a>
                                @else
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .pf-identidad { display: flex; align-items: center; gap: 1.25rem; min-width: 0; flex: 1; }
        .pf-foto {
            width: 96px; height: 96px; flex: none; border-radius: 22px; object-fit: cover;
            border: 3px solid rgba(255, 255, 255, .85); box-shadow: 0 10px 28px rgba(0, 0, 0, .25); background: #fff;
        }
        .pf-foto-iniciales {
            display: inline-flex; align-items: center; justify-content: center;
            background: #cd9244; color: #fff; font-size: 2rem; font-weight: 800; letter-spacing: .02em;
        }
        .pf-hero h1 { overflow-wrap: anywhere; }
        @media (max-width: 575.98px) {
            .pf-identidad { flex-direction: column; align-items: flex-start; gap: .85rem; }
            .pf-foto { width: 76px; height: 76px; border-radius: 18px; }
        }

        .pf-descripcion { font-size: .92rem; line-height: 1.7; color: var(--al-texto); }

        .pf-programa + .pf-programa { margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--al-borde); }
        .pf-programa-titulo { display: flex; align-items: center; justify-content: space-between; gap: .5rem; font-size: .82rem; font-weight: 800; color: var(--al-texto); margin-bottom: .55rem; }
        .pf-cursos { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: .45rem; }
        .pf-curso { display: flex; align-items: center; gap: .6rem; padding: .5rem .65rem; border: 1px solid var(--al-borde); border-radius: 10px; background: var(--al-fondo); }
        .pf-curso-ciclo {
            flex: none; min-width: 2.2rem; text-align: center; font-size: .7rem; font-weight: 800; padding: .2rem .4rem; border-radius: 8px;
            background: rgba(205, 146, 68, .14); color: var(--al-oro-2);
        }
        .pf-curso-nombre { font-size: .82rem; font-weight: 600; color: var(--al-texto); line-height: 1.3; }

        .pf-barra { height: 8px; border-radius: 999px; background: var(--al-fondo-2); overflow: hidden; }
        .pf-barra span { display: block; height: 100%; border-radius: 999px; background: #cd9244; transition: width .3s ease; }
        .pf-pasos { list-style: none; padding: 0; margin: .9rem 0 0; display: flex; flex-direction: column; gap: .45rem; }
        .pf-pasos li { font-size: .84rem; color: var(--al-suave); display: flex; align-items: center; gap: .5rem; }
        .pf-pasos li i { font-size: .8rem; opacity: .45; }
        .pf-pasos li.is-hecho { color: var(--al-texto); }
        .pf-pasos li.is-hecho i { color: var(--al-ok); opacity: 1; }

        .pf-datos { display: grid; grid-template-columns: auto 1fr; gap: .55rem 1rem; margin: 0; font-size: .85rem; }
        .pf-datos dt { font-size: .66rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--al-suave); padding-top: .15rem; }
        .pf-datos dd { margin: 0; color: var(--al-texto); min-width: 0; overflow-wrap: anywhere; }
        .pf-roles { display: flex; flex-wrap: wrap; gap: .3rem; }

        .pf-lista-enlaces { display: flex; flex-direction: column; gap: .45rem; }
        .pf-enlace { display: flex; align-items: center; gap: .7rem; padding: .6rem .7rem; border: 1px solid var(--al-borde); border-radius: 12px; color: var(--al-texto); text-decoration: none !important; transition: border-color .15s ease, background-color .15s ease; }
        a.pf-enlace:hover { border-color: rgba(205, 146, 68, .5); background: rgba(205, 146, 68, .06); color: var(--al-texto); }
        .pf-enlace-icono { width: 34px; height: 34px; flex: none; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: rgba(205, 146, 68, .14); color: var(--al-oro-2); }
        .pf-enlace-texto { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .pf-enlace-texto strong { font-size: .84rem; }
        .pf-enlace-texto small { font-size: .72rem; color: var(--al-suave); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pf-enlace-flecha { color: var(--al-suave); font-size: .75rem; }
    </style>
@endpush
