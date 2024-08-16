@extends('layouts.bolsa')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="row">
            <div class="col-12 mb-3">
                <h4 class="mt-3">
                    Ficha técnica bolsa de trabajo:                
                </h4>
                <p>Espacio para postular a la bolsa de trabajo Pukllasunchis, sube tu CV y datos personales para poder ser publicados en nuestra página web.</p>
                @if (auth()->user()->postulante)
                    <a href="{{ route('postulante.edit', ['postulante' => $user->postulante->id]) }}"
                        class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                        Editar datos <i class="fa fa-edit fa-sm"></i>
                    </a> 
                @endif
                @if (Session::has('success'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
            @if (auth()->user()->postulante)
                <div class="col-lg-12 pb-3 border-bottom d-flex justify-content-center align-items-center pb-2">
                    <div style="height: 180px; width:160px; border:1px solid #80808036">
                        <img src="{{ $user->postulante->img }}" style="object-fit: cover; width:100%; height:100%">
                    </div>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Nombre completo:</label>
                    <p>{{ $user->postulante->nombre }} {{ $user->postulante->apellidos }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Teléfono:</label>
                    <p>{{ $user->postulante->numero }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Email:</label>
                    <p>{{ $user->postulante->email }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Perfil:</label>
                    @if($user->perfil)
                        <p>{{ $user->perfil }}</p>
                    @else
                        <p>Sin perfil asignado</p>
                    @endif
                </div>
                
                <div class="col-lg-3 pb-3 pt-3 border-left border-bottom border-right hoverPostulante">
                    <label class="font-weight-bold">Edad:</label>
                    <p>{{ $user->postulante->edad }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Idioma(s):</label>
                    <p>{{ $user->postulante->idioma }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-right hoverPostulante">
                    <label class="font-weight-bold">DNI:</label>
                    <p>{{ $user->postulante->dni }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-right hoverPostulante">
                    <label class="font-weight-bold">Carrera:</label>
                    <p>{{ $user->postulante->programa->nombre }}</p>
                </div>
                <div class="col-lg-12 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Otros estudios:</label>
                    <p>{{ $user->postulante->otros_estudios }}</p>
                </div>
                <div class="col-lg-12 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Descripcino breve:</label>
                    <p>{{ $user->postulante->descripcion }}</p>
                </div>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h4 class="mt-3 mb-2">Sociales</h4>
                        </div>
                        @if (auth()->user()->postulante && auth()->user()->postulante->cv)
                            <div class="col-lg-4 pb-3 pt-3 border-bottom border-right border-left text-center hoverPostulante">
                                <a href="{{ asset(auth()->user()->postulante->cv) }}" target="_blank"
                                    class="btn btn-sm btn-danger">
                                    Ver CV &nbsp;<i class="fas fa-file-pdf fa-1x"></i> </a>
                            </div>
                        @endif
                        <div class="col-lg-4 pb-3 pt-3 border-bottom border-right text-center hoverPostulante">
                            <a href="{{ $user->postulante->facebook }}" target="_blank" class="btn-primary btn-sm">
                                Ver perfil de FaceBook <i class="fa-brands fa-facebook-square"></i></a>
                        </div>
                        <div class="col-lg-4 pb-3 pt-3 border-bottom border-right text-center hoverPostulante">
                            <a href="{{ $user->postulante->linkedin }}" target="_blank" class="btn-info btn-sm">
                                Ver perfil de LinkedIn <i class="fa-brands fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-12 pb-3 pt-3 border-bottom">
                    <a href="{{ route('postulante.create') }}" class="btn btn-sm btn-primary">
                        Registrar datos
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
