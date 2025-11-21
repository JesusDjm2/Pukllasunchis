@extends('layouts.admin')
@section('contenido')
    <style>
        .table-responsive {
            max-height: 92vh;
            overflow-y: auto;
        }
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #212529;
            color: #fff;
            z-index: 2;
        }
    </style>

    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="text-primary font-weight-bold">{{ $admision->nombre }}:</h3>
            @if ($postulantes->isNotEmpty())
                <div>
                    <a href="{{ route('postulantes.ingresantes') }}" class="btn btn-sm mb-2 btn-primary">
                        Crear ingresantes
                    </a>
                    <form action="{{ route('postulantes.exportar') }}" method="GET" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm mb-2">
                            Exportar CSV <i class="fa fa-file-csv"></i>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
            <div class="col-lg-12 mt-2">
                @php
                    $conteoProgramas = $postulantes->groupBy('programa')->map->count();
                    $totalSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca)->count();
                    $conteoBecas = $postulantes->filter(fn($postulante) => $postulante->estudio_beca)->count();
                    $postulantesSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca);
                    $conteoFiltrado = $postulantesSinBeca->groupBy('programa')->map->count();
                    $totalSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca)->count();

                @endphp
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Programa</th>
                            <th>Total Postulantes</th>
                            <th>Postulantes sin Beca</th>
                            <th>Becas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($conteoProgramas as $programa => $cantidad)
                            <tr>
                                <td>{{ $programa }}</td>
                                <td>{{ $cantidad }}</td>
                                <td>{{ $conteoFiltrado[$programa] ?? 0 }}</td>
                                <td>{{ $cantidad - ($conteoFiltrado[$programa] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total de registros</strong></td>
                            <td>{{ $postulantes->count() }}</td>
                            <td>{{ $totalSinBeca }}</td>
                            <td>{{ $conteoBecas }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>Postulantes</th>
                                <th>Adjuntos</th>
                                <th>Datos que faltan</th>
                                <th>Acciones</th>

                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($postulantes as $key => $postulante)
                                @php
                                    $atributos = $postulante->getAttributes();

                                    $excluir = [
                                        'id',
                                        'declaracion_jurada_salud_pdf',
                                        'declaracion_jurada-documentos_pdf',
                                        'declaracion_jurada_conectividad_pdf',
                                        'created_at',
                                        'updated_at',
                                        'admin_fids_id',
                                        'observaciones',
                                    ];

                                    $faltantes = collect($atributos)
                                        ->reject(function ($valor, $campo) use ($excluir) {
                                            return in_array($campo, $excluir) || !empty($valor);
                                        })
                                        ->keys()
                                        ->toArray();
                                @endphp
                                <tr style="{{ $postulante->estudio_beca ? 'background-color: #d6efe6 !important' : '' }}">
                                    <td>{{ $key + 1 }}</td>
                                    <td><strong>{{ $postulante->apellidos }}, {{ $postulante->nombres }}</strong>
                                        <ul>
                                            <li>DNI: {{ $postulante->dni }}
                                                @if ($postulante->edad)
                                                    <small class="text-muted">({{ $postulante->edad }} años)</small>
                                                @else
                                                    <span class="text-muted">(Sin fecha de nacimiento)</span>
                                                @endif
                                            </li>
                                            <li>Programa: {{ $postulante->programa }}</li>
                                            <li>Email: {{ $postulante->email }}</li>
                                            <li>Beca: @if ($postulante->estudio_beca)
                                                    Sí
                                                @else
                                                    No
                                                @endif
                                            </li>
                                            <li>Cómo se enteró:
                                                {{ !empty($postulante->contacto) ? $postulante->contacto : 'Sin datos para mostrar' }}
                                            </li>
                                            <li>Registro:
                                                {{ $postulante->created_at->timezone('America/Lima')->translatedFormat('l d \d\e F \d\e Y \a \l\a\s H:i') }}
                                                <small class="text-muted">({{ $postulante->created_at->diffForHumans() }})
                                                </small>
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul class="mb-0" style="list-style: none; padding-left: 0;">
                                            <li>
                                                @if ($postulante->voucher_pago)
                                                    <a href="{{ asset($postulante->voucher_pago) }}" target="_blank">Ver
                                                        voucher</a>
                                                @else
                                                    <em class="text-danger">*Sin voucher*</em>
                                                @endif
                                            </li>

                                            <li>
                                                @if ($postulante->foto)
                                                    <a href="{{ asset($postulante->foto) }}" target="_blank">
                                                        Ver foto
                                                    </a>
                                                @else
                                                    <em class="text-danger">*Sin foto*</em>
                                                @endif
                                            </li>
                                            <li>
                                                @if ($postulante->dni_pdf)
                                                    <a href="{{ asset($postulante->dni_pdf) }}" target="_blank">Ver DNI</a>
                                                @else
                                                    <em class="text-danger">*Sin DNI*</em>
                                                @endif
                                            </li>
                                            <li>
                                                @if ($postulante->partida_nacimiento_pdf)
                                                    <a href="{{ asset($postulante->partida_nacimiento_pdf) }}"
                                                        target="_blank">Ver Partida</a>
                                                @else
                                                    <em class="text-danger">*Sin Partida*</em>
                                                @endif
                                            </li>
                                            <li>
                                                @if ($postulante->certificado_secundaria_pdf)
                                                    <a href="{{ asset($postulante->certificado_secundaria_pdf) }}"
                                                        target="_blank">Ver Certificado</a>
                                                @else
                                                    <em class="text-danger">*Sin Certificado de Estudios*</em>
                                                @endif
                                            </li>
                                        </ul>
                                    </td>

                                    <td>
                                        @if (count($faltantes) > 0)
                                            <ul class="mt-2 mb-0 small text-danger">
                                                @foreach ($faltantes as $campo)
                                                    <li>{{ ucfirst(str_replace('_', ' ', $campo)) }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="badge bg-success">✅ Completo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('regulares.show', $postulante->id) }}"
                                            class="btn btn-sm btn-info mt-1">
                                            <i class="fa fa-eye"></i> Ver Detalles
                                        </a><br>
                                        <a href="{{ route('regulares.edit', $postulante->id) }}"
                                            class="btn btn-sm btn-warning mt-1">
                                            <i class="fa fa-edit"></i> Editar Registro
                                        </a><br>
                                        <a href="{{ route('regulares.enviarCorreo', $postulante->id) }}"
                                            class="btn btn-sm btn-primary mt-1">
                                            <i class="fa fa-envelope"></i> Enviar correo
                                        </a>
                                      
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay postulantes registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
