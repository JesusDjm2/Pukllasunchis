@extends('layouts.seguimiento')
@section('titulo','Visor territorial conectado · Seguimiento de Egresados')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="{{ asset('css/seguimiento-egresados.css') }}?v={{ filemtime(public_path('css/seguimiento-egresados.css')) }}">
<style>
.filtro-card {
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 12px 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.filtro-label {
    font-weight: 700;
    font-size: 0.82rem;
    color: #4A5568;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    display: block;
}
.filtro-select {
    width: 100%;
    padding: 7px 10px;
    border: 1px solid #CBD5E0;
    border-radius: 6px;
    background: #fff;
    font-size: 0.9rem;
    color: #2D3748;
}
.legend-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.82rem;
    font-weight: 600;
    background: #EDF2F7;
    color: #2D3748;
}
.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.sin-ubicacion-banner {
    background: #FFFBEB;
    border: 1px solid #FCD34D;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 16px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
</style>
@endpush
@section('contenido')
<div class="seguimiento-panel">
<div class="seg-contenedor">
@include('seguimiento_egresados.partials.grupo_formacion')
<div class="d-flex flex-wrap align-items-center mb-2" style="gap:10px;">
    <a class="button secondary" href="{{ route('seguimiento.panel.coordinacion', ['grupo_formacion' => $grupoFormacion]) }}">← Coordinación de egresados</a>
    <a class="button secondary" href="{{ route('seguimiento.campanas.gestion', ['grupo_formacion' => $grupoFormacion]) }}">Padrón y Campañas</a>
    <a class="button secondary" href="{{ route('seguimiento.laboratorio', ['grupo_formacion' => $grupoFormacion]) }}">Cruces y exportaciones</a>
</div>

<section>
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <div>
            <h1 style="margin-bottom:4px;">Observatorio territorial conectado</h1>
            <p style="margin-bottom:0;color:#4A5568;">Visualización de concentración territorial, residencia, empleo y cobertura de contacto según el catálogo oficial.</p>
        </div>
        <button id="actualizar" type="button" class="btn btn-primary" style="padding:8px 20px;border-radius:8px;font-weight:700;">
            <i class="fas fa-sync-alt"></i> Actualizar mapa
        </button>
    </div>

    <!-- Controles Principales -->
    <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:12px;margin-bottom:14px;">
        <!-- Filtro Ubicación -->
        <div class="filtro-card">
            <label for="capa" class="filtro-label">Tipo de Ubicación</label>
            <select id="capa" class="filtro-select">
                <option value="ambos" selected>Trabajo y Residencia (Ambos)</option>
                <option value="trabajo">Lugar de Trabajo</option>
                <option value="residencia">Lugar de Residencia</option>
            </select>
        </div>

        <!-- Filtro Estado de Contacto -->
        <div class="filtro-card">
            <label for="estado-contacto" class="filtro-label">Estado de Contacto</label>
            <select id="estado-contacto" class="filtro-select">
                <option value="todos" selected>Todos los estados</option>
                <option value="sin_contacto_efectivo">Sin contacto efectivo (Prioritarios)</option>
                <option value="sin_intento">Sin intento registrado</option>
                <option value="intento_sin_respuesta">Intento sin respuesta</option>
                <option value="contacto_efectivo">Contacto efectivo</option>
                <option value="sin_canal_valido">Sin canal válido registrado</option>
            </select>
        </div>

        <!-- Control Vista -->
        <div class="filtro-card">
            <label for="vista" class="filtro-label">Modo de Vista</label>
            <select id="vista" class="filtro-select">
                <option value="calor_total" selected>Calor total (Densidad agregada)</option>
                <option value="calor_categoria">Calor por categoría</option>
                <option value="marcadores">Marcadores o agrupaciones</option>
                <option value="areas">Áreas por indicador (Tabla zonal)</option>
            </select>
        </div>

        <!-- Control Colorear por -->
        <div class="filtro-card">
            <label for="colorear-por" class="filtro-label">Colorear por</label>
            <select id="colorear-por" class="filtro-select">
                <option value="grupo_formacion" selected>Grupo de formación (FID / PPD)</option>
                <option value="tipo_ubicacion">Tipo de ubicación (Residencia / Trabajo)</option>
                <option value="situacion_laboral">Situación laboral (Trabaja / Busca / No trabaja)</option>
                <option value="estado_contacto">Estado de contacto (Efectivo / Sin intento)</option>
                <option value="docencia_eib">Docencia directa y EIB</option>
            </select>
        </div>

        <!-- Control Paleta de Intensidad -->
        <div class="filtro-card" id="wrapper-paleta">
            <label for="paleta" class="filtro-label">Paleta de Intensidad</label>
            <select id="paleta" class="filtro-select">
                <option value="termica" selected>Térmica clásica (Azul → Verde → Amarillo → Rojo)</option>
                <option value="cobalto">Cobalto (Azul cielo → Azul marino)</option>
                <option value="ambar">Ámbar (Amarillo dorado → Ámbar fuego)</option>
                <option value="viridis">Viridis (Púrpura → Turquesa → Amarillo)</option>
            </select>
        </div>

        <!-- Control Cobertura de Radio -->
        <div class="filtro-card" id="wrapper-radio-calor">
            <label for="radio-calor" class="filtro-label">Radio de Cobertura</label>
            <select id="radio-calor" class="filtro-select">
                <option value="55" selected>Alta cobertura (55px · Equilibrado)</option>
                <option value="72">Máxima cobertura regional (72px)</option>
                <option value="42">Focalizado por distrito (42px)</option>
            </select>
        </div>

        <!-- Control Opacidad -->
        <div class="filtro-card" id="wrapper-opacidad-calor">
            <label for="opacidad-calor" class="filtro-label">Intensidad Visual</label>
            <select id="opacidad-calor" class="filtro-select">
                <option value="0.75" selected>Sólida y notoria (Opacidad 75%)</option>
                <option value="0.88">Máxima saturación (Opacidad 88%)</option>
                <option value="0.60">Equilibrada (Opacidad 60%)</option>
            </select>
        </div>

        <!-- Subcategoría activa para Calor por Categoría -->
        <div class="filtro-card" id="wrapper-subcategoria" style="display:none;">
            <label for="subcategoria-calor" class="filtro-label">Categoría a Focalizar</label>
            <select id="subcategoria-calor" class="filtro-select">
                <option value="FID">FID (Formación Inicial Docente)</option>
                <option value="PPD">PPD (Profesionalización Docente)</option>
            </select>
        </div>
    </div>

    <!-- Banner Sin Ubicación Verificable (Fuera del mapa) -->
    <div id="sin-ubicacion-card" class="sin-ubicacion-banner" style="display:none;">
        <div>
            <div style="font-weight:800;color:#92400E;font-size:0.95rem;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-map-marker-slash"></i>
                <span>Sin ubicación verificable: <strong id="sin-ubicacion-total">0</strong> personas</span>
            </div>
            <div style="font-size:0.85rem;color:#78350F;margin-top:4px;">
                Registros del padrón con coordenadas pendientes de precisar o en trámite de validación institucional.
                <span id="sin-ubicacion-desglose" style="font-weight:600;margin-left:6px;"></span>
            </div>
        </div>
        <div>
            <a id="btn-ver-padron-sin-ubicacion" href="#" class="button secondary" style="background:#fff;border:1px solid #D97706;color:#92400E;font-weight:700;padding:6px 14px;border-radius:6px;">
                <i class="fas fa-list-ul"></i> Ver en padrón (Sin ubicación)
            </a>
        </div>
    </div>

    <!-- Estado de Carga / Filtro -->
    <p id="estado" role="status" style="font-size:0.86rem;color:#4A5568;margin-bottom:12px;background:#F1F5F9;padding:10px 14px;border-radius:8px;border:1px solid #CBD5E0;"></p>

    <!-- Leyendas Dinámicas -->
    <div id="contenedor-leyendas" style="margin-bottom:12px;">
        <!-- Leyenda Calor Total -->
        <div id="leyenda-calor" class="d-flex flex-wrap align-items-center" style="gap:14px;font-size:0.88rem;background:#F8FAFC;padding:10px 16px;border-radius:10px;border:1px solid #CBD5E0;">
            <span style="font-weight:700;color:#1A202C;">🔥 Concentración de calor territorial:</span>
            <div id="barra-gradiente" style="height:14px;width:220px;border-radius:7px;border:1px solid #A0AEC0;"></div>
            <span id="texto-escala-calor" style="font-size:0.84rem;color:#4A5568;">Baja (1-2) → Moderada (5-10) → Alta concentración (15-50+ egresados)</span>
            <small style="margin-left:auto;color:#718096;font-style:italic;">* Haz clic en cualquier núcleo para consultar la localidad y acceder a «Ver en padrón»</small>
        </div>

        <!-- Leyenda Categórica (Marcadores y Calor por Categoría) -->
        <div id="leyenda-categorica" class="d-flex flex-wrap align-items-center" style="display:none;gap:12px;background:#F8FAFC;padding:10px 16px;border-radius:10px;border:1px solid #CBD5E0;">
            <span style="font-weight:700;color:#1A202C;" id="leyenda-cat-titulo">Categorías:</span>
            <div id="leyenda-cat-items" class="d-flex flex-wrap" style="gap:8px;"></div>
            <small style="margin-left:auto;color:#718096;">* Haz clic en los puntos para ver el desglose exacto y enlace al padrón</small>
        </div>
    </div>

    <!-- Contenedor del Mapa -->
    <div id="mapa" style="height:580px;min-height:520px;width:100%;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,0.08);border:1px solid #CBD5E0;"></div>
</section>

<!-- Resumen Territorial por Localidad / Distrito -->
<section style="margin-top:24px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <h2 style="margin-bottom:0;">Resumen territorial por localidad / distrito</h2>
        <span class="badge badge-light border" style="font-size:0.82rem;">Protección estadística: Celdas &lt; 5 casos protegidas en pantalla nominal</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0" id="tabla-puntos-territorio">
            <thead style="background:#F8FAFC;">
                <tr>
                    <th>Tipo</th>
                    <th>Localidad / Distrito</th>
                    <th>Provincia</th>
                    <th>Total egresados</th>
                    <th>FID</th>
                    <th>PPD</th>
                    <th>Sin contacto efectivo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody-puntos-territorio">
                <tr><td colspan="8">Cargando datos territoriales…</td></tr>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h2>Vínculo comunitario y necesidades</h2>
    <div id="indicadores" class="grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:12px;margin-bottom:12px;"></div>
    <p style="font-size:0.85rem;color:#718096;">Indicadores declarados y registrados en la base institucional. La cobertura corresponde a perfiles integrados de egresados.</p>
</section>
</div></div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
const map = L.map('mapa').setView([-13.52, -71.97], 8);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 18
}).addTo(map);

