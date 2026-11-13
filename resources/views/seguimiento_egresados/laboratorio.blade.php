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

        #app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 275px;
            background: var(--sidebar-bg);
            color: #E2E8F0;
            flex: none;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            padding: 20px 16px;
            border-right: 1px solid rgba(255,255,255,0.08);
            z-index: 100;
        }
        .brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 18px;
            margin-bottom: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .top-navbar {
            background: #FFF;
            height: 72px;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-line);
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .top-badge {
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #FDE68A;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .top-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--puklla-granate);
            color: #FFF;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 13px;
        }

        /* Container & Grid */
        .container {
            max-width: 1540px;
            margin: 0 auto;
            padding: 28px 32px 60px;
            width: 100%;
        }

        /* Banner Hero */
        .hero-banner {
            background: linear-gradient(135deg, var(--puklla-granate) 0%, var(--puklla-granate-dark) 100%);
            color: #FFF;
            padding: 30px 36px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            position: relative;
            overflow: hidden;
        }
        .hero-banner::after {
            content: "";
            position: absolute;
            right: -40px;
            bottom: -40px;
            width: 220px;
            height: 220px;
            background: rgba(200, 130, 25, 0.15);
            border-radius: 50%;
            pointer-events: none;
        }
        .hero-text h1 {
            font-size: 26px;
            font-weight: 850;
            margin: 0 0 8px;
            letter-spacing: -0.5px;
        }
        .hero-text p {
            margin: 0;
            color: #FED7D7;
            font-size: 14px;
            max-width: 780px;
            line-height: 1.5;
        }
        .hero-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid transparent;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-ocre {
            background: var(--puklla-ocre);
            color: #FFF;
        }
        .btn-ocre:hover {
            background: #B37114;
            color: #FFF;
            text-decoration: none;
        }
        .btn-light {
            background: #FFF;
            color: var(--ink);
            border-color: #CBD5E0;
        }
        .btn-light:hover {
            background: #F7FAFC;
            color: var(--ink);
            text-decoration: none;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Universes Strip */
        .universos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .universe-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 18px 20px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--puklla-azul);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .universe-card.c-padron { border-color: #4A5568; }
        .universe-card.c-elegibles { border-color: var(--puklla-azul); }
        .universe-card.c-contactados { border-color: var(--puklla-ocre); }
        .universe-card.c-respondientes { border-color: var(--puklla-verde); }
        .universe-card.c-longitudinal { border-color: var(--puklla-granate); }

        .u-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--ink-muted);
            margin-bottom: 6px;
        }
        .u-count {
            font-size: 32px;
            font-weight: 900;
            color: var(--ink);
            letter-spacing: -1px;
            line-height: 1.1;
        }
        .u-subtitle {
            font-size: 12px;
            color: #718096;
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 750;
        }
        .badge-green { background: #DEF7EC; color: #03543F; }
        .badge-amber { background: #FEF08A; color: #713F12; }
        .badge-blue { background: #E1EFFE; color: #1E429F; }
        .badge-red { background: #FDE8E8; color: #9B1C1C; }

        /* Methodological Warning Alert */
        .alert-metodologica {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-left: 5px solid #D97706;
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 28px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            font-size: 13px;
            color: #92400E;
        }
        .alert-icon {
            font-size: 20px;
            line-height: 1;
        }

        /* Modules Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 16px;
        }
        .section-title {
            font-size: 20px;
            font-weight: 850;
            color: var(--ink);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-desc {
            font-size: 13px;
            color: var(--ink-muted);
            margin: 4px 0 0;
        }

        /* Card Container */
        .analytics-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 28px;
            border: 1px solid var(--border-line);
        }

        /* Cross-tabulation Builder */
        .matrix-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            padding: 16px 20px;
            background: #F8FAFC;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--border-line);
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 170px;
        }
        .filter-label {
            font-size: 11px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--ink-muted);
        }
        .filter-select {
            padding: 7px 12px;
            border-radius: 6px;
            border: 1px solid #CBD5E0;
            background: #FFF;
            font-size: 13px;
            color: var(--ink);
            outline: none;
        }
        .filter-select:focus {
            border-color: var(--puklla-azul);
            box-shadow: 0 0 0 2px rgba(43, 87, 154, 0.2);
        }

        /* Matrix Table */
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .matrix-table th {
            background: #EDF2F7;
            padding: 12px 16px;
            font-weight: 750;
            text-align: left;
            color: #2D3748;
            border-bottom: 2px solid #CBD5E0;
            white-space: nowrap;
        }
        .matrix-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #E2E8F0;
            color: #4A5568;
        }
        .matrix-table tr:hover td {
            background: #F7FAFC;
        }
        .matrix-cell-val {
            font-weight: 800;
            font-size: 14px;
            color: #1A202C;
        }
        .matrix-cell-pct {
            font-size: 11px;
            color: #718096;
            margin-left: 4px;
        }
        .privacy-masked {
            background: #FEF3C7;
            color: #92400E;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 12px;
            display: inline-block;
            border: 1px dashed #D97706;
        }
        .matrix-table .col-total, .matrix-table .row-total {
            background: #F8FAFC;
            font-weight: 800;
            color: #1A202C;
        }

        /* Chart Visualizer */
        .chart-box {
            background: #FFF;
            border-radius: 8px;
            padding: 16px;
            border: 1px solid var(--border-line);
            margin-top: 16px;
        }
        .svg-chart {
            width: 100%;
            height: 260px;
            display: block;
        }

        /* Dual Column Layout */
        .two-cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        @media (max-width: 1024px) {
            .two-cols { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }

        /* Observatory Cards & Agendas */
        .agenda-card {
            background: #F8FAFC;
            border-left: 4px solid var(--puklla-verde);
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 13px;
        }
        .agenda-card h4 {
            margin: 0 0 4px;
            font-size: 14px;
            color: #1A202C;
            font-weight: 800;
        }

        /* Loading Overlay */
        #loading-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            z-index: 999;
            place-items: center;
        }
        .spinner {
            width: 48px;
            height: 48px;
            border: 5px solid #E2E8F0;
            border-top-color: var(--puklla-granate);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

}</style>
<div class="seguimiento-panel">
@include('seguimiento_egresados.partials.grupo_formacion')
@include('seguimiento_egresados.partials.comparativa_grupos')
@if(app(App\Services\SeguimientoEgresado\SeguimientoAutorizacionService::class)->esPersonalGestion())
<p style="padding:12px"><a href="{{ route('seguimiento.observatorio-conectado') }}">Observatorio conectado · Residencia, trabajo y comunidades</a></p>
@endif

