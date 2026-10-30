SET NAMES utf8mb4;

-- 1) Crea el rol «secretaria».
INSERT INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT 'secretaria', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE `name` = 'secretaria' AND `guard_name` = 'web');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_10_30_000004_add_secretaria_role' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_30_000004_add_secretaria_role');

-- 2) Secretaría: la cuenta con DNI 74859674 recibe el rol «secretaria» y deja «gestor-titulacion»
--    (ese rol ahora es el Área de Investigación). Conserva «admin» y lo demás.
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`)
SELECT r.id, 'App\\Models\\User', u.id
FROM `users` u JOIN `roles` r ON r.`name` = 'secretaria' AND r.`guard_name` = 'web'
WHERE u.`dni` = '74859674'
  AND NOT EXISTS (SELECT 1 FROM `model_has_roles` m WHERE m.`role_id` = r.id AND m.`model_type` = 'App\\Models\\User' AND m.`model_id` = u.id);

DELETE m FROM `model_has_roles` m
JOIN `roles` r ON r.id = m.`role_id` AND r.`name` = 'gestor-titulacion'
JOIN `users` u ON u.id = m.`model_id`
WHERE m.`model_type` = 'App\\Models\\User' AND u.`dni` = '74859674';

-- Comprobación: debe mostrar sus roles, entre ellos «secretaria» y sin «gestor-titulacion».
SELECT u.`dni`, u.`name`, u.`apellidos`, GROUP_CONCAT(r.`name`) AS roles
FROM `users` u
JOIN `model_has_roles` m ON m.`model_id` = u.id AND m.`model_type` = 'App\\Models\\User'
JOIN `roles` r ON r.id = m.`role_id`
WHERE u.`dni` = '74859674'
GROUP BY u.id;
