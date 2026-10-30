# PUKLLA EN RED — DOCUMENTO MAESTRO PARA ANTIGRAVITY
## Especificación funcional, técnica, de datos, seguridad y validación

**Institución:** EESP Pukllasunchis · **Repositorio objetivo:** `JesusDjm2/Pukllasunchis` · **Rama:** `seguimientoesp`  
**Versión de la especificación:** 3.0 — consolidación auditada · **Fecha:** 30 de octubre de 2026  
**Estado:** documento rector de producto, **NO** DDL aprobado, **NO** constatación de despliegue productivo.  
**Auditoría documental:** tres borradores proporcionados por el usuario (`Texto pegado.txt`, `(2)`, `(3)`), documento maestro anterior, estructura de modelo SQL auxiliar y acuerdos/prototipos históricos.

> **Mandato rector:** **simple para el egresado, riguroso para el seguimiento, potente para la toma de decisiones**. Primero auditar el repositorio y su base real; después adaptar e implementar por módulos y pruebas, sin inventar estado del sistema.

## 0. Resumen ejecutivo y orden de autoridad

Puklla en Red integra a los egresados de Formación Inicial Docente (Inicial y Primaria EIB) en una comunidad de acompañamiento académico, laboral, territorial, personal y de aprendizaje recíproco. La institución debe obtener información oportuna para decidir cursos, visitas, apoyos, reencuentros, convenios y mejoras curriculares, evaluando los resultados de sus acciones.

**Jerarquía de fuentes:** (1) código y esquema **real** inspeccionado por Antigravity para decisiones de implementación; (2) acuerdos funcionales institucionales establecidos en este documento; (3) bases históricas importables con trazabilidad; (4) simuladores V4–V11 como evidencia de experiencia de usuario, nunca como fuente autoritativa de backend; (5) borradores SQL de propuesta como ideas, no como migraciones listas para producción.

**Precondiciones:** trabajar en `seguimientoesp`; identificar HEAD/remoto/estado del árbol; registrar conflictos existentes y servicios; entorno de ensayo separado con respaldo; sin acceso irrestricto a información personal en Git; autorización explícita antes de operaciones irreversibles o despliegue.

### 0.1 Auditoría de los tres borradores: decisiones de corrección

