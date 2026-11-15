-- Cartas de aceptación importadas de los formularios de Google: origen, datos extra y clave de importación.
-- Equivale a la migración 2026_11_06_000001_titulacion_cartas_origen.
-- Correr DESPUÉS de 2026_11_05_titulacion_cartas_aceptacion.sql y 2026_11_05_titulacion_cartas_numero.sql.
SET NAMES utf8mb4;

ALTER TABLE `titulacion_cartas_aceptacion`
    ADD COLUMN `origen` VARCHAR(20) NOT NULL DEFAULT 'sistema' AFTER `lote`,
    ADD COLUMN `datos_extra` TEXT NULL DEFAULT NULL AFTER `numero_carta`,
    ADD COLUMN `clave_importacion` VARCHAR(40) NULL DEFAULT NULL AFTER `datos_extra`,
    ADD INDEX `titulacion_cartas_aceptacion_clave_importacion_index` (`clave_importacion`),
    -- Los comprobantes del formulario de Google son enlaces de Drive de hasta ~340 caracteres.
    MODIFY COLUMN `comprobante` VARCHAR(500) NOT NULL;

-- Las registradas a mano («¿Ya la tramitó fuera del sistema?») antes de que existiera esta columna.
UPDATE `titulacion_cartas_aceptacion` SET `origen` = 'manual'
WHERE `observacion` LIKE 'Registrada por el Área de Investigación%' OR `observacion` LIKE 'Registrada por Secretaría%';

INSERT INTO `migrations` (`migration`, `batch`)
SELECT t.migration, t.batch FROM (
    SELECT '2026_11_06_000001_titulacion_cartas_origen' AS migration, COALESCE(MAX(`batch`), 0) + 1 AS batch FROM `migrations`
) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_06_000001_titulacion_cartas_origen');
