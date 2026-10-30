# PUKLLA EN RED — Informe de Entrega Técnica: Fase 1B
## Persistencia Longitudinal y Actualización Rápida de Egresados

**Proyecto:** Puklla en Red — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis  
**Rama:** `sistemadeseguimientodeegresados`  
**Fecha:** 30 de Octubre de 2026  
**Estado:** IMPLEMENTADO Y VERIFICADO (12/12 Pruebas Automatizadas Aprobadas)

---

### 1. Resumen Ejecutivo de la Fase 1B

En cumplimiento estricto de la **Especificación Maestra V3.0** y los acuerdos de la Fase 0, se ha culminado la implementación controlada de la **Fase 1B**, resolviendo los tres requisitos funcionales y de datos fundamentales:

1. **R01 (Integridad de Identidad y Anti-IDOR en Servidor):**
   - La vinculación de cualquier actualización a una persona se realiza inequívocamente a través de `users.id`.
   - Para egresados autenticados, se fuerza `auth()->id()`, impidiendo que un egresado modifique registros ajenos (`HTTP 403`).
   - Para entrevistadores con rol `encuestador-seguimiento`, se verifica en servidor que exista una asignación activa y válida en `GrupoEntrevistador` (`HTTP 403` si intenta consultar o actualizar un caso no asignado).
   - En solicitudes públicas (Campaña WhatsApp), se valida la existencia previa en `users` o se mantiene en aislamiento temporal (`identificador_temporal`) sin crear usuarios fantasmas en la tabla maestra.

2. **R02 (Confirmación Rápida «Sin Cambios» en 1 Clic):**
   - Cuando un egresado o encuestador confirma que la situación laboral y académica se mantiene vigente, el sistema **no duplica episodios laborales** existentes.
   - Preserva la fecha real de inicio del empleo (`fecha_inicio`), actualizando de manera diferenciada la fecha de confirmación semestral (`fecha_ultima_confirmacion`) y el periodo de campaña (`periodo_campana = '2026-II'`).
   - Captura el microchequeo institucional opcional (satisfacción profesional, demandas formativas y solicitudes de acompañamiento prioritario).
   - Si se solicita apoyo prioritario, abre automáticamente un compromiso en `egresado_casos` para la Coordinación de Empleabilidad.

3. **R03 (Persistencia Longitudinal de Trayectorias):**
   - Implementación 100% transaccional (`DB::transaction`) con protección ACID.
   - Soporte para primer empleo, transiciones de puesto o institución (conservando el historial anterior con `fecha_fin`), cierre de episodios específicos y coexistencia de empleos simultáneos (`es_actual = true` en múltiples registros).
   - Cierre de empleo con fecha de finalización desconocida: se preserva `fecha_fin = NULL` en lugar de inventar la fecha de hoy.
   - Declaración de término o desempleo: no crea empleadores ficticios ("Desempleado"); simplemente concluye los episodios activos correspondientes.
   - Idempotencia garantizada ante reenvíos repetidos (doble clic o reintentos de red).

---

### 2. Archivos Modificados y Creados

| Archivo | Tipo | Justificación Técnica |
|---|---|---|
| `database/migrations/2026_10_30_120000_enhance_seguimiento_egresados_tables.php` | Migración Aditiva | Agrega campos aditivos a `egresado_episodios_laborales` (`es_actual`, `sector`, geocodificación UBIGEO, `dedicacion`, `motivo_cambio`), agrega `fecha_ultima_confirmacion` y `es_confirmacion_sin_cambios` a `egresado_actualizaciones`, y registra roles Spatie institucionales. Totalmente reversible. |
| `app/Models/SeguimientoEgresado/EpisodioLaboral.php` | Modelo Eloquent | Incorpora scopes `activos()`, `historicos()`, `educativos()`, `docenciaDirecta()`, casts de fecha y método seguro de cierre `cerrarEpisodio()`. |
| `app/Models/SeguimientoEgresado/Actualizacion.php` | Modelo Eloquent | Incorpora casts de confirmación semestral y relaciones `episodiosLaborales()` y `episodiosActivos()`. |
| `app/Models/SeguimientoEgresado/GrupoEntrevistador.php` | Modelo Eloquent | Incorpora método estático de autorización `esEntrevistadorAsignado(int $interviewerId, int $targetUserId): bool`. |
| `app/Http/Controllers/SeguimientoEgresadoController.php` | Controlador | Reescritura integral de `guardarActualizacion()` con lógica transaccional ACID, R01, R02, R03; endpoints `cerrarEpisodio()` y `obtenerEpisodios()`. |
| `routes/web.php` | Rutas | Registro de rutas seguras para episodios y cierre controlado de empleos. |
| `resources/views/seguimiento_egresados/index.blade.php` | Vista Blade | Integración de `$episodiosDb`, tarjeta de confirmación rápida R02, panel de trayectoria longitudinal, modales de cierre y soporte en `ficha()`. |
| `resources/views/seguimiento_egresados/campana_publica.blade.php` | Vista Blade | Integración de opción rápida de reconfirmación sin cambios para campañas por WhatsApp. |
| `tests/Feature/SeguimientoEgresadosFase1BTest.php` | Suite PHPUnit | 12 pruebas de integración automatizadas que certifican los requisitos R01, R02 y R03. |