| Hallazgo | Riesgo si se implementa literalmente | Decisión obligatoria |
|---|---|---|
| Se afirma PHP 7.4/8.x, MySQL 5.7+/MariaDB y JavaScript vainilla como stack confirmado | Diseñar contra versiones inexistentes u obsoletas | **No imponer versiones**: verificarlas en repositorio/entorno; elegir APIs y tipos compatibles solo tras diagnóstico. |
| DDL crea `egresado_persona` con documento único obligatorio, programa y promoción obligatorios | Duplicar personas, descartar registros incompletos, romper vínculos actuales | Reutilizar identidad institucional; permitir estado académico y programa por verificar; definir conciliación y claves con el esquema real. |
| Segmentación A/B/C/D guardada como atributo obligatorio de episodio laboral | D superpone PRONABEC y A–C; estigmatización y clasificación inconsistente | **Estados multidimensionales**, criterios revisables; PRONABEC es dimensión separada; prioridad de atención validada por persona autorizada. |
| `es_sector_educacion` y `es_actual` por defecto `TRUE` | Inflar empleabilidad docente y crear episodios corrientes falsos | Estados explícitos y desconocido (`NULL`) cuando corresponda; episodios con fechas, posibles trabajos simultáneos y evento de confirmación. |
| Inicio y egreso en `VARCHAR/ENUM` rígidos; condiciones múltiples de titulación reducidas a una sola | Pérdida de historicidad, ambigüedades de cohorte y tipo de estudios | Catálogos versionados, hitos académicos con fuente/fecha, programa/promoción y elegibilidad independientes. |
| `ON DELETE CASCADE` en registros confidenciales | Borrar trazas/bitácora de intervenciones accidentalmente | Retención y eliminación controladas, sin cascada destructiva por defecto; plan legal de conservación. |
| Enlace WhatsApp con token HMAC descrito como garantía de identidad | Enlace compartido compromete perfil | Token aleatorio fuerte **almacenado solo como hash**, expiración, canje limitado, rate limiting, autenticación/verificación adicional al acceso a datos sensibles; no PII en URL. |
| Autoguardado indiscriminado en `localStorage` | Exposición de contactos y notas sensibles en equipos compartidos | Preferir borrador persistente en servidor autenticado, sin guardar PII sensible de forma permanente en navegador. |
| «No cambió nada» salta directamente al paso 4 | Omite nuevos desafíos, bienestar y necesidades | Permitir confirmar lo estable y mostrar microchequeo de bienestar profesional/solicitudes, sin repetir preguntas innecesarias. |
| Supresión de celdas 1–4 presentada como anonimato suficiente | Diferencias entre totales/subtotales pueden revelar valores | Supresión primaria **y complementaria** o agregación; controlar exportaciones, filtros sucesivos y consulta repetida. |
| Sprints excluyen bolsa, formación, visitas, comunidad y evaluación de intervenciones | Se pierde la finalidad institucional del sistema | Mantener roadmap completo con núcleo incremental y fases posteriores explícitas. |
| Informes de julio y diciembre, encuentro en mayo fijados como invariantes | Conflicto con campañas/reencuentros acordados | Calendario configurable; julio/diciembre metas propuestas; no fijar mayo como única fecha. |
| Archivo `(3)` contiene texto de una ejecución Python y duplica la especificación | Agente puede interpretar trazas como instrucciones de implementación | Documento rector limpio, sin transcripciones de consola ni DDL ejecutable prematuro. |

### 0.2 Evidencias históricas y límites de interpretación

- Siete listas de promociones 2021-II a 2025-II: **266 filas, 258 personas únicas por documento** según la consolidación anterior; 94 con condición explícita de egreso/bachiller/licenciatura y 164 pendientes de validar. Son cifras de un **corte**, no universo definitivo.
- Archivo histórico de empleabilidad: 256 filas/254 nombres tras duplicados; encuesta 2026: 119 respuestas brutas → 115 tras duplicados exactos → **113 registros analíticos** después de conciliación; quedan conflictos de identidad. No mezclar denominadores de fuentes.
- Conversaciones previas reportaron un avance local de 248 registros FID y vinculaciones de respuestas: **no comprobado contra la rama actual**. Tratar como antecedente, no como resultado verificado.
- Modelo SQL auxiliar `Modelo_base_datos_Pukllasunchis_2026.sql`: incluye entidades de seguimiento académico (`personas`, `hitos_titulacion`, `insercion_laboral`, etc.). No afirmar que sus tablas existen en la intranet real.
- Algunos registros de listas poseen estado **matriculado, licencia, IX o vacío**; no asignar automáticamente condición de egresado.

---

## 1. Propósito, enfoque y objetivos

Construir una **relación recíproca y sostenida** entre la EESP y sus egresados: conocer cambios de trayectoria, responder solicitudes, facilitar empleo digno y titulación, promover aprendizaje profesional y aprovechar sus experiencias para retroalimentar la formación inicial. Principio: **simple para quien actualiza; profundo para quienes acompañan y deciden**. Secuencia institucional: **dato → interpretación → decisión → acción → seguimiento → evaluación**.

Resultados de producto verificables:
1. Un egresado puede confirmar datos previos sin repetir una encuesta extensa.
2. Una actualización genera versiones históricas, no borra empleos, contratos ni respuestas pasadas.
3. Un encuestador autorizado usa el mismo esquema de formulario en una tarea asignada y auditada.
4. Coordinación puede gestionar solicitudes, visitas, cursos y reencuentros con responsables, plazos y resultados.
5. Dirección puede combinar filtros, construir cruces, comparar cohortes, visualizar mapas, exportar datos adecuados y derivar decisiones operativas.
6. No se divulgan datos sensibles o identificables en portales públicos ni exportaciones agregadas.

