@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    @include('admin._partials.prog-styles')
    <style>
        /* ── Filtro de tabs ────────────────────────────────────────────── */
        .pg-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: .32rem;
            padding: .32rem .78rem;
            border-radius: .5rem;
            font-size: .74rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--pg-border);
            background: var(--pg-card);
            color: var(--pg-muted);
            transition: background .18s, color .18s, border-color .18s;
        }

        .pg-filter-btn:hover,
        .pg-filter-btn.active {
            background: rgba(37, 99, 235, .1);
            color: #2563eb;
            border-color: rgba(37, 99, 235, .28);
        }

        .pg-filter-btn.active-ppd {
            background: rgba(245, 158, 11, .1);
            color: #b45309;
            border-color: rgba(245, 158, 11, .28);
        }

        .dark-mode .pg-filter-btn {
            background: rgba(255, 255, 255, .04);
            color: var(--pg-muted);
            border-color: var(--pg-border);
        }

        .dark-mode .pg-filter-btn:hover,
        .dark-mode .pg-filter-btn.active {
            background: rgba(37, 99, 235, .2);
            color: #93c5fd;
            border-color: rgba(37, 99, 235, .35);
        }

        .dark-mode .pg-filter-btn.active-ppd {
            background: rgba(245, 158, 11, .18);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, .3);
        }

        .dim-mode .pg-filter-btn {
            background: rgba(255, 255, 255, .03);
            color: var(--pg-muted);
            border-color: var(--pg-border);
        }

        .dim-mode .pg-filter-btn:hover,
        .dim-mode .pg-filter-btn.active {
            background: rgba(37, 99, 235, .15);
            color: #93c5fd;
            border-color: rgba(37, 99, 235, .28);
        }

        .dim-mode .pg-filter-btn.active-ppd {
            background: rgba(245, 158, 11, .14);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, .25);
        }

        /* ── Buscador ─────────────────────────────────────────────────── */
        .pg-search-wrap {
            position: relative;
            max-width: 360px;
        }

        .pg-search-wrap .pg-search-icon {
            position: absolute;
            left: .7rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pg-muted);
            font-size: .8rem;
            pointer-events: none;
        }

        .pg-search-wrap .pg-input {
            padding-left: 2rem !important;
        }

        /* ── Tabla container ──────────────────────────────────────────── */
        .pg-table-wrap {
            background: var(--pg-card);
            border: 1px solid var(--pg-border);
            border-radius: .85rem;
            overflow: hidden;
            box-shadow: var(--pg-shadow);
        }

        /* ── Celda datos curso ────────────────────────────────────────── */
        .curso-name {
            font-size: .9rem;
            font-weight: 700;
            color: var(--pg-text);
            line-height: 1.3;
        }

        .curso-meta {
            margin: 0;
            padding: 0;
            list-style: none;
            margin-top: .3rem;
        }

        .curso-meta li {
            font-size: .76rem;
            color: var(--pg-muted);
            line-height: 1.7;
        }

        .curso-meta li strong {
            color: var(--pg-text-2, var(--pg-text));
            font-weight: 600;
        }
    </style>

    <div class="pg-wrap">
        <div class="container-fluid py-3">

            {{-- ── Hero ── --}}
            <div class="pg-hero" id="pg-hero">
                <div class="d-flex align-items-center justify-content-between flex-wrap"
                    style="gap:1rem; position:relative; z-index:1;">
                    <div>
                        <div class="pg-hero-label">
                            <i class="fas fa-university"></i> EESP Pukllasunchis
                        </div>
                        <h4>Cursos</h4>
                        <p class="pg-hero-sub">Listado completo de cursos organizados por programa y ciclo</p>
                    </div>
                    <a href="{{ route('curso.create') }}" class="pg-hero-btn align-self-start">
                        <i class="fas fa-plus fa-xs"></i> Nuevo Curso
                    </a>
                </div>
            </div>

            {{-- ── Stats ── --}}
            <div class="row mb-3">
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
                            <i class="fas fa-book" style="color:#2563eb;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ $cant }}</div>
                            <div class="pg-stat-lbl">Total Cursos</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
                            <i class="fas fa-graduation-cap" style="color:#16a34a;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ $inicial + $EIB }}</div>
                            <div class="pg-stat-lbl">Cursos FID</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
                            <i class="fas fa-user-graduate" style="color:#d97706;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ $iniPPD + $priPPD }}</div>
                            <div class="pg-stat-lbl">Cursos PPD</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Alerta ── --}}
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
                    <a type="button" class="close" data-dismiss="alert"><span>&times;</span></a>
                </div>
            @endif

            {{-- ── Filtros + Buscador ── --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:.6rem;">
                <div class="d-flex flex-wrap" style="gap:.4rem;">
                    <button class="pg-filter-btn active" id="todos">
                        <i class="fas fa-th-list fa-xs"></i> Todos <span class="ml-1"
                            style="opacity:.7;">{{ $cant }}</span>
                    </button>
                    <button class="pg-filter-btn" id="inicial">
                        <i class="fas fa-graduation-cap fa-xs"></i> Inicial <span class="ml-1"
                            style="opacity:.7;">{{ $inicial }}</span>
                    </button>
                    <button class="pg-filter-btn" id="eib">
                        <i class="fas fa-graduation-cap fa-xs"></i> Primaria EIB <span class="ml-1"
                            style="opacity:.7;">{{ $EIB }}</span>
                    </button>
                    <button class="pg-filter-btn active-ppd" id="inippd">
                        <i class="fas fa-user-graduate fa-xs"></i> Inicial PPD <span class="ml-1"
                            style="opacity:.7;">{{ $iniPPD }}</span>
                    </button>
                    <button class="pg-filter-btn active-ppd" id="prippd">
                        <i class="fas fa-user-graduate fa-xs"></i> Primaria PPD <span class="ml-1"
                            style="opacity:.7;">{{ $priPPD }}</span>
                    </button>
                </div>
                <div class="pg-search-wrap">
                    <i class="fas fa-search pg-search-icon"></i>
                    <input type="text" class="pg-input" id="buscador" placeholder="Buscar curso...">
                </div>
            </div>

            {{-- ── Macro tabla reutilizable ── --}}
            @php
                function renderCursoRow($curso)
                {
                    return $curso;
                }
            @endphp

            {{-- ── Tabla TODOS ── --}}
            <div id="tablaCursos" class="pg-table-wrap mb-3">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursos as $curso)
                                <tr>
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"
                                                title="Ver">
                                                <i class="fas fa-eye fa-xs"></i>
                                            </a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"
                                                title="Editar">
                                                <i class="fas fa-pen fa-xs"></i>
                                            </a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del" title="Eliminar">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla INICIAL ── --}}
            <div id="tablaInicial" class="pg-table-wrap mb-3" style="display:none;">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursosInicial as $curso)
                                <tr>
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"><i
                                                    class="fas fa-eye fa-xs"></i></a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"><i
                                                    class="fas fa-pen fa-xs"></i></a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del"><i
                                                        class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla EIB ── --}}
            <div id="tablaEib" class="pg-table-wrap mb-3" style="display:none;">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursosEib as $curso)
                                <tr>
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"><i
                                                    class="fas fa-eye fa-xs"></i></a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"><i
                                                    class="fas fa-pen fa-xs"></i></a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del"><i
                                                        class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla INICIAL PPD ── --}}
            <div id="tablaIniPpd" class="pg-table-wrap mb-3" style="display:none;">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inicialPPD as $curso)
                                <tr>
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"><i
                                                    class="fas fa-eye fa-xs"></i></a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"><i
                                                    class="fas fa-pen fa-xs"></i></a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del"><i
                                                        class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla PRIMARIA PPD ── --}}
            <div id="tablaPriPpd" class="pg-table-wrap mb-3" style="display:none;">
                <div class="table-responsive">
                    <table class="pg-table">
                        <thead>
                            <tr>
                                <th>Datos del Curso</th>
                                <th>Docente(s)</th>
                                <th>Competencias</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($primariaPPD as $curso)
                                <tr>
                                    <td style="min-width:220px;">
                                        <div class="curso-name">{{ $curso->nombre }}</div>
                                        <ul class="curso-meta">
                                            <li>
                                                <strong>Prog/Ciclo:</strong>
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    EIB
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
                                                    Inicial
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif
                                                &mdash; {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><strong>CC:</strong> {{ $curso->cc }}</li>
                                            <li><strong>Horas:</strong> {{ $curso->horas }} &nbsp;|&nbsp;
                                                <strong>Créditos:</strong> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td style="font-size:.8rem; color:var(--pg-text);">
                                        @foreach ($curso->docentes as $docente)
                                            <div>{{ $docente->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td style="font-size:.78rem; color:var(--pg-muted);">
                                        @if ($curso->competencias->isNotEmpty())
                                            <ul class="pl-3 mb-0">
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="font-style:italic;">Sin competencias</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="d-flex justify-content-center" style="gap:.3rem;">
                                            <a href="{{ route('curso.show', $curso->id) }}" class="pg-btn pg-btn-view"><i
                                                    class="fas fa-eye fa-xs"></i></a>
                                            <a href="{{ route('curso.edit', $curso->id) }}" class="pg-btn pg-btn-edit"><i
                                                    class="fas fa-pen fa-xs"></i></a>
                                            <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="pg-btn pg-btn-del"><i
                                                        class="fas fa-trash fa-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- ── GSAP ── --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            gsap.timeline({
                    defaults: {
                        ease: 'power3.out'
                    }
                })
                .fromTo('#pg-hero', {
                    opacity: 0,
                    y: -20,
                    scale: .98
                }, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: .5
                })
                .fromTo('.pg-stat', {
                    opacity: 0,
                    y: 18,
                    scale: .93
                }, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: .35,
                    stagger: .08
                }, '-=.18');
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var $allBtns = $('.pg-filter-btn');

            function showTable(tableId, btn) {
                $('#tablaCursos, #tablaInicial, #tablaEib, #tablaIniPpd, #tablaPriPpd').hide();
                $(tableId).show();
                $allBtns.removeClass('active');
                $(btn).addClass('active');
            }

            $('#todos').click(function() {
                showTable('#tablaCursos', this);
            });
            $('#inicial').click(function() {
                showTable('#tablaInicial', this);
            });
            $('#eib').click(function() {
                showTable('#tablaEib', this);
            });
            $('#inippd').click(function() {
                showTable('#tablaIniPpd', this);
            });
            $('#prippd').click(function() {
                showTable('#tablaPriPpd', this);
            });
        });
    </script>

    <script>
        document.getElementById('buscador').addEventListener('input', function() {
            var filtro = this.value.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').split(' ');
            document.querySelectorAll('table tbody tr').forEach(function(fila) {
                var texto = fila.textContent.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                var ok = filtro.every(function(p) {
                    return texto.indexOf(p) !== -1;
                });
                fila.style.display = ok ? '' : 'none';
            });
        });
    </script>
@endsection
