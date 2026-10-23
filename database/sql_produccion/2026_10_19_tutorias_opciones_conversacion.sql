-- Tutorías: la prioridad pasa a ser "¿cómo quieres conversar?" (se guarda en `urgencia`),
-- docente elegido cuando el alumno pide hablar con otro docente, y motivo opcional.
-- Equivale a la migración 2026_10_19_100000_tutorias_opciones_conversacion.

ALTER TABLE `tutorias`
    ADD COLUMN `docente_id` BIGINT UNSIGNED NULL AFTER `alumno_id`,
    ADD CONSTRAINT `tutorias_docente_id_foreign` FOREIGN KEY (`docente_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `tutorias` MODIFY `motivo` TEXT NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_19_100000_tutorias_opciones_conversacion', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
