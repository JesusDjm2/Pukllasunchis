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
@php
    $fuenteActiva = $fuenteDatos ?? request('fuente_datos', 'seguimiento_vigente');
    $catalogoFuentes = $fuentesDatos ?? \App\Services\SeguimientoEgresado\SeguimientoAnaliticaService::FUENTES_DATOS;
@endphp
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
                        Analítica longitudinal de trayectorias formativas, calidad del empleo, inserción docente y distribución territorial de egresados de la EESP Pukllasunchis, mostrando los conteos exactos de personas únicas (incluidos los menores a 5).
                    </p>
                </div>
                <div class="hero-controls">
                    <button type="button" class="btn btn-ocre" onclick="exportarDatos('csv')">
                        <span>📥</span> Exportar Cruce (CSV)
                    </button>
                    <button type="button" class="btn btn-light" onclick="exportarInformeCohortes()">
                        <span>📊</span> Exportar informe por cohorte (.xlsx)
                    </button>
                    <a href="{{ route('seguimiento.index') }}" class="btn btn-light">
                        <span>📋</span> Ver Padrón Operativo
                    </a>
                </div>
            </div>

            <!-- Panel de Fuente de Datos, Vínculos Conciliados, Personas Únicas y Denominadores -->
            <div class="analytics-card" id="resumen-fuente-conciliacion" style="margin-bottom:24px;border-left:5px solid var(--puklla-granate);">
                <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:16px;margin-bottom:14px;">
                    <div>
                        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.6px;color:var(--puklla-granate);">
                            Fuente de Datos y Trazabilidad de Conciliación
                        </div>
                        <h3 style="margin:4px 0 0;font-size:18px;font-weight:850;color:var(--ink);" id="u-fuente-titulo">
                            {{ $universos['fuente_etiqueta'] ?? ($catalogoFuentes[$fuenteActiva] ?? 'Seguimiento vigente') }}
                        </h3>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <label for="filtro-fuente-datos" style="font-size:12px;font-weight:800;color:var(--ink);">Fuente de datos:</label>
                        <select id="filtro-fuente-datos" name="fuente_datos" class="filter-select" style="font-weight:700;min-width:220px;" onchange="cambiarFuenteDatos(this.value)">
                            @foreach($catalogoFuentes as $codFuente => $nomFuente)
                                <option value="{{ $codFuente }}" @selected($fuenteActiva === $codFuente)>{{ $nomFuente }}</option>
                            @endforeach
                        </select>
                        <span class="badge badge-amber" id="u-fecha-corte">
                            Corte: {{ $universos['fecha_corte'] ?? now()->toDateString() }}
                        </span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:14px;">
                    <div style="background:#F8FAFC;padding:12px 16px;border-radius:8px;border:1px solid #E2E8F0;">
                        <div style="font-size:11px;font-weight:750;text-transform:uppercase;color:#718096;">Registros Vinculados</div>
                        <div style="font-size:24px;font-weight:900;color:var(--puklla-granate);" id="u-registros-vinculados">
                            {{ $universos['registros_vinculados'] ?? 0 }}
                        </div>
                        <div style="font-size:12px;color:#4A5568;" id="u-registros-sub">
                            {{ ($universos['fuente_datos'] ?? $fuenteActiva) === 'encuesta_depurada_2026' ? 'Vínculos confirmados en Conciliación histórica 2026' : 'Fichas de actualización verificadas' }}
                        </div>
                    </div>
                    <div style="background:#F8FAFC;padding:12px 16px;border-radius:8px;border:1px solid #E2E8F0;">
                        <div style="font-size:11px;font-weight:750;text-transform:uppercase;color:#718096;">Personas Únicas (users.id)</div>
                        <div style="font-size:24px;font-weight:900;color:var(--puklla-azul);" id="u-personas-unicas">
                            {{ $universos['personas_unicas'] ?? ($universos['total_padron'] ?? 0) }}
                        </div>
                        <div style="font-size:12px;color:#4A5568;">
                            Denominador general de personas únicas
                        </div>
                    </div>
                    <div style="background:#F8FAFC;padding:12px 16px;border-radius:8px;border:1px solid #E2E8F0;">
                        <div style="font-size:11px;font-weight:750;text-transform:uppercase;color:#718096;">Con Situación Laboral Declarada</div>
                        <div style="font-size:24px;font-weight:900;color:var(--puklla-verde);" id="u-con-info-laboral">
                            {{ $universos['con_info_laboral'] ?? 0 }}
                        </div>
                        <div style="font-size:12px;color:#4A5568;">
                            Denominador de Docencia Directa y Retención en Educación
                        </div>
                    </div>
                    <div style="background:#F8FAFC;padding:12px 16px;border-radius:8px;border:1px solid #E2E8F0;">
                        <div style="font-size:11px;font-weight:750;text-transform:uppercase;color:#718096;">Casos con Fechas para 1.ᵉʳ Empleo</div>
                        <div style="font-size:24px;font-weight:900;color:#2D3748;" id="u-casos-tiempo-empleo">
                            {{ $universos['casos_tiempo_primer_empleo'] ?? 0 }}
                        </div>
                        <div style="font-size:12px;color:#4A5568;">
                            Denominador de Tiempo al 1.ᵉʳ Empleo
                        </div>
                    </div>
                </div>

                <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:12px 16px;font-size:12.5px;color:#78350F;">
                    <div id="u-explicacion-diferencia" style="margin-bottom:6px;font-weight:600;">
                        {{ $universos['explicacion_diferencia'] ?? '' }}
                    </div>
                    <div id="u-denominadores-resumen" style="color:#92400E;">
                        <strong>Denominadores por indicador:</strong>
                        Respondientes y Tasa de Titulación: <span id="den-personas">{{ $universos['personas_unicas'] ?? ($universos['total_padron'] ?? 0) }}</span> personas únicas |
                        En Docencia Directa y Retención en Educación: <span id="den-laboral">{{ $universos['con_info_laboral'] ?? 0 }}</span> personas únicas con situación laboral declarada |
                        Tiempo al 1.ᵉʳ Empleo: <span id="den-tiempo">{{ $universos['casos_tiempo_primer_empleo'] ?? 0 }}</span> casos con fechas válidas de egreso e inicio laboral.
                    </div>
                </div>
            </div>

            <!-- Module 1: Executive Universe Panels -->
            <div class="universos-grid" id="universos-container">
                <div class="universe-card c-padron">
                    <div class="u-title">Padrón / Personas Únicas</div>
                    <div class="u-count" id="u-padron">{{ $universos['total_padron'] ?? 0 }}</div>
                    <div class="u-subtitle">
                        <span>Personas únicas (users.id)</span>
                        <span class="badge badge-blue">100% Universo</span>
                    </div>
                </div>
                <div class="universe-card c-elegibles">
                    <div class="u-title">Elegibles Seguimiento</div>
                    <div class="u-count" id="u-elegibles">{{ $universos['elegibles_seguimiento'] ?? 0 }}</div>
                    <div class="u-subtitle">
                        <span>Cuentas habilitadas</span>
                        <span class="badge badge-blue">Elegibles</span>
                    </div>
                </div>
                <div class="universe-card c-contactados">
                    <div class="u-title">Contactados / Vínculos</div>
                    <div class="u-count" id="u-contactados">{{ ($universos['fuente_datos'] ?? $fuenteActiva) === 'encuesta_depurada_2026' ? ($universos['registros_vinculados'] ?? 0) : ($universos['contactados'] ?? 0) }}</div>
                    <div class="u-subtitle">
                        <span id="u-contactados-label">{{ ($universos['fuente_datos'] ?? $fuenteActiva) === 'encuesta_depurada_2026' ? 'Registros vinculados' : 'Contacto efectivo' }}</span>
                        <span class="badge badge-amber" id="u-cobertura-badge">{{ $universos['cobertura_padron_pct'] ?? 0 }}% Cobertura</span>
                    </div>
                </div>
                <div class="universe-card c-respondientes">
                    <div class="u-title">Respondientes Válidos</div>
                    <div class="u-count" id="u-respondientes">{{ $universos['respondientes'] ?? 0 }}</div>
                    <div class="u-subtitle">
                        <span>Con respuesta registrada</span>
                        <span class="badge badge-green" id="u-respuesta-badge">{{ isset($universos['tasa_respuesta_pct']) && $universos['tasa_respuesta_pct'] !== null ? $universos['tasa_respuesta_pct'].' % de respuesta' : 'Sin dato' }}</span>
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
                    Los <strong id="u-no-respondientes">{{ $universos['no_respondientes'] ?? 0 }} egresados sin actualización en el corte</strong> corresponden al universo de <em>no respondientes</em>
                    (y <strong id="u-sin-dato-laboral">{{ $universos['sin_dato_laboral'] ?? 0 }}</strong> no registran situación laboral confirmada).
                    Bajo ningún supuesto analítico se les imputa la condición de desempleados o inactivos.
                    Todas las tablas, matrices y gráficos muestran los conteos exactos de personas únicas, incluyendo los valores menores a 5 tal cual.
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
                            Tabulación cruzada sobre <strong>personas únicas</strong> (evitando duplicidad por múltiples contratos o respuestas).
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
                            <option value="Inicial" @selected(($filtros['programa'] ?? '') === 'Inicial')>Educación Inicial</option>
                            <option value="Primaria EIB" @selected(($filtros['programa'] ?? '') === 'Primaria EIB')>Educación Primaria EIB</option>
                            <option value="PPD" @selected(($filtros['programa'] ?? '') === 'PPD')>PPD / Complementación</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="filtro-cohorte">Filtro Cohorte</label>
                        <select id="filtro-cohorte" class="filter-select" onchange="actualizarAnalitica()">
                            <option value="">Todas las cohortes</option>
                            <option value="2025" @selected(($filtros['cohorte'] ?? '') === '2025')>Cohorte 2025</option>
                            <option value="2024" @selected(($filtros['cohorte'] ?? '') === '2024')>Cohorte 2024</option>
                            <option value="2023" @selected(($filtros['cohorte'] ?? '') === '2023')>Cohorte 2023</option>
                            <option value="2022" @selected(($filtros['cohorte'] ?? '') === '2022')>Cohorte 2022</option>
                            <option value="2021" @selected(($filtros['cohorte'] ?? '') === '2021')>Cohorte 2021</option>
                            <option value="Por verificar" @selected(($filtros['cohorte'] ?? '') === 'Por verificar')>Por verificar</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="filtro-docencia">Docencia Directa</label>
                        <select id="filtro-docencia" class="filter-select" onchange="actualizarAnalitica()">
                            <option value="">Todos</option>
                            <option value="si" @selected(($filtros['docencia_directa'] ?? '') === 'si')>Solo en aula / docencia directa</option>
                            <option value="no" @selected(($filtros['docencia_directa'] ?? '') === 'no')>Otras áreas / no aula</option>
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
                <div class="section-header" style="flex-wrap:wrap;gap:12px;">
                    <div>
                        <h2 class="section-title">
                            <span>📈</span>
                            <span>Inteligencia Longitudinal y Calidad de Inserción</span>
                        </h2>
                        <p class="section-desc">
                            Evolución de inserción docente, retención en el sector educativo y tiempo hasta el primer empleo por cohorte de egreso, con denominadores explícitos y sin valores de respaldo.
                        </p>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <button type="button" class="btn btn-sm btn-ocre" onclick="exportarInformeCohortes()">
                            <span>📊</span> Exportar informe por cohorte (.xlsx)
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="matrix-table" id="tabla-longitudinal-cohortes">
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
                            @forelse($longitudinalInicial['cohortes'] ?? [] as $data)
                                <tr>
                                    <td><strong>{{ $data['cohorte'] }}</strong></td>
                                    <td>{{ $data['padron_texto'] }}</td>
                                    <td>{{ $data['respondientes_texto'] }}</td>
                                    <td>
                                        @if($data['tasa_docencia_directa'] === null)
                                            <span class="badge badge-amber">{{ $data['docencia_directa_texto'] }}</span>
                                        @else
                                            <span class="badge badge-green">{{ $data['docencia_directa_texto'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $data['tiempo_primer_empleo_texto'] }}</td>
                                    <td>
                                        @if($data['retencion_educativa_pct'] === null)
                                            <span class="badge badge-amber">{{ $data['retencion_educativa_texto'] }}</span>
                                        @else
                                            <span class="badge badge-blue">{{ $data['retencion_educativa_texto'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-amber">{{ $data['tasa_titulacion_texto'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center;color:#718096;font-style:italic;">
                                        Sin registros para los filtros seleccionados en esta fuente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot id="longitudinal-tfoot">
                            @if(isset($longitudinalInicial['totales']))
                                @php $tot = $longitudinalInicial['totales']; @endphp
                                <tr style="border-top:2px solid #CBD5E0;background:#EDF2F7;font-weight:800;">
                                    <td><strong>{{ $tot['cohorte'] }}</strong></td>
                                    <td>{{ $tot['padron_texto'] }}</td>
                                    <td>{{ $tot['respondientes_texto'] }}</td>
                                    <td>{{ $tot['docencia_directa_texto'] }}</td>
                                    <td>{{ $tot['tiempo_primer_empleo_texto'] }}</td>
                                    <td>{{ $tot['retencion_educativa_texto'] }}</td>
                                    <td>{{ $tot['tasa_titulacion_texto'] }}</td>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>

                <!-- Program Comparative Callout -->
                <div class="two-cols" style="margin-top:20px;margin-bottom:0;">
                    <div style="background:#F7FAFC;border-radius:8px;padding:16px;border:1px solid #E2E8F0;" id="comparativa-programas-box">
                        <h4 style="margin:0 0 8px;font-size:14px;color:var(--puklla-granate);">Educación Inicial vs Educación Primaria EIB</h4>
                        <p style="font-size:12px;color:#4A5568;margin-bottom:12px;">
                            Comparativa de inserción en docencia directa sobre personas con información laboral válida.
                        </p>
                        <div style="display:flex;gap:16px;">
                            <div style="flex:1;">
                                <div style="font-size:11px;color:#718096;font-weight:700;">INICIAL</div>
                                <div style="font-size:18px;font-weight:800;color:#2D3748;" id="comp-inicial-docencia">
                                    {{ $longitudinalInicial['comparativa_programas']['Inicial']['en_docencia_texto'] ?? 'Sin dato (0/0)' }}
                                </div>
                                <div style="font-size:11px;color:#718096;">en docencia directa en aula</div>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:11px;color:#718096;font-weight:700;">PRIMARIA EIB</div>
                                <div style="font-size:18px;font-weight:800;color:var(--puklla-verde);" id="comp-eib-docencia">
                                    {{ $longitudinalInicial['comparativa_programas']['Primaria EIB']['en_docencia_texto'] ?? 'Sin dato (0/0)' }}
                                </div>
                                <div style="font-size:11px;color:#718096;">en docencia directa en aula</div>
                            </div>
                        </div>
                    </div>

                    <div style="background:#F7FAFC;border-radius:8px;padding:16px;border:1px solid #E2E8F0;" id="progreso-titulacion-box">
                        <h4 style="margin:0 0 8px;font-size:14px;color:var(--puklla-azul);">Progreso del Proceso de Titulación</h4>
                        <p style="font-size:12px;color:#4A5568;margin-bottom:12px;">
                            Transición desde Bachillerato / PTI hacia Título Profesional Pedagógico en el universo filtrado.
                        </p>
                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <span class="badge badge-green" id="tit-badge-lic">Licenciados/Titulados: {{ $longitudinalInicial['titulacion']['licenciado'] ?? 0 }}</span>
                            <span class="badge badge-blue" id="tit-badge-tesis">Tesis/PTI Aprobado: {{ $longitudinalInicial['titulacion']['tesis_aprobada'] ?? 0 }}</span>
                            <span class="badge badge-amber" id="tit-badge-proc">En Proceso: {{ $longitudinalInicial['titulacion']['pti_en_proceso'] ?? 0 }}</span>
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
                                            <span class="matrix-cell-val">{{ $provTot }}</span>
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());

    function scrollToSection(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    }

    function toggleLoading(show) {
        document.getElementById('loading-overlay').style.display = show ? 'grid' : 'none';
    }

    function obtenerFiltrosActivos() {
        const fuente = document.getElementById('filtro-fuente-datos')?.value || 'seguimiento_vigente';
        const grupo = document.getElementById('grupo-formacion-global')?.value || 'TODOS';
        const prog = document.getElementById('filtro-programa')?.value || '';
        const cohorte = document.getElementById('filtro-cohorte')?.value || '';
        const docencia = document.getElementById('filtro-docencia')?.value || '';

        const filtros = {
            fuente_datos: fuente,
            grupo_formacion: grupo
        };
        if (prog) filtros.programa = prog;
        if (cohorte) filtros.cohorte = cohorte;
        if (docencia) filtros.docencia_directa = docencia;
        return filtros;
    }

    function sincronizarFormularioGrupo(fuente) {
        const form = document.querySelector('form.puklla-grupo-formacion');
        if (!form) return;
        let inputFuente = form.querySelector('input[name="fuente_datos"]');
        if (!inputFuente) {
            inputFuente = document.createElement('input');
            inputFuente.type = 'hidden';
            inputFuente.name = 'fuente_datos';
            form.appendChild(inputFuente);
        }
        inputFuente.value = fuente;
    }

    function cambiarFuenteDatos(nuevaFuente) {
        sincronizarFormularioGrupo(nuevaFuente);
        const url = new URL(window.location.href);
        url.searchParams.set('fuente_datos', nuevaFuente);
        window.history.replaceState({}, '', url.toString());
        actualizarAnalitica();
    }

    function actualizarAnalitica() {
        const fila = document.getElementById('sel-fila').value;
        const columna = document.getElementById('sel-columna').value;
        const filtros = obtenerFiltrosActivos();
        sincronizarFormularioGrupo(filtros.fuente_datos);

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
                if (data.universos) {
                    renderizarUniversosYFuente(data.universos);
                }
                if (data.matriz) {
                    renderizarMatriz(data.matriz);
                }
                if (data.longitudinal) {
                    renderizarLongitudinal(data.longitudinal);
                }
            }
        })
        .catch(err => {
            toggleLoading(false);
            console.error('Error al actualizar analítica:', err);
        });
    }

    function renderizarUniversosYFuente(u) {
        document.getElementById('u-padron').innerText = u.total_padron ?? 0;
        document.getElementById('u-elegibles').innerText = u.elegibles_seguimiento ?? 0;
        const esEncuesta = u.fuente_datos === 'encuesta_depurada_2026';
        document.getElementById('u-contactados').innerText = esEncuesta ? (u.registros_vinculados ?? 0) : (u.contactados ?? 0);
        const lblContactados = document.getElementById('u-contactados-label');
        if (lblContactados) lblContactados.innerText = esEncuesta ? 'Registros vinculados' : 'Contacto efectivo';
        const bCobertura = document.getElementById('u-cobertura-badge');
        if (bCobertura) bCobertura.innerText = (u.cobertura_padron_pct ?? 0) + '% Cobertura';

        document.getElementById('u-respondientes').innerText = u.respondientes ?? 0;
        const bRespuesta = document.getElementById('u-respuesta-badge');
        if (bRespuesta) {
            bRespuesta.innerText = (u.tasa_respuesta_pct !== null && u.tasa_respuesta_pct !== undefined)
                ? (u.tasa_respuesta_pct + ' % de respuesta')
                : 'Sin dato';
        }
        document.getElementById('u-longitudinales').innerText = u.longitudinales_multiperiodo ?? 0;

        // Panel de fuente y denominadores
        const elTitulo = document.getElementById('u-fuente-titulo');
        if (elTitulo) elTitulo.innerText = u.fuente_etiqueta || '';
        const elCorte = document.getElementById('u-fecha-corte');
        if (elCorte) elCorte.innerText = 'Corte: ' + (u.fecha_corte || '');
        const elReg = document.getElementById('u-registros-vinculados');
        if (elReg) elReg.innerText = u.registros_vinculados ?? 0;
        const elRegSub = document.getElementById('u-registros-sub');
        if (elRegSub) elRegSub.innerText = esEncuesta ? 'Vínculos confirmados en Conciliación histórica 2026' : 'Fichas de actualización verificadas';
        const elPers = document.getElementById('u-personas-unicas');
        if (elPers) elPers.innerText = u.personas_unicas ?? (u.total_padron ?? 0);
        const elLab = document.getElementById('u-con-info-laboral');
        if (elLab) elLab.innerText = u.con_info_laboral ?? 0;
        const elTiempo = document.getElementById('u-casos-tiempo-empleo');
        if (elTiempo) elTiempo.innerText = u.casos_tiempo_primer_empleo ?? 0;

        const elExp = document.getElementById('u-explicacion-diferencia');
        if (elExp) elExp.innerText = u.explicacion_diferencia || '';
        const denPers = document.getElementById('den-personas');
        if (denPers) denPers.innerText = u.personas_unicas ?? (u.total_padron ?? 0);
        const denLab = document.getElementById('den-laboral');
        if (denLab) denLab.innerText = u.con_info_laboral ?? 0;
        const denTiempo = document.getElementById('den-tiempo');
        if (denTiempo) denTiempo.innerText = u.casos_tiempo_primer_empleo ?? 0;

        const elNoResp = document.getElementById('u-no-respondientes');
        if (elNoResp) elNoResp.innerText = (u.no_respondientes ?? 0) + ' egresados sin actualización en el corte';
        const elSinDatoLab = document.getElementById('u-sin-dato-laboral');
        if (elSinDatoLab) elSinDatoLab.innerText = u.sin_dato_laboral ?? 0;
    }

    function renderizarLongitudinal(longitudinal) {
        const tbody = document.getElementById('longitudinal-tbody');
        const tfoot = document.getElementById('longitudinal-tfoot');
        if (!tbody) return;

        const cohortes = longitudinal.cohortes || [];
        if (cohortes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#718096;font-style:italic;">Sin registros para los filtros seleccionados en esta fuente.</td></tr>';
        } else {
            let html = '';
            cohortes.forEach(c => {
                const docBadgeClass = c.tasa_docencia_directa === null ? 'badge-amber' : 'badge-green';
                const retBadgeClass = c.retencion_educativa_pct === null ? 'badge-amber' : 'badge-blue';
                html += '<tr>';
                html += '<td><strong>' + c.cohorte + '</strong></td>';
                html += '<td>' + c.padron_texto + '</td>';
                html += '<td>' + c.respondientes_texto + '</td>';
                html += '<td><span class="badge ' + docBadgeClass + '">' + c.docencia_directa_texto + '</span></td>';
                html += '<td>' + c.tiempo_primer_empleo_texto + '</td>';
                html += '<td><span class="badge ' + retBadgeClass + '">' + c.retencion_educativa_texto + '</span></td>';
                html += '<td><span class="badge badge-amber">' + c.tasa_titulacion_texto + '</span></td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
        }

        if (tfoot && longitudinal.totales) {
            const t = longitudinal.totales;
            tfoot.innerHTML = '<tr style="border-top:2px solid #CBD5E0;background:#EDF2F7;font-weight:800;">'
                + '<td><strong>' + t.cohorte + '</strong></td>'
                + '<td>' + t.padron_texto + '</td>'
                + '<td>' + t.respondientes_texto + '</td>'
                + '<td>' + t.docencia_directa_texto + '</td>'
                + '<td>' + t.tiempo_primer_empleo_texto + '</td>'
                + '<td>' + t.retencion_educativa_texto + '</td>'
                + '<td>' + t.tasa_titulacion_texto + '</td>'
                + '</tr>';
        }

        if (longitudinal.comparativa_programas) {
            const elIni = document.getElementById('comp-inicial-docencia');
            const elEib = document.getElementById('comp-eib-docencia');
            if (elIni && longitudinal.comparativa_programas['Inicial']) {
                elIni.innerText = longitudinal.comparativa_programas['Inicial'].en_docencia_texto || 'Sin dato (0/0)';
            }
            if (elEib && longitudinal.comparativa_programas['Primaria EIB']) {
                elEib.innerText = longitudinal.comparativa_programas['Primaria EIB'].en_docencia_texto || 'Sin dato (0/0)';
            }
        }

        if (longitudinal.titulacion) {
            const bLic = document.getElementById('tit-badge-lic');
            const bTes = document.getElementById('tit-badge-tesis');
            const bPti = document.getElementById('tit-badge-proc');
            if (bLic) bLic.innerText = 'Licenciados/Titulados: ' + (longitudinal.titulacion.licenciado ?? 0);
            if (bTes) bTes.innerText = 'Tesis/PTI Aprobado: ' + (longitudinal.titulacion.tesis_aprobada ?? 0);
            if (bPti) bPti.innerText = 'En Proceso: ' + (longitudinal.titulacion.pti_en_proceso ?? 0);
        }
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
                } else {
                    const val = celda.conteo ?? celda.valor ?? 0;
                    html += '<td><span class="matrix-cell-val">' + val + '</span>';
                    if (celda.porcentaje_fila !== null && celda.porcentaje_fila !== undefined) {
                        html += ' <span class="matrix-cell-pct">(' + celda.porcentaje_fila + '%)</span>';
                    }
                    html += '</td>';
                }
            });

            // Total fila
            const totFila = matriz.totales_fila[fila];
            html += '<td class="row-total">' + (totFila ? (totFila.conteo ?? totFila.valor ?? 0) : 0) + '</td>';
            html += '</tr>';
        });

        // Fila de totales por columna
        html += '<tr style="border-top:2px solid #CBD5E0;background:#EDF2F7;">';
        html += '<td><strong>Total Columna</strong></td>';
        matriz.valores_columna.forEach(col => {
            const totCol = matriz.totales_columna[col];
            html += '<td class="col-total">' + (totCol ? (totCol.conteo ?? totCol.valor ?? 0) : 0) + '</td>';
        });

        // Gran Total
        html += '<td class="col-total" style="font-size:15px;color:var(--puklla-granate);">' + (matriz.total_general?.valor ?? matriz.total_general ?? 0) + '</td>';
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
        if (!container || !matriz || !matriz.valores_fila || matriz.valores_fila.length === 0) {
            if (container) container.innerHTML = '<div style="padding:16px;color:#718096;font-style:italic;">Sin datos para graficar en este filtro.</div>';
            return;
        }

        const filas = matriz.valores_fila;
        const maxConteo = Math.max(...filas.map(f => {
            const t = matriz.totales_fila[f];
            return t ? Number(t.conteo ?? t.valor ?? 0) : 0;
        }), 10);

        const svgWidth = 800;
        const barHeight = 28;
        const gap = 14;
        const svgHeight = filas.length * (barHeight + gap) + 30;

        let svg = `<svg viewBox="0 0 ${svgWidth} ${svgHeight}" class="svg-chart">`;

        filas.forEach((f, idx) => {
            const tot = matriz.totales_fila[f];
            const conteo = tot ? Number(tot.conteo ?? tot.valor ?? 0) : 0;
            const w = Math.round((conteo / maxConteo) * 480);
            const y = idx * (barHeight + gap) + 15;

            // Label
            svg += `<text x="10" y="${y + 18}" font-size="12" font-weight="700" fill="#2D3748">${f.substring(0, 24)}</text>`;
            // Background bar
            svg += `<rect x="220" y="${y}" width="480" height="${barHeight}" rx="4" fill="#EDF2F7"/>`;
            // Value bar
            svg += `<rect x="220" y="${y}" width="${Math.max(w, 4)}" height="${barHeight}" rx="4" fill="#2B579A"/>`;
            // Value text
            svg += `<text x="${230 + w + 10}" y="${y + 18}" font-size="12" font-weight="800" fill="#1A202C">${conteo}</text>`;
        });

        svg += '</svg>';
        container.innerHTML = svg;
    }

    function exportarDatos(formato) {
        const fila = document.getElementById('sel-fila').value;
        const columna = document.getElementById('sel-columna').value;
        const filtros = obtenerFiltrosActivos();

        let url = "{{ route('seguimiento.analitica.exportar') }}?fila=" + encodeURIComponent(fila) + "&columna=" + encodeURIComponent(columna) + "&formato=" + encodeURIComponent(formato);
        url += '&fuente_datos=' + encodeURIComponent(filtros.fuente_datos);
        url += '&grupo_formacion=' + encodeURIComponent(filtros.grupo_formacion);
        if (filtros.programa) url += "&programa=" + encodeURIComponent(filtros.programa);
        if (filtros.cohorte) url += "&cohorte=" + encodeURIComponent(filtros.cohorte);
        if (filtros.docencia_directa) url += "&docencia_directa=" + encodeURIComponent(filtros.docencia_directa);

        window.location.href = url;
    }

    function exportarInformeCohortes() {
        const filtros = obtenerFiltrosActivos();
        let url = "{{ route('seguimiento.analitica.exportar-cohortes') }}?formato=xlsx";
        url += '&fuente_datos=' + encodeURIComponent(filtros.fuente_datos);
        url += '&grupo_formacion=' + encodeURIComponent(filtros.grupo_formacion);
        if (filtros.programa) url += "&programa=" + encodeURIComponent(filtros.programa);
        if (filtros.cohorte) url += "&cohorte=" + encodeURIComponent(filtros.cohorte);
        if (filtros.docencia_directa) url += "&docencia_directa=" + encodeURIComponent(filtros.docencia_directa);

        window.location.href = url;
    }

    // Inicializar visualización al cargar
    document.addEventListener('DOMContentLoaded', () => {
        sincronizarFormularioGrupo(document.getElementById('filtro-fuente-datos')?.value || 'seguimiento_vigente');
        @if(isset($matrizInicial))
            dibujarGraficoSvg(@json($matrizInicial));
        @endif
    });
</script>
@endpush