let data = { puntos: [], sin_ubicacion: {}, indicadores: {} };
let layer = L.layerGroup().addTo(map);
let currentRequestId = 0;

// Paletas de calor configurables
const PALETAS = {
    termica: {
        gradient: {
            0.15: '#1e40af',
            0.32: '#0284c7',
            0.50: '#059669',
            0.68: '#eab308',
            0.82: '#f97316',
            1.00: '#dc2626'
        },
        css: 'linear-gradient(to right, #1e40af, #0284c7, #059669, #eab308, #f97316, #dc2626)'
    },
    cobalto: {
        gradient: {
            0.15: '#93c5fd',
            0.40: '#60a5fa',
            0.65: '#2563eb',
            0.85: '#1d4ed8',
            1.00: '#1e3a8a'
        },
        css: 'linear-gradient(to right, #93c5fd, #60a5fa, #2563eb, #1d4ed8, #1e3a8a)'
    },
    ambar: {
        gradient: {
            0.15: '#fef08a',
            0.40: '#fde047',
            0.65: '#eab308',
            0.85: '#d97706',
            1.00: '#92400e'
        },
        css: 'linear-gradient(to right, #fef08a, #fde047, #eab308, #d97706, #92400e)'
    },
    viridis: {
        gradient: {
            0.15: '#440154',
            0.40: '#3b528b',
            0.65: '#21918c',
            0.85: '#5ec962',
            1.00: '#fde725'
        },
        css: 'linear-gradient(to right, #440154, #3b528b, #21918c, #5ec962, #fde725)'
    }
};

