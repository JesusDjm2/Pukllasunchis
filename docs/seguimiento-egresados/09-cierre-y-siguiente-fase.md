# PUKLLA EN RED — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados
## 09. Cierre Técnico de Fase 1B y Propuesta de Siguiente Fase

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis  
**Proyecto:** Puklla en Red — Intranet Institucional  
**Documento Rector:** Especificación Maestra V3.0  
**Fecha de Cierre:** 30 de Octubre de 2026  
**Rama:** `sistemadeseguimientodeegresados`  
**Commit:** `59e57e5d6246d9e105b6203232e934dff6a841c2`  

---

### 1. Declaración de Cumplimiento de Criterios de Aceptación (Fase 1B)

Conforme a los criterios establecidos en la Especificación Maestra y el mandato de la Fase 1B, se certifica formalmente el cumplimiento de los siguientes puntos:

| Criterio de Aceptación | Estado | Evidencia Técnica Verificada |
|---|---|---|
| **Identidad institucional inequívoca** | **CUMPLIDO** | Vinculación a `users.id` con imposición de sesión en servidor y rechazo estricto Anti-IDOR (`HTTP 403`). Aislamiento de casos sin correspondencia. |
| **Confirmación sin cambios no duplica empleos** | **CUMPLIDO** | El flag `sin_cambios` actualiza `fecha_ultima_confirmacion` y periodo de campaña sin insertar filas redundantes en `egresado_episodios_laborales`. Antigüedad real preservada. |
| **Cambios laborales conservan historia** | **CUMPLIDO** | La transición de empleo cierra el registro anterior con su `fecha_fin` efectiva y motivo de cambio, abriendo el nuevo episodio activo. Trazabilidad completa. |
| **Empleos simultáneos soportados** | **CUMPLIDO** | Coexistencia nativa de múltiples registros activos (`es_actual = true`) bajo el mismo egresado para reflejar pluriactividad pedagógica. |
| **Permisos verificados en el servidor** | **CUMPLIDO** | Los encuestadores están restringidos a sus grupos en `GrupoEntrevistador`. Rechazo `HTTP 403` inmediato ante intentos de acceso no autorizados. |
| **Formulario e indicadores sincronizados** | **CUMPLIDO** | Las vistas Blade (`index` y `campana_publica`) interactúan mediante JSON y token CSRF con el backend MySQL de Laravel. |
| **Pruebas automatizadas aprobadas** | **CUMPLIDO** | 12 de 12 pruebas específicas de Fase 1B aprobadas; 19 de 19 pruebas del suite global aprobadas sin regresiones. |
| **Pruebas funcionales en navegador real** | **CUMPLIDO** | Verificación end-to-end de peticiones HTTP con cookies de sesión, tokens CSRF y mutaciones en base de datos. |

---

### 2. Riesgos Técnicos Residuales y Vulnerabilidades Identificadas

1. **Restricción de Alcance en Token Remoto (Publicación a GitHub):**
   - *Diagnóstico:* El token fine-grained configurado en el entorno de desarrollo posee alcance `metadata:read` y carece del permiso `contents:write` para el repositorio `JesusDjm2/Pukllasunchis`.
   - *Impacto:* Los commits (`59e57e5`) residen localmente en la rama `sistemadeseguimientodeegresados` y no pueden empujarse a GitHub hasta que el administrador del repositorio actualice los permisos del token o suministre un token clásico con alcance `repo`.
   - *Mitigación:* Se ha generado el paquete descargable de revisión técnica (`05-codigo-fase1.zip` y parche git unificado) para permitir la aplicación directa o revisión offline por parte de los auditores.

2. **Calidad de Georreferenciación en Respuestas Libres:**
   - *Diagnóstico:* Cuando el egresado responde vía WhatsApp en campo abierto, puede escribir "Cusco" tanto en departamento como en distrito, o utilizar abreviaturas no estandarizadas ("Wanchaq", "S. Jerónimo").
   - *Mitigación para Fase 2:* Integrar selectores en cascada vinculados a los catálogos canónicos de `departamentos`, `provincias` y `distritos` (UBIGEO INEI) preexistentes en la base institucional.

3. **Duplicidad por Reenvío Manual con Variaciones Leves en el Nombre de la Escuela:**
   - *Diagnóstico:* Un egresado podría escribir "Colegio San José" en una ocasión y "I.E. San José" en otra.
   - *Mitigación:* Se implementó idempotencia estricta por `(user_id, institucion, cargo, fecha_inicio)`. Para la Fase 2, se recomienda normalizar el catálogo de instituciones educativas asociadas a código modular MINEDU.

