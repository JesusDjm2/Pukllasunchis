<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Puklla en Red · Observatorio Territorial y Actualización Geográfica de Egresados</title>
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
            background: rgba(27, 122, 73, 0.08);
            color: var(--puklla-verde);
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
            background: linear-gradient(135deg, #1B7A49 0%, #064E3B 100%);
            color: #FFF;
            padding: 24px 30px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px -5px rgba(27, 122, 73, 0.3);
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

        /* Comparison Grid */
        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        @media (max-width: 900px) {
            .comparison-grid { grid-template-columns: 1fr; }
        }

        /* KPI Card */
        .kpi-card {
            background: #FFF;
            border: 1px solid var(--border-line);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .kpi-value {
            font-size: 24px;
            font-weight: 800;
            color: var(--puklla-verde);
            margin: 4px 0;
        }
        .kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--ink-muted);
        }

        /* Geographic Bars */
        .geo-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .geo-label { width: 140px; font-weight: 700; font-size: 12px; color: #374151; }
        .geo-track { flex: 1; background: #E5E7EB; height: 16px; border-radius: 8px; overflow: hidden; }
        .geo-fill { background: var(--puklla-verde); height: 100%; border-radius: 8px; }
        .geo-count { width: 45px; text-align: right; font-weight: 800; font-size: 13px; color: var(--ink); }

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
            text-decoration: none;
        }
        .btn-verde { background: var(--puklla-verde); color: #FFF; }
        .btn-granate { background: var(--puklla-granate); color: #FFF; }
        .btn-outline { background: transparent; border: 1px solid var(--border-line); color: var(--ink); }
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
        <a href="{{ route('seguimiento.campanas.gestion') }}" class="nav-item">
            <span class="nav-icon"><i class="fas fa-bullhorn"></i></span>
            <span>Gestión de Campañas</span>
        </a>
        <a href="{{ route('seguimiento.campanas.monitoreo', ['campanaId' => $campana->id]) }}" class="nav-item">
            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
            <span>Monitoreo de Avance</span>
        </a>
        <a href="{{ route('seguimiento.campanas.territorio', ['campanaId' => $campana->id]) }}" class="nav-item active">
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
        <header class="top-navbar">
            <div class="top-badge">
                <i class="fas fa-globe-americas mr-1"></i>
                <span>Observatorio Territorial · Geografía de Egresados</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size:13px;font-weight:700;color:#4A5568;">
                    {{ auth()->user()->name }}
                </span>
                <span style="background: #DEF7EC; color: #03543F; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                    Privacidad Estadística Activa
                </span>
            </div>
        </header>

        <div class="container">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <div>
                    <h1 class="hero-title">Observatorio Territorial de Egresados</h1>
                    <p class="hero-desc">
                        Compara el estado del conocimiento geográfico antes y después de la campaña. Distingue entre datos confirmados, modificaciones territoriales (mudanzas) y datos completados por primera vez.
                    </p>
                </div>
                <div>
                    <a href="{{ route('seguimiento.campanas.monitoreo', ['campanaId' => $campana->id]) }}" class="btn btn-granate">
                        <i class="fas fa-chart-line"></i> Volver a Monitoreo
                    </a>
                </div>
            </div>

            <!-- Privacidad Estadística y UBIGEO Notice -->
            <div style="background: #F0FDF4; border: 1px solid #BBF7D0; padding: 12px 18px; border-radius: 8px; color: #166534; font-size: 13px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-shield-alt fa-lg"></i>
                <span>
                    <strong>Criterio de Privacidad Institucional:</strong> Las representaciones territoriales se agregan por Departamento, Provincia y Distrito con codificación oficial UBIGEO. No se exponen coordenadas exactas ni direcciones domiciliarias privadas.
                </span>
            </div>

            <!-- Comparación Antes y Después -->
            <div class="comparison-grid">
                <!-- Estado Previo (Basal) -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history text-secondary"></i>
                            <span>Estado Basal Previo a la Campaña</span>
                        </h2>
                        <span style="background: #F3F4F6; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; color: #374151;">
                            Padrón: {{ $metricasTerritoriales['total_padron'] }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="kpi-card">
                            <div class="kpi-label">Residencia Previa Registrada</div>
                            <div class="kpi-value" style="color: #2563EB;">
                                {{ $metricasTerritoriales['basal']['residencia_conocida'] }}
                            </div>
                            <div style="font-size: 12px; color: #718096;">
                                Cobertura basal: <strong>{{ round(($metricasTerritoriales['basal']['residencia_conocida'] / $metricasTerritoriales['total_padron']) * 100, 1) }}%</strong>
                            </div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Residencia Previa Desconocida</div>
                            <div class="kpi-value" style="color: #D97706;">
                                {{ $metricasTerritoriales['basal']['residencia_desconocida'] }}
                            </div>
                            <div style="font-size: 12px; color: #718096;">
                                Casos sin departamento/provincia en ficha histórica
                            </div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Lugar de Trabajo Previo Conocido</div>
                            <div class="kpi-value" style="color: #059669;">
                                {{ $metricasTerritoriales['basal']['trabajo_conocido'] }}
                            </div>
                            <div style="font-size: 12px; color: #718096;">
                                Con institución empleadora y ubicación declarada
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado Posterior (Actualizaciones en Campaña) -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-sync-alt text-success"></i>
                            <span>Actualizaciones Territoriales en la Campaña</span>
                        </h2>
                        <span style="background: #DEF7EC; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; color: #03543F;">
                            Campaña: {{ $campana->codigo }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="kpi-card">
                            <div class="kpi-label">Residencia Confirmada Sin Cambios</div>
                            <div class="kpi-value" style="color: #10B981;">
                                {{ $metricasTerritoriales['actualizado']['residencia_confirmada'] }}
                            </div>
                            <div style="font-size: 12px; color: #718096;">
                                Ratificaron su residencia institucional vigente
                            </div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Residencia Registrada Por Primera Vez</div>
                            <div class="kpi-value" style="color: #047857;">
                                {{ $metricasTerritoriales['actualizado']['residencia_primera_vez'] }}
                            </div>
                            <div style="font-size: 12px; color: #718096;">
                                Casos que carecían de datos y fueron completados
                            </div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">Residencia Modificada (Mudanzas Confirmadas)</div>
                            <div class="kpi-value" style="color: #6366F1;">
                                {{ $metricasTerritoriales['actualizado']['residencia_modificada'] }}
                            </div>
                            <div style="font-size: 12px; color: #718096;">
                                Cambio efectivo de domicilio respecto al registro previo
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribución Geográfica Agregada -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-map-signs text-warning"></i>
                        <span>Distribución Territorial por Provincias de la Región Cusco</span>
                    </h2>
                    <span style="background: #FEF3C7; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; color: #854D0E;">
                        Ámbito Regional Principal
                    </span>
                </div>
                <div class="card-body">
                    <p style="font-size: 13px; color: #6B7280; margin-bottom: 16px;">
                        Concentración de egresados según provincia de residencia declarada o asignada en el padrón:
                    </p>

                    @php
                        $maxProv = max($metricasTerritoriales['distribucion_provincias_cusco'] ?: [1]);
                    @endphp

                    @foreach ($metricasTerritoriales['distribucion_provincias_cusco'] as $provincia => $cantidad)
                        <div class="geo-bar">
                            <div class="geo-label">{{ $provincia }}</div>
                            <div class="geo-track">
                                <div class="geo-fill" style="width: {{ round(($cantidad / $maxProv) * 100) }}%;"></div>
                            </div>
                            <div class="geo-count">{{ $cantidad }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Distribución por Departamentos del Perú -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-flag text-danger"></i>
                        <span>Presencia de Egresados por Departamento</span>
                    </h2>
                </div>
                <div class="card-body">
                    @php
                        $maxDep = max($metricasTerritoriales['distribucion_departamentos'] ?: [1]);
                    @endphp

                    @foreach ($metricasTerritoriales['distribucion_departamentos'] as $departamento => $cantidad)
                        <div class="geo-bar">
                            <div class="geo-label">{{ $departamento }}</div>
                            <div class="geo-track">
                                <div class="geo-fill" style="width: {{ round(($cantidad / $maxDep) * 100) }}%; background: var(--puklla-granate);"></div>
                            </div>
                            <div class="geo-count">{{ $cantidad }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
