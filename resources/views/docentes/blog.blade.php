@extends('layouts.docente')
@section('contenido')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h3 class="mb-2 text-primary font-weight-bold"> {{ $docente->nombre }}</h3>
                {{-- <h5><strong>Curso:</strong> {{ $curso->nombre }}</h5>
                <p>
                    <strong>Programa:</strong> {{ $curso->ciclo->programa->nombre }} ||
                    <strong>Ciclo:</strong> {{ $curso->ciclo->nombre }}
                </p> --}}

                <div class="card mb-3">
                    <div class="card-body">

                        {!! $docente->blog !!}
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('docente.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        </div>
    </div>
@endsection
