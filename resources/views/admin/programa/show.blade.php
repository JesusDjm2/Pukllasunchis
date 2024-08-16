@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h2 class="mb-0 font-weight-bold">{{ $programa->nombre }}</h2>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row bg-white pb-3 mt-3">
            <div class="col-lg-12">
                <h4 class="font-weight-bold">Ciclos:</h4>
            </div>
            @foreach ($ciclos as $ciclo)
                <div class="col-lg-2 mb-2 mt-2">
                    <div class="card">
                        <div class="card-body">
                            <a href="{{ route('ciclo.show', ['ciclo' => $ciclo->id]) }}">
                                <h5 class="mb-3 font-weight-bold text-center">{{ $ciclo->nombre }}</h5>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
