-- Fase 2: programa/ciclo/condición del alumno guardados en cada matrícula (2026-10-01)
-- Ejecutar UNA vez en la BD de producción, ANTES de subir el código. Requiere la Fase 1.

SET NAMES utf8mb4;

ALTER TABLE `matriculas`
  ADD COLUMN `programa_id` bigint(20) unsigned DEFAULT NULL AFTER `id`,
  ADD COLUMN `ciclo_id` bigint(20) unsigned DEFAULT NULL AFTER `programa_id`,
  ADD COLUMN `condicion` varchar(255) DEFAULT NULL AFTER `ciclo_id`,
  ADD CONSTRAINT `matriculas_programa_id_foreign` FOREIGN KEY (`programa_id`) REFERENCES `programas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `matriculas_ciclo_id_foreign` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos` (`id`) ON DELETE SET NULL;

ALTER TABLE `matriculas_ppd`
  ADD COLUMN `programa_id` bigint(20) unsigned DEFAULT NULL AFTER `id`,
  ADD COLUMN `ciclo_id` bigint(20) unsigned DEFAULT NULL AFTER `programa_id`,
  ADD COLUMN `condicion` varchar(255) DEFAULT NULL AFTER `ciclo_id`,
  ADD CONSTRAINT `matriculas_ppd_programa_id_foreign` FOREIGN KEY (`programa_id`) REFERENCES `programas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `matriculas_ppd_ciclo_id_foreign` FOREIGN KEY (`ciclo_id`) REFERENCES `ciclos` (`id`) ON DELETE SET NULL;

UPDATE matriculas m SET m.ciclo_id = ( SELECT c.ciclo_id FROM periodos p JOIN cursos c ON c.id = p.curso_id WHERE p.alumno_id = m.alumno_id AND p.periodo_actual_id = m.periodo_actual_id GROUP BY c.ciclo_id ORDER BY COUNT(*) DESC, c.ciclo_id DESC LIMIT 1 ) WHERE m.ciclo_id IS NULL;

UPDATE matriculas m JOIN periodo_actual pa ON pa.id = m.periodo_actual_id AND pa.actual = 1 JOIN alumnos a ON a.id = m.alumno_id JOIN users u ON u.id = a.user_id SET m.ciclo_id = COALESCE(m.ciclo_id, u.ciclo_id, a.ciclo_id), m.condicion = COALESCE(m.condicion, u.condicion);

UPDATE matriculas m JOIN ciclos c ON c.id = m.ciclo_id SET m.programa_id = c.programa_id WHERE m.programa_id IS NULL;

UPDATE matriculas_ppd m SET m.ciclo_id = ( SELECT c.ciclo_id FROM periodo_ppds p JOIN cursos c ON c.id = p.curso_id WHERE p.alumno_id = m.ppd_id AND p.periodo_actual_ppd_id = m.periodo_actual_ppd_id GROUP BY c.ciclo_id ORDER BY COUNT(*) DESC, c.ciclo_id DESC LIMIT 1 ) WHERE m.ciclo_id IS NULL;

UPDATE matriculas_ppd m JOIN periodo_actual_ppds pa ON pa.id = m.periodo_actual_ppd_id AND pa.actual = 1 JOIN ppds a ON a.id = m.ppd_id JOIN users u ON u.id = a.user_id SET m.ciclo_id = COALESCE(m.ciclo_id, u.ciclo_id, a.ciclo_id), m.condicion = COALESCE(m.condicion, u.condicion);

UPDATE matriculas_ppd m JOIN ciclos c ON c.id = m.ciclo_id SET m.programa_id = c.programa_id WHERE m.programa_id IS NULL;

INSERT INTO migrations (migration, batch) SELECT '2026_10_01_000003_add_snapshot_ciclo_to_matriculas', IFNULL(MAX(batch),0)+1 FROM migrations;
