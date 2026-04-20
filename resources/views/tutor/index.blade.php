@extends('layouts.docente')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4"
            style="border-bottom: 1px dashed #4848fc78; padding-bottom:1em">
            <h3 class="font-weight-bold text-primary">Panel de Tutor</h3>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-lg-6 text-center">
                <div class="card shadow border-0 py-5">
                    <div class="card-body">
                        <i class="fas fa-chalkboard-teacher fa-4x text-primary mb-4"></i>
                        <h2 class="font-weight-bold text-gray-800">
                            ¡Bienvenido, {{ auth()->user()->name }} {{ auth()->user()->apellidos }}!
                        </h2>
                        <p class="text-muted mt-2">Estás ingresando como <strong>Tutor</strong>.</p>
                        @role('docente')
                            @if(auth()->user()->docente)
                                <a href="{{ route('vistaDocente', ['docente' => auth()->user()->docente->id]) }}"
                                   class="btn btn-outline-primary mt-3">
                                    <i class="fas fa-arrow-left mr-1"></i> Ir a mi panel de Docente
                                </a>
                            @endif
                        @endrole
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
