<?php

namespace App\Imports\RicardoPalma;

use App\Models\RicardoPalma\RpActa;
use App\Models\RicardoPalma\RpActaEstudiante;
use App\Models\RicardoPalma\RpCalificacion;
use App\Models\RicardoPalma\RpCurso;
use App\Models\RicardoPalma\RpEstudiante;
use App\Models\RicardoPalma\RpImportacion;
use App\Models\RicardoPalma\RpPeriodo;
use App\Models\RicardoPalma\RpPrograma;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ActaConsolidadaRpImport implements ToCollection, WithHeadingRow
{
    protected ?int $userId;
    protected string $nombreArchivo;
    protected int $exitosos = 0;
    protected int $fallidos = 0;
    protected array $errores = [];
    // Para el resumen que ve el usuario al terminar la carga
    protected int $notasNuevas = 0;
    protected int $notasActualizadas = 0;
    protected int $estudiantesNuevos = 0;
    protected array $actas = [];
    protected array $estudiantes = [];

    public function __construct(string $nombreArchivo, ?int $userId = null)
    {
        $this->nombreArchivo = $nombreArchivo;
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        $programasCache = RpPrograma::all()->keyBy(fn ($p) => mb_strtoupper(trim($p->codigo)));
        $programasNombreCache = RpPrograma::all()->keyBy(fn ($p) => mb_strtoupper(trim($p->nombre)));
        $cursosConCreditosNuevos = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                // +2 por encabezado base 1; las filas armadas desde el formato acta traen su propia referencia
                $numFila = $row['_fila'] ?? $index + 2;

                $periodoRaw = trim((string) ($row['periodo'] ?? ''));
                $programaStr = mb_strtoupper(trim((string) ($row['programa'] ?? '')));
                $semestre = (int) ($row['semestre'] ?? 0);
                $apellidos = trim((string) ($row['apellidos'] ?? ''));
                $nombres = trim((string) ($row['nombres'] ?? ''));
                $cursoNombre = preg_replace('/\s+/u', ' ', trim((string) ($row['curso'] ?? '')));

                // Validaciones mínimas de fila obligatoria
                if ($periodoRaw === '' || $programaStr === '' || $apellidos === '' || $cursoNombre === '') {
                    continue; // Fila vacía al final del archivo
                }

                // 1. Periodo: «1996-II»; también acepta «1996-2», «1996 II»…
                $periodoCod = self::normalizarPeriodo($periodoRaw);
                if (! $periodoCod) {
                    $this->error($numFila, "el periodo '{$periodoRaw}' no tiene el formato AAAA-I o AAAA-II.");
                    continue;
                }
                if ($semestre < 1 || $semestre > 10) {
                    $this->error($numFila, 'el semestre debe ser un número del 1 al 10.');
                    continue;
                }

                // Nota: vacía (retirado, licencia) o un número de 0 a 20
                $notaRaw = $row['nota'] ?? null;
                $nota = null;
                if ($notaRaw !== null && trim((string) $notaRaw) !== '') {
                    $notaTexto = str_replace(',', '.', trim((string) $notaRaw));
                    if (! is_numeric($notaTexto) || (float) $notaTexto < 0 || (float) $notaTexto > 20) {
                        $this->error($numFila, "la nota '{$notaRaw}' no es válida (debe estar entre 0 y 20).");
                        continue;
                    }
                    $nota = (float) $notaTexto;
                }

                [$anio, $sem] = explode('-', $periodoCod);
                $periodo = RpPeriodo::firstOrCreate(
                    ['codigo' => $periodoCod],
                    ['anio' => (int) $anio, 'semestre' => $sem, 'activo' => true]
                );

                // 2. Resolver Programa
                $programa = $programasCache->get($programaStr) ?? $programasNombreCache->get($programaStr);
                if (! $programa) {
                    $this->error($numFila, "el programa '{$programaStr}' no existe en el catálogo.");
                    continue;
                }

                // 3. Resolver Estudiante. La matrícula se repite entre carreras, así que se busca
                // dentro de la misma carrera; el DNI identifica a la persona en cualquier carrera.
                $matricula = trim((string) ($row['nro_matricula'] ?? $row['matricula'] ?? ''));
                $dni = trim((string) ($row['dni'] ?? ''));
                $sexo = strtoupper(substr(trim((string) ($row['sexo'] ?? '')), 0, 1)) ?: null;

                $estudiante = null;
                if ($dni !== '') {
                    $estudiante = RpEstudiante::where('dni', $dni)->first();
                }
                if (! $estudiante && $matricula !== '') {
                    $estudiante = RpEstudiante::where('programa_id', $programa->id)->where('numero_matricula', $matricula)->first();
                }
                if (! $estudiante && $matricula === '' && $dni === '') {
                    $estudiante = RpEstudiante::where('programa_id', $programa->id)
                        ->where('apellidos', $apellidos)->where('nombres', $nombres)->first();
                }

                if (! $estudiante) {
                    $this->estudiantesNuevos++;
                    $estudiante = RpEstudiante::create([
                        'programa_id' => $programa->id,
                        'numero_matricula' => $matricula !== '' ? $matricula : null,
                        'dni' => $dni !== '' ? $dni : null,
                        'apellidos' => $apellidos,
                        'nombres' => $nombres,
                        'sexo' => in_array($sexo, ['M', 'F']) ? $sexo : null,
                        'anio_ingreso' => $semestre === 1 ? (int) $anio : null,
                        'estado' => 'REGULAR',
                    ]);
                } else {
                    // Completar datos que no tenía
                    if (empty($estudiante->dni) && $dni !== '') {
                        $estudiante->dni = $dni;
                    }
                    if (empty($estudiante->numero_matricula) && $matricula !== '') {
                        $estudiante->numero_matricula = $matricula;
                    }
                    if (empty($estudiante->sexo) && in_array($sexo, ['M', 'F'])) {
                        $estudiante->sexo = $sexo;
                    }
                    if ($estudiante->isDirty()) {
                        $estudiante->save();
                    }
                }

                // 4. Resolver Curso. Si la fila trae CREDITOS distintos, se corrige el curso y
                // al final se recalculan los puntajes ya guardados de ese curso.
                $creditosRaw = trim((string) ($row['creditos'] ?? ''));
                $creditos = $creditosRaw !== '' ? max(1, (int) $creditosRaw) : null;
                $curso = RpCurso::firstOrCreate(
                    [
                        'programa_id' => $programa->id,
                        'numero_semestre' => $semestre,
                        'nombre' => $cursoNombre,
                    ],
                    [
                        'creditos' => $creditos ?? 2,
                    ]
                );
                if ($creditos !== null && (int) $curso->creditos !== $creditos) {
                    $curso->update(['creditos' => $creditos]);
                    $cursosConCreditosNuevos[$curso->id] = $creditos;
                }

                // 5. Resolver Acta (n.º de acta y fecha de visación se completan o corrigen si vienen)
                $seccion = trim((string) ($row['seccion'] ?? '')) ?: 'UNICA';
                $turno = trim((string) ($row['turno'] ?? '')) ?: 'TARDE';
                $nroActa = trim((string) ($row['nro_acta'] ?? ''));
                // Fecha de visación (columna FECHA_VISACION; también acepta FECHA_EMISION o FECHA)
                $fecha = self::parsearFecha($row['fecha_visacion'] ?? $row['fecha_emision'] ?? $row['fecha'] ?? null);

                $acta = RpActa::firstOrCreate(
                    [
                        'periodo_id' => $periodo->id,
                        'programa_id' => $programa->id,
                        'numero_semestre' => $semestre,
                        'seccion' => $seccion,
                        'turno' => $turno,
                    ],
                    [
                        'numero_acta' => $nroActa !== '' ? $nroActa : null,
                        'fecha_emision' => $fecha,
                    ]
                );
                if ($nroActa !== '' && $acta->numero_acta !== $nroActa) {
                    $acta->numero_acta = $nroActa;
                }
                if ($fecha && optional($acta->fecha_emision)->toDateString() !== $fecha) {
                    $acta->fecha_emision = $fecha;
                }
                if ($acta->isDirty()) {
                    $acta->save();
                }

                // 6. Registrar o Actualizar Calificación
                $observacion = mb_strtoupper(trim((string) ($row['observacion'] ?? '')));
                if ($observacion === '' && $nota !== null) {
                    $observacion = $nota >= 11 ? 'APROBADO' : 'DESAPROBADO';
                }

                $calificacion = RpCalificacion::updateOrCreate(
                    [
                        'acta_id' => $acta->id,
                        'estudiante_id' => $estudiante->id,
                        'curso_id' => $curso->id,
                    ],
                    [
                        'calificacion' => $nota,
                        'puntaje' => $nota !== null ? round($nota * $curso->creditos, 2) : null,
                        'observacion' => $observacion !== '' ? $observacion : null,
                    ]
                );

                // Observación del alumno en el acta («RETIRADA R.D. N°…»): se repite en cada fila
                // del alumno y se guarda solo si viene.
                $obsAlumno = trim((string) ($row['obs_alumno'] ?? ''));
                if ($obsAlumno !== '') {
                    RpActaEstudiante::updateOrCreate(
                        ['acta_id' => $acta->id, 'estudiante_id' => $estudiante->id],
                        ['observacion' => mb_substr($obsAlumno, 0, 255)]
                    );
                }

                $this->exitosos++;
                $calificacion->wasRecentlyCreated ? $this->notasNuevas++ : $this->notasActualizadas++;
                $this->actas[$acta->id] = true;
                $this->estudiantes[$estudiante->id] = true;
            }

            // Puntajes guardados antes con los créditos anteriores del curso
            foreach ($cursosConCreditosNuevos as $cursoId => $creditos) {
                RpCalificacion::where('curso_id', $cursoId)->whereNotNull('calificacion')
                    ->update(['puntaje' => DB::raw('ROUND(calificacion * '.(int) $creditos.', 2)')]);
            }

            // Registrar en auditoría de importaciones
            RpImportacion::create([
                'user_id' => $this->userId,
                'tipo' => 'ACTA_CONSOLIDADA',
                'archivo_nombre' => $this->nombreArchivo,
                'total_filas' => $this->exitosos + $this->fallidos,
                'filas_exitosas' => $this->exitosos,
                'filas_con_error' => $this->fallidos,
                'detalles_errores' => count($this->errores) > 0 ? $this->errores : null,
                'estado' => $this->fallidos === 0 ? 'COMPLETADO' : 'CON_OBSERVACIONES',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** $fila: número de fila, una referencia ya armada («Hoja «ACTA», fila 14») o null (mensaje completo). */
    public function error(int|string|null $fila, string $mensaje): void
    {
        $this->fallidos++;
        $this->errores[] = $fila === null ? $mensaje : (is_int($fila) ? "Fila {$fila}" : $fila).": {$mensaje}";
    }

    /** «1996-II», «1996-2», «1996 II», «1996II» → «1996-II»; null si no se reconoce. */
    public static function normalizarPeriodo(string $valor): ?string
    {
        if (! preg_match('/^(\d{4})\s*[-\/ ]?\s*(II|I|2|1)$/i', trim($valor), $m)) {
            return null;
        }
        $sem = strtoupper($m[2]);

        return $m[1].'-'.(in_array($sem, ['II', '2'], true) ? 'II' : 'I');
    }

    /** Fecha del Excel (número de serie, «24/01/1997» o «1997-01-24») → «1997-01-24»; null si no se reconoce. */
    public static function parsearFecha($valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }
        if (is_numeric($valor)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $valor))->toDateString();
            } catch (\Throwable $e) {
                return null;
            }
        }
        // «16 - 01 - 98» del acta escrita a máquina → «16-01-98»
        $texto = preg_replace('/\s*([-\/.])\s*/', '$1', trim((string) $valor));
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y', 'd.m.Y'] as $formato) {
            try {
                $fecha = Carbon::createFromFormat('!'.$formato, $texto);
                if ($fecha && $fecha->format($formato) === $texto) {
                    return $fecha->toDateString();
                }
            } catch (\Throwable $e) {
                // probar el siguiente formato
            }
        }

        return null;
    }

    public function getResultado(): array
    {
        return [
            'exitosos' => $this->exitosos,
            'fallidos' => $this->fallidos,
            'errores' => $this->errores,
            'notas_nuevas' => $this->notasNuevas,
            'notas_actualizadas' => $this->notasActualizadas,
            'estudiantes' => count($this->estudiantes),
            'estudiantes_nuevos' => $this->estudiantesNuevos,
            'actas' => array_keys($this->actas),
        ];
    }
}
