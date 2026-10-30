SET NAMES utf8mb4;

ALTER TABLE `titulacion_resoluciones`
    ADD COLUMN `correo_enviado_en` DATETIME NULL DEFAULT NULL AFTER `agrupa`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_10_30_000003_titulacion_resoluciones_correo' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_30_000003_titulacion_resoluciones_correo');