---

### 3. Matriz de Pruebas Automatizadas (PHPUnit)

Ejecución realizada mediante `php artisan test --filter=SeguimientoEgresadosFase1BTest`:

```text
   PASS  Tests\Feature\SeguimientoEgresadosFase1BTest
  ✓ 01 primer episodio laboral                                           0.10s  
  ✓ 02 cambio de empleo con conservacion de historial                    0.04s  
  ✓ 03 finalizacion de empleo sin crear empleador ficticio               0.03s  
  ✓ 04 confirmacion sin cambios no duplica episodios                     0.04s  
  ✓ 05 empleos simultaneos                                               0.04s  
  ✓ 06 fecha de finalizacion desconocida                                 0.03s  
  ✓ 07 reenvio identico idempotente no duplica episodios                 0.04s  
  ✓ 08 rechazo de identificador de usuario invalido                      0.03s  
  ✓ 09 rechazo acceso egresado a otra ficha                              0.03s  
  ✓ 10 rechazo http 403 encuestador caso no asignado                     0.04s  
  ✓ 11 rollback ante error transaccional                                 0.03s  
  ✓ 12 consistencia entre episodios y estado laboral                     0.04s  

  Tests:    12 passed (57 assertions)
  Duration: 0.93s
```

Total del proyecto: **19 passed (69 assertions)**.

---

### 4. Comparación Antes / Después en Base de Datos

#### Caso A: Confirmación Rápida «Sin Cambios» (R02)
- **Antes (enfoque plano tradicional):**
  - Cada confirmación creaba una nueva fila en la tabla de empleos, duplicando la institución y sobreescribiendo `fecha_inicio` con la fecha de hoy, destruyendo la antigüedad del egresado.
- **Ahora (Puklla en Red Fase 1B):**
  - `egresado_episodios_laborales`: Mantiene **exactamente 1 fila**, preservando `fecha_inicio = '2024-03-01'` y `es_actual = 1`.
  - `egresado_actualizaciones`: Actualiza `fecha_ultima_confirmacion = '2026-10-30 11:34:30'`, `es_confirmacion_sin_cambios = 1`, `periodo_campana = '2026-II'`. Cero duplicados.

#### Caso B: Transición de Empleo (R03)
- **Antes:**
  - El nuevo empleo sobreescribía los campos del empleo anterior; la institución anterior desaparecía.
- **Ahora:**
  - Episodio 1: `I.E. 50012` queda con `es_actual = 0`, `fecha_fin = '2024-12-31'`, `motivo_cambio = 'Promoción'`.
  - Episodio 2: `Colegio San José` se crea con `es_actual = 1`, `fecha_inicio = '2025-03-01'`, `fecha_fin = NULL`.
  - Se visualiza la trayectoria histórica completa y la tasa de movilidad laboral.

#### Caso C: Desempleo o Búsqueda Laboral
- **Antes:**
  - Se creaba una fila con `institucion = 'Desempleado'` o `institucion = 'No trabaja'`.
- **Ahora:**
  - Se cierran los empleos activos (`es_actual = 0`).
  - No se genera ninguna fila artificial. La consulta de egresados buscando empleo se realiza mediante `whereDoesntHave('episodiosActivos')` y `actualizaciones.trabaja = 'No trabaja'`.

---

### 5. Análisis de Riesgos Residuales y Mitigaciones

| Riesgo Identificado | Severidad | Mitigación Implementada |
|---|---|---|
| **Egresados con múltiples números no actualizados en WhatsApp** | Media | El generador de enlaces institucionales permite a la coordinación compartir el formulario público `campana-publica` en grupos de cohorte, validando el DNI en servidor. |
| **Concurrencia en llamadas de entrevistadores simultáneos** | Baja | `DB::transaction()` con bloqueo a nivel de fila y chequeo de asignación en `GrupoEntrevistador`. |
| **Omisión de fechas de inicio en empleos antiguos** | Baja | Si el egresado no recuerda el día exacto, el modelo permite almacenar la fecha de captura y conserva `fecha_inicio` aproximada o nula según corresponda sin romper el flujo. |

---

### 6. Procedimiento de Reversión y Recuperación

Si por alguna razón operacional se requiriera revertir los cambios de esta fase:

1. **Reversión de la migración de base de datos:**
   ```bash
   php artisan migrate:rollback --step=1
   ```
   *Verificado: la migración elimina ordenadamente las columnas agregadas sin tocar las tablas preexistentes.*

2. **Reversión de código en Git:**
   ```bash
   git restore app/ resources/ routes/
   ```

3. **Restauración de pruebas:**
   ```bash
   php artisan test
   ```
