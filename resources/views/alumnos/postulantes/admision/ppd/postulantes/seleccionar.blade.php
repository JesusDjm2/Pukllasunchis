@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <!-- Header -->
        <div class="container-fluid mb-4">
            <div class="row align-items-center gy-3 border-bottom pb-3">
                <div class="col-12 col-md">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mr-2"
                            style="width: 48px; height: 48px;">
                            <i class="fas fa-fw fa-users text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 font-weight-bold text-primary">
                                Conversión Masiva de Postulantes PPD
                            </h4>
                            <small class="text-muted">
                                Seleccione los postulantes para convertir en usuarios del sistema
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-auto text-md-end">
                    <a href="{{ route('postulantes.ppd.index') }}" class="btn btn-danger btn-sm w-100 w-md-auto">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Resumen por programa -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="text-primary mb-3"><i class="fas fa-chart-pie"></i> Resumen de postulantes por programa</h6>
                <div class="row">
                    @php
                        $colores = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
                        $index = 0;
                    @endphp
                    @foreach ($postulantesPorPrograma as $programa)
                        @php
                            $color = $colores[$index % count($colores)];
                            $index++;
                        @endphp
                        <div class="col-md-4 col-6 mb-3">
                            <div class="d-flex align-items-center p-2 border rounded bg-light">
                                <span class="badge bg-{{ $color }} me-2 text-white mr-2"
                                    style="font-size: 1rem;">{{ $programa->total }}</span>
                                <div>
                                    <small class="fw-bold d-block">{{ $programa->programa }}</small>
                                    <small
                                        class="text-muted">{{ round(($programa->total / $totalPostulantes) * 100) }}%</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-md-4 col-6 mb-3">
                        <div class="d-flex align-items-center p-2 border rounded bg-light">
                            <span class="badge bg-primary me-2 text-white mr-2" style="font-size: 1rem;">{{ $totalPostulantes }}</span>
                            <div>
                                <small class="fw-bold d-block">TOTAL</small>
                                <small class="text-muted">100%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de selección masiva -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="seleccionarTodos">
                                <label class="form-check-label fw-bold" for="seleccionarTodos">
                                    Seleccionar todos
                                </label>
                            </div>
                            <span class="badge bg-info text-white" id="contadorSeleccionados">0 seleccionados</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <ul class="dropdown-menu" id="filtroPrograma">
                                <li><a class="dropdown-item" href="#" data-programa="todos">Todos los programas</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                @foreach ($postulantesPorPrograma as $programa)
                                    <li><a class="dropdown-item" href="#"
                                            data-programa="{{ $programa->programa }}">{{ $programa->programa }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn btn-success" id="btnConvertirMasivo" disabled>
                            <i class="fas fa-user-plus"></i> Convertir Seleccionados
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de postulantes agrupada por programa -->
        <div class="table-responsive">
            @php
                $programasAgrupados = $postulantes->groupBy('programa');
                $coloresProgramas = [];
                $indexColor = 0;
                foreach ($programasAgrupados as $programa => $items) {
                    $coloresProgramas[$programa] = $colores[$indexColor % count($colores)];
                    $indexColor++;
                }
            @endphp

            @foreach ($programasAgrupados as $programa => $postulantesPrograma)
                <div class="card mb-4 programa-group" data-programa="{{ $programa }}">
                    <div class="card-header bg-{{ $coloresProgramas[$programa] }} bg-opacity-25 py-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center text-white">
                                <span
                                    class="badge bg-{{ $coloresProgramas[$programa] }} me-2 text-white">{{ $postulantesPrograma->count() }}</span>
                                <h6 class="mb-0 fw-bold  text-white">
                                    <i class="fas fa-graduation-cap me-1"></i> {{ $programa }}
                                </h6>
                            </div>
                            <div class="form-check text-white">
                                <input type="checkbox" class="form-check-input seleccionar-programa"
                                    data-programa="{{ $programa }}" id="seleccionar{{ Str::slug($programa) }}">
                                <label class="form-check-label small" for="seleccionar{{ Str::slug($programa) }}">
                                    Seleccionar todos
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">
                                        <div class="form-check">
                                            {{-- <input type="checkbox" class="form-check-input seleccionar-pagina-programa"
                                                data-programa="{{ $programa }}"> --}}
                                        </div>
                                    </th>
                                    <th width="50">#</th>
                                    <th>Postulante</th>
                                    <th width="100" class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($postulantesPrograma as $p)
                                    <tr data-id="{{ $p->id }}"
                                        data-nombre="{{ $p->apellidos }}, {{ $p->nombres }}"
                                        data-programa="{{ $programa }}">
                                        <td class="text-center">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input checkbox-postulante"
                                                    value="{{ $p->id }}" data-programa="{{ $programa }}">
                                            </div>
                                        </td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>{{ $p->apellidos }}, {{ $p->nombres }}</strong>
                                                <div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-id-card"></i> {{ $p->dni }}
                                                    </small>
                                                    <small class="text-muted ms-2">
                                                        <i class="fas fa-envelope"></i> {{ $p->email }}
                                                    </small>
                                                </div>
                                            </div>
                                            @if ($p->convertido)
                                                <span class="badge bg-secondary mt-1">Convertido</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if ($p->apto && $p->apto2)
                                                <span class="badge bg-success">APTO</span>
                                            @else
                                                <span class="badge bg-warning">PENDIENTE</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            @if ($postulantes->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay postulantes disponibles</h5>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos del DOM
            const seleccionarTodos = document.getElementById('seleccionarTodos');
            const checkboxes = document.querySelectorAll('.checkbox-postulante');
            const contadorSeleccionados = document.getElementById('contadorSeleccionados');
            const btnConvertir = document.getElementById('btnConvertirMasivo');
            const programasGroup = document.querySelectorAll('.programa-group');

            // Función para actualizar contador y botón
            function actualizarSeleccion() {
                const seleccionados = document.querySelectorAll('.checkbox-postulante:checked').length;
                contadorSeleccionados.textContent =
                    `${seleccionados} seleccionado${seleccionados !== 1 ? 's' : ''}`;
                btnConvertir.disabled = seleccionados === 0;

                // Actualizar checkboxes de selección por programa
                document.querySelectorAll('.seleccionar-programa').forEach(checkbox => {
                    const programa = checkbox.dataset.programa;
                    const checkboxesPrograma = document.querySelectorAll(
                        `.checkbox-postulante[data-programa="${programa}"]`);
                    const checkboxesVisibles = Array.from(checkboxesPrograma);
                    const checkboxesChecked = Array.from(checkboxesPrograma).filter(cb => cb.checked);

                    if (checkboxesVisibles.length > 0) {
                        checkbox.checked = checkboxesVisibles.length === checkboxesChecked.length;
                        checkbox.indeterminate = checkboxesChecked.length > 0 &&
                            checkboxesChecked.length < checkboxesVisibles.length;
                    }
                });

                // Actualizar checkbox "seleccionar todos" global
                const totalVisibles = Array.from(checkboxes).length;
                const totalChecked = Array.from(checkboxes).filter(cb => cb.checked).length;

                if (totalVisibles > 0) {
                    seleccionarTodos.checked = totalVisibles === totalChecked;
                    seleccionarTodos.indeterminate = totalChecked > 0 && totalChecked < totalVisibles;
                } else {
                    seleccionarTodos.checked = false;
                    seleccionarTodos.indeterminate = false;
                }
            }

            // Seleccionar todos (global)
            if (seleccionarTodos) {
                seleccionarTodos.addEventListener('change', function() {
                    checkboxes.forEach(cb => {
                        cb.checked = seleccionarTodos.checked;
                    });
                    actualizarSeleccion();
                });
            }

            // Seleccionar todo un programa
            document.querySelectorAll('.seleccionar-programa').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const programa = this.dataset.programa;
                    const checkboxesPrograma = document.querySelectorAll(
                        `.checkbox-postulante[data-programa="${programa}"]`);

                    checkboxesPrograma.forEach(cb => {
                        cb.checked = this.checked;
                    });

                    actualizarSeleccion();
                });
            });

            // Seleccionar página de programa
            document.querySelectorAll('.seleccionar-pagina-programa').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const programa = this.dataset.programa;
                    const tabla = this.closest('table');
                    const checkboxesTabla = tabla.querySelectorAll(
                        `.checkbox-postulante[data-programa="${programa}"]`);

                    checkboxesTabla.forEach(cb => {
                        cb.checked = this.checked;
                    });

                    actualizarSeleccion();
                });
            });

            // Evento para cada checkbox individual
            checkboxes.forEach(cb => {
                cb.addEventListener('change', actualizarSeleccion);
            });

            // Filtro por programa
            document.querySelectorAll('#filtroPrograma .dropdown-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const programa = this.dataset.programa;

                    programasGroup.forEach(group => {
                        if (programa === 'todos' || group.dataset.programa === programa) {
                            group.style.display = 'block';
                        } else {
                            group.style.display = 'none';
                        }
                    });

                    actualizarSeleccion();
                });
            });

            // Conversión masiva
            btnConvertir.addEventListener('click', function() {
                const seleccionados = Array.from(document.querySelectorAll('.checkbox-postulante:checked'))
                    .map(cb => cb.value);

                Swal.fire({
                    title: '¿Confirmar conversión masiva?',
                    html: `
                        <p>Se convertirán <strong>${seleccionados.length} postulante(s)</strong> a usuarios del sistema.</p>
                        <p class="text-muted small">Se creará una cuenta para cada uno con:</p>
                        <ul class="text-muted small text-start">
                            <li>Email: el email del postulante</li>
                            <li>Contraseña: su número de DNI</li>
                            <li>Rol: alumnob</li>
                        </ul>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, convertir',
                    cancelButtonText: 'Cancelar',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch('{{ route('postulantes.ppd.convertir-masivo') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    postulantes: seleccionados
                                })
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(error => {
                                        throw new Error(error.message ||
                                            'Error en la conversión');
                                    });
                                }
                                return response.json();
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Error: ${error.message}`);
                            });
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value.success) {
                        const resultados = result.value.resultados;
                        let mensaje = '';

                        if (resultados.exitos.length > 0) {
                            mensaje +=
                                `<strong class="text-success">${resultados.exitos.length} exitoso(s):</strong><br>`;
                            mensaje += resultados.exitos.slice(0, 5).join('<br>');
                            if (resultados.exitos.length > 5) {
                                mensaje += `<br>... y ${resultados.exitos.length - 5} más`;
                            }
                        }

                        if (resultados.errores.length > 0) {
                            mensaje +=
                                `<br><br><strong class="text-danger">${resultados.errores.length} error(es):</strong><br>`;
                            mensaje += resultados.errores.slice(0, 5).join('<br>');
                            if (resultados.errores.length > 5) {
                                mensaje += `<br>... y ${resultados.errores.length - 5} más`;
                            }
                        }

                        Swal.fire({
                            icon: resultados.errores.length === 0 ? 'success' : 'warning',
                            title: 'Proceso completado',
                            html: mensaje,
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                });
            });

            // Inicializar
            actualizarSeleccion();
        });
    </script>
@endsection
