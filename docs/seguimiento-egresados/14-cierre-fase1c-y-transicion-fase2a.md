# PUKLLA EN RED — FASE 1C
## Informe de Cierre Técnico y Transición a la Fase 2A

**Proyecto:** Puklla en Red — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis  
**Fase Completada:** Fase 1C (Seguridad, Integridad Histórica y Consolidación de Fase 1B)  
**Rama:** `sistemadeseguimientodeegresados`  
**Fecha:** 30 de Octubre de 2026  

---

### 1. Resumen de Logros Técnicos de la Fase 1C

La Fase 1C ha subsanado de manera concluyente las brechas identificadas en la auditoría técnica, asegurando que:
1. **Seguridad y Control de Acceso Estricto (Anti-IDOR):**
   - Todas las rutas de intranet requieren sesión autenticada (`auth` middleware).
   - Los egresados únicamente pueden visualizar y modificar su propio expediente institucional.
   - Los encuestadores solo pueden interactuar con los egresados asignados expresamente en grupos activos.
   - Las operaciones de administración (creación/eliminación de grupos y avance de casos) están reservadas con código HTTP 403 para Coordinación y Dirección.
2. **Invitaciones Criptográficas Seguras para WhatsApp:**
   - Despacho de tokens de un solo uso (`INV-` + 48 caracteres aleatorios).
   - Eliminación total de datos personales (DNI, nombres, user_id) en los enlaces públicos compartidos.
   - Registro de consumo con fecha, IP y expiración automática.
3. **Inmunidad Anti-Suplantación (Anti-Spoofing en Web Pública):**
   - Cualquier envío anónimo que intente mutar una ficha conociendo solo un DNI es retenido en una cola de verificación aislada (`estado_verificacion = 'pendiente_verificacion'`).
   - Cero mutación en episodios laborales activos legítimos.
   - Creación automática de casos de validación telefónica para el equipo de seguimiento.
4. **Persistencia Longitudinal Multicampaña:**
   - Las respuestas de cada campaña semestral (2025-II, 2026-I, 2026-II) se almacenan en instantáneas independientes (`periodo_campana`), evitando sobreescrituras destructivas.
   - Bloqueo pesimista de base de datos (`lockForUpdate`) para evitar condiciones de carrera por dobles clics o envíos simultáneos.
5. **Integridad Epistémica de Fechas y Atributos:**
   - Cero invención de fechas con `now()`; las fechas de inicio desconocidas se persisten fielmente como `NULL`.
   - Cero inferencia de docencia directa o empleo educativo sin confirmación expresa del egresado.
6. **Batería de Pruebas Automatizadas:**
   - 20 pruebas de integración y seguridad ejecutadas y aprobadas al 100% (114 aserciones) en PHPUnit.

---

### 2. Riesgos Residuales y Mitigaciones en Operación

| Riesgo Residual | Impacto | Mitigación Implementada | Medida de Monitoreo Recomendada |
|:---|:---|:---|:---|
| **Expiración de tokens WhatsApp no atendidos** | Bajo | Los tokens se generan con vigencia de 30 días. Si expiran, el egresado puede solicitar un nuevo enlace o completar el registro público en cola de validación. | Revisión mensual de tokens no consumidos en `egresado_invitaciones`. |
| **Volumen de solicitudes en cola de verificación** | Bajo-Medio | Las peticiones públicas sin token generan casos `VERIF-*` asignados a Coordinación para validar telefónicamente. | Monitorear el panel de casos para evitar acumulación de pendientes. |
| **Egresados con cambios de número telefónico** | Medio | En la confirmación semestral se solicita validar el teléfono y WhatsApp preferido, actualizando el contacto principal. | Capacitación a entrevistadores para confirmar número alternativo. |

---

### 3. Decisiones Institucionales Requeridas

1. **Aprobación de la Política de Invitaciones WhatsApp:** Confirmar el texto estándar del mensaje generado institucionalmente por la intranet para el envío a los egresados.
2. **Definición del Flujo de Conciliación en Cuarentena:** Aprobar el procedimiento mediante el cual un coordinador valida una solicitud abierta en `pendiente_verificacion` y la promueve a `verificado`.
3. **Autorización para Migración en Entorno Staging / Pruebas:** Autorizar la ejecución de la migración `2026_10_30_130000_enhance_fase1c_security_and_longitudinal_history.php` en el servidor de pruebas previo al pase a producción.

---

### 4. Propuesta de Continuación: Fase 2A (Laboratorio de Inteligencia y Analítica Institucional)

Habiendo garantizado la persistencia longitudinal y la seguridad de las fuentes de datos (R01, R02, R03, R04), el sistema está plenamente habilitado para abordar el **Requisito R10 (Inteligencia Institucional)**.

#### Objetivos Clave de la Fase 2A:
1. **Panel Analítico de Métricas Clave:**
   - Tasa de inserción laboral en educación vs. otros sectores.
   - Porcentaje de egresados en docencia directa de aula y nivel educativo (Inicial, Primaria EIB).
   - Indicadores de estabilidad y régimen contractual (MINEDU nombrado, contratado, particular).
2. **Comparativas Longitudinales entre Cohortes:**
   - Evolución de la inserción laboral al 1°, 2° y 3° año de egreso.
   - Tendencias en titulación oportuna y necesidades de asesoría académica.
3. **Mapeo Geográfico y Movilidad Profesional:**
   - Visualización de concentración geográfica por departamentos, provincias y distritos (Cusco, regiones vecinas y zonas rurales EIB).
4. **Filtros Cruzados Multidimensionales:**
   - Filtros instantáneos por programa, año de cohorte, lengua quechua, práctica EIB y estado de titulación sin recarga de página.
