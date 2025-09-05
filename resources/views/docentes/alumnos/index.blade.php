@extends('layouts.docente')
@section('contenido')
    <div class="container-fluid bg-light">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">Alumnos FID: alumnos por Curso </h4>
            <a href="{{ route('calificar', $docente->id) }}"
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right mb-3">
                Volver
            </a>
        </div>
        <div class="row bg-white">
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
        </div>
        <div class="row pb-5">
            <div class="col-lg-12 table-responsive">
                <div class="accordion" id="alumnosAccordion">
                    @foreach ($alumnosPorCurso as $curso => $alumnos)
                        <div class="card mb-3">
                            <div class="card-header bg-dark" id="heading{{ $loop->index }}">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-white" data-toggle="collapse"
                                        data-target="#collapse{{ $loop->index }}" aria-expanded="true"
                                        aria-controls="collapse{{ $loop->index }}">
                                        <span class="font-weight-bold"> {{ $docente->cursos->find($curso)->nombre }}</span>
                                        <small>
                                            @if ($alumnos->isNotEmpty())
                                                ({{ optional($alumnos->first()->ciclo->programa)->nombre ?? 'Sin programa' }}
                                                -
                                                {{ optional($alumnos->first()->ciclo)->nombre ?? 'Sin ciclo' }})
                                                - Alumnos: {{ count($alumnos) }}
                                            @else
                                                No hay alumnos en este curso.
                                            @endif
                                        </small>

                                    </button>
                                </h5>
                            </div>
                            <div id="collapse{{ $loop->index }}" class="collapse"
                                aria-labelledby="heading{{ $loop->index }}" data-parent="#alumnosAccordion">
                                @if ($alumnos->isEmpty())
                                    <p>No hay alumnos asignados a este curso.</p>
                                @else
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-secondary text-white">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Teléfono</th>
                                                <th>Foto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($alumnos as $alumno)
                                                <tr>
                                                    <td>
                                                        {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                        @php
                                                            $cicloAlumnoId = $alumno->ciclo_id;
                                                            $cicloCursoId = $docente->cursos->find($curso)?->ciclo_id;
                                                        @endphp

                                                        @if ($cicloAlumnoId && $cicloAlumnoId !== $cicloCursoId)
                                                            <span class="badge badge-info">
                                                                Ciclo
                                                                {{ optional($alumno->ciclo)->nombre ?? 'Desconocido' }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $alumno->email }}</td>
                                                    <td>{{ $alumno->numero ?? 'N/A' }}</td>
                                                    <td>
                                                        @if ($alumno->user && $alumno->user->foto)
                                                            <img src="{{ asset('img/estudiantes/' . $alumno->user->foto) }}"
                                                                alt="Foto de {{ $alumno->nombre }}" class="img-fluid"
                                                                style="width: 40px; height: 40px; border-radius: 50%; cursor: pointer;"
                                                                onclick="openModal('{{ asset('img/estudiantes/' . $alumno->user->foto) }}')"
                                                                oncontextmenu="return false;">
                                                        @else
                                                            <span class="text-muted">Sin foto</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @if (empty($alumnosPorCurso))
                    <p>No hay cursos de FID asignados a su perfil.</p>
                @endif
            </div>
        </div>
    </div>

    <div id="imageModal" class="modal" style="display:none;" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation();"
            style="display: flex; flex-direction: column; position: relative;">
            <span onclick="closeModal()" class="cerrarBtn">&times;</span>
            <img id="modalImage" style="width:100%; height:auto;" oncontextmenu="return false;">
        </div>
    </div>

    <!-- Estilos para el modal -->
    <style>
        .cerrarBtn {
            cursor: pointer;
            font-size: 22px;
            float: right;
            margin-left: auto;
            color: #fff;
            font-weight: bold;
            margin-right: -20px;
            padding-left: 8px;
            border-radius: 50%;
            padding-right: 8px;
            transition: 0.3s ease;
        }

        .cerrarBtn:hover {
            color: #000;
            background: #fff;
            border-radius: 50%;
            padding-left: 8px;
            padding-right: 8px;
            transition: 0.3s ease;
        }

        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
            /* Capa oscura */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.25);
            max-width: 200px;
            position: relative;
            background: none
        }

        .modal img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: auto;
            border: 5px solid rgb(255, 255, 255);
            border-radius: 5px
        }
    </style>

    <!-- Script para abrir y cerrar el modal -->
    <script>
        function openModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
    </script>
@endsection
