<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * PPD dura un año (periodo PPD anual: las notas se archivan una vez al año), pero se
     * matricula por SEMESTRE. Antes había una sola matrícula por periodo, así que la del
     * 2.º semestre sobrescribía a la del 1.º y heredaba "voucher verificado / ficha enviada".
     *
     *  - matriculas_ppd.semestre (1|2): cada semestre tiene su matrícula, voucher y envío.
     *  - periodo_actual_ppds.semestre_matricula: qué semestre está abierto para matricularse
     *    (lo elige administración en el periodo; no se deduce del ciclo del alumno).
     *
     * Regularización 2026 (pedido del usuario, 02/10/2026):
     *  - las 19 matrículas existentes del periodo vigente quedan como 1.er semestre;
     *  - TODOS los alumnos PPD activos de 2026 (con ficha) quedan matriculados en el 1.er semestre
     *    (a los que no tenían matrícula se les crea con el voucher de su ficha);
     *  - esos 19 también quedan matriculados en el 2.º semestre (mismo voucher, SIN verificar);
     *  - el periodo vigente queda con la matrícula abierta para el 2.º semestre.
     * SQL plano a propósito: producción solo se actualiza pegando SQL.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE matriculas_ppd ADD COLUMN semestre TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER periodo_actual_ppd_id');
        // El índice nuevo empieza por ppd_id: sigue sirviendo a la FK de ppd_id al quitar el viejo.
        DB::statement('ALTER TABLE matriculas_ppd ADD UNIQUE KEY matriculas_ppd_ppd_periodo_semestre_unique (ppd_id, periodo_actual_ppd_id, semestre)');
        DB::statement('ALTER TABLE matriculas_ppd DROP INDEX matriculas_ppd_ppd_id_periodo_actual_ppd_id_unique');
        DB::statement('ALTER TABLE periodo_actual_ppds ADD COLUMN semestre_matricula TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER formulario_habilitado');

        // Alumnos PPD activos de 2026: rol alumnoB, no egresados, no retirados, con ficha.
        $activos = "SELECT p.id AS ppd_id, u.programa_id, u.ciclo_id, u.condicion, p.num_comprobante
            FROM users u JOIN ppds p ON p.user_id = u.id
            JOIN model_has_roles mr ON mr.model_id = u.id AND mr.model_type = 'App\\\\Models\\\\User'
            JOIN roles r ON r.id = mr.role_id AND r.name = 'alumnoB'
            WHERE (u.condicion IS NULL OR u.condicion <> 'Egresado') AND (u.perfil IS NULL OR u.perfil <> 'Retirado')";
        // Ciclo I de cada programa (el 1.er semestre de PPD es el Ciclo I)
        $cicloI = "(SELECT c.id FROM ciclos c WHERE c.programa_id = a.programa_id AND TRIM(c.nombre) IN ('I', 'Ciclo I') LIMIT 1)";

        // 1) 2.º semestre (va PRIMERO, cuando solo existen las 19 originales). Solo corre si el periodo aún no
        //    tiene NINGUNA matrícula de 2.º semestre: si el SQL se ejecuta dos veces, no copia también las
        //    del 1.er semestre creadas en el paso 2.
        // las matrículas que ya existían se copian como 2.º semestre (Ciclo II),
        //    con su mismo voucher pero SIN verificar ni enviar (es otro trámite).
        DB::statement("
            INSERT INTO matriculas_ppd (ppd_id, periodo_actual_ppd_id, semestre, programa_id, ciclo_id, condicion, fecha_completado, estado, comprobante, voucher_verificado, created_at, updated_at)
            SELECT m.ppd_id, m.periodo_actual_ppd_id, 2, u.programa_id,
                   COALESCE((SELECT c.id FROM ciclos c WHERE c.programa_id = u.programa_id AND TRIM(c.nombre) IN ('II', 'Ciclo II') LIMIT 1), u.ciclo_id),
                   u.condicion, NOW(), 'matriculado', m.comprobante, 0, NOW(), NOW()
            FROM matriculas_ppd m
            JOIN periodo_actual_ppds pa ON pa.id = m.periodo_actual_ppd_id AND pa.actual = 1
            JOIN ppds p ON p.id = m.ppd_id JOIN users u ON u.id = p.user_id
            WHERE m.semestre = 1
              AND NOT EXISTS (SELECT 1 FROM matriculas_ppd m2 WHERE m2.periodo_actual_ppd_id = m.periodo_actual_ppd_id AND m2.semestre = 2)
        ");

        // 2) 1.er semestre: crear la matrícula a quien no la tenía (con el voucher de su ficha)
        DB::statement("
            INSERT INTO matriculas_ppd (ppd_id, periodo_actual_ppd_id, semestre, programa_id, ciclo_id, condicion, fecha_completado, estado, comprobante, voucher_verificado, created_at, updated_at)
            SELECT a.ppd_id, pa.id, 1, a.programa_id, $cicloI, a.condicion, COALESCE(pa.fecha_inicio, NOW()), 'matriculado', a.num_comprobante, 0, NOW(), NOW()
            FROM ($activos) a
            JOIN periodo_actual_ppds pa ON pa.actual = 1
            WHERE NOT EXISTS (SELECT 1 FROM matriculas_ppd m WHERE m.ppd_id = a.ppd_id AND m.periodo_actual_ppd_id = pa.id AND m.semestre = 1)
        ");

        // 3) La foto de ciclo del 1.er semestre es el Ciclo I (la Fase 2 había puesto el ciclo actual).
        DB::statement("
            UPDATE matriculas_ppd m
            JOIN periodo_actual_ppds pa ON pa.id = m.periodo_actual_ppd_id AND pa.actual = 1
            SET m.ciclo_id = COALESCE((SELECT c.id FROM ciclos c WHERE c.programa_id = m.programa_id AND TRIM(c.nombre) IN ('I', 'Ciclo I') LIMIT 1), m.ciclo_id)
            WHERE m.semestre = 1
        ");

        // 4) El periodo vigente queda con la matrícula abierta para el 2.º semestre.
        DB::statement('UPDATE periodo_actual_ppds SET semestre_matricula = 2 WHERE actual = 1');
    }

    public function down(): void
    {
        DB::statement('DELETE FROM matriculas_ppd WHERE semestre = 2');
        DB::statement('ALTER TABLE matriculas_ppd ADD UNIQUE KEY matriculas_ppd_ppd_id_periodo_actual_ppd_id_unique (ppd_id, periodo_actual_ppd_id)');
        DB::statement('ALTER TABLE matriculas_ppd DROP INDEX matriculas_ppd_ppd_periodo_semestre_unique');
        DB::statement('ALTER TABLE matriculas_ppd DROP COLUMN semestre');
        DB::statement('ALTER TABLE periodo_actual_ppds DROP COLUMN semestre_matricula');
    }
};