// Esquemas de colores categóricos
const COLORES_CATEGORIAS = {
    grupo_formacion: {
        titulo: 'Grupo de formación',
        categorias: [
            { id: 'FID', label: 'FID (Cobalto)', color: '#2563eb' },
            { id: 'PPD', label: 'PPD (Fucsia)', color: '#d946ef' },
            { id: 'POR_VERIFICAR', label: 'Por verificar (Gris)', color: '#94a3b8' }
        ]
    },
    tipo_ubicacion: {
        titulo: 'Tipo de ubicación',
        categorias: [
            { id: 'residencia', label: 'Lugar de residencia (Cobalto)', color: '#2563eb' },
            { id: 'trabajo', label: 'Lugar de trabajo (Naranja)', color: '#ea580c' }
        ]
    },
    situacion_laboral: {
        titulo: 'Situación laboral',
        categorias: [
            { id: 'trabaja', label: 'Trabaja (Verde)', color: '#16a34a' },
            { id: 'busca_trabajo', label: 'Busca trabajo (Ámbar)', color: '#d97706' },
            { id: 'no_trabaja', label: 'No trabaja (Violeta)', color: '#7c3aed' },
            { id: 'sin_dato', label: 'Sin dato (Gris)', color: '#94a3b8' }
        ]
    },
    estado_contacto: {
        titulo: 'Estado de contacto',
        categorias: [
            { id: 'contacto_efectivo', label: 'Efectivo (Verde azulado)', color: '#0d9488' },
            { id: 'sin_intento', label: 'Sin intento (Rojo oscuro)', color: '#991b1b' },
            { id: 'intento_sin_respuesta', label: 'Intento sin respuesta (Ámbar)', color: '#d97706' }
        ]
    },
    docencia_eib: {
        titulo: 'Docencia directa / EIB',
        categorias: [
            { id: 'docencia_directa', label: 'Docencia directa (Azul)', color: '#1d4ed8' },
            { id: 'eib', label: 'Enfoque EIB (Verde)', color: '#059669' },
            { id: 'no_docencia', label: 'Otras áreas (Púrpura)', color: '#7c3aed' }
        ]
    }
};

