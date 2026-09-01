@extends('layouts.profesionalizacion')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="ppd-page-header">
            <div>
                <span class="ppd-eyebrow"><i class="fa fa-star mr-1"></i>Competencia</span>
                <h4 class="ppd-page-title">Detalles de la Competencia</h4>
            </div>
            <a href="javascript:history.go(-1)" class="btn btn-sm btn-ppd-volver">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title font-weight-bold">{{ $competencia->nombre }}</h4>
                <p class="card-text"><strong>Descripción:</strong><br> {{ $competencia->descripcion }}</p>
                <p class="card-text"><strong>Capacidades:</strong> {!! $competencia->capacidades !!}</p>                
            </div>
        </div>
    </div>
@endsection
