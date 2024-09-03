@extends('layouts.docente')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h5 class="font-weight-bold"><span class="text-primary">{{ $curso->nombre }} -
                    {{ $curso->ciclo->programa->nombre }} - {{ $curso->ciclo->nombre }}</span></h5>
            <a href="{{ url()->previous() }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Volver
            </a>
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
        <div class="row bg-white pb-5">
            <div class="col-lg-12 table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-dark text-center align-middle">
                        <tr>
                            <th>N°</th>
                            <th class="text-left">Datos</th>
                            <th class="bg-info" style="font-size: 14px">Competencia 2</th>
                            <th class="bg-info" style="font-size: 14px">Competencia 6</th>
                            <th class="bg-info" style="font-size: 14px">Competencia 9</th>
                            <th class="bg-success" style="font-size: 14px">Valoracion del Curso</th>
                            <th class="bg-success" style="font-size: 14px">Clf. del Curso</th>
                            <th class="bg-success" style="font-size: 14px">Clf. para el Sist. Superior</th>
                            <th>Calificar</th>
                        </tr>
                    </thead>
                    <tbody class="text-center align-middle">
                        @foreach ($alumnos as $index => $alumno)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <ul class="text-left">
                                        <li class="font-weight-bold">{{ $alumno->apellidos }},{{ $alumno->nombres }} </li>
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
                                <td>Num muestra: 4</td>
                                <td>Num muestra: 4</td>
                                <td>Num muestra: 4</td>
                                <td>
                                    <a href="" class="btn btn-sm btn-info">Guardar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
