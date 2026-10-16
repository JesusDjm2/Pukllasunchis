-- Fase 1: trayectoria del estudiante (2026-10-01)
-- Ejecutar UNA vez en la BD de producción, ANTES de subir el código.

SET NAMES utf8mb4;

CREATE TABLE `trayectoria_eventos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `periodo_actual_id` bigint(20) unsigned DEFAULT NULL,
  `periodo_actual_ppd_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `valor_anterior` varchar(255) DEFAULT NULL,
  `valor_nuevo` varchar(255) DEFAULT NULL,
  `ciclo_origen_id` bigint(20) unsigned DEFAULT NULL,
  `ciclo_destino_id` bigint(20) unsigned DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `resolucion` varchar(255) DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `origen` varchar(20) NOT NULL DEFAULT 'manual',
  `registrado_por` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trayectoria_eventos_periodo_actual_id_foreign` (`periodo_actual_id`),
  KEY `trayectoria_eventos_periodo_actual_ppd_id_foreign` (`periodo_actual_ppd_id`),
  KEY `trayectoria_eventos_ciclo_origen_id_foreign` (`ciclo_origen_id`),
  KEY `trayectoria_eventos_ciclo_destino_id_foreign` (`ciclo_destino_id`),
  KEY `trayectoria_eventos_registrado_por_foreign` (`registrado_por`),
  KEY `trayectoria_eventos_user_id_tipo_index` (`user_id`,`tipo`),
  KEY `trayectoria_eventos_tipo_fecha_fin_index` (`tipo`,`fecha_fin`),
  CONSTRAINT `trayectoria_eventos_ciclo_destino_id_foreign` FOREIGN KEY (`ciclo_destino_id`) REFERENCES `ciclos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trayectoria_eventos_ciclo_origen_id_foreign` FOREIGN KEY (`ciclo_origen_id`) REFERENCES `ciclos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trayectoria_eventos_periodo_actual_id_foreign` FOREIGN KEY (`periodo_actual_id`) REFERENCES `periodo_actual` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trayectoria_eventos_periodo_actual_ppd_id_foreign` FOREIGN KEY (`periodo_actual_ppd_id`) REFERENCES `periodo_actual_ppds` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trayectoria_eventos_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trayectoria_eventos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO trayectoria_eventos (user_id, tipo, valor_nuevo, ciclo_origen_id, motivo, origen, created_at, updated_at) SELECT u.id, CASE u.perfil WHEN 'Licencia' THEN 'licencia' WHEN 'Retirado' THEN 'retiro' ELSE 'inhabilitacion' END, u.perfil, u.ciclo_id, 'Estado registrado al iniciar el seguimiento de trayectoria', 'migracion', NOW(), NOW() FROM users u WHERE u.perfil IN ('Licencia', 'Retirado', 'Deuda', 'Sin matrícula') AND u.id IN (SELECT mr.model_id FROM model_has_roles mr JOIN roles r ON r.id = mr.role_id WHERE mr.model_type = 'App\\Models\\User' AND r.name IN ('alumno', 'alumnoB', 'inhabilitado')) AND NOT EXISTS (SELECT 1 FROM trayectoria_eventos t WHERE t.user_id = u.id AND t.origen = 'migracion');

INSERT INTO trayectoria_eventos (user_id, tipo, valor_nuevo, ciclo_origen_id, motivo, origen, created_at, updated_at) SELECT u.id, CASE u.condicion WHEN 'Egresado' THEN 'egreso' ELSE 'reincorporacion' END, u.condicion, u.ciclo_id, 'Estado registrado al iniciar el seguimiento de trayectoria', 'migracion', NOW(), NOW() FROM users u WHERE u.condicion IN ('Egresado', 'Reincorporación') AND u.id IN (SELECT mr.model_id FROM model_has_roles mr JOIN roles r ON r.id = mr.role_id WHERE mr.model_type = 'App\\Models\\User' AND r.name IN ('alumno', 'alumnoB', 'inhabilitado')) AND NOT EXISTS (SELECT 1 FROM trayectoria_eventos t WHERE t.user_id = u.id AND t.origen = 'migracion' AND t.tipo IN ('egreso', 'reincorporacion'));

INSERT INTO migrations (migration, batch) SELECT '2026_10_01_000001_create_trayectoria_eventos_table', IFNULL(MAX(batch),0)+1 FROM migrations UNION ALL SELECT '2026_10_01_000002_registrar_estado_inicial_trayectoria', IFNULL(MAX(batch),0)+1 FROM migrations;
