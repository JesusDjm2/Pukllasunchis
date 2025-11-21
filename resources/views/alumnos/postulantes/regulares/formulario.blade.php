@extends('layouts.app')
@section('titulo', 'Formulario de Matrícula')
@section('content')
    <main class="d-flex justify-content-center align-items-center py-4 fondoLogin2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card mt-3 mb-3">
                        <h5 class="text-center mt-3 mb-2">Formulario de Inscripción Regular 2025</h5>
                        <div class="mx-auto mb-2"
                            style="width: 30px; height: 4px; background-color: #4e73df; border-radius: 5px;"></div>
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
                                @include('alumnos.postulantes.regulares.campos.datos-personales')
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg mt-3">Registrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script>
            document.getElementById('btn-consultar').addEventListener('click', async () => {
                const dni = document.getElementById('dni').value;
                if (dni.length !== 8) {
                    alert('El DNI debe tener 8 dígitos');
                    return;
                }
                try {
                    const response = await fetch('{{ route('consulta.dni') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            dni
                        })
                    });
                    const data = await response.json();                   
                    if (data.success && data.data) {
                        document.getElementById('nombres').value = data.data.nombres ?? '';
                        document.getElementById('apellido_paterno').value = data.data.apellido_paterno ?? '';
                        document.getElementById('apellido_materno').value = data.data.apellido_materno ?? '';
                        // 🔹 Combinar los apellidos visibles
                        const apellidosCompletos = [
                            data.data.apellido_paterno ?? '',
                            data.data.apellido_materno ?? ''
                        ].filter(Boolean).join(' '); // Evita espacios dobles si falta uno
                        document.getElementById('apellidos').value = apellidosCompletos;
                    } else {
                        alert('No se encontró información para ese DNI.');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Error consultando el DNI');
                }
            });
        </script>
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
                                let provId = data.find(p => p.nombre === selectedProvincia)?.id;
                                if (provId) {
                                    $(`#${provinciaSelectId}`).val(provId);
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
                                let distId = data.find(d => d.nombre === selectedDistrito)?.id;
                                if (distId) {
                                    $(`#${distritoSelectId}`).val(distId);
                                    $(`#${distritoSelectId}`).trigger('change'); // actualiza hidden
                                }
                            }
                        });
                    }
                }

                // 🌱 Recuperar old() para nacimiento
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
                        cargarProvincias(depId, 'provincia_nacimiento_select', 'distrito_nacimiento_select', oldProvNac,
                            oldDistNac);
                    }
                }

                // 🌱 Recuperar old() para colegio
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

                // 🍼 y 🏫 tus eventos onchange siguen igual
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

                //Script para lenguas 1 y 2
                const lengua1 = document.getElementById('lengua_1');
                const lengua2 = document.getElementById('lengua_2');

                // Cuando cambia la lengua materna
                lengua1.addEventListener('change', function() {
                    const valorSeleccionado = this.value;

                    // Rehabilitar todas las opciones primero
                    Array.from(lengua2.options).forEach(opt => opt.disabled = false);

                    // Deshabilitar la misma opción en el segundo select
                    if (valorSeleccionado) {
                        const opcionIgual = Array.from(lengua2.options).find(opt => opt.value ===
                            valorSeleccionado);
                        if (opcionIgual) {
                            opcionIgual.disabled = true;
                            // Si estaba seleccionada, la limpiamos
                            if (lengua2.value === valorSeleccionado) lengua2.value = '';
                        }
                    }
                });
               
                function toggleVoucherRequirement() {
                    const esBeca = $('#estudio_beca').val();
                    const $voucherInput = $('#voucher_pago');
                    const $voucherLabel = $('label[for="voucher_pago"]');

                    if (esBeca == '1') {
                        // 🔹 Si es beca → quitar required y el asterisco
                        $voucherInput.prop('required', false);
                        $voucherLabel.find('span.text-danger').remove(); // elimina el *
                    } else {
                        // 🔹 Si NO es beca → agregar required y el asterisco si no está
                        $voucherInput.prop('required', true);
                        if ($voucherLabel.find('span.text-danger').length === 0) {
                            $voucherLabel.append(' <span class="text-danger">*</span>');
                        }
                    }
                }

                // Inicializar al cargar
                toggleVoucherRequirement();

                // Escuchar cambios
                $('#estudio_beca').on('change', toggleVoucherRequirement);
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