## 2. Alcance y no objetivos

**Incluye:** FID Educación Inicial y Primaria EIB; campañas; padrón; titulación; trayectoria laboral; contrato; protección social; satisfacción; práctica pedagógica intercultural; residencia y empleo geográficos; formación continua; trabajo digno; solicitudes y acompañamiento; PRONABEC cuando aplique; bolsa laboral gradual; actividades comunitarias; analítica; permisos; auditoría.

**No incluye inicialmente:** diagnóstico clínico, clasificación automática de vulnerabilidad personal, asignación de vacantes sin intervención humana, sanción o presunción automática de incumplimiento PRONABEC, publicación de directorios sin consentimiento, minería de contenido socioemocional para indicadores identificables.

## 3. Personas usuarias y matriz de responsabilidades

| Perfil | Puede realizar | Restricciones |
|---|---|---|
| Egresado | Consultar/corregir su ficha; solicitar apoyo; consultar formación/empleo/eventos; definir visibilidad de su contacto | Sólo datos propios |
| Encuestador temporal | Ver casos/contactos asignados; llamar, registrar resultado; completar misma actualización | Sin acceso a bienestar reservado ni inteligencia/directorios generales; autorización temporal revocable |
| Coordinación de Egresados | Validar padrón, organizar campañas, asignar casos, planificar visitas, consultar contactos con finalidad operativa | Acceso proporcional al trabajo; no notas personales reservadas salvo habilitación expresa |
| Bienestar / Trabajo Social | Recibir, atender y cerrar solicitudes confidenciales, derivar cuando corresponda | Documentación reservada; no compartir contenido en tableros abiertos |
| Empleabilidad / SIL | Vacantes, empleadores, orientación, CV/portafolio, transiciones laborales | No compartir postulaciones sin autorización |
| Formación Continua / SFC | Diagnosticar demanda, programar cursos, evaluar aplicación, gestionar certificaciones | Limitar acceso a datos personales |
| Secretaría | Confirmar egreso, bachillerato, titulación y documentos | Fuente autorizada de hitos académicos |
| Dirección | Analizar indicadores longitudinales, segmentar, evaluar políticas; autorizar acciones | Por defecto agregados; identificables sólo con permisos/finalidad y auditoría |
| Administración | Roles, configuración, catálogo, integraciones y seguridad | Auditoría y mínimo privilegio |

Roles **acumulables** y permisos verificables en backend; nunca depender del selector visual de rol. Debe existir registro de quién accede a contactos, direcciones y expedientes.

## 4. Arquitectura de información y procesos

### 4.1 Identidad y padrón maestro
- Una clave institucional inmutable por persona; tablas de equivalencia con código SIA, identificadores de encuesta y fuente original. Nunca unir únicamente por nombre.
- Estados separados de **elegibilidad** (egreso confirmado / pendiente), **etapa académica** (egresado, bachiller, titulado), **contactabilidad**, **consentimiento**, y **estado laboral**.
- Deduplicación con evidencia y revisión humana. Preservar originales y trazabilidad; no destruir respuestas ni adjudicar programas ambiguos.
- Registro de procedencia, calidad y fecha de observación. Distinguir fecha del hecho y fecha de captura.

