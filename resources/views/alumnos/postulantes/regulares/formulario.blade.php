@extends('layouts.app')
@section('titulo', 'Formulario de Matrícula')
@section('content')
    <main class="d-flex justify-content-center align-items-center py-4 fondoLogin2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card mt-3 mb-3">
                        <h5 class="text-center mt-3 mb-2">Formulario de Inscripción Regular 2025</h5>
                        <div class="mx-auto mb-2" style="width: 30px; height: 4px; background-color: #4e73df; border-radius: 5px;"></div>
                        <p class="text-center">Los campos con (<span class="text-danger">*</span>) son obligatorios.</p>
                        <div class="mt-2">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <form action="{{ route('regulares.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="container mt-3">
                                    <ul class="nav nav-tabs nav-pills nav-justified flex-column flex-sm-row" id="myTab"
                                        role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home"
                                                role="tab" aria-controls="home" aria-selected="true">Datos
                                                Personales</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile"
                                                role="tab" aria-controls="profile"
                                                aria-selected="false">Entidad Educativa y Trabajo</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact"
                                                role="tab" aria-controls="contact"
                                                aria-selected="false">Documentos Adjuntos</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-2">
                                        <div class="tab-pane fade show active" id="home" role="tabpanel"
                                            aria-labelledby="home-tab">
                                            @include('alumnos.postulantes.regulares.campos.datos-personales')
                                            <div class="mt-4">
                                                <button type="button" class="btn btn-primary mt-4 next-tab"
                                                    data-tab="profile">Siguiente</button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="profile" role="tabpanel"
                                            aria-labelledby="profile-tab">
                                            @include('alumnos.postulantes.regulares.campos.colegio')
                                            <div class="mt-4">
                                                <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                                    data-tab="home">Anterior</button>
                                                <button type="button" class="btn btn-primary mt-4 next-tab"
                                                    data-tab="contact">Siguiente</button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="contact" role="tabpanel"
                                            aria-labelledby="contact-tab">
                                            @include('alumnos.postulantes.regulares.campos.documentos')
                                            <button type="button" class="btn btn-secondary mt-4 prev-tab"
                                                data-tab="profile">Anterior</button>
                                            <button type="button" class="btn btn-primary mt-4 next-tab"
                                                data-tab="socioeconomico">Siguiente</button>
                                        </div>                                                                            
                                    </div>
                                    <div>
                                        <button style="float:right; position: relative; bottom: 3em" type="submit"
                                            class="btn btn-primary mt-3">Registrar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
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

        <script>
            $(document).ready(function() {
                $('#myForm').submit(function(event) {
                    if ($('#programa_selector').val() === null) {
                        event.preventDefault();
                        alert('Por favor, elige un programa.');
                    }

                    if ($('#ciclo_selector').val() === null) {
                        event.preventDefault();
                        alert('Por favor, elige un ciclo.');
                    }
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var programaSelector = document.getElementById('programa_selector');
                var cicloSelector = document.getElementById('ciclo_selector');
                programaSelector.addEventListener('change', function() {
                    var programaId = programaSelector.value;
                    cicloSelector.innerHTML = '<option disabled selected>Elegir Ciclo</option>';
                    if (programaId) {
                        fetch('/obtener-ciclos/' + programaId)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(ciclo => {
                                    var option = document.createElement('option');
                                    option.value = ciclo.id;
                                    option.text = ciclo.nombre;
                                    cicloSelector.appendChild(option);
                                });
                            })
                            .catch(error => console.error('Error:', error));
                    }
                });
            });
        </script>
    </main>
@endsection
