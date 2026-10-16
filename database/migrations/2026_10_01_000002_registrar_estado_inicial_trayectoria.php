<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Foto del estado actual al estrenar trayectoria_eventos: quien HOY está con licencia,
     * retirado, inhabilitado, egresado o reincorporado queda con su evento (origen
     * 'migracion', sin fecha_inicio porque no se conoce). Así las licencias activas existen
     * desde el primer día; su fecha real se completa a mano desde el expediente.
     * SQL plano a propósito: producción solo se actualiza pegando SQL (no hay artisan).
     */
    public function up(): void
    {
        $estudiantes = "SELECT mr.model_id FROM model_has_roles mr JOIN roles r ON r.id = mr.role_id
            WHERE mr.model_type = 'App\\\\Models\\\\User' AND r.name IN ('alumno', 'alumnoB', 'inhabilitado')";

        DB::statement("
            INSERT INTO trayectoria_eventos (user_id, tipo, valor_nuevo, ciclo_origen_id, motivo, origen, created_at, updated_at)
            SELECT u.id,
                   CASE u.perfil WHEN 'Licencia' THEN 'licencia' WHEN 'Retirado' THEN 'retiro' ELSE 'inhabilitacion' END,
                   u.perfil, u.ciclo_id, 'Estado registrado al iniciar el seguimiento de trayectoria', 'migracion', NOW(), NOW()
            FROM users u
            WHERE u.perfil IN ('Licencia', 'Retirado', 'Deuda', 'Sin matrícula')
              AND u.id IN ($estudiantes)
              AND NOT EXISTS (SELECT 1 FROM trayectoria_eventos t WHERE t.user_id = u.id AND t.origen = 'migracion')
        ");

        DB::statement("
            INSERT INTO trayectoria_eventos (user_id, tipo, valor_nuevo, ciclo_origen_id, motivo, origen, created_at, updated_at)
            SELECT u.id,
                   CASE u.condicion WHEN 'Egresado' THEN 'egreso' ELSE 'reincorporacion' END,
                   u.condicion, u.ciclo_id, 'Estado registrado al iniciar el seguimiento de trayectoria', 'migracion', NOW(), NOW()
            FROM users u
            WHERE u.condicion IN ('Egresado', 'Reincorporación')
              AND u.id IN ($estudiantes)
              AND NOT EXISTS (SELECT 1 FROM trayectoria_eventos t WHERE t.user_id = u.id AND t.origen = 'migracion'
                              AND t.tipo IN ('egreso', 'reincorporacion'))
        ");
    }

    public function down(): void
    {
        DB::table('trayectoria_eventos')->where('origen', 'migracion')->delete();
    }
};
