<?php

namespace App\Services\Titulacion;

use App\Models\Titulacion\CartaAceptacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as FechaExcel;

/**
 * Importa las solicitudes de Carta de Presentación hechas con los formularios de Google (Excel «Formularios
 * de Titulación (Respuestas)») como cartas de aceptación APROBADAS, con su fecha original y sin enviar correos:
 * Secretaría ya las atendió fuera del sistema. Todas las respuestas (también las repetidas) quedan como historial.
 *  - Hoja «Carta de Presentación» (formulario nuevo, 2023→): se reconoce a la persona por correo institucional;
 *    si escribió otro correo, por nombre (único) o por celular.
 *  - Hoja «Carta Presentación» (formulario antiguo, 2022): por DNI; si no, por nombre.
 * Cada respuesta crea una fila por integrante del grupo que el sistema conoce (igual que el formulario del
 * sistema). Una respuesta ya importada no se vuelve a importar (clave_importacion).
 */
class ImportarCartasFormulario
{
    public const HOJA_NUEVA = 'Carta de Presentación';

    public const HOJA_ANTIGUA = 'Carta Presentación';

    public const METODOS = ['correo' => 'Correo institucional', 'dni' => 'DNI', 'nombre' => 'Nombre', 'celular' => 'Celular'];

    /**
     * Lee el Excel y reconoce a cada persona. No guarda nada.
     *
     * @return Collection<int, array> una fila por respuesta
     */
    public static function leer(string $ruta): Collection
    {
        $lector = IOFactory::createReaderForFile($ruta);
        $lector->setReadDataOnly(true);
        $nombres = collect($lector->listWorksheetNames($ruta));
        $hojaNueva = $nombres->first(fn ($n) => self::igual($n, self::HOJA_NUEVA));
        $hojaAntigua = $nombres->first(fn ($n) => self::igual($n, self::HOJA_ANTIGUA));
        $lector->setLoadSheetsOnly(array_values(array_filter([$hojaNueva, $hojaAntigua])));
        $libro = $lector->load($ruta);

        $personas = self::personas();
        $filas = collect();
        foreach ([[$hojaNueva, 'nueva'], [$hojaAntigua, 'antigua']] as [$hoja, $tipo]) {
            if (! $hoja) {
                continue;
            }
            $datos = array_slice(array_values(array_filter($libro->getSheetByName($hoja)->toArray(null, true, false, false),
                fn ($f) => trim(implode('', array_map('strval', $f))) !== '')), 1);
            foreach ($datos as $i => $f) {
                $filas->push(self::fila($tipo, $hoja, $i + 2, $f, $personas));
            }
        }

        // Ya importadas antes: no se repiten.
        $ya = CartaAceptacion::whereIn('clave_importacion', $filas->pluck('clave'))->pluck('clave_importacion')->flip();

        return $filas->map(fn ($f) => $f + ['ya_importada' => isset($ya[$f['clave']])])->sortBy('fecha')->values();
    }

