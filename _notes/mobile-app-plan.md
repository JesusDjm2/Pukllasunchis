# Plan: App móvil Pukllasunchis (Flutter + API Laravel)

Rama de trabajo: `feature/mobile-api`. Todo el trabajo es local; no se toca
producción ni el FTP en esta tarea.

**Importante — no es un reemplazo:** la web actual (`web.php`, `admin.php`,
vistas Blade) NO se toca ni se apaga. Este trabajo es puramente aditivo: rutas
nuevas bajo `/api/v1` con controladores en `app/Http/Controllers/Api/` (namespace
separado), sobre la MISMA base de datos. Web y la futura app Flutter coexisten y
comparten/sincronizan datos en vivo a través de esa BD compartida — no hay
migración de una plataforma a otra.

## Decisiones técnicas

- Auth API: Laravel Sanctum, tokens personales (Bearer), no cookies SPA.
- Rutas bajo `/api/v1`, prefix en `routes/api.php`.
- Controladores en `app/Http/Controllers/Api/` (no se toca `web.php` ni `admin.php`).
- Respuestas vía `JsonResource`: `{"data": ...}` / errores `{"message": ..., "errors": {...}}`.
- Autorización: rol del usuario (Spatie `HasRoles`) + scope por `user_id`.
- Tests: PHPUnit en `tests/Feature/Api/`.
- Flutter (Fase 2): Riverpod + go_router + dio + flutter_secure_storage, proyecto **FlutterPuklla** en `FlutterPuklla/`.

## Fase 1 — API REST local (Laravel)

- [x] Setup: Sanctum ya configurado (`HasApiTokens` en `User`, migración
      `personal_access_tokens` corrida, grupo `api` en `Kernel.php` sin
      `EnsureFrontendRequestsAreStateful` — correcto para auth por token puro,
      no SPA). No requirió cambios.
- [x] Auth: `POST /api/v1/login`, `POST /api/v1/logout`, `GET /api/v1/me`.
      Bugs preexistentes encontrados y corregidos en el camino (bloqueaban
      migrar en limpio / correr tests): `routes/api.php` nunca se cargaba
      (faltaba en `RouteServiceProvider`); `AppServiceProvider::boot()` hacía
      una query eager a `periodo_actual` fuera de los composers (rompía
      cualquier instalación en limpio); 3 migraciones usaban `->after()`
      dentro de `Schema::create()` (inválido, MySQL lo rechaza); `ppds` tenía
      ~90 columnas varchar(255) y excedía el límite de fila de InnoDB —
      `genero` ya es `TEXT` en las BDs reales por esa razón, la migración no
      coincidía; `UserFactory` no seteaba `apellidos`/`dni`/`genero`
      (NOT NULL sin default). BD de pruebas dedicada: `puklla_testing`
      (`.env.testing`, gitignored).
- [x] Perfil alumno: `GET/PUT /api/v1/alumno/perfil`. Reutiliza las reglas de
      validación de `AlumnoController::actualizarDatos` (sin `foto`, que
      requiere multipart y queda pendiente si se necesita). Se agregaron
      factories nuevas (`Alumno`, `Programa`, `Ciclo`) que no existían y hacen
      falta para el resto de tests de Fase 1.
- [x] Periodo actual: `GET /api/v1/periodo-actual`.
- [x] Matrícula: `GET /api/v1/matriculas`.
- [x] Cursos y calificaciones: `GET /api/v1/cursos`, `GET /api/v1/calificaciones`.
      Bug preexistente encontrado: `alumno_cursos.periodo_actual_id` existe en
      las BDs reales (agregada a mano) pero nunca fue migración — sin ella
      `Alumno::cursosDelPeriodo()` fallaba en una BD nueva. Migración agregada.
- [x] Cursos especiales: `GET /api/v1/cursos-especiales` (catálogo + inscrito +
      porcentaje), `POST .../{curso}/inscribir`, `GET .../{curso}` (árbol
      completo niveles→unidades→lecciones/ejercicios con progreso marcado —
      `respuesta_correcta` NUNCA se expone), `GET .../{curso}/progreso`,
      `POST /api/v1/lecciones/{id}/completar`,
      `POST /api/v1/ejercicios/{id}/responder`. `CursoEspecial` no tenía
      `HasFactory` (único modelo del módulo sin ella).
- [x] Comunicados: `GET /api/v1/comunicados` (paginado, requiere auth como en
      la web).
- [x] Incidencias: `GET /api/v1/incidencias`. Confirmado con el usuario: solo
      docente (sus propios reportes, igual que `IncidenciaController::index`
      en la web) — no existe hoy vista de incidencias para alumnos, no se
      inventó una. `Docente` no tenía factory e `Incidencia` no tenía
      `HasFactory`, ambos agregados.
- [x] Bolsa de trabajo: `GET /api/v1/bolsa-trabajo` (pública, sin auth — igual
      que `informacion/bolsa-de-trabajo` en la web). Filtros opcionales `anio`
      y `mes`, mismos que `BolsaTrabajoListado::datos()`. Nota: el nombre
      "vigentes" del plan original no aplica — la web tampoco filtra por
      `fecha_fin`, solo muestra las 20 más recientes; se replicó el
      comportamiento real en vez de inventar un filtro nuevo.
- [x] Documentar `_notes/api-v1-endpoints.md`.

**FASE 1 COMPLETA — 10/10.** Pendiente: confirmación del usuario antes de
arrancar la Fase 2 (FlutterPuklla).

## Fase 2 — App Flutter (tras cierre y confirmación de Fase 1)

