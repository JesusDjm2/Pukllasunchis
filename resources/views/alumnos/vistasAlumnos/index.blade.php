@extends('layouts.alumno')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="mb-2 text-primary font-weight-bold">Ficha Técnica: </h3>
            @php
                $alumno = auth()->user()->alumno ?? auth()->user()->alumnoB;
            @endphp

            @if ($alumno)
                @if (!Session::has('mostrar_contenido'))
                    <button type="button" class="btn btn-info btn-sm mb-2" id="mostrar-contenido">
                        Notificar que he terminado con mi registro de matrícula. <i class="fa fa-smile"></i>
                    </button>
                @endif
                <span>
                    <a href="{{ route('alumnos.edit', ['alumno' => $alumno->id]) }}" class="btn mb-2 btn-sm btn-primary">
                        Completar Matrícula
                    </a>
                </span>
            @endif
            @if (auth()->user()->alumno)
                <span>
                    <a class="btn btn-sm btn-info mb-2" href="{{ route('ficha-matricula', ['alumno' => $alumno->id]) }}">Ficha de
                        matricula</a>
                </span>
            @endif
        </div>
        <div class="row bg-white" id="contenido-alumno">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>
            @if (auth()->user()->alumno)
                <div class="col-lg-12">
                    <div class="p-2 table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td colspan="3" class="table-dark font-weight-bold">Datos Personales</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Nombre Completo:</td>
                                    <td>{{ $alumno->nombres }} {{ $alumno->apellidos }}</td>
                                    <!-- Celda de imagen solo a partir de aquí -->
                                    <td rowspan="6" colspan="2" class="text-center align-middle">
                                        @if ($alumno->user && $alumno->user->foto)
                                            <img src="{{ asset('img/estudiantes/' . $alumno->user->foto) }}"
                                                alt="Foto de {{ $alumno->nombres }}" class="img-thumbnail"
                                                style="width: 250px; height: auto; object-fit: cover;">
                                        @else
                                            <div class="d-flex flex-column align-items-center mx-auto justify-content-center"
                                                style="width: 150px; height: 150px; border: 1px solid #ccc; border-radius: 0.5rem;">
                                                <i class="fa fa-user fa-5x text-muted"></i>
                                                <small class="text-muted mt-2">Sin foto</small>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">DNI:</td>
                                    <td colspan="1">{{ $alumno->dni }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Correo:</td>
                                    <td>{{ $alumno->email }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Número:</td>
                                    <td>{{ $alumno->numero }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Número de referencia:</td>
                                    <td>{{ $alumno->numero_referencia }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Domicilio:</td>
                                    <td>{{ $alumno->departamento }} - {{ $alumno->provincia }} - {{ $alumno->distrito }}, {{ $alumno->direccion }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="table-dark font-weight-bold">Carrera</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Programa:</th>
                                    <td colspan="2">
                                        <ul>
                                            <li>
                                                {{ $alumno->programa->nombre }}
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Ciclo:</th>
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
                                                                <a href="{{ $sílaboURL }}"
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
                                                                <a href="{{ $sílaboURL }}"
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
                                </tr>

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
                    <a class="btn btn-primary btn-sm" href="{{ route('vistAlumno') }}">Por favor completa tu formulario</a>
                </div>
            @endif
        </div>
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


    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
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

    <style>
        .curso-item {
            transition: background-color 0.3s ease;
        }

        .curso-item:hover {
            background-color: #e7e7e7;
        }
    </style>
@endsection
