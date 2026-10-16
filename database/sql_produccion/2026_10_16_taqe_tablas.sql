-- Taq'e (Ferias de Investigación): tablas para producción (2026-10-16).
-- Generado desde la estructura FINAL en local (equivale a correr todas sus migraciones).
-- Seguro de re-ejecutar: CREATE TABLE IF NOT EXISTS y filas de migrations solo si faltan.
SET NAMES utf8mb4;

-- 1) Rol investigador (en producción ya existe: no hace nada si está)
INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT 'investigador', 'web', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'investigador' AND guard_name = 'web');

-- 2) Grupos (curso, ciclo, programa, periodo, Proyecto Integrador, entrega)
CREATE TABLE IF NOT EXISTS `taqe_grupos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `curso_id` bigint(20) unsigned NOT NULL,
  `ciclo_id` bigint(20) unsigned NOT NULL,
  `programa_id` bigint(20) unsigned NOT NULL,
  `proyecto_id` bigint(20) unsigned DEFAULT NULL,
  `periodo_actual_id` bigint(20) unsigned NOT NULL,
  `creador_alumno_id` bigint(20) unsigned NOT NULL,
  `estado` varchar(255) NOT NULL DEFAULT 'en_proceso',
  `entregado_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taqe_grupos_curso_id_foreign` (`curso_id`),
  KEY `taqe_grupos_ciclo_id_foreign` (`ciclo_id`),
  KEY `taqe_grupos_programa_id_foreign` (`programa_id`),
  KEY `taqe_grupos_periodo_actual_id_foreign` (`periodo_actual_id`),
  KEY `taqe_grupos_creador_alumno_id_foreign` (`creador_alumno_id`),
  KEY `taqe_grupos_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `taqe_grupos_ciclo_id_foreign` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos` (`id`),
  CONSTRAINT `taqe_grupos_creador_alumno_id_foreign` FOREIGN KEY (`creador_alumno_id`) REFERENCES `alumnos` (`id`),
  CONSTRAINT `taqe_grupos_curso_id_foreign` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  CONSTRAINT `taqe_grupos_periodo_actual_id_foreign` FOREIGN KEY (`periodo_actual_id`) REFERENCES `periodo_actual` (`id`),
  CONSTRAINT `taqe_grupos_programa_id_foreign` FOREIGN KEY (`programa_id`) REFERENCES `programas` (`id`),
  CONSTRAINT `taqe_grupos_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Integrantes e invitaciones
CREATE TABLE IF NOT EXISTS `taqe_grupo_alumno` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `taqe_grupo_id` bigint(20) unsigned NOT NULL,
  `alumno_id` bigint(20) unsigned NOT NULL,
  `estado` varchar(255) NOT NULL DEFAULT 'pendiente',
  `respondido_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `taqe_grupo_alumno_taqe_grupo_id_alumno_id_unique` (`taqe_grupo_id`,`alumno_id`),
  KEY `taqe_grupo_alumno_alumno_id_foreign` (`alumno_id`),
  CONSTRAINT `taqe_grupo_alumno_alumno_id_foreign` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`),
  CONSTRAINT `taqe_grupo_alumno_taqe_grupo_id_foreign` FOREIGN KEY (`taqe_grupo_id`) REFERENCES `taqe_grupos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Evidencias y trabajo final (archivo en Drive o enlace)
CREATE TABLE IF NOT EXISTS `taqe_trabajos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `taqe_grupo_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `enlace` varchar(2048) DEFAULT NULL,
  `tipo_archivo` varchar(255) NOT NULL,
  `categoria` varchar(255) NOT NULL DEFAULT 'evidencia',
  `estado` varchar(255) NOT NULL DEFAULT 'en_proceso',
  `subido_por_alumno_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taqe_trabajos_taqe_grupo_id_foreign` (`taqe_grupo_id`),
  KEY `taqe_trabajos_subido_por_alumno_id_foreign` (`subido_por_alumno_id`),
  CONSTRAINT `taqe_trabajos_subido_por_alumno_id_foreign` FOREIGN KEY (`subido_por_alumno_id`) REFERENCES `alumnos` (`id`),
  CONSTRAINT `taqe_trabajos_taqe_grupo_id_foreign` FOREIGN KEY (`taqe_grupo_id`) REFERENCES `taqe_grupos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Pedidos de ayuda al Investigador
CREATE TABLE IF NOT EXISTS `taqe_solicitudes_ayuda` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `taqe_grupo_id` bigint(20) unsigned DEFAULT NULL,
  `grupo_nombre` varchar(255) NOT NULL,
  `alumno_id` bigint(20) unsigned NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `estado` varchar(255) NOT NULL DEFAULT 'pendiente',
  `atendido_por_user_id` bigint(20) unsigned DEFAULT NULL,
  `atendido_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taqe_solicitudes_ayuda_taqe_grupo_id_foreign` (`taqe_grupo_id`),
  KEY `taqe_solicitudes_ayuda_alumno_id_foreign` (`alumno_id`),
  KEY `taqe_solicitudes_ayuda_atendido_por_user_id_foreign` (`atendido_por_user_id`),
  CONSTRAINT `taqe_solicitudes_ayuda_alumno_id_foreign` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`),
  CONSTRAINT `taqe_solicitudes_ayuda_atendido_por_user_id_foreign` FOREIGN KEY (`atendido_por_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `taqe_solicitudes_ayuda_taqe_grupo_id_foreign` FOREIGN KEY (`taqe_grupo_id`) REFERENCES `taqe_grupos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Registrar las migraciones como aplicadas (para que un futuro `migrate` no intente recrearlas)
INSERT INTO migrations (migration, batch)
SELECT t.m, t.b FROM (SELECT '2026_09_10_000000_add_investigador_role' AS m, (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations) AS b) t
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_09_10_000000_add_investigador_role');
INSERT INTO migrations (migration, batch)
SELECT t.m, t.b FROM (SELECT '2026_09_10_000001_create_taqe_tables' AS m, (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations) AS b) t
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_09_10_000001_create_taqe_tables');
INSERT INTO migrations (migration, batch)
SELECT t.m, t.b FROM (SELECT '2026_09_16_000000_add_estado_to_taqe_grupos_table' AS m, (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations) AS b) t
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_09_16_000000_add_estado_to_taqe_grupos_table');
INSERT INTO migrations (migration, batch)
SELECT t.m, t.b FROM (SELECT '2026_09_16_000001_create_taqe_solicitudes_ayuda_table' AS m, (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations) AS b) t
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_09_16_000001_create_taqe_solicitudes_ayuda_table');
INSERT INTO migrations (migration, batch)
SELECT t.m, t.b FROM (SELECT '2026_10_13_000000_add_categoria_to_taqe_trabajos_table' AS m, (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations) AS b) t
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_10_13_000000_add_categoria_to_taqe_trabajos_table');
INSERT INTO migrations (migration, batch)
SELECT t.m, t.b FROM (SELECT '2026_10_15_120000_taqe_enlaces_entrega_y_proyecto' AS m, (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations) AS b) t
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_10_15_120000_taqe_enlaces_entrega_y_proyecto');

-- Verificación: deben salir las 4 tablas
SHOW TABLES LIKE 'taqe\_%';
