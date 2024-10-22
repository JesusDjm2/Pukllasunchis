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
                                <tr>
                                    <td colspan="8" class="bg-dark text-white font-weight-bold text-center text-uppercase">
                                        @if ($periodoUno)
                                            {{ $periodoUno->nombre }}
                                        @else
                                            Aun no existe Periodo 1
                                        @endif
                                    </td>
                                </tr>
                                <tr style="font-size: 14px" class="bg-secondary text-white">
                                    <td class="font-weight-bold align-middle align-middle">Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Valoración Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación Curso</td>
                                    <td class="font-weight-bold align-middle text-center">Calificación Sistema</td>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Recorremos los cursos del ciclo --}}
                                @foreach ($alumno->ciclo->cursos as $curso)
                                    {{-- Obtenemos el periodoUno para cada curso --}}
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
                                        <td class="text-center align-middle bg-success text-white">{{ $periodoUno->valoracion_curso }}</td>
                                        <td class="text-center align-middle bg-success text-white">{{ $periodoUno->calificacion_curso }}</td>
                                        <td class="text-center align-middle bg-success text-white">{{ $periodoUno->calificacion_sistema }}</td>
                                        @else
                                            <td colspan="6" class="text-center">No hay calificaciones disponibles</td>
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
