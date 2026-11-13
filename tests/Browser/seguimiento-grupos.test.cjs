const test=require('node:test');
const assert=require('node:assert/strict');
require('../../public/js/seguimiento-grupos.js');
const {crearPadron}=globalThis.PukllaGrupos;
test('un grupo vacío no recibe egresados ficticios FID',()=>assert.deepEqual(crearPadron([]),[]));
test('la persona con ambos grupos aparece una vez en el padrón conjunto',()=>{
 const rows=crearPadron([{id:'REAL-1',grupos_formacion:['FID','PPD']},{id:'REAL-1',grupos_formacion:['FID']}]);
 assert.equal(rows.length,1);assert.deepEqual(rows[0].grupos_formacion,['FID','PPD']);
});
test('clasificación desconocida no se convierte en FID',()=>assert.deepEqual(crearPadron([{id:'REAL-2'}])[0].grupos_formacion,[]));
test('no se cambia la población autorizada por el servidor',()=>{
 const source=[{id:'REAL-3',grupos_formacion:['PPD'],programa:'Inicial'}];const rows=crearPadron(source);
 assert.equal(rows.length,source.length);assert.deepEqual(rows[0].grupos_formacion,['PPD']);assert.deepEqual(source[0].grupos_formacion,['PPD']);
});
