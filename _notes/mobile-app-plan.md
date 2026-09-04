# Plan: App móvil Pukllasunchis (Flutter + API Laravel)

Rama de trabajo: `feature/mobile-api`. Todo el trabajo es local; no se toca
producción ni el FTP en esta tarea.

## Decisiones técnicas

- Auth API: Laravel Sanctum, tokens personales (Bearer), no cookies SPA.
- Rutas bajo `/api/v1`, prefix en `routes/api.php`.
- Controladores en `app/Http/Controllers/Api/` (no se toca `web.php` ni `admin.php`).
- Respuestas vía `JsonResource`: `{"data": ...}` / errores `{"message": ..., "errors": {...}}`.
- Autorización: rol del usuario (Spatie `HasRoles`) + scope por `user_id`.
- Tests: PHPUnit en `tests/Feature/Api/`.
- Flutter (Fase 2): Riverpod + go_router + dio + flutter_secure_storage, proyecto en `mobile/`.

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
- [ ] Perfil alumno: `GET/PUT /api/v1/alumno/perfil`.
- [ ] Periodo actual: `GET /api/v1/periodo-actual`.
- [ ] Matrícula: `GET /api/v1/matriculas`.
- [ ] Cursos y calificaciones: `GET /api/v1/cursos`, `GET /api/v1/calificaciones`.
- [ ] Cursos especiales: catálogo, unidades, lecciones, ejercicios, progreso.
- [ ] Comunicados: `GET /api/v1/comunicados`.
- [ ] Incidencias: `GET /api/v1/incidencias`.
- [ ] Bolsa de trabajo: `GET /api/v1/bolsa-trabajo`.
- [ ] Documentar `_notes/api-v1-endpoints.md`.

## Fase 2 — App Flutter (tras cierre y confirmación de Fase 1)

- [ ] `flutter create mobile` + estructura por features.
- [ ] Auth (login, token seguro, redirect).
- [ ] Home/dashboard.
- [ ] Cursos especiales (lista → unidades → lecciones → ejercicios → progreso).
- [ ] Calificaciones y matrícula (solo lectura).
- [ ] Comunicados.
- [ ] Config de entorno (`--dart-define=API_BASE_URL`).
- [ ] README en `mobile/`.

## Roles existentes (referencia)

`super-admin`, `admin`, `docente`, `tutor`, `adminB`, `alumnoB`, `alumno`,
`inhabilitado` (ver `app/Http/Controllers/Auth/LoginController.php`).
