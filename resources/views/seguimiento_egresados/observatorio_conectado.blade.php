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
    <div class="grid">
        <div>
            <label for="capa">Ubicación</label>
            <select id="capa">
                <option value="ambos" selected>Trabajo y Residencia (Ambos)</option>
                <option value="trabajo">Trabajo (Encuesta 2026-I y BD Institucional)</option>
                <option value="residencia">Residencia (BD Institucional y Confirmaciones)</option>
            </select>
        </div>
        <div>
            <label for="vista">Vista</label>
            <select id="vista">
                <option value="puntos">Geolocalización agrupada</option>
                <option value="calor">Concentración (calor)</option>
            </select>
        </div>
    </div>
    <button id="actualizar" type="button">Actualizar mapa</button>
    <p id="estado" role="status"></p>
    <div class="d-flex flex-wrap align-items-center mb-2" style="gap:16px;font-size:0.88rem;">
        <span><strong style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#bf7b16;margin-right:5px;"></strong> Lugar de trabajo</span>
        <span><strong style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#224db5;margin-right:5px;"></strong> Lugar de residencia</span>
        <small>Haz clic en cualquier círculo para ver el detalle exacto de egresados FID y PPD.</small>
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

    if (document.getElementById('vista').value === 'calor') {
        L.heatLayer(
            rawPoints.map(p => [Number(p.latitud), Number(p.longitud), p.n]),
            {radius: 30, max: Math.max(5, ...rawPoints.map(p => p.n))}
        ).addTo(layer);
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
document.getElementById('actualizar').onclick = refresh;
refresh();
setInterval(() => { if (!document.hidden) refresh(); }, 60000);
</script>
@endpush
