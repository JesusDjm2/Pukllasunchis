-- ============================================================
--  SUPER-ADMIN SETUP — Ejecutar en phpMyAdmin o consola MySQL
--  Proyecto: Puklla2026  |  Fecha: 2026-05-27
-- ============================================================

-- ── PASO 1: Verificar si el rol ya existe ───────────────────
SELECT id, name FROM roles WHERE name = 'super-admin';

-- ── PASO 2: Crear el rol (solo si NO existe ya) ─────────────
INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT 'super-admin', 'web', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM roles WHERE name = 'super-admin'
);

-- ── PASO 3: Asignar el rol a un usuario ─────────────────────
--  Reemplaza 'correo@ejemplo.com' por el email real del usuario.
--  Puedes ejecutar este bloque cuantas veces necesites para
--  asignar el rol a varios usuarios.

SET @email_usuario = 'correo@ejemplo.com';   -- ← CAMBIAR AQUÍ

INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT
    r.id,
    'App\\Models\\User',
    u.id
FROM roles r
JOIN users u ON u.email = @email_usuario
WHERE r.name = 'super-admin'
  AND NOT EXISTS (
      SELECT 1 FROM model_has_roles mhr
      WHERE mhr.role_id  = r.id
        AND mhr.model_id = u.id
        AND mhr.model_type = 'App\\Models\\User'
  );

-- ── PASO 4: Verificar asignación ────────────────────────────
SELECT
    u.id,
    u.name,
    u.email,
    r.name AS rol
FROM users u
JOIN model_has_roles mhr ON mhr.model_id = u.id
                         AND mhr.model_type = 'App\\Models\\User'
JOIN roles r ON r.id = mhr.role_id
WHERE r.name = 'super-admin'
ORDER BY u.name;

-- ── PASO 5 (OPCIONAL): Quitar el rol 'admin' al super-admin ─
--  Si el usuario tenía rol 'admin' y quieres dejar SOLO 'super-admin':
--
-- DELETE FROM model_has_roles
-- WHERE model_type = 'App\\Models\\User'
--   AND model_id  = (SELECT id FROM users WHERE email = 'correo@ejemplo.com')
--   AND role_id   = (SELECT id FROM roles WHERE name = 'admin');

-- ── NOTAS ───────────────────────────────────────────────────
--  • Spatie Permission usa caché. Después de cambios de roles
--    ejecuta en terminal Laravel:  php artisan permission:cache-reset
--  • O en producción sin acceso a Artisan, vaciar la tabla cache:
--    DELETE FROM cache WHERE key LIKE '%spatie%';
--    (solo si usas cache de BD; con Redis/Memcached usa el panel del servicio)
-- ────────────────────────────────────────────────────────────
