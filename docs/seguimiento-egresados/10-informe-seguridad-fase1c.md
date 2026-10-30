# PUKLLA EN RED — FASE 1C
## Informe de Auditoría y Vulnerabilidades Corregidas

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis  
**Proyecto:** Puklla en Red — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Módulo:** Fase 1C (Seguridad, Integridad Histórica y Anti-Suplantación)  
**Rama Git:** `sistemadeseguimientodeegresados`  
**Entorno Tecnológico:** Laravel 10.48.29, PHP 8.2.28, MySQL 8.0, Eloquent ORM, Spatie Laravel Permission  
**Fecha de Emisión:** 30 de Octubre de 2026  

---

### Resumen Ejecutivo

En cumplimiento de la auditoría técnica posterior a la Fase 1B, se procedió a consolidar los mecanismos de seguridad, autorización, integridad histórica y prevención de fraude en la intranet institucional y los canales públicos de captura de datos (WhatsApp / enlace público).

Se eliminaron **seis brechas críticas de seguridad y persistencia**, implementando mecanismos de defensa en profundidad tanto a nivel de middleware HTTP como en las capas de controlador, modelo Eloquent y transacciones de base de datos con bloqueo pesimista (`lockForUpdate`).

---

### Matriz de Vulnerabilidades y Remediaciones

| ID | Componente / Ruta | Método Afectado | Brecha Identificada | Solución Implementada | Estado |
|:---|:---|:---|:---|:---|:---|
| **SEC-01** | `routes/web.php` | Rutas de intranet `/seguimiento-egresados/*` | **Falta de middleware de autenticación**: Cualquier usuario anónimo podía acceder a la vista general, llamadas, grupos y episodios. | Agrupación bajo `Route::middleware(['auth'])`. Acceso denegado con HTTP 401 a peticiones sin sesión. | **SUBSANADO** |
| **SEC-02** | `SeguimientoEgresadoController.php` | `guardarActualizacion()` | **Suplantación de identidad en formulario público**: Un usuario externo con solo conocer el DNI podía modificar un expediente y cerrar empleos legítimos. | Aislamiento automático en cola de verificación (`pendiente_verificacion`, `solicitud_sin_token`). Cero mutación en episodios activos de terceros. Apertura de caso institucional para validación telefónica. | **SUBSANADO** |
| **SEC-03** | `SeguimientoEgresadoController.php` | `generarEnlaceWhatsapp()` y `campanaPublica()` | **Exposición de datos personales (PII) en enlaces**: Riesgo de incluir DNI o IDs en parámetros de URLs compartidas. | Creación de tokens criptográficos opacos en `egresado_invitaciones` (`INV-` + 48 caracteres aleatorios). URLs sin DNI ni user_id. Validación de expiración y un solo uso. | **SUBSANADO** |
| **SEC-04** | `SeguimientoEgresadoController.php` | `guardarActualizacion()` | **Destrucción de historial longitudinal multicampaña**: `updateOrCreate` solo filtraba por `user_id`, sobreescribiendo respuestas de semestres anteriores. | Búsqueda scoped por `['user_id' => $targetUserId, 'periodo_campana' => $campana]`. Cada campaña semestral (2025-II, 2026-I, 2026-II) conserva su instantánea independiente. | **SUBSANADO** |
| **SEC-05** | `SeguimientoEgresadoController.php` | `guardarActualizacion()` | **Asignación inventada de fecha `now()` a empleos pasados**: `$fechaInicio` tomaba `now()->toDateString()` si el dato no era suministrado. | Corrección a `$fechaInicio = $request->input('inicio_empleo') ?: null`. Preservación de `NULL` estricto en la base de datos. | **SUBSANADO** |
| **SEC-06** | `SeguimientoEgresadoController.php` | `guardarActualizacion()` | **Inferencia no justificada de docencia directa y sector educativo**: Se asumían valores afirmativos ante datos ausentes. | Evaluación condicional estricta. Solo se marca como educativo o docencia si existe declaración expresa del egresado. | **SUBSANADO** |
| **SEC-07** | `SeguimientoEgresadoController.php` | `obtenerEpisodios()` y `cerrarEpisodio()` | **Vulnerabilidad IDOR (Insecure Direct Object Reference)**: Un egresado podía inspeccionar o cerrar el trabajo de otro egresado. | Verificación en servidor mediante `puedeAccederAEgresado()`. Egresados solo acceden a sus propios registros; encuestadores solo a egresados asignados en grupos activos. HTTP 403 ante violaciones. | **SUBSANADO** |
| **SEC-08** | `SeguimientoEgresadoController.php` | `guardarGrupo()`, `eliminarGrupo()`, `avanzarAccion()` | **Falta de segregación de privilegios de gestión**: Roles no autorizados podían mutar grupos o estados de casos. | Verificación de rol en servidor (`esPersonalGestion()`). Exclusivo para Coordinación de Empleabilidad, Administración y Dirección. HTTP 403 para roles no autorizados. | **SUBSANADO** |

