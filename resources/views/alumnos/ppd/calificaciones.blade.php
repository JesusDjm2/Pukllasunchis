@extends('layouts.profesionalizacion')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #c78d40">
            <h4 class="mb-0 text-primary font-weight-bold">Calificaciones: {{ $alumno->programa->nombre }} </h4>
            {{-- <span class="font-weight-bold">
                {{ $alumno->programa->nombre }}
            </span> --}}
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-lg-12">
                @foreach ($ciclosConCursos as $ciclo)
                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <h5 class="mb-0 mt-4 text-primary text-center"><strong>{{ $ciclo->nombre }}</strong></h5>
                            <div style="width: 10px; border-top: 4px solid #c78d40; margin: 0 auto; margin-bottom: 1em;">
                            </div>
                            @if ($ciclo->cursos->isNotEmpty())
                                <div class="table-responsive p-2">
                                    <table class="table table-bordered">
                                        <thead class="text-white"  style="background: #b47e37">
                                            <tr>
                                                <th rowspan="2" class="text-center">Curso</th>
                                                <th class="text-center" colspan="3" style="width: 500px;">Promedios Generales por Competencia
                                                </th>
                                                <th rowspan="2" class="text-center" style="font-size: 12px; ">Nivel de
                                                    Desempeño</th>
                                                <th rowspan="2" class="text-center" style="font-size: 12px">Calificación
                                                    Curso</th>
                                                <th rowspan="2" class="text-center" style="font-size: 12px">Calificación
                                                    Sistema</th>
                                                <th rowspan="2" class="text-center" style="font-size: 12px; border-right:1px solid #b47e37">Observaciones del Docente
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody style="color: #000">
                                            @foreach ($ciclo->cursos as $curso)
                                                @php
                                                    $calif = $curso->calificacionesppd->first();
                                                    $competencias = $curso->competencias
                                                        ->sortBy(function ($comp) {
                                                            return intval(preg_replace('/\D/', '', $comp->nombre));
                                                        })
                                                        ->values();
                                                @endphp
                                                <tr>
                                                    <td rowspan="2" style="border-bottom: 1px solid #b47e37; border-left: 1px solid #b47e37; vertical-align: middle;">
                                                        {{ $curso->nombre }}</td>
                                                    @foreach ($competencias as $index => $competencia)
                                                        @php
                                                            preg_match('/\d+/', $competencia->nombre, $matches);
                                                            $numero = $matches[0] ?? $index + 1;
                                                        @endphp
                                                        <td class="text-center" style="font-size: 12px;">
                                                            <strong>Comp. {{ $numero }}</strong><br>
                                                            <span>{{ $calif?->{'comp' . $numero} }}</span>
                                                        </td>
                                                    @endforeach

                                                    {{-- <td class="text-center" style="font-size: 12px;">
                                                        <strong>{{ $competencias[0]->nombre }}</strong><br>
                                                        <span>{{ $calif?->comp1 }}</span>
                                                    </td>
                                                    <td class="text-center" style="font-size: 12px">
                                                        <strong>{{ $competencias[1]->nombre }}</strong><br>
                                                        <span>{{ $calif?->comp2 }}</span>
                                                    </td>
                                                    <td class="text-center" style="font-size: 12px">
                                                        <strong>{{ $competencias[2]->nombre }}</strong><br>
                                                        <span>{{ $calif?->comp3 }}</span>
                                                    </td> --}}
                                                    <td rowspan="2" class="text-center"
                                                        style="font-size: 14px; border-bottom: 1px solid #b47e37; vertical-align: middle;">
                                                        {{ $calif?->nivel_desempeno ?? 'Sin calificar' }}
                                                    </td>
                                                    <td rowspan="2" class="text-center"
                                                        style="font-size: 15px; border-bottom: 1px solid #b47e37; vertical-align: middle;">
                                                        {{ $calif?->calificacion_curso ?? 'Sin calificar' }}
                                                    </td>
                                                    <td rowspan="2" class="text-center"
                                                        style="font-size: 15px; border-bottom: 1px solid #b47e37; vertical-align: middle;">
                                                        {{ $calif?->calificacion_sistema ?? 'Sin calificar' }}
                                                    </td>
                                                    <td rowspan="2"
                                                        style="font-size: 15px; border-bottom: 1px solid #b47e37; vertical-align: middle; border-right:1px solid #b47e37;">
                                                        {!! nl2br(e($calif?->observaciones ?? '-')) !!}
                                                    </td>

                                                </tr>
                                                <tr>
                                                    @foreach ([1, 2, 3] as $c)
                                                        @php
                                                            $pp = $calif?->{"pp_c{$c}_4"};
                                                            $pf = $calif?->{"pf_c{$c}_3"};
                                                            $pp_int = is_numeric($pp) ? round($pp) : null;
                                                            $pf_int = is_numeric($pf) ? round($pf) : null;
                                                            $val =
                                                                is_numeric($pp) && is_numeric($pf)
                                                                    ? round($pp * 0.4 + $pf * 0.6)
                                                                    : '-';
                                                        @endphp
                                                        <td
                                                            style="font-size: 11px; border-bottom: 1px solid #b47e37;">
                                                            <div
                                                                style="display: flex; justify-content: space-between; gap: 6px;">
                                                                <span style="border-right: 1px solid #8f8f8f54; padding-right: 0.6em; text-align: center;">
                                                                    <strong>40%</strong><br> {{ $pp_int ?? '-' }}
                                                                </span>
                                                                <span style="border-right: 1px solid #8f8f8f54; padding-right: 0.6em; text-align: center;">
                                                                    <strong>60%</strong><br> {{ $pf_int ?? '-' }}
                                                                </span>
                                                                <span style="text-align: center;">
                                                                    <strong>Prom.</strong><br> {{ $val }}
                                                                </span>
                                                            </div>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    Este ciclo no tiene cursos registrados.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