    /** Importa las filas reconocidas que no estaban. Devuelve [respuestas importadas, filas creadas]. */
    public static function importar(Collection $filas): array
    {
        $respuestas = 0;
        $creadas = 0;
        DB::transaction(function () use ($filas, &$respuestas, &$creadas) {
            // De la más antigua a la más reciente: la última queda como la vigente (id más alto).
            foreach ($filas->filter(fn ($f) => $f['usuario'] && ! $f['ya_importada'])->sortBy('fecha') as $f) {
                $u = $f['usuario'];
                $s = $u->seguimientoTitulacion;
                if (! $s) {
                    continue;
                }
                $grupo = collect([$s])->concat(GrupoTitulacion::companeros($s->loadMissing(['actas', 'resoluciones']))
                    ->map(fn ($x) => $x->seguimientoTitulacion)->filter())->unique('id');
                $oficial = self::programaOficial($u);
                $lote = (string) Str::uuid();
                foreach ($grupo as $seg) {
                    $c = new CartaAceptacion([
                        'lote' => $lote, 'origen' => 'formulario', 'seguimiento_id' => $seg->id, 'solicitante_id' => $u->id,
                        'correo' => Str::limit($f['correo'] ?: (string) $u->email, 150, ''),
                        'grupo' => Str::limit($f['grupo'] ?: trim($u->name.' '.$u->apellidos), 1000, ''),
                        'celular' => Str::limit(preg_replace('/[^\d+ ]/', '', $f['celular']), 20, ''),
                        'programa_estudios' => Str::limit($oficial['estudios'] ?? $f['programa'], 100, ''),
                        'programa_formacion' => $oficial['formacion'] ?? $f['formacion'] ?? '',
                        'institucion' => Str::limit($f['institucion'], 300, ''),
                        'director' => Str::limit($f['director'], 200, ''),
                        'titulo' => Str::limit(trim($f['titulo'], " \"“”"), 1000, ''),
                        'comprobante' => Str::limit($f['comprobante'], 500, ''),
                        'declaracion' => $f['declaracion'],
                        'estado' => 'emitida',
                        'atendido_en' => $f['fecha'],
                        'datos_extra' => $f['extra'],
                        'clave_importacion' => $f['clave'],
                    ]);
                    $c->created_at = $f['fecha'];
                    $c->updated_at = now();
                    $c->save();
                    $creadas++;
                }
                $respuestas++;
            }
        });

        return [$respuestas, $creadas];
    }

    // ── Auxiliares ──────────────────────────────────────────────────────

    private static function fila(string $tipo, string $hoja, int $numFila, array $f, array $p): array
    {
        $txt = fn ($i) => trim(preg_replace('/\s+/', ' ', (string) ($f[$i] ?? '')));
        $fecha = is_numeric($f[0] ?? null) ? Carbon::instance(FechaExcel::excelToDateTimeObject($f[0])) : (self::fecha($txt(0)) ?? now());

        if ($tipo === 'nueva') {
            $datos = ['grupo' => $txt(1), 'dni' => preg_replace('/\D/', '', $txt(2)), 'celular' => $txt(3), 'correo' => mb_strtolower($txt(4)),
                'programa' => $txt(5), 'formacion' => self::formacion($txt(6)), 'institucion' => $txt(7), 'director' => $txt(8),
                'titulo' => $txt(9), 'comprobante' => $txt(10), 'declaracion' => $txt(11) !== '',
                'extra' => ['hoja' => $hoja, 'fila' => $numFila]];
            $nombre = trim(preg_split('/[,;\n]|\s-\s|\sy\s/iu', $datos['grupo'])[0]);
        } else {
            $datos = ['grupo' => $txt(1), 'dni' => preg_replace('/\D/', '', $txt(2)), 'celular' => $txt(3), 'correo' => '',
                'programa' => $txt(4), 'formacion' => null, 'institucion' => $txt(6), 'director' => $txt(9),
                'titulo' => $txt(10), 'comprobante' => $txt(11), 'declaracion' => $txt(12) !== '',
                'extra' => ['hoja' => $hoja, 'fila' => $numFila, 'ciclo' => $txt(5), 'codigo_modular' => $txt(7), 'direccion' => $txt(8)]];
            $nombre = $datos['grupo'];
        }

        [$usuario, $metodo, $motivo] = self::reconocer($datos, $nombre, $p);
        $datos['extra']['reconocido_por'] = $metodo;

        return $datos + [
            'tipo' => $tipo, 'hoja' => $hoja, 'fila' => $numFila, 'fecha' => $fecha, 'nombre' => $nombre,
            'usuario' => $usuario, 'metodo' => $metodo, 'motivo' => $motivo,
            'clave' => sha1($hoja.'|'.($f[0] ?? '').'|'.$datos['correo'].'|'.$datos['dni'].'|'.$datos['grupo']),
        ];
    }

