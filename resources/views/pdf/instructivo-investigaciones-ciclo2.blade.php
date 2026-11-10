<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instructivo para Estudiantes - Módulo Investigaciones (Ciclo II)</title>
    <style>
        @page {
            margin: 1.2cm 1.4cm 1.2cm 1.4cm;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8.9pt;
            line-height: 1.34;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* ── Colores Institucionales ── */
        .c-petroleo { color: #33445a; }
        .c-oro { color: #b87c28; }
        .c-ok { color: #0e9467; }
        .c-alerta { color: #dc2626; }

        /* ── Cabecera / Membrete ── */
        .cabecera {
            border-bottom: 2.5px solid #cd9244;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .cabecera table {
            width: 100%;
            border-collapse: collapse;
        }
        .cabecera td {
            vertical-align: middle;
        }
        .cabecera-logo {
            width: 155px;
        }
        .cabecera-logo img {
            width: 145px;
            height: auto;
        }
        .cabecera-titulo {
            text-align: right;
        }
        .cabecera-kicker {
            font-size: 7.2pt;
            font-weight: bold;
            color: #b87c28;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 2px;
        }
        .cabecera-h1 {
            font-size: 13pt;
            font-weight: bold;
            color: #33445a;
            margin: 0 0 2px 0;
            letter-spacing: -0.01em;
        }
        .cabecera-sub {
            font-size: 8.2pt;
            color: #64748b;
        }

        /* ── Pie de Página Fijo (todas las hojas) ── */
        .pie-pagina {
            position: fixed;
            bottom: -0.9cm;
            left: 0;
            right: 0;
            height: 0.7cm;
            border-top: 1px solid #cbd5e1;
            padding-top: 3px;
            font-size: 7pt;
            color: #64748b;
        }
        .pie-pagina table {
            width: 100%;
            border-collapse: collapse;
        }

        /* ── Cajas de Resalte ── */
        .caja-intro {
            background-color: #f8fafc;
            border-left: 3.5px solid #33445a;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 9px;
        }
        .caja-intro p {
            margin: 0;
            font-size: 8.4pt;
            color: #334155;
            line-height: 1.32;
        }

        .caja-proyecto {
            background-color: #fffdfa;
            border: 1.5px solid #f2c88e;
            border-radius: 6px;
            padding: 7px 11px;
            margin-bottom: 10px;
        }
        .caja-proyecto-titulo {
            font-size: 7.5pt;
            font-weight: bold;
            color: #b87c28;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }
        .caja-proyecto-nombre {
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .caja-proyecto-producto {
            font-size: 8.4pt;
            color: #0e9467;
            font-weight: bold;
        }

        /* ── Títulos de Sección ── */
        .seccion-titulo {
            font-size: 10pt;
            font-weight: bold;
            color: #33445a;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 2px;
            margin: 10px 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .seccion-sub {
            font-size: 8pt;
            color: #64748b;
            margin-top: -4px;
            margin-bottom: 8px;
        }

        /* ── Pasos Numerados ── */
        .paso-fila {
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .paso-fila table {
            width: 100%;
            border-collapse: collapse;
        }
        .paso-num-td {
            width: 30px;
            vertical-align: top;
            padding-top: 1px;
        }
        .paso-badge {
            display: inline-block;
            width: 22px;
            height: 22px;
            line-height: 22px;
            text-align: center;
            background-color: #33445a;
            color: #ffffff;
            font-weight: bold;
            font-size: 9pt;
            border-radius: 11px;
        }
        .paso-badge.is-oro { background-color: #cd9244; }
        .paso-badge.is-ok { background-color: #0e9467; }
        .paso-contenido-td {
            vertical-align: top;
        }
        .paso-titulo {
            font-size: 9.3pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .paso-desc {
            font-size: 8.5pt;
            color: #475569;
            margin: 0 0 4px 0;
        }

        /* ── Capturas / Ayuda Visual ── */
        .captura-marco {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 3px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.06);
            margin: 4px 0 2px 0;
            display: inline-block;
        }
        .captura-pie {
            font-size: 7.2pt;
            color: #64748b;
            font-style: italic;
            margin-top: 2px;
            margin-bottom: 4px;
        }

        /* ── Cuadros de Advertencia y Tips ── */
        .caja-tip {
            background-color: #f0fdf4;
            border-left: 3px solid #0e9467;
            padding: 5px 9px;
            font-size: 8pt;
            color: #166534;
            border-radius: 3px;
            margin: 4px 0;
        }
        .caja-alerta {
            background-color: #fef2f2;
            border-left: 3px solid #ef4444;
            padding: 5px 9px;
            font-size: 8pt;
            color: #991b1b;
            border-radius: 3px;
            margin: 4px 0;
        }
        .caja-info {
            background-color: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 5px 9px;
            font-size: 8pt;
            color: #1e40af;
            border-radius: 3px;
            margin: 4px 0;
        }

        /* ── Tablas Informativas ── */
        .tabla-guia {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 8px 0;
            font-size: 8pt;
        }
        .tabla-guia th {
            background-color: #33445a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 7px;
            border: 1px solid #33445a;
        }
        .tabla-guia td {
            padding: 4px 7px;
            border: 1px solid #cbd5e1;
            color: #334155;
            vertical-align: middle;
        }
        .tabla-guia tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* ── Salto de Página ── */
        .salto-pagina {
            page-break-before: always;
        }

        .checklist {
            list-style: none;
            padding: 0;
            margin: 3px 0;
        }
        .checklist li {
            padding: 2.5px 0 2.5px 18px;
            position: relative;
            font-size: 8.2pt;
            color: #334155;
        }
        .casilla-cuadro {
            position: absolute;
            left: 0;
            top: 3px;
            width: 11px;
            height: 11px;
            border: 1.5px solid #64748b;
            border-radius: 2px;
        }

        .badge-mini {
            display: inline-block;
            padding: 1px 5px;
            font-size: 6.8pt;
            font-weight: bold;
            border-radius: 3px;
            background: #e2e8f0;
            color: #334155;
        }
        .badge-mini.is-ok { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>

    {{-- ── Pie de Página Fijo (aparece en todas las hojas) ── --}}
    <div class="pie-pagina">
        <table>
            <tr>
                <td style="text-align: left; width: 65%;">
                    <strong>EESP Pukllasunchis</strong> · Módulo Investigaciones · Guía de Orientación al Estudiante
                </td>
                <td style="text-align: right; width: 35%;">
                    Ciclo II — Formación Inicial Docente
                </td>
            </tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- PÁGINA 1: INGRESO AL MÓDULO Y VISTA DEL DASHBOARD DEL ALUMNO      --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}

    {{-- Cabecera Institucional --}}
    <div class="cabecera">
        <table>
            <tr>
                <td class="cabecera-logo">
                    @if (file_exists(public_path('img/logo-iesp-pukllasunchis.png')))
                        <img src="{{ public_path('img/logo-iesp-pukllasunchis.png') }}" alt="EESP Pukllasunchis">
                    @else
                        <strong style="font-size: 12pt; color: #33445a;">EESP PUKLLASUNCHIS</strong>
                    @endif
                </td>
                <td class="cabecera-titulo">
                    <div class="cabecera-kicker">Material de Orientación Estudiantil · FID 2026</div>
                    <div class="cabecera-h1">GUÍA DEL MÓDULO INVESTIGACIONES</div>
                    <div class="cabecera-sub">Orientaciones prácticas para estudiantes del <strong>Ciclo II</strong></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Introducción --}}
    <div class="caja-intro">
        <p>
            <strong>Estimado(a) estudiante del Ciclo II:</strong> Esta guía te orientará paso a paso en el uso del módulo
            <strong>Investigaciones</strong> dentro de la Intranet del Alumno. Aquí conformarás tu equipo de trabajo,
            registrarás las evidencias de tu trabajo de campo y presentarás tus productos finales para la Feria Institucional.
        </p>
    </div>

    {{-- Proyecto Integrador de Ciclo II --}}
    <div class="caja-proyecto">
        <div class="caja-proyecto-titulo">Tu Proyecto Integrador en este Ciclo:</div>
        <div class="caja-proyecto-nombre">"Escuela y Territorio: Diagnóstico Socio-Cultural"</div>
        <div class="caja-proyecto-producto">
            ★ Producto Final Esperado: Mapa parlante y tríptico descriptivo del territorio sagrado de una Institución Educativa.
        </div>
        <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">
            Curso articulador principal: <strong>Práctica e Investigación II</strong> (con el respaldo metodológico de Comunicación y Lengua Originaria).
        </div>
    </div>

    {{-- Sección 1: Ingreso al módulo y Captura Real del Dashboard --}}
    <div class="seccion-titulo">1. ¿Cómo Ingresar al Módulo desde tu Intranet?</div>
    <div class="seccion-sub">El acceso se realiza directamente desde el menú lateral de tu sesión de estudiante:</div>

    <div class="paso-fila">
        <table>
            <tr>
                <td class="paso-num-td"><span class="paso-badge">1</span></td>
                <td class="paso-contenido-td">
                    <div class="paso-titulo">Ingreso a la Intranet y Menú "Investigaciones"</div>
                    <div class="paso-desc">
                        Ingresa a la <strong>Intranet del Alumno</strong> con tu correo y contraseña institucional.
                        En el menú lateral izquierdo, haz clic en la opción <strong>"Investigaciones"</strong> (con el ícono de matraz).
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Captura Real 1: Dashboard del Alumno (Pantalla Completa) --}}
    @if (file_exists(public_path('img/guia/01_dashboard_alumno_inicio.png')))
        <div style="text-align: center; margin: 4px 0 2px 0;">
            <div class="captura-marco">
                <img src="{{ public_path('img/guia/01_dashboard_alumno_inicio.png') }}" style="width: 520px; height: auto; display: block;" alt="Dashboard del Alumno - Inicio">
            </div>
            <div class="captura-pie">▲ Vista real de tu Dashboard de Alumno: menú lateral "Investigaciones", franja del Proyecto Integrador, invitaciones pendientes y botón "Crear mi primer grupo".</div>
        </div>
    @endif

    <div class="caja-info" style="margin-top: 3px;">
        <strong>Elementos clave que verás en tu pantalla:</strong><br>
        1. <strong>Menú lateral izquierdo:</strong> Opción <em>"Investigaciones"</em> resaltada para tu acceso directo.<br>
        2. <strong>Franja de Proyecto Integrador:</strong> Muestra el nombre y producto del Ciclo II (<em>Escuela y Territorio</em>).<br>
        3. <strong>Avisos de invitación:</strong> Si un compañero ya te invitó, verás una tarjeta amarilla para <em>"Aceptar"</em> o <em>"Rechazar"</em>.<br>
        4. <strong>Botón dorado "Crear mi primer grupo" / "Nuevo grupo":</strong> Para comenzar a organizar tu equipo de trabajo.
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- PÁGINA 2: CREACIÓN DE GRUPO Y FORMACIÓN DEL EQUIPO                --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="salto-pagina"></div>

    <div class="seccion-titulo">2. Formación del Equipo (Crear Grupo o Aceptar Invitación)</div>
    <div class="seccion-sub">El trabajo es colaborativo. Solo uno de los estudiantes del equipo debe crear el grupo:</div>

    <div class="paso-fila">
        <table>
            <tr>
                <td class="paso-num-td"><span class="paso-badge">2</span></td>
                <td class="paso-contenido-td">
                    <div class="paso-titulo">Dos formas de integrar el equipo de trabajo</div>
                    <div class="paso-desc">
                        Coordina previamente con tus compañeros de equipo y elijan una de las dos modalidades:
                    </div>

                    <table style="width: 100%; border-collapse: collapse; margin: 3px 0 6px 0; font-size: 8pt;">
                        <tr>
                            <td style="width: 50%; padding: 5px 9px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; vertical-align: top;">
                                <strong style="color: #33445a;">Opción A: Tú creas el grupo (Organizador/a)</strong><br>
                                1. Pulsa el botón dorado <strong>"Nuevo grupo"</strong>.<br>
                                2. Elige el curso articulador: <em>Práctica e Investigación II</em>.<br>
                                3. Escribe el nombre de tu equipo (ej. <em>"Los Yachaykuna"</em>).<br>
                                4. Marca con un check a tus compañeros de clase.<br>
                                5. Pulsa <strong>"Crear grupo e invitar"</strong>.
                            </td>
                            <td style="width: 50%; padding: 5px 9px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; vertical-align: top;">
                                <strong style="color: #33445a;">Opción B: Tu compañero ya creó el grupo</strong><br>
                                1. Al entrar a Investigaciones verás la tarjeta amarilla en tu pantalla.<br>
                                2. Verifica el nombre de tu equipo y quién te invitó.<br>
                                3. Pulsa el botón verde <strong>"Aceptar"</strong>.<br>
                                4. ¡Listo! Ya formas parte del equipo y podrás subir evidencias junto a tus compañeros.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Captura Real 2: Modal de Crear Grupo en el Dashboard --}}
    @if (file_exists(public_path('img/guia/02_dashboard_crear_grupo.png')))
        <div style="text-align: center; margin: 4px 0 2px 0;">
            <div class="captura-marco">
                <img src="{{ public_path('img/guia/02_dashboard_crear_grupo.png') }}" style="width: 510px; height: auto; display: block;" alt="Modal Crear Grupo en Dashboard">
            </div>
            <div class="captura-pie">▲ Ventana emergente en tu Dashboard: selección de curso articulador (Práctica e Investigación II), nombre de equipo y lista con check para sumar a tus compañeros.</div>
        </div>
    @endif

    <div class="caja-alerta">
        <strong>¡Regla de Oro Institucional!</strong> Cada estudiante solo puede pertenecer a <strong>un grupo por periodo</strong>.
        Si por error aceptaste la invitación de otro grupo, pulsa el botón rojo <strong>"Salir"</strong> dentro de ese grupo para liberarte y poder unirte al equipo correcto.
    </div>

    <div class="seccion-titulo" style="margin-top: 8px;">3. Avance Visual del Grupo en 3 Fases</div>
    <div class="seccion-sub">Una vez conformado el grupo, la plataforma organiza el progreso mediante una barra de 3 estados:</div>

    <div class="caja-tip">
        <strong>Significado de los 3 Pasos en la cabecera de tu grupo:</strong><br>
        • <strong style="color: #0e9467;">Paso 1 (Equipo):</strong> Se marca en verde cuando todos los compañeros invitados pulsan "Aceptar".<br>
        • <strong style="color: #0e9467;">Paso 2 (Evidencias):</strong> Se activa y marca en verde en cuanto suben sus primeras fotos, audios o fichas de campo.<br>
        • <strong style="color: #b87c28;">Paso 3 (Trabajo final):</strong> Pasa a estado "Listo para entregar" y luego a verde oficial cuando el organizador pulsa <em>"Entregar trabajo final"</em>.
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- PÁGINA 3: ESPACIO DE TRABAJO (EVIDENCIAS Y TRABAJO FINAL)         --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="salto-pagina"></div>

    <div class="seccion-titulo">4. Tu Espacio de Trabajo: Evidencias y Entrega Final</div>
    <div class="seccion-sub">Pantalla interactiva en dos columnas para recopilar el trabajo de campo y presentar los productos:</div>

    {{-- Captura Real 3: Dashboard con Grupo Activo (2 Columnas) --}}
    @if (file_exists(public_path('img/guia/03_dashboard_grupo_columnas.png')))
        <div style="text-align: center; margin: 3px 0 2px 0;">
            <div class="captura-marco">
                <img src="{{ public_path('img/guia/03_dashboard_grupo_columnas.png') }}" style="width: 520px; height: auto; display: block;" alt="Espacio de trabajo del estudiante">
            </div>
            <div class="captura-pie">▲ Pantalla de trabajo del estudiante: barra de 3 fases, columna izquierda de Evidencias con botón "↑ Pasar", y columna derecha de Trabajo Final con el botón dorado de entrega oficial.</div>
        </div>
    @endif

    {{-- Detalle de las dos columnas --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 6px;">
                <div class="seccion-titulo" style="font-size: 8.8pt; margin: 4px 0 3px 0;">Columna Izquierda: Evidencias</div>
                <div style="font-size: 7.9pt; color: #475569; margin-bottom: 4px;">
                    Espacio para registrar avances semanales. <u>Cualquier integrante</u> puede subir archivos o enlaces:
                </div>
                <table class="tabla-guia" style="font-size: 7.6pt; margin: 2px 0;">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Formatos Ciclo II</th>
                            <th>Límite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Fotos</strong></td>
                            <td>Entorno escolar, croquis (JPG, PNG)</td>
                            <td>Máx. 10 MB</td>
                        </tr>
                        <tr>
                            <td><strong>Audios</strong></td>
                            <td>Entrevistas a sabios (MP3, WAV)</td>
                            <td>Máx. 10 MB</td>
                        </tr>
                        <tr>
                            <td><strong>Documentos</strong></td>
                            <td>Diario de campo, fichas (Word, PDF)</td>
                            <td>Máx. 25 MB</td>
                        </tr>
                        <tr>
                            <td><strong>Enlaces</strong></td>
                            <td>Google Drive, Canva, YouTube</td>
                            <td>Sin límite</td>
                        </tr>
                    </tbody>
                </table>
                <div class="caja-tip" style="font-size: 7.6pt; padding: 4px 7px;">
                    <strong>Botón "↑ Pasar":</strong> Haz clic en esta flecha verde para promover una evidencia directamente a la columna de Trabajo Final sin tener que volver a subirla.
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 6px;">
                <div class="seccion-titulo" style="font-size: 8.8pt; margin: 4px 0 3px 0;">Columna Derecha: Trabajo Final</div>
                <div style="font-size: 7.9pt; color: #475569; margin-bottom: 4px;">
                    Productos consolidados que serán calificados por los docentes en la Feria:
                </div>
                <div class="caja-info" style="font-size: 7.7pt; padding: 4px 7px; margin-bottom: 4px;">
                    <strong>Productos exigidos para tu Ciclo II:</strong><br>
                    1. <strong>Mapa Parlante:</strong> Digitalizado o en PDF con la zonificación territorial y cultural.<br>
                    2. <strong>Tríptico Descriptivo:</strong> Diagnóstico del territorio sagrado, saberes y apus (PDF o enlace Canva).
                </div>
                <div class="caja-tip" style="font-size: 7.6pt; padding: 4px 7px; background-color: #fffdfa; border-left: 3px solid #b87c28; color: #7c2d12;">
                    <strong>Entrega Oficial:</strong> Una vez colocados los dos productos en la columna derecha, el(la) <strong>Organizador(a)</strong> pulsa el botón dorado <strong>"Entregar trabajo final"</strong>.<br>
                    <em>¿Necesitan corregir algo?</em> Si la fecha límite aún no vence, el organizador puede pulsar <strong>"Reabrir entrega"</strong> para actualizar archivos.
                </div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- PÁGINA 4: AYUDA, ERRORES COMUNES Y LISTA DE CHEQUEO               --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="salto-pagina"></div>

    <div class="seccion-titulo">5. Solución de Imprevistos y Ayuda Directa</div>
    <div class="seccion-sub">Herramientas en tu pantalla para resolver cualquier dificultad con tu equipo:</div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
        <tr>
            <td style="width: 48%; padding: 7px 10px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; vertical-align: top;">
                <strong style="color: #33445a; font-size: 8.7pt;">Botón "Pedir Ayuda" (Salvavidas)</strong>
                <p style="font-size: 8pt; color: #475569; margin: 3px 0 0 0;">
                    En la cabecera de tu grupo encontrarás el botón con el salvavidas. Si tienen dudas metodológicas, dificultades con un integrante o problemas al subir un archivo, escriban su consulta y le llegará directamente al docente responsable del curso.
                </p>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; padding: 7px 10px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; vertical-align: top;">
                <strong style="color: #33445a; font-size: 8.7pt;">Cambio de Organizador</strong>
                <p style="font-size: 8pt; color: #475569; margin: 3px 0 0 0;">
                    Si el organizador del equipo tiene problemas de conectividad o no puede realizar la entrega final, dentro del botón <strong>"Integrantes"</strong> se puede transferir el rol de organizador a otro compañero para que el grupo nunca quede bloqueado.
                </p>
            </td>
        </tr>
    </table>

    {{-- Errores Comunes --}}
    <div class="seccion-titulo">6. Errores Frecuentes y Cómo Resolverlos</div>

    <table class="tabla-guia">
        <thead>
            <tr>
                <th style="width: 30%;">Situación Frecuente</th>
                <th style="width: 38%;">¿Por qué ocurre?</th>
                <th style="width: 32%;">Solución Correcta</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>"El docente no puede abrir mi enlace de Drive o Canva"</strong></td>
                <td>Compartiste el enlace en modo privado sin habilitar permisos de lectura general.</td>
                <td>En Google Drive / Canva, configura: <em>"Cualquier persona con el enlace (Lector)"</em>.</td>
            </tr>
            <tr>
                <td><strong>"Mi foto no sube o sale error de formato"</strong></td>
                <td>Algunos celulares iPhone o nuevos Android guardan en formato <code>.heic</code>.</td>
                <td>Tómale una captura de pantalla a la foto o conviértela a JPG/PNG antes de subirla.</td>
            </tr>
            <tr>
                <td><strong>"Mi video pesa mucho y da error al subir"</strong></td>
                <td>El archivo supera los 50 MB o dura más de 2 minutos.</td>
                <td>Súbelo a YouTube (en modo <em>No listado</em>) o a Google Drive y comparte el enlace web.</td>
            </tr>
            <tr>
                <td><strong>"Creímos que con subir evidencias ya habíamos entregado"</strong></td>
                <td>Las evidencias son el borrador de campo; falta consolidar en la columna derecha.</td>
                <td>Pasa los productos a la columna <strong>Trabajo final</strong> y pulsa el botón dorado <strong>"Entregar"</strong>.</td>
            </tr>
            <tr>
                <td><strong>"Acepté la invitación de otro grupo por error"</strong></td>
                <td>Hiciste clic en Aceptar sin verificar el nombre de los integrantes.</td>
                <td>Pulsa el botón rojo <strong>"Salir"</strong> de ese grupo; quedarás libre para unirte al correcto.</td>
            </tr>
        </tbody>
    </table>

    {{-- Lista de Chequeo para el Estudiante --}}
    <div class="seccion-titulo">7. Lista de Chequeo (Checklist) para la Feria de Investigación</div>
    <div class="seccion-sub">Marca con un lápiz cada punto conforme vayas avanzando con tu equipo:</div>

    <div style="background-color: #fffdfa; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px;">
        <ul class="checklist">
            <li>
                <div class="casilla-cuadro"></div>
                <strong>Paso 1:</strong> Todos los miembros del equipo están dentro del grupo con estado <span class="badge-mini is-ok">Aceptado</span>.
            </li>
            <li>
                <div class="casilla-cuadro"></div>
                <strong>Paso 2:</strong> Subieron al menos 3 evidencias de trabajo de campo (audios de entrevistas, fotos de la I.E., fichas de diagnóstico).
            </li>
            <li>
                <div class="casilla-cuadro"></div>
                <strong>Paso 3:</strong> Diseñaron y subieron el <strong>Mapa Parlante</strong> del territorio de la escuela (imagen en alta resolución o PDF).
            </li>
            <li>
                <div class="casilla-cuadro"></div>
                <strong>Paso 4:</strong> Subieron el <strong>Tríptico Descriptivo</strong> con el diagnóstico socio-cultural y los saberes territoriales.
            </li>
            <li>
                <div class="casilla-cuadro"></div>
                <strong>Paso 5:</strong> Ambos productos están colocados en la columna derecha <strong>"Trabajo Final"</strong>.
            </li>
            <li>
                <div class="casilla-cuadro"></div>
                <strong>Paso 6:</strong> El(la) organizador(a) pulsó <strong>"Entregar trabajo final"</strong> y el grupo figura como <span class="badge-mini is-ok">Finalizado</span>.
            </li>
        </ul>
    </div>

    {{-- Cierre Institucional --}}
    <div style="text-align: center; margin-top: 10px; padding-top: 8px; border-top: 1px dashed #cbd5e1;">
        <span style="font-size: 9.5pt; font-weight: bold; color: #33445a;">
            ¡Muchos éxitos en su investigación y en la Feria Institucional!
        </span><br>
        <span style="font-size: 7.8pt; color: #64748b;">
            EESP Pukllasunchis · Siete Diablitos 222, San Blas, Cusco · www.eesppukllasunchis.edu.pe
        </span>
    </div>

</body>
</html>
