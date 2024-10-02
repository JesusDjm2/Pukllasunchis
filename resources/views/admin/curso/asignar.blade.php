@extends('layouts.admin')
@section('contenido')
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="text-primary font-weight-bold">{{ $curso->nombre }}</h4>
                <p class="font-weight-bold">
                   Programa: {{$curso->ciclo->programa->nombre}} Ciclo: {{$curso->ciclo->nombre}}  
                </p>
                <p>Seleccionar un máximo de 3 Competencias para calificar el Curso.</p>
            </div>
            <div class="col-md-4 text-md-right text-left">
                <a href="{{route('docente.index')}}" class="btn btn-danger btn-sm">Volver</a>
            </div>
        </div>
        <div class="row bg-white">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <form action="{{ route('curso.guardar.competencias', ['cursoId' => $curso->id]) }}" method="POST">
            @csrf
            <ul>
                @foreach($competencias as $competencia)
                    <li>
                        <input 
                            type="checkbox" 
                            name="competencias[]" 
                            value="{{ $competencia->id }}"
                            @if(in_array($competencia->id, $competenciasSeleccionadas)) checked @endif
                        > {{ $competencia->nombre }}
                    </li>
                @endforeach
            </ul>

            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    if (document.querySelectorAll('input[type="checkbox"]:checked').length > 3) {
                        this.checked = false;
                        alert('Solo puedes seleccionar un máximo de 3 competencias.');
                    }
                });
            });
        });
    </script>
@endsection
