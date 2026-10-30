# PUKLLA EN RED — FASE 1C
## Procedimiento Seguro de Migración y Reversión Defensiva

**Módulo:** Fase 1C (Seguridad, Invitaciones Seguras e Historia Longitudinal)  
**Archivo de Migración:** `database/migrations/2026_10_30_130000_enhance_fase1c_security_and_longitudinal_history.php`  
**Entorno:** MySQL 8.0 / InnoDB / Laravel 10.48.29  
**Estado:** Aplicada con éxito en desarrollo local (`sistemadeseguimientodeegresados`)  

---

### 1. Estructura y Justificación de los Cambios

La migración de la Fase 1C es **estrictamente aditiva y no disruptiva**:
- No renombra ni elimina columnas preexistentes.
- No destruye registros de egresados, alumnos, actualizaciones ni episodios laborales previos.
- Utiliza comprobaciones de existencia de esquemas (`Schema::hasTable`, `Schema::hasColumn`) para garantizar idempotencia en caso de ejecuciones parciales.

#### A. Tabla Nueva: `egresado_invitaciones`
Permite el despacho y validación de tokens criptográficos de un solo uso para campañas de WhatsApp o correos de recordatorio sin exponer datos personales (PII) en enlaces públicos:
```sql
CREATE TABLE `egresado_invitaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `token` varchar(64) NOT NULL UNIQUE,
  `user_id` bigint unsigned NULL,
  `identificador_temporal` varchar(50) NULL,
  `campana` varchar(30) NOT NULL DEFAULT '2026-II',
  `canal` varchar(40) NOT NULL DEFAULT 'whatsapp',
  `generado_por` bigint unsigned NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_expiracion` timestamp NULL,
  `fecha_uso` timestamp NULL,
  `ip_uso` varchar(45) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  INDEX `egresado_invitaciones_user_id_index` (`user_id`),
  INDEX `egresado_invitaciones_identificador_temporal_index` (`identificador_temporal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### B. Columnas Aditivas en `egresado_actualizaciones`
Incorpora trazabilidad de auditoría, diferenciación de tipos de respuesta y cuarentena de seguridad:
- `tipo_registro` (varchar 40, nullable, default `'respuesta_campana'`): Distingue entre `'confirmacion_sin_cambios'`, `'respuesta_campana'`, `'correccion_administrativa'` y `'solicitud_sin_token'`.
- `estado_verificacion` (varchar 40, nullable, default `'verificado'`): Distingue entre registros `'verificado'` (intranet o con token) y registros aislados `'pendiente_verificacion'` (público abierto sin token).
- `procedencia` (varchar 80, nullable): Registra el canal exacto (`'intranet_autogestion'`, `'intranet_entrevistador'`, `'publico_whatsapp_invitacion'`, `'publico_whatsapp_no_verificado'`).
- `token_invitacion` (varchar 64, nullable, index): Token criptográfico consumido al responder.
- `ip_registro` (varchar 45, nullable): Dirección IP de captura para auditoría de seguridad.

---

### 2. Procedimiento de Ejecución (Despliegue)

> [!IMPORTANT]
> **REGLA INSTITUCIONAL:** No ejecutar migraciones en el servidor de producción sin previa autorización de la Dirección y sin respaldo íntegro de la base de datos `puklla.sql`.

#### Paso 1: Verificación de estado previo
```bash
php artisan migrate:status
```
Debe comprobarse que la migración `2026_10_30_120000_enhance_seguimiento_egresados_tables` figure como ejecutada.

#### Paso 2: Respaldo de seguridad (Dump preventivo)
```bash
mysqldump -u <usuario> -p <nombre_bd> \
  egresado_actualizaciones \
  egresado_episodios_laborales \
  users > backup_pre_fase1c_$(date +%Y%m%d_%H%M%S).sql
```

#### Paso 3: Ejecución de la migración
```bash
php artisan migrate --path=database/migrations/2026_10_30_130000_enhance_fase1c_security_and_longitudinal_history.php
```

#### Paso 4: Verificación posterior
```bash
php artisan tinker --execute="echo json_encode(Illuminate\Support\Facades\Schema::getColumnListing('egresado_actualizaciones'));"
```

---

### 3. Procedimiento Seguro de Reversión (Rollback Defensivo)

El método `down()` de la migración ha sido programado de manera **defensiva**:
- Elimina únicamente las columnas aditivas de la Fase 1C (`tipo_registro`, `estado_verificacion`, `procedencia`, `token_invitacion`, `ip_registro`).
- Elimina la tabla `egresado_invitaciones`.
- **NO toca** las tablas preexistentes `users`, `alumnos`, `docentes` ni los registros acumulados en `egresado_episodios_laborales`.

#### Ejecución del Rollback
```bash
php artisan migrate:rollback --step=1
```

#### Comprobación de integridad tras reversión
```bash
php artisan tinker --execute="echo App\Models\SeguimientoEgresado\EpisodioLaboral::count();"
```
El número de episodios laborales debe permanecer exactamente igual antes y después de cualquier reversión.
