# FID y PPD: dimensión transversal de seguimiento

Fecha: 13 de noviembre de 2026. Complementa el cambio de perfil/territorio preparado sobre `feature/modulo-seguimiento` en `9a6709572e7d33dd2f094544f317c1bca2ed9675`. Cambios locales para ensayo; no desplegados.

## Fuentes y reglas

- Reutiliza `App\Enums\Modalidad`, `programas.modalidad`, `User::programa`, `alumno.programa`, `alumnoB.programa` y eventos institucionales de egreso. No crea grupos como roles, tablas de personas ni otro catálogo de modalidades.
- `GrupoFormacionService` centraliza clasificación, carga de relaciones, filtros y selección de ficha/programa pertinente. No deduce modalidad del nombre del programa o del rol, ni asigna FID por ausencia de PPD.
- Los eventos explícitos de egreso con programa asociado tienen prioridad. Una matrícula posterior en otro programa no se considera un segundo egreso. Dos egresos confirmados conservan FID y PPD; si faltan pruebas se informa por verificar.
- Sin eventos explícitos, la modalidad del programa institucional se utiliza como referencia; dos fichas contradictorias sin programa principal ni egresos trazables quedan por verificar. Se conserva bandera de contradicción cuando procede, sin reasignar datos ni modificar fuentes institucionales.
- El selector compartido ofrece Todos, FID, PPD, Comparar y Por verificar. Las dos primeras son modalidades académicas; Todos/Comparar son modos de consulta, y Por verificar es un estado de calidad.
- Las comparaciones presentan tasas con denominador propio y ocultan indicadores pequeños. Las personas con ambos egresos pueden estar en ambos grupos. Los totales conjuntos y asignaciones cuentan personas únicas. No sumar FID+PPD para deduplicar.
- Cohortes: se usa fecha atribuida al grupo cuando hay egresos duales; sin ella queda por verificar. No se deduce egreso de dígitos del correo. La comparación de cohortes es descriptiva, no evaluación causal ni una reconstrucción de estados a una fecha histórica; un informe histórico congelado necesita corte de campaña persistido.

## Aplicación en código

1. Padrón: filtro en backend, datos de grupo en cada fila, sin límite silencioso de 300. Se restringen casos/llamadas/actualizaciones/episodios al mismo conjunto autorizado. Se enmascaran notas confidenciales antes de enviarlas a la vista. El filtro no amplía permisos.
2. Campañas: filtro compartido en gestión, monitoreo y territorio; el grupo se envía tanto a vista previa como a ejecución. Las asignaciones conservan su unicidad por campaña/persona. El grupo de la distribución es un filtro del lote, no una reasignación académica del egresado.
3. Docente: lista con FID/PPD y comparación del avance de personas asignadas por grupo, barras de progreso. Corrección de conteos de actualizaciones repetidas y del aislamiento de llamadas por campañas.
4. Laboratorio: grupo como variable de cruce y filtro; numeradores y denominadores utilizan el mismo conjunto. Invitación enviada no equivale a contacto efectivo. Respuesta se calcula sobre padrón filtrado, docencia sobre información laboral disponible; sin denominador se devuelve null. Se elimina inferencia de titulación por cualquier texto no vacío y de EIB laboral por especialidad de formación.
5. CSV: exportación conserva los mismos filtros y explicita el grupo. El formulario anterior enviaba filtros como parámetros simples que el servidor no leía: corregido.
6. Demandas de formación: última respuesta verificada por persona del conjunto filtrado, no toda la historia sumada como demanda vigente.
7. Observatorio: capas separadas de residencia/empleo con filtro; composición FID/PPD de puntos agregados y protección complementaria entre ambos valores. Sin domicilios o identidades en la respuesta del mapa.
8. Mi espacio: muestra clasificación institucional no editable como permiso o autodeclaración. La presentación de programa se distingue de modalidad. Continúa el borrador y la confirmación de datos.
9. Padrón anterior: ya no agrega ejemplos ficticios FID a los registros institucionales ni a filtros PPD vacíos. El consolidado nominal histórico «7 listas» sigue siendo una referencia histórica y no un indicador de cobertura del grupo actual.

## Verificación ejecutada

- 12 pruebas de las reglas PHP reales de clasificación y filtrado, con PHP 8.2 WebAssembly aislado (no Laravel).
- 8 pruebas Node: cuatro del perfil y cuatro de deduplicación/ausencia de registros ficticios en el padrón.
- Análisis sintáctico PHP 8.2 de 24 archivos y compilación de 12 bloques JavaScript Blade con interpolaciones sustituidas para la comprobación.
- `git diff --check` y comprobación reversible del parche.

No se han ejecutado PHPUnit/Laravel, renderizado Blade, consultas SQL ni migraciones contra una base de ensayo. El entorno PHP WebAssembly solo se utilizó para reglas puras y sintaxis; no acredita el funcionamiento de la aplicación completa.

## Verificación requerida en Antigravity

Probar al menos: egresado FID con ficha, PPD con ficha, programa directo sin ficha, clasificación desconocida, fichas contradictorias, persona con ambos egresos, docente sin asignaciones, docente con asignaciones mixtas y coordinador que también entrevista. Con todos los modos de consulta, contrastar cantidades de listado, matrices, CSV y puntos del mapa. Confirmar que una actualización repetida no aumenta personas; una reasignación no las duplica; un filtro no habilita expedientes ajenos. Ensayar cuotas y lotes de distribución sobre una campaña de prueba antes de usar cuentas reales.
