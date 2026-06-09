@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')

    @include('admin._partials.prog-styles')
    <style>
        /* ── Botones de toggle formulario ─────────────────────────────── */
        .pg-toggle-on {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .pg-toggle-off {
            background: var(--pg-card);
            color: var(--pg-muted);
            border: 1px solid var(--pg-border);
        }

        .dark-mode .pg-toggle-on {
            background: rgba(22, 163, 74, .15);
            color: #86efac;
            border-color: rgba(22, 163, 74, .3);
        }

        .dark-mode .pg-toggle-off {
            background: rgba(255, 255, 255, .05);
            color: var(--pg-muted);
            border-color: var(--pg-border);
        }

        .dim-mode .pg-toggle-on {
            background: rgba(22, 163, 74, .12);
            color: #86efac;
            border-color: rgba(22, 163, 74, .25);
        }

        .dim-mode .pg-toggle-off {
            background: rgba(255, 255, 255, .04);
            color: var(--pg-muted);
            border-color: var(--pg-border);
        }

        /* ── Selector de tabla activa ─────────────────────────────────── */
        .pg-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .38rem .9rem;
            border-radius: .5rem;
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: background .18s, border-color .18s;
        }

        .pg-tab-fid {
            background: rgba(37, 99, 235, .12);
            color: #2563eb;
            border-color: rgba(37, 99, 235, .25);
        }

        .pg-tab-ppd {
            background: rgba(6, 182, 212, .1);
            color: #0e7490;
            border-color: rgba(6, 182, 212, .25);
        }

        .dark-mode .pg-tab-fid {
            background: rgba(37, 99, 235, .22);
            color: #93c5fd;
            border-color: rgba(37, 99, 235, .35);
        }

        .dark-mode .pg-tab-ppd {
            background: rgba(6, 182, 212, .18);
            color: #67e8f9;
            border-color: rgba(6, 182, 212, .3);
        }

        .dim-mode .pg-tab-fid {
            background: rgba(37, 99, 235, .18);
            color: #93c5fd;
            border-color: rgba(37, 99, 235, .28);
        }

        .dim-mode .pg-tab-ppd {
            background: rgba(6, 182, 212, .14);
            color: #67e8f9;
            border-color: rgba(6, 182, 212, .25);
        }

        /* ── Tabla wrapper ────────────────────────────────────────────── */
        .pg-table-wrap {
            background: var(--pg-card);
            border: 1px solid var(--pg-border);
            border-radius: .85rem;
            overflow: hidden;
            box-shadow: var(--pg-shadow);
        }

        .pg-table-head-bar {
            padding: .7rem 1.1rem;
            border-bottom: 1px solid var(--pg-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            background: var(--pg-card);
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
                            <i class="fas fa-calendar-alt"></i> EESP Pukllasunchis
                        </div>
                        <h4>Periodos Académicos</h4>
                        <p class="pg-hero-sub">Gestión y visualización de períodos FID y PPD</p>
                    </div>
                    <div class="d-flex" style="gap:.5rem; align-self:flex-start;">
                        <button class="pg-hero-btn" onclick="mostrarTabla('tablaActual')">
                            <i class="fas fa-graduation-cap fa-xs"></i> Periodos FID
                        </button>
                        <button class="pg-hero-btn" style="background:rgba(251,191,36,.22); border-color:rgba(251,191,36,.45);"
                                onclick="mostrarTabla('tablaPPD')">
                            <i class="fas fa-user-graduate fa-xs"></i> Periodos PPD
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Stats ── --}}
            <div class="row mb-4">
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
                            <i class="fas fa-calendar-check" style="color:#2563eb;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ count($periodoactuales) }}</div>
                            <div class="pg-stat-lbl">Periodos FID</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#cffafe,#a5f3fc);">
                            <i class="fas fa-calendar-alt" style="color:#0e7490;"></i>
                        </div>
                        <div>
                            <div class="pg-stat-val">{{ count($periodosppd) }}</div>
                            <div class="pg-stat-lbl">Periodos PPD</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-6 mb-3">
                    <div class="pg-stat">
                        <div class="pg-stat-icon" style="background:linear-gradient(135deg,#dcfce7,#bbf7d0);">
                            <i class="fas fa-circle" style="color:#16a34a; font-size:.65rem;"></i>
                        </div>
                        <div>
                            @php $activos = $periodoactuales->where('actual', true)->count() + $periodosppd->where('actual', true)->count(); @endphp
                            <div class="pg-stat-val">{{ $activos }}</div>
                            <div class="pg-stat-lbl">Activos</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Alertas SweetAlert2 ── --}}
            @if (session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Listo!',
                            text: '{{ session('success') }}',
                            confirmButtonColor: '#28a745',
                            timer: 4000,
                            timerProgressBar: true
                        });
                    });
                </script>
            @endif
            @if (session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: '{{ session('error') }}',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                </script>
            @endif

            {{-- ════ TABLA FID ════════════════════════════════════════════════ --}}
            <div id="tablaActual">
                <div class="pg-section-lbl" style="opacity:1;">
                    <i class="fas fa-graduation-cap" style="color:#2563eb;"></i>
                    Periodos FID
                    <span
                        style="margin-left:.5rem; padding:.15rem .55rem; border-radius:2rem;
                         background:rgba(37,99,235,.1); color:#2563eb; font-size:.65rem;">
                        {{ count($periodoactuales) }} registros
                    </span>
                </div>

                <div class="pg-table-wrap mb-4">
                    <div class="pg-table-head-bar">
                        <span style="font-size:.75rem; font-weight:600; color:var(--pg-muted);">Gestión de Periodos
                            FID</span>
                        <a href="{{ route('periodoactual.create') }}" class="pg-btn pg-btn-view">
                            <i class="fas fa-plus-circle fa-xs"></i> Nuevo periodo FID
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="pg-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Nombre</th>
                                    <th>Horario</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Form. Matrícula</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodoactuales as $key => $p)
                                    <tr>
                                        <td style="font-weight:600; color:var(--pg-muted);">{{ $key + 1 }}</td>
                                        <td>
                                            <div style="font-weight:600; color:var(--pg-text);">{{ $p->nombre }}</div>
                                            <small style="color:var(--pg-muted);">ID: {{ $p->id }}</small>
                                        </td>
                                        <td>
                                            @if ($p->horario)
                                                <a href="{{ asset($p->horario) }}" target="_blank"
                                                    class="pg-btn pg-btn-view">
                                                    <i class="fas fa-file-pdf fa-xs"></i> Ver horario
                                                </a>
                                            @else
                                                <span
                                                    style="font-size:.76rem; color:var(--pg-muted); font-style:italic;">Sin
                                                    archivo</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.82rem; color:var(--pg-text);">
                                            @if ($p->fecha_inicio)
                                                {{ \Carbon\Carbon::parse($p->fecha_inicio)->translatedFormat('d M. Y') }}
                                            @else
                                                <span style="color:var(--pg-muted);">—</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.82rem; color:var(--pg-text);">
                                            @if ($p->fecha_cierre)
                                                {{ \Carbon\Carbon::parse($p->fecha_cierre)->translatedFormat('d M. Y') }}
                                            @else
                                                <span style="color:var(--pg-muted);">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($p->actual)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('periodoactual.toggleFormulario', $p) }}" method="POST"
                                                class="d-inline form-toggle-formulario" data-nombre="{{ $p->nombre }}"
                                                data-habilitado="{{ $p->formulario_habilitado ? '1' : '0' }}">
                                                @csrf
                                                @if ($p->formulario_habilitado)
                                                    <button type="button" class="pg-btn pg-toggle-on btn-toggle-formulario"
                                                        title="Habilitado — clic para deshabilitar">
                                                        <i class="fas fa-lock-open fa-xs"></i> Habilitado
                                                    </button>
                                                @else
                                                    <button type="button"
                                                        class="pg-btn pg-toggle-off btn-toggle-formulario"
                                                        title="Deshabilitado — clic para habilitar">
                                                        <i class="fas fa-lock fa-xs"></i> Deshabilitado
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center flex-wrap" style="gap:.3rem;">
                                                <a href="{{ route('periodoactual.edit', $p) }}" class="pg-btn pg-btn-edit">
                                                    <i class="fas fa-pencil-alt fa-xs"></i> Editar
                                                </a>
                                                @if ($p->actual)
                                                    @if ($p->periodos->isEmpty())
                                                        <form
                                                            action="{{ route('periodoactual.crearCalificaciones', $p->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="pg-btn pg-btn-view">
                                                                <i class="fas fa-book fa-xs"></i> Crear
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form
                                                            action="{{ route('periodoactual.crearCalificaciones', $p->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="pg-btn pg-btn-cycle">
                                                                <i class="fas fa-sync-alt fa-xs"></i> Sincronizar
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('periodoactual.showRegistros', $p->id) }}"
                                                            class="pg-btn pg-btn-view">
                                                            <i class="fas fa-list fa-xs"></i> Ver
                                                        </a>
                                                    @endif
                                                @else
                                                    @if (!$p->periodos->isEmpty())
                                                        <a href="{{ route('periodoactual.showRegistros', $p->id) }}"
                                                            class="pg-btn pg-btn-view">
                                                            <i class="fas fa-list fa-xs"></i> Ver
                                                        </a>
                                                    @endif
                                                @endif
                                                <form action="{{ route('periodoactual.destroy', $p) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="pg-btn pg-btn-del"
                                                        onclick="return confirm('¿Eliminar este período?')">
                                                        <i class="fas fa-trash-alt fa-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center"
                                            style="padding:3rem 1rem; color:var(--pg-muted);">
                                            <i class="fas fa-calendar-times fa-2x mb-2"
                                                style="opacity:.3; display:block;"></i>
                                            No hay períodos registrados
                                            <div class="mt-2">
                                                <a href="{{ route('periodoactual.create') }}" class="pg-btn pg-btn-view">
                                                    <i class="fas fa-plus fa-xs"></i> Crear primer período
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ════ TABLA PPD ════════════════════════════════════════════════ --}}
            <div id="tablaPPD" style="display:none;">
                <div class="pg-section-lbl" style="opacity:1;">
                    <i class="fas fa-user-graduate" style="color:#0e7490;"></i>
                    Periodos PPD
                    <span
                        style="margin-left:.5rem; padding:.15rem .55rem; border-radius:2rem;
                         background:rgba(6,182,212,.1); color:#0e7490; font-size:.65rem;">
                        {{ count($periodosppd) }} registros
                    </span>
                </div>

                <div class="pg-table-wrap mb-4">
                    <div class="pg-table-head-bar">
                        <span style="font-size:.75rem; font-weight:600; color:var(--pg-muted);">Gestión de Periodos
                            PPD</span>
                        <a href="{{ route('periodos.admin.ppd.create') }}" class="pg-btn pg-btn-view"
                            style="background:rgba(6,182,212,.1); color:#0e7490; border-color:rgba(6,182,212,.3);">
                            <i class="fas fa-plus-circle fa-xs"></i> Nuevo periodo PPD
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="pg-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Nombre</th>
                                    <th>Calendario</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodosppd as $key => $p)
                                    <tr>
                                        <td style="font-weight:600; color:var(--pg-muted);">{{ $key + 1 }}</td>
                                        <td>
                                            <div style="font-weight:600; color:var(--pg-text);">{{ $p->nombre }}</div>
                                            <small style="color:var(--pg-muted);">ID: {{ $p->id }}</small>
                                        </td>
                                        <td>
                                            @if ($p->calendario)
                                                <a href="{{ asset($p->calendario) }}" target="_blank"
                                                    class="pg-btn pg-btn-view">
                                                    <i class="fas fa-calendar-alt fa-xs"></i> Ver calendario
                                                </a>
                                            @else
                                                <span
                                                    style="font-size:.76rem; color:var(--pg-muted); font-style:italic;">Sin
                                                    archivo</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.82rem; color:var(--pg-text);">
                                            @if ($p->fecha_inicio)
                                                {{ \Carbon\Carbon::parse($p->fecha_inicio)->translatedFormat('d M. Y') }}
                                            @else
                                                <span style="color:var(--pg-muted);">—</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.82rem; color:var(--pg-text);">
                                            @if ($p->fecha_cierre)
                                                {{ \Carbon\Carbon::parse($p->fecha_cierre)->translatedFormat('d M. Y') }}
                                            @else
                                                <span style="color:var(--pg-muted);">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($p->actual)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center flex-wrap" style="gap:.3rem;">
                                                <a href="{{ route('periodos.admin.ppd.edit', $p) }}"
                                                    class="pg-btn pg-btn-edit">
                                                    <i class="fas fa-pencil-alt fa-xs"></i> Editar
                                                </a>
                                                @if ($p->actual)
                                                    <span
                                                        style="font-size:.7rem; color:var(--pg-muted); align-self:center;">
                                                        <i class="fas fa-info-circle mr-1"></i> Calificaciones al cerrar
                                                        ciclo
                                                    </span>
                                                @elseif ($p->periodosPpd->isEmpty())
                                                    <form
                                                        action="{{ route('periodos.admin.ppd.crearCalificaciones', $p->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="pg-btn pg-btn-view">
                                                            <i class="fas fa-book fa-xs"></i> Crear
                                                        </button>
                                                    </form>
                                                @else
                                                    <form
                                                        action="{{ route('periodos.admin.ppd.sincronizarCalificaciones', $p->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="pg-btn pg-btn-cycle">
                                                            <i class="fas fa-sync-alt fa-xs"></i> Sinc.
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('periodos.admin.ppd.show', $p->id) }}"
                                                        class="pg-btn pg-btn-view">
                                                        <i class="fas fa-list fa-xs"></i> Ver
                                                    </a>
                                                @endif
                                                <form action="{{ route('periodos.admin.ppd.destroy', $p) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="pg-btn pg-btn-del"
                                                        onclick="return confirm('¿Eliminar este período?')">
                                                        <i class="fas fa-trash-alt fa-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center"
                                            style="padding:3rem 1rem; color:var(--pg-muted);">
                                            <i class="fas fa-clock fa-2x mb-2" style="opacity:.3; display:block;"></i>
                                            No hay períodos PPD registrados
                                            <div class="mt-2">
                                                <a href="{{ route('periodos.admin.ppd.create') }}"
                                                    class="pg-btn pg-btn-view"
                                                    style="background:rgba(6,182,212,.1); color:#0e7490; border-color:rgba(6,182,212,.3);">
                                                    <i class="fas fa-plus fa-xs"></i> Crear primer período
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── GSAP ── --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tl = gsap.timeline({
                defaults: {
                    ease: 'power3.out'
                }
            });
            tl.fromTo('#pg-hero', {
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

            function mostrarTabla(id) {
                document.getElementById('tablaActual').style.display = 'none';
                document.getElementById('tablaPPD').style.display = 'none';
                document.getElementById(id).style.display = 'block';
            }
            window.mostrarTabla = mostrarTabla;

            document.querySelectorAll('.btn-toggle-formulario').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var form = btn.closest('.form-toggle-formulario');
                    var nombre = form.dataset.nombre;
                    var habilitado = form.dataset.habilitado === '1';
                    Swal.fire({
                        title: habilitado ? '¿Deshabilitar formulario?' :
                            '¿Habilitar formulario?',
                        html: habilitado ?
                            'Los alumnos <strong>ya no podrán</strong> matricularse en:<br><strong>' +
                            nombre + '</strong>' :
                            'Los alumnos <strong>podrán matricularse</strong> en:<br><strong>' +
                            nombre + '</strong>',
                        icon: habilitado ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: habilitado ? '#6c757d' : '#28a745',
                        cancelButtonColor: '#adb5bd',
                        confirmButtonText: habilitado ? 'Sí, deshabilitar' :
                            'Sí, habilitar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        focusCancel: true,
                    }).then(function(result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>

@endsection