### 4.2 Formulario único y adaptativo («Mi Espacio Puklla»)
Cuatro pantallas móviles, con indicador de avance, autoguardado, confirmación, recuperación de borrador y validación accesible:
1. **Contacto:** celular, WhatsApp, correo, residencia (UBIGEO) y preferencias de comunicación. Solicitar dirección detallada sólo cuando exista propósito justificado y como dato opcional.
2. **Situación académica y laboral:** validar condición de egreso/título; ¿trabaja actualmente?; si nada cambió confirmar; de lo contrario registrar transición laboral, contrato, institución, ámbito rural/urbano, tipo de escuela (incluida EIB), fechas y territorio.
3. **Condiciones, práctica y satisfacción:** estabilidad, protección social, jornada, rango opcional de ingresos, satisfacción profesional y condiciones; posibilidad de desplegar enfoque intercultural; diferencia entre satisfacción pedagógica y contractual.
4. **Oportunidades y vínculos:** necesidades de formación, capacidades para compartir, relación con compañeros (voluntaria, sin publicar datos ajenos), interés en encuentros, solicitud de ayuda, participación y permisos.

Mostrar sólo lo necesario; preguntas condicionadas; cambios destacados; no 50 preguntas obligatorias. Canal de autoría: `self`, `phone_interview`, `coordination`; misma validación en los tres. El historial jamás debe sobrescribirse silenciosamente.

### 4.3 Campañas semestrales (FSS)
- Dos campañas configurables por año, fechas de apertura/cierre; universo elegible; segmentación y cobertura; cierre con corte reproducible.
- Enlace WhatsApp sin login institucional sólo durante campaña, pero **NO revelar ficha por nombre + promoción**: OTP o prueba de posesión de canal previamente verificado; expiración, rate-limit, anti-enumeración, manejo de cambios de número.
- Fuera de campaña: autenticación institucional cuando aplique; ruta alternativa segura para egresados sin acceso vigente.
- Recordatorios y llamadas sólo según consentimiento y reglas de comunicación.
- Tareas de encuestador asignadas por grupo/fecha; resultado «respondió», «sin contacto», «número inválido», «solicitó apoyo», etc.; NO confundir ausencia de respuesta con desempleo.

### 4.4 Historial académico y laboral
- Hitos oficiales: egreso, bachiller, titulación, fechas/documentos y fuente verificada.
- Tabla de episodios laborales: empresa/IE, puesto, sector, tipo de vínculo, dedicación, docencia directa/no, inicio, fin, ubicación; episodios superpuestos permitidos cuando haya varios trabajos.
- Cambios y periodos sin empleo, con motivos opcionales, sin presunciones.
- Cálculos sólo con fechas y elegibilidad: tiempo de egreso→primer empleo, primer empleo docente, contrato estable; mediana/tasas por cohorte; censura y datos faltantes visibles. No equiparar contrato anual o histórico con estabilidad actual.

### 4.5 Acompañamiento y bienestar (SST)
- Entrada voluntaria «Solicitar orientación» con categoría académica/laboral/personal, preferencia de contacto y consentimiento.
- Flujo: solicitud→triage humano→responsable→plan/derivación→seguimiento→resultado→cierre y eventual reapertura.
- Agenda, notificación interna, vencimiento, SLA configurable y tareas; notas de bienestar segregadas por autorización; no aplicar etiquetas automáticas de «persona crítica».
- PRONABEC: módulo condicional por beca/convocatoria, obligaciones y fechas documentales verificadas, alertas para revisión, nunca dictamen automático de incumplimiento.

### 4.6 Bolsa laboral, formación y comunidad
- SIL: alianzas, vacantes verificadas, fechas de vigencia, filtros por programa/territorio/EIB; matching explicable y opt-in; preparación para entrevistas; CV institucional y portafolio pedagógico en PDF.
- SFC: necesidades clasificadas, cursos/talleres/comunidades, cupos, sesiones, inscripción, asistencia, constancias autorizadas, valoración y seguimiento de aplicación a 30–90 días; egresados como facilitadores/mentores.
- SCE: eventos configurables sin mes fijo (el reencuentro 2026 se trabajó para octubre; una propuesta posterior menciona mayo), inscripciones, intereses, grupos focales, acuerdos y seguimiento. Representantes por promoción con autorización expresa.
- Visitas de tres tipos: acompañamiento solicitado, aprendizaje institucional y colaboración entre pares. Muestreo intencional diverso para las 20–24 visitas propuestas, no sólo situaciones difíciles; rutas territoriales, consentimiento, observación y acuerdos.

