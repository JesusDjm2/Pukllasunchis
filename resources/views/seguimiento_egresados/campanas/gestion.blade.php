@extends('layouts.seguimiento')
@section('titulo','Red Puklla de Egresados')
@section('contenido')
<style>@scope (.seguimiento-panel) {

        :scope {
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
        :scope, :scope {
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

}</style>
<div class="seguimiento-panel">
@include('seguimiento_egresados.partials.grupo_formacion')
<div id="app-layout">
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
            {{-- Mensajes de Éxito / Error Flash --}}
            @if(session('success'))
                <div style="background:#DEF7EC;color:#03543F;padding:12px 18px;border-radius:8px;margin-bottom:20px;font-weight:700;display:flex;align-items:center;gap:8px;border:1px solid #BCF0DA;">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div style="background:#FDE8E8;color:#9B1C1C;padding:12px 18px;border-radius:8px;margin-bottom:20px;font-weight:700;display:flex;align-items:center;gap:8px;border:1px solid #FBD5D5;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Hero Banner -->
            <div class="hero-banner">
                <div>
                    <h1 class="hero-title">Gestión de Campañas y Asignación de Entrevistadores</h1>
                    <p class="hero-desc">
                        Organiza campañas de seguimiento utilizando <strong>docentes y egresados reales</strong> de Pukllasunchis.
                        Selecciona a los docentes entrevistadores, revisa el padrón y ejecuta la distribución aleatoria equilibrada o asignación manual.
                    </p>
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('seguimiento.observatorio-conectado', ['grupo_formacion' => $grupoFormacion]) }}" class="btn btn-outline" style="background:#fff;color:var(--puklla-granate);">
                        <i class="fas fa-map-marked-alt mr-1"></i> Observatorio Territorial
                    </a>
                    <a href="{{ route('seguimiento.campanas.monitoreo', ['campanaId' => $campanaActual?->id ?? 10]) }}" class="btn btn-verde">
                        <i class="fas fa-chart-line"></i> Monitoreo en Vivo
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

            {{-- Banner de Filtros Activos provenientes del Observatorio Territorial --}}
            @php
                $hayFiltros = !empty(array_filter($filtros ?? []));
            @endphp
            @if($hayFiltros)
                <div style="background:#EEF2FF;border:1px solid #C7D2FE;border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <div style="font-weight:800;color:#3730A3;font-size:14px;display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-filter"></i>
                            <span>Filtro activo desde el Observatorio territorial:</span>
                        </div>
                        <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;">
                            @if(!empty($filtros['distrito']))
                                <span class="badge badge-blue">Distrito: {{ $filtros['distrito'] }}</span>
                            @endif
                            @if(!empty($filtros['provincia']))
                                <span class="badge badge-blue">Provincia: {{ $filtros['provincia'] }}</span>
                            @endif
                            @if(!empty($filtros['departamento']))
                                <span class="badge badge-blue">Departamento: {{ $filtros['departamento'] }}</span>
                            @endif
                            @if(!empty($filtros['sin_ubicacion']))
                                <span class="badge badge-amber"><i class="fas fa-map-marker-slash mr-1"></i> Sin ubicación verificable</span>
                            @endif
                            @if(!empty($filtros['estado_contacto']) && $filtros['estado_contacto'] !== 'todos')
                                <span class="badge badge-green">Estado de contacto: {{ $filtros['estado_contacto'] }}</span>
                            @endif
                            @if(!empty($filtros['programa']))
                                <span class="badge badge-gray">Programa: {{ $filtros['programa'] }}</span>
                            @endif
                            @if(!empty($filtros['cohorte']))
                                <span class="badge badge-gray">Cohorte: {{ $filtros['cohorte'] }}</span>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <a href="{{ route('seguimiento.observatorio-conectado', array_filter(['grupo_formacion' => $grupoFormacion, 'estado_contacto' => $filtros['estado_contacto'] ?? null])) }}" class="btn btn-outline" style="background:#fff;color:#3730A3;font-weight:700;">
                            <i class="fas fa-arrow-left mr-1"></i> Volver al Observatorio
                        </a>
                        <a href="{{ route('seguimiento.campanas.gestion', ['campana_id' => $campanaActual?->id, 'grupo_formacion' => $grupoFormacion]) }}" class="btn btn-outline" style="background:#fff;color:#dc2626;border-color:#fca5a5;font-weight:700;">
                            <i class="fas fa-times mr-1"></i> Limpiar filtros
                        </a>
                    </div>
                </div>
            @endif

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
                <input type="hidden" name="grupo_formacion" value="{{ $grupoFormacion ?? 'TODOS' }}">
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
                    <div>
                        <h2 class="card-title">
                            <i class="fas fa-list-ul"></i>
                            <span>Padrón de Egresados en esta Campaña ({{ $padronEgresados->count() }} Registros)</span>
                        </h2>
                        <span class="badge badge-gray" style="margin-top:4px;">Identificador Canónico: users.id · Selección Nominal Autorizada</span>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="button" class="btn btn-outline" onclick="seleccionarTodosEgresadosVisibles(true)">
                            <i class="fas fa-check-square"></i> Seleccionar todos ({{ $padronEgresados->count() }})
                        </button>
                        <button type="button" class="btn btn-outline" onclick="seleccionarTodosEgresadosVisibles(false)">
                            <i class="fas fa-square"></i> Desmarcar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 480px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="checkAllEgresados" onchange="toggleAllEgresados(this)">
                                    </th>
                                    <th>ID</th>
                                    <th>Egresado/a</th>
                                    <th>Programa</th>
                                    <th>Cohorte</th>
                                    <th>Teléfono / Canal</th>
                                    <th>Estado de Contacto</th>
                                    <th>Residencia Basal</th>
                                    <th>Estado de Asignación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($padronEgresados->take(120) as $egr)
                                    @php
                                        $cStatus = $egr['estado_contacto'] ?? [];
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="{{ $egr['user_id'] }}" class="egresado-row-check" onchange="actualizarContadorEgresados()">
                                        </td>
                                        <td><code>REAL-{{ $egr['user_id'] }}</code></td>
                                        <td>
                                            <div style="font-weight: 700;">{{ $egr['nombre_completo'] }}</div>
                                            <div style="font-size: 11px; color: var(--ink-muted);">{{ $egr['email'] }}</div>
                                        </td>
                                        <td>{{ $egr['grupo_formacion'] ?? 'Por verificar' }} · {{ $egr['programa'] }}</td>
                                        <td><span class="badge badge-gray">{{ $egr['cohorte'] }}</span></td>
                                        <td>
                                            @if ($egr['tiene_telefono'])
                                                <span class="badge badge-green"><i class="fas fa-phone mr-1"></i> {{ $egr['telefono'] }}</span>
                                            @else
                                                <span class="badge badge-amber"><i class="fas fa-exclamation-triangle mr-1"></i> Sin teléfono</span>
                                            @endif
                                            @if(!empty($cStatus['sin_canal_valido']))
                                                <span class="badge badge-red" title="Sin canal válido de contacto registrado" style="margin-top:2px;">Sin canal válido</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($cStatus['contacto_efectivo']))
                                                <span class="badge badge-green"><i class="fas fa-check-circle mr-1"></i> Contacto efectivo</span>
                                            @elseif(!empty($cStatus['intento_sin_respuesta']))
                                                <span class="badge badge-amber"><i class="fas fa-phone-slash mr-1"></i> Intento sin respuesta</span>
                                            @else
                                                <span class="badge badge-red"><i class="fas fa-hourglass-start mr-1"></i> Sin intento</span>
                                            @endif
                                        </td>
                                        <td>{{ $egr['res_departamento'] }} / {{ $egr['res_provincia'] }}</td>
                                        <td>
                                            @if ($egr['esta_asignado'])
                                                <span class="badge badge-blue">
                                                    <i class="fas fa-user-check mr-1"></i> Asignado: {{ $egr['asignacion_actual']['docente_nombre'] }}
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

                    {{-- Barra de Acción Rápida para Asignación Manual / Focalizada --}}
                    <div style="margin-top: 16px; padding: 14px 18px; background: #F5F3FF; border-radius: 8px; border: 1px solid #DDD6FE; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <span style="font-weight: 800; color: #5B21B6; font-size: 14px;">Egresados Seleccionados:</span>
                            <span id="contadorEgresados" style="font-size: 18px; font-weight: 800; color: var(--puklla-granate); margin-left: 6px;">0</span>
                            <small style="color: #6D28D9; margin-left: 10px;">(Selecciona egresados en la tabla para asignarlos de forma directa o distribuida a docentes)</small>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" id="btnAsignarSeleccionados" class="btn btn-granate" onclick="abrirModalAsignarSeleccionados()" disabled>
                                <i class="fas fa-user-plus mr-1"></i> Asignar seleccionados a campaña
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Vista Previa de Distribución Equilibrada -->
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

