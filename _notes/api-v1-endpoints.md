# API v1 — Endpoints

Base URL local: `http://127.0.0.1:8001/api/v1` (puerto según cómo cada quien
corra `php artisan serve`; en producción sería `https://eesppukllasunchis.edu.pe/api/v1`).

Formato de respuesta:
- Éxito: `{"data": ...}` (objeto, array, o con `"meta"` si hay paginación).
- Error de validación (422): `{"message": ..., "errors": {"campo": ["..."]}}`.
- No autenticado (401), sin permiso (403), no encontrado (404): `{"message": "..."}`.

Auth: Sanctum, tokens personales (Bearer). Todas las rutas salvo `POST /login`
y `GET /bolsa-trabajo` requieren:
```
Authorization: Bearer <token>
```

## Auth

### `POST /login`
Público.
Body: `{"email": "...", "password": "...", "device_name": "opcional"}`
Respuesta: `{"data": {"token": "...", "user": {...}}}`
422 si las credenciales son incorrectas o la cuenta tiene el rol `inhabilitado`.

### `POST /logout`
Auth. Revoca el token actual usado en la petición.

### `GET /me`
Auth. Devuelve el usuario autenticado: `id, name, apellidos, email, roles[],
alumno_id, docente_id`.

## Alumno

### `GET /alumno/perfil`
Auth. Incluye `programa_nombre`/`ciclo_nombre` (además de los `_id`). 404 si
el usuario no tiene un `Alumno` asociado.

### `PUT /alumno/perfil`
Auth. Actualiza los campos autoeditables del alumno (mismas reglas que
`AlumnoController::actualizarDatos` en la web, sin `foto` — pendiente si se
necesita, requiere multipart). 422 en validación, 403 si no tiene `Alumno`.

### `GET /alumno/ficha-pdf`
Auth. Descarga la ficha técnica en PDF (misma plantilla Blade que
`vistasAlumnosController::exportarFichaPDF` en la web), escaneada siempre al
propio alumno autenticado — no acepta un `{alumno}` arbitrario como la ruta
web. 404 si no tiene `Alumno`. Respuesta: binario `application/pdf`.

## Periodo actual

### `GET /periodo-actual`
Auth. 404 si no hay ningún periodo marcado como activo.

## Matrícula

### `GET /matriculas`
Auth. Matrículas del alumno autenticado, más recientes primero. 404 si no
tiene `Alumno`.

## Cursos y calificaciones

### `GET /cursos`
Auth. Misma lógica que `AlumnoController@index` en la web (no solo
`cursosDelPeriodo()` — ese era un bug de Fase 1, corregido): fuente primaria
son las asignaciones explícitas en `alumno_cursos` para el periodo activo; si
no hay ninguna, se muestran todos los cursos del ciclo propio del alumno.
Lista vacía si no hay periodo activo y el alumno tampoco tiene ciclo. 404 si
no tiene `Alumno`.

### `GET /calificaciones`
Auth. Misma lógica que `AlumnoController@calificaciones` en la web, con dos
secciones:

- `data.periodo_actual`: `{"periodo_nombre": "2026-I", "cursos": [...]}` —
  los mismos cursos que `GET /cursos` (asignación explícita o fallback al
  ciclo), cada uno con `{curso_id, curso_nombre, parcial_1, parcial_2,
  promedio, observaciones}`. `parcial_1`/`parcial_2`/`promedio` son
  `{valoracion_curso, calificacion_curso, calificacion_sistema}` o `null` si
  no hay nota registrada (mostrar "Sin datos aún" en el cliente). Son **tres
  modelos separados** (`PeriodoUno`/`PeriodoDos`/`PeriodoTres` — no
  `Periodo`), y ninguno se filtra por período: se toma el primer registro
  del alumno para ese curso, igual que la web (no es un bug nuevo, es el
  comportamiento real).
- `data.anteriores`: `{"2024-I": [...], "2024-II": [...]}`, agrupado por
  `periodoActual->nombre` a partir del modelo `Periodo` (tabla `periodos`;
  **no** `Calificacion`/`calificacions`, que está vacía en producción — ese
  era un bug de Fase 1, corregido). Cada valor es un array de `{id, curso_id,
  curso_nombre, valoracion_curso, calificacion_curso, calificacion_sistema}`.

