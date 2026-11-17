-- Excepciones: alumnos habilitados por Secretaría a solicitar la aprobación de su PTI antes del Ciclo IX.
-- Equivale a la migración 2026_11_12_000001_create_titulacion_habilitaciones_table. Se puede correr dos veces sin daño.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `titulacion_habilitaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `habilitado_por` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titulacion_habilitaciones_user_id_unique` (`user_id`),
  KEY `titulacion_habilitaciones_habilitado_por_foreign` (`habilitado_por`),
  CONSTRAINT `titulacion_habilitaciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `titulacion_habilitaciones_habilitado_por_foreign` FOREIGN KEY (`habilitado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_11_12_000001_create_titulacion_habilitaciones_table' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_12_000001_create_titulacion_habilitaciones_table');
