-- Titulación: enlace del TI (nuevo) y estados del enlace del TI y de la Tesis.
-- Equivale a la migración 2026_10_29_000001_titulacion_enlaces_ti_tesis_estados.
--  * link_ti: enlace del Trabajo de Investigación que comparte el estudiante.
--  * link_ti_estado / link_tesis_estado: 'enviado' (por el estudiante) o 'aprobado' (por el asesor).
--  * *_enviado_en / *_aprobado_en: fecha de cada estado.
-- La RD de aprobación de exposición (TI) y la de sustentación (Tesis) solo se registran con el
-- enlace aprobado. Solo agrega columnas: no modifica ni borra datos existentes.
-- Correr UNA sola vez y ANTES de subir el código.

SET NAMES utf8mb4;

ALTER TABLE `titulacion_tesis`
    ADD COLUMN `link_ti` VARCHAR(500) NULL AFTER `rd_ti`,
    ADD COLUMN `link_ti_estado` VARCHAR(20) NULL AFTER `link_ti`,
    ADD COLUMN `link_ti_enviado_en` DATE NULL AFTER `link_ti_estado`,
    ADD COLUMN `link_ti_aprobado_en` DATE NULL AFTER `link_ti_enviado_en`,
    ADD COLUMN `link_tesis_estado` VARCHAR(20) NULL AFTER `link_tesis`,
    ADD COLUMN `link_tesis_enviado_en` DATE NULL AFTER `link_tesis_estado`,
    ADD COLUMN `link_tesis_aprobado_en` DATE NULL AFTER `link_tesis_enviado_en`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_10_29_000001_titulacion_enlaces_ti_tesis_estados' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) 
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_29_000001_titulacion_enlaces_ti_tesis_estados');
