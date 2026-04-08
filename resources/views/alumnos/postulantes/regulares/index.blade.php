@extends('layouts.admin')
@section('contenido')
    <style>
        .table-responsive {
            max-height: 92vh;
            overflow-y: auto;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #212529;
            color: #fff;
            z-index: 2;
            pointer-events: none;
        }

        /* Estilos adicionales para el buscador */
        .input-group-text.bg-primary {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #limpiarBusqueda {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .card-filter {
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            border-left: 4px solid #007bff;
        }

        /* Estilos mejorados para checkbox */
        .checkbox-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 90px;
            padding: 8px 0;
        }

        .checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0 0 4px 0;
            cursor: pointer;
            position: relative;
            opacity: 1;
            transform: scale(1.1);
        }

        .checkbox-label {
            font-size: 11px;
            line-height: 1.2;
            color: #495057;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            display: block;
            width: 100%;
        }

        /* Estilo para el segundo checkbox */
        .checkbox-container-apto2 {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 90px;
            padding: 8px 0;
            border-top: 1px dashed #dee2e6;
            margin-top: 5px;
        }

        .checkbox-container-apto2 input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0 0 4px 0;
            cursor: pointer;
            position: relative;
            opacity: 1;
            transform: scale(1.1);
        }

        .checkbox-label-apto2 {
            font-size: 11px;
            line-height: 1.2;
            color: #28a745;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
            display: block;
            width: 100%;
        }

        /* Indicador visual de estado combinado */
        .apto-combinado-indicator {
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 10px;
            background-color: #f8f9fa;
            margin-top: 5px;
            text-align: center;
        }

        /* Asegurar alineación vertical */
        td.align-middle {
            vertical-align: middle !important;
        }

        /* Estilo mejorado para filas de beca */
        .fila-beca {
            background-color: #d1e7dd !important;
            /* Verde más suave */
            border-left: 4px solid #198754 !important;
            /* Borde izquierdo verde */
        }

        .fila-beca:hover {
            background-color: #c1dfd3 !important;
            /* Más oscuro al hover */
        }

        /* Badge de Beca más visible */
        .badge-beca {
            background-color: #198754;
            color: white;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 5px;
        }

        /* Mejorar contraste en badges */
        .badge.bg-success {
            background-color: #198754 !important;
        }

        .badge.bg-secondary {
            background-color: #6c757d !important;
        }

        /* Estilo para botón deshabilitado */
        .btn-apto-action.disabled {
            opacity: 0.5;
            pointer-events: none;
            cursor: not-allowed;
        }

        /* Tooltip personalizado para estado de aptos */
        .apto-status-tooltip {
            cursor: help;
            border-bottom: 1px dotted #6c757d;
        }
    </style>

    <div class="container-fluid bg-white pt-2">
        <!-- Header mejorado (inspirado en tu otra vista) -->
        <div class="container-fluid mb-4 p-0">
            <div class="row align-items-center gy-3 border-bottom pb-3">
                <div class="col-12 col-md">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mr-2"
                            style="width: 48px; height: 48px; background-color: rgba(0,123,255,0.1);">
                            <i class="fas fa-fw fa-user-graduate text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 font-weight-bold text-primary">
                                {{ $admision->nombre ?? 'Sin admisión activa' }}
                            </h4>
                            </h4>
                            <small class="text-muted">
                                Gestión de postulantes y verificación de documentos FID
                            </small>
                        </div>
                    </div>
                </div>

                @if ($postulantes->isNotEmpty())
                    <div class="col-12 col-md-auto text-md-end">
                        <a href="{{ route('postulantes.ingresantes') }}" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-user-plus"></i> Crear ingresantes
                        </a>
                        <form action="{{ route('postulantes.exportar') }}" method="GET" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm me-2">
                                <i class="fas fa-file-csv"></i> Exportar CSV
                            </button>
                        </form>
                        <a href="{{ url()->previous() }}" class="btn btn-danger btn-sm">
                            Volver
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Mensaje de éxito -->
        <div class="row">
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tabla de resumen (Programas) -->
        <div class="row">
            <div class="col-lg-12 mt-2">
                @php
                    $conteoProgramas = $postulantes->groupBy('programa')->map->count();
                    $totalSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca)->count();
                    $conteoBecas = $postulantes->filter(fn($postulante) => $postulante->estudio_beca)->count();
                    $postulantesSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca);
                    $conteoFiltrado = $postulantesSinBeca->groupBy('programa')->map->count();
                @endphp

                <div class="card shadow-sm border-0 mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th class="border-0"><i class="fas fa-chart-pie mr-2"></i>Resumen por Programas</th>
                                    <th class="text-center border-0">Total</th>
                                    <th class="text-center border-0">Sin Beca</th>
                                    <th class="text-center border-0">Becas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($conteoProgramas as $programa => $cantidad)
                                    @php
                                        $becas = $cantidad - ($conteoFiltrado[$programa] ?? 0);
                                        $porcentajeBecas = $cantidad > 0 ? round(($becas / $cantidad) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2 text-primary">
                                                    <i class="fas fa-graduation-cap"></i>
                                                </div>
                                                <strong>{{ $programa }}</strong>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="font-weight-bold">{{ $cantidad }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="px-3 py-2">
                                                {{ $conteoFiltrado[$programa] ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="px-3 py-2">
                                                {{ $becas }}
                                            </span>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td class="align-middle">
                                        <i class="fas fa-chart-pie mr-1 text-primary"></i><strong>Totales</strong>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span
                                            class="badge bg-primary text-white px-3 py-2">{{ $postulantes->count() }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-secondary text-white px-3 py-2">{{ $totalSinBeca }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-success text-white px-3 py-2">{{ $conteoBecas }}</span>
                                    </td>

                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <!-- BUSCADOR MEJORADO (integrado de tu otra vista) -->
        <!-- BUSCADOR MEJORADO con filtro de becas integrado -->
        @if ($postulantes->isNotEmpty())
            <div class="card shadow-sm border-0 mb-3 card-filter">
                <div class="card-body py-3">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Barra de búsqueda principal -->
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

                            <!-- Fila con filtros e información (mejor alineado) -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between mt-3">
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-2 d-none d-md-inline mr-2">
                                        <i class="fas fa-info-circle me-1 text-primary"></i>
                                        Mínimo 2 caracteres
                                    </small>
                                    <span id="contadorResultados" class="badge bg-info text-white px-3 py-2">
                                        {{ $postulantes->count() }} de {{ $postulantes->count() }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center mb-2 mb-sm-0">
                                    <span class="text-muted me-2 small mr-2">
                                        <i class="fas fa-filter me-1"></i>Filtrar por beca:
                                    </span>
                                    <select id="filtroBecas" class="form-control form-control-sm"
                                        style="width: auto; min-width: 140px;">
                                        <option value="todos">📋 Todos los postulantes</option>
                                        <option value="con_beca">🎓 Con beca</option>
                                        <option value="sin_beca">📝 Sin beca</option>
                                    </select>
                                </div>


                            </div>

                            <!-- Texto informativo visible solo en móvil -->
                            <small class="text-muted d-block d-md-none mt-2">
                                <i class="fas fa-info-circle me-1 text-primary"></i>
                                Mínimo 2 caracteres para buscar
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabla principal de postulantes -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tablaPostulantes">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>Postulantes</th>
                                <th>Adjuntos</th>
                                <th>Datos que faltan</th>
                                <th style="width: 100px;">Verificado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTabla">
                            @forelse ($postulantes->sortByDesc('created_at') as $key => $postulante)
                                @php
                                    $atributos = $postulante->getAttributes();

                                    $excluir = [
                                        'id',
                                        'declaracion_jurada_salud_pdf',
                                        'declaracion_jurada-documentos_pdf',
                                        'declaracion_jurada_conectividad_pdf',
                                        'created_at',
                                        'updated_at',
                                        'admin_fids_id',
                                        'observaciones',
                                    ];

                                    $faltantes = collect($atributos)
                                        ->reject(function ($valor, $campo) use ($excluir) {
                                            return in_array($campo, $excluir) || !empty($valor);
                                        })
                                        ->keys()
                                        ->toArray();

                                    // Formatear fecha en español
                                    setlocale(LC_TIME, 'es_ES.utf8', 'es_ES', 'esp');
                                    $fechaRegistro = $postulante->created_at->timezone('America/Lima');
                                    $fechaFormateada =
                                        $fechaRegistro->format('d') .
                                        ' de ' .
                                        ucfirst($fechaRegistro->translatedFormat('F')) .
                                        ' del ' .
                                        $fechaRegistro->format('Y');

                                    // Verificar si ambos aptos están en true
                                    $ambosAptos = $postulante->apto && $postulante->apto2;
                                @endphp
                                <tr data-search="{{ strtolower($postulante->nombres . ' ' . $postulante->apellidos . ' ' . $postulante->dni . ' ' . $postulante->email . ' ' . $postulante->numero) }}"
                                    class="{{ $postulante->estudio_beca ? 'fila-beca' : '' }}">

                                    <td class="align-middle contador">{{ $key + 1 }}</td>

                                    <td class="datos-postulante align-middle">
                                        <strong class="nombre-postulante">{{ $postulante->apellidos }},
                                            {{ $postulante->nombres }}</strong>
                                        @if ($postulante->estudio_beca)
                                            <span class="badge-beca">BECA</span>
                                        @endif
                                        <ul class="mt-2 mb-0">
                                            <li><i class="fas fa-id-card fa-fw text-muted mr-1"></i> DNI:
                                                {{ $postulante->dni }}
                                                @if ($postulante->edad)
                                                    <small class="text-muted">({{ $postulante->edad }} años)</small>
                                                @else
                                                    <span class="text-muted">(Sin fecha)</span>
                                                @endif
                                            </li>
                                            <li><i class="fas fa-graduation-cap fa-fw text-muted mr-1"></i> Programa:
                                                {{ $postulante->programa }}</li>
                                            <li><i class="fas fa-envelope fa-fw text-muted mr-1"></i> Email:
                                                {{ $postulante->email }}</li>
                                            <li><i class="fas fa-phone fa-fw text-muted mr-1"></i> Teléfono:
                                                {{ $postulante->numero }}</li>
                                            <li><i class="fas fa-tag fa-fw text-muted mr-1"></i> Beca:
                                                @if ($postulante->estudio_beca)
                                                    <span class="badge bg-success text-white">Sí</span>
                                                @else
                                                    <span class="badge bg-secondary text-white">No</span>
                                                @endif
                                            </li>
                                            <li><i class="fas fa-info-circle fa-fw text-muted mr-1"></i> Contacto:
                                                {{ !empty($postulante->contacto) ? $postulante->contacto : 'Sin datos' }}
                                            </li>
                                            <li><i class="fas fa-calendar-alt fa-fw text-muted mr-1"></i> Registro:
                                                <span class="fw-medium">{{ $fechaFormateada }}</span>
                                                <small
                                                    class="text-muted ms-1">({{ $postulante->created_at->diffForHumans() }})</small>
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle fa-fw text-muted mr-1"></i> Estado
                                                verificación:
                                                <span class="apto-status-tooltip"
                                                    title="Apto 1 (Pago): {{ $postulante->apto ? '✅ Verificado' : '❌ Pendiente' }} | Apto 2: {{ $postulante->apto2 ? '✅ Verificado' : '❌ Pendiente' }}">
                                                    @if ($ambosAptos)
                                                        <span class="badge bg-success text-white">Completo</span>
                                                    @elseif($postulante->apto || $postulante->apto2)
                                                        <span class="badge bg-warning text-dark">Parcial</span>
                                                    @else
                                                        <span class="badge bg-danger text-white">Pendiente</span>
                                                    @endif
                                                </span>
                                            </li>
                                        </ul>
                                    </td>

                                    <td class="align-middle">
                                        <ul class="mb-0" style="list-style: none; padding-left: 0;">
                                            <li class="mb-1">
                                                @if ($postulante->voucher_pago)
                                                    <a href="{{ asset($postulante->voucher_pago) }}" target="_blank"
                                                        class="text-success">
                                                        <i class="fas fa-check-circle"></i> Voucher
                                                    </a>
                                                @else
                                                    <em class="text-danger"><i class="fas fa-times-circle"></i> Sin
                                                        voucher</em>
                                                @endif
                                            </li>
                                            <li class="mb-1">
                                                @if ($postulante->foto)
                                                    <a href="{{ asset($postulante->foto) }}" target="_blank"
                                                        class="text-success">
                                                        <i class="fas fa-check-circle"></i> Foto
                                                    </a>
                                                @else
                                                    <em class="text-danger"><i class="fas fa-times-circle"></i> Sin
                                                        foto</em>
                                                @endif
                                            </li>
                                            <li class="mb-1">
                                                @if ($postulante->dni_pdf)
                                                    <a href="{{ asset($postulante->dni_pdf) }}" target="_blank"
                                                        class="text-success">
                                                        <i class="fas fa-check-circle"></i> DNI
                                                    </a>
                                                @else
                                                    <em class="text-danger"><i class="fas fa-times-circle"></i> Sin
                                                        DNI</em>
                                                @endif
                                            </li>
                                            <li class="mb-1">
                                                @if ($postulante->partida_nacimiento_pdf)
                                                    <a href="{{ asset($postulante->partida_nacimiento_pdf) }}"
                                                        target="_blank" class="text-success">
                                                        <i class="fas fa-check-circle"></i> Partida
                                                    </a>
                                                @else
                                                    <em class="text-danger"><i class="fas fa-times-circle"></i> Sin
                                                        Partida</em>
                                                @endif
                                            </li>
                                            <li class="mb-1">
                                                @if ($postulante->certificado_secundaria_pdf)
                                                    <a href="{{ asset($postulante->certificado_secundaria_pdf) }}"
                                                        target="_blank" class="text-success">
                                                        <i class="fas fa-check-circle"></i> Certificado
                                                    </a>
                                                @else
                                                    <em class="text-danger"><i class="fas fa-times-circle"></i> Sin
                                                        Certificado</em>
                                                @endif
                                            </li>
                                        </ul>
                                    </td>

                                    <td class="align-middle">
                                        @if (count($postulante->faltantes ?? []) > 0)
                                            <span class="badge bg-danger mb-2 text-white">Faltan
                                                {{ count($postulante->faltantes) }} datos</span>
                                            <ul class="mt-2 mb-0 small text-danger ps-3">
                                                @foreach ($postulante->faltantes as $campo)
                                                    <li>{{ ucfirst(str_replace('_', ' ', $campo)) }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="badge bg-success text-white p-2"><i
                                                    class="fa fa-check fa-sm"></i> Completo </span>
                                        @endif
                                    </td>

                                    <td class="text-center align-middle">
                                        <div class="checkbox-container">
                                            <input type="checkbox" class="form-check-input toggle-apto"
                                                data-id="{{ $postulante->id }}" data-type="apto1"
                                                {{ $postulante->apto ? 'checked' : '' }}>
                                            <small class="checkbox-label">Pago verificado</small>
                                        </div>

                                        <!-- Segundo checkbox (apto2) -->
                                        <div class="checkbox-container-apto2">
                                            <input type="checkbox" class="form-check-input toggle-apto2"
                                                data-id="{{ $postulante->id }}" data-type="apto2"
                                                {{ $postulante->apto2 ? 'checked' : '' }}>
                                            <small class="checkbox-label-apto2">Documentos verificados</small>
                                        </div>

                                        <!-- Indicador de estado combinado -->
                                        <div class="apto-combinado-indicator">
                                            @if ($ambosAptos)
                                                <span class="text-success"><i class="fas fa-check-circle"></i> Listo para
                                                    enviar</span>
                                            @else
                                                <span class="text-muted">Falta {{ !$postulante->apto ? 'pago' : '' }}
                                                    {{ !$postulante->apto && !$postulante->apto2 ? 'y' : '' }}
                                                    {{ !$postulante->apto2 ? 'documentos' : '' }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <div class="d-flex flex-column gap-1">
                                            <a href="{{ route('regulares.show', $postulante->id) }}"
                                                class="btn btn-sm btn-info mb-2">
                                                <i class="fa fa-eye"></i> Ver
                                            </a>
                                            <a href="{{ route('regulares.enviarCorreo', $postulante->id) }}"
                                                class="btn btn-sm btn-apto-action mb-2 {{ $postulante->observaciones ? 'btn-secondary' : 'btn-primary' }}
                            {{ $ambosAptos ? '' : 'disabled' }}">
                                                <i class="fa fa-envelope"></i>
                                                {{ $postulante->observaciones ? 'Reenviar' : 'Enviar' }}
                                            </a>

                                            <a href="{{ route('regulares.edit', $postulante->id) }}"
                                                class="btn btn-sm btn-warning mb-2">
                                                <i class="fa fa-edit"></i> Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox d-block mb-2" style="font-size: 2rem;"></i>
                                        No hay postulantes registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buscador = document.getElementById('buscadorPostulantes');
            const limpiarBtn = document.getElementById('limpiarBusqueda');
            const tabla = document.getElementById('cuerpoTabla');
            const contadorSpan = document.getElementById('contadorResultados');
            const filtroBecas = document.getElementById('filtroBecas');

            if (!buscador || !tabla) return;

            const filas = Array.from(tabla.querySelectorAll('tr[data-search]'));
            const totalFilas = filas.length;

            // Función mejorada para obtener CSRF token
            function getCsrfToken() {
                // Intentar obtener del meta tag
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                if (metaToken) {
                    return metaToken.getAttribute('content');
                }

                // Intentar obtener de un input oculto
                const inputToken = document.querySelector('input[name="_token"]');
                if (inputToken) {
                    return inputToken.value;
                }

                console.error('No se pudo encontrar el token CSRF');
                return null;
            }

            const csrfToken = getCsrfToken();

            function actualizarContador(filasVisibles) {
                if (contadorSpan) {
                    contadorSpan.textContent = `${filasVisibles} de ${totalFilas}`;

                    if (filasVisibles === 0) {
                        contadorSpan.className = 'badge bg-danger text-white px-3 py-2';
                    } else if (filasVisibles < totalFilas) {
                        contadorSpan.className = 'badge bg-warning text-dark px-3 py-2';
                    } else {
                        contadorSpan.className = 'badge bg-info text-white px-3 py-2';
                    }
                }
            }

            function filtrarTabla() {
                const texto = buscador.value.toLowerCase().trim();
                const filtroBeca = filtroBecas ? filtroBecas.value : 'todos';

                let filasVisibles = 0;

                filas.forEach(fila => {
                    const tieneBeca = fila.classList.contains('fila-beca');
                    let pasaFiltroBeca = true;

                    if (filtroBeca === 'con_beca') {
                        pasaFiltroBeca = tieneBeca;
                    } else if (filtroBeca === 'sin_beca') {
                        pasaFiltroBeca = !tieneBeca;
                    }

                    if (!pasaFiltroBeca) {
                        fila.style.display = 'none';
                        return;
                    }

                    if (texto.length >= 2) {
                        const datos = fila.getAttribute('data-search') || '';
                        const terminos = texto.split(/\s+/);
                        const coincide = terminos.every(termino => datos.includes(termino));

                        if (coincide) {
                            fila.style.display = '';
                            filasVisibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    } else {
                        fila.style.display = '';
                        filasVisibles++;
                    }
                });

                actualizarContador(filasVisibles);
            }

            function limpiarBusqueda() {
                buscador.value = '';
                if (filtroBecas) filtroBecas.value = 'todos';
                filtrarTabla();
                buscador.focus();
            }

            buscador.addEventListener('input', filtrarTabla);
            if (filtroBecas) filtroBecas.addEventListener('change', filtrarTabla);
            if (limpiarBtn) limpiarBtn.addEventListener('click', limpiarBusqueda);

            actualizarContador(totalFilas);

            buscador.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') limpiarBusqueda();
            });

            function actualizarBotonEnviar(fila) {
                const apto1 = fila.querySelector('.toggle-apto')?.checked || false;
                const apto2 = fila.querySelector('.toggle-apto2')?.checked || false;
                const ambosAptos = apto1 && apto2;

                const btnEnviar = fila.querySelector('.btn-apto-action');
                if (btnEnviar) {
                    if (ambosAptos) {
                        btnEnviar.classList.remove('disabled');
                        btnEnviar.removeAttribute('disabled');
                    } else {
                        btnEnviar.classList.add('disabled');
                        btnEnviar.setAttribute('disabled', 'disabled');
                    }
                }

                const indicador = fila.querySelector('.apto-combinado-indicator');
                if (indicador) {
                    if (ambosAptos) {
                        indicador.innerHTML =
                            '<span class="text-success"><i class="fas fa-check-circle"></i> Listo para enviar</span>';
                    } else {
                        const faltaPago = !apto1;
                        const faltaDocs = !apto2;
                        let texto = 'Falta ';
                        if (faltaPago && faltaDocs) {
                            texto += 'pago y documentos';
                        } else if (faltaPago) {
                            texto += 'pago';
                        } else if (faltaDocs) {
                            texto += 'documentos';
                        }
                        indicador.innerHTML = `<span class="text-muted">${texto}</span>`;
                    }
                }
            }

            // Función genérica para hacer fetch con CSRF
            async function fetchWithCSRF(url, data) {
                if (!csrfToken) {
                    throw new Error('No hay token CSRF disponible');
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });

                if (response.status === 419) {
                    throw new Error('La sesión ha expirado. Por favor recarga la página.');
                }

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                return response.json();
            }

            // Función para actualizar apto1
            async function actualizarApto1(checkbox) {
                const fila = checkbox.closest('tr');
                const habilitar = checkbox.checked;
                const id = checkbox.dataset.id;
                const valor = habilitar ? 1 : 0;

                checkbox.disabled = true;

                try {
                    const data = await fetchWithCSRF(`/postulantes-regular/${id}/apto`, {
                        apto: valor
                    });

                    if (data.success) {
                        actualizarBotonEnviar(fila);
                        mostrarNotificacion('Pago verificado correctamente', 'success');
                    } else {
                        throw new Error(data.message || 'Error al actualizar');
                    }
                } catch (error) {
                    console.error('Error apto1:', error);
                    checkbox.checked = !habilitar;
                    actualizarBotonEnviar(fila);
                    mostrarNotificacion(error.message, 'danger');
                } finally {
                    checkbox.disabled = false;
                }
            }

            // Función para actualizar apto2 - CORREGIDA con mejor manejo de CSRF
            async function actualizarApto2(checkbox) {
                const fila = checkbox.closest('tr');
                const habilitar = checkbox.checked;
                const id = checkbox.dataset.id;
                const valor = habilitar ? 1 : 0;

                checkbox.disabled = true;

                try {
                    const data = await fetchWithCSRF(`/postulantes-regular/${id}/apto2`, {
                        apto2: valor
                    });

                    if (data.success) {
                        // Mantener el estado del checkbox según la respuesta del servidor
                        checkbox.checked = data.apto2 === 1 || data.apto2 === true;

                        if (checkbox.checked) {
                            checkbox.setAttribute('checked', 'checked');
                        } else {
                            checkbox.removeAttribute('checked');
                        }

                        actualizarBotonEnviar(fila);
                        mostrarNotificacion('Documentos verificados correctamente', 'success');
                    } else {
                        checkbox.checked = !habilitar;
                        if (!habilitar) {
                            checkbox.removeAttribute('checked');
                        } else {
                            checkbox.setAttribute('checked', 'checked');
                        }
                        throw new Error(data.message || 'Error al actualizar');
                    }
                } catch (error) {
                    console.error('Error apto2:', error);
                    checkbox.checked = !habilitar;
                    if (!habilitar) {
                        checkbox.removeAttribute('checked');
                    } else {
                        checkbox.setAttribute('checked', 'checked');
                    }
                    actualizarBotonEnviar(fila);
                    mostrarNotificacion(error.message, 'danger');
                } finally {
                    checkbox.disabled = false;
                }
            }

            function mostrarNotificacion(mensaje, tipo) {
                const toast = document.createElement('div');
                toast.className =
                    `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow`;
                toast.style.zIndex = '9999';
                toast.style.minWidth = '300px';
                toast.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            }

            // Event listeners para checkboxes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('toggle-apto')) {
                    e.preventDefault();
                    actualizarApto1(e.target);
                }

                if (e.target.classList.contains('toggle-apto2')) {
                    e.preventDefault();
                    actualizarApto2(e.target);
                }
            });

            // Inicializar todos los botones al cargar
            filas.forEach(fila => {
                actualizarBotonEnviar(fila);
            });
        });
    </script>
@endsection
