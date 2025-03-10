@extends('layouts.alumno')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="mb-0 text-uppercase text-primary font-weight-bold">Notas </h4>
            <span class="font-weight-bold">
                {{ $alumno->programa->nombre }} - {{ $alumno->ciclo->nombre }}
            </span>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>
        <div class="row mb-4">
            <div class="col-lg-12">
                @if ($alumno->ciclo->cursos->isNotEmpty())
                    <div class="p-2 table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr style="font-size: 14px" class="bg-secondary text-white">
                                    <td class="font-weight-bold align-middle align-middle">Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Periodo</td>
                                    <td class="font-weight-bold align-middle text-center">Valoración Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación Sistema</td>
                                </tr>
                            </thead>
                            {{-- <tbody>
                                @foreach ($alumno->ciclo->cursos as $curso)
                                    @php
                                        $periodoUno = $curso
                                            ->periodos()
                                            ->where('alumno_id', $alumno->id)
                                            ->first();
                                    @endphp
                                    <tr>
                                        <td class="align-middle bg-light">
                                            <a href="{{ route('curso.show', $curso->id) }}">{{ $curso->nombre }}</a>
                                        </td>
                                        @if ($periodoUno)
                                            <td class="text-center align-middle bg-success text-white">
                                                {{ $periodoUno->valoracion_curso }}</td>
                                            <td class="text-center align-middle bg-success text-white">
                                                {{ $periodoUno->calificacion_curso }}</td>
                                            <td class="text-center align-middle bg-success text-white">
                                                {{ $periodoUno->calificacion_sistema }}</td>
                                        @else
                                            <td colspan="6" class="text-center">No hay calificaciones disponibles</td>
                                        @endif     
                                                                    

                                    </tr>
                                @endforeach
                            </tbody> --}}
                            <tbody>
                                @foreach ($alumno->ciclo->cursos as $curso)
                                    @php
                                        // Encuentra el periodo asociado al curso actual
                                        $periodoUno = $curso
                                            ->periodos()
                                            ->where('alumno_id', $alumno->id)
                                            ->first();
                                        $periodoDos = $curso
                                            ->periododos()
                                            ->where('alumno_id', $alumno->id)
                                            ->first();
                                        $periodoTres = $curso
                                            ->periodotres()
                                            ->where('alumno_id', $alumno->id)
                                            ->first();
                                    @endphp
                                    <tr>
                                        <!-- Nombre del curso con rowspan -->
                                        <td rowspan="3" class="align-middle bg-light font-weight-bold"
                                            style="border-bottom: 1px solid #39779b; border-left: 1px solid #39779b">
                                            {{-- <a href="{{ route('curso.show', $curso->id) }}" class="font-weight-bold">{{ $curso->nombre }}</a> --}}
                                            {{ $curso->nombre }}
                                        </td>
                                        <td class="font-weight-bold text-primary">Parcial 1</td>

                                        <!-- Calificaciones del primer periodo -->
                                        @if ($periodoUno)
                                            <td class="text-center align-middle text-primary">
                                                {{ $periodoUno->valoracion_curso }}
                                            </td>
                                            <td class="text-center align-middle text-primary">
                                                {{ $periodoUno->calificacion_curso }}
                                            </td>
                                            <td class="text-center align-middle text-primary">
                                                {{ $periodoUno->calificacion_sistema }}
                                            </td>
                                        @else
                                            <td colspan="3" class="text-center">No hay calificaciones disponibles</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-info">Parcial 2</td>
                                        <!-- Calificaciones del segundo periodo -->
                                        @if ($periodoDos)
                                            <td class="text-center align-middle text-info">
                                                {{ $periodoDos->valoracion_curso }}
                                            </td>
                                            <td class="text-center align-middle text-info">
                                                {{ $periodoDos->calificacion_curso }}
                                            </td>
                                            <td class="text-center align-middle text-info">
                                                {{ $periodoDos->calificacion_sistema }}
                                            </td>
                                        @else
                                            <td colspan="3" class="text-center">No hay calificaciones disponibles</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #39779b;" class="text-success font-weight-bold">
                                            Promedio</td>
                                        @if ($periodoTres)
                                            <td style="border-bottom: 1px solid #39779b;"
                                                class="text-center align-middle text-success">
                                                {{ $periodoTres->valoracion_curso }}
                                            </td>
                                            <td style="border-bottom: 1px solid #39779b;"
                                                class="text-center align-middle text-success">
                                                {{ $periodoTres->calificacion_curso }}
                                            </td>
                                            <td style="border-bottom: 1px solid #39779b;"
                                                class="text-center align-middle text-success">
                                                {{ $periodoTres->calificacion_sistema }}
                                            </td>
                                        @else
                                            <td colspan="3" class="text-center"
                                                style="border-bottom: 1px solid #39779b;">No hay calificaciones disponibles
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                @else
                    <div class="alert alert-warning" role="alert">
                        No hay cursos asignados para este alumno.
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
