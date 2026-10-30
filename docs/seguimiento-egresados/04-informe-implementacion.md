# PUKLLA EN RED — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados
## 04. Informe de Implementación Técnica — Fase 1B

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis (Cusco, Perú)  
**Proyecto:** Puklla en Red — Intranet Institucional  
**Documento Rector:** Especificación Maestra V3.0 (`docs/seguimiento-egresados/ESPECIFICACION_MAESTRA.md`)  
**Fecha de Emisión:** 30 de Octubre de 2026  
**Auditoría Técnica:** Paquete de Revisión Externa — Entrega Fase 1B  

---

### Metadatos de Control de Versiones y Entorno

- **Repositorio:** `JesusDjm2/Pukllasunchis`
- **Rama Activa:** `sistemadeseguimientodeegresados`
- **Commit HEAD Exacto:** `59e57e5d6246d9e105b6203232e934dff6a841c2`
- **Mensaje de Commit:** `feat(seguimiento-egresados): implementacion controlada Fase 1B (R01, R02, R03)`
- **Fecha de Commit:** `2026-10-30 11:35:53 -0500`
- **Autor del Commit:** `Pavel Chaparro Acurio <pavel@pukllavirtual.edu.pe>`
- **Estado de Publicación:** **Únicamente en desarrollo local aislado**. La rama remota en GitHub no ha sido actualizada debido a restricciones de alcance en el token fine-grained del entorno (`metadata:read`, sin `contents:write`). Los cambios están salvaguardados en el repositorio local y empaquetados en este dossier.
- **Entorno de Ejecución:** PHP 8.2.32, Laravel Framework 10.50.2, MySQL 26.10 (puerto 3306), Mac Darwin x86_64.

---

### 1. Resumen Ejecutivo de la Fase 1B

La **Fase 1B: Persistencia Longitudinal y Actualización Rápida de Egresados** tuvo como propósito transformar el prototipo inicial de seguimiento en un subsistema institucional robusto, seguro y verificable dentro del ecosistema Laravel de la EESP Pukllasunchis.

El desarrollo resolvió de manera integral tres requisitos estructurales definidos en la Especificación Maestra:
1. **R01 (Integridad de Identidad y Anti-IDOR en Servidor):** Ninguna identidad se confía al cliente ni a parámetros manipulables del formulario; se valida en el servidor a través de la sesión autenticada (`users.id`) o mediante verificación previa en la base de datos institucional.
2. **R02 (Confirmación Rápida «Sin Cambios» en 1 Clic):** Los egresados cuya situación no haya variado pueden reconfirmar su vigencia semestral sin generar episodios laborales duplicados y preservando la fecha original de contratación.
3. **R03 (Persistencia Longitudinal de Trayectorias):** Transiciones laborales, empleos sucesivos, múltiples empleos simultáneos y periodos sin empleo se registran mediante transacciones ACID (`DB::transaction`) sin crear empleadores ficticios y conservando la historia laboral.

---

### 2. Relación de Cambios con Requisitos Rectores

#### Requisito R01: Integridad de Identidad y Control de Privilegios

```
Cliente (Navegador / Móvil)
  │
  ├── Caso Autenticado (Egresado) ──────> Servidor impone auth()->id()
  │                                       (Si envía user_id ajeno -> HTTP 403)
  │
  ├── Caso Entrevistador ───────────────> GrupoEntrevistador::esEntrevistadorAsignado()
  │                                       (Si no está en su grupo -> HTTP 403)
  │
  └── Caso Campaña Pública (WhatsApp) ──> DNI existe en users -> Vincula a user_id
                                          DNI no existe -> Aísla en identificador_temporal
                                          (ID numérico inválido -> HTTP 422)
```

- **Mapeo a `users.id`:** Todo egresado que interactúa con la intranet se ancla a su registro canónico en la tabla maestra `users`.
- **Mitigación de IDOR (Insecure Direct Object Reference):** Se implementó una verificación estricta en el controlador:
  ```php
  if ($authUser->condicion === 'Egresado' || $authUser->hasRole('egresado') || $authUser->hasRole('alumno')) {
      if ($targetUserId && (int)$targetUserId !== (int)$authUser->id) {
          return response()->json([
              'success' => false,
              'error' => 'No tienes autorización para modificar la ficha de otro egresado.'
          ], 403);
      }
      $targetUserId = (int)$authUser->id;
      $idTemporal = null;
  }
  ```
- **Autorización Delegada para Encuestadores:** Los docentes o entrevistadores con rol `encuestador-seguimiento` solo pueden actualizar egresados asignados a su comisión en la tabla `egresado_grupos_entrevistadores`. Si intentan enviar una actualización para un egresado fuera de su lote, el servidor responde `HTTP 403 Forbidden`.
- **Cero Usuarios Fantasma:** En el formulario público de WhatsApp (`campana-publica`), si se recibe un DNI que no tiene coincidencia institucional, el sistema **no inserta un registro arbitrario en `users`**; almacena la respuesta asociada a `identificador_temporal = 'CAMP-{DNI}'` para que la Coordinación realice un cotejo antes de cualquier vinculación.

