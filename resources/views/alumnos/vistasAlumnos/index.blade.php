@extends('layouts.alumno')
@section('titulo', 'Ficha técnica')
@section('contenido')
    @php
        $alumno = auth()->user()->alumno ?? auth()->user()->alumnoB;
        $headerActions = '';
        if (auth()->user()->alumno) {
            $headerActions =
                '<a class="btn btn-sm btn-info shadow-sm mr-1" href="' .
                e(route('ficha-matricula', ['alumno' => $alumno->id])) .
                '"><i class="fas fa-file-alt mr-1"></i> Ficha de matrícula (PDF)</a>';

            if (isset($yaMatriculado) && $yaMatriculado) {
                $headerActions .=
                    '<span class="badge badge-success px-2 py-1 align-middle" style="font-size:12px;border-radius:6px;">' .
                    '<i class="fas fa-check-circle mr-1"></i>Matriculado · ' .
                    e(optional($periodoActual)->nombre) .
                    '</span>';
            } elseif (isset($periodoActual) && $periodoActual?->formulario_habilitado) {
                $headerActions .=
                    '<a class="btn btn-sm btn-warning shadow-sm" href="' .
                    e(route('alumnos.editarDatos')) .
                    '"><i class="fas fa-graduation-cap mr-1"></i> Completar ficha de matrícula</a>';
            }
        }
    @endphp
    @include('partials.alumno-page-header', [
        'title' => 'Ficha técnica',
        'subtitle' => 'Tus datos personales, programa, ciclo y cursos del semestre.',
        'actions' => $headerActions,
    ])

    <div class="row" id="contenido-alumno">
        <div class="col-12">
            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ Session::get('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
        {{-- Aviso de matrícula pendiente --}}
        @if (auth()->user()->alumno && isset($periodoActual) && $periodoActual && $periodoActual->formulario_habilitado && isset($yaMatriculado) && !$yaMatriculado)
            <div class="col-12 mb-3">
                <div class="alert alert-warning d-flex align-items-center shadow-sm mb-0 py-2">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>El período <strong>{{ $periodoActual->nombre }}</strong> está abierto.
                        Completa tu ficha de matrícula usando el botón del encabezado.</span>
                </div>
            </div>
        @endif

        @if (auth()->user()->alumno)
            <div class="col-lg-12">
                <div class="alumno-shell p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-bordered alumno-table-ficha mb-0">
                            <tbody>
                                <tr>
                                    <td colspan="3" class="alumno-th-section">Datos personales</td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label">Nombre completo</td>
                                    <td>{{ $alumno->nombres }} {{ $alumno->apellidos }}</td>
                                    <!-- Celda de imagen solo a partir de aquí -->
                                    <td rowspan="6" colspan="2" class="text-center align-middle bg-white">
                                        @if ($alumno->user && $alumno->user->foto)
                                            <div class="alumno-photo-wrap d-inline-block">
                                                <img src="{{ asset('img/estudiantes/' . $alumno->user->foto) }}"
                                                    alt="Foto de {{ $alumno->nombres }}" class="img-fluid"
                                                    style="max-width: 220px; height: auto; object-fit: cover;">
                                            </div>
                                        @else
                                            <div class="alumno-photo-placeholder mx-auto">
                                                <i class="fa fa-user fa-4x mb-2 opacity-50"></i>
                                                <small>Sin foto registrada</small>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label">DNI</td>
                                    <td colspan="1">{{ $alumno->dni }}</td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label">Correo</td>
                                    <td>{{ $alumno->email }}</td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label">Teléfono</td>
                                    <td>{{ $alumno->numero }}</td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label">Número de referencia</td>
                                    <td>{{ $alumno->numero_referencia }}</td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label">Domicilio</td>
                                    <td>{{ $alumno->departamento }} — {{ $alumno->provincia }} — {{ $alumno->distrito }},
                                        {{ $alumno->direccion }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="alumno-th-section">Programa y ciclo</td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label">Programa</td>
                                    <td colspan="2">
                                        <ul>
                                            <li>
                                                {{ $alumno->programa->nombre }}
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label">Ciclo</td>
                                    <td colspan="2">
                                        @if ($alumno->ciclo)
                                            <ul>
                                                <li style="font-family: 'Courier New', Courier, monospace; font-weight:600">
                                                    {{ $alumno->ciclo->nombre }}
                                                </li>
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="alumno-th-label align-top pt-3">Cursos del semestre</td>
                                    <td colspan="2">
                                        @if ($alumno->cursos->isNotEmpty())
                                            <ul class="alumno-curso-list">
                                            @foreach ($alumno->cursos as $curso)
                                                <li class="alumno-curso-item">
                                                    <div>
                                                        <a href="{{ route('curso.show', $curso->id) }}" class="mr-2">
                                                            {{ $curso->nombre }}
                                                        </a>
                                                        @if ($curso->ciclo_id != $alumno->ciclo_id)
                                                            <span class="badge badge-info">
                                                                Ciclo {{ $curso->ciclo->nombre }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        @if (!str_contains($curso->cc, 'Extracurricular'))
                                                            @if ($curso->relacionsilabo || $curso->silabo)
                                                                @php
                                                                    $silaboObj = $curso->relacionsilabo ?? null;
                                                                    $periodoSilabo = $silaboObj->periodo ?? null;
                                                                @endphp

                                                                @if ($periodoSilabo === $periodoActual->nombre)
                                                                    @php
                                                                        $sílaboURL = $curso->relacionsilabo
                                                                            ? route(
                                                                                'silabos.show',
                                                                                $curso->relacionsilabo->id,
                                                                            )
                                                                            : asset(
                                                                                'docentes/silabo/' . $curso->silabo,
                                                                            );
                                                                    @endphp
                                                                    <a href="{{ $sílaboURL }}" target="_blank"
                                                                        class="btn btn-success btn-sm mb-2">
                                                                        <i class="fa fa-eye"></i> Ver Sílabo
                                                                    </a>
                                                                @else
                                                                    <span>No hay sílabo disponible</span>
                                                                @endif
                                                            @else
                                                                <span>No hay sílabo</span>
                                                            @endif
                                                        @else
                                                            <span>No disponible</span>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                            </ul>
                                        @else
                                            <ul class="alumno-curso-list">
                                            @foreach ($alumno->ciclo->cursos as $curso)
                                                <li class="alumno-curso-item">
                                                    <div>
                                                        <a href="{{ route('curso.show', $curso->id) }}" class="mr-2">
                                                            {{ $curso->nombre }}
                                                        </a>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        @if (!str_contains($curso->cc, 'Extracurricular'))
                                                            @if ($curso->relacionsilabo || $curso->silabo)
                                                                @php
                                                                    $silaboObj = $curso->relacionsilabo ?? null;
                                                                    $periodoSilabo = $silaboObj->periodo ?? null;
                                                                @endphp

                                                                @if ($periodoSilabo === $periodoActual->nombre)
                                                                    @php
                                                                        $sílaboURL = $curso->relacionsilabo
                                                                            ? route(
                                                                                'silabos.show',
                                                                                $curso->relacionsilabo->id,
                                                                            )
                                                                            : asset(
                                                                                'docentes/silabo/' . $curso->silabo,
                                                                            );
                                                                    @endphp
                                                                    <a href="{{ $sílaboURL }}" target="_blank"
                                                                        class="btn btn-success btn-sm mb-2">
                                                                        <i class="fa fa-eye"></i> Ver Sílabo
                                                                    </a>
                                                                @else
                                                                    <span>No hay sílabo disponible</span>
                                                                @endif
                                                            @else
                                                                <span>No hay sílabo</span>
                                                            @endif
                                                        @else
                                                            <span>No disponible</span>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                                {{-- <tr>
                                    <td class="font-weight-bold">Cursos del semestre:</td>
                                    <td colspan="2">
                                        @if ($alumno->cursos->isNotEmpty())
                                            @foreach ($alumno->cursos as $curso)
                                                <li class="d-flex align-items-center justify-content-between curso-item"
                                                    style="border-bottom: 1px dashed rgba(128, 128, 128, 0.526)">
                                                    <div>
                                                        <a href="{{ route('curso.show', $curso->id) }}"
                                                            class="mr-2">{{ $curso->nombre }}</a>
                                                        @if ($curso->ciclo_id != $alumno->ciclo_id)
                                                            <span class="badge badge-info">Ciclo
                                                                {{ $curso->ciclo->nombre }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        @if (!str_contains($curso->cc, 'Extracurricular'))
                                                            @if ($curso->relacionsilabo || $curso->silabo)
                                                                @php
                                                                    $sílaboURL = $curso->relacionsilabo
                                                                        ? route(
                                                                            'silabos.show',
                                                                            $curso->relacionsilabo->id,
                                                                        )
                                                                        : asset('docentes/silabo/' . $curso->silabo);
                                                                @endphp
                                                                <a href="{{ $sílaboURL }}" target="_blank"
                                                                    class="btn btn-success btn-sm mb-2">
                                                                    <i class="fa fa-eye"></i> Ver Sílabo
                                                                </a>
                                                            @else
                                                                <span>No hay sílabo</span>
                                                            @endif
                                                        @else
                                                            <span>No disponible</span>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        @else
                                            @foreach ($alumno->ciclo->cursos as $curso)
                                                <li class="d-flex align-items-center justify-content-between curso-item"
                                                    style="border-bottom: 1px dashed rgba(128, 128, 128, 0.526)">
                                                    <div>
                                                        <a href="{{ route('curso.show', $curso->id) }}"
                                                            class="mr-2">{{ $curso->nombre }}</a>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        @if (!str_contains($curso->cc, 'Extracurricular'))
                                                            @if ($curso->relacionsilabo || $curso->silabo)
                                                                @php
                                                                    $sílaboURL = $curso->relacionsilabo
                                                                        ? route(
                                                                            'silabos.show',
                                                                            $curso->relacionsilabo->id,
                                                                        )
                                                                        : asset('docentes/silabo/' . $curso->silabo);
                                                                @endphp
                                                                <a href="{{ $sílaboURL }}" target="_blank"
                                                                    class="btn btn-success btn-sm mb-2">
                                                                    <i class="fa fa-eye"></i> Ver Sílabo
                                                                </a>
                                                            @else
                                                                <span>No hay sílabo</span>
                                                            @endif
                                                        @else
                                                            <span>No disponible</span>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr> --}}

                                @if (isset($alumno->user->pendiente))
                                    <tr class="bg-danger text-white">
                                        <td>Curso(s) a cargo: <br> <small>*Es responsabilidad del estudiante solicitar la
                                                subsanación de cursos pendientes.</small></td>
                                        <td colspan="3">
                                            @php
                                                $cursos = explode(',', $alumno->user->pendiente);
                                            @endphp
                                            @foreach ($cursos as $curso)
                                                <li>{{ trim($curso) }}</li>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="col-lg-12">
                    <div class="alumno-shell text-center py-5">
                        <p class="mb-3 text-muted">Aún no tienes ficha FID registrada en el sistema.</p>
                        <a class="btn btn-primary btn-lg shadow-sm" href="{{ route('vistAlumno') }}">
                            <i class="fas fa-edit mr-2"></i> Completar formulario de datos
                        </a>
                    </div>
                </div>
            @endif
        </div>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este administrador?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a id="confirm-delete" class="btn btn-danger" href="#">Eliminar</a>
                </div>
            </div>
        </div>
    </div>


    <script>
        $('#confirmDeleteModal').on('show.bs.modal', function(e) {
            var button = $(e.relatedTarget);
            var url = button.data('href');
            var modal = $(this);
            modal.find('#confirm-delete').attr('href', url);
        });
    </script>


@endsection