function actualizarLeyenda() {
    const vista = document.getElementById('vista').value;
    const colorearPor = document.getElementById('colorear-por').value;
    const paletaKey = document.getElementById('paleta').value;
    const leyCalor = document.getElementById('leyenda-calor');
    const leyCat = document.getElementById('leyenda-categorica');
    const barra = document.getElementById('barra-gradiente');
    const wrapSubcat = document.getElementById('wrapper-subcategoria');

    if (barra && PALETAS[paletaKey]) {
        barra.style.background = PALETAS[paletaKey].css;
    }

    if (vista === 'calor_total') {
        leyCalor.style.display = 'flex';
        leyCat.style.display = 'none';
        wrapSubcat.style.display = 'none';
    } else if (vista === 'calor_categoria') {
        leyCalor.style.display = 'flex';
        leyCat.style.display = 'none';
        wrapSubcat.style.display = 'block';

        // Llenar opciones de subcategoría según lo seleccionado en colorearPor
        const subSelect = document.getElementById('subcategoria-calor');
        const config = COLORES_CATEGORIAS[colorearPor];
        if (config && subSelect) {
            subSelect.innerHTML = config.categorias.map(c => `<option value="${c.id}">${c.label}</option>`).join('');
        }
    } else if (vista === 'marcadores') {
        leyCalor.style.display = 'none';
        leyCat.style.display = 'flex';
        wrapSubcat.style.display = 'none';

        const config = COLORES_CATEGORIAS[colorearPor];
        if (config) {
            document.getElementById('leyenda-cat-titulo').textContent = `${config.titulo}:`;
            document.getElementById('leyenda-cat-items').innerHTML = config.categorias.map(c => `
                <span class="legend-chip">
                    <span class="legend-dot" style="background:${c.color};"></span>
                    ${c.label}
                </span>
            `).join('');
        }
    } else { // Areas / tabla
        leyCalor.style.display = 'none';
        leyCat.style.display = 'flex';
        wrapSubcat.style.display = 'none';
        document.getElementById('leyenda-cat-titulo').textContent = 'Vista Zonal:';
        document.getElementById('leyenda-cat-items').innerHTML = '<span class="legend-chip"><i class="fas fa-table mr-1"></i> Consulta agregada por localidad y distrito en la tabla inferior</span>';
    }
}

