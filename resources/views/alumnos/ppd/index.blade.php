@extends('layouts.profesionalizacion')
@section('contenido')
    <div class="container-fluid pt-2">
        <div class="ppd-page-header">
            <div>
                <span class="ppd-eyebrow"><i class="fa fa-graduation-cap mr-1"></i>Profesionalización Docente</span>
                <h3 class="ppd-page-title"><i class="fa fa-file-text mr-2"></i>Ficha Técnica</h3>
            </div>
            <div class="ppd-page-actions">
                @if ($alumno && $formularioHabilitado && $periodoActualPpd)
                    @if ($matriculaActual)
                        <span class="badge badge-success px-3 py-2" style="font-size:13px;border-radius:6px;">
                            <i class="fa fa-check-circle mr-1"></i>Matriculado · {{ $periodoActualPpd->nombre }}
                        </span>
                    @else
                        <a href="{{ route('ppd.edit', $alumno->id) }}" class="btn btn-sm btn-warning shadow-sm">
                            <i class="fa fa-graduation-cap mr-1"></i> Completar matrícula
                        </a>
                    @endif
                @endif
            </div>
        </div>

        <!-- Alertas con mejor presentación -->
        <div class="row">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show text-center shadow-sm" role="alert">
                        <i class="fa fa-check-circle mr-2"></i>
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if ($alumno && $formularioHabilitado && $periodoActualPpd && ! $matriculaActual)
            <div class="alert alert-warning d-flex align-items-center shadow-sm mb-4 py-2 border-0">
                <i class="fa fa-exclamation-circle fa-lg mr-3 flex-shrink-0"></i>
                <span>El período <strong>{{ $periodoActualPpd->nombre }}</strong> está abierto.
                    Actualiza tus datos generales para completar tu matrícula usando el botón del encabezado.
                    No es necesario volver a llenar toda la ficha técnica.</span>
            </div>
        @endif

        @if (session('mostrar_popup_matricula'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Formulario enviado!',
                        html: 'Tu formulario fue llenado con éxito.<br>El área de cobranzas verificará tu pago para poder enviarte tu ficha de matrícula.',
                        confirmButtonColor: '#4e73df',
                        confirmButtonText: 'Entendido',
                    });
                });
            </script>
        @endif

        <div class="row" id="contenido-alumno">
            @if (auth()->user()->alumnoB)
                <div class="col-lg-12">
                    <!-- Tarjeta de información principal -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <tbody>
                                        <!-- Encabezado de sección con gradiente -->
                                        <tr class="ppd-section-band">
                                            <td colspan="4" class="font-weight-bold py-3">
                                                <i class="fa fa-graduation-cap mr-2"></i>Información del Programa
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold bg-light" width="20%">Programa:</td>
                                            <td colspan="3">
                                                <span class="badge badge-primary badge-pill px-3 py-2">
                                                    {{ $alumno->user->programa->nombre }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold bg-light align-middle">Cursos del Programa:</td>
                                            <td colspan="3" class="p-3">
                                                @php
                                                    $ciclosOrdenados = $cursos
                                                        ->pluck('ciclo')
                                                        ->filter()
                                                        ->unique('id')
                                                        ->sortBy(function ($ciclo) {
                                                            return (int) $ciclo->numero;
                                                        });

                                                    $cursosPorCiclo = $cursos->groupBy('ciclo_id');
                                                @endphp

                                                <!-- Lista de ciclos y cursos -->
                                                <div class="list-group list-group-flush">
                                                    @foreach ($ciclosOrdenados as $ciclo)
                                                        @if ($cursosPorCiclo->has($ciclo->id))
                                                            <div class="list-group-item font-weight-bold py-3 mt-2 rounded border-left border-primary border-width-4"
                                                                style="background: linear-gradient(to right, #f8f9fa, #ffffff);">
                                                                <div class="d-flex align-items-center">
                                                                    <span
                                                                        class="badge badge-primary mr-3 px-3 py-2">{{ $ciclo->numero }}</span>
                                                                    <span class="text-dark">{{ $ciclo->nombre }}</span>
                                                                    <span
                                                                        class="badge badge-light ml-auto px-3">{{ $cursosPorCiclo[$ciclo->id]->count() }}
                                                                        cursos</span>
                                                                </div>
                                                            </div>

                                                            <!-- Cursos del Ciclo -->
                                                            @foreach ($cursosPorCiclo[$ciclo->id] as $curso)
                                                                <div class="list-group-item curso-item py-3 border-bottom"
                                                                    style="padding-left: 4rem !important;">
                                                                    <div
                                                                        class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                                                        <div class="flex-grow-1 mb-2 mb-md-0">
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="fa fa-circle text-primary mr-2"
                                                                                    style="font-size: 8px;"></i>
                                                                                <a href="{{ route('curso.show', $curso->id) }}"
                                                                                    class="font-weight-bold text-decoration-none text-dark hover-primary">
                                                                                    {{ $curso->nombre }}
                                                                                </a>
                                                                            </div>

                                                                            @if ($curso->docentes->count())
                                                                                <small class="text-muted d-block mt-1 ml-3">
                                                                                    <i
                                                                                        class="fa fa-user mr-1 text-info"></i>
                                                                                    <span
                                                                                        class="font-weight-bold">Docente(s):</span>
                                                                                    <span
                                                                                        class="text-primary">{{ $curso->docentes->pluck('nombre')->join(', ') }}</span>
                                                                                </small>
                                                                            @else
                                                                                <small class="text-muted d-block mt-1 ml-3">
                                                                                    <i
                                                                                        class="fa fa-user mr-1 text-warning"></i>
                                                                                    <span
                                                                                        class="font-weight-bold">Docente(s):</span>
                                                                                    <span
                                                                                        class="text-warning font-weight-bold">No
                                                                                        asignado</span>
                                                                                </small>
                                                                            @endif
                                                                        </div>

                                                                        <div class="d-flex align-items-center ml-md-3">
                                                                            @php
                                                                                $periodoActualSilaboV = \App\Models\PeriodoActual::where('actual', true)->first();
                                                                                $silaboEstructuradoV = $curso->silabos->firstWhere('periodo_actual_id', $periodoActualSilaboV->id ?? null)
                                                                                    ?? $curso->silabos->firstWhere('periodo', $periodoActualSilaboV->nombre ?? null);
                                                                                $silaboPdfV = $curso->silabosPdf->where('periodo_actual_id', $periodoActualSilaboV->id ?? null)->first();
                                                                            @endphp
                                                                            @if ($silaboEstructuradoV)
                                                                                <a class="btn btn-outline-success btn-sm rounded-pill px-3 d-inline-flex align-items-center"
                                                                                    href="{{ route('silabo.pdf', $silaboEstructuradoV->id) }}"
                                                                                    target="_blank" title="Ver Sílabo">
                                                                                    <i class="fa fa-file-pdf-o mr-2"></i>
                                                                                    Sílabo
                                                                                </a>
                                                                            @elseif ($silaboPdfV)
                                                                                <a class="btn btn-outline-success btn-sm rounded-pill px-3 d-inline-flex align-items-center"
                                                                                    href="{{ asset('docentes/silabo/' . $silaboPdfV->pdf) }}"
                                                                                    target="_blank" title="Ver Sílabo">
                                                                                    <i class="fa fa-file-pdf-o mr-2"></i>
                                                                                    Sílabo
                                                                                </a>
                                                                            @elseif ($curso->silabo)
                                                                                <a class="btn btn-outline-success btn-sm rounded-pill px-3 d-inline-flex align-items-center"
                                                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                                                    target="_blank" title="Ver Sílabo">
                                                                                    <i class="fa fa-file-pdf-o mr-2"></i>
                                                                                    Sílabo
                                                                                </a>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    @endforeach

                                                    <!-- Cursos sin ciclo asignado -->
                                                    @php
                                                        $cursosSinCiclo = $cursos->filter(function ($curso) {
                                                            return !$curso->ciclo;
                                                        });
                                                    @endphp

                                                    @if ($cursosSinCiclo->count() > 0)
                                                        <div class="list-group-item font-weight-bold py-3 mt-3 rounded border-left border-secondary border-width-4"
                                                            style="background: linear-gradient(to right, #f8f9fa, #ffffff);">
                                                            <div class="d-flex align-items-center">
                                                                <i
                                                                    class="fa fa-exclamation-triangle text-warning mr-3 fa-lg"></i>
                                                                <span>CURSOS SIN CICLO ASIGNADO</span>
                                                                <span
                                                                    class="badge badge-secondary ml-auto px-3">{{ $cursosSinCiclo->count() }}
                                                                    cursos</span>
                                                            </div>
                                                        </div>

                                                        @foreach ($cursosSinCiclo as $curso)
                                                            <div class="list-group-item curso-item py-3 border-bottom"
                                                                style="padding-left: 4rem !important;">
                                                                <div
                                                                    class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                                                    <div class="flex-grow-1 mb-2 mb-md-0">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="fa fa-circle text-secondary mr-2"
                                                                                style="font-size: 8px;"></i>
                                                                            <a href="{{ route('curso.show', $curso->id) }}"
                                                                                class="font-weight-bold text-decoration-none text-dark hover-primary">
                                                                                {{ $curso->nombre }}
                                                                            </a>
                                                                        </div>

                                                                        @if ($curso->docentes->count())
                                                                            <small class="text-muted d-block mt-1 ml-3">
                                                                                <i class="fa fa-user mr-1 text-info"></i>
                                                                                <span
                                                                                    class="font-weight-bold">Docente(s):</span>
                                                                                <span
                                                                                    class="text-primary">{{ $curso->docentes->pluck('nombre')->join(', ') }}</span>
                                                                            </small>
                                                                        @else
                                                                            <small class="text-muted d-block mt-1 ml-3">
                                                                                <i class="fa fa-user mr-1 text-warning"></i>
                                                                                <span
                                                                                    class="font-weight-bold">Docente(s):</span>
                                                                                <span
                                                                                    class="text-warning font-weight-bold">No
                                                                                    asignado</span>
                                                                            </small>
                                                                        @endif
                                                                    </div>

                                                                    <div class="d-flex align-items-center ml-md-3">
                                                                        @php
                                                                            $periodoActualSilaboV = \App\Models\PeriodoActual::where('actual', true)->first();
                                                                            $silaboEstructuradoV = $curso->silabos->firstWhere('periodo_actual_id', $periodoActualSilaboV->id ?? null)
                                                                                ?? $curso->silabos->firstWhere('periodo', $periodoActualSilaboV->nombre ?? null);
                                                                            $silaboPdfV = $curso->silabosPdf->where('periodo_actual_id', $periodoActualSilaboV->id ?? null)->first();
                                                                        @endphp
                                                                        @if ($silaboEstructuradoV)
                                                                            <a class="btn btn-outline-success btn-sm rounded-pill px-3 d-inline-flex align-items-center"
                                                                                href="{{ route('silabo.pdf', $silaboEstructuradoV->id) }}"
                                                                                target="_blank" title="Ver Sílabo">
                                                                                <i class="fa fa-file-pdf-o mr-2"></i>
                                                                                Sílabo
                                                                            </a>
                                                                        @elseif ($silaboPdfV)
                                                                            <a class="btn btn-outline-success btn-sm rounded-pill px-3 d-inline-flex align-items-center"
                                                                                href="{{ asset('docentes/silabo/' . $silaboPdfV->pdf) }}"
                                                                                target="_blank" title="Ver Sílabo">
                                                                                <i class="fa fa-file-pdf-o mr-2"></i>
                                                                                Sílabo
                                                                            </a>
                                                                        @elseif ($curso->silabo)
                                                                            <a class="btn btn-outline-success btn-sm rounded-pill px-3 d-inline-flex align-items-center"
                                                                                href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                                                target="_blank" title="Ver Sílabo">
                                                                                <i class="fa fa-file-pdf-o mr-2"></i>
                                                                                Sílabo
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Cursos Pendientes (si existen) -->
                                        @if (isset($alumno->user->pendiente) && !empty($alumno->user->pendiente))
                                            <tr class="bg-warning bg-gradient">
                                                <td colspan="4" class="font-weight-bold text-white py-3">
                                                    <i class="fa fa-exclamation-triangle mr-2"></i>Cursos Pendientes
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="p-3">
                                                    <small class="text-muted d-block mb-2">
                                                        <i class="fa fa-info-circle mr-1"></i>
                                                        *Es responsabilidad del estudiante solicitar la subsanación de
                                                        cursos pendientes.
                                                    </small>
                                                    <div class="row">
                                                        @php
                                                            $cursosPendientes = explode(',', $alumno->user->pendiente);
                                                        @endphp
                                                        @foreach ($cursosPendientes as $curso)
                                                            <div class="col-md-6 mb-2">
                                                                <div
                                                                    class="d-flex align-items-center p-2 bg-light rounded">
                                                                    <i class="fa fa-clock-o text-warning mr-2"></i>
                                                                    <span>{{ trim($curso) }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        
                                        <tr class="ppd-section-band">
                                            <td colspan="4" class="font-weight-bold py-3">
                                                <i class="fa fa-user mr-2"></i>Datos Personales
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold bg-light" width="20%">Nombre Completo:</td>
                                            <td width="30%">{{ $alumno->nombres }} {{ $alumno->apellidos }}</td>
                                            <td class="font-weight-bold bg-light" width="20%">DNI:</td>
                                            <td width="30%">{{ $alumno->dni }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold bg-light">Correo:</td>
                                            <td>{{ $alumno->email }}</td>
                                            <td class="font-weight-bold bg-light">Número:</td>
                                            <td>{{ $alumno->numero }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold bg-light">Núm. Referencia:</td>
                                            <td>{{ $alumno->numero_referencia }}</td>
                                            <td class="font-weight-bold bg-light">Domicilio:</td>
                                            <td>{{ $alumno->departamento }} - {{ $alumno->provincia }} -
                                                {{ $alumno->distrito }}, {{ $alumno->direccion }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-12">
                    <div class="alert text-center py-4 shadow-sm" role="alert">
                        <i class="fa fa-edit fa-3x mb-3 d-block"></i>
                        <h5 class="mb-3">Complete su formulario de registro de matrícula</h5>
                        <a href="{{ route('formPPD') }}" class="btn btn-primary px-4">
                            <i class="fa fa-arrow-right mr-2"></i>Ir al Formulario
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .curso-item {
            transition: all 0.3s ease;
        }

        .curso-item:hover {
            background-color: var(--ppd-accent-soft) !important;
            transform: translateX(5px);
        }

        .hover-primary:hover {
            color: var(--ppd-accent) !important;
        }

        .border-width-4 {
            border-width: 4px !important;
        }

        .bg-warning.bg-gradient {
            background: linear-gradient(45deg, #ffc107, #e0a800) !important;
        }

        .table td {
            vertical-align: middle;
        }

        .list-group-item {
            border: none;
            border-radius: 0 !important;
        }

        .badge {
            font-weight: 500;
        }

        .rounded-pill {
            border-radius: 50px !important;
        }
    </style>

@endsection
