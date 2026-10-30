<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta Consolidada {{ $acta->numero_acta ? '№ ' . $acta->numero_acta : '#' . $acta->id }} - IESP Ricardo Palma</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style id="rp-page-style">
        /* ══════════════════════════════════════════════════════════════════════
           MÁRGENES OFICIALES DE PÁGINA (A4 HORIZONTAL POR DEFECTO)
           Margen superior e inferior: 10mm | Margen izquierdo y derecho: 12mm
           Garantiza espacio limpio alrededor de toda la hoja.
           ══════════════════════════════════════════════════════════════════════ */
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm 12mm;
        }
    </style>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
            background-color: #334155;
            color: #000;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Barra Flotante de Herramientas (Solo pantalla) ── */
        .rp-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: #0f172a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            z-index: 10000;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
        }
        .rp-print-bar__info {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.84rem;
        }
        .rp-print-bar__badge {
            background: #0e9467;
            color: #fff;
            font-weight: 700;
            font-size: 0.72rem;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-transform: uppercase;
        }
        .rp-print-bar__controles {
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }
        .rp-btn-ctrl {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.8rem;
            border-radius: 7px;
            font-size: 0.79rem;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .rp-btn-ctrl--primary {
            background: #cd9244;
            color: #fff;
        }
        .rp-btn-ctrl--primary:hover {
            background: #b9823b;
        }
        .rp-btn-ctrl--excel {
            background: #0e9467;
            color: #fff;
        }
        .rp-btn-ctrl--excel:hover {
            background: #0c7a55;
        }
        .rp-btn-ctrl--dark {
            background: #1e293b;
            color: #e2e8f0;
            border-color: #334155;
        }
        .rp-btn-ctrl--dark:hover, .rp-btn-ctrl--dark.is-active {
            background: #334155;
            color: #fff;
            border-color: #cd9244;
        }
        .rp-select-ctrl {
            background: #1e293b;
            color: #fff;
            border: 1px solid #334155;
            padding: 0.4rem 0.65rem;
            border-radius: 7px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* ── Aviso sutil sobre configuración del navegador ── */
        .rp-tip-margen {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #1e293b;
            color: #94a3b8;
            font-size: 0.75rem;
            padding: 0.35rem 1rem;
            margin: 62px auto 0 auto;
            max-width: 900px;
            border-radius: 8px;
            border: 1px solid #334155;
        }
        .rp-tip-margen strong {
            color: #f1f5f9;
        }

        /* ── Lienzo del Documento A4 en Pantalla ── */
        .rp-hoja-marco {
            margin: 15px auto 40px auto;
            display: flex;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        .rp-documento-impresion {
            background: #ffffff;
            color: #000000;
            /* Margen visible alrededor de la hoja en pantalla */
            padding: 10mm 12mm;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            border-radius: 3px;
            width: 297mm; /* Exacto A4 Horizontal */
            min-height: 200mm;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .rp-documento-impresion.is-portrait {
            width: 210mm; /* Exacto A4 Vertical */
            min-height: 290mm;
            padding: 12mm 10mm;
        }

        /* ── Encabezado Oficial Ministerial ── */
        .rp-encabezado {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 5px;
        }
        .rp-escudo {
            width: 44px;
            height: 44px;
            flex: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rp-escudo svg {
            width: 100%;
            height: 100%;
        }
        .rp-titulos {
            text-align: center;
            flex: 1;
            padding: 0 10px;
        }
        .rp-titulos .t1 { font-size: 7.2pt; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .rp-titulos .t2 { font-size: 7.6pt; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; }
        .rp-titulos .t3 { font-size: 9.5pt; font-weight: 800; letter-spacing: 0.02em; margin-top: 1px; }
        .rp-titulos .t4 { font-size: 8.6pt; font-weight: 800; color: #1e3a5f; margin-top: 1px; letter-spacing: 0.03em; }

        /* ── Ficha Técnica de Metadatos ── */
        .rp-ficha-meta {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
            margin-bottom: 5px;
            border: 1px solid #000;
        }
        .rp-ficha-meta td {
            padding: 2px 6px;
            border: 1px solid #000;
            line-height: 1.15;
        }
        .rp-ficha-meta .meta-eti {
            font-weight: 700;
            background: #f1f5f9;
            width: 14%;
        }
        .rp-ficha-meta .meta-val {
            font-weight: 700;
            width: 36%;
        }

        /* ── Matriz Oficial de Calificaciones ── */
        .rp-tabla-marco {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .rp-tabla-oficial {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            color: #000;
            line-height: 1.1;
        }
        .rp-tabla-oficial th,
        .rp-tabla-oficial td {
            border: 1px solid #000;
            padding: 1.8px 1px;
            vertical-align: middle;
            text-align: center;
        }
        .rp-tabla-oficial thead th {
            background-color: #f1f5f9;
            font-weight: 800;
            text-transform: uppercase;
        }
        .rp-tabla-oficial thead .th-curso-nombre {
            font-size: 6.5pt;
            font-weight: 700;
            line-height: 1.1;
            padding: 2.5px 1px;
            max-width: 95px;
            word-break: break-word;
        }
        .rp-tabla-oficial thead .th-sub {
            font-size: 6.2pt;
            font-weight: 700;
            background-color: #e2e8f0;
            padding: 1px 0;
        }
        .rp-tabla-oficial tbody td.td-orden {
            width: 24px;
            font-weight: 700;
            font-size: 6.8pt;
        }
        .rp-tabla-oficial tbody td.td-mat {
            width: 50px;
            font-weight: 700;
            font-size: 7pt;
        }
        .rp-tabla-oficial tbody td.td-nom {
            text-align: left;
            padding-left: 5px;
            font-weight: 700;
            white-space: nowrap;
            font-size: 7pt;
        }
        .rp-tabla-oficial tbody td.td-nota {
            font-weight: 700;
            font-size: 7.4pt;
            width: 21px;
        }
        .rp-tabla-oficial tbody td.td-nota.is-desaprobado {
            color: #c0392b;
            font-weight: 800;
        }
        .rp-tabla-oficial tbody td.td-pjt {
            font-size: 6.5pt;
            color: #475569;
            width: 21px;
        }
        .rp-tabla-oficial tbody td.td-total {
            background: #fafafa;
            font-weight: 700;
            width: 30px;
            font-size: 7pt;
        }
        .rp-tabla-oficial tbody td.td-prom {
            font-weight: 800;
            font-size: 7.8pt;
            width: 40px;
        }
        .rp-tabla-oficial tbody td.td-prom.is-desaprobado {
            color: #c0392b;
        }
        .rp-tabla-oficial tbody td.td-obs {
            font-size: 6.6pt;
            font-weight: 700;
            width: 62px;
            text-transform: uppercase;
        }
        .rp-tabla-oficial tbody td.td-obs.is-retirado {
            color: #c0392b;
        }

        /* ── Pie: fecha de visación (como el «Fecha:» del pie del acta física) ── */
        .rp-pie-contenedor {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 15px;
            margin-top: 8px;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        .rp-cuadro-impreso {
            border-collapse: collapse;
            font-size: 6.8pt;
        }
        .rp-cuadro-impreso th, .rp-cuadro-impreso td {
            border: 1px solid #000;
            padding: 1px 5px;
            line-height: 1.15;
        }
        .rp-cuadro-impreso th { background: #f1f5f9; }
        .rp-titulos .t-fijos {
            font-size: 6.5pt;
            color: #333;
            margin-top: 2px;
        }
        .rp-pie-fecha strong {
            text-transform: uppercase;
            margin-right: 4px;
        }

        /* ══════════════════════════════════════════════════════════════════════
           REGLAS DE IMPRESIÓN OFICIAL (@media print)
           ══════════════════════════════════════════════════════════════════════ */
        @media print {
            html, body {
                width: 100% !important;
                height: auto !important;
                min-height: auto !important;
                max-height: none !important;
                background: #fff !important;
                color: #000 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            .rp-print-bar, .rp-tip-margen {
                display: none !important;
            }
            .rp-hoja-marco {
                margin: 0 !important;
                padding: 0 !important;
                transform: none !important;
                display: block !important;
                width: 100% !important;
                height: auto !important;
            }
            .rp-documento-impresion {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                min-height: auto !important;
                max-height: none !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                /* El padding de seguridad asegura que NUNCA toque el borde de la hoja física */
                padding: 2mm 0 !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
            .rp-tabla-oficial {
                page-break-inside: avoid !important;
            }
            .rp-tabla-oficial tr {
                page-break-inside: avoid !important;
            }
            .rp-pie-contenedor {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    {{-- BARRA DE CONTROL FLOTANTE (Solo pantalla) --}}
    <header class="rp-print-bar">
        <div class="rp-print-bar__info">
            <span class="rp-print-bar__badge">
                <i class="fas fa-check-circle"></i> A4 · 1 Hoja
            </span>
            <strong>IESP "Ricardo Palma"</strong> · Acta {{ $acta->numero_acta ? '№ ' . $acta->numero_acta : '#' . $acta->id }} ({{ $acta->periodo?->codigo }})
        </div>

        <div class="rp-print-bar__controles">
            {{-- Selector de Orientación --}}
            <span style="font-size: 0.76rem; color: #94a3b8; font-weight: 600;">Orientación:</span>
            <button type="button" class="rp-btn-ctrl rp-btn-ctrl--dark is-active" id="btnLandscape" onclick="setOrientacion('landscape')" title="A4 Horizontal (Recomendado para actas)">
                <i class="fas fa-arrows-alt-h"></i> Horizontal
            </button>
            <button type="button" class="rp-btn-ctrl rp-btn-ctrl--dark" id="btnPortrait" onclick="setOrientacion('portrait')" title="A4 Vertical">
                <i class="fas fa-arrows-alt-v"></i> Vertical
            </button>

            {{-- Selector de Márgenes de Página --}}
            <span style="font-size: 0.76rem; color: #94a3b8; font-weight: 600; margin-left: 0.3rem;">Márgenes:</span>
            <select class="rp-select-ctrl" id="selectMargenes" onchange="setMargenes(this.value)">
                <option value="normal" selected>Normal (10mm - Estándar)</option>
                <option value="minimo">Estrecho (6mm)</option>
                <option value="amplio">Amplio (15mm)</option>
            </select>

            {{-- Selector de Escala de Zoom --}}
            <span style="font-size: 0.76rem; color: #94a3b8; font-weight: 600; margin-left: 0.3rem;">Zoom:</span>
            <select class="rp-select-ctrl" id="selectZoom" onchange="setZoom(this.value)">
                <option value="1" selected>100%</option>
                <option value="0.95">95%</option>
                <option value="0.90">90%</option>
                <option value="0.85">85%</option>
                <option value="0.80">80%</option>
            </select>

            {{-- Exportar Excel --}}
            <a href="{{ route('rp.actas.excel', $acta) }}" class="rp-btn-ctrl rp-btn-ctrl--excel" title="Descargar archivo Excel oficial">
                <i class="fas fa-file-excel"></i> Excel
            </a>

            {{-- Imprimir --}}
            <button type="button" class="rp-btn-ctrl rp-btn-ctrl--primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir en A4
            </button>

            {{-- Volver / Cerrar --}}
            <a href="{{ route('rp.actas.show', $acta) }}" class="rp-btn-ctrl rp-btn-ctrl--dark" title="Volver al detalle del acta">
                <i class="fas fa-times"></i> Volver
            </a>
        </div>
    </header>

    {{-- Aviso contextual de impresión --}}
    <div class="rp-tip-margen">
        <i class="fas fa-lightbulb" style="color: #fbbf24;"></i>
        <span>En la ventana de la impresora (Ctrl+P): verifica que <strong>Márgenes</strong> esté en <strong>"Predeterminado"</strong> para que se aplique el margen oficial de 10mm.</span>
    </div>

    {{-- MARCO Y LIENZO IMPRESO (A4 EXACTO) --}}
    <main class="rp-hoja-marco" id="hojaMarco">
        <article class="rp-documento-impresion" id="docImpresion">

            <div>
                {{-- ENCABEZADO OFICIAL MINISTERIAL --}}
                <header class="rp-encabezado">
                    <div class="rp-escudo">
                        {{-- Escudo Nacional del Perú (Vectorial Oficial) --}}
                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M50 5 C30 5 15 20 15 45 C15 75 50 95 50 95 C50 95 85 75 85 45 C85 20 70 5 50 5 Z" fill="#fff" stroke="#000" stroke-width="2"/>
                            <path d="M50 5 L50 95" stroke="#000" stroke-width="1.5"/>
                            <path d="M15 45 L85 45" stroke="#000" stroke-width="1.5"/>
                            <circle cx="32" cy="25" r="8" fill="#c0392b"/>
                            <circle cx="68" cy="25" r="8" fill="#c0392b"/>
                            <rect x="25" y="55" width="50" height="25" fill="#f1c40f" stroke="#000" stroke-width="1"/>
                            <text x="50" y="72" font-size="12" font-weight="bold" text-anchor="middle" fill="#000">PERÚ</text>
                        </svg>
                    </div>
                    <div class="rp-titulos">
                        <div class="t1">República del Perú · Ministerio de Educación</div>
                        <div class="t2">Dirección Regional de Educación {{ \App\Models\RicardoPalma\RpActa::DRE }}</div>
                        <div class="t3">INSTITUTO DE EDUCACIÓN SUPERIOR PEDAGÓGICO "RICARDO PALMA"</div>
                        <div class="t4">ACTA CONSOLIDADA DE EVALUACIÓN DEL RENDIMIENTO ACADÉMICO</div>
                        {{-- Datos fijos del instituto que imprimen las actas físicas --}}
                        <div class="t-fijos">
                            Autorización de funcionamiento: {{ \App\Models\RicardoPalma\RpActa::AUTORIZACION }}
                            · D.R.E.: {{ \App\Models\RicardoPalma\RpActa::DRE }}
                            @foreach (\App\Models\RicardoPalma\RpActa::UBICACION as $eti => $valor)
                                · {{ $eti }}: {{ $valor }}
                            @endforeach
                        </div>
                    </div>
                    <div class="rp-escudo">
                        {{-- Emblema Pedagógico --}}
                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="45" stroke="#000" stroke-width="2" fill="#fff"/>
                            <circle cx="50" cy="50" r="38" stroke="#1e3a5f" stroke-width="1" stroke-dasharray="3 3"/>
                            <path d="M30 65 L50 30 L70 65 Z" fill="#cd9244" stroke="#000" stroke-width="1"/>
                            <path d="M40 65 L50 45 L60 65 Z" fill="#fff"/>
                            <text x="50" y="80" font-size="9" font-weight="bold" text-anchor="middle" fill="#000">IESP RP</text>
                        </svg>
                    </div>
                </header>

                {{-- FICHA TÉCNICA --}}
                <table class="rp-ficha-meta">
                    <tr>
                        <td class="meta-eti">PROGRAMA:</td>
                        <td class="meta-val">{{ $acta->programa?->nombre }}</td>
                        <td class="meta-eti">PERIODO ACAD.:</td>
                        <td class="meta-val">{{ $acta->periodo?->codigo }}</td>
                    </tr>
                    <tr>
                        <td class="meta-eti">SEMESTRE / CICLO:</td>
                        <td class="meta-val">Semestre {{ $acta->numero_semestre }}</td>
                        <td class="meta-eti">SECCIÓN / TURNO:</td>
                        <td class="meta-val">Sección: {{ $acta->seccion }} · Turno: {{ $acta->turno }}</td>
                    </tr>
                    <tr>
                        <td class="meta-eti">N° ACTA OFICIAL:</td>
                        <td class="meta-val">{{ $acta->numero_acta ?: 'S/N' }}</td>
                        <td class="meta-eti">FECHA DE VISACIÓN:</td>
                        <td class="meta-val">{{ $acta->fecha_emision ? $acta->fecha_emision->format('d/m/Y') : '—' }}</td>
                    </tr>
                </table>

                {{-- MATRIZ DE CALIFICACIONES --}}
                <table class="rp-tabla-oficial">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 24px;">N°</th>
                            <th rowspan="2" style="width: 50px;">Matrícula</th>
                            <th rowspan="2" style="text-align: left; padding-left: 5px;">Apellidos y Nombres</th>
                            @foreach ($cursos as $c)
                                <th colspan="2" class="th-curso-nombre">
                                    {{ $c->nombre }}
                                    <div style="font-size: 6pt; font-weight: normal; margin-top: 1px;">({{ $c->creditos }} crd)</div>
                                </th>
                            @endforeach
                            <th rowspan="2" style="width: 30px;">Pjt Tot</th>
                            <th rowspan="2" style="width: 28px;">Tot Crd</th>
                            <th rowspan="2" style="width: 40px;">Prom. Pond.</th>
                            <th rowspan="2" style="width: 62px;">Situación</th>
                        </tr>
                        <tr>
                            @foreach ($cursos as $c)
                                <th class="th-sub" style="width: 21px;">CLF</th>
                                <th class="th-sub" style="width: 21px;">PJT</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estudiantes as $index => $item)
                            @php
                                $prom = $item->promedio_ponderado;
                                $esRetirado = $item->retirado;
                            @endphp
                            <tr>
                                <td class="td-orden">{{ $index + 1 }}</td>
                                <td class="td-mat">{{ $item->estudiante?->numero_matricula ?: '—' }}</td>
                                <td class="td-nom">
                                    {{ mb_strtoupper($item->estudiante?->apellidos . ', ' . $item->estudiante?->nombres) }}
                                </td>

                                {{-- Notas por curso --}}
                                @foreach ($cursos as $c)
                                    @php
                                        $calif = $item->notas->get($c->id);
                                        $nota = $calif?->calificacion;
                                        $pjt = $calif?->puntaje;
                                    @endphp
                                    <td class="td-nota {{ $nota !== null && $nota < 11 ? 'is-desaprobado' : '' }}">
                                        {{ $nota !== null ? number_format($nota, 0) : '—' }}
                                    </td>
                                    <td class="td-pjt">
                                        {{ $pjt !== null ? number_format($pjt, 0) : '—' }}
                                    </td>
                                @endforeach

                                {{-- Totales y Promedio --}}
                                <td class="td-total">{{ $item->puntaje_total }}</td>
                                <td class="td-total">{{ $item->total_creditos }}</td>
                                <td class="td-prom {{ $prom !== null && $prom < 11 ? 'is-desaprobado' : '' }}">
                                    {{ $prom !== null ? number_format($prom, 2) : '—' }}
                                </td>
                                <td class="td-obs {{ $esRetirado ? 'is-retirado' : '' }}">
                                    {{-- La observación del acta física manda sobre la situación calculada --}}
                                    @if ($item->observacion)
                                        {{ $item->observacion }}
                                    @else
                                        {{ $item->situacion }}@if ($item->cursos_desaprobados) ({{ $item->cursos_desaprobados }})@endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 7 + ($cursos->count() * 2) }}" style="padding: 15px; text-align: center;">
                                    No existen calificaciones registradas en este acta.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pie: cuadro estadístico (como el del acta física) y fecha de visación; sin firmas --}}
            <footer class="rp-pie-contenedor">
                @include('admin.ricardo-palma.actas._cuadro', ['clase' => 'rp-cuadro-impreso', 'titulo' => 'CUADRO ESTADÍSTICO'])
                <div class="rp-pie-fecha">
                    <strong>Fecha de visación:</strong>
                    {{ $acta->fecha_emision ? $acta->fecha_emision->locale('es')->translatedFormat('j \d\e F \d\e Y') : '____ de ______________ de ______' }}
                </div>
            </footer>
        </article>
    </main>

    {{-- SCRIPT INTERACTIVO --}}
    <script>
        let currentOrientacion = 'landscape';
        let currentMargenTB = '10mm';
        let currentMargenLR = '12mm';

        function aplicarEstilosPagina() {
            const styleTag = document.getElementById('rp-page-style');
            const sizeStr = (currentOrientacion === 'portrait') ? 'A4 portrait' : 'A4 landscape';
            styleTag.innerHTML = `@page { size: ${sizeStr}; margin: ${currentMargenTB} ${currentMargenLR} ${currentMargenTB} ${currentMargenLR}; }`;
        }

        function setOrientacion(modo) {
            currentOrientacion = modo;
            const doc = document.getElementById('docImpresion');
            const btnLand = document.getElementById('btnLandscape');
            const btnPort = document.getElementById('btnPortrait');

            if (modo === 'portrait') {
                doc.classList.add('is-portrait');
                btnPort.classList.add('is-active');
                btnLand.classList.remove('is-active');
            } else {
                doc.classList.remove('is-portrait');
                btnLand.classList.add('is-active');
                btnPort.classList.remove('is-active');
            }
            aplicarEstilosPagina();
        }

        function setMargenes(tipo) {
            const doc = document.getElementById('docImpresion');
            if (tipo === 'minimo') {
                currentMargenTB = '6mm';
                currentMargenLR = '6mm';
                doc.style.padding = '6mm 6mm';
            } else if (tipo === 'amplio') {
                currentMargenTB = '15mm';
                currentMargenLR = '15mm';
                doc.style.padding = '15mm 15mm';
            } else {
                // Normal
                currentMargenTB = '10mm';
                currentMargenLR = '12mm';
                doc.style.padding = (currentOrientacion === 'portrait') ? '12mm 10mm' : '10mm 12mm';
            }
            aplicarEstilosPagina();
        }

        function setZoom(escala) {
            const marco = document.getElementById('hojaMarco');
            marco.style.transform = `scale(${escala})`;
            marco.style.transformOrigin = 'top center';
        }

        // Inicializar al cargar
        aplicarEstilosPagina();
    </script>
</body>
</html>
