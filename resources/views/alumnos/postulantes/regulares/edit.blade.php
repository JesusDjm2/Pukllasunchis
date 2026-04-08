@extends('layouts.app')
@section('titulo', 'Formulario de Matrícula')
@section('content')
    <main class="d-flex justify-content-center align-items-center py-4 fondoLogin2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card mt-3 mb-3">
                        <h5 class="text-center mt-3 mb-2">Formulario de Inscripción Regular</h5>
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
                            <form action="{{ route('regulares.update', $postulante->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="container mt-3">
                                    @include('alumnos.postulantes.regulares.campos.datos-personales-edit')

                                    <button style="float:right; position: relative; bottom: 3em" type="submit"
                                        class="btn btn-primary mt-3">Actualizar</button>
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
                // 🌎 FUNCIONES REUTILIZABLES
                function cargarProvincias(departamentoId, provinciaSelectId, distritoSelectId, selectedProvincia = null,
                    selectedDistrito = null) {
                    $(`#${provinciaSelectId}`).html('<option>Cargando...</option>').prop('disabled', true);
                    $(`#${distritoSelectId}`).html('<option>-- Selecciona una provincia --</option>').prop('disabled',
                        true);

                    if (departamentoId) {
                        $.get(`/get-provincias/${departamentoId}`, function(data) {
                            let options = '<option value="">-- Selecciona --</option>';
                            data.forEach(p => options += `<option value="${p.id}">${p.nombre}</option>`);
                            $(`#${provinciaSelectId}`).html(options).prop('disabled', false);

                            if (selectedProvincia) {
                                // Buscar por nombre o ID según corresponda
                                let provId = null;

                                // Si selectedProvincia es un ID numérico
                                if (!isNaN(selectedProvincia)) {
                                    provId = selectedProvincia;
                                } else {
                                    // Si es nombre, buscar por nombre
                                    let provincia = data.find(p => p.nombre === selectedProvincia);
                                    provId = provincia ? provincia.id : null;
                                }

                                if (provId) {
                                    $(`#${provinciaSelectId}`).val(provId);
                                    // Actualizar el campo hidden
                                    let provinciaTexto = $(`#${provinciaSelectId} option:selected`).text();
                                    $(`#${provinciaSelectId.replace('_select', '')}`).val(provinciaTexto);
                                    // Carga distritos solo después de seleccionar la provincia
                                    cargarDistritos(provId, distritoSelectId, selectedDistrito);
                                }
                            }
                        });
                    }
                }

                function cargarDistritos(provinciaId, distritoSelectId, selectedDistrito = null) {
                    $(`#${distritoSelectId}`).html('<option>Cargando...</option>').prop('disabled', true);

                    if (provinciaId) {
                        $.get(`/get-distritos/${provinciaId}`, function(data) {
                            let options = '<option value="">-- Selecciona --</option>';
                            data.forEach(d => options += `<option value="${d.id}">${d.nombre}</option>`);
                            $(`#${distritoSelectId}`).html(options).prop('disabled', false);

                            if (selectedDistrito) {
                                // Buscar por nombre o ID según corresponda
                                let distId = null;

                                // Si selectedDistrito es un ID numérico
                                if (!isNaN(selectedDistrito)) {
                                    distId = selectedDistrito;
                                } else {
                                    // Si es nombre, buscar por nombre
                                    let distrito = data.find(d => d.nombre === selectedDistrito);
                                    distId = distrito ? distrito.id : null;
                                }

                                if (distId) {
                                    $(`#${distritoSelectId}`).val(distId);
                                    // Actualizar el campo hidden
                                    let distritoTexto = $(`#${distritoSelectId} option:selected`).text();
                                    $(`#${distritoSelectId.replace('_select', '')}`).val(distritoTexto);
                                }
                            }
                        });
                    }
                }

                // Función para inicializar los datos según el modo (crear o editar)
                function inicializarSelectores() {
                    // Verificar si estamos en modo edición (existe postulanteData)
                    if (typeof postulanteData !== 'undefined') {
                        // MODO EDICIÓN - Cargar datos del postulante
                        cargarDatosEdicion();
                    } else {
                        // MODO CREACIÓN - Cargar datos de old()
                        cargarDatosOld();
                    }
                }

                // Cargar datos para edición
                function cargarDatosEdicion() {
                    // DEPARTAMENTO DE NACIMIENTO
                    if (postulanteData.departamento_nacimiento) {
                        let depNacSelect = $('#departamento_nacimiento_select');
                        // Buscar el option que coincida con el texto guardado
                        let depOption = depNacSelect.find('option').filter(function() {
                            return $(this).text() === postulanteData.departamento_nacimiento;
                        });

                        if (depOption.length) {
                            let depId = depOption.val();
                            depNacSelect.val(depId);
                            $('#departamento_nacimiento').val(postulanteData.departamento_nacimiento);
                            cargarProvincias(depId, 'provincia_nacimiento_select', 'distrito_nacimiento_select',
                                postulanteData.provincia_nacimiento, postulanteData.distrito_nacimiento);
                        }
                    }

                    // DEPARTAMENTO DE COLEGIO
                    if (postulanteData.departamento_colegio) {
                        let depColeSelect = $('#departamento_colegio_select');
                        let depOption = depColeSelect.find('option').filter(function() {
                            return $(this).text() === postulanteData.departamento_colegio;
                        });

                        if (depOption.length) {
                            let depId = depOption.val();
                            depColeSelect.val(depId);
                            $('#departamento_colegio').val(postulanteData.departamento_colegio);
                            cargarProvincias(depId, 'provincia_colegio_select', 'distrito_colegio_select',
                                postulanteData.provincia_colegio, postulanteData.distrito_colegio);
                        }
                    }
                }

                // Cargar datos para creación (usando old())
                function cargarDatosOld() {
                    const oldDepNac = "{{ old('departamento_nacimiento') }}";
                    const oldProvNac = "{{ old('provincia_nacimiento') }}";
                    const oldDistNac = "{{ old('distrito_nacimiento') }}";

                    if (oldDepNac) {
                        let depId = $('#departamento_nacimiento_select option').filter(function() {
                            return $(this).text() === oldDepNac;
                        }).val();
                        if (depId) {
                            $('#departamento_nacimiento_select').val(depId);
                            $('#departamento_nacimiento').val(oldDepNac);
                            cargarProvincias(depId, 'provincia_nacimiento_select', 'distrito_nacimiento_select',
                                oldProvNac,
                                oldDistNac);
                        }
                    }

                    const oldDepCole = "{{ old('departamento_colegio') }}";
                    const oldProvCole = "{{ old('provincia_colegio') }}";
                    const oldDistCole = "{{ old('distrito_colegio') }}";

                    if (oldDepCole) {
                        let depId = $('#departamento_colegio_select option').filter(function() {
                            return $(this).text() === oldDepCole;
                        }).val();
                        if (depId) {
                            $('#departamento_colegio_select').val(depId);
                            $('#departamento_colegio').val(oldDepCole);
                            cargarProvincias(depId, 'provincia_colegio_select', 'distrito_colegio_select', oldProvCole,
                                oldDistCole);
                        }
                    }
                }

                // 🍽️ Eventos onchange (igual que antes)
                $('#departamento_nacimiento_select').on('change', function() {
                    const texto = $(this).find('option:selected').text();
                    $('#departamento_nacimiento').val(texto);
                    const id = $(this).val();
                    cargarProvincias(id, 'provincia_nacimiento_select', 'distrito_nacimiento_select');
                });

                $('#provincia_nacimiento_select').on('change', function() {
                    const texto = $(this).find('option:selected').text();
                    $('#provincia_nacimiento').val(texto);
                    const id = $(this).val();
                    cargarDistritos(id, 'distrito_nacimiento_select');
                });

                $('#distrito_nacimiento_select').on('change', function() {
                    const texto = $(this).find('option:selected').text();
                    $('#distrito_nacimiento').val(texto);
                });

                $('#departamento_colegio_select').on('change', function() {
                    const texto = $(this).find('option:selected').text();
                    $('#departamento_colegio').val(texto);
                    const id = $(this).val();
                    cargarProvincias(id, 'provincia_colegio_select', 'distrito_colegio_select');
                });

                $('#provincia_colegio_select').on('change', function() {
                    const texto = $(this).find('option:selected').text();
                    $('#provincia_colegio').val(texto);
                    const id = $(this).val();
                    cargarDistritos(id, 'distrito_colegio_select');
                });

                $('#distrito_colegio_select').on('change', function() {
                    const texto = $(this).find('option:selected').text();
                    $('#distrito_colegio').val(texto);
                });

                // Inicializar los selectores al cargar la página
                inicializarSelectores();
            });
        </script>
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
