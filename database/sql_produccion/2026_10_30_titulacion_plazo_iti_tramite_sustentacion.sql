SET NAMES utf8mb4;

ALTER TABLE `titulacion_ptis`
    ADD COLUMN `fecha_iti` DATE NULL AFTER `link_iti`;

ALTER TABLE `titulacion_tesis`
    ADD COLUMN `tramite_sust_pagado_en` DATE NULL AFTER `rd_tesis`,
    ADD COLUMN `tramite_sust_recibo` VARCHAR(100) NULL AFTER `tramite_sust_pagado_en`,
    ADD COLUMN `correo_rd_tesis_enviado_en` DATETIME NULL AFTER `tramite_sust_recibo`;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_10_30_000001_titulacion_plazo_iti_tramite_sustentacion' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_30_000001_titulacion_plazo_iti_tramite_sustentacion');