function resolverColorPunto(p, dimension) {
    if (dimension === 'tipo_ubicacion') {
        return p.tipo === 'residencia' ? '#2563eb' : '#ea580c';
    }
    if (dimension === 'grupo_formacion') {
        const fid = p.por_grupo?.FID ?? 0;
        const ppd = p.por_grupo?.PPD ?? 0;
        if (fid > 0 && ppd === 0) return '#2563eb';
        if (ppd > 0 && fid === 0) return '#d946ef';
        return '#0284c7'; // Mixto
    }
    if (dimension === 'situacion_laboral') {
        const sl = p.por_situacion_laboral || {};
        if ((sl.trabaja ?? 0) > 0) return '#16a34a';
        if ((sl.busca_trabajo ?? 0) > 0) return '#d97706';
        if ((sl.no_trabaja ?? 0) > 0) return '#7c3aed';
        return '#94a3b8';
    }
    if (dimension === 'estado_contacto') {
        const c = p.por_contacto || {};
        if ((c.contacto_efectivo ?? 0) > 0) return '#0d9488';
        if ((c.intento_sin_respuesta ?? 0) > 0) return '#d97706';
        if ((c.sin_intento ?? 0) > 0) return '#991b1b';
        return '#64748b';
    }
    if (dimension === 'docencia_eib') {
        const d = p.por_docencia_eib || {};
        if ((d.eib ?? 0) > 0) return '#059669';
        if ((d.docencia_directa ?? 0) > 0) return '#1d4ed8';
        return '#7c3aed';
    }
    return '#2563eb';
}

function resolverValorSubcategoria(p, dimension, subcatId) {
    if (dimension === 'grupo_formacion') {
        return p.por_grupo?.[subcatId] ?? 0;
    }
    if (dimension === 'tipo_ubicacion') {
        return p.tipo === subcatId ? p.n : 0;
    }
    if (dimension === 'situacion_laboral') {
        return p.por_situacion_laboral?.[subcatId] ?? 0;
    }
    if (dimension === 'estado_contacto') {
        return p.por_contacto?.[subcatId] ?? 0;
    }
    if (dimension === 'docencia_eib') {
        return p.por_docencia_eib?.[subcatId] ?? 0;
    }
    return p.n;
}

function construirPopupHtml(p, estadoContacto, grupoFormacion) {
    const fid = p.por_grupo?.FID ?? 0;
    const ppd = p.por_grupo?.PPD ?? 0;
    const sinContacto = p.por_contacto?.sin_contacto_efectivo ?? 0;
    const efectivo = p.por_contacto?.contacto_efectivo ?? 0;
    const badgeTipo = p.tipo === 'trabajo'
        ? '<span class="badge badge-warning" style="background:#ea580c;color:#fff;">Trabajo</span>'
        : '<span class="badge badge-primary" style="background:#2563eb;color:#fff;">Residencia</span>';

    const urlPadron = `{{ route('seguimiento.campanas.gestion') }}?distrito=${encodeURIComponent(p.distrito || p.nombre)}&provincia=${encodeURIComponent(p.provincia || '')}&departamento=${encodeURIComponent(p.departamento || '')}&estado_contacto=${encodeURIComponent(estadoContacto)}&grupo_formacion=${encodeURIComponent(grupoFormacion)}&tipo_ubicacion=${encodeURIComponent(p.tipo)}`;

    return `
        <div style="font-family:system-ui,-apple-system,sans-serif;font-size:13px;line-height:1.4;min-width:210px;">
            <div style="font-size:11px;margin-bottom:4px;">${badgeTipo}</div>
            <strong style="font-size:14px;color:#1e293b;display:block;">${p.nombre}</strong>
            <div style="color:#64748b;font-size:11px;margin-bottom:8px;">${p.distrito || ''} · ${p.provincia || ''} (${p.departamento || ''})</div>
            
            <div style="background:#f8fafc;padding:6px 8px;border-radius:6px;border:1px solid #e2e8f0;margin-bottom:8px;">
                <div>Total egresados: <strong>${p.suprimido ? '<5 (Protegido)' : p.n}</strong></div>
                <div style="font-size:11px;color:#475569;">FID: <strong>${fid}</strong> · PPD: <strong>${ppd}</strong></div>
                <div style="font-size:11px;color:#0d9488;">Contacto efectivo: <strong>${efectivo}</strong></div>
                <div style="font-size:11px;color:#dc2626;">Sin contacto efectivo: <strong>${sinContacto}</strong></div>
            </div>

            <a href="${urlPadron}" class="button" style="display:block;text-align:center;padding:6px 12px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none;font-weight:700;font-size:12px;">
                <i class="fas fa-list-ul mr-1"></i> Ver en padrón (${p.distrito || p.nombre}) →
            </a>
        </div>
    `;
}

