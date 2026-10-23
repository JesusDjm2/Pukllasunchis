-- Titulación: pasos de la Tesis (Licenciatura) que se registran desde la ficha del gestor.
-- Equivale a la migración 2026_10_23_000001_titulacion_tesis_pasos_licenciatura.
--  * enlaces_dictamen: enlaces de los dictámenes de los dictaminantes (uno o más, lista JSON).
--  * link_tesis: enlace de la tesis.
--  * licenciatura_escuela / fecha_licenciatura_escuela: grado de Licenciatura aceptado por la Escuela.
-- La situación de la tesis usa la columna `estado` que ya existe (no cambia la estructura).
-- Se puede correr una sola vez; no modifica ni borra datos existentes.

ALTER TABLE `titulacion_tesis`
    ADD COLUMN `enlaces_dictamen` LONGTEXT NULL AFTER `fecha_extension`,
    ADD COLUMN `link_tesis` VARCHAR(500) NULL AFTER `rd_tesis`,
    ADD COLUMN `licenciatura_escuela` TINYINT(1) NOT NULL DEFAULT 0 AFTER `estado`,
    ADD COLUMN `fecha_licenciatura_escuela` DATE NULL AFTER `licenciatura_escuela`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_23_000001_titulacion_tesis_pasos_licenciatura', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
