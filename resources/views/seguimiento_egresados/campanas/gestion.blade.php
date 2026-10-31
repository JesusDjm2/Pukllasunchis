<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Puklla en Red · Gestión de Campañas y Selección de Docentes Entrevistadores</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --puklla-crema: #FAF8F5;
            --puklla-ocre: #C88219;
            --puklla-granate: #832128;
            --puklla-granate-dark: #5C151B;
            --puklla-verde: #1B7A49;
            --puklla-azul: #2B579A;
            --sidebar-bg: #1A1F2B;
            --sidebar-hover: #262D3D;
            --card-bg: #FFFFFF;
            --ink: #2D3748;
            --ink-muted: #718096;
            --border-line: #E2E8F0;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            --radius: 12px;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            min-height: 100%;
            font: 14px/1.5 Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: var(--ink);
            background: var(--puklla-crema);
        }
        button, input, select, textarea { font: inherit; }
        button { cursor: pointer; }
        a { color: var(--puklla-azul); text-decoration: none; }
        a:hover { text-decoration: underline; }

        #app-layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 275px;
            background: var(--sidebar-bg);
            color: #E2E8F0;
            flex: none;
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
        }
        .brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .brand-box img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
            padding: 4px;
        }
        .brand-title {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -0.3px;
            color: #FFF;
            line-height: 1.2;
        }
        .brand-subtitle {
            font-size: 11px;
            color: #A0AEC0;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .nav-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #718096;
            font-weight: 800;
            margin: 18px 8px 8px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: #CBD5E0;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 4px;
            transition: all 0.2s;
            border: 0;
            background: transparent;
            width: 100%;
            text-align: left;
        }
        .nav-item:hover {
            background: var(--sidebar-hover);
            color: #FFF;
            text-decoration: none;
        }
        .nav-item.active {
            background: var(--puklla-granate);
            color: #FFF;
            box-shadow: 0 2px 8px rgba(131, 33, 40, 0.4);
        }
        .nav-icon {
            font-size: 16px;
            width: 24px;
            text-align: center;
        }

        /* Main Workspace */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .top-navbar {
            background: #FFF;
            border-bottom: 1px solid var(--border-line);
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .top-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(131, 33, 40, 0.08);
            color: var(--puklla-granate);
            font-weight: 700;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 28px 32px;
        }

        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(135deg, var(--puklla-granate) 0%, var(--puklla-granate-dark) 100%);
            color: #FFF;
            padding: 24px 30px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px -5px rgba(131, 33, 40, 0.3);
        }
        .hero-title { font-size: 22px; font-weight: 800; margin: 0 0 6px; }
        .hero-desc { font-size: 13px; opacity: 0.9; margin: 0; max-width: 750px; }

        /* Card Styles */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-line);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            background: #FAFAFA;
            border-bottom: 1px solid var(--border-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--ink);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-body { padding: 20px; }

        /* KPI Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #FFF;
            border: 1px solid var(--border-line);
            border-radius: 10px;
            padding: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: transform 0.2s;
        }
        .kpi-card:hover { transform: translateY(-2px); }
        .kpi-value {
            font-size: 26px;
            font-weight: 800;
            color: var(--puklla-granate);
            line-height: 1.1;
            margin: 4px 0;
        }
        .kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--ink-muted);
        }
        .kpi-desc { font-size: 12px; color: #718096; margin-top: 4px; }

        /* Tables */
        .table-responsive { overflow-x: auto; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .data-table th {
            background: #F8FAFC;
            padding: 12px 14px;
            font-weight: 700;
            color: #4A5568;
            text-align: left;
            border-bottom: 2px solid var(--border-line);
        }
        .data-table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border-line);
            vertical-align: middle;
        }
        .data-table tr:hover { background: #F8FAFC; }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-green { background: #DEF7EC; color: #03543F; }
        .badge-blue { background: #E1EFFE; color: #1E429F; }
        .badge-amber { background: #FEF08A; color: #854D0E; }
        .badge-red { background: #FDE8E8; color: #9B1C1C; }
        .badge-gray { background: #F3F4F6; color: #374151; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            border: 0;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-granate {
            background: var(--puklla-granate);
            color: #FFF;
        }
        .btn-granate:hover { background: var(--puklla-granate-dark); text-decoration: none; color: #FFF; }
        .btn-verde { background: var(--puklla-verde); color: #FFF; }
        .btn-verde:hover { background: #135A35; color: #FFF; text-decoration: none; }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-line);
            color: var(--ink);
        }
        .btn-outline:hover { background: #F8FAFC; text-decoration: none; }

        /* Modal preview */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-box {
            background: #FFF;
            border-radius: var(--radius);
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            padding: 24px;
        }
    </style>
</head>
<body>
<div id="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="brand-box">
            <img src="{{ asset('img/logoiesp.ico') }}" alt="Pukllasunchis">
            <div>
                <div class="brand-title">Puklla en Red</div>
                <div class="brand-subtitle">EESP Pukllasunchis</div>
            </div>
        </div>

        <div class="nav-label">Módulos de Coordinación</div>
        <a href="{{ route('seguimiento.campanas.gestion') }}" class="nav-item active">
            <span class="nav-icon"><i class="fas fa-bullhorn"></i></span>
            <span>Gestión de Campañas</span>
        </a>
        <a href="{{ route('seguimiento.campanas.monitoreo', ['campanaId' => $campanaActual?->id ?? 10]) }}" class="nav-item">
            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
            <span>Monitoreo de Avance</span>
        </a>
        <a href="{{ route('seguimiento.campanas.territorio', ['campanaId' => $campanaActual?->id ?? 10]) }}" class="nav-item">
            <span class="nav-icon"><i class="fas fa-map-marked-alt"></i></span>
            <span>Observatorio Territorial</span>
        </a>
        <a href="{{ route('seguimiento.index') }}" class="nav-item">
            <span class="nav-icon"><i class="fas fa-user-graduate"></i></span>
            <span>Padrón Maestro</span>
        </a>
        <a href="{{ route('seguimiento.laboratorio') }}" class="nav-item">
            <span class="nav-icon"><i class="fas fa-microscope"></i></span>
            <span>Laboratorio Analítico</span>
        </a>

        <div class="nav-label">Intranet Institucional</div>
        <a href="{{ route('admin') }}" class="nav-item">
            <span class="nav-icon"><i class="fas fa-arrow-left"></i></span>
            <span>Panel Administrador</span>
        </a>
        <a href="{{ route('auditoria.logout') }}" class="nav-item" style="color: #F87171;">
            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
            <span>Cerrar Sesión</span>
        </a>
    </aside>

    <!-- Main Workspace -->
    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="top-badge">
                <i class="fas fa-shield-alt mr-1"></i>
                <span>Coordinación de Empleabilidad · Puklla en Red</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size:13px;font-weight:700;color:#4A5568;">
                    {{ auth()->user()->name }} (Coordinación)
                </span>
                <span class="badge badge-green">Sesión Segura</span>
            </div>
        </header>

        <div class="container">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <div>
                    <h1 class="hero-title">Gestión de Campañas y Asignación de Entrevistadores</h1>
                    <p class="hero-desc">
                        Organiza campañas de seguimiento utilizando <strong>docentes y egresados reales</strong> de Pukllasunchis.
                        Selecciona a los docentes entrevistadores, revisa el padrón y ejecuta la distribución aleatoria equilibrada.
                    </p>
                </div>
                <div>
                    <a href="{{ route('seguimiento.campanas.monitoreo', ['campanaId' => $campanaActual?->id ?? 10]) }}" class="btn btn-verde">
                        <i class="fas fa-chart-line"></i> Ver Monitoreo en Vivo
                    </a>
                </div>
            </div>

            <!-- Selector de Campaña y Alerta de Ensayo -->
            <div class="card">
                <div class="card-body" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-weight: 800; font-size: 14px;">Campaña Activa:</span>
                        <select id="selectCampana" onchange="cambiarCampana(this.value)" style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border-line); font-weight: 700;">
                            @foreach ($campanas as $c)
                                <option value="{{ $c->id }}" {{ $c->id === $campanaActual?->id ? 'selected' : '' }}>
                                    {{ $c->nombre }} ({{ $c->codigo }}) {{ $c->es_ensayo ? '— [MODO ENSAYO]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($campanaActual?->es_ensayo)
                        <div style="background: #FEF3C7; border: 1px solid #F59E0B; padding: 6px 14px; border-radius: 8px; color: #92400E; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-vial"></i>
                            <span>MODALIDAD DE ENSAYO ACTIVA: Los registros de llamada y actualizaciones quedarán aislados como eventos de prueba sin contactar personas reales.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Diagnóstico del Padrón de Egresados Reales -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-database text-primary"></i>
                        <span>Diagnóstico del Padrón Institucional de Egresados Reales</span>
                    </h2>
                    <span class="badge badge-blue">{{ $diagnosticoPadron['total_egresados_reales'] }} Egresados Reales Registrados</span>
                </div>
                <div class="card-body">
                    <div class="kpi-grid">
                        <div class="kpi-card">
                            <div class="kpi-label">Padrón Institucional Total</div>
                            <div class="kpi-value">{{ $diagnosticoPadron['total_egresados_reales'] }}</div>
                            <div class="kpi-desc">Egresados reales verificados en base EESP</div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">Con Teléfono Disponible</div>
                            <div class="kpi-value" style="color: var(--puklla-verde);">{{ $diagnosticoPadron['con_telefono'] }}</div>
                            <div class="kpi-desc">Aptos para contacto telefónico directo</div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">Sin Teléfono (Requiere Vía Alterna)</div>
                            <div class="kpi-value" style="color: var(--puklla-ocre);">{{ $diagnosticoPadron['sin_telefono'] }}</div>
                            <div class="kpi-desc">Conservados en padrón para correo/redes</div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">Con Correo Institucional Válido</div>
                            <div class="kpi-value" style="color: var(--puklla-azul);">{{ $diagnosticoPadron['con_email_valido'] }}</div>
                            <div class="kpi-desc">100% con trazabilidad digital verificada</div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-label">Residencia Basal Conocida</div>
                            <div class="kpi-value">{{ $diagnosticoPadron['con_residencia'] }}</div>
                            <div class="kpi-desc">Con departamento/provincia registrado</div>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; border-radius: 8px; padding: 12px 16px; border: 1px solid var(--border-line); font-size: 13px; color: #4A5568;">
                        <strong>Distribución por Programa de Estudios:</strong>
                        @foreach ($diagnosticoPadron['por_programa'] as $prog => $cant)
                            <span class="badge badge-gray mr-2" style="font-size: 12px; margin-right: 8px;">
                                {{ $prog }}: <strong>{{ $cant }}</strong>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Paso 1: Selección de Docentes Entrevistadores Reales -->
            <form id="formDistribucion" method="POST" action="{{ route('seguimiento.campanas.distribuir') }}">
                @csrf
                <input type="hidden" name="campana_id" value="{{ $campanaActual?->id }}">
                <input type="hidden" name="es_ensayo" value="{{ $campanaActual?->es_ensayo ? '1' : '0' }}">

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">
                                <i class="fas fa-chalkboard-teacher text-danger"></i>
                                <span>Paso 1: Selección de Docentes Entrevistadores Reales ({{ $docentesElegibles->count() }} Disponibles)</span>
                            </h2>
                            <p style="margin: 4px 0 0; font-size: 12px; color: var(--ink-muted);">
                                Selecciona los docentes que participarán en esta campaña. Se mantendrá su rol institucional <code>docente</code> intacto y se les habilitará la función de seguimiento.
                            </p>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn btn-outline" onclick="seleccionarPrimeros(5)">
                                <i class="fas fa-check-double"></i> Seleccionar 5
                            </button>
                            <button type="button" class="btn btn-outline" onclick="seleccionarPrimeros(10)">
                                <i class="fas fa-check-double"></i> Seleccionar 10
                            </button>
                            <button type="button" class="btn btn-outline" onclick="deseleccionarTodos()">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 380px;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"><input type="checkbox" id="checkAll" onchange="toggleAll(this)"></th>
                                        <th>Docente Institucional</th>
                                        <th>Programa / Área</th>
                                        <th>Roles Actuales</th>
                                        <th>Estado</th>
                                        <th>Función Seguimiento</th>
                                        <th>Asignados Actuales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($docentesElegibles as $d)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="docentes[]" value="{{ $d['user_id'] }}" class="docente-check" onchange="actualizarContadorDocentes()">
                                            </td>
                                            <td>
                                                <div style="font-weight: 700; color: var(--ink);">{{ $d['nombre_completo'] }}</div>
                                                <div style="font-size: 11px; color: var(--ink-muted);">{{ $d['email'] }} · DNI: {{ $d['dni'] ?? 'N/D' }}</div>
                                            </td>
                                            <td><span class="badge badge-gray">{{ $d['programa'] }}</span></td>
                                            <td>
                                                @foreach ($d['roles'] as $r)
                                                    <span class="badge {{ $r === 'docente' ? 'badge-blue' : ($r === 'seguimiento-egresados' ? 'badge-green' : 'badge-gray') }}">
                                                        {{ $r }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td><span class="badge badge-green"><i class="fas fa-check-circle"></i> Activo</span></td>
                                            <td>
                                                @if ($d['tiene_rol_seguimiento'])
                                                    <span class="badge badge-green"><i class="fas fa-key"></i> Asignado</span>
                                                @else
                                                    <span class="badge badge-amber"><i class="fas fa-clock"></i> Pendiente activación</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $d['asignados_actuales'] }}</strong> egresados
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top: 16px; padding: 12px 16px; background: #EEF2FF; border-radius: 8px; border: 1px solid #C7D2FE; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <span style="font-weight: 800; color: #3730A3;">Docentes Seleccionados:</span>
                                <span id="contadorDocentes" style="font-size: 16px; font-weight: 800; color: var(--puklla-granate); margin-left: 6px;">0</span>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button type="button" class="btn btn-outline" onclick="abrirPreview()">
                                    <i class="fas fa-calculator"></i> Vista Previa de Distribución Equilibrada
                                </button>
                                <button type="submit" id="btnConfirmarDist" class="btn btn-granate" disabled>
                                    <i class="fas fa-random"></i> Confirmar y Distribuir Equitativamente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Padrón de Egresados en la Campaña -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list-ul"></i>
                        <span>Padrón de Egresados Reales en esta Campaña ({{ $padronEgresados->count() }} Registros)</span>
                    </h2>
                    <span class="badge badge-gray">Identificador Canónico: users.id</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Egresado/a</th>
                                    <th>Programa</th>
                                    <th>Cohorte</th>
                                    <th>Teléfono</th>
                                    <th>Residencia Basal</th>
                                    <th>Estado de Asignación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($padronEgresados->take(80) as $egr)
                                    <tr>
                                        <td><code>REAL-{{ $egr['user_id'] }}</code></td>
                                        <td>
                                            <div style="font-weight: 700;">{{ $egr['nombre_completo'] }}</div>
                                            <div style="font-size: 11px; color: var(--ink-muted);">{{ $egr['email'] }}</div>
                                        </td>
                                        <td>{{ $egr['programa'] }}</td>
                                        <td><span class="badge badge-gray">{{ $egr['cohorte'] }}</span></td>
                                        <td>
                                            @if ($egr['tiene_telefono'])
                                                <span class="badge badge-green"><i class="fas fa-phone mr-1"></i> {{ $egr['telefono'] }}</span>
                                            @else
                                                <span class="badge badge-amber"><i class="fas fa-exclamation-triangle mr-1"></i> Sin teléfono</span>
                                            @endif
                                        </td>
                                        <td>{{ $egr['res_departamento'] }} / {{ $egr['res_provincia'] }}</td>
                                        <td>
                                            @if ($egr['esta_asignado'])
                                                <span class="badge badge-blue">
                                                    <i class="fas fa-user-check mr-1"></i> Asignado a: {{ $egr['asignacion_actual']['docente_nombre'] }}
                                                </span>
                                            @else
                                                <span class="badge badge-gray">Sin asignar en campaña</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Vista Previa de Distribución -->
<div id="modalPreview" class="modal-overlay">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-line); padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--puklla-granate);">
                <i class="fas fa-balance-scale mr-2"></i> Vista Previa de Distribución Aleatoria Equilibrada
            </h3>
            <button onclick="cerrarPreview()" style="border: 0; background: transparent; font-size: 18px; color: #718096;">&times;</button>
        </div>

        <div id="previewContenido">
            <div style="text-align: center; padding: 30px;">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p>Calculando distribución óptima entre docentes seleccionados...</p>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-line); padding-top: 14px;">
            <button type="button" class="btn btn-outline" onclick="cerrarPreview()">Cerrar</button>
            <button type="button" class="btn btn-granate" onclick="confirmarDesdeModal()">
                <i class="fas fa-check"></i> Proceder con la Distribución
            </button>
        </div>
    </div>
</div>

<script>
    function cambiarCampana(campanaId) {
        window.location.href = "{{ route('seguimiento.campanas.gestion') }}?campana_id=" + campanaId;
    }

    function toggleAll(master) {
        const checks = document.querySelectorAll('.docente-check');
        checks.forEach(c => c.checked = master.checked);
        actualizarContadorDocentes();
    }

    function seleccionarPrimeros(n) {
        deseleccionarTodos();
        const checks = document.querySelectorAll('.docente-check');
        for (let i = 0; i < Math.min(n, checks.length); i++) {
            checks[i].checked = true;
        }
        actualizarContadorDocentes();
    }

    function deseleccionarTodos() {
        document.querySelectorAll('.docente-check').forEach(c => c.checked = false);
        const master = document.getElementById('checkAll');
        if (master) master.checked = false;
        actualizarContadorDocentes();
    }

    function actualizarContadorDocentes() {
        const count = document.querySelectorAll('.docente-check:checked').length;
        document.getElementById('contadorDocentes').textContent = count;
        const btn = document.getElementById('btnConfirmarDist');
        if (btn) btn.disabled = (count === 0);
    }

    function abrirPreview() {
        const checks = document.querySelectorAll('.docente-check:checked');
        if (checks.length === 0) {
            alert('Por favor selecciona al menos un docente entrevistador.');
            return;
        }

        const modal = document.getElementById('modalPreview');
        modal.style.display = 'flex';

        const docentes = Array.from(checks).map(c => c.value);
        const campanaId = "{{ $campanaActual?->id }}";
        const esEnsayo = "{{ $campanaActual?->es_ensayo ? '1' : '0' }}";

        fetch("{{ route('seguimiento.campanas.distribuir.preview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                campana_id: campanaId,
                docentes: docentes,
                es_ensayo: esEnsayo === '1'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('previewContenido').innerHTML = `<div style="color: red; padding: 20px;"><strong>Error:</strong> ${data.error}</div>`;
                return;
            }

            const p = data.data;
            let html = `
                <div style="background: #F8FAFC; border: 1px solid var(--border-line); border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; text-align: center;">
                        <div>
                            <div style="font-size: 11px; color: var(--ink-muted); text-transform: uppercase;">Egresados a Distribuir</div>
                            <div style="font-size: 22px; font-weight: 800; color: var(--puklla-granate);">${p.total_egresados}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--ink-muted); text-transform: uppercase;">Docentes Seleccionados</div>
                            <div style="font-size: 22px; font-weight: 800; color: var(--puklla-azul);">${p.total_docentes}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--ink-muted); text-transform: uppercase;">Promedio por Docente</div>
                            <div style="font-size: 22px; font-weight: 800; color: var(--puklla-verde);">${p.promedio_por_docente}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--ink-muted); text-transform: uppercase;">Diferencia Máxima</div>
                            <div style="font-size: 22px; font-weight: 800; color: #047857;">${p.diferencia_maxima} persona(s)</div>
                        </div>
                    </div>
                </div>

                <h4 style="margin: 0 0 10px; font-size: 14px;">Reparto Equitativo por Docente:</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Docente Entrevistador</th>
                            <th>Cantidad Asignada</th>
                            <th>Muestra de Egresados Asignados</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            p.resumen.forEach(r => {
                html += `
                    <tr>
                        <td>
                            <strong>${r.docente_nombre}</strong><br>
                            <small class="text-muted">${r.docente_email}</small>
                        </td>
                        <td><span class="badge badge-green" style="font-size: 13px;">${r.cantidad_asignada} egresados</span></td>
                        <td><small style="color: #4A5568;">${r.muestra_egresados.join(', ')} ...</small></td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;
            document.getElementById('previewContenido').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('previewContenido').innerHTML = `<div style="color: red; padding: 20px;"><strong>Error de conexión:</strong> ${err.message}</div>`;
        });
    }

    function cerrarPreview() {
        document.getElementById('modalPreview').style.display = 'none';
    }

    function confirmarDesdeModal() {
        cerrarPreview();
        document.getElementById('formDistribucion').submit();
    }
</script>
</body>
</html>
