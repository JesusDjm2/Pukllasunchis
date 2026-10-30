-- IESP Ricardo Palma: acervo histórico de actas consolidadas (1995-2015).
-- Equivale a la migración 2026_10_26_121500_create_ricardo_palma_tables + RicardoPalmaSeeder
-- (las 4 carreras y los períodos 1995-I a 2015-II).
-- Módulo aislado: 7 tablas propias rp_*; no toca ninguna tabla de FID ni PPD.
-- Se puede correr más de una vez: crea las tablas solo si no existen y no duplica el catálogo.
-- Correr ANTES de subir el código.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `rp_programas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `nivel` varchar(60) NOT NULL DEFAULT 'Superior Pedagógico',
  `total_semestres` tinyint(3) unsigned NOT NULL DEFAULT 10,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rp_programas_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rp_periodos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `anio` smallint(5) unsigned NOT NULL,
  `semestre` varchar(10) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rp_periodos_codigo_unique` (`codigo`),
  KEY `rp_periodos_anio_semestre_index` (`anio`,`semestre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rp_cursos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `programa_id` bigint(20) unsigned NOT NULL,
  `numero_semestre` tinyint(3) unsigned NOT NULL,
  `codigo` varchar(30) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `creditos` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `horas` tinyint(3) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rp_cursos_programa_id_numero_semestre_index` (`programa_id`,`numero_semestre`),
  CONSTRAINT `rp_cursos_programa_id_foreign` FOREIGN KEY (`programa_id`) REFERENCES `rp_programas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rp_estudiantes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `programa_id` bigint(20) unsigned DEFAULT NULL,
  `numero_matricula` varchar(50) DEFAULT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `apellidos` varchar(150) NOT NULL,
  `nombres` varchar(150) NOT NULL,
  `sexo` char(1) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `anio_ingreso` smallint(5) unsigned DEFAULT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'REGULAR',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rp_estudiantes_programa_id_foreign` (`programa_id`),
  KEY `rp_estudiantes_numero_matricula_index` (`numero_matricula`),
  KEY `rp_estudiantes_dni_index` (`dni`),
  CONSTRAINT `rp_estudiantes_programa_id_foreign` FOREIGN KEY (`programa_id`) REFERENCES `rp_programas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rp_actas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `periodo_id` bigint(20) unsigned NOT NULL,
  `programa_id` bigint(20) unsigned NOT NULL,
  `numero_semestre` tinyint(3) unsigned NOT NULL,
  `numero_acta` varchar(50) DEFAULT NULL,
  `seccion` varchar(30) NOT NULL DEFAULT 'UNICA',
  `turno` varchar(30) NOT NULL DEFAULT 'TARDE',
  `fecha_emision` date DEFAULT NULL,
  `total_creditos` tinyint(3) unsigned DEFAULT NULL,
  `archivo_escaneo_path` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rp_actas_identificador_unico` (`periodo_id`,`programa_id`,`numero_semestre`,`seccion`,`turno`),
  KEY `rp_actas_programa_id_foreign` (`programa_id`),
  CONSTRAINT `rp_actas_periodo_id_foreign` FOREIGN KEY (`periodo_id`) REFERENCES `rp_periodos` (`id`),
  CONSTRAINT `rp_actas_programa_id_foreign` FOREIGN KEY (`programa_id`) REFERENCES `rp_programas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rp_calificaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `acta_id` bigint(20) unsigned NOT NULL,
  `estudiante_id` bigint(20) unsigned NOT NULL,
  `curso_id` bigint(20) unsigned NOT NULL,
  `calificacion` decimal(4,2) DEFAULT NULL,
  `puntaje` decimal(6,2) DEFAULT NULL,
  `observacion` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rp_calif_acta_est_curso_unico` (`acta_id`,`estudiante_id`,`curso_id`),
  KEY `rp_calificaciones_curso_id_foreign` (`curso_id`),
  KEY `rp_calificaciones_estudiante_id_curso_id_index` (`estudiante_id`,`curso_id`),
  CONSTRAINT `rp_calificaciones_acta_id_foreign` FOREIGN KEY (`acta_id`) REFERENCES `rp_actas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rp_calificaciones_curso_id_foreign` FOREIGN KEY (`curso_id`) REFERENCES `rp_cursos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rp_calificaciones_estudiante_id_foreign` FOREIGN KEY (`estudiante_id`) REFERENCES `rp_estudiantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rp_importaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `archivo_nombre` varchar(255) NOT NULL,
  `archivo_path` varchar(255) DEFAULT NULL,
  `periodo_id` bigint(20) unsigned DEFAULT NULL,
  `programa_id` bigint(20) unsigned DEFAULT NULL,
  `numero_semestre` tinyint(3) unsigned DEFAULT NULL,
  `total_filas` int(10) unsigned NOT NULL DEFAULT 0,
  `filas_exitosas` int(10) unsigned NOT NULL DEFAULT 0,
  `filas_con_error` int(10) unsigned NOT NULL DEFAULT 0,
  `detalles_errores` longtext DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'COMPLETADO',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rp_importaciones_user_id_foreign` (`user_id`),
  KEY `rp_importaciones_periodo_id_foreign` (`periodo_id`),
  KEY `rp_importaciones_programa_id_foreign` (`programa_id`),
  CONSTRAINT `rp_importaciones_periodo_id_foreign` FOREIGN KEY (`periodo_id`) REFERENCES `rp_periodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rp_importaciones_programa_id_foreign` FOREIGN KEY (`programa_id`) REFERENCES `rp_programas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rp_importaciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo base: las 4 carreras
INSERT IGNORE INTO `rp_programas` (`codigo`, `nombre`, `nivel`, `total_semestres`, `activo`, `created_at`, `updated_at`) VALUES
('INICIAL', 'Educación Inicial', 'Superior Pedagógico', 10, 1, NOW(), NOW()),
('PRIMARIA', 'Educación Primaria', 'Superior Pedagógico', 10, 1, NOW(), NOW()),
('SEC-COM', 'Educación Secundaria: Especialidad Comunicación', 'Superior Pedagógico', 10, 1, NOW(), NOW()),
('SEC-MAT', 'Educación Secundaria: Especialidad Matemáticas', 'Superior Pedagógico', 10, 1, NOW(), NOW());

-- Períodos 1995-I a 2015-II
INSERT IGNORE INTO `rp_periodos` (`codigo`, `anio`, `semestre`, `activo`, `created_at`, `updated_at`) VALUES
('1995-I', 1995, 'I', 1, NOW(), NOW()), ('1995-II', 1995, 'II', 1, NOW(), NOW()),
('1996-I', 1996, 'I', 1, NOW(), NOW()), ('1996-II', 1996, 'II', 1, NOW(), NOW()),
('1997-I', 1997, 'I', 1, NOW(), NOW()), ('1997-II', 1997, 'II', 1, NOW(), NOW()),
('1998-I', 1998, 'I', 1, NOW(), NOW()), ('1998-II', 1998, 'II', 1, NOW(), NOW()),
('1999-I', 1999, 'I', 1, NOW(), NOW()), ('1999-II', 1999, 'II', 1, NOW(), NOW()),
('2000-I', 2000, 'I', 1, NOW(), NOW()), ('2000-II', 2000, 'II', 1, NOW(), NOW()),
('2001-I', 2001, 'I', 1, NOW(), NOW()), ('2001-II', 2001, 'II', 1, NOW(), NOW()),
('2002-I', 2002, 'I', 1, NOW(), NOW()), ('2002-II', 2002, 'II', 1, NOW(), NOW()),
('2003-I', 2003, 'I', 1, NOW(), NOW()), ('2003-II', 2003, 'II', 1, NOW(), NOW()),
('2004-I', 2004, 'I', 1, NOW(), NOW()), ('2004-II', 2004, 'II', 1, NOW(), NOW()),
('2005-I', 2005, 'I', 1, NOW(), NOW()), ('2005-II', 2005, 'II', 1, NOW(), NOW()),
('2006-I', 2006, 'I', 1, NOW(), NOW()), ('2006-II', 2006, 'II', 1, NOW(), NOW()),
('2007-I', 2007, 'I', 1, NOW(), NOW()), ('2007-II', 2007, 'II', 1, NOW(), NOW()),
('2008-I', 2008, 'I', 1, NOW(), NOW()), ('2008-II', 2008, 'II', 1, NOW(), NOW()),
('2009-I', 2009, 'I', 1, NOW(), NOW()), ('2009-II', 2009, 'II', 1, NOW(), NOW()),
('2010-I', 2010, 'I', 1, NOW(), NOW()), ('2010-II', 2010, 'II', 1, NOW(), NOW()),
('2011-I', 2011, 'I', 1, NOW(), NOW()), ('2011-II', 2011, 'II', 1, NOW(), NOW()),
('2012-I', 2012, 'I', 1, NOW(), NOW()), ('2012-II', 2012, 'II', 1, NOW(), NOW()),
('2013-I', 2013, 'I', 1, NOW(), NOW()), ('2013-II', 2013, 'II', 1, NOW(), NOW()),
('2014-I', 2014, 'I', 1, NOW(), NOW()), ('2014-II', 2014, 'II', 1, NOW(), NOW()),
('2015-I', 2015, 'I', 1, NOW(), NOW()), ('2015-II', 2015, 'II', 1, NOW(), NOW());

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_10_26_121500_create_ricardo_palma_tables' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_26_121500_create_ricardo_palma_tables');
