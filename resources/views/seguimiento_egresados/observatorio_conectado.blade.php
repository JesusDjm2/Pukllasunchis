@extends('layouts.seguimiento')
@section('titulo','Visor territorial conectado · Seguimiento de Egresados')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="{{ asset('css/seguimiento-egresados.css') }}?v={{ filemtime(public_path('css/seguimiento-egresados.css')) }}">
@endpush
@section('contenido')
<div class="seguimiento-panel">
<div class="seg-contenedor">
@include('seguimiento_egresados.partials.grupo_formacion')
<div class="d-flex flex-wrap align-items-center mb-2" style="gap:10px;">
    <a class="button secondary" href="{{ route('seguimiento.panel.coordinacion', ['grupo_formacion' => $grupoFormacion]) }}">← Coordinación de egresados</a>
    <a class="button secondary" href="{{ route('seguimiento.laboratorio', ['grupo_formacion' => $grupoFormacion]) }}">Cruces y exportaciones</a>
</div>
<section>
    <h1>Observatorio territorial conectado</h1>
    <p>Residencia y empleo (Base institucional, Encuesta 2026-I e historial longitudinal) se muestran por localidad o distrito oficial del catálogo, incluyendo los conteos exactos de cada ubicación (también aquellos menores a 5).</p>
    <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;margin-bottom:12px;">
        <div>
            <label for="capa" style="font-weight:600;font-size:0.88rem;color:#2D3748;margin-bottom:4px;display:block;">Ubicación</label>
            <select id="capa" style="width:100%;padding:8px 12px;border:1px solid #CBD5E0;border-radius:8px;background:#fff;">
                <option value="ambos" selected>Trabajo y Residencia (Ambos)</option>
                <option value="trabajo">Trabajo (Encuesta 2026-I y BD Institucional)</option>
                <option value="residencia">Residencia (BD Institucional y Confirmaciones)</option>
            </select>
        </div>
        <div>
            <label for="vista" style="font-weight:600;font-size:0.88rem;color:#2D3748;margin-bottom:4px;display:block;">Tipo de Vista</label>
            <select id="vista" style="width:100%;padding:8px 12px;border:1px solid #CBD5E0;border-radius:8px;background:#fff;">
                <option value="calor" selected>Concentración de calor (Mapa térmico territorial)</option>
                <option value="puntos">Geolocalización agrupada (Círculos por localidad)</option>
            </select>
        </div>
        <div id="wrapper-radio-calor">
            <label for="radio-calor" style="font-weight:600;font-size:0.88rem;color:#2D3748;margin-bottom:4px;display:block;">Cobertura de Calor</label>
            <select id="radio-calor" style="width:100%;padding:8px 12px;border:1px solid #CBD5E0;border-radius:8px;background:#fff;">
                <option value="55" selected>Alta cobertura territorial (55px · Recomendado)</option>
                <option value="72">Máxima cobertura regional (72px)</option>
                <option value="42">Focalizada por distrito (42px)</option>
            </select>
        </div>
        <div id="wrapper-opacidad-calor">
            <label for="opacidad-calor" style="font-weight:600;font-size:0.88rem;color:#2D3748;margin-bottom:4px;display:block;">Intensidad y Visibilidad</label>
            <select id="opacidad-calor" style="width:100%;padding:8px 12px;border:1px solid #CBD5E0;border-radius:8px;background:#fff;">
                <option value="0.75" selected>Muy notoria y sólida (Opacidad 75%)</option>
                <option value="0.88">Máxima saturación (Opacidad 88%)</option>
                <option value="0.60">Equilibrada (Opacidad 60%)</option>
            </select>
        </div>
    </div>
    <div style="margin-bottom:12px;">
        <button id="actualizar" type="button" class="btn btn-primary" style="padding:7px 18px;border-radius:8px;font-weight:600;">Actualizar mapa</button>
    </div>
    <p id="estado" role="status" style="font-size:0.85rem;color:#4A5568;margin-bottom:12px;background:#EDF2F7;padding:8px 12px;border-radius:6px;"></p>
    
    <!-- Leyenda Puntos -->
    <div id="leyenda-puntos" class="d-flex flex-wrap align-items-center mb-2" style="display:none;gap:16px;font-size:0.88rem;">
        <span><strong style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#bf7b16;margin-right:5px;"></strong> Lugar de trabajo</span>
        <span><strong style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#224db5;margin-right:5px;"></strong> Lugar de residencia</span>
        <small>Haz clic en cualquier círculo para ver el detalle exacto de egresados FID y PPD.</small>
    </div>

    <!-- Leyenda Calor (Muy notoria y visible) -->
    <div id="leyenda-calor" class="d-flex flex-wrap align-items-center mb-2" style="gap:14px;font-size:0.88rem;background:#F8FAFC;padding:10px 16px;border-radius:10px;border:1px solid #CBD5E0;">
        <span style="font-weight:700;color:#1A202C;">🔥 Concentración de calor territorial:</span>
        <div style="height:14px;width:200px;border-radius:7px;background:linear-gradient(to right, #1e40af, #0284c7, #059669, #eab308, #f97316, #dc2626);border:1px solid #A0AEC0;"></div>
        <span style="font-size:0.84rem;color:#4A5568;">Baja (1-2) → Moderada (5-10) → Alta concentración (15-50+ egresados)</span>
        <small style="margin-left:auto;color:#718096;font-style:italic;">* Haz clic en los núcleos para ver la localidad y conteos exactos</small>
    </div>
    <div id="mapa" style="height:560px;min-height:520px;width:100%;border-radius:16px;margin-top:8px;"></div>
