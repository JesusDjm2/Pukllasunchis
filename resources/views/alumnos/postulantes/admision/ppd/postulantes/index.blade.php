@extends('layouts.admin')
@section('contenido')
    <!-- En la sección <head> de layouts/admin.blade.php o donde cargas tus CSS -->

    <style>
        table:first-of-type thead tr {
            pointer-events: none;
        }

        .checkbox-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .checkbox-label {
            font-size: 12px;
            margin-top: 2px;
        }
    </style>
    <div class="container-fluid bg-white">
        <!-- Header simplificado -->
        <div class="container-fluid mb-4">
            <div class="row align-items-center gy-3 border-bottom pb-3">
                <!-- Título con icono -->
                <div class="col-12 col-md">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Icono -->
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mr-2"
                            style="width: 48px; height: 48px;">
                            <i class="fas fa-fw fa-users text-white"></i>
                        </div>
                        <!-- Texto -->
                        <div>
                            <h4 class="mb-1 font-weight-bold text-primary">
                                Postulantes PPD 2026
                            </h4>
                            <small class="text-muted">
                                Gestión de postulantes PPD
                            </small>
                        </div>
                    </div>
                </div>

                <!-- En el header, después del botón Volver -->
                <div class="col-12 col-md-auto text-md-end">
                    <a href="{{ route('postulantes.ppd.seleccion-masiva') }}" class="btn btn-success btn-sm me-2">
                        <i class="fas fa-users-cog"></i> Conversión Masiva
                    </a>
                    <a href="{{ url()->previous() }}" class="btn btn-danger btn-sm">
                        Volver
                    </a>
                </div>

            </div>
        </div>


        <!-- Tabla mejorada visualmente -->
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light bg-dark text-white">
                        <tr>
                            <th class="px-4 py-3">Programa</th>
                            <th class="px-4 py-3 text-center">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($postulantesPorPrograma as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info bg-opacity-10 text-info rounded-circle p-2 me-2 mr-2">
                                            <i class="bi bi-mortarboard-fill"></i>
                                        </span>
                                        {{ $item->programa }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center text-white">
                                    <span class="badge bg-info rounded-pill px-3 py-2">
                                        {{ $item->total }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                                    No hay postulantes registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="px-4 py-3 font-weight-bold">TOTAL</td>
                            <td class="px-4 py-3 text-center text-white">
                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                    {{ $totalPostulantes }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-info alert-dismissible fade show">
                {{ session('success') }}
                <button class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card shadow-sm mt-3 border-0 mb-3">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white border-0">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="buscadorPostulantes"
                                class="form-control form-control border-0 bg-light"
                                placeholder="Buscar por Nombre, Apellidos, DNI, Email o Teléfono..." autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="limpiarBusqueda">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div
                            class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mt-2">

                            <small class="text-muted mb-2 mb-md-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Escribe para filtrar en tiempo real. Mínimo 2 caracteres.
                            </small>

                            <span id="contadorResultados" class="badge bg-info text-white">
                            </span>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Postulante</th>
                        <th>Adjuntos</th>
                        <th width="150">Verificaciones</th>
                        <th width="200">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($postulantes as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <!-- Datos del postulante -->
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center mb-2">
                                        <div>
                                            <span class="fw-bold text-uppercase font-weight-bold nombre-postulante">
                                                {{ $p->apellidos }}, {{ $p->nombres }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row g-1 mt-1">
                                        <div class="col-lg-6 col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-id-card me-1"></i> DNI: {{ $p->dni }}
                                            </small>
                                        </div>
                                        <div class="col-lg-6 col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-1"></i> Teléfono: {{ $p->telefono }}
                                            </small>
                                        </div>
                                        <div class="col-lg-6 col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i> Email: {{ $p->email }}
                                            </small>
                                        </div>
                                        <div class="col-lg-6 col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-graduation-cap me-1"></i> Programa:
                                                {{ $p->programa }}
                                            </small>
                                        </div>
                                        <div class="col-lg-6 col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i> Medio de contacto:
                                                {{ $p->medio_conocimiento }}
                                            </small>
                                        </div>
                                        <div class="col-lg-6 col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Fecha inscripción:
                                                {{ \Carbon\Carbon::parse($p->created_at)->locale('es')->translatedFormat('d M Y') }}
                                                <span class="text-muted ms-1">
                                                    ({{ \Carbon\Carbon::parse($p->created_at)->diffForHumans() }})
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    @if ($p->dni_adjunto)
                                        <li class="text-success">
                                            DNI
                                            <a class="text-primary" href="{{ asset($p->dni_adjunto) }}" target="_blank">
                                                (Ver)
                                            </a>
                                        </li>
                                    @else
                                        <em class="text-danger">Falta DNI en archivo</em><br>
                                    @endif

                                    {{-- Certificado --}}
                                    @if ($p->certificado)
                                        <li class="text-success">
                                            Certificado
                                            <a href="{{ asset($p->certificado) }}" target="_blank">
                                                (ver)
                                            </a>
                                        </li>
                                    @else
                                        <em class="text-danger">Falta Certificado</em><br>
                                    @endif

                                    @if ($p->foto)
                                        <li class="text-success">
                                            Foto
                                            <a href="{{ asset($p->foto) }}" target="_blank">
                                                (ver)
                                            </a>
                                        </li>
                                    @else
                                        <em class="text-danger">Falta Foto</em><br>
                                    @endif

                                    @if ($p->titulo)
                                        <li class="text-success">
                                            Título
                                            <a href="{{ asset($p->titulo) }}" target="_blank">
                                                (ver)
                                            </a>
                                        </li>
                                    @else
                                        <em class="text-danger">Falta Título</em><br>
                                    @endif

                                    @if ($p->voucher)
                                        <li class="text-success">
                                            Voucher
                                            <a href="{{ asset($p->voucher) }}" target="_blank">
                                                (ver)
                                            </a>
                                        </li>
                                    @else
                                        <li class="text-danger fw-bold">Falta Voucher</li>
                                    @endif
                                </ul>
                            </td>
                            <td class="text-center">
                                <div class="checkbox-container">
                                    <div class="text-center">
                                        <!-- Verificación 1: Pago -->
                                        <div class="mb-2">
                                            <div class="form-check d-inline-block">
                                                <input type="checkbox" class="form-check-input toggle-apto"
                                                    id="apto-{{ $p->id }}" data-id="{{ $p->id }}"
                                                    data-type="apto" {{ $p->apto ? 'checked' : '' }}>
                                                <label class="form-check-label" for="apto-{{ $p->id }}"
                                                    style="font-size: 12px;">
                                                    Pago verificado
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Verificación 2: Documentos -->
                                        <div class="mb-2">
                                            <div class="form-check d-inline-block">
                                                <input type="checkbox" class="form-check-input toggle-apto"
                                                    id="apto2-{{ $p->id }}" data-id="{{ $p->id }}"
                                                    data-type="apto2" {{ $p->apto2 ? 'checked' : '' }}>
                                                <label class="form-check-label" for="apto2-{{ $p->id }}"
                                                    style="font-size: 12px;">
                                                    Documentos
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Estado general -->
                                        <div class="mt-1">
                                            @if ($p->apto && $p->apto2)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> APTO
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock"></i> PENDIENTE
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                {{-- <a href="{{ route('postulantes.ppd.show', $p) }}"
                                    class="btn btn-sm btn-info shadow-sm mb-2" title="Ver postulante">
                                    <i class="fa fa-eye"></i> Ver registro
                                </a><br>

                                <a href="{{ route('postulantes.ppd.edit', $p) }}"
                                    class="btn btn-sm btn-warning shadow-sm mb-2" title="Editar postulante">
                                    <i class="fa fa-edit"></i> Editar
                                </a>

                                <form action="{{ route('enviarcorreo.ppd', $p->id) }}" method="POST"
                                    style="display: inline; width: 100%;" class="form-envio-correo"
                                    data-postulante-id="{{ $p->id }}">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-sm mt-1 btn-enviar-correo
                       {{ $p->enviado ? 'btn-secondary' : 'btn-primary' }} 
                       {{ $p->apto && $p->apto2 ? '' : 'disabled' }}"
                                        {{ $p->apto && $p->apto2 ? '' : 'disabled' }}
                                        data-postulante-id="{{ $p->id }}" data-initial-apto="{{ $p->apto }}"
                                        data-initial-apto2="{{ $p->apto2 }}"
                                        title="{{ $p->apto && $p->apto2 ? '' : 'Debe estar APTO en ambas verificaciones' }}">
                                        <i class="fa fa-envelope"></i>
                                        <span class="btn-text">
                                            {{ $p->enviado ? 'Reenviar' : 'Enviar correo' }}
                                        </span>
                                    </button>
                                </form> --}}
                                <div class="d-flex flex-column align-items-stretch gap-2">
                                    <!-- Botón Ver -->
                                    <a href="{{ route('postulantes.ppd.show', $p) }}"
                                        class="btn btn-sm btn-info shadow-sm w-100 mb-2" title="Ver postulante">
                                        <i class="fa fa-eye"></i> Ver registro
                                    </a>

                                    <!-- Botón Editar -->
                                    <a href="{{ route('postulantes.ppd.edit', $p) }}"
                                        class="btn btn-sm btn-warning shadow-sm w-100 mb-2" title="Editar postulante">
                                        <i class="fa fa-edit"></i> Editar
                                    </a>

                                    <!-- Formulario de correo -->
                                    <form action="{{ route('enviarcorreo.ppd', $p->id) }}" method="POST"
                                        class="form-envio-correo w-100" data-postulante-id="{{ $p->id }}">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm w-100 btn-enviar-correo
                {{ $p->enviado ? 'btn-secondary' : 'btn-primary' }} 
                {{ $p->apto && $p->apto2 ? '' : 'disabled' }}"
                                            {{ $p->apto && $p->apto2 ? '' : 'disabled' }}
                                            data-postulante-id="{{ $p->id }}"
                                            data-initial-apto="{{ $p->apto }}"
                                            data-initial-apto2="{{ $p->apto2 }}"
                                            title="{{ $p->apto && $p->apto2 ? '' : 'Debe estar APTO en ambas verificaciones' }}">
                                            <i class="fa fa-envelope"></i>
                                            <span class="btn-text">
                                                {{ $p->enviado ? 'Reenviar' : 'Enviar correo' }}
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay postulantes registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buscador = document.getElementById('buscadorPostulantes');
            const limpiarBtn = document.getElementById('limpiarBusqueda');

            // Seleccionar SOLO la tabla de postulantes (la segunda tabla)
            const tablaPostulantes = document.querySelectorAll('.table-responsive table')[1];
            if (!tablaPostulantes) return;

            const tbody = tablaPostulantes.querySelector('tbody');
            const filas = Array.from(tbody.querySelectorAll('tr'));

            // Crear contador de resultados
            let contadorResultados = document.getElementById('contadorResultados');
            if (!contadorResultados) {
                contadorResultados = document.createElement('span');
                contadorResultados.id = 'contadorResultados';
                contadorResultados.className = 'badge bg-info ms-2';
                buscador.closest('.col-md-12').appendChild(contadorResultados);
            }

            function normalizarTexto(texto) {
                if (!texto) return '';
                return texto.toString()
                    .toLowerCase()
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, "")
                    .trim();
            }

            function obtenerTextoPostulante(fila) {
                const celdaPostulante = fila.cells[1];
                if (!celdaPostulante) return '';
                return celdaPostulante.innerText || celdaPostulante.textContent;
            }

            function obtenerTelefonoPostulante(fila) {
                // Asumiendo que el teléfono está en la celda con índice 2 (tercera columna)
                // Ajusta el índice según la estructura real de tu tabla
                const celdaTelefono = fila.cells[2];
                if (!celdaTelefono) return '';
                return celdaTelefono.innerText || celdaTelefono.textContent;
            }

            function filtrarPostulantes() {
                const termino = normalizarTexto(buscador.value);
                let contador = 0;

                if (termino.length < 2) {
                    filas.forEach(fila => {
                        fila.style.display = '';
                        contador++;
                    });
                } else {
                    filas.forEach(fila => {
                        // Ignorar filas con colspan (como encabezados o mensajes)
                        if (fila.cells.length === 1 && fila.cells[0].colSpan > 1) {
                            fila.style.display = '';
                            contador++;
                            return;
                        }

                        const textoPostulante = normalizarTexto(obtenerTextoPostulante(fila));
                        const telefonoPostulante = normalizarTexto(obtenerTelefonoPostulante(fila));

                        // Buscar en NOMBRE y TELÉFONO
                        if (textoPostulante.includes(termino) || telefonoPostulante.includes(termino)) {
                            fila.style.display = '';
                            contador++;
                        } else {
                            fila.style.display = 'none';
                        }
                    });
                }

                // Manejar mensaje de no resultados
                const filaNoResultados = tbody.querySelector('tr.no-resultados');

                if (contador === 0 && termino.length >= 2) {
                    if (!filaNoResultados) {
                        const nuevaFila = document.createElement('tr');
                        nuevaFila.className = 'no-resultados';
                        nuevaFila.innerHTML = `
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-search fa-2x text-muted mb-2"></i><br>
                            No se encontraron postulantes
                        </td>
                    `;
                        tbody.appendChild(nuevaFila);
                    }
                } else {
                    if (filaNoResultados) {
                        filaNoResultados.remove();
                    }
                }

                contadorResultados.textContent = `Mostrando ${contador} postulante${contador !== 1 ? 's' : ''}`;
            }

            function limpiarBusqueda() {
                buscador.value = '';

                filas.forEach(fila => {
                    fila.style.display = '';
                });

                const filaNoResultados = tbody.querySelector('tr.no-resultados');
                if (filaNoResultados) {
                    filaNoResultados.remove();
                }

                contadorResultados.textContent = `Mostrando ${filas.length} postulantes`;
            }

            let timeoutId;
            buscador.addEventListener('input', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(filtrarPostulantes, 300);
            });

            limpiarBtn.addEventListener('click', limpiarBusqueda);

            buscador.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filtrarPostulantes();
                }
            });

            // Inicializar contador
            contadorResultados.textContent = `Mostrando ${filas.length} postulantes`;
        });
    </script>
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buscador = document.getElementById('buscadorPostulantes');
            const limpiarBtn = document.getElementById('limpiarBusqueda');

            // Seleccionar SOLO la tabla de postulantes (la segunda tabla)
            const tablaPostulantes = document.querySelectorAll('.table-responsive table')[1];
            if (!tablaPostulantes) return;

            const tbody = tablaPostulantes.querySelector('tbody');
            const filas = Array.from(tbody.querySelectorAll('tr'));

            // Crear contador de resultados
            let contadorResultados = document.getElementById('contadorResultados');
            if (!contadorResultados) {
                contadorResultados = document.createElement('span');
                contadorResultados.id = 'contadorResultados';
                contadorResultados.className = 'badge bg-info ms-2';
                buscador.closest('.col-md-12').appendChild(contadorResultados);
            }

            function normalizarTexto(texto) {
                if (!texto) return '';
                return texto.toString()
                    .toLowerCase()
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, "")
                    .trim();
            }

            function obtenerTextoPostulante(fila) {
                const celdaPostulante = fila.cells[1];
                if (!celdaPostulante) return '';
                return celdaPostulante.innerText || celdaPostulante.textContent;
            }

            function filtrarPostulantes() {
                const termino = normalizarTexto(buscador.value);
                let contador = 0;

                if (termino.length < 2) {
                    filas.forEach(fila => {
                        fila.style.display = '';
                        contador++;
                    });
                } else {
                    filas.forEach(fila => {
                        if (fila.cells.length === 1 && fila.cells[0].colSpan > 1) {
                            fila.style.display = '';
                            contador++;
                            return;
                        }

                        const textoPostulante = normalizarTexto(obtenerTextoPostulante(fila));

                        if (textoPostulante.includes(termino)) {
                            fila.style.display = '';
                            contador++;
                        } else {
                            fila.style.display = 'none';
                        }
                    });
                }

                // Manejar mensaje de no resultados
                const filaNoResultados = tbody.querySelector('tr.no-resultados');

                if (contador === 0 && termino.length >= 2) {
                    if (!filaNoResultados) {
                        const nuevaFila = document.createElement('tr');
                        nuevaFila.className = 'no-resultados';
                        nuevaFila.innerHTML = `
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-2"></i><br>
                        No se encontraron postulantes
                    </td>
                `;
                        tbody.appendChild(nuevaFila);
                    }
                } else {
                    if (filaNoResultados) {
                        filaNoResultados.remove();
                    }
                }

                contadorResultados.textContent = `Mostrando ${contador} postulante${contador !== 1 ? 's' : ''}`;
            }

            function limpiarBusqueda() {
                buscador.value = '';

                filas.forEach(fila => {
                    fila.style.display = '';
                });

                const filaNoResultados = tbody.querySelector('tr.no-resultados');
                if (filaNoResultados) {
                    filaNoResultados.remove();
                }

                contadorResultados.textContent = `Mostrando ${filas.length} postulantes`;
            }

            let timeoutId;
            buscador.addEventListener('input', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(filtrarPostulantes, 300);
            });

            limpiarBtn.addEventListener('click', limpiarBusqueda);

            buscador.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filtrarPostulantes();
                }
            });

            // Inicializar contador
            contadorResultados.textContent = `Mostrando ${filas.length} postulantes`;
        });
    </script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para mostrar notificación elegante
            function mostrarNotificacion(titulo, mensaje, tipo = 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: tipo,
                    title: mensaje
                });
            }

            // Función para mostrar confirmación elegante
            function mostrarConfirmacion(mensaje) {
                return Swal.fire({
                    title: '¿Está seguro?',
                    text: mensaje,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar'
                });
            }

            // Función para actualizar estado del botón de correo
            function actualizarBotonCorreo(fila, apto, apto2) {
                const btnCorreo = fila.querySelector('.btn-enviar-correo');
                const badgeEstado = fila.querySelector('.badge');

                if (!btnCorreo) return;

                if (apto && apto2) {
                    btnCorreo.disabled = false;
                    btnCorreo.classList.remove('disabled');
                    btnCorreo.title = '';

                    if (badgeEstado) {
                        badgeEstado.className = 'badge badge-success';
                        badgeEstado.innerHTML = '<i class="fas fa-check-circle"></i> APTO';
                    }
                } else {
                    btnCorreo.disabled = true;
                    btnCorreo.classList.add('disabled');
                    btnCorreo.title = 'Debe estar APTO en ambas verificaciones';

                    if (badgeEstado) {
                        badgeEstado.className = 'badge badge-warning';
                        badgeEstado.innerHTML = '<i class="fas fa-clock"></i> PENDIENTE';
                    }
                }
            }

            // Event listener para los checkboxes
            document.addEventListener('change', function(e) {
                if (!e.target.classList.contains('toggle-apto')) return;

                const checkbox = e.target;
                const fila = checkbox.closest('tr');
                const postulanteId = checkbox.dataset.id;
                const tipo = checkbox.dataset.type;
                const valor = checkbox.checked ? 1 : 0;

                const checkboxApto = fila.querySelector('[data-type="apto"]');
                const checkboxApto2 = fila.querySelector('[data-type="apto2"]');

                const aptoActual = checkboxApto ? checkboxApto.checked : false;
                const apto2Actual = checkboxApto2 ? checkboxApto2.checked : false;
                actualizarBotonCorreo(fila, aptoActual, apto2Actual);

                fetch(`/postulantes/${postulanteId}/apto`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            [tipo]: valor
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Actualizado:', data);
                        const btnCorreo = fila.querySelector('.btn-enviar-correo');
                        if (btnCorreo) {
                            if (tipo === 'apto') {
                                btnCorreo.setAttribute('data-initial-apto', valor ? '1' : '0');
                            } else if (tipo === 'apto2') {
                                btnCorreo.setAttribute('data-initial-apto2', valor ? '1' : '0');
                            }
                        }

                        // Notificación de éxito
                        mostrarNotificacion('Éxito', 'Estado actualizado correctamente', 'success');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        checkbox.checked = !checkbox.checked;
                        const aptoRevertido = checkboxApto ? checkboxApto.checked : false;
                        const apto2Revertido = checkboxApto2 ? checkboxApto2.checked : false;
                        actualizarBotonCorreo(fila, aptoRevertido, apto2Revertido);
                        mostrarNotificacion('Error', 'Error al actualizar el estado', 'error');
                    });
            });

            document.addEventListener('submit', async function(e) {
                if (!e.target.classList.contains('form-envio-correo')) return;

                e.preventDefault();

                const form = e.target;
                const btn = form.querySelector('button[type="submit"]');

                const fila = form.closest('tr');
                const checkboxApto = fila.querySelector('[data-type="apto"]');
                const checkboxApto2 = fila.querySelector('[data-type="apto2"]');

                if (!checkboxApto || !checkboxApto2) {
                    mostrarNotificacion('Error', 'No se encontraron los campos de verificación',
                        'error');
                    return false;
                }

                if (!checkboxApto.checked || !checkboxApto2.checked) {
                    mostrarNotificacion('Advertencia',
                        'El postulante debe estar verificado en ambos campos', 'warning');
                    return false;
                }

                const nombreElement = fila.querySelector('strong');
                const nombrePostulante = nombreElement ? nombreElement.textContent : 'este postulante';

                const confirmacion = await mostrarConfirmacion(`¿Enviar correo a ${nombrePostulante}?`);

                if (!confirmacion.isConfirmed) {
                    return false;
                }

                const originalHTML = btn.innerHTML;
                const originalText = btn.querySelector('.btn-text') ? btn.querySelector('.btn-text')
                    .textContent : '';
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
                btn.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });

                    const contentType = response.headers.get('content-type');
                    let data;

                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        throw new Error('El servidor devolvió HTML en lugar de JSON');
                    }

                    if (data.success) {
                        mostrarNotificacion('Éxito', data.success, 'success');

                        const btnText = btn.querySelector('.btn-text');
                        if (btnText) {
                            btnText.textContent = 'Reenviar';
                        }
                        btn.className = 'btn btn-sm mt-1 btn-secondary';
                        btn.title = '';
                        btn.setAttribute('data-initial-enviado', '1');

                    } else if (data.error) {
                        mostrarNotificacion('Error', data.error, 'error');
                    }

                } catch (error) {
                    console.error('Error:', error);

                    if (error.message.includes('HTML') || error.message.includes('JSON')) {
                        window.location.href = form.action + '?ajax=true';
                    } else {
                        mostrarNotificacion('Error', 'Error al enviar el correo: ' + error.message,
                            'error');
                    }
                } finally {
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;

                    if (originalText && btn.querySelector('.btn-text')) {
                        btn.querySelector('.btn-text').textContent = originalText;
                    }
                }
            });

            // Inicializar estado de los botones
            document.querySelectorAll('tr').forEach(fila => {
                const checkboxApto = fila.querySelector('[data-type="apto"]');
                const checkboxApto2 = fila.querySelector('[data-type="apto2"]');

                if (checkboxApto && checkboxApto2) {
                    const apto = checkboxApto.checked;
                    const apto2 = checkboxApto2.checked;
                    actualizarBotonCorreo(fila, apto, apto2);
                }
            });

            // Mostrar notificación de sesión de Laravel si existe
            @if (session('success'))
                mostrarNotificacion('Éxito', '{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                mostrarNotificacion('Error', '{{ session('error') }}', 'error');
            @endif

            @if (session('warning'))
                mostrarNotificacion('Advertencia', '{{ session('warning') }}', 'warning');
            @endif

            @if (session('info'))
                mostrarNotificacion('Información', '{{ session('info') }}', 'info');
            @endif
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
