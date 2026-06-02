-- ============================================================
-- SUPER-ADMIN: Crear nuevo rol y asignarlo a usuarios existentes
-- ============================================================
-- EJECUTAR DIRECTAMENTE EN MYSQL (phpMyAdmin / consola)

-- 1. Insertar el nuevo rol
INSERT INTO roles (name, guard_name, created_at, updated_at) 
VALUES ('super-admin', 'web', NOW(), NOW());

-- 2. (OPCIONAL) Asignar a un usuario admin específico
--    Reemplaza 'email@ejemplo.com' con el correo del admin que será super-admin
-- UPDATE users SET 
--     updated_at = NOW()
-- WHERE email = 'email@ejemplo.com';
-- INSERT INTO model_has_roles (role_id, model_type, model_id)
-- VALUES (
--     (SELECT id FROM roles WHERE name = 'super-admin'),
--     'App\Models\User',
--     (SELECT id FROM users WHERE email = 'email@ejemplo.com')
-- );

-- 3. Verificar
SELECT * FROM roles ORDER BY id;