function draw() {
    map.invalidateSize();
    layer.clearLayers();
    actualizarLeyenda();

    const capa = document.getElementById('capa').value;
    const vista = document.getElementById('vista').value;
    const colorearPor = document.getElementById('colorear-por').value;
    const paletaKey = document.getElementById('paleta').value;
    const estadoContacto = document.getElementById('estado-contacto').value;
    const grupoFormacion = document.getElementById('grupo-formacion-global')?.value || 'FID';

    const rawPoints = (data.puntos || []).filter(p => capa === 'ambos' || p.tipo === capa);

    // Actualizar tabla resumen
    const tbody = document.getElementById('tbody-puntos-territorio');
    if (tbody) {
        const sorted = [...rawPoints].sort((a, b) => b.n - a.n);
        if (!sorted.length) {
            tbody.innerHTML = '<tr><td colspan="8">No hay puntos georreferenciados para los filtros seleccionados.</td></tr>';
        } else {
            tbody.innerHTML = sorted.map(p => {
                const fid = p.por_grupo?.FID ?? 0;
                const ppd = p.por_grupo?.PPD ?? 0;
                const sinContacto = p.por_contacto?.sin_contacto_efectivo ?? 0;
                const badge = p.tipo === 'trabajo'
                    ? '<span class="badge" style="background:#ea580c;color:#fff;padding:2px 8px;border-radius:4px;">Trabajo</span>'
                    : '<span class="badge" style="background:#2563eb;color:#fff;padding:2px 8px;border-radius:4px;">Residencia</span>';
                const urlPadronRow = `{{ route('seguimiento.campanas.gestion') }}?distrito=${encodeURIComponent(p.distrito || p.nombre)}&estado_contacto=${encodeURIComponent(estadoContacto)}&grupo_formacion=${encodeURIComponent(grupoFormacion)}&tipo_ubicacion=${encodeURIComponent(p.tipo)}`;
                const totalText = p.suprimido ? '<span title="Protección de celda pequeña (<5)">&lt;5</span>' : p.n;
                return `
                    <tr>
                        <td>${badge}</td>
                        <td><strong>${p.nombre}</strong></td>
                        <td>${p.provincia || 'Cusco'}</td>
                        <td><strong>${totalText}</strong></td>
                        <td>${fid}</td>
                        <td>${ppd}</td>
                        <td><span style="color:#dc2626;font-weight:700;">${sinContacto}</span></td>
                        <td><a href="${urlPadronRow}" class="button secondary" style="padding:2px 8px;font-size:11px;">Ver en padrón</a></td>
                    </tr>
                `;
            }).join('');
        }
    }

    if (!rawPoints.length) return;

    if (vista === 'calor_total') {
        const radiusVal = Number(document.getElementById('radio-calor')?.value || 55);
        const opacityVal = Number(document.getElementById('opacidad-calor')?.value || 0.75);
        const maxN = Math.max(1, ...rawPoints.map(p => p.n));

        const heatPoints = rawPoints.map(p => {
            const lat = Number(p.latitud);
            const lng = Number(p.longitud);
            const ratio = Math.log10(p.n + 1) / Math.log10(maxN + 1);
            const intensidad = Math.max(0.48, Math.min(1.0, 0.45 + (ratio * 0.55)));
            return [lat, lng, intensidad];
        });

        const selectedPaleta = PALETAS[paletaKey] || PALETAS.termica;

        L.heatLayer(heatPoints, {
            radius: radiusVal,
            blur: Math.round(radiusVal * 0.46),
            maxZoom: 11,
            max: 1.0,
            minOpacity: opacityVal,
            gradient: selectedPaleta.gradient
        }).addTo(layer);

        // Círculos clicables para consultar localidad exacta
        rawPoints.forEach(p => {
            const lat = Number(p.latitud);
            const lng = Number(p.longitud);
            const marker = L.circleMarker([lat, lng], {
                radius: Math.min(8, 3.5 + Math.sqrt(p.n)),
                color: '#ffffff',
                fillColor: p.tipo === 'residencia' ? '#1e3a8a' : '#ea580c',
                weight: 1.5,
                fillOpacity: 0.90
            }).addTo(layer);

            marker.bindPopup(construirPopupHtml(p, estadoContacto, grupoFormacion));
        });

    } else if (vista === 'calor_categoria') {
        const radiusVal = Number(document.getElementById('radio-calor')?.value || 55);
        const opacityVal = Number(document.getElementById('opacidad-calor')?.value || 0.75);
        const subcatId = document.getElementById('subcategoria-calor')?.value || 'FID';

        const subPoints = rawPoints.filter(p => resolverValorSubcategoria(p, colorearPor, subcatId) > 0);
        const maxSubN = Math.max(1, ...subPoints.map(p => resolverValorSubcategoria(p, colorearPor, subcatId)));

        const heatPoints = subPoints.map(p => {
            const val = resolverValorSubcategoria(p, colorearPor, subcatId);
            const lat = Number(p.latitud);
            const lng = Number(p.longitud);
            const ratio = Math.log10(val + 1) / Math.log10(maxSubN + 1);
            const intensidad = Math.max(0.48, Math.min(1.0, 0.45 + (ratio * 0.55)));
            return [lat, lng, intensidad];
        });

        const selectedPaleta = PALETAS[paletaKey] || PALETAS.termica;

        if (heatPoints.length) {
            L.heatLayer(heatPoints, {
                radius: radiusVal,
                blur: Math.round(radiusVal * 0.46),
                maxZoom: 11,
                max: 1.0,
                minOpacity: opacityVal,
                gradient: selectedPaleta.gradient
            }).addTo(layer);
        }

        subPoints.forEach(p => {
            const lat = Number(p.latitud);
            const lng = Number(p.longitud);
            const marker = L.circleMarker([lat, lng], {
                radius: 6,
                color: '#ffffff',
                fillColor: resolverColorPunto(p, colorearPor),
                weight: 1.5,
                fillOpacity: 0.90
            }).addTo(layer);

            marker.bindPopup(construirPopupHtml(p, estadoContacto, grupoFormacion));
        });

    } else { // Marcadores o agrupaciones y áreas
        const seenCoords = {};
        rawPoints.forEach(p => {
            let lat = Number(p.latitud);
            let lng = Number(p.longitud);
            const coordKey = lat.toFixed(4) + ':' + lng.toFixed(4);
            if (seenCoords[coordKey]) {
                lat += (p.tipo === 'trabajo' ? 0.005 : -0.005);
                lng += (p.tipo === 'trabajo' ? 0.005 : -0.005);
            }
            seenCoords[coordKey] = true;

            const colorPunto = resolverColorPunto(p, colorearPor);

            const marker = L.circleMarker([lat, lng], {
                radius: Math.min(26, 7 + Math.sqrt(p.n) * 2.2),
                color: colorPunto,
                fillColor: colorPunto,
                weight: 2,
                fillOpacity: 0.82
            }).addTo(layer);

            marker.bindPopup(construirPopupHtml(p, estadoContacto, grupoFormacion));
        });
    }

    if (rawPoints.length) {
        map.fitBounds(rawPoints.map(p => [Number(p.latitud), Number(p.longitud)]), {maxZoom: 11, padding: [30, 30]});
    }
}

