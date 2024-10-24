@extends('layouts.alumno')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="mb-2 text-primary font-weight-bold">Ficha Técnica: </h3>
            @if (auth()->user()->alumno)
                @if (!Session::has('mostrar_contenido'))
                    <button type="button" class="btn btn-info btn-sm mb-2" id="mostrar-contenido">
                        Notificar que he terminado con mi registro. <i class="fa fa-smile"></i>
                    </button>
                @endif
                 <span>
                    <a href="{{ route('alumnos.edit', ['alumno' => $alumno->id]) }}" class="btn btn-sm btn-primary">Actualizar
                        Matricula
                    </a>
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
                {{-- @if (auth()->user()->alumno)
                    <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                        {{ Session::get('success') }} Botón de actualización de datos de ficha de matricula habilitado
                        hasta el 16 de agosto, de no tener datos para
                        actualizar solo llenar el <strong> Número de boleta electrónica emitida por la EES para el ciclo
                            2024 II y guardar el registro. De ser becado, solo guardar registro. </strong>
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif --}}
            </div>
            @if (auth()->user()->alumno)
                <div class="col-lg-12">
                    <div class="p-2 table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td colspan="4" class="table-dark">Carrera</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Programa:</th>
                                    <td>
                                        <ul>
                                            <li>
                                                {{ $alumno->programa->nombre }}
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Ciclo:</th>
                                    <td>
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
                                    <td>
                                        @foreach ($alumno->ciclo->cursos as $curso)
                                            <li class="d-flex align-items-center justify-content-between curso-item" style="border-bottom: 1px dashed rgba(128, 128, 128, 0.526)">
                                                <a href="{{ route('curso.show', $curso->id) }}"
                                                    class="mr-2">{{ $curso->nombre }}</a>
                                                <div class="d-flex align-items-center">
                                                    {{-- @if ($curso->docentes->count() > 0)
                                                        <div class="mr-1">
                                                            <ul><strong>Docentes:</strong>
                                                                @foreach ($curso->docentes as $docente)
                                                                    {{ $docente->nombre }}
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif --}}
                                                    @if ($curso->silabo)
                                                        <a class="btn btn-success btn-sm d-inline-block"
                                                            href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                            target="_blank" title="Ver Sílabo">
                                                            Ver sílabo <i class="fa fa-eye fa-sm"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </td>
                                </tr>

                                @if (isset($alumno->user->pendiente))
                                    <tr class="bg-danger text-white">
                                        <td>Curso(s) a cargo: <br> <small>*Es responsabilidad del estudiante solicitar la
                                                subsanación de cursos pendientes.</small></td>
                                        <td>
                                            @php
                                                $cursos = explode(',', $alumno->user->pendiente);
                                            @endphp
                                            @foreach ($cursos as $curso)
                                                <li>{{ trim($curso) }}</li>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="table-dark">Datos Personales</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Nombre Completo:</th>
                                    <td>{{ $alumno->nombres }} {{ $alumno->apellidos }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">DNI:</th>
                                    <td>{{ $alumno->dni }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Correo:</th>
                                    <td>{{ $alumno->email }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Número:</th>
                                    <td>{{ $alumno->numero }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Número de referencia:</th>
                                    <td>{{ $alumno->numero_referencia }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Domicilio:</th>
                                    <td>{{ $alumno->direccion }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="col-lg-12">
                    <a class="btn btn-primary" href="{{ route('vistAlumno') }}">Por favor completa tu formulario</a>
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
            var button = $(e.relatedTarget); // Botón que activó el modal
            var url = button.data('href'); // Obtiene la URL del atributo data-href
            var modal = $(this);
            modal.find('#confirm-delete').attr('href', url); // Configura el enlace de eliminación
        });
    </script>

    {{-- Script para mostrar contenido de alumnos --}}
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
