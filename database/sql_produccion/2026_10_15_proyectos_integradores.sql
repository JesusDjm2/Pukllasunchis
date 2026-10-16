-- Proyectos Integradores (2026-10-15): producto de los 4 proyectos de Ciclos I–VIII con el texto
-- definido por coordinación. Los nombres se conservan (los muestra el sílabo, Sección III).
-- Se identifican por nombre (no por id) por si los ids de producción difieren.
-- No cambia la estructura: ciclos.proyecto_id ya asigna cada ciclo a su proyecto.
SET NAMES utf8mb4;

-- 1) Vista previa: deben salir 4 filas, con sus ciclos (I-II, III-IV, V-VI, VII-VIII)
SELECT p.id, p.nombre, p.producto, GROUP_CONCAT(DISTINCT c.nombre ORDER BY c.id) AS ciclos
FROM proyectos p LEFT JOIN ciclos c ON c.proyecto_id = p.id
WHERE p.nombre LIKE 'Escuela y Territorio%' OR p.nombre LIKE 'Pr_cticas de Crianza%'
   OR p.nombre LIKE 'Saber y Conocer%' OR p.nombre LIKE 'Rituales escolares%'
GROUP BY p.id, p.nombre, p.producto;

-- 2) Actualizar productos
UPDATE proyectos SET producto = 'Mapa parlante y tríptico descriptivo del territorio sagrado de una Institución Educativa'
WHERE nombre LIKE 'Escuela y Territorio%';
UPDATE proyectos SET producto = 'Podcast comparativo de prácticas de crianza entre familias con procedencias culturales distintas'
WHERE nombre LIKE 'Pr_cticas de Crianza%';
UPDATE proyectos SET producto = 'Material didáctico concreto que ponga en valor los saberes: juegos, títeres, cuentos (material narrativo: rotafolio con audio, video, stopmotion)'
WHERE nombre LIKE 'Saber y Conocer%';
UPDATE proyectos SET producto = 'Rituales Escolares - didácticas escolares alternativas (ABP, Proyectos de Aprendizaje)'
WHERE nombre LIKE 'Rituales escolares%';

-- 3) Docentes encargados/organizadores de cada Proyecto Integrador (migración 2026_10_15_130000)
CREATE TABLE IF NOT EXISTS `proyecto_docente` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint(20) unsigned NOT NULL,
  `docente_id` bigint(20) unsigned NOT NULL,
  `rol` enum('encargado','organizador') NOT NULL DEFAULT 'organizador',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proyecto_docente_proyecto_id_docente_id_unique` (`proyecto_id`,`docente_id`),
  KEY `proyecto_docente_docente_id_foreign` (`docente_id`),
  CONSTRAINT `proyecto_docente_docente_id_foreign` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyecto_docente_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO migrations (migration, batch)
SELECT t.m, t.b FROM (SELECT '2026_10_15_130000_create_proyecto_docente_table' AS m, (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations) AS b) t
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_10_15_130000_create_proyecto_docente_table');
