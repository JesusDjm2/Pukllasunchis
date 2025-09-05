@extends('layouts.profesionalizacion')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h5 class="mb-0 text-uppercase font-weight-bold">Detalles del curso</h5>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>
        <div class="row pb-4">
            <div class="col-lg-12">
                <table class="table table-hover">
                    <tr style="background: #80808030">
                        <td class="font-weight-bold">Nombre del curso:</td>
                        <td>{{ $curso->nombre }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Programa:</td>
                        <td>
                            {{ $curso->ciclo->programa->nombre }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Ciclo:</td>
                        <td>
                            {{ $curso->ciclo->nombre }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Docentes:</td>
                        <td>
                            @if ($docentes->count() > 0)
                                @foreach ($docentes as $docente)
                                    <li><a href="{{ route('docente.show', $docente->id) }}">{{ $docente->nombre }}</a></li>
                                @endforeach
                            @else
                                No asignado
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Sílabo:</td>
                        <td>
                            @if ($curso->silabo)
                                <a class="btn btn-success btn-sm d-inline-block"
                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}" target="_blank"
                                    title="Ver Sílabo">
                                    Ver sílabo <i class="fa fa-eye fa-sm"></i>
                                </a>
                            @else
                                No hay un sílabo asignado a este Curso
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Classroom:</td>
                        <td>
                            @if ($curso->classroom)
                                <a href="{{ $curso->classroom }}" target="_blank">Link <small><i
                                            class="fa fa-sm fa-external-link-alt"></i></small> </a> →
                                {{ $curso->clave }} <i class="fa fa-sm fa-lock"></i>
                            @else
                                No tiene asignado un Classroom
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Componente Curricular:</td>
                        <td>{{ $curso->cc }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Horas Semanales:</td>
                        <td>{{ $curso->horas }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Créditos:</td>
                        <td>{{ $curso->creditos }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Competencias:</td>
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
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Alumnos regissstrados:</td>
                        <td>{{ $cantidadAlumnos }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Alumnos:</td>
                        <td>
                            @foreach ($alumnos as $alumno)
                                {{ $loop->iteration }}. {{ $alumno->apellidos }}, {{ $alumno->nombres }}<br>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
