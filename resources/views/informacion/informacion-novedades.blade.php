@extends('layouts.home')
@section('metas')
    @php $titulo = 'Novedades Pukllasunchis'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis</title>
    <meta name="description" content="Comunicados y novedades oficiales de la EESPP Pukllasunchis.">
    <meta name="keywords" content="{{ $titulo }}, comunicados, EESP Pukllasunchis">
@endsection

@section('contenido')
    <div class="bradcam_area novedades bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> Información / {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col-12">
                <h2 class="linea-debajo mb-4">{{ $titulo }}</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-9 order-2 order-lg-1">
                @if ($comunicados->isEmpty())
                    <div class="alert alert-info mb-0">
                        No hay comunicados publicados por el momento.
                    </div>
                @else
                    @foreach ($comunicados->groupBy('anio') as $anio => $comunicadosAnio)
                        <div class="mb-4">
                            <h4 class="linea-debajo mb-3">{{ $anio }}</h4>
                            @foreach ($comunicadosAnio->groupBy('mes') as $mesNum => $comunicadosMes)
                                <h6 class="text-primary font-weight-bold mb-3">
                                    {{ $mesesNombres[(int) $mesNum] ?? 'Mes '.$mesNum }}
                                </h6>
                                <div class="row novedadesCard">
                                    @foreach ($comunicadosMes as $comunicado)
                                        @php
                                            $assetArchivo = asset($comunicado->archivo);
                                            $modalId = 'comunicadoModal_'.$comunicado->id;
                                        @endphp
                                        <div class="col-lg-6 mb-3">
                                            <div class="card h-100">
                                                @if ($comunicado->esPdf())
                                                    <a href="{{ $assetArchivo }}" target="_blank" rel="noopener noreferrer">
                                                        <p class="text-center mt-3 mb-1">{{ $comunicado->titulo }}</p>
                                                        <p class="text-center mb-1"><i class="fa fa-calendar"></i>
                                                            {{ $comunicado->fecha_publicacion?->format('d/m/Y') }}</p>
                                                        <div class="d-flex flex-column align-items-center justify-content-center py-5 px-3"
                                                            style="min-height:220px;">
                                                            <i class="fas fa-file-pdf text-danger" style="font-size: 3.1rem;"></i>
                                                            <span class="btn btn-outline-danger btn-sm mt-3">Abrir PDF</span>
                                                        </div>
                                                    </a>
                                                @else
                                                    <a type="button" data-toggle="modal" data-target="#{{ $modalId }}">
                                                        <p class="text-center mt-3 mb-1">{{ $comunicado->titulo }}</p>
                                                        <p class="text-center mb-1"><i class="fa fa-calendar"></i>
                                                            {{ $comunicado->fecha_publicacion?->format('d/m/Y') }}</p>
                                                        <div class="overflow">
                                                            <img class="text-center img-fluid"
                                                                style="height: 100%; max-height: 290px; object-fit: cover;"
                                                                src="{{ $assetArchivo }}" alt="">
                                                        </div>
                                                    </a>
                                                @endif
                                                @if (!empty($comunicado->descripcion))
                                                    <p class="small text-muted px-3 pt-2 pb-3 mb-0">{{ $comunicado->descripcion }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="col-lg-3 order-1 order-lg-2 mb-4 mb-lg-0">
                <div class="pegajoso desktop">
                    <h3 class="linea-debajo">Filtrar comunicados</h3>
                    <form method="GET" action="{{ route('novedades') }}">
                        <div class="form-group">
                            <label for="filtro_anio" class="small font-weight-bold mb-1">Año</label>
                            <select name="anio" id="filtro_anio" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach ($anios as $anio)
                                    <option value="{{ $anio }}" {{ (string) request('anio') === (string) $anio ? 'selected' : '' }}>
                                        {{ $anio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary btn-block mb-2">Filtrar</button>
                        @if (request()->hasAny(['anio']))
                            <a href="{{ route('novedades') }}" class="btn btn-sm btn-outline-secondary btn-block">
                                Limpiar
                            </a>
                        @endif
                    </form>
                    <p class="small text-muted mt-3 mb-0">
                        Mostrando {{ $comunicados->count() }} comunicado(s).
                    </p>
                </div>
            </div>
        </div>
    </div>

    @foreach ($comunicados as $comunicado)
        @if ($comunicado->esImagen())
            <div class="modal fade" id="comunicadoModal_{{ $comunicado->id }}" tabindex="-1" role="dialog"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                            <img class="img-fluid" width="100%" src="{{ asset($comunicado->archivo) }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
