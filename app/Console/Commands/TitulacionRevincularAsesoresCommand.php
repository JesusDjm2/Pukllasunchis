<?php

namespace App\Console\Commands;

use App\Models\Titulacion\Acta;
use App\Models\Titulacion\Importacion;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\Tesis;
use App\Models\User;
use App\Services\Titulacion\ResolverDocente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Vuelve a intentar vincular el asesor de PTIs/Tesis/Actas que quedaron
 * sin asesor_id al importar (el criterio anterior no entendía títulos
 * repetidos, orden "Apellidos, Nombres" ni errores de tipeo). Toma el
 * nombre en texto de las filas de staging de esa persona.
 *
 * Solo completa asesor_id vacíos: nunca pisa uno puesto a mano.
 */
class TitulacionRevincularAsesoresCommand extends Command
{
    protected $signature = 'titulacion:revincular-asesores {--dry-run : Solo muestra qué se vincularía, sin guardar}';

    protected $description = 'Vincula a docentes reales los asesores de PTI/Tesis/Actas que quedaron sin asesor';

    /** @var array<string,int> nombre en texto => veces que no se pudo resolver */
    private array $sinResolver = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $contadores = ['PTI' => 0, 'Tesis' => 0, 'Acta' => 0];

        DB::transaction(function () use (&$contadores, $dryRun) {
            $objetivos = [
                'PTI' => [Pti::whereNull('asesor_id')->with('seguimiento')->get(), ['asesor_pti', 'asesor']],
                'Tesis' => [Tesis::whereNull('asesor_id')->with('seguimiento')->get(), ['asesor_tesis', 'asesor_pti', 'asesor']],
                'Acta' => [Acta::whereNull('asesor_id')->with('seguimiento')->get(), ['asesor_tesis', 'asesor_pti', 'asesor']],
            ];

            foreach ($objetivos as $tipo => [$registros, $claves]) {
                foreach ($registros as $registro) {
                    $userId = $registro->seguimiento?->user_id;
                    if (! $userId) {
                        continue;
                    }

                    $docenteId = $this->resolverDesdeStaging($userId, $claves);
                    if ($docenteId) {
                        $contadores[$tipo]++;
                        if (! $dryRun) {
                            $registro->update(['asesor_id' => $docenteId]);
                        }
                    }
                }
            }
        });

        $this->info(($dryRun ? '[dry-run] Se vincularían' : 'Vinculados').": {$contadores['PTI']} PTIs, {$contadores['Tesis']} Tesis, {$contadores['Acta']} Actas.");

        if ($this->sinResolver) {
            arsort($this->sinResolver);
            $this->warn('Nombres de asesor sin docente único en el sistema:');
            $this->table(['Nombre en el archivo', 'Registros'], collect($this->sinResolver)
                ->map(fn ($n, $nombre) => [mb_substr(preg_replace('/\s+/', ' ', $nombre), 0, 70), $n])
                ->values()->all());
        }

        return self::SUCCESS;
    }

    private function resolverDesdeStaging(int $userId, array $claves): ?int
    {
        $dnis = collect([User::find($userId)?->dni])->filter();
        $filas = Importacion::where('user_id', $userId)
            ->when($dnis->isNotEmpty(), fn ($q) => $q->orWhereIn('dni', $dnis))
            ->get();

        $vistos = [];
        foreach ($claves as $clave) {
            foreach ($filas as $fila) {
                $nombre = trim((string) (($fila->datos_normalizados ?? [])[$clave] ?? ''));
                if ($nombre === '' || isset($vistos[$nombre])) {
                    continue;
                }
                $vistos[$nombre] = true;
                if ($id = ResolverDocente::resolver($nombre)) {
                    return $id;
                }
            }
        }

        foreach (array_keys($vistos) as $nombre) {
            $this->sinResolver[$nombre] = ($this->sinResolver[$nombre] ?? 0) + 1;
        }

        return null;
    }
}
