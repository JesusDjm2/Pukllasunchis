@extends('layouts.profesionalizacion')
@section('contenido')
    <style>
        table thead tr {
            pointer-events: none;
        }
    </style>
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
                <div class="table-responsive p-2">
                    <table class="table table-bordered">
                        <thead class="text-white thead-dark">
                            <tr>
                                <th rowspan="2" class="text-center" style="width: 50px;">#</th>
                                <th rowspan="2" class="text-center">Curso</th>
                                <th class="text-center" colspan="3" style="width: 500px;">
                                    Promedios Generales por Competencia
                                </th>
                                <th rowspan="2" class="text-center" style="font-size: 12px;">Nivel de Desempeño</th>
                                <th rowspan="2" class="text-center" style="font-size: 12px">Calificación Curso</th>
                                <th rowspan="2" class="text-center" style="font-size: 12px">Calificación Sistema</th>
                                <th rowspan="2" class="text-center"
                                    style="font-size: 12px; border-right:1px solid #000000">
                                    Observaciones del Docente
                                </th>
                            </tr>
                        </thead>
                        <tbody style="color: #000">
                            @foreach ($cursos as $curso)
                                @php
                                    $calif = $curso->calificacionesppd->first();
                                    $competencias = $curso->competencias
                                        ->sortBy(function ($comp) {
                                            return intval(preg_replace('/\D/', '', $comp->nombre));
                                        })
                                        ->values();
                                @endphp
                                <tr>
                                    {{-- 🔢 Contador --}}
                                    <td rowspan="2" class="text-center"
                                        style="border-bottom: 1px solid #000; border-left: 1px solid #000;">
                                        {{ $loop->iteration }}.
                                    </td>

                                    {{-- 📘 Nombre del curso --}}
                                    <td rowspan="2" style="border-bottom: 1px solid #000; vertical-align: middle;" class="text-dark">
                                        <strong>{{ $curso->nombre }}</strong>
                                        <ul class="text-dark">
                                            <li>CC: {{ $curso->cc }}</li>
                                            <li>Créditos: {{ $curso->creditos }}</li>                                            
                                        </ul>
                                    </td>

                                    {{-- Competencias --}}
                                    @foreach ($competencias as $index => $competencia)
                                        @php
                                            preg_match('/\d+/', $competencia->nombre, $matches);
                                            $numero = $matches[0] ?? $index + 1;
                                        @endphp
                                        <td class="text-center" style="font-size: 11px;">
                                            <strong>Competencia {{ $numero }}</strong><br>
                                            <span>{{ $calif?->{'comp' . $numero} }}</span>
                                        </td>
                                    @endforeach

                                    {{-- Nivel / Calificaciones / Observaciones --}}
                                    <td rowspan="2" class="text-center"
                                        style="font-size: 14px; border-bottom: 1px solid #000; vertical-align: middle;">
                                        {{ $calif?->nivel_desempeno ?? 'Sin calificar' }}
                                    </td>
                                    <td rowspan="2" class="text-center"
                                        style="font-size: 15px; border-bottom: 1px solid #000; vertical-align: middle;">
                                        {{ $calif?->calificacion_curso ?? 'Sin calificar' }}
                                    </td>
                                    <td rowspan="2" class="text-center"
                                        style="font-size: 15px; border-bottom: 1px solid #000; vertical-align: middle;">
                                        {{ $calif?->calificacion_sistema ?? 'Sin calificar' }}
                                    </td>
                                    <td rowspan="2"
                                        style="font-size: 15px; border-bottom: 1px solid #000; vertical-align: middle; border-right:1px solid #000;">
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
                                                is_numeric($pp) && is_numeric($pf) ? round($pp * 0.4 + $pf * 0.6) : '-';
                                        @endphp
                                        <td style="font-size: 14px; border-bottom: 1px solid #000; text-align: center;">
                                           {{ $val }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection
