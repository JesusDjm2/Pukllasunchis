@extends('layouts.alumno')
@section('titulo', 'Datos del docente')
@section('contenido')
    @include('partials.alumno-page-header', [
        'title' => 'Datos del docente',
        'subtitle' => 'Información de contacto y perfil.',
        'actions' =>
            '<a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="fas fa-arrow-left mr-1"></i> Volver</a>',
    ])

    <div class="row">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card alumno-detail-card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted small text-uppercase">Nombre</dt>
                        <dd class="col-sm-8 font-weight-bold">{{ $docente->nombre }}</dd>
                        <dt class="col-sm-4 text-muted small text-uppercase">Correo</dt>
                        <dd class="col-sm-8"><a href="mailto:{{ $docente->email }}">{{ $docente->email }}</a></dd>
                        <dt class="col-sm-4 text-muted small text-uppercase">Teléfono</dt>
                        <dd class="col-sm-8">{{ $docente->telefono ?: 'No registrado' }}</dd>
                        <dt class="col-sm-4 text-muted small text-uppercase">Descripción</dt>
                        <dd class="col-sm-8">{{ $docente->descripcion ?: 'Sin descripción.' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @if ($docente->imagen)
                <div class="card alumno-detail-card overflow-hidden">
                    <img src="{{ asset('storage/' . $docente->imagen) }}" class="card-img-top"
                        alt="Foto de {{ $docente->nombre }}" loading="lazy">
                </div>
            @else
                <div class="alumno-photo-placeholder mx-auto" style="max-width: 100%; aspect-ratio: 4/5;">
                    <i class="fas fa-user-tie fa-4x mb-3 opacity-40"></i>
                    <span class="small text-center px-3">Sin fotografía registrada</span>
                </div>
            @endif
        </div>
    </div>
@endsection