</section>
<section>
    <h2>Resumen territorial por localidad / distrito</h2>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0" id="tabla-puntos-territorio">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Localidad / Distrito</th>
                    <th>Total egresados</th>
                    <th>FID</th>
                    <th>PPD</th>
                </tr>
            </thead>
            <tbody id="tbody-puntos-territorio">
                <tr><td colspan="5">Cargando datos territoriales…</td></tr>
            </tbody>
        </table>
    </div>
</section>
<section>
    <h2>Vínculo comunitario</h2>
    <div id="indicadores"></div>
    <p>Indicadores declarados y registrados en la base institucional. La cobertura corresponde a perfiles integrados de egresados.</p>
</section>
<section>
    <h2>Encuentros</h2>
    <p>Las preferencias voluntarias se conservan en el perfil. La recomendación automática por tiempos de viaje requiere un catálogo de sedes y un servicio de rutas validado; este mapa no estima accesibilidad mediante distancias en línea recta.</p>
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
let data = {puntos: []}, layer = L.layerGroup().addTo(map);

function draw() {
    map.invalidateSize();
    layer.clearLayers();
    const capa = document.getElementById('capa').value;
    const rawPoints = data.puntos.filter(p => capa === 'ambos' || p.tipo === capa);

    // Actualizar tabla resumen con todos los valores tal cual (incluidos < 5)
    const tbody = document.getElementById('tbody-puntos-territorio');
    if (tbody) {
        const sorted = [...rawPoints].sort((a, b) => b.n - a.n);
        if (!sorted.length) {
            tbody.innerHTML = '<tr><td colspan="5">No hay puntos georreferenciados para este filtro.</td></tr>';
        } else {
            tbody.innerHTML = sorted.map(p => {
                const fid = p.por_grupo?.FID ?? 0;
                const ppd = p.por_grupo?.PPD ?? 0;
                const badge = p.tipo === 'trabajo'
                    ? '<span class="badge badge-warning">Trabajo</span>'
                    : '<span class="badge badge-primary">Residencia</span>';
                return `<tr><td>${badge}</td><td><strong>${p.nombre}</strong></td><td><strong>${p.n}</strong></td><td>${fid}</td><td>${ppd}</td></tr>`;
            }).join('');
        }
    }

    const esCalor = document.getElementById('vista').value === 'calor';
    const leyPuntos = document.getElementById('leyenda-puntos');
    const leyCalor = document.getElementById('leyenda-calor');
    const wrapRadio = document.getElementById('wrapper-radio-calor');
    const wrapOpac = document.getElementById('wrapper-opacidad-calor');

    if (leyPuntos) leyPuntos.style.display = esCalor ? 'none' : 'flex';
    if (leyCalor) leyCalor.style.display = esCalor ? 'flex' : 'none';
    if (wrapRadio) wrapRadio.style.display = esCalor ? 'block' : 'none';
    if (wrapOpac) wrapOpac.style.display = esCalor ? 'block' : 'none';

    if (esCalor) {
        const radiusVal = Number(document.getElementById('radio-calor')?.value || 55);
        const opacityVal = Number(document.getElementById('opacidad-calor')?.value || 0.75);

        const maxN = Math.max(1, ...rawPoints.map(p => p.n));
        const heatPoints = rawPoints.map(p => {
            const lat = Number(p.latitud);
            const lng = Number(p.longitud);
            // Ponderación perceptiva calibrada: garantiza presencia territorial visible y notoria (base 0.48),
            // evitando desvanecerse en transparencia frente a grandes urbes.
            const ratio = Math.log10(p.n + 1) / Math.log10(maxN + 1);
            const intensidad = Math.max(0.48, Math.min(1.0, 0.45 + (ratio * 0.55)));
            return [lat, lng, intensidad];
        });

        // Capa de concentración de calor: colores más vivos, menor transparencia y mayor radio territorial
        L.heatLayer(heatPoints, {
            radius: radiusVal,
            blur: Math.round(radiusVal * 0.46),
            maxZoom: 11,
            max: 1.0,
            minOpacity: opacityVal,
            gradient: {
                0.15: '#1e40af', // Azul cobalto perimetral nítido y visible
                0.32: '#0284c7', // Azul cerúleo luminoso
                0.50: '#059669', // Verde esmeralda vivo
                0.68: '#eab308', // Amarillo oro cálido
                0.82: '#f97316', // Naranja fuego intenso
                1.00: '#dc2626'  // Rojo carmesí de máxima concentración
            }
        }).addTo(layer);

        // Puntos de referencia interactivos para consulta de la localidad exacta al hacer clic
        rawPoints.forEach(p => {
            const lat = Number(p.latitud);
            const lng = Number(p.longitud);
            const fid = p.por_grupo?.FID ?? 0;
            const ppd = p.por_grupo?.PPD ?? 0;
            const text = document.createElement('span');
            text.textContent = `[${p.tipo === 'trabajo' ? 'Trabajo' : 'Residencia'}] ${p.nombre}: ${p.n} egresado(s) · FID: ${fid} · PPD: ${ppd}.`;

            L.circleMarker([lat, lng], {
                radius: Math.min(8, 3.5 + Math.sqrt(p.n)),
                color: '#ffffff',
                fillColor: p.tipo === 'residencia' ? '#1e3a8a' : '#7c2d12',
                weight: 1.5,
                fillOpacity: 0.90
            }).bindPopup(text).addTo(layer);
        });
    } else {
        // Si en vista "ambos" coinciden trabajo y residencia en la misma localidad, desplazar ligeramente para que ambos círculos sean visibles y clicables
        const seenCoords = {};
        rawPoints.forEach(p => {
            let lat = Number(p.latitud);
            let lng = Number(p.longitud);
            const coordKey = lat.toFixed(4) + ':' + lng.toFixed(4);
            if (seenCoords[coordKey]) {
                lat += (p.tipo === 'trabajo' ? 0.0045 : -0.0045);
                lng += (p.tipo === 'trabajo' ? 0.0045 : -0.0045);
            }
            seenCoords[coordKey] = true;

            const fid = p.por_grupo?.FID ?? 0;
            const ppd = p.por_grupo?.PPD ?? 0;
            const text = document.createElement('span');
            text.textContent = `[${p.tipo === 'trabajo' ? 'Trabajo' : 'Residencia'}] ${p.nombre}: ${p.n} egresado(s) · FID: ${fid} · PPD: ${ppd}.`;
            L.circleMarker([lat, lng], {
                radius: Math.min(26, 7 + Math.sqrt(p.n) * 2.2),
                color: p.tipo === 'residencia' ? '#224db5' : '#bf7b16',
                fillColor: p.tipo === 'residencia' ? '#224db5' : '#bf7b16',
                weight: 2,
                fillOpacity: 0.78
            }).bindPopup(text).addTo(layer);
        });
    }
    if (rawPoints.length) {
        map.fitBounds(rawPoints.map(p => [Number(p.latitud), Number(p.longitud)]), {maxZoom: 11, padding: [30, 30]});
    }
}

