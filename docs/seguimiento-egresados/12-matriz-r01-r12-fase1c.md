# PUKLLA EN RED — FASE 1C
## Matriz Revisada de Requisitos Funcionales y Técnicos (R01–R12)

**Proyecto:** Puklla en Red — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Entidad:** EESP Pukllasunchis  
**Estado General:** Fase 1C Consolidada  
**Fecha de Revisión:** 30 de Octubre de 2026  

---

### Clasificación de Estado:
- **EXISTENTE, PROBADO Y APROBADO:** Código plenamente operativo en la rama `sistemadeseguimientodeegresados` con pruebas unitarias/integración automatizadas aprobadas (100% pass) y verificación de seguridad.
- **HABILITADO EN BACKEND / LISTO PARA FASE 2:** Estructura de base de datos, modelos y endpoints listos; interfaz de analítica institucional programada para la Fase 2A/2B.
- **PLANIFICADO:** Alcance previsto para fases posteriores (Fase 2C / Fase 3).

---

### Matriz Detallada de Requisitos (R01–R12)

| Req | Denominación del Requisito | Alcance Funcional / Técnico | Evidencia en Código y Pruebas | Estado Fase 1C |
|:---|:---|:---|:---|:---|
| **R01** | **Integridad de Identidad y Anti-IDOR** | Resolución estricta de identidad mediante `users.id`. Prohibición de inyecciones arbitrarias de `user_id` desde formularios. Controles de servidor para sesiones de egresados y entrevistadores. | - `SeguimientoEgresadoController::puedeAccederAEgresado()`<br>- Verificación de asignación en `GrupoEntrevistador`<br>- Pruebas `test_08_rechazo_de_identificador_de_usuario_invalido`, `test_09_rechazo_acceso_egresado_a_otra_ficha`, `test_02_egresado_no_puede_consultar_ni_cerrar_episodios_de_otros`. | **EXISTENTE, PROBADO Y APROBADO** |
| **R02** | **Confirmación Rápida Semestral «Sin Cambios»** | Camino rápido de un solo clic para egresados cuya situación académica y laboral sigue vigente. Cero duplicación de episodios laborales. Microchequeo opcional de apoyo. | - Endpoint transaccional `guardarActualizacion()` con switch `$isSinCambios`<br>- Marcación `es_confirmacion_sin_cambios = true`<br>- Apertura de caso prioritario ante requerimientos urgentes<br>- Pruebas `test_04_confirmacion_sin_cambios_no_duplica_episodios`, `test_06_invitacion_criptografica_whatsapp_actualiza_con_verificacion`. | **EXISTENTE, PROBADO Y APROBADO** |
| **R03** | **Persistencia Longitudinal de Trayectorias** | Historia laboral continua, trazable e íntegra. Soporte de primer empleo, cambios de cargo, término de relación y empleos simultáneos. Cero empleadores ficticios "Desempleado". Cero fechas de inicio inventadas con `now()`. | - Modelo `EpisodioLaboral.php` con métodos de cierre selectivo<br>- `Actualizacion::updateOrCreate()` scoped por `periodo_campana`<br>- Pruebas `test_01`, `test_02`, `test_03`, `test_05`, `test_06`, `test_07_preservacion_multicampana`, `test_08_no_inferencia_fechas`. | **EXISTENTE, PROBADO Y APROBADO** |
| **R04** | **Control de Accesos, Segregación y Privilegios** | Políticas en servidor según roles Spatie (`egresado`, `encuestador-seguimiento`, `coordinacion-empleabilidad`, `admin`, `docente`). Egresados solo ven/mutan su expediente; encuestadores solo sus asignados; grupos y avance de casos restringido a coordinación. | - Middleware `auth` en `routes/web.php`<br>- Scoping en `SeguimientoEgresadoController::index()`<br>- Pruebas `test_01_rutas_intranet_requieren_autenticacion`, `test_03_encuestador_no_asignado_recibe_403`, `test_04_egresados_y_encuestadores_no_pueden_gestionar_grupos`. | **EXISTENTE, PROBADO Y APROBADO** |
| **R05** | **Microchequeo Formativo y Demandas de Capacitación** | Captura estructurada de necesidades de formación continua, didáctica EIB, quechua, metodologías lúdicas y disposición para compartir saberes pedagógicos. | - Campos persistidos en `egresado_actualizaciones` (`temas_interes`, `puedo_compartir`, `necesidades_apoyo`, `satisfaccion`)<br>- Formulario móvil público y modal de actualización en intranet. | **EXISTENTE, PROBADO Y APROBADO** |
| **R06** | **Detección y Gestión de Alertas Institucionales** | Creación automatizada o manual de casos de acompañamiento ante demandas de titulación o vulnerabilidad laboral. Seguimiento de estados (`Por revisar` -> `En atención` -> `Cerrado`). | - Modelo `Caso.php`<br>- Endpoints `guardarAccion()` y `avanzarAccion()`<br>- Generación de casos `VERIF-*` ante peticiones sin token<br>- Prueba `test_04_egresados_y_encuestadores_no_pueden_gestionar_grupos_ni_avanzar_casos`. | **EXISTENTE, PROBADO Y APROBADO** |
| **R07** | **Gestión de Equipos y Asignación de Entrevistadores** | Organización de encuestadores por programas y cohortes para campañas telefónicas. Verificación en servidor de la relación encuestador-egresado. | - Modelo `GrupoEntrevistador.php` con método estático `esEntrevistadorAsignado()`<br>- Endpoints `guardarGrupo()` y `eliminarGrupo()` con autorización estricta para directivos. | **EXISTENTE, PROBADO Y APROBADO** |
| **R08** | **Bitácora de Contactos, Llamadas y Trazabilidad** | Registro de intentos de llamada (`No contesta`, `Contactado`, `Buzón`) y auditoría de accesos a datos personales sensibles. | - Modelos `Llamada.php` y `AccesoContacto.php`<br>- Endpoints `guardarLlamada()` y `auditarContacto()` con comprobación de autorización (prohibido para egresados). | **EXISTENTE, PROBADO Y APROBADO** |
| **R09** | **Integración de Canales Múltiples (Web / WhatsApp)** | Campañas seguras para WhatsApp mediante invitaciones criptográficas opacas sin PII en la URL. Aislamiento anti-suplantación en accesos web abiertos. | - Tabla y modelo `InvitacionCampana`<br>- Endpoint `generarEnlaceWhatsapp()` con generación de tokens `INV-*`<br>- Formulario público `campana_publica.blade.php`<br>- Pruebas `test_05_intento_publico_sin_token` y `test_06_invitacion_criptografica_whatsapp`. | **EXISTENTE, PROBADO Y APROBADO** |
| **R10** | **Inteligencia Institucional y Filtros Cruzados** | Tableros analíticos multidimensionales (tasa de ocupación en docencia directa, migración geográfica, inserción EIB, cohortes). | - Estructura de datos completa (`egresado_episodios_laborales`, `egresado_actualizaciones`, ubicaciones geográficas)<br>- Visualizador preliminar conectado<br>- Módulo analítico profundo programado para Fase 2A/2B. | **HABILITADO EN BACKEND / LISTO PARA FASE 2A** |
| **R11** | **Portal y Autoservicio del Egresado** | Espacio institucional donde el egresado consulta su situación, reporta cambios de empleo y solicita apoyo en titulación. | - Vista `/seguimiento-egresados` con scoping automático para rol `egresado`<br>- Formulario público verificado mediante magic link WhatsApp. | **EXISTENTE, PROBADO Y APROBADO** |
| **R12** | **Auditoría, Trazabilidad y Reversión Defensiva** | Seguridad de operaciones de base de datos, transacciones ACID con rollback seguro y migraciones con método `down()` defensivo sin destrucción accidental de datos. | - Migración `2026_10_30_130000_enhance_fase1c_security_and_longitudinal_history.php`<br>- Transacciones `DB::transaction()` con `lockForUpdate()`<br>- Prueba `test_11_rollback_ante_error_transaccional`. | **EXISTENTE, PROBADO Y APROBADO** |

---

### Resumen de Cumplimiento
- **Requisitos Probados y Aprobados:** 11 de 12 (R01, R02, R03, R04, R05, R06, R07, R08, R09, R11, R12).
- **Requisitos Habilitados a nivel de Datos (para frontend en Fase 2A):** 1 de 12 (R10: Tableros de filtros cruzados y mapas analíticos).