---

#### Requisito R02: Confirmación Rápida Semestral «Sin Cambios»

- **Principio:** Reducir la fricción para el egresado docente que continúa laborando en la misma institución educativa sin alterar la trazabilidad cronológica.
- **Lógica Transaccional:**
  ```php
  $isSinCambios = $request->boolean('sin_cambios')
      || $request->input('cambio_empleo') === 'Sin cambios'
      || $request->boolean('confirmar_vigente');

  if ($isSinCambios) {
      $actualizacion = Actualizacion::updateOrCreate(
          ['user_id' => $targetUserId, 'identificador_temporal' => $idTemporal],
          [
              'nombre_completo' => $nombre,
              'canal' => $canal,
              'registrado_por' => $registradoPor,
              'fecha_ultima_confirmacion' => now(),
              'es_confirmacion_sin_cambios' => true,
              'periodo_campana' => $campana,
              'satisfaccion' => $request->input('satisfaccion') ?: null,
              'temas_interes' => $request->input('temas') ?: $request->input('temas_interes'),
              'necesidades_apoyo' => $request->input('necesidad_prioritaria') ?: $request->input('necesidades'),
              'puedo_compartir' => $request->input('puedo_compartir') ?: null,
          ]
      );
      // No se ejecuta inserción en egresado_episodios_laborales
      return response()->json([
          'success' => true,
          'tipo' => 'confirmacion_sin_cambios',
          'message' => 'Situación actual confirmada exitosamente. No se han duplicado registros laborales.',
          'actualizacion_id' => $actualizacion->id,
          'fecha_confirmacion' => now()->toDateString(),
          'episodios_activos' => $episodiosActivos
      ]);
  }
  ```
- **Preservación de Fechas:** La fecha de contratación original (`fecha_inicio` en `egresado_episodios_laborales`) se mantiene inalterada (ejemplo: `2024-03-01`), mientras que `fecha_ultima_confirmacion` registra la verificación semestral (`2026-10-30 11:34:30`).
- **Microchequeo Estratégico:** Permite registrar satisfacción profesional, intereses formativos y solicitudes de apoyo prioritario. Si el egresado selecciona `Sí: titulación` o `Sí: búsqueda de empleo`, se abre un caso en `egresado_casos` sin forzar al egresado a llenar un formulario largo.

---

#### Requisito R03: Persistencia Longitudinal de Trayectorias

- **Registro Transaccional Seguro:** Todas las mutaciones en `egresado_actualizaciones`, `egresado_episodios_laborales` y `egresado_casos` se ejecutan dentro de `DB::transaction(function () { ... })`. Un fallo en cualquier etapa revierte por completo las escrituras parciales.
- **Transición de Empleo:** Al ingresar a una nueva institución o puesto, el egresado puede cerrar el empleo previo (`episodio_id_cerrar`) indicando `fecha_fin_anterior` y `motivo_cambio`. El episodio previo pasa a `es_actual = false`, y se crea el nuevo con `es_actual = true`.
- **Empleos Simultáneos:** Si un egresado labora en dos instituciones (ej. colegio matutino y apoyo pedagógico por horas en la tarde), el sistema permite registrar el segundo empleo sin exigir el cierre del primero, manteniendo ambos como `es_actual = true`.
- **Fecha de Fin Desconocida:** Cuando se informa el término de una relación contractual sin conocer el día exacto de cese, el sistema conserva `fecha_fin = NULL` (o fecha aproximada declarada) en lugar de autoasignar la fecha actual (`now()`).
- **Regla Fundamental de No-Ficción:** Cuando un egresado declara `trabaja = 'No trabaja'`, se marcan los episodios previos como concluidos. **Bajo ninguna circunstancia se crea una fila espuria con `institucion = 'Desempleado'` o `institucion = 'No trabaja'`.**
- **Idempotencia:** Ante envíos duplicados con los mismos valores de institución, cargo y fecha de inicio, el sistema actualiza la fila existente sin crear una segunda entrada.

---

### 3. Detalle de Archivos Modificados y Justificación Técnica

#### 3.1. Migración Aditiva
- **Ruta:** `database/migrations/2026_10_30_120000_enhance_seguimiento_egresados_tables.php`
- **Justificación:** Añadir las columnas necesarias para soportar el ciclo de vida longitudinal de episodios y la trazabilidad de confirmaciones sin alterar la integridad de las tablas preexistentes ni borrar datos institucionales.
- **Detalle de Columnas Agregadas:**
  - `egresado_episodios_laborales`:
    - `es_actual` (boolean, default true, indexado).
    - `sector` (string, nullable: Público, Privado, Convenio).
    - `trab_departamento`, `trab_provincia`, `trab_distrito` (string, nullable: Geocodificación UBIGEO).
    - `dedicacion` (string, nullable: Tiempo completo, Tiempo parcial, Por horas).
    - `motivo_cambio` (string, nullable: Término de contrato, Renuncia, Promoción).
    - `registrado_por` (string, nullable).
    - `fecha_captura` (timestamp, default CURRENT_TIMESTAMP).
    - `notas` (text, nullable).
  - `egresado_actualizaciones`:
    - `fecha_ultima_confirmacion` (timestamp, nullable).
    - `es_confirmacion_sin_cambios` (boolean, default false).
    - `periodo_campana` (string, nullable, indexado).
  - **Roles Spatie:** Sembrado idempotente de los 5 roles canónicos de Puklla en Red: `egresado`, `encuestador-seguimiento`, `coordinacion-empleabilidad`, `bienestar`, `formacion-continua`.