## 5. Inteligencia institucional: requisitos irrenunciables

Una consola para Dirección y Coordinación con **filtros simultáneos**, persistencia de selección y constructor de tablas dinámicas por filas y columnas. Variables: programa, promoción y semestre, estado académico/titulación, tiempo desde egreso, situación laboral, sector, docencia directa, tipo de contrato, condición de trabajo digno, satisfacción, territorio residencia/trabajo, necesidades formativas, estado de actualización y canal. Cortes temporales comparables.

Vistas: (a) cobertura y calidad del padrón; (b) inserción y trayectorias; (c) condiciones laborales y satisfacción; (d) territorios; (e) formación continua; (f) solicitudes y resultados; (g) vínculos y reencuentros; (h) evaluación de intervenciones. Mapas por UBIGEO validado, sin puntos domiciliarios personales.

Cada gráfico/exportación indica **universo, numerador, denominador, fecha de corte, filtros, faltantes, fuente y criterios de exclusión**. Suprimir o agrupar celdas pequeñas (regla inicial <5, revisable conforme al riesgo de reidentificación). Respuestas abiertas de bienestar fuera de exportaciones generales. Exportación a CSV/XLSX/PDF sólo con permisos, justificación y trazabilidad.

**Del análisis a la acción:** seleccionar subconjunto elegible→proponer campaña de contacto/curso/visita/encuentro→revisión humana→responsable/fecha→registro de resultados→evaluación del cambio. Las acciones derivadas de cruces no dan acceso automático a datos personales restringidos.

## 6. Modelo lógico mínimo (orientativo, no migración literal)

`persons`, `person_source_links`, `program_enrollments`, `graduation_events`, `academic_milestones`, `contact_points`, `contact_consents`, `territory_locations`, `labor_episodes`, `employment_snapshots`, `working_conditions`, `practice_reflections`, `survey_instruments`, `survey_versions`, `campaigns`, `campaign_invites`, `survey_submissions`, `interviewer_assignments`, `call_attempts`, `support_requests`, `support_actions`, `support_referrals`, `course_offerings`, `course_participations`, `course_evaluations`, `job_opportunities`, `job_matches`, `institution_partners`, `visits`, `events`, `event_participations`, `peer_connections_consented`, `user_roles`, `role_permissions`, `access_audit`, `data_quality_flags`, `import_batches`.

**IMPORTANTE:** Antigravity debe mapear primero las tablas reales de producción, aprovechar sus claves, reutilizar `GrupoEntrevistador.php` y el generador de WhatsApp si están presentes y funcionales, y diseñar migraciones **aditivas, reversibles y probadas**. El SQL auxiliar visto es un ejemplo de seguimiento académico FID, no evidencia de las tablas actuales de la intranet.

## 7. Requisitos técnicos no funcionales

- Seguridad: verificación backend en cada API/ruta, sesiones robustas, protección contra CSRF/XSS/SQLi, validación servidor, rate limits, auditoría, separación de roles, mínimo privilegio, cifrado y copias de respaldo. Cero secretos o PII en Git, HTML estático, logs o mensajes compartidos.
- Protección de datos personales: finalidad, autorización y derechos de los titulares; privacidad por diseño, plazos de conservación, clasificación de sensibilidad, protocolo de incidentes. Revisar marco peruano aplicable con asesoría institucional.
- UX: mobile-first, accesibilidad AA como objetivo, navegación con teclado, contraste, estados vacíos, validaciones en lenguaje sencillo, indicadores del progreso, recuperación ante fallo de conexión.
- Calidad: consultas eficientes e indexadas, paginación, registros de cambios y concurrencia segura; posibilidad de escala sin replicar información.
- Observabilidad: errores, latencia, incidentes de sincronización, tareas demoradas; registros técnicos desidentificados.

