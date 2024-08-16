@extends('layouts.bolsa')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 mt-3">
                Ficha técnica: <small class="text-primary"> {{ $postulante->nombre }} {{ $postulante->apellidos }} </small>
            </h4>
            <a href="javascript:history.go(-1)"
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                <i class="fa fa-arrow-left"></i> Volver 
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="col-lg-12 pb-3 border-bottom d-flex justify-content-center align-items-center pb-2">
                <div style="height: 180px; width:160px; border:1px solid #80808036">
                    <img src="{{ asset($postulante->img) }}" style="object-fit: cover; width:100%; height:100%">
                </div>
            </div>

            <div class="col-lg-4 pt-3 border-bottom border-left border-right hoverPostulante">
                <label class="font-weight-bold">Nombre completo:</label>
                <p>{{ $postulante->nombre }} {{ $postulante->apellidos }}</p>
            </div>
            <div class="col-lg-4 pt-3 border-bottom border-right hoverPostulante">
                <label class="font-weight-bold">DNI:</label>
                <p>{{ $postulante->dni }}</p>
            </div>
            <div class="col-lg-4 pt-3 border-bottom border-left border-right hoverPostulante">
                <label class="font-weight-bold">Email:</label>
                <p>{{ $postulante->email }}</p>
            </div>
            <div class="col-lg-3 pt-3 border-bottom border-right hoverPostulante">
                <label class="font-weight-bold">Edad:</label>
                <p>{{ $postulante->edad }}</p>
            </div>
            
            <div class="col-lg-3 pt-3 border-bottom border-left border-right hoverPostulante">
                <label class="font-weight-bold">Idioma(s):</label>
                <p>{{ $postulante->idioma }}</p>
            </div>
            <div class="col-lg-3 pt-3 border-bottom border-left border-right hoverPostulante">
                <label class="font-weight-bold">Teléfono:</label>
                <p>{{ $postulante->numero }}</p>
            </div>

            <div class="col-lg-3 pt-3 border-bottom  border-right hoverPostulante">
                <label class="font-weight-bold">Carrera:</label>
                <p>{{ $postulante->programa->nombre }}</p>
            </div>
            <div class="col-lg-12 pt-3 border-bottom border-left border-right hoverPostulante">
                <label class="font-weight-bold">Otros estudios:</label>
                <p>{{ $postulante->otros_estudios }}</p>
            </div>
            <div class="col-lg-12 pt-3 border-bottom border-left border-right hoverPostulante">
                <label class="font-weight-bold">Descripción breve:</label>
                <p>{{ $postulante->descripcion }}</p>
            </div>
            @if ($postulante && $postulante->cv)
                <div class="col-lg-4 pt-3 pb-3 border-bottom border-left text-center hoverPostulante">
                    <a href="{{ asset($postulante->cv) }}" target="_blank" class="btn btn-sm btn-danger">
                        Ver CV &nbsp;<i class="fas fa-file-pdf fa-1x"></i>
                    </a>
                </div>
            @else
                <div class="col-lg-4 pt-3 pb-3 border-bottom t text-center hoverPostulante">
                    <span class="text-muted">CV no disponible</span>
                </div>
            @endif
            <div class="col-lg-4 pt-3 pb-3 border-bottom  text-center hoverPostulante">
                <a href="{{ $postulante->facebook }}" target="_blank" class="btn-primary btn-sm">
                    Ver perfil de FaceBook <i class="fa-brands fa-facebook-square"></i></a>
            </div>
            <div class="col-lg-4 pt-3 pb-3 border-bottom border-right text-center hoverPostulante">
                <a href="{{ $postulante->linkedin }}" target="_blank" class="btn-info btn-sm">
                    Ver perfil de LinkedIn <i class="fa-brands fa-linkedin"></i>
                </a>
            </div>
        </div>
    </div>
@endsection
