@extends('layouts.admin')

@section('titulo', 'Asignar Ciclos al Tutor')

@section('contenido')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4"
         style="border-bottom:1px dashed #4848fc78;padding-bottom:1em">
        <h4 class="font-weight-bold text-primary mb-0">
            <i class="fas fa-chalkboard-teacher mr-2"></i>
            Asignar ciclos al Tutor
        </h4>
        <a href="{{ route('admin') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:44px;height:44px;background:linear-gradient(135deg,#4e73df,#224abe);">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold text-gray-800">
                                {{ $tutor->name }} {{ $tutor->apellidos }}
                            </div>
                            <div class="text-muted small">{{ $tutor->email }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tutor.ciclos.update', $tutor->id) }}" method="POST">
                        @csrf
                        <p class="text-muted small mb-3">
                            Selecciona los ciclos que este tutor supervisará. Puede tener varios ciclos asignados.
                        </p>

                        @php
                            $porPrograma = $ciclos->groupBy(fn($c) => optional($c->programa)->nombre ?? 'Sin programa');
                        @endphp

                        @foreach ($porPrograma as $programa => $ciclsGrupo)
                            <div class="mb-3">
                                <div class="text-uppercase text-muted font-weight-bold mb-2"
                                     style="font-size:.68rem;letter-spacing:.08em;">
                                    {{ $programa }}
                                </div>
                                <div class="row">
                                    @foreach ($ciclsGrupo as $ciclo)
                                        <div class="col-6 col-sm-4 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                       id="ciclo{{ $ciclo->id }}"
                                                       name="ciclos[]"
                                                       value="{{ $ciclo->id }}"
                                                       {{ in_array($ciclo->id, $asignados) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ciclo{{ $ciclo->id }}">
                                                    Ciclo {{ $ciclo->nombre }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <hr class="my-2">
                        @endforeach

                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('admin') }}" class="btn btn-outline-secondary mr-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Guardar asignación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
