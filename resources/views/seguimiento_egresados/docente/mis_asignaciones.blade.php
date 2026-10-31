<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Puklla en Red · Mis Egresados Asignados (Panel Docente Entrevistador)</title>
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

        /* KPI Grid 9 boxes */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #FFF;
            border: 1px solid var(--border-line);
            border-radius: 10px;
            padding: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .kpi-value {
            font-size: 24px;
            font-weight: 800;
            color: var(--puklla-granate);
            margin: 4px 0;
        }
        .kpi-label {
            font-size: 11px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--ink-muted);
        }
        .kpi-desc { font-size: 11px; color: #718096; line-height: 1.3; }

        /* Table */
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
            padding: 12px 14px;
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
        .badge-purple { background: #F3E8FF; color: #6B21A8; }
        .badge-gray { background: #F3F4F6; color: #374151; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            border: 0;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-granate { background: var(--puklla-granate); color: #FFF; }
        .btn-granate:hover { background: var(--puklla-granate-dark); color: #FFF; text-decoration: none; }
        .btn-verde { background: var(--puklla-verde); color: #FFF; }
        .btn-verde:hover { background: #135A35; color: #FFF; text-decoration: none; }
        .btn-outline { background: transparent; border: 1px solid var(--border-line); color: var(--ink); }
        .btn-outline:hover { background: #F8FAFC; text-decoration: none; }

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
            max-width: 650px;
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
                <div class="brand-subtitle">Panel del Docente</div>
            </div>
        </div>

        <div class="nav-label">Mi Acompañamiento</div>
        <a href="{{ route('seguimiento-egresados.mis-asignaciones') }}" class="nav-item active">
            <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
            <span>Mis Egresados Asignados</span>
        </a>
        <a href="{{ route('seguimiento.index') }}" class="nav-item">
            <span class="nav-icon"><i class="fas fa-users"></i></span>
            <span>Padrón de Seguimiento</span>
        </a>

        <div class="nav-label">Mi Docencia Institucional</div>
        @if (auth()->user()?->docente)
            <a href="{{ route('vistaDocente', ['docente' => auth()->user()->docente->id]) }}" class="nav-item">
                <span class="nav-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                <span>Mi Dashboard Docente</span>
            </a>
        @endif
        <a href="{{ route('auditoria.logout') }}" class="nav-item" style="color: #F87171;">
            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
            <span>Cerrar Sesión</span>
        </a>
    </aside>

    <!-- Main Workspace -->
    <div class="main-content">
        <header class="top-navbar">
            <div class="top-badge">
                <i class="fas fa-user-tie mr-1"></i>
                <span>Docente Entrevistador · Puklla en Red</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size:13px;font-weight:700;color:#4A5568;">
                    {{ auth()->user()->name }} {{ auth()->user()->apellidos }}
                </span>
                <span class="badge badge-green">Acceso Restringido a Mis Asignados</span>
            </div>
        </header>

        <div class="container">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <div>
                    <h1 class="hero-title">Mis Egresados Asignados</h1>
                    <p class="hero-desc">
                        Lista de trabajo y acompañamiento individual. Recuerda que hacer clic en el teléfono utiliza la marcación de tu dispositivo y no registra automáticamente la llamada.
                    </p>
                </div>
                <div>
                    <span class="badge badge-amber" style="font-size: 13px; padding: 6px 12px;">
                        <i class="fas fa-user-check mr-1"></i> {{ $metricasDocente['total_asignados'] }} Egresados Asignados
                    </span>
                </div>
            </div>

            <!-- 9 Indicadores Personales con Explicación Explícita -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-tachometer-alt text-danger"></i>
                        <span>Mis Indicadores de Desempeño y Cobertura (Explicación del Cómputo)</span>
                    </h2>
                    <span class="badge badge-blue">Docente ID: {{ auth()->id() }}</span>
                </div>
                <div class="card-body">
                    <div class="kpi-grid">
                        <div class="kpi-card">
                            <div class="kpi-label">1. Egresados Asignados</div>
                            <div class="kpi-value">{{ $metricasDocente['total_asignados'] }}</div>
                            <div class="kpi-desc">Total de egresados bajo mi responsabilidad en la campaña.</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">2. Pendientes de Contactar</div>
                            <div class="kpi-value" style="color: var(--puklla-ocre);">{{ $metricasDocente['pendientes_contactar'] }}</div>
                            <div class="kpi-desc">Egresados que aún no han sido contactados efectivamente.</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">3. Intentos de Llamada</div>
                            <div class="kpi-value" style="color: #6B7280;">{{ $metricasDocente['intentos_llamada'] }}</div>
                            <div class="kpi-desc">Número bruto de marcaciones realizadas (incluye no contesta).</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">4. Personas Contactadas</div>
                            <div class="kpi-value" style="color: var(--puklla-verde);">{{ $metricasDocente['contactados_efectivos'] }}</div>
                            <div class="kpi-desc">Personas únicas con contacto efectivo (no llamadas repetidas).</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">5. Actualizaciones Completas</div>
                            <div class="kpi-value" style="color: #047857;">{{ $metricasDocente['actualizaciones_completas'] }}</div>
                            <div class="kpi-desc">Fichas completadas con nuevo empleo o datos académicos.</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">6. Actualizaciones Parciales</div>
                            <div class="kpi-value" style="color: #D97706;">{{ $metricasDocente['actualizaciones_parciales'] }}</div>
                            <div class="kpi-desc">Respuestas parciales con entrevista pendiente de concluir.</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">7. Confirmaciones Sin Cambios</div>
                            <div class="kpi-value" style="color: #2563EB;">{{ $metricasDocente['confirmaciones_sin_cambios'] }}</div>
                            <div class="kpi-desc">Egresados que confirmaron vigencia de su registro anterior.</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">8. Entrevistas Reprogramadas</div>
                            <div class="kpi-value" style="color: #6366F1;">{{ $metricasDocente['entrevistas_reprogramadas'] }}</div>
                            <div class="kpi-desc">Llamadas acordadas para una fecha y hora posterior.</div>
                        </div>

                        <div class="kpi-card">
                            <div class="kpi-label">9. Casos Pendientes Atención</div>
                            <div class="kpi-value" style="color: #7C3AED;">{{ $metricasDocente['casos_atencion'] }}</div>
                            <div class="kpi-desc">Derivaciones a Bienestar o solicitudes de acompañamiento.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Trabajo: Mis Egresados -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">
                            <i class="fas fa-clipboard-check text-primary"></i>
                            <span>Mi Lista de Trabajo y Seguimiento ({{ $metricasDocente['lista_trabajo']->count() }} Egresados)</span>
                        </h2>
                        <p style="margin: 4px 0 0; font-size: 12px; color: var(--ink-muted);">
                            Acceso exclusivo: Solo puedes consultar y registrar entrevistas con tus egresados asignados vigentes.
                        </p>
                    </div>
                </div>

                <div class="card-body">
                    @if ($metricasDocente['lista_trabajo']->isEmpty())
                        <div style="text-align: center; padding: 40px; color: #718096;">
                            <i class="fas fa-inbox fa-3x mb-2"></i>
                            <p style="font-size: 15px;">Actualmente no tienes egresados asignados en ninguna campaña activa.</p>
                            <small>Comunícate con Coordinación de Empleabilidad para asignaciones.</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Egresado/a</th>
                                        <th>Programa</th>
                                        <th>Cohorte</th>
                                        <th>Teléfono Contacto</th>
                                        <th>Estado del Seguimiento</th>
                                        <th>Último Intento</th>
                                        <th>Próxima Acción</th>
                                        <th style="text-align: right;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($metricasDocente['lista_trabajo'] as $item)
                                        <tr>
                                            <td>
                                                <div style="font-weight: 700; color: var(--ink);">{{ $item['nombre_completo'] }}</div>
                                                <div style="font-size: 11px; color: var(--ink-muted);"><code>REAL-{{ $item['user_id'] }}</code> · {{ $item['email'] }}</div>
                                            </td>
                                            <td><span class="badge badge-gray">{{ $item['programa'] }}</span></td>
                                            <td>{{ $item['cohorte'] }}</td>
                                            <td>
                                                @if ($item['tiene_telefono'])
                                                    <a href="tel:{{ $item['telefono'] }}" class="btn btn-outline btn-sm" style="font-weight: 700; color: var(--puklla-verde);">
                                                        <i class="fas fa-phone mr-1"></i> {{ $item['telefono'] }}
                                                    </a>
                                                    <div style="font-size: 10px; color: #9CA3AF; margin-top: 2px;">(Clic no registra llamada)</div>
                                                @else
                                                    <span class="badge badge-amber"><i class="fas fa-exclamation-triangle"></i> Sin teléfono</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $st = $item['estado_seguimiento'];
                                                    $badgeClass = match($st) {
                                                        'Actualización completa' => 'badge-green',
                                                        'Confirmación sin cambios' => 'badge-blue',
                                                        'Contacto efectivo' => 'badge-green',
                                                        'Entrevista programada' => 'badge-amber',
                                                        'Actualización parcial' => 'badge-amber',
                                                        'Número equivocado' => 'badge-red',
                                                        'Requiere derivación' => 'badge-purple',
                                                        default => 'badge-gray',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $st }}</span>
                                            </td>
                                            <td>
                                                @if ($item['ultimo_intento_fecha'])
                                                    <div style="font-size: 12px; font-weight: 700;">{{ $item['ultimo_intento_fecha'] }}</div>
                                                    <div style="font-size: 11px; color: #6B7280;">{{ $item['ultimo_intento_resultado'] }}</div>
                                                @else
                                                    <span style="color: #9CA3AF; font-size: 12px;">Sin intentos</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span style="font-size: 12px; color: #374151;">{{ $item['proxima_accion'] }}</span>
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display: inline-flex; gap: 6px;">
                                                    <button type="button" class="btn btn-outline" onclick="abrirModalLlamada({{ $item['user_id'] }}, '{{ addslashes($item['nombre_completo']) }}', {{ $item['campana_id'] }})">
                                                        <i class="fas fa-phone-alt"></i> Registrar Llamada
                                                    </button>
                                                    <button type="button" class="btn btn-granate" onclick="abrirModalActualizar({{ $item['user_id'] }}, '{{ addslashes($item['nombre_completo']) }}', '{{ $item['telefono'] }}', '{{ $item['res_departamento'] }}', '{{ $item['res_provincia'] }}', '{{ $item['res_distrito'] }}')">
                                                        <i class="fas fa-edit"></i> Actualizar Ficha
                                                    </button>
                                                </div>
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

<!-- Modal Registrar Intento / Llamada Telefónica con los 11 Estados -->
<div id="modalLlamada" class="modal-overlay">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-line); padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--puklla-granate);">
                <i class="fas fa-phone-volume mr-2"></i> Registro de Intento / Entrevista Telefónica
            </h3>
            <button onclick="cerrarModalLlamada()" style="border: 0; background: transparent; font-size: 18px; color: #718096;">&times;</button>
        </div>

        <form id="formRegistroLlamada" onsubmit="guardarLlamadaDocente(event)">
            @csrf
            <input type="hidden" id="llamadaTargetUserId">
            <input type="hidden" id="llamadaCampanaId">

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #4A5568; display: block; margin-bottom: 4px;">Egresado/a:</label>
                <input type="text" id="llamadaNombreEgresado" readonly style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px; background: #F8FAFC; font-weight: 700;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #4A5568; display: block; margin-bottom: 4px;">Resultado de la Comunicación (11 Estados Oficiales):</label>
                <select id="llamadaTipoResultado" required onchange="ajustarCamposLlamada(this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px; font-weight: 600;">
                    <option value="">Selecciona el resultado de la marcación...</option>
                    <optgroup label="Intentos sin contacto">
                        <option value="por_contactar">Por contactar</option>
                        <option value="intento_sin_respuesta">Intento sin respuesta (No contesta / Apagado / Buzón)</option>
                        <option value="numero_equivocado">Número equivocado o inexistente</option>
                    </optgroup>
                    <optgroup label="Contactos Efectivos">
                        <option value="contacto_efectivo">Contacto efectivo (Comunicación lograda)</option>
                        <option value="entrevista_programada">Entrevista programada / Reprogramada</option>
                        <option value="actualizacion_parcial">Actualización parcial</option>
                        <option value="actualizacion_completa">Actualización completa (Ficha llenada)</option>
                        <option value="confirmacion_sin_cambios">Confirmación sin cambios</option>
                        <option value="rechazo_participacion">Rechazo de participación</option>
                    </optgroup>
                    <optgroup label="Gestión Especial y Derivación">
                        <option value="requiere_verificacion">Requiere verificación de datos</option>
                        <option value="requiere_derivacion">Requiere derivación institucional (Bienestar / Tutoría)</option>
                    </optgroup>
                </select>
            </div>

            <div id="campoReprogramacion" style="display: none; margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #4A5568; display: block; margin-bottom: 4px;">Fecha y Hora Programada para la Entrevista:</label>
                <input type="datetime-local" id="llamadaFechaReprogramada" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
            </div>

            <div id="campoDerivacion" style="display: none; margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 700; color: #7C3AED; display: block; margin-bottom: 4px;">Motivo de la Derivación a Bienestar / Coordinación:</label>
                <textarea id="llamadaMotivoDerivacion" rows="2" placeholder="Especifica la situación (ej: requiere apoyo psicológico, consulta sobre bolsa de trabajo, orientación para titulación)..." style="width: 100%; padding: 8px 12px; border: 1px solid #C4B5FD; border-radius: 6px;"></textarea>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #4A5568; display: block; margin-bottom: 4px;">Observaciones y Notas de la Comunicación:</label>
                <textarea id="llamadaObservaciones" rows="3" placeholder="Detalles de la conversación, tono del egresado, disponibilidad horaria o comentarios relevantes..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-line); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="cerrarModalLlamada()">Cancelar</button>
                <button type="submit" class="btn btn-granate">
                    <i class="fas fa-save"></i> Guardar Registro de Llamada
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Actualización de Ficha (Formulario Espejo Institucional) -->
<div id="modalActualizarFicha" class="modal-overlay">
    <div class="modal-box" style="max-width: 750px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-line); padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: var(--puklla-granate);">
                <i class="fas fa-id-card mr-2"></i> Actualización de Ficha de Egresado (Modo Entrevistador)
            </h3>
            <button onclick="cerrarModalActualizar()" style="border: 0; background: transparent; font-size: 18px; color: #718096;">&times;</button>
        </div>

        <form id="formActualizacionFicha" onsubmit="guardarFichaDocente(event)">
            @csrf
            <input type="hidden" id="fichaTargetUserId">
            <input type="hidden" id="fichaCampana" value="CAMP-ENSAYO-2026">

            <div style="background: #F8FAFC; border: 1px solid var(--border-line); border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                <strong>Egresado:</strong> <span id="fichaNombre"></span>
            </div>

            <!-- Opción rápida sin cambios -->
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                <label style="font-weight: 700; color: #1E40AF; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" id="checkSinCambios" onchange="toggleFormularioSinCambios(this.checked)">
                    <span>Confirmación rápida «Sin Cambios» (El egresado ratifica que sus datos laborales y de residencia no han variado)</span>
                </label>
            </div>

            <div id="camposDetalladosFicha">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Teléfono de Contacto:</label>
                        <input type="text" id="fichaTelefono" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; text-transform: uppercase;">¿Trabaja Actualmente?:</label>
                        <select id="fichaTrabaja" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Institución / Empleador Actual:</label>
                        <input type="text" id="fichaInstitucion" placeholder="Ej: I.E. 50021 Cusco" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Cargo o Función:</label>
                        <input type="text" id="fichaCargo" placeholder="Ej: Docente de aula" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Departamento Residencia:</label>
                        <input type="text" id="fichaDep" value="CUSCO" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Provincia Residencia:</label>
                        <input type="text" id="fichaProv" value="CUSCO" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Distrito Residencia:</label>
                        <input type="text" id="fichaDist" value="CUSCO" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Necesidades de Apoyo / Solicitud Formativa:</label>
                    <input type="text" id="fichaNecesidades" placeholder="Ej: Talleres en Quechua EIB, Acompañamiento pedagógico..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-line); border-radius: 6px;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-line); padding-top: 14px;">
                <button type="button" class="btn btn-outline" onclick="cerrarModalActualizar()">Cancelar</button>
                <button type="submit" class="btn btn-granate">
                    <i class="fas fa-save"></i> Guardar Actualización en Expediente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalLlamada(userId, nombre, campanaId) {
        document.getElementById('llamadaTargetUserId').value = userId;
        document.getElementById('llamadaNombreEgresado').value = nombre;
        document.getElementById('llamadaCampanaId').value = campanaId || '';
        document.getElementById('modalLlamada').style.display = 'flex';
    }

    function cerrarModalLlamada() {
        document.getElementById('modalLlamada').style.display = 'none';
    }

    function ajustarCamposLlamada(tipo) {
        document.getElementById('campoReprogramacion').style.display = (tipo === 'entrevista_programada') ? 'block' : 'none';
        document.getElementById('campoDerivacion').style.display = (tipo === 'requiere_derivacion') ? 'block' : 'none';
    }

    function guardarLlamadaDocente(e) {
        e.preventDefault();
        const userId = document.getElementById('llamadaTargetUserId').value;
        const tipoResultado = document.getElementById('llamadaTipoResultado').value;
        const obs = document.getElementById('llamadaObservaciones').value;
        const campanaId = document.getElementById('llamadaCampanaId').value;
        const fechaReprog = document.getElementById('llamadaFechaReprogramada').value;
        const motivoDeriv = document.getElementById('llamadaMotivoDerivacion').value;

        fetch("{{ route('seguimiento-egresados.llamada') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: 'REAL-' + userId,
                tipo_resultado: tipoResultado,
                result: tipoResultado.replace(/_/g, ' '),
                comment: obs,
                campana_id: campanaId,
                fecha_reprogramada: fechaReprog,
                requiere_derivacion: (tipoResultado === 'requiere_derivacion'),
                motivo_derivacion: motivoDeriv,
                es_ensayo: true
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Llamada registrada correctamente en el expediente.');
                cerrarModalLlamada();
                window.location.reload();
            } else {
                alert('Error al registrar: ' + (data.error || 'Ocurrió un error.'));
            }
        })
        .catch(err => {
            alert('Error de conexión: ' + err.message);
        });
    }

    function abrirModalActualizar(userId, nombre, tel, dep, prov, dist) {
        document.getElementById('fichaTargetUserId').value = userId;
        document.getElementById('fichaNombre').textContent = nombre;
        document.getElementById('fichaTelefono').value = tel || '';
        document.getElementById('fichaDep').value = dep || 'CUSCO';
        document.getElementById('fichaProv').value = prov || 'CUSCO';
        document.getElementById('fichaDist').value = dist || 'CUSCO';
        document.getElementById('modalActualizarFicha').style.display = 'flex';
    }

    function cerrarModalActualizar() {
        document.getElementById('modalActualizarFicha').style.display = 'none';
    }

    function toggleFormularioSinCambios(sinCambios) {
        document.getElementById('camposDetalladosFicha').style.opacity = sinCambios ? '0.4' : '1';
        document.getElementById('camposDetalladosFicha').style.pointerEvents = sinCambios ? 'none' : 'auto';
    }

    function guardarFichaDocente(e) {
        e.preventDefault();
        const userId = document.getElementById('fichaTargetUserId').value;
        const sinCambios = document.getElementById('checkSinCambios').checked;
        const tel = document.getElementById('fichaTelefono').value;
        const trabaja = document.getElementById('fichaTrabaja').value;
        const inst = document.getElementById('fichaInstitucion').value;
        const cargo = document.getElementById('fichaCargo').value;
        const dep = document.getElementById('fichaDep').value;
        const prov = document.getElementById('fichaProv').value;
        const dist = document.getElementById('fichaDist').value;
        const nec = document.getElementById('fichaNecesidades').value;

        fetch("{{ route('seguimiento-egresados.actualizar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                sin_cambios: sinCambios,
                telefono: tel,
                trabaja: trabaja,
                institucion: inst,
                cargo: cargo,
                res_departamento: dep,
                res_provincia: prov,
                res_distrito: dist,
                necesidades: nec,
                campana: 'CAMP-ENSAYO-2026',
                es_ensayo: true
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Ficha actualizada exitosamente en el expediente institucional.');
                cerrarModalActualizar();
                window.location.reload();
            } else {
                alert('Error al actualizar: ' + (data.error || 'Ocurrió un error.'));
            }
        })
        .catch(err => {
            alert('Error de conexión: ' + err.message);
        });
    }
</script>
</body>
</html>