async function refresh() {
    try {
        const res = await fetch(@json(route('seguimiento.observatorio-conectado.datos')).concat('?grupo_formacion=', encodeURIComponent(document.getElementById('grupo-formacion-global').value)), {headers: {Accept: 'application/json'}});
        if (!res.ok) throw Error();
        data = await res.json();
        document.getElementById('estado').textContent = `${data.grupo_formacion} · ${data.cobertura} perfiles integrados · ${data.puntos.length} puntos territoriales georreferenciados · Actualizado: ${new Date(data.actualizado_at).toLocaleString('es-PE')} · Pendientes de precisar distrito/localidad: ${data.pendientes_coordenadas.residencia} residencias y ${data.pendientes_coordenadas.trabajo} empleos.`;
        const labels = {residencia_comunidad: 'Residen en comunidad', trabajo_comunidad: 'Trabajan en comunidad', contribucion: 'Declaran colaborar con comunidad'};
        document.getElementById('indicadores').replaceChildren(...Object.entries(data.indicadores).map(([key, value]) => {
            const p = document.createElement('p');
            p.textContent = `${labels[key]}: ${value.valor}`;
            return p;
        }));
        draw();
    } catch {
        document.getElementById('estado').textContent = 'No se pudo actualizar. Comprueba la conexión y vuelve a intentar.';
    }
}

window.addEventListener('resize', () => map.invalidateSize());
setTimeout(() => map.invalidateSize(), 250);
document.getElementById('capa').onchange = draw;
document.getElementById('vista').onchange = draw;
document.getElementById('radio-calor').onchange = draw;
document.getElementById('opacidad-calor').onchange = draw;
document.getElementById('actualizar').onclick = refresh;
refresh();
setInterval(() => { if (!document.hidden) refresh(); }, 60000);
</script>
@endpush
