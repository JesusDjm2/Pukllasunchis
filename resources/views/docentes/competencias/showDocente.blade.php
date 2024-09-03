@extends('layouts.docente')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h4 class="font-weight-bold text-primary">Detalles de la Competencia</h4>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title font-weight-bold">{{ $competencia->nombre }}</h4>
                <p class="card-text"><strong>Descripción:</strong><br> {{ $competencia->descripcion }}</p>
                <p class="card-text"><strong>Capacidades:</strong> {!! $competencia->capacidades !!}</p>
                {{-- <a href="{{ route('competencias.index') }}" class="btn btn-primary">Volver a la lista</a>
                <a href="{{ route('competencias.edit', $competencia->id) }}" class="btn btn-info">Editar</a> --}}
            </div>
        </div>
    </div>
@endsection
