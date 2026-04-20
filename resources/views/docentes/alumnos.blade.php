@extends('layouts.docente')

@section('titulo', 'Alumnos del curso')

@section('contenido')
    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Curso',
            'title' => $curso->nombre,
            'subtitle' => ($curso->ciclo->programa->nombre ?? '') . ' — ' . ($curso->ciclo->nombre ?? ''),
            'backUrl' => url()->previous() !== url()->current() ? url()->previous() : route('vistaDocente', ['docente' => $docente->id]),
            'backLabel' => 'Volver',
        ])

        <div class="row">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="card docente-ui-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm mb-0 docente-ui-table-wide text-center">
                        <thead class="thead-dark align-middle">
                            <tr>
                                <th>N°</th>
                                <th class="text-left">Datos</th>
                                <th class="bg-info">Comp. 2</th>
                                <th class="bg-info">Comp. 6</th>
                                <th class="bg-info">Comp. 9</th>
                                <th class="bg-success">Valoración</th>
                                <th class="bg-success">Clf. curso</th>
                                <th class="bg-success">Clf. sistema</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            @foreach ($alumnos as $index => $alumno)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <ul class="text-left small mb-0 pl-3">
                                            <li class="font-weight-bold">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</li>
                                            <li>{{ $alumno->email }}</li>
                                            <li>{{ $alumno->dni }}</li>
                                        </ul>
                                    </td>
                                    <td>
                                        <select name="estado" class="form-control form-control-sm">
                                            <option selected>Escoger</option>
                                            <option value="Previo al inicio">Previo al inicio</option>
                                            <option value="Inicio">Inicio</option>
                                            <option value="En Proceso">En Proceso</option>
                                            <option value="Logrado">Logrado</option>
                                            <option value="Destacado">Destacado</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="estado" class="form-control form-control-sm">
                                            <option selected>Escoger</option>
                                            <option value="Previo al inicio">Previo al inicio</option>
                                            <option value="Inicio">Inicio</option>
                                            <option value="En Proceso">En Proceso</option>
                                            <option value="Logrado">Logrado</option>
                                            <option value="Destacado">Destacado</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="estado" class="form-control form-control-sm">
                                            <option selected>Escoger</option>
                                            <option value="Previo al inicio">Previo al inicio</option>
                                            <option value="Inicio">Inicio</option>
                                            <option value="En Proceso">En Proceso</option>
                                            <option value="Logrado">Logrado</option>
                                            <option value="Destacado">Destacado</option>
                                        </select>
                                    </td>
                                    <td><span class="text-muted small">—</span></td>
                                    <td><span class="text-muted small">—</span></td>
                                    <td><span class="text-muted small">—</span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled>Guardar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
