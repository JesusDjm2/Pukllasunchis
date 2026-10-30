@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'IESP Ricardo Palma - Actas Consolidadas')

@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid py-3 al-ui rp-modulo">
        {{-- Hero institucional --}}
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <i class="fas fa-archive"></i> Acervo Histórico (1995 - 2015)
                    </div>
                    <h1>IESP Ricardo Palma</h1>
                    <p class="al-hero-sub">
                        Módulo de consulta, gestión y custodia de actas consolidadas y rendimiento académico de los egresados de la IESP Ricardo Palma.
                    </p>
                    <div class="al-chips">
                        <span class="al-chip"><i class="fas fa-university"></i> 4 Carreras Profesionales</span>
                        <span class="al-chip"><i class="fas fa-calendar-alt"></i> 10 Semestres Académicos</span>
                        <span class="al-chip is-ok"><i class="fas fa-shield-alt"></i> Acceso Administrativo</span>
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('rp.importar.index') }}" class="al-btn al-btn-oro">
                        <i class="fas fa-file-excel"></i> Carga Masiva
                    </a>
                    <a href="{{ route('rp.actas.index') }}" class="al-btn al-btn-claro">
                        <i class="fas fa-table"></i> Ver Actas
                    </a>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        {{-- Alertas del sistema --}}
        @if (Session::has('success'))
            <div class="al-aviso is-ok mb-3">
                <i class="fas fa-check-circle"></i>
                <div>{{ Session::get('success') }}</div>
            </div>
        @endif
        @if (Session::has('warning'))
            <div class="al-aviso is-warn mb-3">
                <i class="fas fa-exclamation-triangle"></i>
                <div>{{ Session::get('warning') }}</div>
            </div>
        @endif

        {{-- Métricas e Indicadores --}}
        <div class="al-stats">
            <a href="{{ route('rp.programas.index') }}" class="al-stat">
                <div class="al-stat-icono is-oro">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <div class="al-stat-valor">{{ $totalProgramas }}</div>
                    <div class="al-stat-etiqueta">Carreras</div>
                </div>
            </a>
            <div class="al-stat">
                <div class="al-stat-icono is-info">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="al-stat-valor">{{ $totalPeriodos }}</div>
                    <div class="al-stat-etiqueta">Periodos (1995-2015)</div>
                </div>
            </div>
            <a href="{{ route('rp.estudiantes.index') }}" class="al-stat">
                <div class="al-stat-icono is-ok">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div class="al-stat-valor">{{ $totalEstudiantes }}</div>
                    <div class="al-stat-etiqueta">Estudiantes Registrados</div>
                </div>
            </a>
            <a href="{{ route('rp.actas.index') }}" class="al-stat">
                <div class="al-stat-icono is-oro">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <div class="al-stat-valor">{{ $totalActas }}</div>
                    <div class="al-stat-etiqueta">Actas Consolidadas</div>
                </div>
            </a>
        </div>

        {{-- Carreras Profesionales Base & Actas Recientes --}}
        <div class="row">
            {{-- Columna izquierda: Carreras --}}
            <div class="col-lg-7 mb-4">
                <div class="al-card h-100 mb-0">
                    <div class="al-card-cab">
                        <div class="al-card-cab-icono">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div>
                            <h2>Carreras Profesionales</h2>
                            <p>Especialidades y planes curriculares de 10 semestres</p>
                        </div>
                        <div class="al-card-cab-extra">
                            <a href="{{ route('rp.programas.index') }}" class="al-btn al-btn-sm al-btn-borde">
                                Ver Mallas <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="al-tabla-marco">
                        <table class="al-tabla">
                            <thead>
                                <tr>
                                    <th>Carrera</th>
                                    <th class="text-center">Semestres</th>
                                    <th class="text-center">Cursos Registrados</th>
                                    <th class="text-center">Estudiantes</th>
                                    <th class="text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($programas as $p)
                                    <tr>
                                        <td>
                                            <a href="{{ route('rp.programas.show', $p) }}" class="font-weight-bold">
                                                <i class="fas fa-award mr-1" style="color: var(--al-oro);"></i>
                                                {{ $p->nombre }}
                                            </a>
                                            <div class="al-suave" style="font-size: .72rem;">Cód: {{ $p->codigo }}</div>
                                        </td>
                                        <td class="text-center al-suave">{{ $p->total_semestres }}</td>
                                        <td class="text-center">
                                            <span class="al-badge is-muted">{{ $p->cursos_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="al-badge is-info">{{ $p->estudiantes_count }}</span>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('rp.programas.show', $p) }}" class="al-btn al-btn-sm al-btn-borde" title="Ver detalle de asignaturas">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="al-vacio">No hay programas cargados.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Columna derecha: Últimas Actas Consolidadas --}}
            <div class="col-lg-5 mb-4">
                <div class="al-card h-100 mb-0">
                    <div class="al-card-cab">
                        <div class="al-card-cab-icono">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h2>Actas Recientes</h2>
                            <p>Últimos consolidados incorporados</p>
                        </div>
                        <div class="al-card-cab-extra">
                            <a href="{{ route('rp.actas.index') }}" class="al-btn al-btn-sm al-btn-borde">
                                Ver Todas <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <ul class="al-cursos">
                        @forelse ($ultimasActas as $acta)
                            <li class="al-curso">
                                <div class="al-curso-num">
                                    <i class="fas fa-file-invoice" style="color: var(--al-oro);"></i>
                                </div>
                                <div class="al-curso-texto">
                                    <a href="{{ route('rp.actas.show', $acta) }}" class="al-curso-nombre">
                                        Acta {{ $acta->numero_acta ? '№ ' . $acta->numero_acta : '#' . $acta->id }}
                                        <span class="al-badge is-info ml-1" style="font-size: .65rem;">{{ $acta->periodo?->codigo }}</span>
                                    </a>
                                    <div class="al-curso-meta">
                                        {{ $acta->programa?->nombre }} · Semestre {{ $acta->numero_semestre }}
                                    </div>
                                    <div class="al-curso-meta">
                                        <i class="far fa-calendar-check mr-1"></i>Visación:
                                        @if ($acta->fecha_emision)
                                            <strong>{{ $acta->fecha_emision->format('d/m/Y') }}</strong>
                                        @else
                                            <span class="al-badge is-warn" style="font-size: .62rem;">Sin fecha</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="al-curso-accion">
                                    <span class="al-badge is-ok">{{ $acta->calificaciones_count }} notas</span>
                                    <a href="{{ route('rp.actas.show', $acta) }}" class="al-btn al-btn-sm al-btn-borde">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </li>
                        @empty
                            <div class="al-vacio">
                                <i class="fas fa-folder-open"></i>
                                <p class="mb-2">Aún no se han importado actas consolidadas.</p>
                                <a href="{{ route('rp.importar.index') }}" class="al-btn al-btn-sm al-btn-oro">
                                    <i class="fas fa-upload mr-1"></i> Importar Primer Lote
                                </a>
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
