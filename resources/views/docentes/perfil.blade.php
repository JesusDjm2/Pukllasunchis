@extends('layouts.docente')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h4 class="font-weight-bold">Datos del Docente:</h4>
            <a href="{{ route('docente.edit', $docente->id) }}"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right">
                Editar Perfil <i class="fa fa-edit fa-sm"></i>
            </a>
        </div>

        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ Session::get('success') }}
                <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
            </div>
        @endif

        <div class="row pb-4">
            <div class="col-md-2 d-flex justify-content-center align-items-start">
                <!-- Foto del docente -->
                @if ($docente->foto)
                    <img src="{{ asset('docentes/fotos/' . $docente->foto) }}" alt="Foto actual" class="img-thumbnail mt-2"
                        style="max-height: 200px;">
                @else
                    <!-- Cuadro simulador de foto -->
                    <div class="photo-placeholder d-flex justify-content-center align-items-center">
                        <span></span>
                    </div>
                @endif
            </div>
            <div class="col-md-10">
                <!-- Información del docente -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title pb-2" style="border-bottom: 1px dashed grey"><span
                                class="font-weight-bold">Nombre:</span> {{ $docente->nombre }}</h5>
                        <p class="card-text pb-2" style="border-bottom: 1px dashed grey"><strong>DNI:</strong>
                            {{ $docente->dni }}</p>
                        <p class="card-text pb-2" style="border-bottom: 1px dashed grey"><strong>Email:</strong>
                            {{ $docente->email }}</p>
                        <p class="card-text">
                            <strong>Descripción:</strong>
                            @if ($docente->descripcion)
                                {{ $docente->descripcion }}
                            @else
                                Agregar una breve descripción de su perfil. Esta podrá ser vista por sus alumnos.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .photo-placeholder {
            width: 150px;
            /* Tamaño de la foto de pasaporte */
            height: 200px;
            /* Tamaño de la foto de pasaporte */
            background-color: #e0e0e0;
            /* Fondo gris */
            border: 1px solid #ccc;
            /* Borde opcional */
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            font-size: 1.2rem;
            color: #888;
            text-align: center;
            font-weight: bold;
        }

        .photo-placeholder::before {
            content: 'Foto';
            position: absolute;
            transform: rotate(-45deg);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 1.5rem;
            color: rgba(0, 0, 0, 0.1);
        }

        .photo-img {
            max-width: 100%;
            max-height: 200px;
        }
    </style>
@endsection
