@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
            <h3 class="mb-0 text-gray-800">Crear nuevo alumno</h3>            
            <a href="{{ route('alumnos.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>            
        </div>
        <div class="row">
            <div class="col-lg-12">
                <span><p>Los campos con (<span class="text-danger">*</span>) son obligatorios. </p></span>
            </div>
            <div class="col-12">
                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="col-lg-12">
                <form action="{{ route('alumnos.store') }}" method="POST">
                    @csrf
                    <div class="container mt-3">
                        <ul class="nav nav-tabs nav-pills nav-justified flex-column flex-sm-row" id="myTab"
                            role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                    aria-controls="home" aria-selected="true">Datos Personales</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                                    aria-controls="profile" aria-selected="false">Caraterísticas Familiares</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                                    aria-controls="contact" aria-selected="false">Aspectos Educativos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="socioeconomico-tab" data-toggle="tab" href="#socioeconomico"
                                    role="tab" aria-controls="socioeconomico" aria-selected="false">
                                    Socioeconómicos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="vivienda-tab" data-toggle="tab" href="#vivienda" role="tab"
                                    aria-controls="vivienda" aria-selected="false">Vivienda</a>
                            </li>
                        </ul> 
                        <div class="tab-content mt-2">
                            <div class="tab-pane fade show active" id="home" role="tabpanel"
                                aria-labelledby="home-tab">
                                @include('alumnos.datos-personales')
                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                @include('alumnos.caracteristicas-familiares')
                            </div>
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                @include('alumnos.aspectos-educativos')
                            </div>
                            <div class="tab-pane fade" id="socioeconomico" role="tabpanel"
                                aria-labelledby="socioeconomico-tab">
                                @include('alumnos.aspectos-socioeconomico')
                            </div>
                            <div class="tab-pane fade" id="vivienda" role="tabpanel" aria-labelledby="vivienda-tab">
                                @include('alumnos.aspectos-adicionales')
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Registrar </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