## 8. Pruebas de aceptación (obligatorias antes de considerar «funcional»)

1. Un egresado confirma «sin cambios» y termina en menos pasos; el padrón, campaña y reporte se actualizan correctamente.
2. Al declarar fin de trabajo no queda «empleado estable actual»; se conserva el episodio anterior.
3. Encuestador puede actualizar **sólo** su caso asignado y durante vigencia; acceso a caso ajeno falla en servidor.
4. Egresado no puede acceder a otro perfil cambiando un ID o URL; enlace de campaña no revela información sin verificación adicional.
5. Dirección cruza programa × contrato y promoción × empleo; filtros se combinan; porcentajes coinciden con exportación y respetan faltantes.
6. Exportaciones agregadas suprimen celdas pequeñas; un archivo no revela notas de bienestar o contactos sin permiso.
7. Solicitud de apoyo genera tarea y seguimiento; sólo Bienestar accede a notas reservadas.
8. Course/eventos y visitas registran acciones y evaluación, sin duplicar personas.
9. Reimportar lote de base no crea duplicados y conserva trazabilidad; conflictos quedan en cola de revisión humana.
10. Pruebas E2E con navegador real para móvil y escritorio, incluyendo navegación, botones, estados, errores de red, CSV/XLSX, permisos negativos, formularios de cuatro pasos y recálculo de indicadores.
11. Todas las migraciones se ejecutan en base de prueba con rollback y respaldo; no se aplica a producción sin aprobación explícita.

## 9. Plan de trabajo en Antigravity y definición de terminado

### Fase 0: diagnóstico y conciliación
Inspeccionar HEAD/branch, esquema real MySQL/SQL disponible, roles, módulo `GrupoEntrevistador.php`, generador WhatsApp, carga de formularios y código de reportes. Producir mapa **CONFIRMADO / INFERIDO / PENDIENTE**. Auditar fuente autorizada (sin volcar PII en reporte) y correspondencias históricas. No tocar producción.

### Fase 1: núcleo confiable
Persona única, elegibilidad, importaciones y conflictos, historial, campañas, formulario adaptativo compartido, permisos backend y bitácora. Pruebas automatizadas antes del primer piloto.

### Fase 2: atención y decisiones
Gestión de solicitudes, asignación de acciones, directorio restringido, laboratorio multidimensional y reportes; métricas fiables por corte.

### Fase 3: servicios y comunidad
Cursos, bolsa de trabajo y alianzas, visitas, encuentros, mentorías y PRONABEC verificado por modalidad.

### Fase 4: evaluación y despliegue
Panel de impacto, QA E2E, accesibilidad móvil, pruebas de seguridad, capacitación, piloto y aprobación formal.

**Definición de terminado por historia:** código integrado con patrones existentes; tests unit/integración/E2E relevantes aprobados; permisos negativos probados; migración reversible; documentación de endpoints y datos; sin fugas de PII; validación funcional con Coordinación/Dirección; changelog y capturas en el PR. No afirmar «probado» solo por análisis estático.

---

## 10. Contratos de integración: especificación condicionada al diagnóstico

Antigravity debe documentar primero las **rutas existentes**. La siguiente lista es un **contrato de comportamiento**, NO una orden de crear endpoints REST concretos si la aplicación usa otro patrón.