<!-- Modal Asignación de Egresados Seleccionados Manualmente -->
<div id="modalAsignarSeleccionados" class="modal-overlay">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-line); padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--puklla-granate);">
                <i class="fas fa-user-plus mr-2"></i> Asignar Egresados Seleccionados a Campaña
            </h3>
            <button onclick="cerrarModalAsignarSeleccionados()" style="border: 0; background: transparent; font-size: 18px; color: #718096;">&times;</button>
        </div>

        <div style="margin-bottom: 16px; background: #EEF2FF; padding: 12px 16px; border-radius: 8px; border: 1px solid #C7D2FE;">
            <div style="font-weight: 800; color: #3730A3;">
                Has seleccionado <span id="modalSelCount" style="font-size: 16px; color: var(--puklla-granate);">0</span> egresados para asignar en la campaña: <strong>{{ $campanaActual?->nombre }}</strong>
            </div>
            <p style="margin: 4px 0 0; font-size: 12px; color: #4B5563;">
                Selecciona uno o más docentes entrevistadores. Si seleccionas varios, la asignación se repartirá equitativamente entre ellos de forma transaccional (ACID).
            </p>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="font-weight: 700; font-size: 13px; color: #374151; display: block; margin-bottom: 6px;">
                Docentes Entrevistadores de Destino:
            </label>
            <div style="max-height: 180px; overflow-y: auto; border: 1px solid var(--border-line); border-radius: 6px; padding: 8px;">
                @foreach ($docentesElegibles as $d)
                    <label style="display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-bottom: 1px solid #F3F4F6; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" value="{{ $d['user_id'] }}" class="docente-asignar-check">
                        <span><strong>{{ $d['nombre_completo'] }}</strong> <small class="text-muted">({{ $d['programa'] }}) · {{ $d['asignados_actuales'] }} asignados</small></span>
                    </label>
                @endforeach
            </div>
            <div style="margin-top: 8px;">
                <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="seleccionarTodosDocentesAsignar(true)">Seleccionar todos</button>
                <button type="button" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;" onclick="seleccionarTodosDocentesAsignar(false)">Limpiar</button>
            </div>
        </div>

        <div id="previewAsignarSelContenido" style="display: none; margin-bottom: 16px; background: #F8FAFC; border: 1px solid var(--border-line); border-radius: 8px; padding: 14px;">
            {{-- Rellenado vía AJAX preview --}}
        </div>

        <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-line); padding-top: 14px;">
            <button type="button" class="btn btn-outline" onclick="cerrarModalAsignarSeleccionados()">Cancelar</button>
            <button type="button" class="btn btn-outline" style="background:#F3F4F6;" onclick="ejecutarPreviewAsignarSeleccionados()">
                <i class="fas fa-calculator mr-1"></i> Vista previa de asignación
            </button>
            <button type="button" id="btnConfirmarAsignacionSel" class="btn btn-granate" onclick="confirmarAsignacionSeleccionados()">
                <i class="fas fa-check-circle mr-1"></i> Confirmar y Aplicar (Transaccional)
            </button>
        </div>
    </div>
