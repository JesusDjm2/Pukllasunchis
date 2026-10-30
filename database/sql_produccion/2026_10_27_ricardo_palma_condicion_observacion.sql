SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `rp_acta_estudiante` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `acta_id` bigint(20) unsigned NOT NULL,
  `estudiante_id` bigint(20) unsigned NOT NULL,
  `condicion` varchar(20) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rp_acta_estudiante_unico` (`acta_id`,`estudiante_id`),
  KEY `rp_acta_estudiante_estudiante_id_foreign` (`estudiante_id`),
  CONSTRAINT `rp_acta_estudiante_acta_id_foreign` FOREIGN KEY (`acta_id`) REFERENCES `rp_actas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rp_acta_estudiante_estudiante_id_foreign` FOREIGN KEY (`estudiante_id`) REFERENCES `rp_estudiantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_10_27_000001_create_rp_acta_estudiante_table' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_27_000001_create_rp_acta_estudiante_table');