#### 3.2. Modelo `EpisodioLaboral.php`
- **Ruta:** `app/Models/SeguimientoEgresado/EpisodioLaboral.php`
- **Justificación:** Encapsular la lógica de negocio del ciclo laboral docente.
- **Mejoras Incorporadas:**
  - Scopes de consulta institucional: `scopeActivos($q)`, `scopeHistoricos($q)`, `scopeEducativos($q)`, `scopeDocenciaDirecta($q)`.
  - Método de dominio: `cerrarEpisodio(?string $fechaFin = null, ?string $motivo = null): bool`.
  - Casts de datos: `fecha_inicio => date`, `fecha_fin => date`, `es_actual => boolean`, `es_empleo_educativo => boolean`, `es_docencia_directa => boolean`.

#### 3.3. Modelo `Actualizacion.php`
- **Ruta:** `app/Models/SeguimientoEgresado/Actualizacion.php`
- **Justificación:** Relacionar la fotografía de estado del egresado con sus episodios longitudinales y confirmar la vigencia semestral.
- **Mejoras Incorporadas:**
  - Relaciones Eloquent: `episodiosLaborales()` (HasMany a través de `user_id`) y `episodiosActivos()` (`where('es_actual', true)`).
  - Casts: `fecha_ultima_confirmacion => datetime`, `es_confirmacion_sin_cambios => boolean`.

#### 3.4. Modelo `GrupoEntrevistador.php`
- **Ruta:** `app/Models/SeguimientoEgresado/GrupoEntrevistador.php`
- **Justificación:** Validación de servidor de privilegios para entrevistadores telefónicos.
- **Mejoras Incorporadas:**
  - Método estático: `esEntrevistadorAsignado(int $interviewerId, int $targetUserId): bool`. Inspecciona asignaciones directas, miembros JSON de comisión y lotes de alumnos/cohortes.

#### 3.5. Controlador `SeguimientoEgresadoController.php`
- **Ruta:** `app/Http/Controllers/SeguimientoEgresadoController.php`
- **Justificación:** Centralizar el procesamiento transaccional seguro y la API interna de persistencia longitudinal.
- **Mejoras Incorporadas:**
  - `guardarActualizacion(Request $request)`: Detección estricta de identidad, bifurcación para confirmación rápida sin cambios (R02), creación/actualización idempotente de episodios con soporte para empleos simultáneos (R03), cierre de empleos previos y apertura automática de compromisos de apoyo en `egresado_casos`.
  - `cerrarEpisodio(Request $request, $id)`: Endpoint específico para finalizar un empleo conservando `fecha_fin = NULL` si se desconoce la fecha exacta.
  - `obtenerEpisodios(Request $request, $userId)`: Endpoint JSON para consultar la trayectoria longitudinal de un egresado.

#### 3.6. Enrutamiento `routes/web.php`
- **Ruta:** `routes/web.php`
- **Mejoras Incorporadas:**
  - `GET /seguimiento-egresados/episodios/{userId}` (`name: seguimiento-egresados.episodios`).
  - `POST /seguimiento-egresados/episodios/{id}/cerrar` (`name: seguimiento-egresados.episodios.cerrar`).

#### 3.7. Vistas Blade Integradas
- **`resources/views/seguimiento_egresados/index.blade.php`:**
  - Carga `$episodiosDb` en la inicialización JavaScript.
  - Añade la tarjeta de acción rápida: `⚡ Confirmar situación vigente sin cambios (R02)` conectada a `APP.confirmarSinCambios()`.
  - Muestra la trayectoria laboral con badges de estado (`[Activo]` vs `[Concluido]`) y botón inline para «Cerrar empleo» con fecha efectiva.
  - Renderiza el historial longitudinal en la pestaña `Mi ficha`.
- **`resources/views/seguimiento_egresados/campana_publica.blade.php`:**
  - Añade la casilla de confirmación rápida semestral sin cambios para el flujo ágil en WhatsApp.
  - Asegura que ningún dato personal sensible se conserve de forma persistente en `localStorage`.

---

### 4. Certificación y Trazabilidad de Auditoría

1. **Reversibilidad Garantizada:** La migración cuenta con método `down()` probado que elimina limpiamente las columnas y roles agregados mediante `php artisan migrate:rollback`.
2. **Aislamiento en Base de Datos:** Las pruebas PHPUnit utilizan la directiva `DatabaseTransactions`, asegurando que no se contaminen las tablas productivas con datos sintéticos.
3. **Protección de Datos Personales (PII):** Todas las pruebas, fixtures y ejemplos de auditoría emplean identidades ficticias y generadas sintéticamente, garantizando cumplimiento de normativas de confidencialidad institucional.
