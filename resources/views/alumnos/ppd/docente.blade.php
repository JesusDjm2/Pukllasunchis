@extends('layouts.profesionalizacion')
@section('contenido')
    <div class="container mt-4">
        <div class="ppd-page-header">
            <div>
                <span class="ppd-eyebrow"><i class="fa fa-chalkboard-teacher mr-1"></i>Docente</span>
                <h4 class="ppd-page-title">Datos del docente</h4>
            </div>
            <a href="javascript:history.back()" class="btn btn-sm btn-ppd-volver">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
        <div class="row">
            <div class="col-md-9">
                <div class="card shadow-sm border-0">
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
                <div class="card mb-3 shadow-sm border-0">
                    <img src="{{ $docente->imagen ? asset('storage/' . $docente->imagen) : 'https://via.placeholder.com/300' }}"
                        class="card-img-top" width="100%">
                </div>
            </div>
        </div>
    </div>
@endsection
