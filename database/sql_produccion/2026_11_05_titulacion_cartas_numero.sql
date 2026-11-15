-- N° de la Carta de Presentación (correlativo por año). Equivale a la migración
-- 2026_11_05_000002_titulacion_cartas_numero. Correr DESPUÉS de 2026_11_05_titulacion_cartas_aceptacion.sql.
SET NAMES utf8mb4;

ALTER TABLE `titulacion_cartas_aceptacion`
    ADD COLUMN `numero_carta` VARCHAR(10) NULL DEFAULT NULL AFTER `carta`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_11_05_000002_titulacion_cartas_numero' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_05_000002_titulacion_cartas_numero');
