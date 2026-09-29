<?php

namespace App\Console\Commands;

use App\Models\Titulacion\Importacion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use App\Services\Titulacion\Expediente;
use App\Services\Titulacion\NombresImportados;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga el número de expediente en seguimiento_titulacion.
 *
 * 1) Si se pasa {archivo} (una línea por persona: "Apellidos, Nombres<TAB>Nº 0001-T/PUK"),
 *    esa lista manda: se busca a la persona por nombre (coincidencia única)
 *    entre los egresados registrados; si todavía es pendiente, el número se
 *    guarda en sus filas de staging para aplicarse al crear su cuenta.
 * 2) Para quien siga sin número, se toma de lo que ya traían los archivos
 *    importados (columnas expediente / num_expediente / numero_expediente).
 *
 * Nunca pisa un número distinto sin avisar: los desacuerdos se listan.
 */
class TitulacionExpedientesCommand extends Command
{
    protected $signature = 'titulacion:expedientes {archivo? : Lista nombre<TAB>expediente} {--dry-run : Solo muestra el resultado}';

    protected $description = 'Carga los números de expediente de titulación (desde una lista y/o desde lo importado)';

    public const CLAVES_STAGING = ['expediente', 'num_expediente', 'numero_expediente'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $rep = ['lista_registrado' => 0, 'lista_pendiente' => 0, 'lista_sin_numero' => [], 'lista_no_encontrado' => [],
            'lista_ambiguo' => [], 'lista_cambio' => [], 'staging' => 0, 'desacuerdo_staging' => []];

        $registrados = User::where(fn ($q) => $q->where('condicion', 'Egresado')->orWhereHas('seguimientoTitulacion'))
            ->with('seguimientoTitulacion')->get(['id', 'name', 'apellidos', 'dni', 'condicion']);

        $ctl = app(\App\Http\Controllers\Titulacion\TitulacionController::class);
        $pendientes = collect((new \ReflectionMethod($ctl, 'pendientesAgrupados'))->invoke($ctl))->whereNull('user_id');
        $nombrePend = fn ($x) => $x['apellidos'] ? $x['apellidos'].' '.$x['nombres'] : (string) $x['nombre'];

        DB::transaction(function () use ($dryRun, &$rep, $registrados, $pendientes, $nombrePend) {
            $asignadosPorLista = [];

            // ---------- 1) Lista del usuario ----------
            if ($ruta = $this->argument('archivo')) {
                foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
                    [$bruto, $expTexto] = array_pad(explode("\t", $linea, 2), 2, '');
                    $nombre = trim(preg_replace('/\s+/', ' ', preg_replace('/\([^)]*\)/', '', $bruto)));
                    if ($nombre === '') {
                        continue;
                    }
                    $exp = Expediente::normalizar($expTexto);
                    if (! $exp) {
                        $rep['lista_sin_numero'][] = [$nombre, trim($expTexto)];
                        continue;
                    }

                    $reg = $registrados->filter(fn (User $u) => NombresImportados::mismaPersona($nombre, $u->apellidos.' '.$u->name));
                    if ($reg->count() === 1) {
                        $u = $reg->first();
                        $asignadosPorLista[$u->id] = $exp;
                        $seg = $u->seguimientoTitulacion;
                        $actual = $seg?->numero_expediente;
                        if ($actual && $actual !== $exp) {
                            $rep['lista_cambio'][] = [$nombre, $actual, $exp];
                        }
                        if ($actual !== $exp) {
                            $rep['lista_registrado']++;
                            if (! $dryRun) {
                                SeguimientoTitulacion::firstOrCreate(['user_id' => $u->id])->update(['numero_expediente' => $exp]);
                            }
                        }
                        continue;
                    }
                    if ($reg->count() > 1) {
                        $rep['lista_ambiguo'][] = [$nombre, $reg->map(fn ($u) => $u->apellidos.', '.$u->name)->implode(' | ')];
                        continue;
                    }

                    $pend = $pendientes->filter(fn ($x) => NombresImportados::mismaPersona($nombre, $nombrePend($x)));
                    if ($pend->count() === 1) {
                        $p = $pend->first();
                        $rep['lista_pendiente']++;
                        if (! $dryRun) {
                            $filas = $p['dni']
                                ? Importacion::where('dni', $p['dni'])
                                : Importacion::where('nombre_normalizado', Importacion::find($p['importacion_id'])->nombre_normalizado);
                            foreach ($filas->where('estado_match', '!=', 'dividido')->get() as $f) {
                                $f->update(['datos_normalizados' => array_merge($f->datos_normalizados ?? [], ['expediente' => $exp, 'expediente_confirmado' => true])]);
                            }
                        }
                        continue;
                    }
                    $rep[$pend->count() > 1 ? 'lista_ambiguo' : 'lista_no_encontrado'][] = [$nombre, $pend->count() > 1 ? 'varios pendientes' : ''];
                }
            }

            // ---------- 2) Lo que ya traían los archivos ----------
            foreach ($registrados as $u) {
                $filas = Importacion::where('estado_match', '!=', 'dividido')
                    ->where(fn ($q) => $q->where('user_id', $u->id)->when($u->dni, fn ($qq) => $qq->orWhere('dni', $u->dni)))
                    ->get();
                $delArchivo = $filas->map(fn ($f) => Expediente::deFila($f->datos_normalizados ?? [], $f->nombre_estudiante ?: $u->apellidos.' '.$u->name))
                    ->filter()->unique()->values();
                if ($delArchivo->isEmpty()) {
                    continue;
                }

                $yaTiene = $asignadosPorLista[$u->id] ?? $u->seguimientoTitulacion?->numero_expediente;
                if ($yaTiene) {
                    if (! $delArchivo->contains($yaTiene)) {
                        $rep['desacuerdo_staging'][] = [$u->apellidos.', '.$u->name, $yaTiene, $delArchivo->implode(' / ')];
                    }
                    continue;
                }
                if ($delArchivo->count() > 1) {
                    $rep['desacuerdo_staging'][] = [$u->apellidos.', '.$u->name, '(ninguno)', $delArchivo->implode(' / ')];
                    continue;
                }
                $rep['staging']++;
                if (! $dryRun) {
                    SeguimientoTitulacion::firstOrCreate(['user_id' => $u->id])->update(['numero_expediente' => $delArchivo->first()]);
                }
            }
        });

        $this->table(['Resultado', 'Cantidad'], [
            ['Lista → egresados registrados', $rep['lista_registrado']],
            ['Lista → pendientes (se aplica al crear la cuenta)', $rep['lista_pendiente']],
            ['Lista sin número válido', count($rep['lista_sin_numero'])],
            ['Lista: no encontrado', count($rep['lista_no_encontrado'])],
            ['Lista: más de un candidato', count($rep['lista_ambiguo'])],
            ['Desde archivos importados', $rep['staging']],
        ]);
        foreach (['lista_sin_numero' => ['Nombre', 'Texto'], 'lista_no_encontrado' => ['Nombre', ''], 'lista_ambiguo' => ['Nombre', 'Candidatos'],
            'lista_cambio' => ['Nombre', 'Tenía', 'Nuevo (lista)'], 'desacuerdo_staging' => ['Nombre', 'Asignado', 'Archivos dicen']] as $k => $cab) {
            if ($rep[$k]) {
                $this->warn($k.':');
                $this->table($cab, $rep[$k]);
            }
        }
        $this->info($dryRun ? '[dry-run] No se guardó nada.' : 'Listo.');

        return self::SUCCESS;
    }
}