    /** @return array{0:?User, 1:?string, 2:?string} [persona, cómo se reconoció, por qué no] */
    private static function reconocer(array $d, string $nombre, array $p): array
    {
        if ($d['correo'] !== '' && isset($p['correo'][$d['correo']])) {
            return [$p['correo'][$d['correo']], 'correo', null];
        }
        if ($d['dni'] !== '' && isset($p['dni'][$d['dni']])) {
            return [$p['dni'][$d['dni']], 'dni', null];
        }
        $tokens = self::tokens($nombre);
        $porNombre = $p['lista']->filter(fn ($u) => $p['tokens'][$u->id]->count() >= 2
            && $p['tokens'][$u->id]->intersect($tokens)->count() >= min(3, $p['tokens'][$u->id]->count()));
        if ($porNombre->count() === 1) {
            return [$porNombre->first(), 'nombre', null];
        }
        $cel = substr(preg_replace('/\D/', '', $d['celular']), -9);
        $porCelular = strlen($cel) === 9 ? $p['lista']->filter(fn ($u) => in_array($cel, $p['celulares'][$u->id], true)) : collect();
        if ($porCelular->count() === 1) {
            return [$porCelular->first(), 'celular', null];
        }

        return [null, null, $porNombre->count() > 1 ? 'Varias personas con ese nombre' : 'No se encontró a la persona (correo, DNI, nombre ni celular)'];
    }

    /** Personas con proceso de titulación, con sus índices de búsqueda. */
    private static function personas(): array
    {
        $lista = User::whereHas('seguimientoTitulacion')
            ->with(['seguimientoTitulacion', 'programa', 'alumno:id,user_id,numero', 'alumnoB:id,user_id,numero'])
            ->get(['id', 'name', 'apellidos', 'dni', 'email', 'telefono', 'programa_id']);

        return [
            'lista' => $lista,
            'correo' => $lista->filter(fn ($u) => filled($u->email))->keyBy(fn ($u) => mb_strtolower(trim($u->email)))->all(),
            'dni' => $lista->filter(fn ($u) => filled($u->dni))->keyBy(fn ($u) => preg_replace('/\D/', '', $u->dni))->all(),
            'tokens' => $lista->mapWithKeys(fn ($u) => [$u->id => self::tokens($u->name.' '.$u->apellidos)])->all(),
            'celulares' => $lista->mapWithKeys(fn ($u) => [$u->id => collect([$u->telefono, $u->alumno?->numero, $u->alumnoB?->numero])
                ->map(fn ($c) => substr(preg_replace('/\D/', '', (string) $c), -9))->filter(fn ($c) => strlen($c) === 9)->values()->all()])->all(),
        ];
    }

    private static function tokens(string $texto): Collection
    {
        return collect(preg_split('/[^a-z]+/', Str::lower(Str::ascii($texto))))->filter(fn ($t) => strlen($t) > 2)->values();
    }

    private static function formacion(string $texto): ?string
    {
        return match (true) {
            str_contains($texto, '(PPD)') => 'PPD',
            str_contains($texto, '(SEE)') => 'SEE',
            str_contains($texto, '(FID)') => 'FID',
            default => null,
        };
    }

    /** Programa de la ficha (manda sobre lo escrito en el formulario). */
    private static function programaOficial(User $u): array
    {
        $p = $u->programa;
        if (! $p) {
            return [];
        }

        return [
            'estudios' => match (true) {
                $p->esInicial() => 'Educación Inicial',
                (bool) $p->eib => 'Educación Primaria Intercultural Bilingüe',
                $p->esPrimaria() => 'Educación Primaria',
                default => null,
            },
            'formacion' => $p->esPpd() ? 'PPD' : 'FID',
        ];
    }

    private static function fecha(string $t): ?Carbon
    {
        try {
            return $t !== '' ? Carbon::parse(str_replace('/', '-', $t)) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function igual(string $a, string $b): bool
    {
        return Str::lower(Str::ascii(trim($a))) === Str::lower(Str::ascii(trim($b)));
    }
}
