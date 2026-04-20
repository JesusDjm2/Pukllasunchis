@extends('layouts.docente')

@section('titulo', 'Mi perfil — Docente')

@section('contenido')
    <div class="container-fluid docente-ui-page docente-perfil-page">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between mb-3 pt-3">
            <div class="mb-2 mb-md-0">
                <p class="text-uppercase text-muted small mb-1 font-weight-bold" style="letter-spacing: .04em;">Perfil
                </p>
                <h1 class="h4 font-weight-bold text-gray-800 mb-0">Datos del docente</h1>
            </div>
            <div class="d-flex">
                <a href="{{ route('docente.edit', $docente->id) }}" class="btn btn-primary btn-sm shadow-sm btn-block">
                    <i class="fa fa-edit fa-sm mr-1"></i> Editar perfil
                </a>
            </div>
        </div>

        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-4 col-lg-3 mb-4 mb-md-0 d-flex justify-content-center justify-content-md-start">
                @if ($docente->foto)
                    <div class="docente-foto-wrap shadow-sm">
                        <img src="{{ asset('docentes/fotos/' . $docente->foto) }}" alt="Foto de {{ $docente->nombre }}"
                            class="img-fluid docente-foto-img">
                    </div>
                @else
                    <div class="photo-placeholder shadow-sm" role="img" aria-label="Sin foto de perfil">
                        <span class="sr-only">Sin foto</span>
                    </div>
                @endif
            </div>
            <div class="col-md-8 col-lg-9">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 p-md-4">
                        <dl class="row mb-0 docente-dl">
                            <dt class="col-sm-4 col-lg-3 text-muted small text-uppercase">Nombre</dt>
                            <dd class="col-sm-8 col-lg-9 font-weight-bold text-gray-800">{{ $docente->nombre }}</dd>

                            <dt class="col-sm-4 col-lg-3 text-muted small text-uppercase pt-3 border-top">DNI</dt>
                            <dd class="col-sm-8 col-lg-9 pt-3 border-top">{{ $docente->dni }}</dd>

                            <dt class="col-sm-4 col-lg-3 text-muted small text-uppercase pt-3 border-top">Correo</dt>
                            <dd class="col-sm-8 col-lg-9 pt-3 border-top">
                                <a href="mailto:{{ $docente->email }}">{{ $docente->email }}</a>
                            </dd>

                            <dt class="col-sm-4 col-lg-3 text-muted small text-uppercase pt-3 border-top align-self-start">
                                Descripción</dt>
                            <dd class="col-sm-8 col-lg-9 pt-3 border-top mb-0">
                                @if ($docente->descripcion)
                                    {{ $docente->descripcion }}
                                @else
                                    <span class="text-muted font-italic">Aún no hay descripción. Puede añadirla al editar
                                        el perfil; será visible para sus estudiantes.</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .docente-perfil-page .docente-foto-wrap {
            max-width: 220px;
            border-radius: 0.35rem;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .docente-perfil-page .docente-foto-img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 280px;
            object-fit: cover;
        }

        .docente-perfil-page .photo-placeholder {
            width: 100%;
            max-width: 220px;
            height: 260px;
            background: linear-gradient(145deg, #eef1f5, #e2e6ea);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 0.35rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .docente-perfil-page .photo-placeholder::after {
            content: 'Sin foto';
            font-size: 0.95rem;
            color: #8892a0;
            font-weight: 600;
        }

        .docente-perfil-page .docente-dl dt {
            font-weight: 600;
        }
    </style>
@endpush