</div>

</div>
@endsection
@push('scripts')
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

    // --- Manejo de Egresados Seleccionados ---
    function toggleAllEgresados(master) {
        document.querySelectorAll('.egresado-row-check').forEach(c => c.checked = master.checked);
        actualizarContadorEgresados();
    }

    function seleccionarTodosEgresadosVisibles(marcar) {
        document.querySelectorAll('.egresado-row-check').forEach(c => c.checked = marcar);
        const master = document.getElementById('checkAllEgresados');
        if (master) master.checked = marcar;
        actualizarContadorEgresados();
    }

    function actualizarContadorEgresados() {
        const checks = document.querySelectorAll('.egresado-row-check:checked');
        const count = checks.length;
        document.getElementById('contadorEgresados').textContent = count;
        const btn = document.getElementById('btnAsignarSeleccionados');
        if (btn) btn.disabled = (count === 0);
    }

    function seleccionarTodosDocentesAsignar(marcar) {
        document.querySelectorAll('.docente-asignar-check').forEach(c => c.checked = marcar);
    }

    function abrirModalAsignarSeleccionados() {
        const count = document.querySelectorAll('.egresado-row-check:checked').length;
        if (count === 0) {
            alert('Por favor selecciona al menos un egresado.');
            return;
        }
        document.getElementById('modalSelCount').textContent = count;
        document.getElementById('previewAsignarSelContenido').style.display = 'none';
        document.getElementById('modalAsignarSeleccionados').style.display = 'flex';
    }

    function cerrarModalAsignarSeleccionados() {
        document.getElementById('modalAsignarSeleccionados').style.display = 'none';
    }

    function ejecutarPreviewAsignarSeleccionados() {
        const egresados = Array.from(document.querySelectorAll('.egresado-row-check:checked')).map(c => Number(c.value));
        const docentes = Array.from(document.querySelectorAll('.docente-asignar-check:checked')).map(c => Number(c.value));

        if (!docentes.length) {
            alert('Por favor selecciona al menos un docente para asignar.');
            return;
        }

        const previewDiv = document.getElementById('previewAsignarSelContenido');
        previewDiv.style.display = 'block';
        previewDiv.innerHTML = '<div style="text-align:center;padding:12px;"><i class="fas fa-spinner fa-spin mr-2"></i> Calculando vista previa...</div>';

        fetch("{{ route('seguimiento.campanas.asignar-seleccionados.preview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                campana_id: "{{ $campanaActual?->id }}",
                docentes: docentes,
                egresados: egresados,
                es_ensayo: "{{ $campanaActual?->es_ensayo ? '1' : '0' }}" === '1'
            })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                previewDiv.innerHTML = `<div style="color:red;"><strong>Error:</strong> ${res.error}</div>`;
                return;
            }
            const d = res.data;
            let repHtml = '';
            (d.reparto || []).forEach(r => {
                repHtml += `<li><strong>${r.docente_nombre}</strong>: ${r.asignados_count} asignaciones</li>`;
            });

            previewDiv.innerHTML = `
                <div style="font-weight:700;color:#1E293B;margin-bottom:8px;">Resultado de la Vista Previa:</div>
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px;text-align:center;margin-bottom:10px;">
                    <div style="background:#DEF7EC;padding:8px;border-radius:6px;border:1px solid #BCF0DA;">
                        <div style="font-size:11px;color:#03543F;font-weight:700;">A Asignar</div>
                        <div style="font-size:20px;font-weight:800;color:#03543F;">${d.asignados}</div>
                    </div>
                    <div style="background:#FEF08A;padding:8px;border-radius:6px;border:1px solid #FDE047;">
                        <div style="font-size:11px;color:#854D0E;font-weight:700;">Ya Asignados</div>
                        <div style="font-size:20px;font-weight:800;color:#854D0E;">${d.ya_asignados}</div>
                    </div>
                    <div style="background:#F3F4F6;padding:8px;border-radius:6px;border:1px solid #E5E7EB;">
                        <div style="font-size:11px;color:#4B5563;font-weight:700;">No Elegibles</div>
                        <div style="font-size:20px;font-weight:800;color:#4B5563;">${d.no_elegibles}</div>
                    </div>
                </div>
                <div style="font-size:12px;color:#4B5563;">
                    <strong>Reparto previsto por docente:</strong>
                    <ul style="margin:4px 0 0 16px;padding:0;">${repHtml}</ul>
                </div>
            `;
        })
        .catch(err => {
            previewDiv.innerHTML = `<div style="color:red;"><strong>Error de conexión:</strong> ${err.message}</div>`;
        });
    }

    function confirmarAsignacionSeleccionados() {
        const egresados = Array.from(document.querySelectorAll('.egresado-row-check:checked')).map(c => Number(c.value));
        const docentes = Array.from(document.querySelectorAll('.docente-asignar-check:checked')).map(c => Number(c.value));

        if (!docentes.length) {
            alert('Por favor selecciona al menos un docente para asignar.');
            return;
        }

        const btn = document.getElementById('btnConfirmarAsignacionSel');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Asignando de forma transaccional...';

        fetch("{{ route('seguimiento.campanas.asignar-seleccionados') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                campana_id: "{{ $campanaActual?->id }}",
                docentes: docentes,
                egresados: egresados,
                es_ensayo: "{{ $campanaActual?->es_ensayo ? '1' : '0' }}" === '1'
            })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                alert('Error al asignar: ' + res.error);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Confirmar y Aplicar (Transaccional)';
                return;
            }
            alert(`Asignación completada exitosamente:\n- ${res.data.asignados} egresados asignados.\n- ${res.data.ya_asignados} ya estaban asignados.\n- ${res.data.no_elegibles} no elegibles.`);
            window.location.reload();
        })
        .catch(err => {
            alert('Error de red al aplicar asignación: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Confirmar y Aplicar (Transaccional)';
        });
    }

    // --- Distribución Aleatoria Equilibrada ---
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
                grupo_formacion: document.getElementById('grupo-formacion-global')?.value || 'TODOS',
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
@endpush