404 si no tiene `Alumno`.

## Cursos especiales (asincrónicos)

### `GET /cursos-especiales`
Auth. Catálogo de cursos activos, con `inscrito` (bool) y `porcentaje` (solo
si está inscrito) por curso.

### `POST /cursos-especiales/{curso}/inscribir`
Auth. Inscribe al usuario autenticado (idempotente).

### `GET /cursos-especiales/{curso}`
Auth. Requiere inscripción (403 si no). Árbol completo
`niveles → unidades → lecciones/ejercicios`, con `completada`/`completado`
marcado por lección/ejercicio. **`respuesta_correcta` nunca se expone.**

### `GET /cursos-especiales/{curso}/progreso`
Auth. Requiere inscripción. Resumen: totales y completados de lecciones y
ejercicios, y porcentaje general.

### `POST /lecciones/{leccion}/completar`
Auth. Requiere inscripción al curso de la lección. Marca la lección como
completada para el usuario.

### `POST /ejercicios/{ejercicio}/responder`
Auth. Requiere inscripción. Body: `{"respuesta": "..."}`.
Respuesta: `{"data": {"correcta": bool, "puntaje": int}}`.

## Comunicados

### `GET /comunicados`
Auth. Paginado (15/página), más recientes primero por `fecha_publicacion`.
`{"data": [...], "meta": {"current_page", "last_page", "total"}}`.

## Notificaciones

Sistema in-app v1 (sin push/FCM — ver _notes/mobile-app-plan.md). Reutiliza
la tabla `notifications` nativa de Laravel, ya usada en la web para
incidencias/sugerencias/tutorías (admin/tutor) — para alumno es la primera
vez que se usa. 3 tipos, elegidos para evitar fatiga de notificación:
`MatriculaVoucherVerificado` (al verificar el voucher, no al desverificar),
`CalificacionRegistrada` (Parcial 1/2/Promedio — vía `NotaParcialObserver`,
que cubre los 8 flujos de `CalificacionController`, no solo uno; con guarda
anti-duplicado: no notifica si no cambió ninguna nota real), y
`ComunicadoPublicado` (a todos los `alumno` al crear uno).

### `GET /notificaciones`
Auth. Paginado (15/página), más recientes primero.
`{"data": [{"id", "tipo", "datos", "leida", "leida_at", "created_at"}, ...],
"meta": {...}}`. `tipo` es el nombre corto de la clase (`CalificacionRegistrada`,
`ComunicadoPublicado`, `MatriculaVoucherVerificado`) — el cliente lo usa para
ícono/color y a dónde navegar. `datos` es el payload específico de cada tipo
(ver las clases en `app/Notifications/`).

### `GET /notificaciones/no-leidas-count`
Auth. `{"data": {"count": N}}` — para el badge de la campanita.

### `POST /notificaciones/{id}/leer`
Auth. `{id}` es el UUID de la notificación. 404 si no existe o no es del
usuario autenticado (no hay route model binding implícito a propósito, para
no exponer notificaciones ajenas).

### `POST /notificaciones/marcar-todas-leidas`
Auth. Marca todas las no leídas del usuario autenticado.

## Incidencias

### `GET /incidencias`
Auth, solo docente. Reportes creados por el docente autenticado (no existe
vista de incidencias para alumnos en la web, no se agregó una). Paginado
(15/página). 404 si el usuario no tiene un `Docente` asociado.

## Bolsa de trabajo

### `GET /bolsa-trabajo`
**Público, sin auth.** Query params opcionales `anio`, `mes`. Últimas 20
ofertas ordenadas por `created_at` desc (mismo comportamiento que
`BolsaTrabajoListado::datos()` en la web — no filtra por vigencia).
`fecha_inicio`/`fecha_fin` ya no existen (columnas eliminadas después de
Fase 1); cada oferta trae `fecha_publicacion`, `vigente` (calculado,
`fecha_publicacion` + 20 días), `nombre_publicador`, `telefono_publicador`,
`relacion_publicador`.

## Titulación (alumno / egresado)

