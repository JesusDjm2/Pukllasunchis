@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h5 class="mb-0 text-uppercase font-weight-bold">Detalles del curso</h5>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
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
                            @role('admin')
                                <a href="{{ route('programa.show', ['programa' => $curso->ciclo->programa->id]) }}">
                                    {{ $curso->ciclo->programa->nombre }} <small><i class="fa fa-eye"></i></small>
                                </a>
                            @endrole
                            @role('alumno')
                                {{ $curso->ciclo->programa->nombre }}
                            @endrole
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Ciclo:</td>
                        <td>
                            @role('admin')
                                <a href="{{ route('ciclo.show', ['ciclo' => $curso->ciclo->id]) }}">
                                    {{ $curso->ciclo->nombre }} <small><i class="fa fa-eye"></i></small></a>
                            @endrole
                            @role('alumno')
                                {{ $curso->ciclo->nombre }}
                            @endrole
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Docente:</td>
                        <td>
                            @if ($curso->docente)
                                {{ $curso->docente->nombre }}
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
                        <td class="font-weight-bold">Alumnos registrados:</td>
                        <td>{{ $cantidadAlumnos }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Alumnos:</td>
                        <td>
                            <ul>
                                @role('admin')
                                    @foreach ($alumnos as $alumno)
                                        <li>
                                            <a href="{{ route('alumnos.show', ['alumno' => $alumno->id]) }}">
                                                {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endrole
                                @role('alumno')
                                    @foreach ($alumnos as $alumno)
                                        <li>
                                            {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                        </li>
                                    @endforeach
                                @endrole
                            </ul>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="d-sm-flex align-items-center justify-content-between mb-2 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h5 class="mb-0 text-uppercase font-weight-bold">Competencias Modulares</h5>
        </div>
        <p class="mt-3 text-success">En proceso...</p>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 75%;" aria-valuenow="25" aria-valuemin="0"
                aria-valuemax="100">75%</div>
        </div>
    </div>
@endsection
