-- Matrícula PPD por semestre (2026-10-02)
-- PPD es anual (notas una vez al año) pero se matricula por semestre: columna `semestre` en la matrícula,
-- `semestre_matricula` en el periodo, y regularización 2026 (todos los activos matriculados en el 1.er semestre;
-- las 19 matrículas existentes también en el 2.º; periodo vigente abierto para el 2.º semestre).
-- Ejecutar UNA vez en la BD de producción, ANTES de subir el código.

SET NAMES utf8mb4;

ALTER TABLE matriculas_ppd ADD COLUMN semestre TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER periodo_actual_ppd_id;

ALTER TABLE matriculas_ppd ADD UNIQUE KEY matriculas_ppd_ppd_periodo_semestre_unique (ppd_id, periodo_actual_ppd_id, semestre);

ALTER TABLE matriculas_ppd DROP INDEX matriculas_ppd_ppd_id_periodo_actual_ppd_id_unique;

ALTER TABLE periodo_actual_ppds ADD COLUMN semestre_matricula TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER formulario_habilitado;

INSERT INTO matriculas_ppd (ppd_id, periodo_actual_ppd_id, semestre, programa_id, ciclo_id, condicion, fecha_completado, estado, comprobante, voucher_verificado, created_at, updated_at) SELECT m.ppd_id, m.periodo_actual_ppd_id, 2, u.programa_id, COALESCE((SELECT c.id FROM ciclos c WHERE c.programa_id = u.programa_id AND TRIM(c.nombre) IN ('II', 'Ciclo II') LIMIT 1), u.ciclo_id), u.condicion, NOW(), 'matriculado', m.comprobante, 0, NOW(), NOW() FROM matriculas_ppd m JOIN periodo_actual_ppds pa ON pa.id = m.periodo_actual_ppd_id AND pa.actual = 1 JOIN ppds p ON p.id = m.ppd_id JOIN users u ON u.id = p.user_id WHERE m.semestre = 1 AND NOT EXISTS (SELECT 1 FROM matriculas_ppd m2 WHERE m2.periodo_actual_ppd_id = m.periodo_actual_ppd_id AND m2.semestre = 2);

INSERT INTO matriculas_ppd (ppd_id, periodo_actual_ppd_id, semestre, programa_id, ciclo_id, condicion, fecha_completado, estado, comprobante, voucher_verificado, created_at, updated_at) SELECT a.ppd_id, pa.id, 1, a.programa_id, (SELECT c.id FROM ciclos c WHERE c.programa_id = a.programa_id AND TRIM(c.nombre) IN ('I', 'Ciclo I') LIMIT 1), a.condicion, COALESCE(pa.fecha_inicio, NOW()), 'matriculado', a.num_comprobante, 0, NOW(), NOW() FROM (SELECT p.id AS ppd_id, u.programa_id, u.ciclo_id, u.condicion, p.num_comprobante FROM users u JOIN ppds p ON p.user_id = u.id JOIN model_has_roles mr ON mr.model_id = u.id AND mr.model_type = 'App\\Models\\User' JOIN roles r ON r.id = mr.role_id AND r.name = 'alumnoB' WHERE (u.condicion IS NULL OR u.condicion <> 'Egresado') AND (u.perfil IS NULL OR u.perfil <> 'Retirado')) a JOIN periodo_actual_ppds pa ON pa.actual = 1 WHERE NOT EXISTS (SELECT 1 FROM matriculas_ppd m WHERE m.ppd_id = a.ppd_id AND m.periodo_actual_ppd_id = pa.id AND m.semestre = 1);

UPDATE matriculas_ppd m JOIN periodo_actual_ppds pa ON pa.id = m.periodo_actual_ppd_id AND pa.actual = 1 SET m.ciclo_id = COALESCE((SELECT c.id FROM ciclos c WHERE c.programa_id = m.programa_id AND TRIM(c.nombre) IN ('I', 'Ciclo I') LIMIT 1), m.ciclo_id) WHERE m.semestre = 1;

UPDATE periodo_actual_ppds SET semestre_matricula = 2 WHERE actual = 1;

INSERT INTO migrations (migration, batch) SELECT '2026_10_02_000001_add_semestre_to_matriculas_ppd', IFNULL(MAX(batch),0)+1 FROM migrations;
