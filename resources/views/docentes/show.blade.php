@extends('layouts.docente')
@section('contenido')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">Cursos asignados:</strong></h4>

            <div class="mb-4 text-center">
                <button class="btn btn-success btn-sm mx-2" onclick="mostrarTabla('fid')">Cursos FID</button>
                <button class="btn btn-primary btn-sm mx-2" onclick="mostrarTabla('ppd')">Cursos PPD</button>

            </div>
            <a href="{{ route('vistaDocente', $docente->id) }}"
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right">
                Volver
            </a>
        </div>

        <div class="row bg-white">
            <div class="col-lg-12">
                @php
                    $primerNombre = explode(' ', trim($docente->nombre))[0];
                @endphp
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }} 🎉
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->has('silabo'))
                    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                        <strong>¡Ups, {{ $primerNombre }}! 😅</strong> {{ $errors->first('silabo') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div class="row pb-5">
            <div class="col-lg-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Estimado/a docente,</strong>
                    Estamos mejorando nuestro panel de administración. Pronto tendrá acceso a nuevas y mejoradas
                    funcionalidades.
                    Gracias por su paciencia.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            @php
                $cursosPPD = $docente->cursos->filter(
                    fn($curso) => str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
                );
                $otrosCursos = $docente->cursos->filter(
                    fn($curso) => !str_contains($curso->ciclo->programa->nombre ?? '', 'PPD'),
                );
            @endphp
            <div class="col-lg-12 table-responsive mb-5" id="tablappd">
                <h4 class="font-weight-bold text-primary text-center" style="font-size: 20px">Cursos PPD</h4>
                @if ($cursosPPD->isEmpty())
                    <div class="text-center text-muted my-4">
                        <strong>No tiene cursos asignados de Profesionalización Docente</strong>
                    </div>
                @else
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
                            @foreach ($cursosPPD->values() as $index => $curso)
                                @php
                                    $contador++;
                                @endphp
                                <tr>
                                    <td>
                                        {{ $contador }}
                                    </td>
                                    <td>
                                        <ul>
                                            <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                            <ul>
                                                <li>
                                                    {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                    -
                                                    {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                                </li>
                                                <li>Horas: {{ $curso->horas }}</li>
                                                <li>Créditos: {{ $curso->creditos }}</li>
                                                <li>CC: {{ $curso->cc }}</li>
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
                                        @if (!str_contains($curso->cc, 'Extracurricular'))
                                            @if (!$curso->relacionsilabo && !$curso->silabo)
                                                <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                    class="btn btn-primary btn-sm mb-2">Crear Sílabo</a><br>

                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Seleccionar archivo
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm">Subir sílabo
                                                            en
                                                            PDF</button>
                                                    </div>
                                                    <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted"></div>
                                                </form>
                                            @elseif ($curso->relacionsilabo)
                                                <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                    class="btn btn-success btn-sm mb-2"><i class="fa fa-eye"></i> Ver
                                                    Sílabo</a>

                                                <a href="{{ route('silabos.edit', ['silabo' => $curso->relacionsilabo->id, 'curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                    class="btn btn-warning btn-sm mb-2"><i class="fa fa-edit"></i> Editar
                                                    Sílabo</a>
                                            @elseif ($curso->silabo)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}" target="_blank"
                                                    title="Ver Sílabo">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>
                                            @endif
                                        @else
                                            <span>Extracurricular permite sólo subir archivo en PDF.</span><br>

                                            @if ($curso->silabo)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                    target="_blank" title="Ver Sílabo">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>
                                            @else
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Seleccionar archivo
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Subir sílabo en PDF
                                                        </button>
                                                    </div>
                                                    <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted">
                                                    </div>
                                                </form>
                                            @endif
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

                                    <td>
                                        <form action="{{ route('cursos.classroomClaveCRUD', ['curso' => $curso->id]) }}"
                                            method="POST">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group mb-2">
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm" value="{{ $curso->classroom }}"
                                                    placeholder="Classroom Link">
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
                @endif
            </div>

            <div class="col-lg-12 table-responsive" id="tablafid" style="display: none;">
                <h4 class="font-weight-bold text-primary text-center" style="font-size: 20px">Cursos FID</h4>
                @if ($otrosCursos->isEmpty())
                    <div class="text-center text-muted my-4">
                        <strong>No tiene cursos asignados de FID</strong>
                    </div>
                @else
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
                            @foreach ($otrosCursos->values() as $index => $curso)
                                @php
                                    $contador++;
                                @endphp
                                <tr @if (str_contains($curso->ciclo->programa->nombre ?? '', 'PPD')) style="background-color: #e7f3ff" @endif>

                                    <td>
                                        {{ $contador }}
                                    </td>
                                    <td>
                                        <ul>
                                            <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                            <ul>
                                                <li>
                                                    {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                    -
                                                    {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                                </li>
                                                <li>Horas: {{ $curso->horas }}</li>
                                                <li>Créditos: {{ $curso->creditos }}</li>
                                                <li>CC: {{ $curso->cc }}</li>
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
                                        @if (!str_contains($curso->cc, 'Extracurricular'))
                                            @if (!$curso->relacionsilabo && !$curso->silabo)
                                                <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                    class="btn btn-primary btn-sm mb-2">Crear Sílabo</a><br>

                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Seleccionar archivo
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm">Subir sílabo
                                                            en
                                                            PDF</button>
                                                    </div>
                                                    <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted">
                                                    </div>
                                                </form>
                                            @elseif ($curso->relacionsilabo)
                                                <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                    class="btn btn-success btn-sm mb-2"><i class="fa fa-eye"></i> Ver
                                                    Sílabo</a>

                                                <a href="{{ route('silabos.edit', ['silabo' => $curso->relacionsilabo->id, 'curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                    class="btn btn-warning btn-sm mb-2"><i class="fa fa-edit"></i> Editar
                                                    Sílabo</a>
                                            @elseif ($curso->silabo)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                    target="_blank" title="Ver Sílabo">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>
                                            @endif
                                        @else
                                            <span>Extracurricular permite sólo subir archivo en PDF.</span><br>

                                            @if ($curso->silabo)
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                                <a class="btn btn-success btn-sm d-inline-block"
                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                    target="_blank" title="Ver Sílabo">
                                                    <i class="fa fa-eye fa-sm"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#confirmDeleteModal"
                                                    data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                    title="Eliminar sílabo">
                                                    <i class="fa fa-trash fa-sm"></i>
                                                </a>
                                            @else
                                                <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <div class="input-group mb-2">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            Seleccionar archivo
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Subir sílabo en PDF
                                                        </button>
                                                    </div>
                                                    <div id="file-name-{{ $curso->id }}" class="mt-2 text-muted">
                                                    </div>
                                                </form>
                                            @endif
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

                                    <td>
                                        <form action="{{ route('cursos.classroomClaveCRUD', ['curso' => $curso->id]) }}"
                                            method="POST">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group mb-2">
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm" value="{{ $curso->classroom }}"
                                                    placeholder="Classroom Link">
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
                @endif
            </div>
            <!-- Modal de Confirmación -->
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
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
            <script>
                function mostrarTabla(tipo) {
                    const tablaPPD = document.getElementById('tablappd');
                    const tablaFID = document.getElementById('tablafid');

                    if (tipo === 'ppd') {
                        tablaPPD.style.display = 'block';
                        tablaFID.style.display = 'none';
                    } else if (tipo === 'fid') {
                        tablaPPD.style.display = 'none';
                        tablaFID.style.display = 'block';
                    }
                }
            </script>
        @endsection
