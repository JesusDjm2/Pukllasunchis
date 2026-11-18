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
<section><h1>Observatorio territorial conectado</h1><p>Residencia y empleo (Encuesta 2026-I e historial institucional) se muestran por localidad o distrito oficial del catálogo, nunca domicilios individuales.</p><div class="grid"><div><label for="capa">Ubicación</label><select id="capa"><option value="ambos" selected>Trabajo y Residencia (Ambos)</option><option value="trabajo">Trabajo (Encuesta 2026-I)</option><option value="residencia">Residencia</option></select></div><div><label for="vista">Vista</label><select id="vista"><option value="puntos">Geolocalización agrupada</option><option value="calor">Concentración (calor)</option></select></div></div><button id="actualizar">Actualizar</button><p id="estado" role="status"></p><div id="mapa" style="height:560px;border-radius:16px;margin-top:12px;"></div></section><section><h2>Vínculo comunitario</h2><div id="indicadores"></div><p>Indicadores declarados; no prueban por sí solos que la formación haya causado un retorno. La cobertura corresponde a perfiles integrados de egresados.</p></section><section><h2>Encuentros</h2><p>Las preferencias voluntarias se conservan en el perfil. La recomendación automática por tiempos de viaje requiere un catálogo de sedes y un servicio de rutas validado; este mapa no estima accesibilidad mediante distancias en línea recta.</p></section></div></div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
const map=L.map('mapa').setView([-13.52,-71.97],7);L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap contributors'}).addTo(map);
let data={puntos:[]}, layer=L.layerGroup().addTo(map);
function draw(){layer.clearLayers();const capa=document.getElementById('capa').value;const points=data.puntos.filter(p=>capa==='ambos'||p.tipo===capa);if(document.getElementById('vista').value==='calor'){L.heatLayer(points.map(p=>[Number(p.latitud),Number(p.longitud),p.n]),{radius:30,max:Math.max(5,...points.map(p=>p.n))}).addTo(layer);}else{points.forEach(p=>{const text=document.createElement('span');text.textContent=`[${p.tipo==='trabajo'?'Trabajo':'Residencia'}] ${p.nombre}: ${p.n} egresados únicos · FID: ${p.por_grupo?.FID ?? 'Protegido'} · PPD: ${p.por_grupo?.PPD ?? 'Protegido'}.`;L.circleMarker([Number(p.latitud),Number(p.longitud)],{radius:Math.min(25,7+Math.sqrt(p.n)*2),color:p.tipo==='residencia'?'#224db5':'#bf7b16',fillOpacity:0.75}).bindPopup(text).addTo(layer);});}if(points.length)map.fitBounds(points.map(p=>[Number(p.latitud),Number(p.longitud)]),{maxZoom:12,padding:[25,25]});}
async function refresh(){try{const res=await fetch(@json(route('seguimiento.observatorio-conectado.datos')).concat('?grupo_formacion=',encodeURIComponent(document.getElementById('grupo-formacion-global').value)),{headers:{Accept:'application/json'}});if(!res.ok)throw Error();data=await res.json();document.getElementById('estado').textContent=`${data.grupo_formacion} · ${data.cobertura} perfiles confirmados · Actualizado: ${new Date(data.actualizado_at).toLocaleString('es-PE')} · Sin coordenadas verificadas: ${data.pendientes_coordenadas.residencia} residencias y ${data.pendientes_coordenadas.trabajo} empleos. Las agrupaciones pequeñas no aparecen.`;const labels={residencia_comunidad:'Residen en comunidad',trabajo_comunidad:'Trabajan en comunidad',contribucion:'Declaran colaborar con comunidad'};document.getElementById('indicadores').replaceChildren(...Object.entries(data.indicadores).map(([key,value])=>{const p=document.createElement('p');p.textContent=`${labels[key]}: ${value.valor}`;return p;}));draw();}catch{document.getElementById('estado').textContent='No se pudo actualizar. Comprueba la conexión y vuelve a intentar.';}}
document.getElementById('capa').onchange=draw;document.getElementById('vista').onchange=draw;document.getElementById('actualizar').onclick=refresh;refresh();setInterval(()=>{if(!document.hidden)refresh();},60000);
</script>
@endpush
