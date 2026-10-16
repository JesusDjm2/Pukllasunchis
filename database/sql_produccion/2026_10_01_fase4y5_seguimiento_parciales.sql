-- Fases 4 y 5 del expediente del estudiante (2026-10-01)
-- Fase 4: seguimiento_acciones + expediente_documentos. Fase 5: periodo_parciales (archivo de Parcial 1 y 2).
-- Ejecutar UNA vez en la BD de producción, ANTES de subir el código. Requiere las Fases 1 y 2.

SET NAMES utf8mb4;

CREATE TABLE `seguimiento_acciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `fecha` date NOT NULL,
  `responsable_id` bigint(20) unsigned DEFAULT NULL,
  `resumen` text NOT NULL,
  `acuerdos` text DEFAULT NULL,
  `proxima_revision` date DEFAULT NULL,
  `estado` varchar(15) NOT NULL DEFAULT 'abierto',
  `confidencial` tinyint(1) NOT NULL DEFAULT 0,
  `registrado_por` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seguimiento_acciones_responsable_id_foreign` (`responsable_id`),
  KEY `seguimiento_acciones_registrado_por_foreign` (`registrado_por`),
  KEY `seguimiento_acciones_user_id_fecha_index` (`user_id`,`fecha`),
  KEY `seguimiento_acciones_estado_proxima_revision_index` (`estado`,`proxima_revision`),
  CONSTRAINT `seguimiento_acciones_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seguimiento_acciones_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seguimiento_acciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `expediente_documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `trayectoria_evento_id` bigint(20) unsigned DEFAULT NULL,
  `seguimiento_accion_id` bigint(20) unsigned DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `tamano` int(10) unsigned DEFAULT NULL,
  `subido_por` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expediente_documentos_trayectoria_evento_id_foreign` (`trayectoria_evento_id`),
  KEY `expediente_documentos_seguimiento_accion_id_foreign` (`seguimiento_accion_id`),
  KEY `expediente_documentos_subido_por_foreign` (`subido_por`),
  KEY `expediente_documentos_user_id_index` (`user_id`),
  CONSTRAINT `expediente_documentos_seguimiento_accion_id_foreign` FOREIGN KEY (`seguimiento_accion_id`) REFERENCES `seguimiento_acciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expediente_documentos_subido_por_foreign` FOREIGN KEY (`subido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expediente_documentos_trayectoria_evento_id_foreign` FOREIGN KEY (`trayectoria_evento_id`) REFERENCES `trayectoria_eventos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expediente_documentos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `periodo_parciales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `periodo_actual_id` bigint(20) unsigned NOT NULL,
  `alumno_id` bigint(20) unsigned NOT NULL,
  `curso_id` bigint(20) unsigned NOT NULL,
  `parcial` tinyint(3) unsigned NOT NULL,
  `valoracion_curso` varchar(255) DEFAULT NULL,
  `calificacion_curso` varchar(255) DEFAULT NULL,
  `calificacion_sistema` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periodo_parciales_unico` (`periodo_actual_id`,`alumno_id`,`curso_id`,`parcial`),
  KEY `periodo_parciales_alumno_id_foreign` (`alumno_id`),
  KEY `periodo_parciales_curso_id_foreign` (`curso_id`),
  CONSTRAINT `periodo_parciales_alumno_id_foreign` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `periodo_parciales_curso_id_foreign` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `periodo_parciales_periodo_actual_id_foreign` FOREIGN KEY (`periodo_actual_id`) REFERENCES `periodo_actual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parcial 1 vigente → archivo
INSERT INTO periodo_parciales (periodo_actual_id, alumno_id, curso_id, parcial, valoracion_curso, calificacion_curso, calificacion_sistema, created_at, updated_at) SELECT pa.id, t.alumno_id, t.curso_id, 1, t.valoracion_curso, t.calificacion_curso, t.calificacion_sistema, NOW(), NOW() FROM periodouno t JOIN periodo_actual pa ON pa.actual = 1 AND (pa.fecha_inicio IS NULL OR t.created_at >= pa.fecha_inicio) JOIN alumnos a ON a.id = t.alumno_id JOIN cursos c ON c.id = t.curso_id ON DUPLICATE KEY UPDATE valoracion_curso = VALUES(valoracion_curso), calificacion_curso = VALUES(calificacion_curso), calificacion_sistema = VALUES(calificacion_sistema), updated_at = NOW();

-- Parcial 2 vigente → archivo
INSERT INTO periodo_parciales (periodo_actual_id, alumno_id, curso_id, parcial, valoracion_curso, calificacion_curso, calificacion_sistema, created_at, updated_at) SELECT pa.id, t.alumno_id, t.curso_id, 2, t.valoracion_curso, t.calificacion_curso, t.calificacion_sistema, NOW(), NOW() FROM periodo_dos t JOIN periodo_actual pa ON pa.actual = 1 AND (pa.fecha_inicio IS NULL OR t.created_at >= pa.fecha_inicio) JOIN alumnos a ON a.id = t.alumno_id JOIN cursos c ON c.id = t.curso_id ON DUPLICATE KEY UPDATE valoracion_curso = VALUES(valoracion_curso), calificacion_curso = VALUES(calificacion_curso), calificacion_sistema = VALUES(calificacion_sistema), updated_at = NOW();

INSERT INTO migrations (migration, batch)
SELECT '2026_10_01_000004_create_seguimiento_y_documentos_expediente', IFNULL(MAX(batch),0)+1 FROM migrations
UNION ALL SELECT '2026_10_01_000005_create_periodo_parciales_table', IFNULL(MAX(batch),0)+1 FROM migrations;
