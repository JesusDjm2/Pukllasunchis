@extends('layouts.alumno')
@section('contenido')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 d-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
                style="border-bottom: 1px dashed #80808078">
                <h4 class="mb-2 text-primary font-weight-bold">Datos del docente:</h4>
                <a href="javascript:history.back()" class="btn btn-sm btn-danger">Volver</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8">{{ $docente->nombre }}</dd>
                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">{{ $docente->email }}</dd>
                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8">
                                @if ($docente->telefono)
                                    {{ $docente->telefono }}
                                @else
                                    No asignado
                                @endif
                            </dd>

                            {{-- <dt class="col-sm-4">DNI:</dt>
                            <dd>

                            </dd> --}}
                            {{-- <dd class="col-sm-8">{{ $docente->dni }}</dd> --}}

                            <dt class="col-sm-4">Descripción:</dt>
                            <dd class="col-sm-8">
                                @if ($docente->descripcion)
                                    {{ $docente->descripcion }}
                                @else
                                    No asignado
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <!-- Imagen del Docente -->
            <div class="col-md-3">
                <div class="card mb-3">
                    <img src="{{ $docente->imagen ? asset('storage/' . $docente->imagen) : 'https://via.placeholder.com/300' }}"
                        class="card-img-top" width="100%">
                </div>
            </div>
        </div>
    </div>
@endsection