### `GET /alumno/titulacion`
Auth. "Mi proceso de titulación": lo mismo que la web muestra en
`titulacion/_avance_alumno` (con proceso) o `titulacion/_guia_inicio` (sin
proceso pero ya puede iniciarlo), calculado con los mismos servicios
(`AvanceTitulacion`, `GuiaTramites`, `VigenciaPti`, `Solicitud`). Solo lectura.
- `disponible`: egresado (`condicion='Egresado'`), o con `seguimiento_titulacion`,
  o `TitulacionController::puedeIniciar()` (FID desde el Ciclo IX). Si es
  `false`, el resto viene vacío.
- Con proceso: `estado {clave, nombre, color}`, `siguiente_paso`, `etapas[]`
  (PTI → TI → Tesis, cada hito con `situacion` logrado|actual|pendiente|sin_registro),
  `vigencia_pti`, `carta` y `originalidad` (estado de la última solicitud + URL web).
- Sin proceso: `etapas[]` sin logros, `solicitud_pti` (primer paso).
- `tramites`: "qué hacer ahora" (pago Caja Cusco, pasos, formulario, descargas,
  contactos de WhatsApp) del tramo en curso.
- Las solicitudes (PTI, carta, originalidad) llevan archivos: la app abre sus
  URLs en la web (`mi-titulacion.*`), no se envían por la API.

## Investigaciones (ex Taq'e)

### `GET /taqe/guia-pdf`
Auth. Guía del estudiante de Investigaciones (la misma vista que la web
`taqe.guia.pdf`: `pdf.instructivo-investigaciones-ciclo2`). Descarga binaria.

## `GET /me` — campos agregados
`condicion` y `es_egresado`: el egresado tiene su propio panel (sin
matrícula, cursos del periodo, tutoría ni investigaciones), como su intranet web.

## Solicitudes de titulación (envío desde la app)

`MiTitulacionSolicitudController` — **espejo** de los controladores web
`Titulacion\MiSolicitudPtiController`, `CartaAceptacionController` y
`MiSolicitudOriginalidadController` (mismas reglas, mismos datos guardados,
mismos avisos). La API es aditiva: si cambian las reglas en la web, hay que
cambiarlas aquí también (`tests/Feature/Api/MiTitulacionSolicitudTest`).

Cada una tiene `GET` (lo que necesita el formulario: `motivo_no_puede` —null
si puede enviar—, `ultima`, `valores` iniciales, `fijos` —campos que manda la
ficha—, `opciones`, reglas de `archivos`, `pago`) y `POST` multipart
(201 con `message`; 409 si no corresponde; 422 con `errors` por campo).
Throttle 30/10 min, como la web.

### `GET|POST /alumno/titulacion/solicitud-pti`
Primer paso (antes de tener proceso). Campos: `integrante2`, `integrante3`,
`celulares`, `programa_estudios`, `ciclo`, `area`, `linea` (debe ser de esa
área), `titulo`, `pti` (pdf/doc/docx ≤10 MB), `comprobantes[]` (1–5,
pdf/jpg/png ≤10 MB), `declaracion`.

### `GET|POST /alumno/titulacion/carta`
Con el PTI aprobado y correo institucional. Campos: `grupo`, `celular`,
`programa_estudios`, `programa_formacion`, `institucion`, `director`,
`titulo`, `comprobante` (pdf/jpg/png ≤5 MB), `declaracion`. Crea una fila por
integrante del grupo (mismo `lote`), sube el comprobante a Drive (o al
servidor si falla) y **envía el correo de recepción** (`CartaRecibidaMail`).

### `GET|POST /alumno/titulacion/originalidad`
Solo con `TITULACION_ORIGINALIDAD_ALUMNOS=true`; si no, `habilitada=false` y
`formulario_externo` (Google Forms). Campos: `etapa` (ti|tesis), `titulo`,
`celulares`, `domicilio`, `fecha_pago` (≤ hoy), `documento` (doc/docx ≤10 MB),
`comprobantes[]`, `declaracion`.

`GET /alumno/titulacion` indica en cada acción y en cada trámite
`formulario_app` (`solicitud-pti` | `carta` | `originalidad`) para que la app
abra su formulario nativo; null = se abre en el navegador.
