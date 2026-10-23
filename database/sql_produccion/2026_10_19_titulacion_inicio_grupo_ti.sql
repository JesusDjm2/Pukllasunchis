-- Titulación: inicio del proceso por grupo + pasos nuevos del TI.
-- Equivale a la migración 2026_10_19_200000_titulacion_inicio_grupo_ti.
--  * seguimiento_titulacion.grupo_id: integrantes que iniciaron juntos el PTI.
--  * titulacion_tesis.url_repositorio_ti: enlace del TI en el Repositorio Institucional.
--  * titulacion_participantes: asesores (1 o más) y jurado del TI/Tesis, con cargo opcional.

ALTER TABLE `seguimiento_titulacion`
    ADD COLUMN `grupo_id` BIGINT UNSIGNED NULL AFTER `numero_expediente`,
    ADD INDEX `seguimiento_titulacion_grupo_id_index` (`grupo_id`);

ALTER TABLE `titulacion_tesis`
    ADD COLUMN `url_repositorio_ti` VARCHAR(500) NULL AFTER `rd_ti`;

CREATE TABLE `titulacion_participantes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `seguimiento_id` BIGINT UNSIGNED NOT NULL,
    `etapa` VARCHAR(10) NOT NULL,
    `funcion` VARCHAR(10) NOT NULL,
    `docente_id` BIGINT UNSIGNED NULL,
    `asesor_externo_id` BIGINT UNSIGNED NULL,
    `cargo` VARCHAR(40) NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `titulacion_participantes_seguimiento_id_etapa_funcion_index` (`seguimiento_id`, `etapa`, `funcion`),
    CONSTRAINT `titulacion_participantes_seguimiento_id_foreign` FOREIGN KEY (`seguimiento_id`) REFERENCES `seguimiento_titulacion` (`id`) ON DELETE CASCADE,
    CONSTRAINT `titulacion_participantes_docente_id_foreign` FOREIGN KEY (`docente_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `titulacion_participantes_asesor_externo_id_foreign` FOREIGN KEY (`asesor_externo_id`) REFERENCES `titulacion_asesores` (`id`) ON DELETE SET NULL,
    CONSTRAINT `titulacion_participantes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `titulacion_participantes_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_19_200000_titulacion_inicio_grupo_ti', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
