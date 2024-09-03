@extends('layouts.docente')
@section('contenido')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">Cursos asignados:</strong></h4>
            {{-- <a href="{{ route('vistaDocente', $docente->id)}}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Volver 
            </a> --}}
        </div>
        <div class="row bg-white">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <div class="row pb-5">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Estimado/a docente,</strong>
                Estamos mejorando nuestro panel de administración. Pronto tendrá acceso a nuevas y mejoradas funcionalidades.

                Gracias por su paciencia.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="col-lg-12 table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>N°</th>
                            <th>Detalles del Curso</th>
                            <th>Competencias</th>
                            <th>Sílabo</th>
                            <th>ClassRoom</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $contador = 0;
                        @endphp
                        @foreach ($docente->cursos as $curso)
                            @php
                                $contador++;
                            @endphp
                            <tr>
                                <td>{{ $contador }}</td>
                                <td>
                                    <ul>
                                        <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                        <ul>
                                            <li>
                                                {{ $curso->ciclo && $curso->ciclo->programa
                                                    ? (str_contains($curso->ciclo->programa->nombre, 'Inicial')
                                                        ? 'Prog. Inicial'
                                                        : (str_contains($curso->ciclo->programa->nombre, 'EIB')
                                                            ? 'Prog. EIB'
                                                            : $curso->ciclo->programa->nombre))
                                                    : 'Sin programa asignado' }}
                                                -
                                                {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                            </li>
                                            <li>Horas: {{ $curso->horas }}</li>
                                            <li>Créditos: {{ $curso->creditos }}</li>
                                        </ul>
                                    </ul>
                                </td>
                                <td>
                                    @if ($curso->competencias->isEmpty())
                                        <span>No tiene asignada ninguna competencia.</span>
                                    @else
                                        @foreach ($curso->competencias as $competencia)
                                            <li>
                                                <a href="{{ route('competencias.show', $competencia->id) }}">
                                                    {{ $competencia->nombre }} </a>
                                            </li>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if ($curso->silabo)
                                        <!-- Formulario para actualizar el Sílabo -->
                                        <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                            method="POST" enctype="multipart/form-data" style="display:inline-block;">
                                            @csrf
                                            <div class="input-group mb-2">
                                                <!-- Botón personalizado para seleccionar el archivo -->
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                    Actualizar
                                                </button>
                                                <!-- Input de archivo oculto -->
                                                <input type="file" id="file-input-{{ $curso->id }}" name="silabo" accept=".pdf"
                                                    style="display: none;" aria-label="Subir sílabo" onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                <!-- Botón para enviar el formulario -->
                                                <button type="submit" class="btn btn-primary btn-sm" title="Editar">
                                                    <i class="fa fa-upload fa-sm"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <!-- Botón para ver el Sílabo -->
                                        <a class="btn btn-success btn-sm d-inline-block"
                                            href="{{ asset('docentes/silabo/' . $curso->silabo) }}" target="_blank"
                                            title="Ver Sílabo">
                                            <i class="fa fa-eye fa-sm"></i>
                                        </a>
                                
                                        <!-- Botón de Eliminar con Modal -->
                                        <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                            data-target="#confirmDeleteModal"
                                            data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                            title="Eliminar sílabo">
                                            <i class="fa fa-trash fa-sm"></i>
                                        </a>
                                    @else
                                        <!-- Formulario para subir el Sílabo -->
                                        <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                            method="POST" enctype="multipart/form-data" style="display:inline-block;">
                                            @csrf
                                            <div class="input-group mb-2">
                                                <!-- Botón personalizado para seleccionar el archivo -->
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                    Seleccionar archivo
                                                </button>
                                                <!-- Input de archivo oculto -->
                                                <input type="file" id="file-input-{{ $curso->id }}" name="silabo"
                                                    accept=".pdf" style="display: none;" aria-label="Subir sílabo" onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                <!-- Botón para enviar el formulario -->
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    Subir
                                                </button>
                                            </div>
                                            <!-- Contenedor para mostrar el nombre del archivo seleccionado -->
                                            <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted"></div>
                                        </form>
                                    @endif
                                </td>
                                <script>
                                    function updateFileName(input, elementId) {
                                        const fileInput = input;
                                        const fileNameDisplay = document.getElementById(elementId);
                                
                                        if (fileInput.files.length > 0) {
                                            fileNameDisplay.textContent = fileInput.files[0].name;
                                        } else {
                                            fileNameDisplay.textContent = '';
                                        }
                                    }
                                </script>
                                {{-- Ver lista de alumnos para calificar --}}
                                {{-- <td>                                    
                                    <a href="{{ route('docentes.cursos.alumnos', ['curso' => $curso->id, 'docente' => $docente->id]) }}"
                                        class="btn btn-sm btn-info">
                                        Ver<i class="fa fa-sm fa-eye"></i>
                                    </a>
                                </td> --}}
                                <td>
                                    <form action="{{ route('cursos.classroomClaveCRUD', ['curso' => $curso->id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('POST')
                                        <div class="input-group mb-2">
                                            <input type="text" name="classroom" class="form-control form-control-sm"
                                                value="{{ $curso->classroom }}" placeholder="Classroom Link">
                                        </div>
                                        <div class="input-group mb-2">
                                            <input type="text" name="clave" class="form-control form-control-sm"
                                                value="{{ $curso->clave }}" placeholder="Código Classroom">
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            @php
                                                $buttonText =
                                                    $curso->classroom || $curso->clave ? 'Actualizar' : 'Subir';
                                            @endphp
                                            <button type="submit"
                                                class="btn btn-primary btn-sm">{{ $buttonText }}</button>
                                            <button type="submit" class="btn btn-danger btn-sm" name="delete"
                                                value="true"
                                                onclick="return confirm('¿Estás seguro de eliminar estos campos?');">Eliminar</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
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
                    ¿Estás seguro de que deseas eliminar este Sílabo?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirm-delete" class="btn btn-danger">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#confirmDeleteModal').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget); // El botón que activó el modal
                var url = button.data('href'); // La URL a la que se debe enviar la solicitud DELETE
                var modal = $(this);
                modal.find('#confirm-delete').data('href',
                    url); // Configura la URL en el botón de confirmación
            });

            $('#confirm-delete').click(function() {
                var url = $(this).data('href'); // Obtener la URL configurada
                var form = $('<form>', {
                    'method': 'POST',
                    'action': url
                }).append($('<input>', {
                    'name': '_token',
                    'value': $('meta[name="csrf-token"]').attr('content'),
                    'type': 'hidden'
                })).append($('<input>', {
                    'name': '_method',
                    'value': 'DELETE',
                    'type': 'hidden'
                }));
                $('body').append(form);
                form.submit();
            });
        });
    </script>

@endsection
