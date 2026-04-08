@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="text-primary font-weight-bold">Seleccionar Periodo de Admisión:</h3>

            <a href="{{ route('regulares.index') }}" class="btn btn-sm btn-danger">Volver</a>
        </div>
        <div class="row mt-4">
            <div class="col-lg-12 text-center">
                {{-- Botón para PPD (enlace vacío por ahora) --}}
                <a href="" class="btn btn-primary btn-lg m-2">
                    Periodos PPD
                </a>
                {{-- Botón para FID (enlace correcto solicitado) --}}
                <a class="btn btn-success btn-lg m-2" href="{{ route('admin-fids.index') }}">
                    Periodos FID
                </a>
            </div>
        </div>
    </div>
@endsection
