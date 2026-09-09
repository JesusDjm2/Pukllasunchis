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

## Incidencias

### `GET /incidencias`
Auth, solo docente. Reportes creados por el docente autenticado (no existe
vista de incidencias para alumnos en la web, no se agregó una). Paginado
(15/página). 404 si el usuario no tiene un `Docente` asociado.

## Bolsa de trabajo

### `GET /bolsa-trabajo`
**Público, sin auth.** Query params opcionales `anio`, `mes`. Últimas 20
ofertas (mismo comportamiento que `BolsaTrabajoListado::datos()` en la web —
no filtra por vigencia/`fecha_fin`).
