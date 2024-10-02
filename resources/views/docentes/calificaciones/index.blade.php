@extends('layouts.docente')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">Calificar Cursos:</strong></h4>
            
            <a href="{{ route('vistaDocente', $docente->id) }}"
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm float-right">
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
        <div class="row pb-5">
            <div class="col-lg-12 table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>N°</th>
                            <th>Detalles del Curso</th>
                            <th>Competencias</th>
                            <th>Competencias a calificar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $contador = 0;
                        @endphp
                        @foreach ($docente->cursos as $curso)
                            @php
                                $contador++;
                            @endphp
                            <tr>
                                <td>{{ $contador }}</td>
                                <td>
                                    <ul>
                                        <li class="font-weight-bold">{{ $curso->nombre }}</li>
                                        <ul>
                                            <li>
                                                {{ $curso->ciclo && $curso->ciclo->programa
                                                    ? (str_contains($curso->ciclo->programa->nombre, 'Inicial')
                                                        ? 'Prog. Inicial'
                                                        : (str_contains($curso->ciclo->programa->nombre, 'EIB')
                                                            ? 'Prog. EIB'
                                                            : $curso->ciclo->programa->nombre))
                                                    : 'Sin programa asignado' }}
                                                -
                                                {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                            </li>
                                            <li>Horas: {{ $curso->horas }}</li>
                                            <li>Créditos: {{ $curso->creditos }}</li>
                                        </ul>
                                    </ul>
                                </td>
                                <td>
                                    <form
                                        action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                                        <input type="hidden" name="curso_id" value="{{ $curso->id }}">

                                        <ul>
                                            @foreach ($curso->competencias as $competencia)
                                                <li>{{ $competencia->nombre }}</li>
                                                <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                                            @endforeach
                                        </ul>
                                        @if ($curso->competencias->count() <= 3)
                                            <button type="submit" class="btn btn-primary btn-sm float-right"
                                                id="calificar-btn-{{ $curso->id }}">
                                                Calificar
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('competencias.calificar', ['docente' => $docente->id, 'curso' => $curso->id]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="docente_id" value="{{ $docente->id }}">
                                        <input type="hidden" name="curso_id" value="{{ $curso->id }}">                                
                                        @if ($curso->competenciasSeleccionadas->isNotEmpty())
                                            <ul>
                                                @foreach ($curso->competenciasSeleccionadas as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                    <input type="hidden" name="competencias[]" value="{{ $competencia->id }}">
                                                @endforeach
                                            </ul>                                
                                            <button type="submit" class="btn btn-primary btn-sm float-right" id="calificar-btn-{{ $curso->id }}">
                                                Calificar
                                            </button>
                                        @endif
                                    </form>
                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
