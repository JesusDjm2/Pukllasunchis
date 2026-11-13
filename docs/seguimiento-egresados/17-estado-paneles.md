# Paneles incorporados al código · entrega 3

Código sobre `feature/modulo-seguimiento`, base `9a6709572e7d33dd2f094544f317c1bca2ed9675`. Cambios locales preparados para Antigravity. GitHub denegó la escritura con 403; no hay commit, PR ni despliegue remoto de esta entrega.

| Panel | Incorporado |
|---|---|
| Docente institucional | Menú docente existente, con accesos simultáneos a coordinación y entrevistas. Se conserva tutoría, docencia y las demás funciones originales. Una cuenta puede ejercer ambas funciones. |
| Coordinación | Entrada propia con campañas, fechas y propósito; acceso a asignación equilibrada, previsualización, reasignación y monitoreo existentes; visor territorial y laboratorio sujeto a permisos; solicitudes con responsable, plazo, estado y auditoría; expectativas de formación y disponibilidad, búsqueda y filtro FID/PPD. |
| Entrevistador | Solo asignaciones con campaña vigente; celular, correo y enlace WhatsApp; contacto alternativo únicamente autorizado para campaña; porcentaje real del perfil; mismo formulario ampliado durante llamada; confirmación verbal; resultado, impresiones, motivo de derivación, próxima llamada e historial de las últimas 20; evolución diaria y avance por grupo. |
| Egresado / Mi espacio | Formulario y borrador privado; indicador de completitud; confirmación obligatoria antes del primer acceso a cuenta/comunidad; núcleo: programa, año y correo. Preguntas sensibles y voluntarias no bloquean el acceso. Solicitudes propias, enlace a cuenta/titulación y entrada comunitaria posterior. |
| Informes | Cierre transaccional de campaña y corte conservado sin edición por interfaz; contadores reales de TODOS/FID/PPD/por verificar, barras, denominadores, cobertura y empleo/comunidad de perfiles; balance, horas, costo ejecutado y presupuesto propuesto; impresión/PDF desde navegador. |
| Menú institucional | Pantallas de campaña, laboratorio, mapa, formulario y nuevos paneles integrados con el menú compartido; usuarios docentes usan su layout docente original. El antiguo inicio de seguimiento redirige según funciones reales, sin selector de privilegios. |

## Integración y límites

La coordinación se concede mediante los permisos existentes de gestionar campañas y grupos; para laboratorio y visor se concede además `seguimiento-egresados.ver-analitica`. No se crea un rol coordinador nuevo ni una administración paralela. El docente elegido conserva su rol docente y la habilitación funcional histórica `seguimiento-egresados`; el acceso a personas caduca con la asignación/campaña. Esa habilitación permite actualizar el formulario exclusivamente de sus asignados. Superadministración conserva la asignación de permisos.

Las entrevistas validan persona y campaña en servidor, registran `registrado_por` y procedencia, y asocian la actualización a la campaña seleccionada. No muestran las solicitudes privadas de apoyo ni alteran el borrador personal del egresado. La autogestión se atribuye a campaña cuando hay exactamente una asignación vigente; si concurren varias, no se inventa una atribución. Las respuestas del egresado y las impresiones de la llamada se almacenan separadas.

Los cortes describen datos disponibles al cierre; no reconstruyen una línea de base anterior ni atribuyen causalmente resultados a la formación/campaña. TODOS no se obtiene sumando FID y PPD, porque pueden existir dobles egresos. Los recursos financieros son declaraciones de coordinación, no contabilidad verificada ni financiamiento obtenido. Los cortes son de consulta interna para usuarios autorizados; el PDF se revisa antes de difundirlo.

Se reutilizan modelos y servicios institucionales. Las nuevas tablas son `egresado_campana_cortes` y `egresado_panel_auditoria`; migración aditiva `2026_11_13_000002_create_egresado_paneles.php`. No se eliminan datos en `down`. Ninguna pantalla genera personas o lugares ficticios.

## Comprobación realizada

46 pruebas aisladas: 12 de clasificación FID/PPD, 16 del servicio real de autorización con repositorios sustituidos, 8 del middleware real de primera visita con almacenamiento sustituido, y 10 de interfaz con Node/jsdom. Sintaxis comprobada con PHP 8.2 WebAssembly y compilación sintáctica de los bloques JS de las vistas. Las pruebas aisladas no validan consultas SQL, sesiones reales ni ejecución completa de Blade.

**Pendiente antes de activar:** ejecutar Laravel/Blade y migraciones sobre una copia de ensayo con dependencias instaladas, comprobar rutas, permisos, combinaciones de roles, reasignación y vencimientos con MySQL; probar cierre simultáneo, impresión y datos FID/PPD. Esta entrega no está validada en producción.

**Fuera del desarrollo de estos paneles:** diseño de Comunidad, notificaciones automáticas, chat por promoción/interés, catálogos geográficos oficiales completos, servicio de rutas y sedes por tiempo de viaje. El enlace comunitario utiliza el Ayllu existente y no rediseña ese módulo.
