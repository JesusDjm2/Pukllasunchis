-- Egresado de PRUEBA para probar el proceso de Titulación (06/10/2026).
-- NO es un registro real: DNI 99999901, expediente PRUEBA-01.
-- Correo: davidmiranda.eesp+titulacion@pukllavirtual.edu.pe  (el "+titulacion" llega
-- a la bandeja de davidmiranda.eesp@pukllavirtual.edu.pe; el correo debe ser único).
-- Clave inicial: PruebaTit-2026  (cambiarla después de entrar).
-- PTI inscrito el 10/12/2024 → vence 31/12/2026: aparece en «PTI por vencer».
-- Se puede ejecutar más de una vez: si la cuenta ya existe, no inserta nada.

START TRANSACTION;

INSERT INTO users (name, apellidos, dni, email, password, condicion, programa_id, ciclo_id, created_at, updated_at)
SELECT 'David (PRUEBA)', 'Titulación Prueba', '99999901', 'davidmiranda.eesp+titulacion@pukllavirtual.edu.pe',
       '$2y$12$nm1sVJZT377to2p1F05eUOyMQntYcTPi.NQ3tJ4kyIhZQo8kg71Pi', 'Egresado',
       p.id, c.id, NOW(), NOW()
FROM programas p
JOIN ciclos c ON c.programa_id = p.id AND c.nombre = 'Egresados 2025-I'
WHERE p.nombre = 'Programa Inicial'
  AND NOT EXISTS (SELECT 1 FROM users WHERE email = 'davidmiranda.eesp+titulacion@pukllavirtual.edu.pe' OR dni = '99999901')
LIMIT 1;

SET @uid := (SELECT id FROM users WHERE email = 'davidmiranda.eesp+titulacion@pukllavirtual.edu.pe');

-- Rol alumno
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', @uid FROM roles r
WHERE r.name = 'alumno' AND r.guard_name = 'web' AND @uid IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM model_has_roles m WHERE m.model_id = @uid AND m.role_id = r.id AND m.model_type = 'App\\Models\\User');

-- Seguimiento de titulación
INSERT INTO seguimiento_titulacion (user_id, estado_titulacion_id, numero_expediente, created_at, updated_at)
SELECT @uid, e.id, 'PRUEBA-01', NOW(), NOW() FROM estados_titulacion e
WHERE e.nombre = 'PTI en proceso' AND @uid IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM seguimiento_titulacion s WHERE s.user_id = @uid AND s.deleted_at IS NULL);

SET @sid := (SELECT id FROM seguimiento_titulacion WHERE user_id = @uid AND deleted_at IS NULL LIMIT 1);

-- PTI (sin RD todavía: así se puede probar generar la RD de aprobación)
INSERT INTO titulacion_ptis (seguimiento_id, numero, etapa, titulo, fecha_pti, estado, created_at, updated_at)
SELECT @sid, 1, 'bachiller', 'Trabajo de investigación de prueba (no es un registro real)', '2024-12-10', 'pendiente', NOW(), NOW()
FROM DUAL
WHERE @sid IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM titulacion_ptis t WHERE t.seguimiento_id = @sid AND t.deleted_at IS NULL);

COMMIT;

-- Comprobación: debe devolver 1 fila con rol alumno, estado "PTI en proceso" y fecha_pti 2024-12-10.
SELECT u.id, u.email, u.condicion, r.name AS rol, e.nombre AS estado, t.fecha_pti
FROM users u
LEFT JOIN model_has_roles m ON m.model_id = u.id AND m.model_type = 'App\\Models\\User'
LEFT JOIN roles r ON r.id = m.role_id
LEFT JOIN seguimiento_titulacion s ON s.user_id = u.id AND s.deleted_at IS NULL
LEFT JOIN estados_titulacion e ON e.id = s.estado_titulacion_id
LEFT JOIN titulacion_ptis t ON t.seguimiento_id = s.id AND t.deleted_at IS NULL
WHERE u.email = 'davidmiranda.eesp+titulacion@pukllavirtual.edu.pe';

-- ─── Para BORRAR la cuenta de prueba cuando ya no se necesite ───
-- SET @uid := (SELECT id FROM users WHERE email = 'davidmiranda.eesp+titulacion@pukllavirtual.edu.pe');
-- DELETE t FROM titulacion_ptis t JOIN seguimiento_titulacion s ON s.id = t.seguimiento_id WHERE s.user_id = @uid;
-- DELETE FROM titulacion_avisos_pti WHERE seguimiento_id IN (SELECT id FROM seguimiento_titulacion WHERE user_id = @uid);
-- DELETE FROM titulacion_resoluciones WHERE seguimiento_id IN (SELECT id FROM seguimiento_titulacion WHERE user_id = @uid);
-- DELETE FROM seguimiento_titulacion WHERE user_id = @uid;
-- DELETE FROM model_has_roles WHERE model_id = @uid AND model_type = 'App\\Models\\User';
-- DELETE FROM users WHERE id = @uid;
