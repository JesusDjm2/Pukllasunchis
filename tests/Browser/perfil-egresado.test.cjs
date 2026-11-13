const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');
const source = fs.readFileSync('public/js/perfil-egresado.js','utf8');
function boot(datos={}) {
 const dom=new JSDOM(`<meta name="csrf-token" content="test"><form id="perfil-form"><div id="contacto"></div><div id="residencia"></div><div id="situacion"></div><div id="trabajos"></div><div id="formacion"></div><div id="apoyo"></div><div id="comunidad"></div><div id="encuentros"></div><button id="agregar" type="button"></button><button id="borrador" type="button"></button></form><progress id="avance" max="100"></progress><span id="avance-texto"></span><span id="pendientes"></span><span id="confirmacion"></span><span id="estado"></span>`,{url:'https://puklla.test/',runScripts:'outside-only'});
 dom.window.structuredClone=structuredClone;
 dom.window.PUKLLA_PERFIL={datos,guardar:'/guardar',draft:'/draft',lugares:'/lugares',divisiones:'/divisiones'};
 dom.window.fetch=async()=>({ok:true,json:async()=>[]});dom.window.eval(source);return dom;
}
test('sin respuestas no se inventa empleo ni avance',()=>{const d=boot();assert.equal(d.window.document.querySelector('[data-path="trabaja"]').value,'');assert.equal(d.window.document.getElementById('avance').value,0);d.window.close();});
test('docencia despliega preguntas EIB y territorio de cada empleo',()=>{const d=boot({trabajos:[{institucion:'Escuela',cargo:'Docente',docencia:true,eib:'si',lugar:{}}]});assert.equal(d.window.document.querySelector('[data-teaching]').hidden,false);assert.equal(d.window.document.querySelector('[data-eib]').hidden,false);assert.ok(d.window.document.querySelector('[data-path="trabajos.0.lugar.comunidad_tipo"]'));d.window.close();});
test('campos voluntarios no disminuyen el porcentaje del núcleo',()=>{const d=boot({contacto:{programa:'Inicial',egreso:2025,correo:'test@example.test'},residencia:{departamento:'Cusco',provincia:'Canchis',distrito:'Sicuani'},trabaja:'no'});assert.equal(d.window.document.getElementById('avance').value,100);d.window.close();});
test('error de servidor conserva respuestas y no comunica éxito',async()=>{const d=boot();d.window.fetch=async()=>({ok:false,json:async()=>({message:'Error de prueba'})});d.window.document.getElementById('borrador').click();await new Promise(r=>setTimeout(r,10));assert.equal(d.window.document.getElementById('estado').textContent,'Error de prueba');assert.ok(d.window.document.querySelector('[data-path="contacto.correo"]'));d.window.close();});
test('entrevista usa el mismo formulario sin borrador y envía confirmación verbal',async()=>{
 const d=boot({contacto:{programa:'Inicial',egreso:2025,correo:'test@example.test'}});
 d.window.close();
 const markup=`<meta name="csrf-token" content="test"><form id="perfil-form"><div id="contacto"></div><div id="residencia"></div><div id="situacion"></div><div id="trabajos"></div><div id="formacion"></div><div id="comunidad"></div><div id="encuentros"></div><input id="confirmacion-verbal" type="checkbox" checked><button id="agregar" type="button"></button></form><progress id="avance" max="100"></progress><span id="avance-texto"></span><span id="pendientes"></span><span id="confirmacion"></span><span id="estado"></span>`;
 const dom=new JSDOM(markup,{url:'https://puklla.test/',runScripts:'outside-only'});dom.window.structuredClone=structuredClone;
 dom.window.PUKLLA_PERFIL={datos:{},esEntrevista:true,guardar:'/entrevista/20',divisiones:'/divisiones'};
 let sent;
 dom.window.fetch=async(url,opts)=>{if(opts){sent={url,body:JSON.parse(opts.body)};return {ok:false,json:async()=>({message:'Revisar campos'})};}return {ok:true,json:async()=>[]};};
 dom.window.eval(source);dom.window.document.getElementById('perfil-form').dispatchEvent(new dom.window.Event('submit',{cancelable:true}));await new Promise(r=>setTimeout(r,10));
 assert.equal(sent.url,'/entrevista/20');assert.equal(sent.body.confirmacion_verbal,true);assert.equal(sent.body.apoyo,undefined);assert.equal(dom.window.document.getElementById('estado').textContent,'Revisar campos');dom.window.close();
});
test('contacto alternativo conserva revocación explícita de autorización',async()=>{
 const d=boot({contacto_alterno:{autorizado:true,nombre:'Prueba',telefono:'000'}});
 const alterno=d.window.document.createElement('div');alterno.id='alterno';d.window.document.getElementById('perfil-form').append(alterno);
 // Un cambio de docencia vuelve a renderizar; los campos se leen del estado, no se inventan.
 d.window.document.getElementById('agregar').click();
 const check=d.window.document.querySelector('[data-path="contacto_alterno.autorizado"]');assert.ok(check.checked);check.checked=false;check.dispatchEvent(new d.window.Event('change',{bubbles:true}));
 let payload;d.window.fetch=async(url,opts)=>{payload=JSON.parse(opts.body);return {ok:false,json:async()=>({message:'No guardado'})};};d.window.document.getElementById('borrador').click();await new Promise(r=>setTimeout(r,10));assert.equal(payload.contacto_alterno.autorizado,false);d.window.close();
});