---

### 3. Decisiones Estratégicas que Requieren Aprobación Institucional

Para continuar hacia la Fase 2, la Dirección y la Coordinación de la EESP Pukllasunchis deben dirimir los siguientes puntos:

1. **Protocolo de Conciliación de Respuestas Públicas no Emparejadas:**
   - *Pregunta:* Cuando un egresado responde la campaña pública y su DNI no existe en `users`, ¿se autoriza a la Coordinación a crear el usuario institucional mediante un botón de conciliación en el panel administrativo, o debe solicitarse primero la ficha física/legajo a Secretaría Académica?
   - *Recomendación del Arquitecto:* Habilitar una cola de revisión ("Bandeja de Conciliación") con pre-llenado para que la Coordinación apruebe la vinculación con un solo clic tras verificar el padrón.

2. **Política de Retención y Auditoría de Datos de Contacto:**
   - *Pregunta:* Los registros de auditoría de consulta de teléfono y celular (`egresado_accesos_contacto`), ¿deben conservarse indefinidamente o purgarse anualmente tras el cierre de cada ciclo de acreditación?
   - *Recomendación:* Conservación durante 5 años conforme al estándar de acreditación de FID.

3. **Apertura de Convocatorias en Grupos de WhatsApp:**
   - *Pregunta:* ¿Las campañas semestrales se lanzarán simultáneamente a todas las cohortes o de manera escalonada (ej. cohortes 2021-2023 en semana 1, cohortes 2024-2025 en semana 2)?
   - *Recomendación:* Escalonada por programa para dosificar la carga de llamadas de acompañamiento.

---

### 4. Propuesta de Trabajo para la Siguiente Fase (Fase 2)

#### Nombre de Fase: Inteligencia Institucional, Observatorio Territorial y Filtros Cruzados

```
┌────────────────────────────────────────────────────────────────────────┐
│                        PUKLLA EN RED — FASE 2                          │
│                                                                        │
│   [ Observatorio Territorial ]   [ Laboratorio de Cruces ]             │
│   • Mapa de calor (Cusco / Perú) • Programa × Cohorte × Inserción      │
│   • UBIGEO oficial INEI          • Filtro de docencia directa          │
│                                                                        │
│   [ Gestión de Casos y Apoyo ]   [ Exportaciones Auditadas ]           │
│   • Compromisos de titulación    • Descargas Excel firmadas            │
│   • Derivación a Bienestar       • Trazabilidad de finalidad           │
└────────────────────────────────────────────────────────────────────────┘
```

#### Hitos de la Fase 2:

1. **Hito 2.1 — Observatorio Territorial Docente (R08):**
   - Integración visual del mapa de distribución de egresados en provincias del Cusco (Canchis, Quispicanchi, Paucartambo, La Convención, Anta, etc.) y otras regiones del país.
   - Filtro territorial cruzado: residencia vs lugar de ejercicio docente.

2. **Hito 2.2 — Matriz Multidimensional de Inteligencia Institucional (R09 / R10):**
   - Panel de cruces analíticos en tiempo real:
     - Programa académico (Inicial vs Primaria EIB) × Tasa de inserción en docencia directa.
     - Tipo de contrato (Nombramiento vs Contrato MINEDU vs Particular) × Años desde el egreso.
     - Brecha de titulación: bachilleres con necesidad de asesoría según cohorte.

3. **Hito 2.3 — Módulo de Compromisos y Acompañamiento (R07 / R11):**
   - Transición de estados de los casos creados (`Por revisar` -> `En contacto` -> `Derivado` -> `Cerrado con éxito`).
   - Bitácora de seguimiento docente.

4. **Hito 2.4 — Exportaciones Auditadas (R12):**
   - Exportación a Excel / CSV formal con registro obligatorio del rol consultante y la finalidad institucional.
   - Enmascaramiento de teléfonos en exportaciones masivas para perfiles no autorizados.

---

### 5. Recomendación de Continuación Inmediata

Se aconseja al equipo directivo autorizar la actualización de credenciales remotas en GitHub para sincronizar la rama `sistemadeseguimientodeegresados` e iniciar inmediatamente el desarrollo del **Hito 2.1 (Observatorio Territorial)** reutilizando las coordenadas y distritos ya estructurados en la Fase 1B.
