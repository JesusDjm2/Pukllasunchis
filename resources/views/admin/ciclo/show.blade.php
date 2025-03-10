@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
            style="border-bottom: 1px dashed #80808078">
            <h5 class="mb-0 font-weight-bold text-uppercase" style="color: #4e73df; font-size:25px">
                {{ $ciclo->programa->nombre }} - {{ $ciclo->nombre }}</h5>
            <a href="{{ route('ciclo.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row bg-white">
            @if (str_contains($ciclo->nombre, 'Egresados'))
                <div class="col-lg-12 mt-0">
                    <h5 class="font-weight-bold">Alumnos: 42</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">#</th> <!-- Columna de índice -->
                                    <th scope="col">Nombres</th>
                                    <th scope="col">Programa</th>
                                    <th scope="col">Teléfono</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $alumno)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td> <!-- Número de fila -->
                                        <td>{{ $alumno->nombres }}, {{ $alumno->apellidos }}</td>
                                        <td>{{ $ciclo->programa->nombre }}</td>
                                        <td>{{ $alumno->numero }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="col-lg-12">
                    <h5 class="font-weight-bold">Cursos:</h5>
                </div>
                <div class="col-lg-12">
                    @if (Session::has('success'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ Session::get('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                </div>
                @php
                    $cursosOrdenados = $ciclo->cursos->sortBy('nombre');
                @endphp
                @foreach ($cursosOrdenados as $curso)
                    <div class="col-lg-4 mb-2 mt-2">
                        <div class="card" style="height: 100px">
                            <div class="card-body text-center">
                                <a href="{{ route('curso.show', $curso->id) }}">
                                    <h6>
                                        {{ $curso->nombre }}
                                        @if (str_contains($curso->cc, 'Extracurricular'))
                                            <small class="d-block mt-1 text-muted">(Extracurricular)</small>
                                        @endif
                                    </h6>
                                </a>
                                <a href="{{ route('curso.show', $curso->id) }}" class="btn btn-sm btn-info"
                                    title="Ver Curso">
                                    <i class="fa fa-eye fa-sm"></i> Ver Curso
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-lg-12 mt-4">
                    <h5 class="font-weight-bold">Alumnos:</h5>
                    <form action="{{ route('ciclo.updateAlumnos') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="ciclo_id" value="{{ $ciclo->id }}">
                        <div class="form-group">
                            <label for="nuevo_ciclo">Seleccionar Nuevo Ciclo:</label>
                            <select name="nuevo_ciclo_id" id="nuevo_ciclo" class="form-control" required>
                                @foreach ($ciclosDisponibles as $cicloDisponible)
                                    @if ($cicloDisponible->id != $ciclo->id)
                                        <option value="{{ $cicloDisponible->id }}">{{ $cicloDisponible->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="alumnos"><strong> Seleccionar Alumnos para cambiar de ciclo:</strong></label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">Seleccionar</th>
                                            <th scope="col">Nombre y Apellido</th>
                                            <th scope="col">DNI</th>
                                            <th scope="col">Pendiente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alumnos as $alumno)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="alumnos[]"
                                                            value="{{ $alumno->id }}" id="alumno{{ $alumno->id }}"
                                                            style="width: 15px;height: 15px; cursor: pointer;">
                                                    </div>
                                                </td>
                                                <td>
                                                    <label class="form-check-label" for="alumno{{ $alumno->id }}">
                                                        {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                    </label>
                                                </td>
                                                <td>{{ $alumno->dni }}</td>
                                                <td>
                                                    @if (isset($alumno->user->pendiente))
                                                        {{ $alumno->user->pendiente }}
                                                    @else
                                                        ---
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        @foreach ($alumnosB as $alumno)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="alumnos[]"
                                                            value="{{ $alumno->id }}" id="alumno{{ $alumno->id }}"
                                                            style="width: 15px;height: 15px; cursor: pointer;">
                                                    </div>
                                                </td>
                                                <td>
                                                    <label class="form-check-label" for="alumno{{ $alumno->id }}">
                                                        {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                    </label>
                                                </td>
                                                <td>{{ $alumno->dni }}</td>
                                                <td>
                                                    @if (isset($alumno->user->pendiente))
                                                        {{ $alumno->user->pendiente }}
                                                    @else
                                                        ---
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Cambiar Ciclo</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
