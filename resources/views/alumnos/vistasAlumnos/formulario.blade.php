@extends('layouts.app')
@section('titulo', 'Formulario de Matrícula')
@section('content')
    <main class="d-flex justify-content-center align-items-center py-4 fondoLogin2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card mt-3 mb-3 shadow">
                        <div class="card-header text-white py-3" style="background-color: rgb(194 141 74);">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">
                                        <i class="fas fa-graduation-cap mr-2"></i>
                                        @auth
                                            @if (auth()->user()->hasRole('alumno'))
                                                Registro de matrícula - Formación Inicial Docente
                                            @elseif(auth()->user()->hasRole('alumnoB'))
                                                Registro de matrícula - Profesionalización Docente
                                            @endif
                                        @endauth
                                    </h5>
                                </div>
                                <div class="col-auto">
                                    <div class="bg-white text-dark py-1 px-3 rounded-pill small font-weight-bold">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <span class="text-danger">*</span> Campos requeridos
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Alertas de errores mejoradas --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                                <strong><i class="fas fa-exclamation-triangle mr-2"></i>
                                    Por favor, corrige los siguientes errores:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <div class="card-body">
                            @auth
                                @if (auth()->user()->hasRole('alumno'))
                                    <form action="{{ route('alumnos.store') }}" method="POST" enctype="multipart/form-data"
                                        id="registroForm">
                                        @csrf
                                        {{-- Datos Personales --}}
                                        <div class="form-section">
                                            <div class="section-title">
                                                <h5><i class="fas fa-user mr-2"></i>Datos Personales</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.datos-personales')
                                        </div>
                                        {{-- Familiares/Educativos --}}
                                        <div class="form-section mt-4">
                                            <div class="section-title">
                                                <h5><i class="fas fa-users mr-2"></i>Datos Familiares y Educativos</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.caracteristicas-familiares')
                                        </div>
                                        {{-- Socioeconómicos --}}
                                        <div class="form-section mt-4">
                                            <div class="section-title">
                                                <h5><i class="fas fa-chart-line mr-2"></i>Aspectos Socioeconómicos</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.aspectos-socioeconomico')
                                        </div>
                                        {{-- Aspectos Vivienda --}}
                                        <div class="form-section mt-4">
                                            <div class="section-title">
                                                <h5><i class="fas fa-home mr-2"></i>Aspectos de Vivienda</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.aspectos-vivienda')
                                        </div>
                                        {{-- Salud y Adicionales --}}
                                        <div class="form-section mt-4">
                                            <div class="section-title">
                                                <h5><i class="fas fa-heartbeat mr-2"></i>Salud y Aspectos Adicionales</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.aspectos-salud-culturales')
                                        </div>
                                        <div class="text-center mt-5 pt-3 border-top">
                                            <button type="submit" class="btn btn-primary btn-lg px-5" id="submitForm">
                                                <i class="fas fa-check mr-2"></i>Finalizar Registro
                                            </button>
                                        </div>
                                    </form>
                                @elseif(auth()->user()->hasRole('alumnoB'))
                                    <form action="{{ route('ppd.store') }}" method="POST" class="needs-validation" novalidate>
                                        @csrf

                                        {{-- Datos Personales --}}
                                        <div class="form-section">
                                            <div class="section-title">
                                                <h5><i class="fas fa-user mr-2"></i>Datos Personales</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.datos-personales')
                                        </div>

                                        {{-- Datos Educativos PPD --}}
                                        <div class="form-section mt-4">
                                            <div class="section-title">
                                                <h5><i class="fas fa-graduation-cap mr-2"></i>Datos Educativos</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.ppd.form.educativos')
                                        </div>

                                        {{-- Socioeconómicos --}}
                                        <div class="form-section mt-4">
                                            <div class="section-title">
                                                <h5><i class="fas fa-chart-line mr-2"></i>Aspectos Socioeconómicos</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.aspectos-socioeconomico')
                                        </div>

                                        {{-- Aspectos Vivienda --}}
                                        <div class="form-section mt-4">
                                            <div class="section-title">
                                                <h5><i class="fas fa-home mr-2"></i>Aspectos de Vivienda</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.aspectos-vivienda')
                                        </div>

                                        {{-- Salud y Adicionales --}}
                                        <div class="form-section mt-4">
                                            <div class="section-title">
                                                <h5><i class="fas fa-heartbeat mr-2"></i>Salud y Aspectos Adicionales</h5>
                                                <hr>
                                            </div>
                                            @include('alumnos.aspectos-salud-culturales')
                                        </div>

                                        <div class="text-center mt-5 pt-3 border-top">
                                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                                <i class="fas fa-save mr-2"></i>Registrar Matrícula
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scripts mejorados --}}
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

        <script>
            $(document).ready(function() {
                // Función para resaltar campos con error
                function highlightErrors() {
                    @if ($errors->any())
                        // Resaltar todos los campos con error
                        @foreach ($errors->keys() as $errorKey)
                            $('[name="{{ $errorKey }}"]').addClass('is-invalid');

                            // Agregar mensaje de error si no existe
                            if (!$('[name="{{ $errorKey }}"]').next('.invalid-feedback').length) {
                                $('[name="{{ $errorKey }}"]').after(
                                    '<div class="invalid-feedback">Este campo es obligatorio</div>');
                            }

                            // Hacer scroll al primer error
                            if ($('.is-invalid').first().length) {
                                $('html, body').animate({
                                    scrollTop: $('.is-invalid').first().offset().top - 100
                                }, 500);
                            }
                        @endforeach
                    @endif
                }

                // Ejecutar resaltado de errores al cargar la página
                highlightErrors();

                // Validación en tiempo real
                $('input, select, textarea').on('change blur', function() {
                    if ($(this).prop('required')) {
                        if ($(this).val()) {
                            $(this).removeClass('is-invalid');
                            $(this).next('.invalid-feedback').remove();
                        } else {
                            $(this).addClass('is-invalid');
                            if (!$(this).next('.invalid-feedback').length) {
                                $(this).after('<div class="invalid-feedback">Este campo es obligatorio</div>');
                            }
                        }
                    }

                    // Validación específica para email
                    if ($(this).attr('type') === 'email' && $(this).val()) {
                        var emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
                        if (!emailRegex.test($(this).val())) {
                            $(this).addClass('is-invalid');
                            if (!$(this).next('.invalid-feedback').length) {
                                $(this).after('<div class="invalid-feedback">Ingrese un email válido</div>');
                            }
                        }
                    }

                    // Validación para teléfono
                    if ($(this).attr('type') === 'tel' && $(this).val()) {
                        var phoneRegex = /^[0-9+\-\s()]{7,15}$/;
                        if (!phoneRegex.test($(this).val())) {
                            $(this).addClass('is-invalid');
                            if (!$(this).next('.invalid-feedback').length) {
                                $(this).after('<div class="invalid-feedback">Ingrese un teléfono válido</div>');
                            }
                        }
                    }
                });

                // Validación final del formulario
                $('#registroForm, .needs-validation').on('submit', function(e) {
                    var isValid = true;
                    var firstInvalid = null;

                    // Validar todos los campos requeridos
                    $(this).find('[required]').each(function() {
                        if (!$(this).val()) {
                            isValid = false;
                            $(this).addClass('is-invalid');

                            if (!firstInvalid) {
                                firstInvalid = $(this);
                            }

                            if (!$(this).next('.invalid-feedback').length) {
                                $(this).after(
                                    '<div class="invalid-feedback">Este campo es obligatorio</div>');
                            }
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    // Validar emails
                    $(this).find('input[type="email"]').each(function() {
                        if ($(this).val()) {
                            var emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
                            if (!emailRegex.test($(this).val())) {
                                isValid = false;
                                $(this).addClass('is-invalid');
                                if (!firstInvalid) firstInvalid = $(this);
                            }
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();

                        // Mostrar alerta
                        if (!$('.alert-warning').length) {
                            $('.card-body').prepend(
                                '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                                '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                                '<strong>Por favor, completa todos los campos requeridos</strong><br>' +
                                'Los campos marcados en rojo son obligatorios.' +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                '<span aria-hidden="true">&times;</span>' +
                                '</button>' +
                                '</div>'
                            );
                        }

                        // Hacer scroll al primer campo inválido
                        if (firstInvalid) {
                            $('html, body').animate({
                                scrollTop: firstInvalid.offset().top - 100
                            }, 500);
                            firstInvalid.focus();
                        }

                        return false;
                    }
                });

                // Deshabilitar enter para prevenir envíos accidentales
                $(document).on('keyup keypress', 'form input:not([type="submit"])', function(e) {
                    if (e.keyCode == 13) {
                        e.preventDefault();
                        return false;
                    }
                });

                // Inicializar tooltips
                $('[data-toggle="tooltip"]').tooltip();
            });

            // Script para programas y ciclos
            document.addEventListener('DOMContentLoaded', function() {
                var programaSelector = document.getElementById('programa_selector');
                var cicloSelector = document.getElementById('ciclo_selector');

                if (programaSelector && cicloSelector) {
                    programaSelector.addEventListener('change', function() {
                        var programaId = programaSelector.value;
                        cicloSelector.innerHTML = '<option disabled selected>Cargando ciclos...</option>';
                        cicloSelector.disabled = true;

                        if (programaId) {
                            fetch('/obtener-ciclos/' + programaId)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Error en la respuesta del servidor');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    cicloSelector.innerHTML =
                                        '<option disabled selected>Elegir Ciclo</option>';
                                    if (data.length > 0) {
                                        data.forEach(ciclo => {
                                            var option = document.createElement('option');
                                            option.value = ciclo.id;
                                            option.text = ciclo.nombre;
                                            cicloSelector.appendChild(option);
                                        });
                                        cicloSelector.disabled = false;
                                    } else {
                                        cicloSelector.innerHTML =
                                            '<option disabled>No hay ciclos disponibles</option>';
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    cicloSelector.innerHTML =
                                        '<option disabled>Error al cargar ciclos</option>';
                                });
                        } else {
                            cicloSelector.innerHTML =
                                '<option disabled selected>Primero selecciona un programa</option>';
                        }
                    });
                }
            });
        </script>
        <style>
            .fondoLogin2 {
                min-height: 100vh;
                background-attachment: fixed;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                position: relative;
                z-index: 1;
            }

            .form-section {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                transition: all 0.3s ease;
            }

            .form-section:hover {
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            }

            .section-title h5 {
                color: rgb(194 141 74);
                font-weight: 600;
                margin-bottom: 5px;
            }

            .section-title hr {
                margin-top: 5px;
                border-color: #e0e0e0;
            }

            .card {
                border: none;
                border-radius: 15px;
            }

            .card-header {
                border-radius: 15px 15px 0 0 !important;
            }

            .is-invalid {
                border-color: #dc3545;
                background-color: #fff8f8;
            }

            .invalid-feedback {
                display: block;
                font-size: 80%;
                color: #dc3545;
            }

            .btn-primary {
                background-color: rgb(194 141 74);
                border-color: rgb(194 141 74);
                border-radius: 25px;
                padding: 10px 30px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-primary:hover {
                background-color: rgb(174 121 54);
                border-color: rgb(174 121 54);
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .btn-primary:active {
                transform: translateY(0);
            }

            .fondoLogin2 {
                min-height: 100vh;
                background-color: #f5f5f5;
            }

            /* Animación para resaltar campos con error */
            @keyframes shake {

                0%,
                100% {
                    transform: translateX(0);
                }

                25% {
                    transform: translateX(-5px);
                }

                75% {
                    transform: translateX(5px);
                }
            }

            .is-invalid:focus {
                animation: shake 0.3s ease-in-out;
            }

            /* Mejoras en inputs */
            input,
            select,
            textarea {
                transition: all 0.3s ease;
            }

            input:focus,
            select:focus,
            textarea:focus {
                box-shadow: 0 0 0 0.2rem rgba(194, 141, 74, 0.25);
                border-color: rgb(194 141 74);
            }

            @media (max-width: 768px) {
                .form-section {
                    padding: 15px;
                }

                .btn-primary {
                    padding: 8px 20px;
                }
            }
        </style>
    </main>
@endsection
