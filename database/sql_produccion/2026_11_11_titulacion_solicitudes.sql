-- Solicitudes de trámites de titulación (empieza con la solicitud de aprobación de PTI).
-- Equivale a la migración 2026_11_11_000001_create_titulacion_solicitudes_table. Se puede correr dos veces sin daño.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `titulacion_solicitudes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(30) NOT NULL,
  `solicitante_id` bigint(20) unsigned DEFAULT NULL,
  `seguimiento_id` bigint(20) unsigned DEFAULT NULL,
  `correo` varchar(150) NOT NULL DEFAULT '',
  `integrantes` json DEFAULT NULL,
  `celulares` varchar(200) NOT NULL DEFAULT '',
  `programa_estudios` varchar(100) NOT NULL DEFAULT '',
  `ciclo` varchar(30) NOT NULL DEFAULT '',
  `titulo` text DEFAULT NULL,
  `datos` json DEFAULT NULL,
  `archivo` text DEFAULT NULL,
  `comprobante` text DEFAULT NULL,
  `declaracion` tinyint(1) NOT NULL DEFAULT 0,
  `estado` varchar(20) NOT NULL DEFAULT 'recibida',
  `observacion` text DEFAULT NULL,
  `origen` varchar(20) NOT NULL DEFAULT 'sistema',
  `clave_importacion` varchar(40) DEFAULT NULL,
  `atendido_por` bigint(20) unsigned DEFAULT NULL,
  `atendido_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titulacion_solicitudes_clave_importacion_unique` (`clave_importacion`),
  KEY `titulacion_solicitudes_tipo_index` (`tipo`),
  KEY `titulacion_solicitudes_tipo_estado_index` (`tipo`, `estado`),
  KEY `titulacion_solicitudes_solicitante_id_foreign` (`solicitante_id`),
  KEY `titulacion_solicitudes_seguimiento_id_foreign` (`seguimiento_id`),
  KEY `titulacion_solicitudes_atendido_por_foreign` (`atendido_por`),
  CONSTRAINT `titulacion_solicitudes_solicitante_id_foreign` FOREIGN KEY (`solicitante_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `titulacion_solicitudes_seguimiento_id_foreign` FOREIGN KEY (`seguimiento_id`) REFERENCES `seguimiento_titulacion` (`id`) ON DELETE SET NULL,
  CONSTRAINT `titulacion_solicitudes_atendido_por_foreign` FOREIGN KEY (`atendido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_11_11_000001_create_titulacion_solicitudes_table' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_11_000001_create_titulacion_solicitudes_table');
