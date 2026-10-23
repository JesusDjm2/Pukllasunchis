<?php

namespace App\Http\Controllers\Titulacion;

use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\Importacion;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Tesis;
use App\Models\User;
use App\Services\Titulacion\Expediente;
use App\Services\Titulacion\NombresImportados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * "Sincronizar planilla": el gestor sube la hoja de expedientes de la Unidad de
 * Investigación (EXP. PPD / EXP. FID) y el sistema completa el proceso de
 * titulación de cada persona — sin terminal, en producción.
 *
 * 1) Vista previa: se ejecuta TODO (importación a staging, cuentas nuevas,
 *    seguimiento, PTI/Tesis/RD) dentro de una transacción que se deshace al
 *    final; lo que se muestra es exactamente lo que pasará al confirmar.
 * 2) Confirmar: se repite de verdad, creando solo las cuentas que el gestor
 *    dejó marcadas.
 *
 * Regla: la planilla SOLO completa lo vacío. Un dato ya cargado con otro valor
 * no se toca y se lista como diferencia para revisarlo a mano.
 */
class SincronizacionController extends TitulacionController
{
    public const ORIGENES = [
        'exp-ppd-marilyn' => ['etiqueta' => 'Expedientes PPD (hoja «EXP. PPD»)', 'tipo' => 'ppd'],
        'exp-fid-marilyn' => ['etiqueta' => 'Expedientes FID (hoja «EXP (FID)»)', 'tipo' => 'fid'],
        // Sin DNI: se identifica por nombre (solo si hay una única persona que coincide); no crea cuentas.
        'expo-sust-marilyn' => ['etiqueta' => 'Actas de exposición y sustentación, FID y PPD (hoja «EXPO-SUST»)', 'tipo' => 'actas'],
        // Archivo general de la Unidad de Investigación: títulos, asesor, enlaces, dictaminantes, grados,
        // celular y situación. Sin DNI (por nombre); los datos del grupo vienen en la fila del primero.
        'ppd-carlos' => ['etiqueta' => 'Archivo general de investigaciones PPD (hoja «PPD ARCHIVO GENERAL»)', 'tipo' => 'archivo', 'modalidad' => 'ppd'],
        'fid-carlos' => ['etiqueta' => 'Archivo general de investigaciones FID (hoja «FID ARCHIVO GENERAL»)', 'tipo' => 'archivo', 'modalidad' => 'fid'],
    ];

    /** Archivo general: campos que la hoja escribe solo en la fila del primer integrante del grupo. */
    private const CAMPOS_DE_GRUPO = [
        'titulo_tesis', 'titulo_ti', 'asesor', 'rd_pti', 'link_iti', 'fecha_exposicion', 'jurado_exposicion', 'url_ti_repositorio',
        'dictaminante_1', 'link_dictamen_1', 'dictaminante_2', 'link_dictamen_2', 'fecha_sustentacion', 'url_repositorio',
        'jurado_sustentacion', 'enlace_tesis', 'situacion_tesis',
    ];

    /** Archivo general: datos del primer integrante que hereda cada fila de un grupo (por id de fila). */
    private array $herencia = [];

    private const CARPETA = 'titulacion/sincronizaciones';

    /** Hoja de actas: año de la última fecha completa antes de cada fila (para fechas sin año). */
    private array $anioPorFila = [];

    /** Hoja de actas: filas que se vincularon aquí por nombre (la modalidad de la hoja no calzaba). */
    private array $vinculadasPorNombre = [];

    /**
     * Fecha de un acta escrita a mano: "22 de septiembe del 2023", "5 de abril de 2024", "28 de agosto".
     * El mes se reconoce por sus 3 primeras letras (tolera erratas); sin año usa $anioSiFalta.
     *
     * @return array{0: ?string, 1: bool} [Y-m-d, si el año se dedujo]
     */
    private static function fechaActa(string $texto, ?int $anioSiFalta): array
    {
        $meses = ['ene' => 1, 'feb' => 2, 'mar' => 3, 'abr' => 4, 'may' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8, 'sep' => 9, 'set' => 9, 'oct' => 10, 'nov' => 11, 'dic' => 12];
        $t = strtolower(Str::ascii(trim($texto)));
        if (! preg_match('/(\d{1,2})\s+(?:de\s+)?([a-z]{3,})\.?(?:\s+(?:del?\s+)?(\d{4}))?/', $t, $m) || ! isset($meses[substr($m[2], 0, 3)])) {
            return [null, false];
        }
        $anio = ! empty($m[3]) ? (int) $m[3] : $anioSiFalta;
        if (! $anio || ! checkdate($meses[substr($m[2], 0, 3)], (int) $m[1], $anio)) {
            return [null, false];
        }

        return [sprintf('%04d-%02d-%02d', $anio, $meses[substr($m[2], 0, 3)], $m[1]), empty($m[3])];
    }

    public function form()
    {
        return view('titulacion.sincronizar', ['paso' => 'subir', 'origenes' => self::ORIGENES]);
    }