| Operación de dominio | Solicitud | Respuesta esperada | Seguridad |
|---|---|---|---|
| Obtener perfil propio | Identidad obtenida de sesión validada, nunca ID confiado por cliente | Ficha vigente y fecha/fuente de confirmación | Solo titular o rol autorizado |
| Guardar borrador | Campos modificados, versión de formulario, revisión del registro | Identificador de borrador, fecha, estado de validación | Control de sesión/token y CSRF según arquitectura |
| Confirmar actualización | Cambios, consentimiento, fecha observada, clave de idempotencia | Recibo de campaña y cambios auditados | Bloquear duplicados y escrituras concurrentes |
| Registrar llamada | Asignación, resultado, fecha y observación mínima | Evento agregado a bitácora de campaña | Rol temporal + caso asignado + vigencia |
| Crear solicitud de apoyo | Tipo, mensaje opcional, canal preferido | Ticket y estado con confirmación de recepción | Segregar contenido sensible |
| Consultar cruces | Filtros, filas, columnas, fecha de corte | Conteos, denominadores, faltantes, etiquetas de calidad | Autorización servidor, supresión complementaria |
| Crear intervención | Población elegible o solicitud, motivo, responsable y fecha | Plan en borrador para aprobación humana | Sin derivar datos individuales de agregados ocultos |
| Exportar | Filtros, columnas permitidas, finalidad | Archivo y log de exportación | Columnas por rol; no notas confidenciales |

### 10.1 Eventos y procedencia (reglas de datos)

- Todo cambio conserva **fecha efectiva del hecho**, **fecha de registro**, **fuente** (egresado/entrevistador/secretaría/importación), **actor**, **versión** y **validación**.
- No borrar episodios laborales al cambiar de trabajo: cerrar o dejar fecha desconocida del episodio anterior, y crear el siguiente; múltiples trabajos simultáneos son válidos.
- Una confirmación sin cambios **no** es un nuevo empleo: crear evento de confirmación/snapshot, no una copia sin control del episodio.
- Separar residencia y lugar de trabajo, usar UBIGEO verificado y señalar geocodificación incierta.
- Distinguir `No trabaja`, `No responde`, `Sin dato` y `Pendiente de verificar`; ninguna de estas categorías se convierte en otra por inferencia.
- La respuesta a «¿cómo estás?» **no equivale** a satisfacción laboral codificada. Preservar textos originales y su acceso reservado.
- `INICIAL` y `PRIMARIA_EIB` no pueden deducirse de la categoría académica `T. Bach.`; dejar `POR_VERIFICAR`.

### 10.2 Diseño de interfaz y ergonomía

- **Egresado (móvil):** tarjeta inicial de utilidad (cursos/oportunidades/apoyo), confirmación en cuatro pasos como máximo y posibilidad de finalizar pronto cuando no hay cambios, con microchequeo voluntario; estado de guardado visible.
- **Encuestador:** mismos campos y validaciones, pero con contexto de asignación, resultado de contacto, retorno a su lista y sin acceso a fichas ajenas ni al texto confidencial de SST.
- **Coordinación:** ficha editable con trazabilidad, bandeja de necesidades y tareas, planificador de visita/curso/encuentro, filtros guardables y directorio autorizado para contactar.
- **Dirección:** portada ejecutiva y **laboratorio libre** filas × columnas × filtros; comparación por promoción/territorio; selección de cortes; descarga con metodología; acciones institucionales autorizadas. No degradar esto a tarjetas KPI decorativas.
- **Estilo:** respetar primero diseño, componentes y navegación reales de la intranet; aplicar lenguaje gráfico editorial de infografías Pukllasunchis (crema/ocre/granate/verde) al reporte donde sea coherente. Confirmar logo y tipografías existentes. Sin números inventados en mockups productivos.
- **Accesibilidad:** móvil desde 360 px, etiquetas visibles, foco, estados de error, teclado, contraste, textos claros; no depender del color para comunicar estados.

### 10.3 Planes y políticas derivados de los cruces

Una decisión institucional debe quedar estructurada como: `pregunta_de_gestion → consulta_reproducible (filtros, periodo, fuente) → interpretación → población elegible/alcance → propuesta → aprobación humana → responsables/plazos → intervención → seguimiento → evaluación`.

### 10.4 Modelo de amenazas y permisos mínimos

