@extends('layouts.alumno')
@section('titulo', 'Ficha técnica')
@section('contenido')
    @php
        $alumno = auth()->user()->alumno ?? auth()->user()->alumnoB;
        $headerActions = '';
        if (auth()->user()->alumno) {
            $headerActions =
                '<a class="btn btn-sm btn-info shadow-sm" href="' .
                e(route('ficha-matricula', ['alumno' => $alumno->id])) .
                '"><i class="fas fa-file-alt mr-1"></i> Ficha de matrícula (PDF)</a>';
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

    {{-- Script para enviar correo de notificación --}}
    <script>
        document.getElementById('mostrar-contenido')?.addEventListener('click', function() {
            this.style.display = 'none';
            fetch("{{ route('mostrar-contenido') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    @if (auth()->user()->alumno)
                        alumno_id: {{ $alumno->id }}
                    @endif
                }),
            }).then(response => {
                if (response.ok) {
                    alert('Correo enviado correctamente.');
                } else {
                    alert('Error al enviar el correo.');
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Error al enviar el correo.');
            });
        });
    </script>

@endsection