    public function previsualizar(Request $request)
    {
        $data = $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
            'origen' => 'required|in:' . implode(',', array_keys(self::ORIGENES)),
        ], [
            'archivo.mimes' => 'Sube la hoja exportada como CSV (Archivo › Descargar › Valores separados por comas).',
        ]);

        $token = (string) Str::uuid();
        $request->file('archivo')->storeAs(self::CARPETA, $token . '.csv', 'local');
        session(["titulacion_sync.{$token}" => [
            'origen' => $data['origen'],
            'nombre' => $request->file('archivo')->getClientOriginalName(),
        ]]);

        $reporte = $this->sincronizar($token, $data['origen'], null, true);

        return view('titulacion.sincronizar', [
            'paso' => 'previa', 'origenes' => self::ORIGENES, 'reporte' => $reporte, 'token' => $token,
            'origen' => $data['origen'], 'nombreArchivo' => $request->file('archivo')->getClientOriginalName(),
        ]);
    }

    public function aplicar(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|uuid',
            'crear' => 'array',
            'crear.*' => 'string|max:20',
            'asesores_nuevos' => 'array',
            'asesores_nuevos.*' => 'string|max:150',
        ]);
        $meta = session("titulacion_sync.{$data['token']}");
        abort_unless($meta && is_file($this->ruta($data['token'])), 410, 'La vista previa expiró. Vuelve a subir la planilla.');

        $reporte = $this->sincronizar($data['token'], $meta['origen'], $data['crear'] ?? [], false, $data['asesores_nuevos'] ?? []);
        session()->forget("titulacion_sync.{$data['token']}");

        return view('titulacion.sincronizar', [
            'paso' => 'hecho', 'origenes' => self::ORIGENES, 'reporte' => $reporte,
            'origen' => $meta['origen'], 'nombreArchivo' => $meta['nombre'],
        ]);
    }

    private function ruta(string $token): string
    {
        return storage_path('app/' . self::CARPETA . '/' . $token . '.csv');
    }

    /**
     * Corre la sincronización completa. $simular: todo en una transacción que se
     * deshace (vista previa). $crear: DNIs de las cuentas nuevas autorizadas
     * (null = todas las elegibles, para la vista previa).
     */
    private function sincronizar(string $token, string $origen, ?array $crear, bool $simular, array $asesoresNuevos = []): array
    {
        @set_time_limit(300);
        $tipo = self::ORIGENES[$origen]['tipo'];
        $items = [];
        $creados = [];

        DB::beginTransaction();
        try {
            // Asesores/jurados que el gestor marcó en la vista previa: se agregan al catálogo ANTES de
            // sincronizar, así esta misma pasada ya los vincula.
            foreach (array_unique(array_filter(array_map('trim', $asesoresNuevos))) as $nombre) {
                if (\App\Models\Titulacion\Asesor::all()->contains(fn ($a) => NombresImportados::mismaPersona($nombre, $a->apellidos . ' ' . $a->nombres))) {
                    continue;
                }
                $palabras = preg_split('/\s+/u', $nombre);
                $nApellidos = count($palabras) >= 3 ? 2 : 1;
                \App\Models\Titulacion\Asesor::create([
                    'nombres' => implode(' ', array_slice($palabras, 0, -$nApellidos)) ?: $nombre,
                    'apellidos' => implode(' ', array_slice($palabras, -$nApellidos)),
                    'activo' => true,
                ]);
                $creados[] = $nombre;
            }

            Artisan::call('titulacion:importar', ['archivo' => $this->ruta($token), 'origen' => $origen]);
            $lote = Importacion::where('archivo_nombre', $token . '.csv')->latest('id')->value('lote_importacion');
            // Filas sin nombre (relleno "BLANCOO" al final de la hoja) no son personas.
            $filas = Importacion::where('lote_importacion', $lote)->orderBy('id')->get()
                ->filter(fn (Importacion $f) => filled($f->nombre_normalizado));

            // Una persona = un DNI; sin DNI, la cuenta con la que se vinculó o, si no, su nombre.
            $personas = $filas->groupBy(fn (Importacion $f) => preg_replace('/\D/', '', (string) $f->dni)
                ?: ($f->user_id ? 'u:' . $f->user_id : 'n:' . $f->nombre_normalizado));
            $correos = $personas->map(fn ($g) => mb_strtolower(trim((string) ($g->first()->datos_normalizados['correo'] ?? ''))))->filter()->countBy();

            if ($tipo === 'actas') {
                // El importador busca por nombre solo en la modalidad que dice la fila, y la hoja a veces
                // se equivoca ("TESIS FID" para un grupo PPD). Si el nombre coincide con UNA sola cuenta de
                // todo el sistema y es de un egresado, se vincula (criterio más estricto que el del importador).
                $cuentas = User::whereNotNull('apellidos')->get(['id', 'name', 'apellidos', 'condicion']);
                foreach ($filas->where('estado_match', 'sin_match') as $f) {
                    $coinciden = $cuentas->filter(fn ($u) => NombresImportados::mismaPersona((string) $f->nombre_estudiante, $u->apellidos . ' ' . $u->name));
                    if ($coinciden->count() === 1 && $coinciden->first()->condicion === 'Egresado') {
                        $f->update(['user_id' => $coinciden->first()->id, 'estado_match' => 'vinculado']);
                        $this->vinculadasPorNombre[$f->id] = true;
                    }
                }
                $personas = $filas->groupBy(fn (Importacion $f) => $f->user_id ? 'u:' . $f->user_id : 'n:' . $f->nombre_normalizado);

                // La hoja va en orden cronológico: una fecha sin año ("28 de agosto") toma el de la fila anterior.
                $ultimoAnio = null;
                foreach (Importacion::where('lote_importacion', $lote)->orderBy('fila_num')->get() as $f) {
                    $this->anioPorFila[$f->fila_num] = $ultimoAnio;
                    [$fechaFila] = self::fechaActa((string) ($f->datos_normalizados['fecha_acta'] ?? ''), null);
                    $ultimoAnio = $fechaFila ? (int) substr($fechaFila, 0, 4) : $ultimoAnio;
                }
            }

            if ($tipo === 'archivo') {
                $modalidad = self::ORIGENES[$origen]['modalidad'];
                // El importador solo busca entre egresados; aquí también hay alumnos activos (PTI apto).
                // Se vincula si el nombre coincide con UNA sola cuenta de todo el sistema y esa cuenta
                // tiene ficha de estudiante de esta modalidad.
                $cuentas = User::with(['alumno:id,user_id', 'alumnoB:id,user_id'])->whereNotNull('apellidos')->get(['id', 'name', 'apellidos', 'condicion']);
                foreach ($filas->where('estado_match', '!=', 'vinculado') as $f) {
                    $coinciden = $cuentas->filter(fn ($u) => NombresImportados::mismaPersona((string) $f->nombre_estudiante, $u->apellidos . ' ' . $u->name));
                    $u = $coinciden->count() === 1 ? $coinciden->first() : null;
                    if ($u && ($modalidad === 'ppd' ? $u->alumnoB : $u->alumno)) {
                        $f->update(['user_id' => $u->id, 'estado_match' => 'vinculado']);
                    }
                }
                // Integrantes de un grupo: heredan los datos del primero si la RD del PTI de ese primero
                // los nombra (la RD lista a todo el grupo); así no se mezcla a nadie por error.
                $primero = null;
                foreach ($filas->sortBy('fila_num') as $f) {
                    $d = $f->datos_normalizados ?? [];
                    $tieneDatos = collect(['titulo_tesis', 'titulo_ti', 'asesor', 'rd_pti'])->contains(fn ($c) => filled($d[$c] ?? null));
                    if ($tieneDatos) {
                        $primero = $d;
                        continue;
                    }
                    if ($primero && $this->nombradoEnRd((string) $f->nombre_estudiante, (string) ($primero['rd_pti'] ?? ''))) {
                        $this->herencia[$f->id] = array_intersect_key($primero, array_flip(self::CAMPOS_DE_GRUPO));
                    }
                }
                $personas = $filas->groupBy(fn (Importacion $f) => $f->user_id ? 'u:' . $f->user_id : 'n:' . $f->nombre_normalizado);
            }

            foreach ($personas as $grupo) {
                $items[] = match ($tipo) {
                    'actas' => $this->procesarActas($grupo),
                    'archivo' => $this->procesarArchivo($grupo, self::ORIGENES[$origen]['modalidad']),
                    default => $this->procesarPersona($grupo, $tipo, $lote, $correos, $crear, $simular),
                };
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        $simular ? DB::rollBack() : DB::commit();

        $items = collect($items);

        return [
            'items' => $items,
            'conteo' => $items->countBy('accion')->all(),
            'con_diferencias' => $items->filter(fn ($i) => ! empty($i['diferencias']))->count(),
            'faltantes' => collect($this->faltantes)->sortDesc()->all(),
            'asesores_creados' => $creados,
            'simulado' => $simular,
        ];
    }

    private function procesarPersona($grupo, string $tipo, ?string $lote, $correos, ?array $crear, bool $simular): array
    {
        /** @var Importacion $fila */
        $fila = $grupo->first();
        $d = $fila->datos_normalizados ?? [];
        $dni = preg_replace('/\D/', '', (string) $fila->dni);
        $item = [
            'nombre' => $fila->nombre_estudiante ?: '(sin nombre)',
            'dni' => $fila->dni,
            'periodo' => $d['periodo'] ?? null,
            'programa' => $d['programa'] ?? null,
            'expediente' => Expediente::normalizar($d['num_expediente'] ?? null),
            'user_id' => $fila->user_id,
            'cambios' => [],
            'diferencias' => [],
            'motivos' => [],
            'propuesta' => null,
        ];

        // Retirados: la hoja los marca en el expediente o en la fecha del PTI.
        if (self::marcadoRetirado($d)) {
            return array_merge($item, ['accion' => 'omitido', 'motivos' => ['Retirado(a) según la planilla']]);
        }

        if ($fila->user_id) {
            $user = User::with(['alumno', 'alumnoB'])->find($fila->user_id);
            $ficha = $tipo === 'ppd' ? $user?->alumnoB : $user?->alumno;
            if (! $user || ! $ficha) {
                return array_merge($item, ['accion' => 'revisar', 'motivos' => [
                    'Tiene cuenta (' . ($user->email ?? '—') . ') pero no ficha de estudiante ' . strtoupper($tipo) . ': usa «Completar ficha» en Titulación.',
                ]]);
            }
            $item['nombre'] = trim($user->apellidos . ', ' . $user->name, ', ');
            $item['condicion'] = $user->condicion;

            $antes = $this->foto($user->id);
            $seguimiento = SeguimientoTitulacion::firstOrCreate(
                ['user_id' => $user->id],
                ['estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti')]
            );
            $item['diferencias'] = $this->aplicarDatosTitulacionDesdeCsv($seguimiento, (string) $fila->dni, true, $lote);
            $item['cambios'] = array_merge(
                $seguimiento->wasRecentlyCreated ? ['Se inicia su proceso de titulación'] : [],
                $this->cambios($antes, $this->foto($user->id))
            );

            return $item + ['accion' => $item['cambios'] ? 'completa' : 'al_dia'];
        }

        // ── Sin cuenta: ¿se puede crear con seguridad? ──
        $correo = mb_strtolower(trim((string) ($d['correo'] ?? '')));
        $nombreSeparado = NombresImportados::separarApellidosNombres($fila->nombre_estudiante);
        $nombrePorRegla = false;
        if (! $nombreSeparado && count($palabras = preg_split('/\s+/', trim((string) $fila->nombre_estudiante))) >= 3) {
            // "Bellido Sullcarana Elisa": dos apellidos y el resto nombres (verificar en la vista previa).
            $nombreSeparado = ['apellidos' => implode(' ', array_slice($palabras, 0, 2)), 'nombres' => implode(' ', array_slice($palabras, 2))];
            $nombrePorRegla = true;
        }
        $nombreSeparado = $nombreSeparado ? array_map(fn ($v) => trim(preg_replace('/\s*\([^)]*\)\s*/', ' ', $v)), $nombreSeparado) : null;
        $programaId = $this->resolverProgramaId((string) ($d['programa'] ?? ''), $tipo);
        $anio = preg_match('/\b(20\d\d)\b/', (string) ($d['periodo'] ?? ''), $m) ? $m[1] : null;

        $motivos = array_values(array_filter([
            strlen($dni) !== 8 ? 'DNI dudoso («' . $fila->dni . '»): debe tener 8 dígitos' : null,
            $correo === '' ? 'Sin correo en la planilla' : null,
            $correo !== '' && ! filter_var($correo, FILTER_VALIDATE_EMAIL) ? "Correo no válido ({$correo})" : null,
            $correo !== '' && ($correos[$correo] ?? 0) > 1 ? "Correo repetido en la planilla con otra persona ({$correo})" : null,
            $correo !== '' && User::whereRaw('LOWER(email) = ?', [$correo])->exists() ? "El correo {$correo} ya es de otra cuenta" : null,
            strlen($dni) === 8 && User::where('dni', $dni)->exists() ? 'Ya existe una cuenta con ese DNI' : null,
            ! $nombreSeparado ? 'No se pudo separar apellidos y nombres' : null,
            ! $programaId ? 'Programa no reconocido («' . ($d['programa'] ?? '') . '»)' : null,
            $anio && (int) $anio >= (int) now()->year ? "Cohorte {$anio} (en curso): debería tener ya su cuenta de alumno, revisar a mano" : null,
        ]));
        if ($motivos) {
            return array_merge($item, ['accion' => 'revisar', 'motivos' => $motivos]);
        }

        $item['propuesta'] = [
            'apellidos' => $nombreSeparado['apellidos'], 'nombres' => $nombreSeparado['nombres'], 'nombre_por_regla' => $nombrePorRegla,
            'correo' => $correo, 'programa' => \App\Models\Programa::find($programaId)?->nombre, 'anio' => $anio,
        ];
        if (! $simular && ! in_array($dni, $crear ?? [], true)) {
            return $item + ['accion' => 'no_marcado'];
        }

        // bcrypt costo 12 (el del sistema) tarda ~250 ms por cuenta: con ~100 cuentas la petición
        // supera el límite de tiempo del hosting. Costo 10 (el de PHP por defecto) sigue siendo
        // seguro; en la vista previa la cuenta se descarta, así que basta el mínimo.
        $user = User::create([
            'name' => $nombreSeparado['nombres'], 'apellidos' => $nombreSeparado['apellidos'], 'dni' => $dni,
            'email' => $correo, 'condicion' => 'Egresado', 'password' => Hash::make($dni, ['rounds' => $simular ? 4 : 10]),
        ]);
        $this->crearFichaEgresado($user, [
            'nombres' => $nombreSeparado['nombres'], 'apellidos' => $nombreSeparado['apellidos'], 'dni' => $dni,
            'email' => $correo, 'programa_id' => $programaId, 'anio_ingreso' => $anio, 'tipo' => $tipo,
        ]);
        Importacion::whereIn('dni', [$dni, $fila->dni])->whereIn('estado_match', ['sin_match', 'vinculado'])
            ->update(['user_id' => $user->id, 'estado_match' => 'vinculado']);
        $seguimiento = SeguimientoTitulacion::where('user_id', $user->id)->first();
        $this->aplicarDatosTitulacionDesdeCsv($seguimiento, (string) $fila->dni, true, $lote);
        $item['user_id'] = $user->id;
        $item['cambios'] = array_merge([($simular ? 'Se crea su' : 'Se creó su') . ' cuenta de egresado (contraseña inicial: su DNI)'], $this->cambios($this->foto(0), $this->foto($user->id)));

        return $item + ['accion' => 'crear'];
    }

    /**
     * Hoja EXPO-SUST: cada fila es un acta (exposición del TI o sustentación de la tesis) de
     * una persona identificada por su nombre. Sin cuenta vinculada no se puede crear nada
     * (la hoja no trae DNI ni correo): queda para revisión.
     */
    private function procesarActas($grupo): array
    {
        /** @var Importacion $fila */
        $fila = $grupo->first();
        $d = $fila->datos_normalizados ?? [];
        $item = [
            'nombre' => $fila->nombre_estudiante, 'dni' => null, 'periodo' => $d['ingreso'] ?? null, 'programa' => $d['carrera'] ?? null,
            'expediente' => Expediente::deFila($d, $fila->nombre_estudiante), 'user_id' => $fila->user_id,
            'cambios' => [], 'diferencias' => [], 'motivos' => [], 'propuesta' => null,
        ];

        if (! $fila->user_id) {
            // El importador solo busca entre egresados; si hay UNA cuenta de otro tipo con ese nombre
            // (docente, alumno sin marcar como egresado) se sugiere, sin vincularla sola.
            $parecidas = $fila->estado_match === 'ambiguo' ? collect() : User::whereNotNull('apellidos')->get(['id', 'name', 'apellidos', 'condicion'])
                ->filter(fn ($u) => NombresImportados::mismaPersona((string) $fila->nombre_estudiante, $u->apellidos . ' ' . $u->name));
            $sugerida = $parecidas->count() === 1 ? $parecidas->first() : null;

            return array_merge($item, ['accion' => 'revisar', 'motivos' => [
                match (true) {
                    $fila->estado_match === 'ambiguo' => 'Varias personas del sistema coinciden con este nombre: vincúlalo a mano.',
                    (bool) $sugerida => "Coincide con la cuenta de {$sugerida->apellidos}, {$sugerida->name} (" . ($sugerida->getRoleNames()->implode(', ') ?: 'sin rol')
                        . ($sugerida->condicion ? ", {$sugerida->condicion}" : '') . '), que no figura como egresado(a): usa «Completar ficha» o márcalo como egresado y vuelve a sincronizar.',
                    default => 'No tiene cuenta en el sistema (esta hoja no trae DNI ni correo para crearla): primero sincroniza la hoja de expedientes que sí los trae.',
                },
            ]]);
        }

        $user = User::find($fila->user_id);
        $item['nombre'] = trim($user->apellidos . ', ' . $user->name, ', ');
        $item['dni'] = $user->dni;
        $item['condicion'] = $user->condicion;

        $antes = $this->foto($user->id);
        $seguimiento = SeguimientoTitulacion::firstOrCreate(
            ['user_id' => $user->id],
            ['estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti')]
        );
        $diferencias = [];
        foreach ($grupo as $f) {
            if (isset($this->vinculadasPorNombre[$f->id])) {
                $diferencias[] = 'Una fila dice «' . trim(preg_replace('/\s+/', ' ', (string) ($f->datos_normalizados['tipo_k'] ?? ''))) . '» pero su nombre solo coincide con esta cuenta: se vinculó. Verifícalo.';
            }
            $this->aplicarFilaActa($seguimiento, $f, $diferencias);
        }
        $item['diferencias'] = $diferencias;
        $item['cambios'] = array_merge(
            $seguimiento->wasRecentlyCreated ? ['Se inicia su proceso de titulación'] : [],
            $this->cambios($antes, $this->foto($user->id))
        );

        return $item + ['accion' => $item['cambios'] ? 'completa' : 'al_dia'];
    }

    /** Una fila de EXPO-SUST -> acta + jurados + fecha en la tesis + expediente. Solo llena lo vacío. */
    private function aplicarFilaActa(SeguimientoTitulacion $seguimiento, Importacion $f, array &$diferencias): void
    {
        $d = $f->datos_normalizados ?? [];
        $k = Str::upper(Str::ascii(trim((string) ($d['tipo_k'] ?? ''))));
        $numero = preg_replace('/\D/', '', (string) ($d['num_pag_acta'] ?? '')) ?: null;
        if (str_contains($k, 'ANULAD')) {
            $diferencias[] = 'La hoja marca un acta como ANULADA' . ($numero ? " (N° {$numero})" : '') . ': no se registró.';

            return;
        }
        $tipo = str_contains($k, 'TESIS') ? 'tesis' : 'expo';
        $etiqueta = $tipo === 'expo' ? 'Acta de exposición' : 'Acta de sustentación';

        // En los grupos, cada celda trae un valor por integrante (uno por línea), en el mismo orden.
        $integrantes = array_values(array_filter(array_map('trim', explode(';', (string) ($d['integrantes_grupo'] ?? '')))));
        $pos = 0;
        foreach ($integrantes as $i => $nombre) {
            if (NombresImportados::mismaPersona($nombre, (string) $f->nombre_estudiante)) {
                $pos = $i;
                break;
            }
        }
        $dePosicion = function ($celda) use ($pos) {
            $lineas = array_values(array_filter(array_map('trim', preg_split('/\R+/u', (string) $celda)), 'strlen'));

            return count($lineas) > 1 ? ($lineas[$pos] ?? null) : ($lineas[0] ?? null);
        };
        $promedio = str_replace(',', '.', (string) $dePosicion($d['promedio'] ?? ''));
        $promedio = is_numeric($promedio) && (float) $promedio > 0 && (float) $promedio <= 20 ? round((float) $promedio, 2) : null;
        $fecha = $d['fecha_acta_parseada'] ?? null;
        if (! $fecha && filled($d['fecha_acta'] ?? null)) {
            [$fecha, $deducido] = self::fechaActa($d['fecha_acta'], $this->anioPorFila[$f->fila_num] ?? null);
            if (! $fecha) {
                $diferencias[] = "{$etiqueta}: no se entiende la fecha «{$d['fecha_acta']}»: no se registró.";
            } elseif ($deducido) {
                $diferencias[] = "{$etiqueta}: la fecha «{$d['fecha_acta']}» no tiene año; se tomó " . substr($fecha, 0, 4) . ' por el orden de la hoja. Verifícalo.';
            }
        }
        $titulo = self::limpiarTitulo($d['titulo_trabajo'] ?? null) ?: null;
        $titulo = $titulo && mb_strlen($titulo) > 12 ? $titulo : null; // "Los tic ...." no es un título

        $acta = \App\Models\Titulacion\Acta::firstOrNew(['seguimiento_id' => $seguimiento->id, 'tipo' => $tipo, 'numero_acta' => $numero]);
        [$asesorActaId, $asesorActaExterno] = $this->resolverPersona(self::primeraPersona($d['asesor'] ?? null));
        if (blank($acta->asesor_id) && blank($acta->asesor_externo_id) && $asesorActaExterno) {
            $acta->asesor_externo_id = $asesorActaExterno;
        }
        foreach (['fecha' => $fecha, 'promedio' => $promedio, 'titulo_trabajo' => $titulo,
            'asesor_id' => blank($acta->asesor_externo_id) ? $asesorActaId : null] as $campo => $valor) {
            if ($valor === null) {
                continue;
            }
            $actual = $acta->{$campo};
            if (blank($actual)) {
                $acta->{$campo} = $valor;
            } elseif ($campo === 'fecha' && $actual->format('Y-m-d') !== $valor) {
                $diferencias[] = "{$etiqueta} N° {$numero} · fecha: sistema «{$actual->format('d/m/Y')}» · planilla «" . \Illuminate\Support\Carbon::parse($valor)->format('d/m/Y') . '»';
            } elseif ($campo === 'promedio' && (float) $actual !== (float) $valor) {
                $diferencias[] = "{$etiqueta} N° {$numero} · promedio: sistema «{$actual}» · planilla «{$valor}»";
            }
        }
        if (! $acta->exists || $acta->isDirty()) {
            $acta->save();
        }
        foreach (['jurado_1' => 'jurado1', 'jurado_2' => 'jurado2', 'jurado_3' => 'jurado3', 'jurado_4' => 'jurado4'] as $clave => $rol) {
            $nombreJurado = self::primeraPersona($d[$clave] ?? null);
            [$docenteId, $externoId] = $this->resolverPersona($nombreJurado);
            if ($docenteId || $externoId) {
                \App\Models\Titulacion\Jurado::firstOrCreate(['acta_id' => $acta->id, 'docente_id' => $docenteId, 'asesor_externo_id' => $externoId], ['rol' => $rol]);
            }
            // La secuencia del proceso muestra el jurado de la exposición del TI desde «Participantes».
            if ($tipo === 'expo') {
                $this->agregarParticipante($seguimiento, 'ti', 'jurado', $nombreJurado);
            }
        }
        if ($tipo === 'expo') {
            $this->agregarParticipante($seguimiento, 'ti', 'asesor', self::primeraPersona($d['asesor'] ?? null));
        }

        // La fecha del acta es la de exposición (TI) o la de sustentación (tesis).
        $tesis = Tesis::firstOrNew(['seguimiento_id' => $seguimiento->id]);
        $campoFecha = $tipo === 'expo' ? 'fecha_exposicion' : 'fecha_sustentacion';
        if ($fecha && blank($tesis->{$campoFecha})) {
            $tesis->{$campoFecha} = $fecha;
        }
        if ($tipo === 'tesis' && $titulo && blank($tesis->titulo)) {
            $tesis->titulo = $titulo;
        }
        if ($tesis->isDirty()) {
            $tesis->save();
        }

        $expediente = Expediente::deFila($d, $f->nombre_estudiante);
        if ($expediente && blank($seguimiento->numero_expediente)) {
            if (! SeguimientoTitulacion::where('numero_expediente', $expediente)->exists()) {
                $seguimiento->update(['numero_expediente' => $expediente]);
            } else {
                $diferencias[] = "Expediente {$expediente}: ya lo tiene otra persona — no se asignó";
            }
        }
    }

    /** Archivo general: asesores/dictaminantes que no están en el sistema -> cuántas personas los tienen. */
    private array $faltantes = [];

    /** Catálogo de asesores externos (cargado una vez). */
    private ?\Illuminate\Support\Collection $catalogoAsesores = null;

    /**
     * Nombre libre de un asesor o dictaminante -> [docente_id, asesor_externo_id]. Primero un docente con
     * cuenta (ResolverDocente); si no, UN solo asesor del catálogo con ese nombre. Si no hay ninguno, se
     * anota en $faltantes para que el gestor lo agregue al catálogo y vuelva a sincronizar.
     */
    private function resolverPersona(?string $nombre): array
    {
        if (blank($nombre)) {
            return [null, null];
        }
        if ($docente = \App\Services\Titulacion\ResolverDocente::resolver($nombre)) {
            return [$docente, null];
        }
        $this->catalogoAsesores ??= \App\Models\Titulacion\Asesor::all();
        $coinciden = $this->catalogoAsesores->filter(fn ($a) => NombresImportados::mismaPersona($nombre, $a->apellidos . ' ' . $a->nombres));
        // Erratas de la hoja ("Clotilde Amable Linares" por "Clotilde Natividad Amable Pinares"): si comparte
        // 2+ palabras con UNA sola persona del catálogo, es ella.
        if ($coinciden->isEmpty()) {
            $coinciden = $this->catalogoAsesores->filter(fn ($a) => self::palabrasEnComun($nombre, $a->apellidos . ' ' . $a->nombres) >= 2);
        }
        if ($coinciden->count() === 1) {
            return [null, $coinciden->first()->id];
        }
        // Agrupa variantes del mismo nombre ("Clotilde Amable Pinares" / "Clotilde Natividad Amable Pinares").
        // Variantes del mismo nombre (incluidas erratas) se cuentan juntas; queda la más larga como nombre.
        $clave = collect(array_keys($this->faltantes))->first(fn ($k) => NombresImportados::mismaPersona($k, $nombre) || self::palabrasEnComun($k, $nombre) >= 2);
        if ($clave === null) {
            $this->faltantes[$nombre] = 1;
        } elseif (mb_strlen($nombre) > mb_strlen($clave)) {
            $this->faltantes[$nombre] = $this->faltantes[$clave] + 1;
            unset($this->faltantes[$clave]);
        } else {
            $this->faltantes[$clave]++;
        }

        return [null, null];
    }

    /** Palabras de 3+ letras (sin tildes ni conectores) que comparten dos nombres. */
    private static function palabrasEnComun(string $a, string $b): int
    {
        $palabras = fn ($t) => array_unique(array_filter(
            explode(' ', preg_replace('/[^A-Z]+/', ' ', strtoupper(Str::ascii($t)))),
            fn ($p) => strlen($p) >= 3 && ! in_array($p, ['DEL', 'LOS', 'LAS', 'LIC', 'MAG', 'DRA', 'PROF', 'BACH'], true)
        ));

        return count(array_intersect($palabras($a), $palabras($b)));
    }

    /**
     * Quita comillas, tildes sueltas y puntuación de los extremos de un título. Con expresión /u y no
     * con trim(): trim() recorta por bytes y, con caracteres de varios bytes en la lista (“ ” ´),
     * partía letras como «¿» y dejaba texto UTF-8 inválido que MySQL rechaza.
     */
    private static function limpiarTitulo(?string $texto): string
    {
        $t = preg_replace('/^[\s"“”´\'\x{0301},.]+|[\s"“”´\'\x{0301},.]+$/u', '', (string) $texto);

        return $t === null ? '' : preg_replace('/\s+/u', ' ', $t);
    }

    /** true si al menos 2 palabras del nombre (4+ letras) aparecen en el texto de la RD del grupo. */
    private function nombradoEnRd(string $nombre, string $rd): bool
    {
        if (! preg_match('/\bR\.?\s?D\b/i', $rd)) {
            return false; // un enlace de Drive no dice a quién nombra
        }
        $rd = ' ' . preg_replace('/[^A-Z]+/', ' ', strtoupper(Str::ascii($rd))) . ' ';
        $palabras = array_filter(explode(' ', preg_replace('/[^A-Z]+/', ' ', strtoupper(Str::ascii($nombre)))), fn ($p) => strlen($p) >= 4);

        return count(array_filter($palabras, fn ($p) => str_contains($rd, " {$p} "))) >= 2;
    }

    /**
     * Fecha escrita de cualquier forma en el archivo general: "22/11/2024", "27/06/25", "vier 22/03/2024",
     * "24 de abril de 2026". Null si no se entiende o es imposible (formato roto como "05/0372024").
     */
    private static function fechaLibre(?string $texto): ?string
    {
        $t = trim((string) $texto);
        if (preg_match('#\b(\d{1,2})/(\d{1,2})/(\d{4}|\d{2})\b#', $t, $m)) {
            $anio = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];

            return checkdate((int) $m[2], (int) $m[1], $anio) ? sprintf('%04d-%02d-%02d', $anio, $m[2], $m[1]) : null;
        }
        [$fecha] = self::fechaActa($t, null);

        return $fecha;
    }

    /** "Ana Corzo / Firma: Kiara Eguiluz", "Bach. X\nFirma: Y" -> el asesor real es el primero. */
    private static function primeraPersona(?string $texto): ?string
    {
        return self::personasDeCelda($texto)[0] ?? null;
    }

    /**
     * Todas las personas de una celda de jurado o asesor: una por línea, o separadas por « / », coma o
     * « y ». Lo que va después de «Firma:» (quien firma por otro) y lo que está entre paréntesis
     * no son integrantes. Sin títulos (Lic., Mag., Dra.…).
     *
     * @return string[]
     */
    private static function personasDeCelda(?string $texto): array
    {
        $t = preg_replace(['/\bfirma\s*:[^\n]*/iu', '/\([^)]*\)?/u', '/,\s*aprueba.*$/imu'], '', (string) $texto);
        $personas = [];
        foreach (preg_split('/\n|\/|,|;|\s+y\s+|\s-\s/u', $t) as $parte) {
            $parte = trim(preg_replace('/^\s*((bach|lic|mag|mg|dra?|prof|psic[oó]l|ing|doc)\.?\s*)+/iu', '', trim($parte)), " \t,.\"'?");
            if (mb_strlen($parte) >= 5 && preg_match('/\p{L}{3,}\s+\p{L}{3,}/u', $parte)) {
                $personas[] = preg_replace('/\s+/u', ' ', $parte);
            }
        }

        return $personas;
    }

    /**
     * Asesor o jurado de una etapa en «Participantes», que es de donde lee la secuencia del proceso.
     * Solo si la persona está en el sistema (docente o catálogo); si ya figura, no se duplica.
     */
    private function agregarParticipante(SeguimientoTitulacion $seguimiento, string $etapa, string $funcion, ?string $nombre): void
    {
        [$docenteId, $externoId] = $this->resolverPersona($nombre);
        if (! $docenteId && ! $externoId) {
            return;
        }
        \App\Models\Titulacion\Participante::firstOrCreate([
            'seguimiento_id' => $seguimiento->id, 'etapa' => $etapa, 'funcion' => $funcion,
            'docente_id' => $docenteId, 'asesor_externo_id' => $externoId,
        ]);
    }

    /** «Situación del Tesis» de la hoja → valor de tesis.estado (Tesis::SITUACIONES); null si no se reconoce. */
    private static function situacionTesis(?string $texto): ?string
    {
        $t = mb_strtolower(Str::ascii(trim((string) $texto)));

        return match (true) {
            $t === '' => null,
            str_contains($t, 'repositorio') => 'en_repositorio',
            str_contains($t, 'solicit') => 'no_solicito',
            str_contains($t, 'no se presenta'), str_contains($t, 'no present') => 'no_se_presenta',
            str_starts_with($t, 'pendiente') => 'pendiente',
            default => null,
        };
    }

    /**
     * Situación que escribe la Unidad ("T. Bach", "Licenciada/a"…) -> rango de estados del sistema
     * en el que debería estar la persona. Null si no permite comparar.
     */
    private static function estadoEsperado(string $situacion): ?array
    {
        $s = mb_strtolower(Str::ascii(trim($situacion)));

        return match (true) {
            str_starts_with($s, 'licenciad') => ['licenciado', 'licenciado'],
            $s === 'bachiller', str_starts_with($s, 't. lic') => ['bachiller', 'tesis_aprobada'],
            str_starts_with($s, 't. bach'), str_starts_with($s, 'tr. repo') => ['pti_aprobado', 'ti_aprobado'],
            str_starts_with($s, 'pti apto'), str_starts_with($s, 'vence pti') => ['pti_aprobado', 'pti_aprobado'],
            str_starts_with($s, 'pti pendiente') => ['sin_pti', 'pti_en_proceso'],
            default => null,
        };
    }

    /** Hoja «Archivo general» (PPD o FID): una persona puede tener una sola fila; se completan sus datos. */
    private function procesarArchivo($grupo, string $modalidad): array
    {
        /** @var Importacion $fila */
        $fila = $grupo->first();
        $d = $fila->datos_normalizados ?? [];
        $item = [
            'nombre' => $fila->nombre_estudiante, 'dni' => null, 'periodo' => $d['anio_ingreso'] ?? null, 'programa' => $d['programa'] ?? null,
            'expediente' => null, 'user_id' => $fila->user_id, 'cambios' => [], 'diferencias' => [], 'motivos' => [], 'propuesta' => null,
        ];

        if (preg_match('/no culmin/i', (string) ($d['situacion'] ?? ''))) {
            return array_merge($item, ['accion' => 'omitido', 'motivos' => ['La hoja dice «No culminó»']]);
        }
        if (! $fila->user_id) {
            $parecidas = User::whereNotNull('apellidos')->get(['id', 'name', 'apellidos', 'condicion'])
                ->filter(fn ($u) => NombresImportados::mismaPersona((string) $fila->nombre_estudiante, $u->apellidos . ' ' . $u->name));

            return array_merge($item, ['accion' => 'revisar', 'motivos' => [match (true) {
                $parecidas->count() > 1 => 'Varias personas del sistema coinciden con este nombre: vincúlalo a mano.',
                $parecidas->count() === 1 => "Coincide con la cuenta de {$parecidas->first()->apellidos}, {$parecidas->first()->name} ("
                    . ($parecidas->first()->getRoleNames()->implode(', ') ?: 'sin rol') . '), que no tiene ficha de estudiante ' . strtoupper($modalidad) . ': usa «Completar ficha».',
                default => 'No tiene cuenta en el sistema (esta hoja no trae DNI ni correo): primero sincroniza la hoja de expedientes que sí los trae.',
            }]]);
        }

        $user = User::with(['alumno', 'alumnoB'])->find($fila->user_id);
        $item['nombre'] = trim($user->apellidos . ', ' . $user->name, ', ');
        $item['dni'] = $user->dni;
        $item['condicion'] = $user->condicion;

        $antes = $this->foto($user->id);
        $seguimiento = SeguimientoTitulacion::firstOrCreate(
            ['user_id' => $user->id],
            ['estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti')]
        );
        $diferencias = [];
        $extras = [];
        foreach ($grupo as $f) {
            $this->aplicarFilaArchivo($seguimiento, $f, $user, $modalidad, $diferencias, $extras);
        }

        // Situación de la hoja vs. estado que el sistema calcula de los datos: solo se avisa si no cuadra.
        if (($esperado = self::estadoEsperado((string) ($d['situacion'] ?? ''))) !== null) {
            $orden = array_keys(\App\Services\Titulacion\AvanceTitulacion::ESTADOS);
            $actual = $seguimiento->fresh(['pti', 'ptis', 'tesis', 'actas', 'resoluciones'])->avance()->estadoClave();
            $pos = array_search($actual, $orden, true);
            if ($pos < array_search($esperado[0], $orden, true) || $pos > array_search($esperado[1], $orden, true)) {
                $diferencias[] = "Situación: la hoja dice «{$d['situacion']}», pero con los datos registrados el sistema calcula «"
                    . \App\Services\Titulacion\AvanceTitulacion::ESTADOS[$actual] . '». Revisa qué falta o qué sobra.';
            }
        }

        $item['diferencias'] = $diferencias;
        $item['cambios'] = array_merge(
            $seguimiento->wasRecentlyCreated ? ['Se inicia su proceso de titulación'] : [],
            $this->cambios($antes, $this->foto($user->id)),
            $extras
        );

        return $item + ['accion' => $item['cambios'] ? 'completa' : 'al_dia'];
    }

    /** Una fila del archivo general (con lo heredado del grupo) -> PTI, Tesis, dictaminantes, celular. Solo llena lo vacío. */
    private function aplicarFilaArchivo(SeguimientoTitulacion $seguimiento, Importacion $f, User $user, string $modalidad, array &$diferencias, array &$extras): void
    {
        $d = $f->datos_normalizados ?? [];
        foreach ($this->herencia[$f->id] ?? [] as $campo => $valor) {
            if (blank($d[$campo] ?? null)) {
                $d[$campo] = $valor;
            }
        }
        $esUrl = fn ($v) => (bool) preg_match('#^https?://\S+$#i', trim((string) $v));
        // Un título es texto real: no "Falta contexto", ni un fragmento cortado ("como estrategia…", en minúscula).
        $titulo = function ($v) {
            $v = self::limpiarTitulo($v);
            if (mb_strlen($v) < 20 || preg_match('/^(falta contexto|no reporta|no hizo|en proceso de redacci|lo est[aá] rearmando)/iu', $v) || preg_match('/^\p{Ll}/u', $v)) {
                return null;
            }

            return $v;
        };
        $anioIngreso = preg_match('/(20\d\d)/', (string) ($d['anio_ingreso'] ?? ''), $m) ? (int) $m[1] : null;
        $fechaValida = function (?string $texto, string $etiqueta) use ($anioIngreso, &$diferencias) {
            if (blank($texto)) {
                return null;
            }
            $fecha = self::fechaLibre($texto);
            if (! $fecha) {
                $diferencias[] = "{$etiqueta}: no se entiende la fecha «{$texto}»: no se registró.";

                return null;
            }
            $anio = (int) substr($fecha, 0, 4);
            if (($anioIngreso && $anio < $anioIngreso) || $anio > (int) now()->year + 1) {
                $diferencias[] = "{$etiqueta}: la fecha «{$texto}» no es posible para esta persona (ingresó en {$anioIngreso}): no se registró.";

                return null;
            }

            return $fecha;
        };

        // PTI: título del trabajo (FID: «Título TI»; PPD: su única columna de título), asesor, RD y enlace al ITI.
        $pti = Pti::firstOrNew(['seguimiento_id' => $seguimiento->id]);
        $asesorTexto = self::primeraPersona($d['asesor'] ?? null);
        [$asesorId, $asesorExternoId] = $this->resolverPersona($asesorTexto);
        if ($asesorTexto && ! $asesorId && ! $asesorExternoId && blank($pti->asesor_id) && blank($pti->asesor_externo_id)) {
            $diferencias[] = "Asesor «{$asesorTexto}»: no está en el sistema (ni como docente ni en el catálogo de asesores).";
        }
        $rdPti = trim((string) ($d['rd_pti'] ?? ''));
        foreach ([
            'titulo' => $titulo($modalidad === 'fid' ? ($d['titulo_ti'] ?? null) : ($d['titulo_tesis'] ?? null)),
            'link_iti' => $esUrl($d['link_iti'] ?? null) ? trim($d['link_iti']) : null,
            'rd_pti' => preg_match('/\bR\.?\s?D\b/i', $rdPti) ? $rdPti : null,
        ] as $campo => $valor) {
            if ($valor !== null && blank($pti->{$campo})) {
                $pti->{$campo} = $valor;
            }
        }
        // Un solo asesor: docente con cuenta o asesor del catálogo; solo si no tiene ninguno.
        if (blank($pti->asesor_id) && blank($pti->asesor_externo_id)) {
            $pti->asesor_id = $asesorId;
            $pti->asesor_externo_id = $asesorExternoId;
        }
        // La secuencia del proceso muestra asesor y jurado del TI desde «Participantes».
        $this->agregarParticipante($seguimiento, 'ti', 'asesor', $asesorTexto);
        foreach (self::personasDeCelda($d['jurado_exposicion'] ?? null) as $jurado) {
            $this->agregarParticipante($seguimiento, 'ti', 'jurado', $jurado);
        }
        if (! $pti->exists ? $pti->isDirty(['titulo', 'asesor_id', 'asesor_externo_id', 'link_iti', 'rd_pti']) : $pti->isDirty()) {
            $pti->save();
        }
        if ($pti->rd_pti && $pti->wasChanged('rd_pti') || ($pti->wasRecentlyCreated && $pti->rd_pti)) {
            Resolucion::firstOrCreate(['seguimiento_id' => $seguimiento->id, 'tipo' => 'PTI', 'numero_rd' => $pti->rd_pti]);
        }

        // Tesis: título (FID), fechas, repositorios y grados en SUNEDU.
        $tesis = Tesis::firstOrNew(['seguimiento_id' => $seguimiento->id]);
        $bachiller = preg_match('/^acept/i', trim((string) ($d['grado_bachiller'] ?? '')));
        $licenciado = preg_match('/^s[ií]\b/iu', trim((string) ($d['licenciado_sunedu'] ?? $d['registro_sunedu'] ?? '')));
        // «Grado Licenciado: Aceptado» (FID) = terminó todo el proceso; la hoja deja vacío «Registro SUNEDU».
        $licenciadoAceptado = ! $licenciado && preg_match('/^acept/i', trim((string) ($d['grado_licenciado'] ?? '')));
        foreach ([
            'titulo' => $modalidad === 'fid' ? $titulo($d['titulo_tesis'] ?? null) : null,
            'fecha_exposicion' => $fechaValida($d['fecha_exposicion'] ?? null, 'Fecha de exposición del TI'),
            'fecha_sustentacion' => $fechaValida($d['fecha_sustentacion'] ?? null, 'Fecha de sustentación de la tesis'),
            'url_repositorio_ti' => $esUrl($d['url_ti_repositorio'] ?? null) ? trim($d['url_ti_repositorio']) : null,
            'url_repositorio' => $esUrl($d['url_repositorio'] ?? null) ? trim($d['url_repositorio']) : null,
            'link_tesis' => $esUrl($d['enlace_tesis'] ?? null) ? trim($d['enlace_tesis']) : null,
            // «Grado Bachiller: Aceptado» = su grado ya está en SUNEDU (puede iniciar la Licenciatura).
            'bachiller_sunedu' => $bachiller ? 'Aceptado (archivo general)' : null,
            'titulo_sunedu' => $licenciado ? 'Registrado en SUNEDU (archivo general)' : ($licenciadoAceptado ? 'Aceptado (archivo general)' : null),
        ] as $campo => $valor) {
            if ($valor !== null && blank($tesis->{$campo})) {
                $tesis->{$campo} = $valor;
            }
        }
        // «Grado Licenciado: Aceptado» también es el paso «Licenciatura aceptada por la Escuela» (sin fecha en la hoja).
        if (preg_match('/^acept/i', trim((string) ($d['grado_licenciado'] ?? ''))) && ! $tesis->licenciatura_escuela) {
            $tesis->licenciatura_escuela = true;
        }
        // Situación del Tesis («En repositorio», «No solicitó»…): solo si en el sistema sigue sin definir (pendiente).
        $situacion = self::situacionTesis($d['situacion_tesis'] ?? null);
        if ($situacion && in_array($tesis->estado, [null, '', 'pendiente'], true) && $situacion !== $tesis->estado) {
            $tesis->estado = $situacion;
        } elseif (filled($d['situacion_tesis'] ?? null) && ! $situacion) {
            $diferencias[] = 'Situación del Tesis «'.Str::limit($d['situacion_tesis'], 80)."»: es un avance, no una de las opciones (En repositorio, Pendiente, No solicitó, No se presenta); sigue «Pendiente».";
        }
        // Enlaces a los dictámenes (Link Dictamen 1 y 2): se suman a los que ya tenga, sin repetir.
        $enlaces = collect([1, 2])->map(fn ($n) => $esUrl($d["link_dictamen_{$n}"] ?? null) ? trim($d["link_dictamen_{$n}"]) : null)->filter();
        $todos = collect($tesis->enlaces_dictamen ?? [])->concat($enlaces)->unique()->values();
        if ($todos->count() > count($tesis->enlaces_dictamen ?? [])) {
            $tesis->enlaces_dictamen = $todos->all();
        }
        if ($tesis->isDirty()) {
            $tesis->save();
        }

        // Dictaminantes de la tesis (Paso «Dictaminantes»); sus enlaces ya quedaron en «Enlaces de los dictaminantes».
        foreach ([1, 2] as $n) {
            $nombre = self::primeraPersona($d["dictaminante_{$n}"] ?? null);
            if (! $nombre) {
                continue;
            }
            [$docenteId, $externoId] = $this->resolverPersona($nombre);
            if ($docenteId || $externoId) {
                \App\Models\Titulacion\Participante::firstOrCreate([
                    'seguimiento_id' => $seguimiento->id, 'etapa' => 'tesis', 'funcion' => 'jurado',
                    'docente_id' => $docenteId, 'asesor_externo_id' => $externoId, 'cargo' => 'Dictaminante',
                ]);
            } else {
                $diferencias[] = "Dictaminante «{$nombre}»: no está en el sistema (ni como docente ni en el catálogo de asesores); no se agregó.";
            }
        }
        // Jurado Sus.Tesis (Paso «Jurado de la sustentación»): aparte de los dictaminantes, aunque sean los mismos docentes.
        foreach (self::personasDeCelda($d['jurado_sustentacion'] ?? null) as $jurado) {
            [$docenteId, $externoId] = $this->resolverPersona($jurado);
            if (! $docenteId && ! $externoId) {
                $diferencias[] = "Jurado de sustentación «{$jurado}»: no está en el sistema; no se agregó.";
                continue;
            }
            $yaEsta = \App\Models\Titulacion\Participante::where(['seguimiento_id' => $seguimiento->id, 'etapa' => 'tesis', 'funcion' => 'jurado', 'docente_id' => $docenteId, 'asesor_externo_id' => $externoId])
                ->get()->contains(fn ($p) => ! \App\Services\Titulacion\PasosProceso::esDictaminante($p->cargo));
            if (! $yaEsta) {
                \App\Models\Titulacion\Participante::create([
                    'seguimiento_id' => $seguimiento->id, 'etapa' => 'tesis', 'funcion' => 'jurado', 'docente_id' => $docenteId, 'asesor_externo_id' => $externoId,
                ]);
            }
        }

        // Celular: a la ficha del estudiante, solo si su teléfono está vacío. Se toma el primer número válido.
        $ficha = $modalidad === 'ppd' ? $user->alumnoB : $user->alumno;
        if ($ficha && blank($ficha->numero) && filled($d['celular'] ?? null)) {
            preg_match_all('/9(?:\s?\d){8}(?!\d)/', (string) $d['celular'], $nums);
            $numero = isset($nums[0][0]) ? preg_replace('/\D/', '', $nums[0][0]) : null;
            if ($numero) {
                DB::table($modalidad === 'ppd' ? 'ppds' : 'alumnos')->where('id', $ficha->id)->update(['numero' => $numero]);
                $ficha->numero = $numero;
                $extras[] = "Celular {$numero} — copiado a su ficha (estaba vacío)";
            } else {
                $diferencias[] = "Celular «{$d['celular']}»: no parece un número de celular válido; no se copió.";
            }
        }
    }

    /** Estado de titulación de una persona, para comparar antes/después. */
    private function foto(int $userId): array
    {
        $seg = $userId ? SeguimientoTitulacion::where('user_id', $userId)->first() : null;
        if (! $seg) {
            return ['expediente' => null, 'pti' => [], 'tesis' => [], 'rds' => [], 'actas' => [], 'participantes' => []];
        }
        // El asesor se muestra con su nombre, no con su id.
        $valor = fn ($m, $c) => match (true) {
            $m->{$c} instanceof \DateTimeInterface => $m->{$c}->format('d/m/Y'),
            $c === 'asesor_id' && $m->{$c} => trim((User::find($m->{$c})?->name ?? '') . ' ' . (User::find($m->{$c})?->apellidos ?? '')),
            $c === 'asesor_externo_id' && $m->{$c} => \App\Models\Titulacion\Asesor::find($m->{$c})?->nombreCompleto(),
            default => $m->{$c},
        };
        $campos = fn ($m, array $cols) => $m ? collect($cols)->mapWithKeys(fn ($c) => [$c => $valor($m, $c)])->all() : [];

        return [
            'expediente' => $seg->numero_expediente,
            'pti' => $campos(Pti::where('seguimiento_id', $seg->id)->first(), ['fecha_pti', 'rd_pti', 'titulo', 'asesor_id', 'asesor_externo_id', 'link_iti', 'fecha_vencimiento', 'estado']),
            'tesis' => ($t = Tesis::where('seguimiento_id', $seg->id)->first()) ? $campos($t, ['fecha_exposicion', 'rd_ti', 'url_repositorio_ti', 'bachiller_sunedu', 'rd_extension', 'fecha_sustentacion', 'titulo', 'rd_tesis', 'url_repositorio', 'titulo_sunedu', 'link_tesis']) + [
                // Pasos nuevos de la Tesis, como texto (vacío = sin registrar) para que la vista previa los cuente.
                'enlaces_dictamen' => $t->enlaces_dictamen ? count($t->enlaces_dictamen) . ' enlace(s)' : null,
                'situacion' => $t->estado && $t->estado !== 'pendiente' ? (Tesis::SITUACIONES[$t->estado] ?? $t->estado) : null,
                'licenciatura_escuela' => $t->licenciatura_escuela ? 'Sí' : null,
            ] : [],
            'participantes' => \App\Models\Titulacion\Participante::where('seguimiento_id', $seg->id)->get()
                ->mapWithKeys(fn ($p) => [$p->id => ($p->etapa === 'ti' ? 'TI · ' : 'Tesis · ') . ($p->cargo ?: \App\Models\Titulacion\Participante::FUNCIONES[$p->funcion] ?? $p->funcion) . ': '
                    . ($p->docente_id ? trim((User::find($p->docente_id)?->name ?? '') . ' ' . (User::find($p->docente_id)?->apellidos ?? '')) : \App\Models\Titulacion\Asesor::find($p->asesor_externo_id)?->nombreCompleto())])->all(),
            'rds' => Resolucion::where('seguimiento_id', $seg->id)->get()->map(fn ($r) => $r->tipo . '|' . $r->numero_rd)->all(),
            'actas' => \App\Models\Titulacion\Acta::withCount('jurados')->where('seguimiento_id', $seg->id)->get()
                ->mapWithKeys(fn ($a) => [$a->id => ($a->tipo === 'expo' ? 'Acta de exposición' : 'Acta de sustentación')
                    . ($a->numero_acta ? ' N° ' . $a->numero_acta : '') . ($a->fecha ? ' · ' . $a->fecha->format('d/m/Y') : '')
                    . ($a->promedio !== null ? ' · promedio ' . rtrim(rtrim((string) $a->promedio, '0'), '.') : '')
                    . ($a->jurados_count ? ' · ' . $a->jurados_count . ' jurado(s)' : '')])->all(),
        ];
    }

    /** Lo que se completó, en frases cortas para la vista previa. */
    private function cambios(array $antes, array $despues): array
    {
        $etiquetas = [
            'fecha_pti' => 'fecha del PTI', 'rd_pti' => 'RD del PTI', 'titulo' => 'título', 'fecha_vencimiento' => 'vencimiento del PTI',
            'estado' => 'estado', 'asesor_id' => 'asesor(a)', 'asesor_externo_id' => 'asesor(a) del catálogo', 'link_iti' => 'enlace al ITI', 'url_repositorio_ti' => 'TI en el repositorio', 'url_repositorio' => 'tesis en el repositorio', 'titulo_sunedu' => 'licenciado en SUNEDU', 'fecha_exposicion' => 'fecha de exposición', 'rd_ti' => 'RD de exposición (TI)',
            'bachiller_sunedu' => 'bachiller SUNEDU', 'rd_extension' => 'RD de extensión', 'fecha_sustentacion' => 'fecha de sustentación', 'rd_tesis' => 'RD de tesis',
            'link_tesis' => 'enlace de la tesis', 'enlaces_dictamen' => 'enlaces de los dictaminantes', 'situacion' => 'situación de la tesis',
            'licenciatura_escuela' => 'Licenciatura aceptada por la Escuela',
        ];
        $lista = [];
        if (blank($antes['expediente']) && filled($despues['expediente'])) {
            $lista[] = 'Expediente ' . $despues['expediente'];
        }
        foreach (['pti' => 'PTI', 'tesis' => 'TI/Tesis'] as $clave => $grupo) {
            foreach ($despues[$clave] as $campo => $valor) {
                if (blank($antes[$clave][$campo] ?? null) && filled($valor)) {
                    $lista[] = "{$grupo} · " . ($etiquetas[$campo] ?? $campo) . ': ' . Str::limit((string) $valor, 60);
                }
            }
        }
        foreach (array_diff_key($despues['participantes'] ?? [], $antes['participantes'] ?? []) as $texto) {
            $lista[] = $texto . ' — registrado(a)';
        }
        foreach ($despues['actas'] ?? [] as $id => $texto) {
            if (! isset($antes['actas'][$id])) {
                $lista[] = $texto . ' — nueva';
            } elseif ($antes['actas'][$id] !== $texto) {
                $lista[] = $texto . ' — se completó';
            }
        }
        // La RD que ya se mostró como dato del PTI/Tesis no se repite como "RD registrada".
        $yaMostradas = array_filter(array_merge(array_values($despues['pti']), array_values($despues['tesis'])), 'is_string');
        $tipos = ['PTI' => 'PTI', 'Exposicion' => 'exposición', 'Extension' => 'extensión', 'Tesis' => 'tesis', 'CambioAsesor' => 'cambio de asesor'];
        foreach (array_diff($despues['rds'], $antes['rds']) as $rd) {
            [$t, $n] = explode('|', $rd, 2);
            if (in_array($n, $yaMostradas, true)) {
                continue;
            }
            $lista[] = 'RD de ' . ($tipos[$t] ?? $t) . ' registrada: ' . Str::limit($n, 60);
        }

        return $lista;
    }
}