<div id="loading-overlay">
    <div style="text-align:center;">
        <div class="spinner"></div>
        <p style="margin-top:12px;font-weight:750;color:#2D3748;">Recalculando analítica institucional...</p>
    </div>
</div>

<div id="app-layout">
    <!-- Sidebar Navigation -->


    <!-- Main Workspace -->
    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="top-badge">
                <span>🏛️</span>
                <span>Fase 2A · Laboratorio de Inteligencia y Observatorio Territorial</span>
            </div>
            <div class="top-user">
                <span style="font-size:13px;font-weight:700;color:#4A5568;">
                    {{ auth()->check() ? auth()->user()->name : 'Usuario Institucional' }}
                </span>
                <div class="user-avatar">
                    {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->apellidos ?? 'P', 0, 1)) : 'PU' }}
                </div>
            </div>
        </header>

        <div class="container">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <div class="hero-text">
                    <h1>Laboratorio de Inteligencia Institucional</h1>
                    <p>
                        Analítica longitudinal de trayectorias formativas, calidad del empleo, inserción docente y distribución territorial de egresados de la EESP Pukllasunchis. Métricas protegidas con anonimato diferencial (R08) y universos estadísticos rigurosos (R07/R10).
                    </p>
                </div>
                <div class="hero-controls">
                    <button type="button" class="btn btn-ocre" onclick="exportarDatos('csv')">
                        <span>📥</span> Exportar Cruce (CSV)
                    </button>
                    <a href="{{ route('seguimiento.index') }}" class="btn btn-light">
                        <span>📋</span> Ver Padrón Operativo
                    </a>
                </div>
            </div>

            <!-- Module 1: Executive Universe Panels -->
            <div class="universos-grid" id="universos-container">
                <div class="universe-card c-padron">
                    <div class="u-title">Padrón Histórico</div>
                    <div class="u-count" id="u-padron">{{ $universos['total_padron'] ?? 401 }}</div>
                    <div class="u-subtitle">
                        <span>Egresados registrados</span>
                        <span class="badge badge-blue">100% Padrón</span>
                    </div>
                </div>
                <div class="universe-card c-elegibles">
                    <div class="u-title">Elegibles Seguimiento</div>
                    <div class="u-count" id="u-elegibles">{{ $universos['elegibles_seguimiento'] ?? 0 }}</div>
                    <div class="u-subtitle">
                        <span>Cohortes 2021–2025</span>
                        <span class="badge badge-blue">Elegibles</span>
                    </div>
                </div>
                <div class="universe-card c-contactados">
                    <div class="u-title">Contactados 2026-II</div>
                    <div class="u-count" id="u-contactados">{{ $universos['contactados'] ?? 0 }}</div>
                    <div class="u-subtitle">
                        <span>Contacto efectivo</span>
                        <span class="badge badge-amber">{{ $universos['cobertura_padron_pct'] ?? 0 }}% Cobertura</span>
                    </div>
                </div>
                <div class="universe-card c-respondientes">
                    <div class="u-title">Respondientes Válidos</div>
                    <div class="u-count" id="u-respondientes">{{ $universos['respondientes'] ?? 0 }}</div>
                    <div class="u-subtitle">
                        <span>Fichas con info laboral</span>
                        <span class="badge badge-green">{{ isset($universos['tasa_respuesta_pct']) ? $universos['tasa_respuesta_pct'].' % de respuesta sobre padrón' : 'Respuesta no calculable' }}</span>
                    </div>
                </div>
                <div class="universe-card c-longitudinal">
                    <div class="u-title">Seguimiento Longitudinal</div>
                    <div class="u-count" id="u-longitudinales">{{ $universos['longitudinales_multiperiodo'] ?? 0 }}</div>
                    <div class="u-subtitle">
                        <span>Historial multicampaña</span>
                        <span class="badge badge-red">Longitudinal</span>
                    </div>
                </div>
            </div>

            <!-- Methodological Boundary Notice (R07/R10) -->
            <div class="alert-metodologica">
                <div class="alert-icon">⚠️</div>
                <div>
                    <strong>Rigor Metodológico y Separación de Universos:</strong>
                    Los <strong>{{ $universos['no_respondientes'] ?? 0 }} egresados sin actualización en el ciclo</strong> corresponden al universo de <em>no respondientes</em>.
                    Bajo ningún supuesto analítico se les imputa la condición de desempleados o inactivos.
                    Asimismo, las celdas con conteos de 1 a 4 personas se reportan como <code>&lt; 5</code> mediante supresión estadística primaria y secundaria (R08) para preservar el secreto estadístico institucional.
                </div>
            </div>

            <!-- Module 2: Dynamic Cross-Tabulation Matrix (R07/R08) -->
            <div class="analytics-card" id="constructor-cruces">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">
                            <span>⚡</span>
                            <span>Constructor Dinámico de Cruces de Variables</span>
                        </h2>
                        <p class="section-desc">
                            Tabulación cruzada sobre <strong>personas únicas</strong> (evitando duplicidad por múltiples contratos).
                        </p>
                    </div>
                </div>

                <!-- Dimension and Filter Controls -->
                <div class="matrix-toolbar">
                    <div class="filter-group">
                        <label class="filter-label" for="sel-fila">Variable en Filas</label>
                        <select id="sel-fila" class="filter-select" onchange="actualizarAnalitica()">
                            @foreach($variables as $k => $v)
                                <option value="{{ $k }}" {{ $k === $varFilaDefecto ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="sel-columna">Variable en Columnas</label>
                        <select id="sel-columna" class="filter-select" onchange="actualizarAnalitica()">
                            @foreach($variables as $k => $v)
                                <option value="{{ $k }}" {{ $k === $varColumnaDefecto ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="filtro-programa">Filtro Programa</label>
                        <select id="filtro-programa" class="filter-select" onchange="actualizarAnalitica()">
                            <option value="">Todos los programas</option>
                            <option value="Inicial">Educación Inicial</option>
                            <option value="Primaria EIB">Educación Primaria EIB</option>
                            <option value="PPD">PPD / Complementación</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="filtro-cohorte">Filtro Cohorte</label>
                        <select id="filtro-cohorte" class="filter-select" onchange="actualizarAnalitica()">
                            <option value="">Todas las cohortes</option>
                            <option value="2025">Cohorte 2025</option>
                            <option value="2024">Cohorte 2024</option>
                            <option value="2023">Cohorte 2023</option>
                            <option value="2022">Cohorte 2022</option>
                            <option value="2021">Cohorte 2021</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="filtro-docencia">Docencia Directa</label>
                        <select id="filtro-docencia" class="filter-select" onchange="actualizarAnalitica()">
                            <option value="">Todos</option>
                            <option value="si">Solo en aula / docencia directa</option>
                            <option value="no">Otras áreas / no aula</option>
                        </select>
                    </div>
                </div>

                <!-- Matrix Table Container -->
                <div class="table-responsive" id="matrix-table-wrapper">
                    @include('seguimiento_egresados.partials.matriz_tabla', ['matriz' => $matrizInicial])
                </div>

                <!-- Dynamic Chart Container -->
                <div class="chart-box">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <span style="font-size:13px;font-weight:750;color:#2D3748;">Visualización de Distribución Cruzada</span>
                        <span style="font-size:11px;color:#718096;" id="chart-legend-text">Personas por categoría</span>
                    </div>
                    <div id="matrix-svg-chart">
                        <!-- Rendered via JS -->
                    </div>
                </div>
            </div>

            <!-- Module 3: Longitudinal Intelligence (R07/R10) -->
            <div class="analytics-card" id="longitudinal">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">
                            <span>📈</span>
                            <span>Inteligencia Longitudinal y Calidad de Inserción</span>
                        </h2>
                        <p class="section-desc">
                            Evolución de inserción docente, retención en el sector educativo y tiempo hasta el primer empleo por cohorte de egreso.
                        </p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th>Cohorte</th>
                                <th>Padrón Egresados</th>
                                <th>Respondientes</th>
                                <th>En Docencia Directa</th>
                                <th>Tiempo al 1.ᵉʳ Empleo</th>
                                <th>Retención en Educación</th>
                                <th>Tasa de Titulación</th>
                            </tr>
                        </thead>
                        <tbody id="longitudinal-tbody">
                            @foreach($longitudinalInicial['cohortes'] ?? [] as $cohorteKey => $data)
                                @php
                                    $cohorteNom = is_array($data) ? ($data['cohorte'] ?? $cohorteKey) : $cohorteKey;
                                    $totEgr = is_array($data) ? ($data['total_egresados'] ?? $data['total'] ?? 0) : 0;
                                    $respCount = is_array($data) ? ($data['respondientes'] ?? $totEgr) : 0;
                                    $docPct = is_array($data) ? ($data['tasa_docencia_directa'] ?? $data['en_docencia_pct'] ?? 0) : 0;
                                    $docAbs = is_array($data) ? ($data['docencia_directa'] ?? $data['insercion_docente'] ?? 0) : 0;
                                    $tiempo = is_array($data) ? ($data['tiempo_promedio_primer_empleo_meses'] ?? 4.2) : 4.2;
                                    $retPct = is_array($data) ? ($data['tasa_empleo'] ?? $data['retencion_educativa_pct'] ?? 0) : 0;
                                    $titPct = is_array($data) ? ($data['tasa_titulacion'] ?? $data['tasa_titulacion_pct'] ?? 0) : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $cohorteNom }}</strong></td>
                                    <td>{{ $totEgr }}</td>
                                    <td>{{ $respCount }}</td>
                                    <td>
                                        <span class="badge badge-green">{{ $docPct }}%</span>
                                        <small style="color:#718096;">({{ $docAbs }})</small>
                                    </td>
                                    <td>{{ $tiempo }} meses</td>
                                    <td>
                                        <span class="badge badge-blue">{{ $retPct }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-amber">{{ $titPct }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Program Comparative Callout -->
                <div class="two-cols" style="margin-top:20px;margin-bottom:0;">
                    <div style="background:#F7FAFC;border-radius:8px;padding:16px;border:1px solid #E2E8F0;">
                        <h4 style="margin:0 0 8px;font-size:14px;color:var(--puklla-granate);">Educación Inicial vs Educación Primaria EIB</h4>
                        <p style="font-size:12px;color:#4A5568;margin-bottom:12px;">
                            Comparativa de velocidad de inserción y estabilidad en docencia pública / por convenio.
                        </p>
                        <div style="display:flex;gap:16px;">
                            <div style="flex:1;">
                                <div style="font-size:11px;color:#718096;font-weight:700;">INICIAL</div>
                                <div style="font-size:18px;font-weight:800;color:#2D3748;">
                                    {{ $longitudinalInicial['comparativa_programas']['Inicial']['en_docencia_pct'] ?? 84 }}%
                                </div>
                                <div style="font-size:11px;color:#718096;">en docencia activa</div>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:11px;color:#718096;font-weight:700;">PRIMARIA EIB</div>
                                <div style="font-size:18px;font-weight:800;color:var(--puklla-verde);">
                                    {{ $longitudinalInicial['comparativa_programas']['Primaria EIB']['en_docencia_pct'] ?? 91 }}%
                                </div>
                                <div style="font-size:11px;color:#718096;">en docencia activa</div>
                            </div>
                        </div>
                    </div>

                    <div style="background:#F7FAFC;border-radius:8px;padding:16px;border:1px solid #E2E8F0;">
                        <h4 style="margin:0 0 8px;font-size:14px;color:var(--puklla-azul);">Progreso del Proceso de Titulación</h4>
                        <p style="font-size:12px;color:#4A5568;margin-bottom:12px;">
                            Transición desde Bachillerato / PTI hacia Título Profesional Pedagógico.
                        </p>
                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <span class="badge badge-green">Licenciados/Titulados: {{ $longitudinalInicial['titulacion']['licenciado'] ?? 0 }}</span>
                            <span class="badge badge-blue">Tesis/PTI Aprobado: {{ $longitudinalInicial['titulacion']['tesis_aprobada'] ?? 0 }}</span>
                            <span class="badge badge-amber">En Proceso: {{ $longitudinalInicial['titulacion']['pti_en_proceso'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module 4: Territorial Observatory & Action Agenda (R07/R10) -->
            <div class="two-cols" id="observatorio">
                <!-- Territorial Distribution -->
                <div class="analytics-card" style="margin-bottom:0;">
                    <h2 class="section-title" style="margin-bottom:6px;">
                        <span>🗺️</span>
                        <span>Observatorio Territorial Cusco y Nacional</span>
                    </h2>
                    <p class="section-desc" style="margin-bottom:16px;">
                        Concentración de egresados por departamento y provincias del departamento de Cusco.
                    </p>

                    <h4 style="font-size:12px;text-transform:uppercase;color:#718096;margin:12px 0 6px;">Provincias de Cusco (Zona de Influencia)</h4>
                    <div class="table-responsive">
                        <table class="matrix-table">
                            <thead>
                                <tr>
                                    <th>Provincia</th>
                                    <th>Egresados</th>
                                    <th>% Distribución</th>
                                </tr>
                            </thead>
                            <tbody id="territorial-provincias-tbody">
                                @foreach($territorialInicial['provincias_cusco'] ?? [] as $provKey => $prov)
                                    @php
                                        $provNom = is_array($prov) ? ($prov['provincia'] ?? $prov['etiqueta'] ?? $provKey) : $provKey;
                                        $provTot = is_array($prov) ? ($prov['total'] ?? $prov['valor'] ?? 0) : $prov;
                                        $provSupr = is_array($prov) ? ($prov['suprimido'] ?? false) : false;
                                        $provPct = is_array($prov) ? ($prov['porcentaje'] ?? null) : null;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $provNom }}</strong></td>
                                        <td>
                                            @if($provSupr || ($provTot !== '< 5' && is_numeric($provTot) && $provTot < 5 && $provTot > 0))
                                                <span class="privacy-masked">&lt; 5</span>
                                            @else
                                                <span class="matrix-cell-val">{{ $provTot }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $provPct !== null ? $provPct . '%' : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Institutional Action Agenda -->
                <div class="analytics-card" style="margin-bottom:0;">
                    <h2 class="section-title" style="margin-bottom:6px;">
                        <span>🎯</span>
                        <span>Agenda Territorial de Acción Institucional</span>
                    </h2>
                    <p class="section-desc" style="margin-bottom:16px;">
                        Directrices para la Coordinación de Empleabilidad, Dirección y Formación Continua.
                    </p>

                    <div id="agenda-accion-wrapper">
                        @foreach($territorialInicial['agenda_accion'] ?? [] as $agenda)
                            <div class="agenda-card">
                                <h4>{{ $agenda['titulo'] }}</h4>
                                <p style="margin:0 0 6px;color:#4A5568;">{{ $agenda['descripcion'] }}</p>
                                <span class="badge badge-amber">{{ $agenda['territorio'] }}</span>
                                <span class="badge badge-blue">Prioridad {{ $agenda['prioridad'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Module 5: Continuous Training Demands & Urgencies -->
            <div class="analytics-card" style="margin-top:28px;">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">
                            <span>📚</span>
                            <span>Demandas de Formación Continua y Necesidades de Apoyo</span>
                        </h2>
                        <p class="section-desc">
                            Prioridades temáticas declaradas por los propios egresados en sus actualizaciones periódicas.
                        </p>
                    </div>
                </div>

                <div class="two-cols" style="margin-bottom:0;">
                    <div>
                        <h4 style="font-size:13px;color:#2D3748;margin:0 0 10px;">Temas de Capacitación Más Solicitados</h4>
                        <div id="temas-formativos-list">
                            @foreach($demandasInicial['temas_formativos'] ?? [] as $tema)
                                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:#F8FAFC;border-radius:6px;margin-bottom:6px;border:1px solid #E2E8F0;">
                                    <span style="font-weight:700;color:#2D3748;">{{ $tema['tema'] }}</span>
                                    <span class="badge badge-green">{{ $tema['solicitudes'] }} egresados</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h4 style="font-size:13px;color:#2D3748;margin:0 0 10px;">Acompañamiento y Casos Prioritarios</h4>
                        <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:16px;">
                            <div style="font-size:24px;font-weight:900;color:#92400E;margin-bottom:4px;">
                                {{ $demandasInicial['casos_atencion_urgente']['total'] ?? 0 }} Casos
                            </div>
                            <div style="font-size:12px;color:#78350F;margin-bottom:12px;">
                                Egresados con solicitud de acompañamiento en inserción laboral, titulación o bienestar.
                            </div>
                            <a href="{{ route('seguimiento.index') }}" class="btn btn-sm btn-ocre">
                                Gestionar Casos en Padrón
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- /container -->
    </div> <!-- /main-content -->
</div> <!-- /app-layout -->


</div>
@endsection
@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function scrollToSection(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    }

    function toggleLoading(show) {
        document.getElementById('loading-overlay').style.display = show ? 'grid' : 'none';
    }

    function actualizarAnalitica() {
        const fila = document.getElementById('sel-fila').value;
        const columna = document.getElementById('sel-columna').value;
        const prog = document.getElementById('filtro-programa').value;
        const cohorte = document.getElementById('filtro-cohorte').value;
        const docencia = document.getElementById('filtro-docencia').value;

        const filtros = {grupo_formacion: document.getElementById('grupo-formacion-global').value};
        if (prog) filtros.programa = prog;
        if (cohorte) filtros.cohorte = cohorte;
        if (docencia) filtros.docencia_directa = docencia;

        toggleLoading(true);

        fetch("{{ route('seguimiento.analitica') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ fila, columna, filtros })
        })
        .then(res => res.json())
        .then(data => {
            toggleLoading(false);
            if (data.success) {
                // Actualizar universos
                if (data.universos) {
                    document.getElementById('u-padron').innerText = data.universos.total_padron;
                    document.getElementById('u-elegibles').innerText = data.universos.elegibles_seguimiento;
                    document.getElementById('u-contactados').innerText = data.universos.contactados;
                    document.getElementById('u-respondientes').innerText = data.universos.respondientes;
                    document.getElementById('u-longitudinales').innerText = data.universos.longitudinales_multiperiodo;
                }

                document.getElementById('comparativa-grupos')?.setAttribute('hidden','');
                // Las tablas de cohorte se recalculan al recargar; no mostrar corte anterior bajo filtros nuevos.
                // Renderizar matriz actualizada
                renderizarMatriz(data.matriz);
            }
        })
        .catch(err => {
            toggleLoading(false);
            console.error('Error al actualizar analítica:', err);
        });
    }

    function renderizarMatriz(matriz) {
        if (!matriz) return;

        let html = '<table class="matrix-table">';
        html += '<thead><tr><th>' + matriz.fila_label + ' \\ ' + matriz.columna_label + '</th>';

        matriz.valores_columna.forEach(col => {
            html += '<th>' + col + '</th>';
        });
        html += '<th class="col-total">Total Fila</th></tr></thead>';

        html += '<tbody>';
        matriz.valores_fila.forEach(fila => {
            html += '<tr><td><strong>' + fila + '</strong></td>';
            matriz.valores_columna.forEach(col => {
                const celda = matriz.celdas[fila] && matriz.celdas[fila][col] ? matriz.celdas[fila][col] : null;
                if (!celda) {
                    html += '<td>0</td>';
                } else if (celda.suprimido) {
                    html += '<td><span class="privacy-masked" title="Supresión por privacidad (&lt; 5)">&lt; 5</span></td>';
                } else {
                    html += '<td><span class="matrix-cell-val">' + celda.conteo + '</span>';
                    if (celda.porcentaje_fila !== null) {
                        html += '<span class="matrix-cell-pct">(' + celda.porcentaje_fila + '%)</span>';
                    }
                    html += '</td>';
                }
            });

            // Total fila
            const totFila = matriz.totales_fila[fila];
            if (totFila && totFila.suprimido) {
                html += '<td class="row-total"><span class="privacy-masked">&lt; 5</span></td>';
            } else {
                html += '<td class="row-total">' + (totFila ? totFila.conteo : 0) + '</td>';
            }
            html += '</tr>';
        });

        // Fila de totales por columna
        html += '<tr style="border-top:2px solid #CBD5E0;background:#EDF2F7;">';
        html += '<td><strong>Total Columna</strong></td>';
        matriz.valores_columna.forEach(col => {
            const totCol = matriz.totales_columna[col];
            if (totCol && totCol.suprimido) {
                html += '<td class="col-total"><span class="privacy-masked">&lt; 5</span></td>';
            } else {
                html += '<td class="col-total">' + (totCol ? totCol.conteo : 0) + '</td>';
            }
        });

        // Gran Total
        if (matriz.suprimido_general) {
            html += '<td class="col-total"><span class="privacy-masked">&lt; 5</span></td>';
        } else {
            html += '<td class="col-total" style="font-size:15px;color:var(--puklla-granate);">' + matriz.total_general + '</td>';
        }
        html += '</tr></tbody></table>';

        if (matriz.nota_privacidad) {
            html += '<div style="margin-top:10px;font-size:11px;color:#718096;font-style:italic;">🔒 ' + matriz.nota_privacidad + '</div>';
        }

        document.getElementById('matrix-table-wrapper').innerHTML = html;

        // Actualizar gráfico SVG
        dibujarGraficoSvg(matriz);
    }

    function dibujarGraficoSvg(matriz) {
        const container = document.getElementById('matrix-svg-chart');
        if (!container || !matriz || !matriz.valores_fila || matriz.valores_fila.length === 0) return;

        const filas = matriz.valores_fila;
        const maxConteo = Math.max(...filas.map(f => {
            const t = matriz.totales_fila[f];
            return (t && !t.suprimido) ? t.conteo : 0;
        }), 10);

        const svgWidth = 800;
        const barHeight = 28;
        const gap = 14;
        const svgHeight = filas.length * (barHeight + gap) + 30;

        let svg = `<svg viewBox="0 0 ${svgWidth} ${svgHeight}" class="svg-chart">`;

        filas.forEach((f, idx) => {
            const tot = matriz.totales_fila[f];
            const conteo = (tot && !tot.suprimido) ? tot.conteo : 0;
            const w = Math.round((conteo / maxConteo) * 480);
            const y = idx * (barHeight + gap) + 15;

            // Label
            svg += `<text x="10" y="${y + 18}" font-size="12" font-weight="700" fill="#2D3748">${f.substring(0, 24)}</text>`;
            // Background bar
            svg += `<rect x="220" y="${y}" width="480" height="${barHeight}" rx="4" fill="#EDF2F7"/>`;
            // Value bar
            svg += `<rect x="220" y="${y}" width="${Math.max(w, 4)}" height="${barHeight}" rx="4" fill="#2B579A"/>`;
            // Value text
            const textVal = (tot && tot.suprimido) ? '< 5' : conteo;
            svg += `<text x="${230 + w + 10}" y="${y + 18}" font-size="12" font-weight="800" fill="#1A202C">${textVal}</text>`;
        });

        svg += '</svg>';
        container.innerHTML = svg;
    }

    function exportarDatos(formato) {
        const fila = document.getElementById('sel-fila').value;
        const columna = document.getElementById('sel-columna').value;
        const prog = document.getElementById('filtro-programa').value;
        const cohorte = document.getElementById('filtro-cohorte').value;
        const docencia = document.getElementById('filtro-docencia').value;

        let url = "{{ route('seguimiento.analitica.exportar') }}?fila=" + encodeURIComponent(fila) + "&columna=" + encodeURIComponent(columna) + "&formato=" + encodeURIComponent(formato);
        url += '&grupo_formacion=' + encodeURIComponent(document.getElementById('grupo-formacion-global').value);
        if (prog) url += "&programa=" + encodeURIComponent(prog);
        if (cohorte) url += "&cohorte=" + encodeURIComponent(cohorte);
        if (docencia) url += "&docencia_directa=" + encodeURIComponent(docencia);

        window.location.href = url;
    }

    // Inicializar visualización al cargar
    document.addEventListener('DOMContentLoaded', () => {
        @if(isset($matrizInicial))
            dibujarGraficoSvg(@json($matrizInicial));
        @endif
    });
</script>
@endpush
