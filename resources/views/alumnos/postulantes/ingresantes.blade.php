@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid px-4 py-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
            <div>
                <h3 class="text-primary font-weight-bold mb-1">
                    <i class="fas fa-user-graduate me-2"></i>Seleccionar Ingresantes
                </h3>
                <p class="text-muted small mb-0">Selecciona los postulantes que serán convertidos en nuevos estudiantes.</p>
            </div>
            <a href="{{ route('regulares.index') }}" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-circle fa-lg me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-1">¡Error de validación!</strong>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li class="small">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-circle fa-lg me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong>¡Error!</strong> {{ session('error') }}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-triangle fa-lg me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong>{{ session('warning') }}</strong>
                                @if (session('errores_detallados'))
                                    <div class="mt-2">
                                        <button class="btn btn-warning btn-sm" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#detalleErrores">
                                            <i class="fas fa-list me-1"></i>Ver detalles
                                            ({{ count(session('errores_detallados')) }})
                                        </button>
                                        <div class="collapse mt-2" id="detalleErrores">
                                            <div class="card card-body bg-light border-0">
                                                <ul class="mb-0 small">
                                                    @foreach (session('errores_detallados') as $error)
                                                        <li class="text-dark mb-1">{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if (session('duplicados'))
                    <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-users fa-lg me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong>Registros duplicados</strong>
                                <div class="mt-2">
                                    <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#detalleDuplicados">
                                        <i class="fas fa-list me-1"></i>Ver duplicados ({{ count(session('duplicados')) }})
                                    </button>
                                    <div class="collapse mt-2" id="detalleDuplicados">
                                        <div class="card card-body bg-light border-0">
                                            <ul class="mb-0 small">
                                                @foreach (session('duplicados') as $duplicado)
                                                    <li class="text-dark mb-1">{{ $duplicado }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-check-circle fa-lg me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                {{ session('success') }}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <form action="{{ route('postulantes.guardarIngresantes') }}" method="POST" id="formIngresantes">
            @csrf
            {{-- Tarjetas de selección de programas --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="btn-group w-100" role="group">
                                @if (isset($postulantesInicial) && $postulantesInicial->count() > 0)
                                    <button type="button" class="btn btn-outline-primary btn-tab active"
                                        data-tab="tablaInicial">
                                        <i class="fas fa-child me-2"></i>Educación Inicial
                                        <span
                                            class="badge bg-primary rounded-pill ms-2">{{ $postulantesInicial->count() }}</span>
                                    </button>
                                @endif
                                @if (isset($postulantesPrimaria) && $postulantesPrimaria->count() > 0)
                                    <button type="button" class="btn btn-outline-success btn-tab" data-tab="tablaPrimaria">
                                        <i class="fas fa-chalkboard-user me-2"></i>Educación Primaria EIB
                                        <span
                                            class="badge bg-success rounded-pill ms-2">{{ $postulantesPrimaria->count() }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla Inicial --}}
            @if (isset($postulantesInicial) && $postulantesInicial->count() > 0)
                <div id="tablaInicial" class="tabla-programa">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="text-primary mb-0">
                                    <i class="fas fa-child me-2"></i>Lista de Postulantes - Educación Inicial
                                </h5>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAllInicial">
                                    <label class="form-check-label small" for="selectAllInicial">
                                        Seleccionar todos
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50" class="text-center">
                                                <i class="fas fa-check-square"></i>
                                            </th>
                                            <th>Nombre Completo</th>
                                            <th>DNI</th>
                                            <th>Programa</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($postulantesInicial as $postulante)
                                            <tr class="align-middle">
                                                <td class="text-center">
                                                    <input type="checkbox" name="postulantesSeleccionados[]"
                                                        value="{{ $postulante->id }}"
                                                        class="form-check-input postulante-checkbox"
                                                        data-programa="inicial">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary-light rounded-circle me-3">
                                                            <i class="fas fa-user-graduate text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $postulante->apellidos }},
                                                                {{ $postulante->nombres }}</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="small">{{ $postulante->dni }}</code>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                                        <i
                                                            class="fas fa-graduation-cap me-1"></i>{{ $postulante->programa }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-link text-primary seleccionar-unico"
                                                        data-id="{{ $postulante->id }}">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info border-0 shadow-sm" id="noInicial">
                    <i class="fas fa-info-circle me-2"></i>No hay postulantes disponibles para Educación Inicial.
                </div>
            @endif

            {{-- Tabla Primaria --}}
            @if (isset($postulantesPrimaria) && $postulantesPrimaria->count() > 0)
                <div id="tablaPrimaria" class="tabla-programa" style="display: none;">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="text-success mb-0">
                                    <i class="fas fa-chalkboard-user me-2"></i>Lista de Postulantes - Educación Primaria
                                    EIB
                                </h5>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAllPrimaria">
                                    <label class="form-check-label small" for="selectAllPrimaria">
                                        Seleccionar todos
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50" class="text-center">
                                                <i class="fas fa-check-square"></i>
                                            </th>
                                            <th>Nombre Completo</th>
                                            <th>DNI</th>
                                            <th>Programa</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($postulantesPrimaria as $postulante)
                                            <tr class="align-middle">
                                                <td class="text-center">
                                                    <input type="checkbox" name="postulantesSeleccionados[]"
                                                        value="{{ $postulante->id }}"
                                                        class="form-check-input postulante-checkbox"
                                                        data-programa="primaria">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-success-light rounded-circle me-3">
                                                            <i class="fas fa-user-graduate text-success"></i>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $postulante->apellidos }},
                                                                {{ $postulante->nombres }}</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="small">{{ $postulante->dni }}</code>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                                        <i
                                                            class="fas fa-graduation-cap me-1"></i>{{ $postulante->programa }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-link text-success seleccionar-unico"
                                                        data-id="{{ $postulante->id }}">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info border-0 shadow-sm" id="noPrimaria" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i>No hay postulantes disponibles para Educación Primaria EIB.
                </div>
            @endif

            {{-- Barra de acciones fija --}}
            <div class="position-sticky bottom-0 bg-white py-3 border-top shadow-sm" style="z-index: 1000;">
                <div class="container-fluid px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary rounded-pill me-2" id="contadorSeleccionados">0</span>
                            <span class="text-muted small">postulante(s) seleccionado(s)</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm me-2" onclick="deseleccionarTodos()">
                                <i class="fas fa-times me-1"></i>Deseleccionar todos
                            </button>
                            <button type="submit" class="btn btn-success btn-sm" id="btnGuardar">
                                <i class="fas fa-save me-1"></i>Guardar Ingresantes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        /* Estilos personalizados (solo para mejorar la presentación) */
        .avatar-sm {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .bg-primary-light {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .btn-tab.active {
            background-color: var(--bs-primary);
            color: white;
        }

        .btn-tab.active .badge {
            background-color: white !important;
            color: var(--bs-primary);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
            cursor: pointer;
        }

        .position-sticky {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95) !important;
        }
    </style>

    <script>
        // Variables globales
        let contadorInterval;

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tabs
            inicializarTabs();

            // Inicializar selección de filas al hacer clic
            inicializarSeleccionFilas();

            // Inicializar botones de selección individual
            inicializarBotonesIndividuales();

            // Inicializar selección todos por tabla
            inicializarSelectAll();

            // Actualizar contador
            actualizarContador();

            // Auto-cerrar alertas después de 5 segundos
            setTimeout(function() {
                document.querySelectorAll('.alert-dismissible').forEach(alert => {
                    let bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        function inicializarTabs() {
            const tabs = document.querySelectorAll('.btn-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');

                    // Actualizar estado de los tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Mostrar la tabla correspondiente
                    document.querySelectorAll('.tabla-programa').forEach(tabla => {
                        tabla.style.display = 'none';
                    });

                    const tablaActiva = document.getElementById(tabId);
                    if (tablaActiva) {
                        tablaActiva.style.display = 'block';
                    }

                    // Guardar en sessionStorage
                    sessionStorage.setItem('ultimaTabla', tabId);
                });
            });

            // Recuperar última tabla visible
            const ultimaTabla = sessionStorage.getItem('ultimaTabla');
            if (ultimaTabla && document.getElementById(ultimaTabla)) {
                const tabActivo = Array.from(document.querySelectorAll('.btn-tab')).find(
                    tab => tab.getAttribute('data-tab') === ultimaTabla
                );
                if (tabActivo) {
                    tabActivo.click();
                }
            }
        }

        function inicializarSeleccionFilas() {
            document.querySelectorAll('.table tbody tr').forEach(row => {
                row.addEventListener('click', function(e) {
                    // Evitar que se active al hacer clic en el checkbox o botones
                    if (e.target.type !== 'checkbox' && !e.target.closest('.seleccionar-unico')) {
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                            actualizarContador();
                        }
                    }
                });
            });
        }

        function inicializarBotonesIndividuales() {
            document.querySelectorAll('.seleccionar-unico').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const postulanteId = this.getAttribute('data-id');
                    const checkbox = document.querySelector(`input[value="${postulanteId}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        actualizarContador();

                        // Animación de feedback
                        this.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                        setTimeout(() => {
                            this.innerHTML = '<i class="fas fa-check-circle"></i>';
                        }, 500);
                    }
                });
            });
        }

        function inicializarSelectAll() {
            // Select all para Inicial
            const selectAllInicial = document.getElementById('selectAllInicial');
            if (selectAllInicial) {
                selectAllInicial.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('#tablaInicial .postulante-checkbox');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    actualizarContador();
                });
            }

            // Select all para Primaria
            const selectAllPrimaria = document.getElementById('selectAllPrimaria');
            if (selectAllPrimaria) {
                selectAllPrimaria.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('#tablaPrimaria .postulante-checkbox');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    actualizarContador();
                });
            }

            // Escuchar cambios en checkboxes individuales
            document.querySelectorAll('.postulante-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    actualizarContador();
                    actualizarSelectAllEstado();
                });
            });
        }

        function actualizarContador() {
            const checkboxes = document.querySelectorAll('input[name="postulantesSeleccionados[]"]:checked');
            const contador = checkboxes.length;
            const contadorElement = document.getElementById('contadorSeleccionados');

            if (contadorElement) {
                contadorElement.textContent = contador;

                // Cambiar color según cantidad
                if (contador > 0) {
                    contadorElement.classList.remove('bg-secondary');
                    contadorElement.classList.add('bg-success');
                } else {
                    contadorElement.classList.remove('bg-success');
                    contadorElement.classList.add('bg-secondary');
                }
            }

            // Habilitar/deshabilitar botón guardar
            const btnGuardar = document.getElementById('btnGuardar');
            if (btnGuardar) {
                btnGuardar.disabled = contador === 0;
            }
        }

        function actualizarSelectAllEstado() {
            // Para Inicial
            const checkboxesInicial = document.querySelectorAll('#tablaInicial .postulante-checkbox');
            const selectAllInicial = document.getElementById('selectAllInicial');
            if (selectAllInicial && checkboxesInicial.length > 0) {
                const todosChecked = Array.from(checkboxesInicial).every(cb => cb.checked);
                selectAllInicial.checked = todosChecked;
                selectAllInicial.indeterminate = !todosChecked && Array.from(checkboxesInicial).some(cb => cb.checked);
            }

            // Para Primaria
            const checkboxesPrimaria = document.querySelectorAll('#tablaPrimaria .postulante-checkbox');
            const selectAllPrimaria = document.getElementById('selectAllPrimaria');
            if (selectAllPrimaria && checkboxesPrimaria.length > 0) {
                const todosChecked = Array.from(checkboxesPrimaria).every(cb => cb.checked);
                selectAllPrimaria.checked = todosChecked;
                selectAllPrimaria.indeterminate = !todosChecked && Array.from(checkboxesPrimaria).some(cb => cb.checked);
            }
        }

        function deseleccionarTodos() {
            document.querySelectorAll('input[name="postulantesSeleccionados[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            actualizarContador();
            actualizarSelectAllEstado();
        }

        // Validación antes de enviar
        document.getElementById('formIngresantes')?.addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('input[name="postulantesSeleccionados[]"]:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('⚠️ Debes seleccionar al menos un postulante.');
                return false;
            }

            if (!confirm(`¿Estás seguro de convertir ${checkboxes.length} postulante(s) a estudiantes?`)) {
                e.preventDefault();
                return false;
            }
        });
    </script>


@endsection
