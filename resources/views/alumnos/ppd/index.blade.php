@extends('layouts.profesionalizacion')
@section('contenido')
    <div class="container-fluid pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-1"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="mb-2 text-primary font-weight-bold">Ficha Técnica: </h3>
        </div>
        <div class="row bg-white" id="contenido-alumno">
            @if (auth()->user()->alumnoB)
                <div class="col-lg-12">
                    <div class="p-2 table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td colspan="4" class="table-dark">Infromación del Programa</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Programa:</th>
                                    <td>
                                        {{ $alumno->programa->nombre }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Ciclo:</th>
                                    <td>
                                        @if ($alumno->ciclo)
                                            {{ $alumno->ciclo->nombre }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Cursos del semestre:</td>
                                    <td>
                                        @foreach ($alumno->ciclo->cursos as $curso)
                                            <li class="d-flex align-items-center justify-content-between curso-item"
                                                style="border-bottom: 1px dashed rgba(128, 128, 128, 0.526)">
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
                    {{-- <a class="btn btn-primary" href="{{ route('vistAlumno') }}">Por favor completa tu formulario</a> --}}
                    <a href="{{ route('formPPD') }}" class="btn btn-primary btn-sm">Por favor complete su formulario</a>
                </div>
            @endif
        </div>
    </div>
@endsection