---

### Detalle Técnico de las Modificaciones

#### 1. Protección de Rutas (`routes/web.php`)
```php
// Rutas públicas throttled
Route::prefix('seguimiento-egresados')->group(function () {
    Route::get('/campana-publica', [SeguimientoEgresadoController::class, 'campanaPublica'])
        ->middleware('throttle:60,1')
        ->name('seguimiento-egresados.campana-publica');
    Route::post('/campana-publica/actualizar', [SeguimientoEgresadoController::class, 'guardarActualizacion'])
        ->middleware('throttle:30,1')
        ->name('seguimiento-egresados.campana-publica.actualizar');
});

// Rutas de intranet protegidas con sesión obligatoria
Route::middleware(['auth'])->prefix('seguimiento-egresados')->group(function () {
    Route::get('/', [SeguimientoEgresadoController::class, 'index'])->name('seguimiento-egresados.index');
    Route::get('/episodios/{userId}', [SeguimientoEgresadoController::class, 'obtenerEpisodios'])->name('seguimiento-egresados.episodios');
    Route::post('/episodios/{id}/cerrar', [SeguimientoEgresadoController::class, 'cerrarEpisodio'])->name('seguimiento-egresados.episodios.cerrar');
    Route::post('/llamada', [SeguimientoEgresadoController::class, 'guardarLlamada'])->name('seguimiento-egresados.llamada');
    Route::post('/auditar-contacto', [SeguimientoEgresadoController::class, 'auditarContacto'])->name('seguimiento-egresados.auditar-contacto');
    Route::get('/enlace-whatsapp', [SeguimientoEgresadoController::class, 'generarEnlaceWhatsapp'])->name('seguimiento-egresados.enlace-whatsapp');
    Route::post('/accion', [SeguimientoEgresadoController::class, 'guardarAccion'])->name('seguimiento-egresados.accion');
    Route::post('/avanzar-accion/{id}', [SeguimientoEgresadoController::class, 'avanzarAccion'])->name('seguimiento-egresados.avanzar-accion');
    Route::post('/grupos', [SeguimientoEgresadoController::class, 'guardarGrupo'])->name('seguimiento-egresados.grupos.guardar');
    Route::delete('/grupos/{id}', [SeguimientoEgresadoController::class, 'eliminarGrupo'])->name('seguimiento-egresados.grupos.eliminar');
});
```

#### 2. Mecanismo de Invitación Criptográfica y Anti-Suplantación
- Se creó la entidad `InvitacionCampana` vinculada a la tabla `egresado_invitaciones`.
- Al generar un enlace para WhatsApp, se produce un token opaco tipo `INV-` + 48 caracteres aleatorios mediante `Str::random(48)`.
- La URL final para el usuario es:
  `https://intranet.pukllasunchis.edu.pe/seguimiento-egresados/campana-publica?token=INV-xxxxxxxxxxxxxxxxxxxxxxxx`
  **Cero DNI, cero user_id, cero datos de contacto expuestos.**
- Cuando una petición pública llega sin token pero indicando un DNI:
  - Se clasifica como `tipo_registro = 'solicitud_sin_token'`, `estado_verificacion = 'pendiente_verificacion'`, `procedencia = 'publico_whatsapp_no_verificado'`.
  - El expediente consolidado y los episodios laborales activos del egresado institucional permanecen **100% inalterados**.
  - Se emite automáticamente un `Caso` institucional asignado a la Coordinación de Empleabilidad para verificación manual telefónica.

#### 3. Preservación Longitudinal Multicampaña
- Se refactorizó la llamada a `updateOrCreate` en `egresado_actualizaciones`:
```php
$actualizacion = Actualizacion::updateOrCreate(
    [
        'user_id' => $targetUserId,
        'periodo_campana' => $campana, // Aislamiento semestral
    ],
    [
        'nombre_completo' => $nombre,
        'tipo_registro' => $isSinCambios ? 'confirmacion_sin_cambios' : 'respuesta_campana',
        'estado_verificacion' => $estadoVerificacion,
        'procedencia' => $procedencia,
        'token_invitacion' => $tokenInput,
        // ...
    ]
);
```

#### 4. Integridad de Fechas y Concurrencia
- Cero asignación inventada de `now()`:
  `$fechaInicio = $request->input('inicio_empleo') ?: null;`
- Concurrencia serializada mediante bloqueo pesimista en base de datos:
  `Actualizacion::where('user_id', $targetUserId)->lockForUpdate()->get();`
  `EpisodioLaboral::where('user_id', $targetUserId)->lockForUpdate()->get();`

---

### Conclusión de Auditoría

Con estas correcciones, la Fase 1C satisface cabalmente el criterio de aceptación:
1. **Ninguna persona no autorizada puede consultar ni alterar el expediente de otra.**
2. **Ninguna actualización posterior destruye el historial longitudinal preexistente.**
3. **El 100% de los requisitos ha sido validado mediante pruebas automatizadas de integración y seguridad.**
