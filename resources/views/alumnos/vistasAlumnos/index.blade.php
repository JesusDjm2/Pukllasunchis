@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="mb-0 text-gray-800">Ficha Técnica: </h3>
            <span><a href="{{ route('alumnos.edit', ['alumno' => $alumno->id]) }}" class="btn btn-sm btn-primary">Actualizar
                    datos</a></span>
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
                                        @if ($alumno->ciclo)
                                            <ul>
                                                @foreach ($alumno->ciclo->cursos as $curso)
                                                    <li>
                                                        <a href="{{ route('curso.show', ['curso' => $curso->id]) }}">{{ $curso->nombre }}
                                                            <i class="fa fa-eye"></i></a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                                @if (isset($alumno->user->pendiente))
                                    <tr class="bg-danger text-white">
                                        <td class="font-weight-bold">Cursos pendientes: </td>
                                        <td>{{ $alumno->user->pendiente }}</td>
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
    @endsection
