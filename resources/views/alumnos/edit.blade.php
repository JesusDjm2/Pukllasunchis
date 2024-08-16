@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-gray-800">Editar alumno:    <strong> {{ $alumno->apellidos }}
                    {{ $alumno->nombres }}</strong></h4>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
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
                @if (stripos($alumno->num_comprobante, 'beca') !== false)
                        <div class="alert alert-info text-center" role="alert">
                            De no tener ningun dato para actualizar, solo guardar el formulario.
                        </div>
                    @else
                        <div class="alert alert-info text-center" role="alert">
                            De no tener ningun dato para actualizar, solo completar número de voucher y guardar el formulario.
                        </div>
                    @endif
            </div>
            <div class="col-lg-12 card pt-3 pb-3 border border-primary">
                <form action="{{ route('alumnos.update', ['alumno' => $alumno->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="container-fluid mt-3">
                        <ul class="nav nav-tabs nav-pills nav-justified flex-column flex-sm-row" id="myTab"
                            role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                    aria-controls="home" aria-selected="true">Datos Personales</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                                    aria-controls="profile" aria-selected="false">Familiares/Educativos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="socioeconomico-tab" data-toggle="tab" href="#socioeconomico"
                                    role="tab" aria-controls="socioeconomico" aria-selected="false">
                                    Socioeconómicos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                                    aria-controls="contact" aria-selected="false">Aspecto Vivienda</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" id="vivienda-tab" data-toggle="tab" href="#vivienda" role="tab"
                                    aria-controls="vivienda" aria-selected="false">Salud y Adicionales</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-2">
                            <div class="tab-pane fade show active" id="home" role="tabpanel"
                                aria-labelledby="home-tab">
                                @include('alumnos.vistasAlumnos.edit.datos-personales-edit')
                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary mt-4 next-tab"
                                        data-tab="profile">Siguiente</button>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                @include('alumnos.vistasAlumnos.edit.caracteristicas-familiares-edit')
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                        data-tab="home">Anterior</button>
                                    <button type="button" class="btn btn-primary mt-4 next-tab"
                                        data-tab="contact">Siguiente</button>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="socioeconomico" role="tabpanel"
                                aria-labelledby="socioeconomico-tab">
                                @include('alumnos.vistasAlumnos.edit.aspectos-socioeconomico-edit')
                                <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                    data-tab="profile">Anterior</button>
                                <button type="button" class="btn btn-primary mt-4 next-tab"
                                    data-tab="socioeconomico">Siguiente</button>
                            </div>
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                @include('alumnos.vistasAlumnos.edit.aspectos-vivienda-edit')
                                <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                    data-tab="contact">Anterior</button>
                                <button type="button" class="btn btn-primary mt-4 next-tab"
                                    data-tab="vivienda">Siguiente</button>
                            </div>

                            <div class="tab-pane fade" id="vivienda" role="tabpanel" aria-labelledby="vivienda-tab">
                                @include('alumnos.vistasAlumnos.edit.aspectos-salud-culturales-edit')
                                <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                    data-tab="socioeconomico">Anterior</button>
                            </div>
                        </div>
                        <button style="float:right; position: relative; bottom: 3em" type="submit"
                            class="btn btn-primary mt-3">Actualizar </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Función para avanzar al siguiente tab
            $('.next-tab').click(function() {
                var currentTab = $(this).closest('.tab-pane').attr('id');
                var nextTab = $(this).data('tab');

                // Validar campos requeridos antes de cambiar de tab
                if (validateTab(currentTab)) {
                    $('#' + currentTab).removeClass('show active');
                    $('#' + nextTab).addClass('show active');
                    $('#myTab a[href="#' + nextTab + '"]').tab('show');
                }
            });

            // Función para retroceder al tab anterior
            $('.prev-tab').click(function() {
                var currentTab = $(this).closest('.tab-pane').attr('id');
                var prevTab = $(this).data('tab');

                $('#' + currentTab).removeClass('show active');
                $('#' + prevTab).addClass('show active');
                $('#myTab a[href="#' + prevTab + '"]').tab('show');
            });

            // Función para validar campos requeridos en el tab actual
            function validateTab(tabId) {
                var isValid = true;

                $('#' + tabId + ' [required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                return isValid;
            }
        });
    </script>
@endsection
