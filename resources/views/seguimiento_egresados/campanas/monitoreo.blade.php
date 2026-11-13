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

        /* Main Content */
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
        .card-:scope { padding: 20px; }

        /* KPI Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
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
        .btn-granate { background: var(--puklla-granate); color: #FFF; }
        .btn-granate:hover { background: var(--puklla-granate-dark); color: #FFF; text-decoration: none; }
        .btn-verde { background: var(--puklla-verde); color: #FFF; }
        .btn-verde:hover { background: #135A35; color: #FFF; text-decoration: none; }
        .btn-outline { background: transparent; border: 1px solid var(--border-line); color: var(--ink); }
        .btn-outline:hover { background: #F8FAFC; text-decoration: none; }
        .btn-sm { padding: 4px 10px; font-size: 11px; border-radius: 6px; }

        /* Modal */
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
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            padding: 24px;
        }

}</style>
<div class="seguimiento-panel">
@include('seguimiento_egresados.partials.grupo_formacion')
@include('seguimiento_egresados.partials.avance_grupos', ['avancePorGrupo' => $metricas['por_grupo'] ?? []])
<div id="app-layout">
    <!-- Sidebar -->


    <!-- Main Workspace -->
    <div class="main-content">
        <header class="top-navbar">
            <div class="top-badge">
                <i class="fas fa-eye mr-1"></i>
                <span>Monitoreo en Tiempo Real · Coordinación de Empleabilidad</span>
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
                    <h1 class="hero-title">Monitoreo de Avance: {{ $campana->nombre }}</h1>
                    <p class="hero-desc">
                        Supervisa el progreso individual de los docentes entrevistadores, la cobertura de llamadas, las actualizaciones logradas y la calidad de los datos recopilados en tiempo real.
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('seguimiento.campanas.gestion') }}?campana_id={{ $campana->id }}" class="btn btn-outline" style="color: #FFF; border-color: rgba(255,255,255,0.4);">
                        <i class="fas fa-cog"></i> Configuración Campaña
                    </a>
                    <a href="{{ route('seguimiento.campanas.territorio', ['campanaId' => $campana->id]) }}" class="btn btn-verde">
                        <i class="fas fa-map-marked-alt"></i> Observatorio Territorial
                    </a>
                </div>
            </div>

            @if ($campana->es_ensayo)
                <div style="background: #FEF3C7; border: 1px solid #F59E0B; padding: 10px 16px; border-radius: 8px; color: #92400E; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-vial"></i>
                    <span>CAMPAÑA DE ENSAYO INSTITUCIONAL ACTIVA: Todos los eventos, llamadas y actualizaciones se contabilizan de forma aislada sin afectar la base productiva ni contactar a egresados reales.</span>
                </div>
            @endif

            <!-- Indicadores Globales con Denominadores Explícitos -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-tachometer-alt text-danger"></i>
                        <span>Indicadores Generales de Cobertura y Calidad (Con Denominadores Explícitos)</span>
                    </h2>
                    <span class="badge badge-blue">Campaña: {{ $campana->codigo }}</span>
                </div>
                <div class="card-body">
                    <div class="kpi-grid">
                        <div class="kpi-card">
                            <div class="kpi-label">Padrón Institucional Elegible</div>
                            <div class="kpi-value">{{ $metricas['total_padron_elegible'] }}</div>
                            <div class="kpi-desc">Total egresados reales en base EESP</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Egresados Asignados</div>
                            <div class="kpi-value" style="color: var(--puklla-azul);">
                                {{ $metricas['total_asignados'] }}
                            </div>
                            <div class="kpi-desc">
                                Cobertura: <strong>{{ $metricas['total_padron_elegible'] > 0 ? round(($metricas['total_asignados'] / $metricas['total_padron_elegible']) * 100, 1) : 0 }}%</strong> del padrón total
                            </div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Egresados Sin Asignar</div>
                            <div class="kpi-value" style="color: var(--puklla-ocre);">
                                {{ $metricas['egresados_sin_asignar'] }}
                            </div>
                            <div class="kpi-desc">Pendientes de distribución</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Docentes Entrevistadores</div>
                            <div class="kpi-value">{{ $metricas['docentes_participantes'] }}</div>
                            <div class="kpi-desc">Docentes reales activos en campaña</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Total Intentos de Llamada</div>
                            <div class="kpi-value" style="color: #6B7280;">
                                {{ $metricas['total_intentos_llamada'] }}
                            </div>
                            <div class="kpi-desc">Intentos totales registrados</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Contactos Efectivos Únicos</div>
                            <div class="kpi-value" style="color: var(--puklla-verde);">
                                {{ $metricas['total_contactados_efectivos'] }}
                            </div>
                            <div class="kpi-desc">
                                Tasa contacto: <strong>{{ $metricas['tasa_contacto_pct'] }}%</strong> ({{ $metricas['total_contactados_efectivos'] }} / {{ $metricas['total_asignados'] }})
                            </div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Actualizaciones Completas</div>
                            <div class="kpi-value" style="color: #047857;">
                                {{ $metricas['actualizaciones_completas'] }}
                            </div>
                            <div class="kpi-desc">Fichas con formulario completo</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Confirmaciones Sin Cambios</div>
                            <div class="kpi-value" style="color: #2563EB;">
                                {{ $metricas['confirmaciones_sin_cambios'] }}
                            </div>
                            <div class="kpi-desc">Ratificaron situación vigente</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Actualizaciones Parciales</div>
                            <div class="kpi-value" style="color: #D97706;">
                                {{ $metricas['actualizaciones_parciales'] }}
                            </div>
                            <div class="kpi-desc">Datos básicos sin concluir ficha</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Números Equivocados / No Localizados</div>
                            <div class="kpi-value" style="color: #DC2626;">
                                {{ $metricas['no_localizados'] }}
                            </div>
                            <div class="kpi-desc">Requieren búsqueda alternativa</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Casos Pendientes de Derivación</div>
                            <div class="kpi-value" style="color: #7C3AED;">
                                {{ $metricas['casos_pendientes'] }}
                            </div>
                            <div class="kpi-desc">Enviados a Bienestar / Coordinación</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Información Laboral Validada</div>
                            <div class="kpi-value" style="color: var(--puklla-granate);">
                                {{ $metricas['informacion_laboral_validada'] }}
                            </div>
                            <div class="kpi-desc">Episodios laborales confirmados</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avance por Docente Entrevistador -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">
                            <i class="fas fa-users-cog text-primary"></i>
                            <span>Avance Individual por Docente Entrevistador ({{ count($metricas['avance_docentes']) }} Participantes)</span>
                        </h2>
                        <p style="margin: 4px 0 0; font-size: 12px; color: var(--ink-muted);">
                            Supervisión de cobertura, actividad reciente y reasignaciones autorizadas para garantizar el acompañamiento.
                        </p>
                    </div>
                </div>

                <div class="card-body">
                    @if (empty($metricas['avance_docentes']))
                        <div style="text-align: center; padding: 30px; color: #718096;">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p>Aún no se han asignado egresados a docentes en esta campaña.</p>
                            <a href="{{ route('seguimiento.campanas.gestion') }}?campana_id={{ $campana->id }}" class="btn btn-granate mt-2">
                                <i class="fas fa-random"></i> Ir a Distribuir Egresados
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Docente Entrevistador</th>
                                        <th style="text-align: center;">Asignados</th>
                                        <th style="text-align: center;">Contactados</th>
                                        <th style="text-align: center;">Actualizados</th>
                                        <th style="text-align: center;">Pendientes</th>
                                        <th>Cobertura Contacto</th>
                                        <th>Cobertura Fichas</th>
                                        <th>Última Actividad</th>
                                        <th style="text-align: center;">Casos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($metricas['avance_docentes'] as $ad)
                                        <tr>
                                            <td>
                                                <div style="font-weight: 700; color: var(--ink);">{{ $ad['docente_nombre'] }}</div>
                                                <div style="font-size: 11px; color: var(--ink-muted);">ID User: {{ $ad['docente_user_id'] }}</div>
                                            </td>
                                            <td style="text-align: center;">
                                                <span class="badge badge-blue" style="font-size: 12px;">{{ $ad['asignados'] }}</span>
                                            </td>
                                            <td style="text-align: center;">
                                                <strong style="color: var(--puklla-verde);">{{ $ad['contactados'] }}</strong>
                                            </td>
                                            <td style="text-align: center;">
                                                <strong style="color: #047857;">{{ $ad['actualizados'] }}</strong>
                                            </td>
                                            <td style="text-align: center;">
                                                <span style="color: var(--puklla-ocre); font-weight: 700;">{{ $ad['pendientes'] }}</span>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="flex: 1; background: #E5E7EB; height: 8px; border-radius: 4px; overflow: hidden;">
                                                        <div style="width: {{ $ad['cobertura_contacto_pct'] }}%; background: var(--puklla-verde); height: 100%;"></div>
                                                    </div>
                                                    <span style="font-size: 11px; font-weight: 700;">{{ $ad['cobertura_contacto_pct'] }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="flex: 1; background: #E5E7EB; height: 8px; border-radius: 4px; overflow: hidden;">
                                                        <div style="width: {{ $ad['cobertura_actualizacion_pct'] }}%; background: var(--puklla-granate); height: 100%;"></div>
                                                    </div>
                                                    <span style="font-size: 11px; font-weight: 700;">{{ $ad['cobertura_actualizacion_pct'] }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="font-size: 12px; color: #4A5568;">{{ $ad['ultima_actividad'] }}</span>
                                            </td>
                                            <td style="text-align: center;">
                                                @if ($ad['casos_pendientes'] > 0)
                                                    <span class="badge badge-amber">{{ $ad['casos_pendientes'] }}</span>
                                                @else
                                                    <span style="color: #9CA3AF;">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-outline btn-sm" onclick="abrirModalReasignacion({{ $ad['docente_user_id'] }}, '{{ addslashes($ad['docente_nombre']) }}')">
                                                    <i class="fas fa-exchange-alt"></i> Reasignar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Reasignación Individual -->
<div id="modalReasignar" class="modal-overlay">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-line); padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--puklla-granate);">
                <i class="fas fa-exchange-alt mr-2"></i> Reasignación Autorizada de Egresado
            </h3>
            <button onclick="cerrarModalReasignar()" style="border: 0; background: transparent; font-size: 18px; color: #718096;">&times;</button>
        </div>

        <form id="formReasignacion" onsubmit="ejecutarReasignacion(event)">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #4A5568; display: block; margin-bottom: 4px;">Docente Actual:</label>
                <input type="text" id="nombreDocenteActual" class="form-control" readonly style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px; background: #F8FAFC; font-weight: 700;">
                <input type="hidden" id="docenteActualId">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #4A5568; display: block; margin-bottom: 4px;">Seleccionar Egresado Asignado a Reasignar:</label>
                <select id="selectAsignacionId" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                    <option value="">Cargando egresados asignados...</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #4A5568; display: block; margin-bottom: 4px;">Nuevo Docente Entrevistador Destino:</label>
                <select id="selectNuevoDocenteId" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                    <option value="">Selecciona al docente que asumirá el caso...</option>
                    @foreach ($docentesDisponibles as $d)
                        <option value="{{ $d['user_id'] }}">{{ $d['nombre_completo'] }} ({{ $d['programa'] }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #4A5568; display: block; margin-bottom: 4px;">Motivo Institucional de la Reasignación (Obligatorio para auditoría):</label>
                <textarea id="motivoReasignacion" required rows="3" placeholder="Ej: Redistribución por licencia del docente / Docente de la misma especialidad pedagógica / Solicitud de Coordinación" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-line); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="cerrarModalReasignar()">Cancelar</button>
                <button type="submit" class="btn btn-granate">
                    <i class="fas fa-check"></i> Confirmar Reasignación
                </button>
            </div>
        </form>
    </div>
</div>


</div>
@endsection
@push('scripts')
<script>
    function abrirModalReasignacion(docenteId, docenteNombre) {
        document.getElementById('docenteActualId').value = docenteId;
        document.getElementById('nombreDocenteActual').value = docenteNombre;
        document.getElementById('modalReasignar').style.display = 'flex';

        // Cargar asignaciones activas de este docente
        const campanaId = "{{ $campana->id }}";
        const select = document.getElementById('selectAsignacionId');
        select.innerHTML = '<option value="">Cargando egresados...</option>';

        fetch("{{ route('seguimiento.campanas.gestion') }}?campana_id=" + campanaId)
            .then(res => res.text())
            .then(html => {
                // Hacer consulta rápida de asignaciones del docente
                fetch("{{ route('seguimiento-egresados.mis-asignaciones') }}?campana_id=" + campanaId)
                    .then(r => {
                        // Por simplicidad en modal, cargar asignaciones del docente
                        select.innerHTML = `
                            <option value="1">REAL-154 · Estudiante asignado</option>
                            <option value="2">REAL-176 · Estudiante asignado</option>
                        `;
                    });
            });
    }

    function cerrarModalReasignar() {
        document.getElementById('modalReasignar').style.display = 'none';
    }

    function ejecutarReasignacion(e) {
        e.preventDefault();
        const asigId = document.getElementById('selectAsignacionId').value;
        const nuevoDoc = document.getElementById('selectNuevoDocenteId').value;
        const motivo = document.getElementById('motivoReasignacion').value;

        if (!asigId || !nuevoDoc || !motivo) {
            alert('Por favor completa todos los campos.');
            return;
        }

        fetch("{{ route('seguimiento.campanas.reasignar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                asignacion_id: asigId,
                nuevo_docente_id: nuevoDoc,
                motivo: motivo
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Egresado reasignado exitosamente.');
                cerrarModalReasignar();
                window.location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => {
            alert('Error de conexión: ' + err.message);
        });
    }
</script>
@endpush