Nombre del proyecto: **FlutterPuklla** (carpeta `FlutterPuklla/`, hermana del
repo Laravel, no `mobile/`).

- [x] `flutter create FlutterPuklla` + estructura por features. **Nota:** el
      CLI de `flutter` está bloqueado en esta máquina — algo (antivirus/EDR)
      impide que `cmd.exe` invoque `powershell.exe` (falla con "Acceso
      denegado" incluso en un comando trivial), y `flutter.bat` necesita ese
      paso en *cada* ejecución para resolver la versión del engine. Un
      `git clone -b stable` del propio SDK de Flutter también se cortó
      repetidamente con `SSL_read: decryption failed or bad record mac`,
      consistente con la misma interferencia. El usuario está revisando su
      antivirus en una sesión aparte con permisos de administrador. Mientras
      tanto se creó la estructura a mano: `pubspec.yaml`, `analysis_options.yaml`,
      `.gitignore`, `README.md`, y `lib/` organizado por feature (`core/`,
      `routing/`, `features/{auth,home,cursos_especiales,calificaciones,comunicados}`
      con subcarpetas `data/application/presentation`). Pendiente, una vez
      resuelto el bloqueo: `flutter create . --project-name flutter_puklla`
      (genera `android/`/`ios/` sin tocar `lib/` ni `pubspec.yaml`) y
      `flutter pub get` — recién ahí se puede correr `flutter analyze` /
      `flutter test` para verificar de verdad este scaffolding.
- [x] Auth (login, token seguro, redirect). `features/auth/`: `data/auth_user.dart`
      (modelo de `GET /me`), `data/auth_repository.dart` (login/me/logout
      contra `/api/v1`, guarda/borra el token en `TokenStorage`),
      `application/auth_controller.dart` (`StateNotifier<AuthState>`: estados
      `unknown` (restaurando sesión) → `authenticated`/`unauthenticated`;
      al crearse intenta `GET /me` con el token guardado y si falla lo
      descarta), `presentation/login_screen.dart` (formulario real con
      validación y manejo de `ApiException`). `routing/app_router.dart` ahora
      hace `redirect` según `AuthStatus` (no autenticado → `/login`,
      autenticado en `/login` → `/home`). `HomeScreen` muestra el nombre del
      usuario y tiene botón de logout, para poder probar el ciclo completo
      login → home → logout → login. Errores de dio se normalizan a
      `ApiException` vía `core/network/api_exception.dart` +
      `throwApiException()`. **Sin verificar en un emulador/dispositivo
      real todavía** — sigue pendiente `flutter create .` + `flutter pub get`
      (bloqueados por el mismo antivirus/EDR, ver tarea anterior) para poder
      compilar y probar contra `POST /api/v1/login` real.
- [x] Home/dashboard. `features/home/data/`: `periodo_actual.dart` +
      `periodo_actual_repository.dart` (`GET /periodo-actual`, 404 → `null`,
      no es un error real) y `curso.dart` + `curso_repository.dart`
      (`GET /cursos`; `horas`/`creditos` son `string` en la BD, no
      numéricos — confirmado en la migración `create_cursos_table`, no
      asumido). `application/home_providers.dart`: `periodoActualProvider` y
      `cursosProvider` (`FutureProvider`, con pull-to-refresh vía
      `ref.invalidate`). `HomeScreen` ahora es el dashboard real: si
      `user.isAlumno` muestra tarjeta de periodo actual + lista de cursos del
      periodo; si no (p.ej. docente), mensaje de que la vista aún es solo
      para alumnos — sin llamar a endpoints que igual 404earían. Sigue
      pendiente compilar/probar de verdad (mismo bloqueo de antivirus/EDR).
- [x] Cursos especiales (lista → unidades → lecciones → ejercicios → progreso).
      Modelos fieles al contrato real (revisado en `CursoEspecialController` +
      `CursoEspecialResource`, no asumido): `opciones` de ejercicio es
      `List<String>?` (cast `array` en Eloquent, `null` si es respuesta
      libre); las lecciones del árbol **no** traen `contenido_texto`/
      `archivo_url` — la API de Fase 1 solo expone id/nombre/tipo/duración/
      completada, así que la pantalla de lección es solo un check "marcar
      completada", sin visor de contenido (no se inventó nada extra).
      `CursosEspecialesScreen` (catálogo, con botón "Inscribirme" si no está
      inscrito) → `CursoEspecialDetalleScreen` (árbol expandible
      niveles→unidades→lecciones/ejercicios + barra de progreso; responder un
      ejercicio abre un bottom sheet con opción múltiple o texto libre según
      `opciones`). Acciones (inscribir/completar lección/responder ejercicio)
      viven en `CursosEspecialesController`, que invalida los providers de
      lectura afectados tras cada mutación. Se agregaron rutas
      `/cursos-especiales` y `/cursos-especiales/:id` y un enlace desde
      `HomeScreen` (si no, la pantalla quedaba inalcanzable). Sigue sin poder
      compilarse/probarse en un dispositivo real (mismo bloqueo de
      antivirus/EDR).
- [ ] Calificaciones y matrícula (solo lectura).
- [ ] Comunicados.
- [ ] Config de entorno (`--dart-define=API_BASE_URL`).
- [ ] README en `FlutterPuklla/`.

## Roles existentes (referencia)

`super-admin`, `admin`, `docente`, `tutor`, `adminB`, `alumnoB`, `alumno`,
`inhabilitado` (ver `app/Http/Controllers/Auth/LoginController.php`).