async function refresh() {
    const thisRequestId = ++currentRequestId;
    const grupoFormacion = document.getElementById('grupo-formacion-global')?.value || 'FID';
    const estadoContacto = document.getElementById('estado-contacto').value;
    const tipoUbicacion = document.getElementById('capa').value;

    const url = new URL(@json(route('seguimiento.observatorio-conectado.datos')), window.location.origin);
    url.searchParams.set('grupo_formacion', grupoFormacion);
    url.searchParams.set('estado_contacto', estadoContacto);
    url.searchParams.set('tipo_ubicacion', tipoUbicacion);

    document.getElementById('estado').textContent = 'Consultando observatorio territorial y cobertura...';

    try {
        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('Error al consultar datos');

        const json = await res.json();
        if (thisRequestId !== currentRequestId) return; // Descartar respuestas de consultas anteriores

        data = json;

        // Estado general
        document.getElementById('estado').textContent = `${data.grupo_formacion} · ${data.cobertura} perfiles integrados · ${data.puntos.length} puntos territoriales · Actualizado: ${new Date(data.actualizado_at).toLocaleTimeString('es-PE')} · Pendientes de precisar: ${data.pendientes_coordenadas.residencia} residencias y ${data.pendientes_coordenadas.trabajo} empleos.`;

        // Card Sin Ubicación Verificable
        const sinUbicacionCard = document.getElementById('sin-ubicacion-card');
        const sinU = data.sin_ubicacion || {};
        if (sinU.total > 0) {
            sinUbicacionCard.style.display = 'flex';
            document.getElementById('sin-ubicacion-total').textContent = sinU.total;
            document.getElementById('sin-ubicacion-desglose').textContent = `(FID: ${sinU.por_grupo?.FID ?? 0} · PPD: ${sinU.por_grupo?.PPD ?? 0} · Sin contacto: ${sinU.por_contacto?.sin_contacto_efectivo ?? 0})`;
            document.getElementById('btn-ver-padron-sin-ubicacion').href = `{{ route('seguimiento.campanas.gestion') }}?sin_ubicacion=1&estado_contacto=${encodeURIComponent(estadoContacto)}&grupo_formacion=${encodeURIComponent(grupoFormacion)}`;
        } else {
            sinUbicacionCard.style.display = 'none';
        }

        // Indicadores comunitarios
        const labels = {
            residencia_comunidad: 'Residen en comunidad',
            trabajo_comunidad: 'Trabajan en comunidad',
            contribucion: 'Declaran colaborar con comunidad'
        };
        const indContainer = document.getElementById('indicadores');
        indContainer.innerHTML = Object.entries(data.indicadores || {}).map(([key, val]) => `
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;">
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">${labels[key] || key}</div>
                <div style="font-size:22px;font-weight:800;color:#1e3a8a;margin-top:2px;">${val.valor}</div>
                ${val.suprimido ? '<small style="color:#d97706;font-size:10px;">(Celda protegida &lt;5)</small>' : ''}
            </div>
        `).join('');

        draw();
    } catch (err) {
        if (thisRequestId === currentRequestId) {
            document.getElementById('estado').textContent = 'No se pudo actualizar el visor territorial. Comprueba la conexión e intenta de nuevo.';
        }
    }
}

window.addEventListener('resize', () => map.invalidateSize());
setTimeout(() => map.invalidateSize(), 250);

document.getElementById('capa').onchange = refresh;
document.getElementById('estado-contacto').onchange = refresh;
document.getElementById('vista').onchange = draw;
document.getElementById('colorear-por').onchange = draw;
document.getElementById('paleta').onchange = draw;
document.getElementById('radio-calor').onchange = draw;
document.getElementById('opacidad-calor').onchange = draw;
document.getElementById('subcategoria-calor').onchange = draw;
document.getElementById('actualizar').onclick = refresh;

const globalSelect = document.getElementById('grupo-formacion-global');
if (globalSelect) {
    globalSelect.addEventListener('change', refresh);
}

refresh();
</script>
@endpush
