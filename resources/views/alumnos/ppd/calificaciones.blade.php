@extends('layouts.profesionalizacion')
@section('contenido')
    <style>
        table thead tr {
            pointer-events: none;
        }

        .califs-table {
            font-size: .82rem;
            width: 100% !important;
            table-layout: fixed;
        }

        .califs-table td {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .califs-table th {
            font-size: .68rem;
            letter-spacing: .03em;
            text-transform: uppercase;
            font-weight: 700;
            vertical-align: middle !important;
        }

        .califs-table td,
        .califs-table th {
            border-color: #e3e6f0;
        }

        .califs-table tbody tr:hover {
            background-color: var(--ppd-accent-soft);
        }

        .califs-curso {
            font-size: .9rem;
        }

        .califs-meta {
            font-size: .72rem;
            color: #858796;
        }

        .califs-promedio-row td {
            background-color: #fafbfe;
            color: #858796;
        }

        .califs-promedio-row td:first-child::before {
            content: 'Promedio del período:';
            font-size: .68rem;
            font-weight: 600;
            display: block;
        }
    </style>
    <div class="container-fluid bg-white">
        <div class="ppd-page-header">
            <div>
                <span class="ppd-eyebrow"><i class="fa fa-book mr-1"></i>Ficha Técnica</span>
                <h4 class="ppd-page-title">Calificaciones: {{ $alumno->programa->nombre }}</h4>
            </div>
            <a href="javascript:history.go(-1)" class="btn btn-sm btn-ppd-volver">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 califs-table">
                            <colgroup>
                                <col style="width: 3%;">
                                <col style="width: 18%;">
                                <col style="width: 9%;">
                                <col style="width: 9%;">
                                <col style="width: 9%;">
                                <col style="width: 10%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                                <col style="width: 26%;">
                            </colgroup>
                            <thead class="ppd-section-band">
                                <tr>
                                    <th rowspan="2" class="text-center">#</th>
                                    <th rowspan="2">Curso</th>
                                    <th class="text-center" colspan="3">
                                        Promedios Generales por Competencia
                                    </th>
                                    <th rowspan="2" class="text-center">Nivel de<br>Desempeño</th>
                                    <th rowspan="2" class="text-center">Calif.<br>Curso</th>
                                    <th rowspan="2" class="text-center">Calif.<br>Sistema</th>
                                    <th rowspan="2">Observaciones del Docente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $contador = 0; @endphp
                                @foreach ($ciclosConCursos as $ciclo)
                                    @if ($ciclo->cursos->count())
                                        <tr class="ppd-section-band">
                                            <td colspan="8" class="font-weight-bold py-2">
                                                <i class="fa fa-layer-group mr-1"></i>Ciclo {{ $ciclo->numeroRomano }}
                                            </td>
                                        </tr>
                                    @endif
                                    @foreach ($ciclo->cursos as $curso)
                                    @php
                                        $contador++;
                                        $calif = $curso->calificacionesppd->first();
                                        $competencias = $curso->competencias
                                            ->sortBy(function ($comp) {
                                                return intval(preg_replace('/\D/', '', $comp->nombre));
                                            })
                                            ->values();
                                        $nivel = $calif?->nivel_desempeno;
                                        $nivelBadge = [
                                            'Destacado' => 'badge-success',
                                            'Logrado' => 'badge-primary',
                                            'En proceso' => 'badge-warning',
                                            'En inicio' => 'badge-danger',
                                        ][$nivel] ?? 'badge-secondary';
                                    @endphp
                                    <tr>
                                        <td rowspan="2" class="text-center align-middle">
                                            {{ $contador }}
                                        </td>

                                        <td rowspan="2" class="align-middle">
                                            <div class="califs-curso font-weight-bold text-dark">{{ $curso->nombre }}</div>
                                            <div class="califs-meta">CC: {{ $curso->cc }} &middot; Créditos: {{ $curso->creditos }}</div>
                                        </td>

                                        {{-- Competencias --}}
                                        @foreach ($competencias as $index => $competencia)
                                            @php
                                                preg_match('/\d+/', $competencia->nombre, $matches);
                                                $numero = $matches[0] ?? $index + 1;
                                            @endphp
                                            <td class="text-center align-middle">
                                                <div class="califs-meta mb-1">Competencia {{ $numero }}</div>
                                                <span class="font-weight-bold">{{ $calif?->{'comp' . $numero} ?? '—' }}</span>
                                            </td>
                                        @endforeach

                                        <td rowspan="2" class="text-center align-middle">
                                            <span class="badge {{ $nivelBadge }} px-2 py-1">{{ $nivel ?? 'Sin calificar' }}</span>
                                        </td>
                                        <td rowspan="2" class="text-center align-middle font-weight-bold">
                                            {{ $calif?->calificacion_curso ?? '—' }}
                                        </td>
                                        <td rowspan="2" class="text-center align-middle font-weight-bold">
                                            {{ $calif?->calificacion_sistema ?? '—' }}
                                        </td>
                                        <td rowspan="2" class="align-middle">
                                            {!! nl2br(e($calif?->observaciones ?: '—')) !!}
                                        </td>
                                    </tr>
                                    <tr class="califs-promedio-row">
                                        @foreach ([1, 2, 3] as $c)
                                            @php
                                                $pp = $calif?->{"pp_c{$c}_4"};
                                                $pf = $calif?->{"pf_c{$c}_3"};
                                                $val =
                                                    is_numeric($pp) && is_numeric($pf) ? round($pp * 0.4 + $pf * 0.6) : '—';
                                            @endphp
                                            <td class="text-center align-middle">
                                               {{ $val }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
