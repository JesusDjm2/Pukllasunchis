/* Perfil privado: borradores solo en servidor; no guarda datos personales en localStorage. */
(() => {
'use strict';
const cfg = window.PUKLLA_PERFIL;
let profile = structuredClone(cfg.datos || {});
const form = document.getElementById('perfil-form');
const escape = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const get = (o, p) => p.split('.').reduce((v,k) => v?.[k], o);
function set(o,p,v) {const keys=p.split('.'); const last=keys.pop(); let target=o; for (let i=0;i<keys.length;i++) {const k=keys[i]; target=target[k]??=(/^\d+$/.test(keys[i+1]||'')?[]:{});} target[last]=v;}
let seq=0;
function field(path,label,options=null,type='text') {
 const value=get(profile,path); const id='pf-'+(++seq);
 let input;
 if(options) input=`<select id="${id}" data-path="${path}"><option value="">Selecciona una respuesta</option>${options.map(x=>{const [v,l]=Array.isArray(x)?x:[x,x];return `<option value="${escape(v)}" ${String(value??'')===String(v)?'selected':''}>${escape(l)}</option>`;}).join('')}</select>`;
 else if(type==='textarea') input=`<textarea id="${id}" data-path="${path}" rows="3">${escape(value)}</textarea>`;
 else if(type==='checkbox') input=`<input id="${id}" data-path="${path}" type="checkbox" ${value?'checked':''}>`;
 else input=`<input id="${id}" data-path="${path}" type="${type}" value="${escape(value)}" ${type==='number'?'step="any"':''}>`;
 return `<div><label for="${id}">${escape(label)}</label>${input}</div>`;
}
const yesNo=[['si','Sí'],['no','No'],['no_se','No lo sé']];
function place(path,title) {
 return `<fieldset data-place="${path}"><legend>${escape(title)}</legend><label>Buscar lugar en el catálogo territorial</label><input type="search" data-search="${path}" placeholder="Escribe el nombre de la localidad o escuela" aria-label="Buscar lugar"><div class="results" data-results="${path}"></div><small>Si no aparece, completa los datos. Quedará pendiente de georreferenciar; nunca ubicamos tu vivienda automáticamente.</small><div class="grid">`+
 ['departamento','provincia','distrito','localidad'].map((k,i)=>field(path+'.'+k,['Región/departamento','Provincia','Distrito','Centro poblado o localidad'][i])).join('')+
 field(path+'.ubigeo','UBIGEO distrital (si lo conoces)')+field(path+'.comunidad_tipo','¿Este lugar pertenece a una comunidad campesina o nativa?',[['campesina','Comunidad campesina'],['nativa','Comunidad nativa'],['no','No pertenece'],['no_se','No lo sé']])+field(path+'.comunidad_nombre','¿Cuál es el nombre de la comunidad? (si corresponde)')+`</div><small>Centro poblado y comunidad son registros distintos. La comunidad declarada no acredita pertenencia étnica personal.</small><p data-location-status="${path}">${get(profile,path+'.lugar_id')?'Lugar seleccionado del catálogo':'Sin coordenadas confirmadas'}</p></fieldset>`;
}
function job(j,i) {
 const p='trabajos.'+i;
 return `<fieldset class="job"><legend>Empleo ${i+1}</legend><div class="grid">${field(p+'.institucion','Institución, organización o trabajo independiente')}${field(p+'.cargo','¿Qué función realizas?')}${field(p+'.inicio','Fecha de inicio (opcional)','', 'date')}${field(p+'.fin','Fecha de fin (solo si concluyó)','', 'date')}${field(p+'.educativo','Trabajo en el sector educativo',null,'checkbox')}${field(p+'.docencia','Realizo docencia',null,'checkbox')}</div><div data-teaching="${i}" ${j.docencia?'':'hidden'}><h3>Características de la escuela · MINEDU</h3><div class="grid">${field(p+'.codigo_modular','Código modular del servicio educativo (7 dígitos)')}${field(p+'.modalidad','Modalidad educativa',['EBR','EBA','EBE','Otra'])}${field(p+'.nivel','Nivel o ciclo educativo')}${field(p+'.caracteristica','Característica de la institución',[['unidocente','Unidocente'],['polidocente_multigrado','Polidocente multigrado o incompleta'],['polidocente_completa','Polidocente completa'],['no_se','No lo sé']])}${field(p+'.aula','Organización del aula que atiendes',[['uniedad','Uniedad (Inicial)'],['multiedad','Multiedad (Inicial)'],['un_grado','Un grado (Primaria)'],['multigrado','Multigrado (Primaria)'],['no_corresponde','No corresponde']])}${field(p+'.area','Área geográfica del servicio',[['rural','Rural'],['urbana','Urbana'],['no_se','No lo sé']])}${field(p+'.eib','¿Tu institución está registrada como prestadora del servicio EIB?',yesNo)}</div><div data-eib="${i}" ${j.eib==='si'?'':'hidden'}><div class="grid">${field(p+'.forma_eib','Forma de atención EIB',[['fortalecimiento','Fortalecimiento cultural y lingüístico'],['revitalizacion','Revitalización cultural y lingüística'],['no_se','No lo sé']])}${field(p+'.escenario','Escenario sociocultural y lingüístico',[[1,'1 · Predomina lengua originaria'],[2,'2 · Uso de lengua originaria y castellano'],[3,'3 · Predomina castellano; comprensión limitada de lengua originaria'],[4,'4 · Uso reducido de lengua originaria'],[5,'5 · Confluencia de pueblos y lenguas']])}</div><small>RM 389-2024-MINEDU: fortalecimiento corresponde a escenarios 1–2 y revitalización a 3–5, en ámbito rural o urbano. No lo deducimos de la lengua que utilizas.</small></div>${field(p+'.lenguas','¿Qué lengua o lenguas utilizas para enseñar?')}</div>${place(p+'.lugar','Lugar de trabajo')}</fieldset>`;
}
function render() {
 document.getElementById('contacto').innerHTML=field('contacto.nombre_preferido','¿Cómo prefieres que te llamemos? (opcional)')+field('contacto.programa','¿En qué programa y año egresaste? · Programa')+field('contacto.egreso','Año de egreso',null,'number')+field('contacto.correo','Confirma tu correo electrónico (coloca el correo que usas más seguido)',null,'email')+field('contacto.telefono','Número de contacto actual',null,'tel')+field('contacto.medio','¿Qué medio prefieres para que nos comuniquemos contigo?',['WhatsApp','Llamada','Correo']);
 document.getElementById('residencia').innerHTML=place('residencia','¿Dónde vives actualmente?');
 document.getElementById('situacion').innerHTML=field('trabaja','¿Actualmente realizas algún trabajo remunerado, por cuenta propia o para una institución?',[['si','Sí'],['no','No'],['prefiero_no_responder','Prefiero no responder']])+field('busqueda','¿Estás buscando trabajo o nuevas oportunidades laborales?',[['primer_empleo','Busco mi primer trabajo'],['con_experiencia','Busco trabajo y tengo experiencia previa'],['otras_oportunidades','Trabajo y busco otras oportunidades'],['no_busco','No estoy buscando por ahora'],['prefiero_no_responder','Prefiero no responder']]);
 document.getElementById('trabajos').innerHTML=(profile.trabajos||[]).map(job).join('');
 document.getElementById('formacion').innerHTML=field('formacion.estudios','¿Actualmente realizas otros estudios o alguna formación?',null,'textarea')+field('formacion.temas','¿Qué te gustaría aprender o fortalecer? Puedes indicar varios temas',null,'textarea')+field('formacion.prioridad','¿Qué tema sería prioritario para ti?')+field('formacion.compartir','¿Qué saber, experiencia o recurso te gustaría compartir?',null,'textarea');
 if(document.getElementById('alterno'))document.getElementById('alterno').innerHTML=field('contacto_alterno.autorizado','Autorizo contactar a esta persona únicamente para ayudar a localizarme en campañas de egresados',null,'checkbox')+field('contacto_alterno.nombre','Nombre de la persona (opcional)')+field('contacto_alterno.telefono','Teléfono (opcional)',null,'tel')+field('contacto_alterno.correo','Correo (opcional)',null,'email');
 (document.getElementById('apoyo') || document.createElement('div')).innerHTML=field('apoyo.solicitar','Quiero que una persona del equipo se comunique conmigo',null,'checkbox')+field('apoyo.tema','¿Sobre qué tema?',[['empleo','Búsqueda de empleo'],['pedagogia','Práctica pedagógica'],['titulacion','Titulación o trámites'],['formacion','Formación continua'],['bienestar','Bienestar personal o familiar'],['otro','Otro motivo'],['privado','Prefiero conversar en privado']])+field('apoyo.detalle','Cuéntanos lo que consideres necesario (opcional)',null,'textarea');
 document.getElementById('comunidad').innerHTML=`<fieldset><legend>¿Qué vínculo tienes con la comunidad? Puedes marcar varios</legend>${[['origen','Es mi comunidad de origen'],['resido','Resido aquí'],['trabajo','Trabajo aquí'],['colaboro','Participo o colaboro'],['otro','Otro vínculo']].map(([v,l])=>`<label><input type="checkbox" data-vinculo="${v}" ${(profile.comunidad?.vinculos||[]).includes(v)?'checked':''}>${l}</label>`).join('')}</fieldset>`+field('comunidad.desde','¿Desde cuándo mantienes este vínculo? (opcional)',null,'date')+field('comunidad.actividades','¿Qué actividades realizas o compartes con la comunidad?',null,'textarea')+field('comunidad.influencia','¿Tu formación en Pukllasunchis influyó en tu decisión de trabajar o contribuir en una comunidad?',[['mucho','Mucho'],['algo','Algo'],['poco','Poco'],['nada','Nada'],['no_preciso','No puedo precisarlo']])+field('comunidad.relato','¿De qué manera? (opcional)',null,'textarea')+field('comunidad.apoyo','¿Qué apoyo te ayudaría a continuar ese trabajo o participación?',null,'textarea');
 document.getElementById('encuentros').innerHTML=field('encuentros.lugar','¿Qué lugar te queda más cómodo para las reuniones de egresados? Puedes proponer varias localidades',null,'textarea')+field('encuentros.laborales','¿Qué días y horarios tienes disponibles en días laborales?',null,'textarea')+field('encuentros.fin_semana','¿Qué días y horarios tienes disponibles los fines de semana?',null,'textarea')+field('encuentros.variable','Mi disponibilidad varía',null,'checkbox');
 setupDivisions(); progress();
}
async function setupDivisions(){
 for(const fs of form.querySelectorAll('[data-place]')){
  const path=fs.dataset.place;
  for(const [i,key] of ['departamento','provincia','distrito'].entries()){
   const el=fs.querySelector(`[data-path="${path}.${key}"]`);const list=document.createElement('datalist');list.id=el.id+'-opciones';document.getElementById(list.id)?.remove();el.setAttribute('list',list.id);el.after(list);
   const url=new URL(cfg.divisiones,location.origin);
   if(i>0){const d=get(profile,path+'.departamento');if(!d)continue;url.searchParams.set('departamento',d);}
   if(i>1){const p=get(profile,path+'.provincia');if(!p)continue;url.searchParams.set('provincia',p);}
   try{const res=await fetch(url,{headers:{Accept:'application/json'}});if(res.ok){const names=await res.json();list.replaceChildren(...names.map(name=>{const option=document.createElement('option');option.value=name;return option;}));}}catch{/* entrada manual disponible */}
  }
 }
}
function collect(){
 form.querySelectorAll('[data-path]').forEach(el=>{let v=el.type==='checkbox'?el.checked:(el.value.trim()||null); if(el.dataset.path.endsWith('.escenario')&&v)v=Number(v);set(profile,el.dataset.path,v);});
 profile.comunidad??={}; profile.comunidad.vinculos=[...form.querySelectorAll('[data-vinculo]:checked')].map(e=>e.dataset.vinculo);
 return profile;
}
function progress(){
 const fields=['contacto.programa','contacto.egreso','contacto.correo','residencia.departamento','residencia.provincia','residencia.distrito','trabaja'];
 if(profile.trabaja==='si')fields.push('trabajos.0.institucion','trabajos.0.cargo','trabajos.0.lugar.distrito');
 const missing=fields.filter(p=>get(profile,p)==null||get(profile,p)==='');const pct=Math.round(100*(fields.length-missing.length)/fields.length);
 document.getElementById('avance').value=pct;document.getElementById('avance-texto').textContent=`Tu perfil está completo al ${pct} %. Falta el ${100-pct} %.`;
 document.getElementById('pendientes').textContent=missing.length?`${missing.length} datos del núcleo pendientes. Las preguntas voluntarias no reducen tu avance.`:'Núcleo completo. Puedes revisar tus datos antes de confirmar.';
}
form.addEventListener('input',()=>{collect();progress();});
form.addEventListener('change',e=>{
 collect();for(const [i,j] of (profile.trabajos||[]).entries()){form.querySelector(`[data-teaching="${i}"]`).hidden=!j.docencia;form.querySelector(`[data-eib="${i}"]`).hidden=j.eib!=='si';}progress();
 if(e.target.dataset.path && /\.(departamento|provincia|distrito|localidad)$/.test(e.target.dataset.path)){
 const p=e.target.dataset.path.split('.').slice(0,-1).join('.');const o=get(profile,p);delete o.lugar_id;delete o.latitud;delete o.longitud;form.querySelector(`[data-location-status="${p}"]`).textContent='Ubicación modificada: pendiente de georreferenciar';
 const changed=e.target.dataset.path.split('.').pop(); if(changed==='departamento'||changed==='provincia'){for(const key of (changed==='departamento'?['provincia','distrito','localidad']:['distrito','localidad'])){o[key]=null;form.querySelector(`[data-path="${p}.${key}"]`).value='';} setupDivisions();}
 }
});
document.getElementById('agregar').onclick=()=>{collect();profile.trabajos??=[];profile.trabajos.push({institucion:'',cargo:'',lugar:{}});render();};
let timer;
form.addEventListener('input',e=>{if(!e.target.dataset.search)return;clearTimeout(timer);const el=e.target;timer=setTimeout(async()=>{
 const path=el.dataset.search, target=form.querySelector(`[data-results="${path}"]`); if(el.value.length<3){target.replaceChildren();return;}
 try{const url=new URL(cfg.lugares,location.origin);url.searchParams.set('q',el.value);for(const k of ['departamento','provincia','distrito']){const v=get(profile,path+'.'+k);if(v)url.searchParams.set(k,v);}const res=await fetch(url,{headers:{Accept:'application/json'}});if(!res.ok)throw Error();const places=await res.json();target.replaceChildren();if(!places.length)target.textContent='No encontrado. Puedes registrarlo manualmente.';
 places.forEach(p=>{const b=document.createElement('button');b.type='button';b.textContent=`${p.nombre} · ${p.distrito}, ${p.provincia}, ${p.departamento} (${p.tipo})`;b.onclick=()=>{collect();if(p.tipo==='comunidad'){set(profile,path+'.comunidad_nombre',p.nombre);render();return;}set(profile,path,{...(get(profile,path)||{}),lugar_id:p.id,departamento:p.departamento,provincia:p.provincia,distrito:p.distrito,localidad:p.nombre,ubigeo:p.ubigeo,latitud:p.latitud,longitud:p.longitud});render();};target.append(b);});
 }catch{target.textContent='No se pudo consultar el catálogo. Conserva tus datos y vuelve a intentar.';}
 },350);});
function payload(){const p=structuredClone(collect());for(const loc of [p.residencia,...(p.trabajos||[]).map(j=>j.lugar)])if(loc){for(const k of ['precision','fuente','fecha_fuente'])delete loc[k];}if(cfg.esEntrevista){p.confirmacion_verbal=document.getElementById('confirmacion-verbal').checked;p.campana_id=cfg.campanaId;}return p;}
async function save(draft){
 const status=document.getElementById('estado');status.textContent='Guardando…';const buttons=[...document.querySelectorAll('.sticky button')];buttons.forEach(b=>b.disabled=true);
 try{const res=await fetch(draft?cfg.draft:cfg.guardar,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(payload())});const data=await res.json();if(!res.ok||!data.success)throw Error(Object.values(data.errors||{}).flat().join('\n')||data.message||'No se pudo guardar. Tus respuestas siguen en pantalla.');
 if(!draft){profile=data.datos;document.getElementById('confirmacion').textContent=new Date(data.confirmado_at).toLocaleString('es-PE');progress();}status.textContent=draft?data.message:'Tu perfil quedó guardado. El observatorio utilizará esta actualización. Si solicitaste apoyo, lo encontrarás en Mis solicitudes al volver a abrir este espacio.';
 }catch(e){status.textContent=e.message;}finally{buttons.forEach(b=>b.disabled=false);}
}
form.onsubmit=e=>{e.preventDefault();save(false);};if(document.getElementById('borrador'))document.getElementById('borrador').onclick=()=>save(true);
document.getElementById('restaurar')?.addEventListener('click',()=>{profile=structuredClone(cfg.borrador);render();});
render();
})();
