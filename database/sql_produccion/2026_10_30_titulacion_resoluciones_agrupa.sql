SET NAMES utf8mb4;

ALTER TABLE `titulacion_resoluciones`
    ADD COLUMN `agrupa` TINYINT(1) NOT NULL DEFAULT 1 AFTER `detalle`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_10_30_000002_titulacion_resoluciones_agrupa' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_30_000002_titulacion_resoluciones_agrupa');
