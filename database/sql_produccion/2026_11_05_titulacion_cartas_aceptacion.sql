-- Carta de aceptación de la Institución Educativa (paso después de la RD del PTI).
-- Equivale a la migración 2026_11_05_000001_titulacion_cartas_aceptacion. Se puede correr dos veces sin daño.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `titulacion_cartas_aceptacion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lote` char(36) NOT NULL,
  `seguimiento_id` bigint(20) unsigned NOT NULL,
  `solicitante_id` bigint(20) unsigned NOT NULL,
  `correo` varchar(150) NOT NULL,
  `grupo` varchar(1000) NOT NULL,
  `celular` varchar(20) NOT NULL,
  `programa_estudios` varchar(100) NOT NULL,
  `programa_formacion` varchar(100) NOT NULL,
  `institucion` varchar(300) NOT NULL,
  `director` varchar(200) NOT NULL,
  `titulo` varchar(1000) NOT NULL,
  `comprobante` varchar(120) NOT NULL,
  `declaracion` tinyint(1) NOT NULL DEFAULT 0,
  `estado` varchar(20) NOT NULL DEFAULT 'enviada',
  `observacion` text DEFAULT NULL,
  `carta` varchar(500) DEFAULT NULL,
  `atendido_por` bigint(20) unsigned DEFAULT NULL,
  `atendido_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `titulacion_cartas_aceptacion_seguimiento_id_foreign` (`seguimiento_id`),
  KEY `titulacion_cartas_aceptacion_solicitante_id_foreign` (`solicitante_id`),
  KEY `titulacion_cartas_aceptacion_atendido_por_foreign` (`atendido_por`),
  KEY `titulacion_cartas_aceptacion_lote_index` (`lote`),
  CONSTRAINT `titulacion_cartas_aceptacion_atendido_por_foreign` FOREIGN KEY (`atendido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `titulacion_cartas_aceptacion_seguimiento_id_foreign` FOREIGN KEY (`seguimiento_id`) REFERENCES `seguimiento_titulacion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `titulacion_cartas_aceptacion_solicitante_id_foreign` FOREIGN KEY (`solicitante_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_11_05_000001_titulacion_cartas_aceptacion' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_05_000001_titulacion_cartas_aceptacion');
