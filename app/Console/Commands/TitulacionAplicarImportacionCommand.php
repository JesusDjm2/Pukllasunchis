<?php

namespace App\Console\Commands;

use App\Http\Controllers\Titulacion\TitulacionController;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\Importacion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Los datos de staging (PTI, Tesis, RDs, expediente...) solo se volcaban al
 * CREAR una cuenta. Este comando los aplica a quienes ya tienen cuenta:
 *
 * - Egresados (condicion='Egresado'): se aplica sobre su seguimiento.
 * - Alumnos FID activos (rol alumno, no inhabilitados): se les inicia el
 *   seguimiento en "PTI en proceso" (igual que el botón "Iniciar proceso")
 *   sin tocar su condición, y se aplica.
 * - Cualquier otro (docente, inhabilitado, PPD sin egresar...) se omite y
 *   se lista: eso lo decide una persona desde el panel.
 *
 * Es idempotente: PTI/Tesis usan updateOrCreate y las RDs firstOrCreate.
 */
class TitulacionAplicarImportacionCommand extends Command
{
    protected $signature = 'titulacion:aplicar-importacion
                            {--origen= : Solo filas de este origen (ej. exp-fid-marilyn)}
                            {--dry-run : Solo muestra a quién se aplicaría, sin guardar}';

    protected $description = 'Aplica los datos de titulación del staging a las personas que ya tienen cuenta';

    public function handle(TitulacionController $titulacion): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $dnis = Importacion::whereNotNull('user_id')
            ->whereNotNull('dni')->where('dni', '!=', '')
            ->where('estado_match', '!=', 'dividido')
            ->when($this->option('origen'), fn ($q, $o) => $q->where('origen', $o))
            ->get(['dni', 'user_id'])
            ->unique('dni');

        $aplicados = ['egresado' => 0, 'activo' => 0];
        $omitidos = [];

        foreach ($dnis as $fila) {
            $user = User::with('seguimientoTitulacion')->find($fila->user_id);
            if (! $user) {
                continue;
            }

            if ($user->condicion === 'Egresado') {
                $tipo = 'egresado';
            } elseif ($user->hasRole('alumno') && ! $user->hasRole('inhabilitado')) {
                $tipo = 'activo';
            } else {
                $omitidos[] = [$user->id, $user->apellidos.', '.$user->name, $fila->dni, implode(',', $user->getRoleNames()->all()).' / '.($user->condicion ?? '—')];
                continue;
            }

            $aplicados[$tipo]++;
            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($user, $fila, $titulacion) {
                // Arranca "Sin PTI"; el estado real lo calcula recalcularEstado()
                // a medida que se guardan PTI/TI/Tesis/RD desde el staging.
                $seguimiento = $user->seguimientoTitulacion ?? SeguimientoTitulacion::create([
                    'user_id' => $user->id,
                    'estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti'),
                ]);
                $seguimiento->setRelation('user', $user);

                $titulacion->aplicarDatosTitulacionDesdeCsv($seguimiento, $fila->dni);
            });
        }

        if ($omitidos) {
            $this->warn('Omitidos (tienen cuenta pero no son egresados ni alumnos activos — revisar en el panel):');
            $this->table(['user_id', 'Nombre', 'DNI', 'Roles / condición'], $omitidos);
        }

        $this->info(($dryRun ? '[dry-run] Se aplicaría' : 'Aplicado')." a {$aplicados['egresado']} egresados y {$aplicados['activo']} alumnos activos (seguimiento iniciado en \"PTI en proceso\").");

        return self::SUCCESS;
    }
}