- Riesgos: enumeración de cuentas, acceso horizontal por modificación de ID (IDOR), filtración por enlaces compartidos, XSS en notas, SQLi, exportación masiva, accesos administrativos excesivos, cruzamientos que reidentifican, logs con PII, borradores en equipos compartidos.
- Medidas: validación y autorización por objeto en backend, mínimo privilegio, permisos acumulables sin escalada, tokens aleatorios de alta entropía con hash en BD y canje expirable, protección de sesión, bitácoras de acceso, backups cifrados, eliminación/retención controlada, revisión de seguridad y pruebas negativas automatizadas.
- Un enlace WhatsApp no es prueba suficiente de identidad para mostrar datos. Validar el canal con segundo factor adecuado y una ruta segura para personas que cambiaron número.
- Datos de bienestar no deben ser exportables en informes generales; si existe un indicador de atención, agregarlo sin revelar contenido ni permitir reidentificación.
- Los controles de conteos pequeños no son sinónimo de anonimato absoluto; aplicar supresión secundaria y limitar filtros repetidos.

## 11. Matriz de trazabilidad y pruebas de aceptación

| ID | Requisito | Escenario E2E exigido | Evidencia de entrega |
|---|---|---|---|
| R01 | Perfil único | Importación repetida no duplica persona y conserva procedencia | Reporte de conciliación y caso de prueba |
| R02 | Formularios adaptativos | Sin cambios termina rápido; cambio de contrato despliega campos correctos | Video/capturas móvil + test automatizado |
| R03 | Escritura longitudinal | Cambio a desempleo conserva puesto anterior y corrige el indicador actual | Consulta antes/después + aserciones |
| R04 | Encuestador asignado | Llama/edita su caso; ID de otro caso obtiene 403 | Pruebas positivas y negativas |
| R05 | Campaña segura | Token usado/expirado/reutilizado no abre ficha sin verificación | Pruebas de token y protección de identidad |
| R06 | Solicitud SST | Genera ticket, responsable, seguimiento, cierre y reapertura | Estado registrado, permisos separados |
| R07 | Analítica | Filtros compuestos y cruces conservan selección y números exactos | Test SQL vs UI vs CSV/XLSX |
| R08 | Privacidad estadística | Conteos pequeños no recuperables de totales ni exportaciones | Test de supresión complementaria |
| R09 | Formación/visitas/eventos | Una necesidad origina actividad, inscripción/visita y evaluación | Historial acción y resultado |
| R10 | QA navegador | Recorridos completos para los 4 perfiles con interacción de botones | Reporte de pruebas navegador real |
| R11 | Reversibilidad | Migración en entorno de ensayo, respaldo y plan de reversión | Log de migración y rollback seguro |
| R12 | Accesibilidad | Sin overflow 360px; navegación por teclado y errores accesibles | Capturas y auditoría básica |

## 12. Secuencia operativa para Antigravity: Fase 0

1. Confirmar ruta, branch, `git status`, HEAD local/remoto y cambios no confirmados; no sobrescribir trabajo ajeno.
2. Identificar framework y versiones **reales**, esquema, migraciones y tablas, rutas, auth/RBAC, frontend, UI y pruebas; revisar `GrupoEntrevistador.php` si existe y generador de WhatsApp.
3. Revisa datos fuente mediante estructura, estadísticas de calidad y correspondencias, sin volcar nombres, DNI, teléfonos o notas personales en informes ni Git.
4. Contrasta los requisitos R01–R12 con evidencias del proyecto e identifica qué existe, qué se puede reutilizar y qué falta. Etiqueta CONFIRMADO, INFERIDO y PENDIENTE.
5. Redacta cuatro archivos: `00-auditoria-repositorio.md`, `01-mapa-datos.md`, `02-matriz-brechas.md` y `03-plan-incremental.md` en `docs/seguimiento-egresados/`.
6. Al concluir, entrega un resumen de hallazgos, riesgo técnico, propuesta de orden de trabajo y decisiones que necesitan aprobación. No modifiques tablas ni datos productivos; no ejecutes migraciones ni despliegues todavía.
