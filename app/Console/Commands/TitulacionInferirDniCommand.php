<?php

namespace App\Console\Commands;

use App\Models\Titulacion\Importacion;
use App\Services\Titulacion\NombresImportados;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * La hoja de secretaría (expo-sust-marilyn) no trae DNI; los expedientes
 * de Marilyn sí. La misma persona quedaba como dos pendientes distintos y,
 * al crearle la cuenta por DNI, sus datos de actas/jurados de la hoja de
 * secretaría no se le aplicaban (se buscan por DNI).
 *
 * Este comando copia el DNI a las filas sin DNI cuyo nombre coincide con
 * UNA sola persona que sí tiene DNI en staging. Se marca con
 * dni_inferido=true en datos_normalizados para poder rastrearlo. Si hay
 * dos candidatos, no toca nada.
 */
class TitulacionInferirDniCommand extends Command
{
    protected $signature = 'titulacion:inferir-dni {--dry-run : Solo muestra el resultado, sin guardar}';

    protected $description = 'Copia el DNI a filas de staging sin DNI que coinciden con una única persona con DNI';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Una entrada por DNI con todos los nombres con que aparece.
        $personasConDni = Importacion::whereNotNull('dni')->where('dni', '!=', '')
            ->where('estado_match', '!=', 'dividido')
            ->get()
            ->groupBy('dni')
            ->map(fn ($filas) => $filas->map(fn (Importacion $f) => $f->nombre_estudiante
                ?: trim(($f->datos_normalizados['apellidos'] ?? '').' '.($f->datos_normalizados['nombres'] ?? '')))
                ->filter()->unique()->values());

        $sinDni = Importacion::where(fn ($q) => $q->whereNull('dni')->orWhere('dni', ''))
            ->whereNull('user_id')
            ->where('estado_match', 'sin_match')
            ->whereNotNull('nombre_estudiante')
            ->get();

        $asignados = 0;
        $ambiguos = 0;
        $ejemplos = [];

        DB::transaction(function () use ($sinDni, $personasConDni, $dryRun, &$asignados, &$ambiguos, &$ejemplos) {
            foreach ($sinDni as $fila) {
                $candidatos = $personasConDni->filter(fn ($nombres) => $nombres->contains(
                    fn ($n) => NombresImportados::mismaPersona($fila->nombre_estudiante, $n)
                ));

                if ($candidatos->count() > 1) {
                    $ambiguos++;
                    continue;
                }
                if ($candidatos->isEmpty()) {
                    continue;
                }

                $dni = (string) $candidatos->keys()->first();
                $asignados++;
                if (count($ejemplos) < 8) {
                    $ejemplos[] = [$fila->nombre_estudiante, $candidatos->first()->first(), $dni];
                }

                if (! $dryRun) {
                    $fila->update([
                        'dni' => $dni,
                        'datos_normalizados' => array_merge($fila->datos_normalizados ?? [], ['dni_inferido' => true]),
                    ]);
                }
            }
        });

        $this->table(['Fila sin DNI', 'Coincide con', 'DNI'], $ejemplos);
        $this->info(($dryRun ? '[dry-run] Se asignaría' : 'Asignado')." DNI a {$asignados} filas. Con más de un candidato (no se tocaron): {$ambiguos}.");

        return self::SUCCESS;
    }
}
