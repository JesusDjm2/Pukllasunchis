# 02 — Matriz de Brechas frente a Requisitos de Aceptación (R01–R12)
## PUKLLA EN RED · EESP Pukllasunchis

**Criterios de clasificación:**
- **`[CONFIRMADO]`**: Implementado en código real del repositorio, probado y respaldado por rutas, modelos o datos.
- **`[INFERIDO]`**: Presente en prototipo, interfaz o esquema parcial, pero requiere endurecimiento en backend o reglas de seguridad.
- **`[PENDIENTE]`**: Aún no implementado en backend, pruebas automatizadas o flujos de producción.

---

### Matriz Detallada de Trazabilidad R01–R12

| ID | Requisito del Documento Maestro | Estado | Evidencia en el Repositorio | Brecha Técnica a Resolver |
|---|---|---|---|---|
| **R01** | **Perfil único:** Importación repetida no duplica persona y conserva procedencia. Llave institucional inmutable. | `[CONFIRMADO]` | Tabla `users` (401 egresados, PK `id`, DNI único, `condicion = 'Egresado'`). Relaciones en `egresado_actualizaciones.user_id`. | Crear comando o seeder de conciliación formal de las "Siete Listas" con reporte de duplicados y sin sobrescribir llaves. |
| **R02** | **Formularios adaptativos:** Si "no cambió nada" termina rápido; cambio laboral despliega campos correctos. | `[CONFIRMADO]` | UI en `seguimiento_egresados/index.blade.php` (función `actualizacionAgil`) y `campana_publica.blade.php`. | Implementar en el backend el guardado abreviado sin crear nuevo episodio laboral duplicado. |
| **R03** | **Escritura longitudinal:** Cambio a desempleo conserva puesto anterior y corrige el indicador actual. | `[INFERIDO]` | Modelo `EpisodioLaboral.php` y tabla `egresado_episodios_laborales` tienen `fecha_inicio`, `fecha_fin`. | Endpoint `actualizar` guarda actualmente en `egresado_actualizaciones`; falta enganchar el cierre formal del episodio en `egresado_episodios_laborales`. |
| **R04** | **Encuestador asignado:** Llama/edita solo su caso asignado; ID de otro caso obtiene 403. | `[INFERIDO]` | `egresado_grupos_entrevistadores` y `egresado_llamadas` creados. `GrupoEntrevistador.php` activo. | La autorización estricta en servidor (middleware/policy de Laravel que retorne HTTP 403 si el ID no está en `egresados_ids` del encuestador). |
| **R05** | **Campaña segura:** Token WhatsApp no revela ficha por simple nombre/promoción; caducidad y verificación. | `[INFERIDO]` | `campana_publica.blade.php` es autónomo. Flujo de verificación de 6 dígitos simulado en JS. | Migrar a token criptográfico aleatorio firmado (HMAC / temporal) con hash en BD y verificación por SMS/WhatsApp o PIN personal. |
| **R06** | **Solicitud SST (Bienestar):** Genera ticket, responsable, seguimiento, cierre y reapertura. | `[CONFIRMADO]` | Modelo `Caso.php`, tabla `egresado_casos`, rutas `/accion` y `/avanzar-accion/{id}`. | Añadir segregación estricta de notas confidenciales (solo rol `bienestar` / coordinación) y tabla `egresado_caso_acciones` para historial. |
| **R07** | **Analítica y Laboratorio Libre:** Filtros compuestos y cruces conservan selección y números exactos. | `[INFERIDO]` | Módulo analítico integrado en JavaScript en `seguimiento_egresados/index.blade.php` con tablas cruzadas y filtros. | Implementar endpoint backend `/seguimiento-egresados/analitica` para recalcular dinámicamente sobre la base real MySQL con cortes reproducibles. |
| **R08** | **Privacidad estadística:** Conteos pequeños (<5) no recuperables de totales ni exportaciones. | `[PENDIENTE]` | Actualmente las tablas muestran conteos directos sin supresión. | Implementar algoritmo de supresión primaria y complementaria (regla de dominancia/celdas <5) en reportes y exportaciones XLSX/CSV. |
| **R09** | **Formación / Visitas / Eventos:** Una necesidad origina actividad, inscripción/visita y evaluación. | `[INFERIDO]` | Pestaña "Formación continua" clasifica demandas en UI; tabla `bolsa_trabajo_ofertas` existe en BD. | Crear entidades formales `cursos_ofertas`, `visitas_acompanamiento` y relacionarlas con las demandas declaradas por egresados. |
| **R10** | **QA Navegador Real:** Recorridos completos para los 4 perfiles con interacción de botones y sin errores. | `[INFERIDO]` | Probado con curl y scripts Python en servidor local (HTTP 200). Visualizador interactivo disponible. | Configurar suite de pruebas automatizadas (Laravel Dusk o Pest / PHPUnit Feature Tests) que simulen el navegador real. |
| **R11** | **Reversibilidad y Entorno Seguro:** Migración en entorno de ensayo, respaldo y rollback seguro. | `[CONFIRMADO]` | Migraciones Laravel creadas con método `down()` reversible (`dropIfExists`). Base MySQL local aislada. | Generar procedimiento documentado de respaldo `mysqldump` previo a cualquier ejecución en servidor institucional. |
| **R12** | **Accesibilidad y Ergonomía Móvil:** Sin desborde en 360px; navegación teclado y contraste. | `[CONFIRMADO]` | `campana_publica.blade.php` diseñado mobile-first responsivo con viewport configurado y estilos fluidos. | Auditoría Lighthouse / WCAG AA de contraste y accesibilidad de teclado en formularios. |

---

### Resumen de Cumplimiento Diagnóstico:
- **Confirmados:** 5 / 12 (42%)
- **Inferidos / Por Consolidar:** 6 / 12 (50%)
- **Pendientes:** 1 / 12 (8%)
