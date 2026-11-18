<?php

namespace App\Services\SeguimientoEgresado;

use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConciliacionHistoricoService
{
    public const FUENTES_DEFINIDAS = [
        'encuesta_depurada_2026' => [
            'codigo' => 'encuesta_depurada_2026',
            'nombre' => 'Encuesta de Empleabilidad y Seguimiento 2026 (113 respuestas depuradas)',
            'tipo' => 'encuesta_2026',
            'archivo_relativo' => '01_ENCUESTA_2026/encuesta_2026_113_respuestas_depuradas.csv',
            'fecha_corte_conocida' => null,
            'fecha_corte_etiqueta' => 'Corte 2026 (fecha exacta no consignada)',
            'notas_calidad' => '119 respuestas brutas -> 115 nombres únicos -> 113 casos analíticos depurados.',
        ],
        'maestra_historica_2026' => [
            'codigo' => 'maestra_historica_2026',
            'nombre' => 'Base Maestra Histórica y Encuesta Consolidada (254 personas)',
            'tipo' => 'maestra_historica',
            'archivo_relativo' => '01_ENCUESTA_2026/base_maestra_historico_y_encuesta_254_personas.csv',
            'fecha_corte_conocida' => null,
            'fecha_corte_etiqueta' => 'Consolidado histórico / 2026',
            'notas_calidad' => '256 filas de origen -> 254 personas tras excluir 2 duplicados exactos.',
        ],
        'padron_promociones_v8' => [
            'codigo' => 'padron_promociones_v8',
            'nombre' => 'Padrón Histórico de Siete Promociones 2021-II a 2025-II (258 personas)',
            'tipo' => 'padron_promociones',
            'archivo_relativo' => '02_PADRON_HISTORICO/padron_historico_258_personas_con_nombres.csv',
            'fecha_corte_conocida' => '2026-10-28',
            'fecha_corte_etiqueta' => '2021-II a 2025-II (corte 2026-10-28)',
            'notas_calidad' => '266 filas en 7 listas -> 258 personas únicas (94 con egreso confirmado en esa fuente y 164 pendientes).',
        ],
    ];

    public function __construct(
        private GrupoFormacionService $grupoFormacionService,
        private SeguimientoAutorizacionService $autorizacionService
    ) {}

    /**
     * Comprueba que la base efectiva sea un entorno aislado cuando se solicita escritura ($apply = true).
     */
    public function verificarBaseDestinoAislada(bool $apply, bool $confirmIsolated = false, ?string $expectedDb = null): string
    {
        $pdo = DB::connection()->getPdo();
        $dbEfectiva = DB::connection()->getDatabaseName();

        if (!$pdo || empty($dbEfectiva)) {
            throw new \RuntimeException('No se pudo verificar la conexión efectiva a la base de datos.');
        }

        if ($apply) {
            if (app()->environment('production')) {
                throw new \RuntimeException('PROHIBIDO: No se permite ejecutar --apply en entorno de producción.');
            }

            if (!$confirmIsolated) {
                throw new \RuntimeException('SEGURIDAD: Para escribir en la base de ensayo debe confirmar explícitamente con --confirm-isolated.');
            }

            $dbLower = strtolower($dbEfectiva);
            if ($dbLower === 'puklla' || (str_contains($dbLower, 'prod') && !str_contains($dbLower, 'preprod'))) {
                throw new \RuntimeException(
                    "SEGURIDAD: Escritura bloqueada sobre la base institucional '{$dbEfectiva}'. " .
                    "La conciliación con escritura solo está permitida en una base aislada de ensayo (p. ej. puklla_ensayo o puklla_preprod)."
                );
            }

            if ($expectedDb !== null && $dbEfectiva !== $expectedDb) {
                throw new \RuntimeException(
                    "SEGURIDAD: La base efectiva '{$dbEfectiva}' no coincide con la base autorizada '{$expectedDb}'."
                );
            }
        }

        return $dbEfectiva;
    }

    /**
     * Resuelve la ruta del paquete privado (directorio o ZIP) y verifica todos los hashes SHA-256 del manifiesto.
     */
    public function verificarPaqueteFuente(string $sourcePath): array
    {
        if (!file_exists($sourcePath)) {
            throw new \RuntimeException("No existe la ruta de origen especificada: {$sourcePath}");
        }

        $rootDir = $sourcePath;

        if (is_file($sourcePath) && str_ends_with(strtolower($sourcePath), '.zip')) {
            $extractDir = storage_path('app/privado_historico/extracted_' . substr(hash_file('sha256', $sourcePath), 0, 12));
            if (!is_dir($extractDir)) {
                mkdir($extractDir, 0700, true);
            }
            @chmod($extractDir, 0700);
            $zip = new \ZipArchive();
            if ($zip->open($sourcePath) !== true) {
                throw new \RuntimeException('No se pudo abrir el archivo ZIP privado.');
            }
            $zip->extractTo($extractDir);
            $zip->close();
            $rootDir = $extractDir;
        }

        if (is_dir($rootDir . '/PUKLLA_EN_RED_DATOS_REALES_ENCUESTA_HISTORICO_2026')) {
            $rootDir .= '/PUKLLA_EN_RED_DATOS_REALES_ENCUESTA_HISTORICO_2026';
        }

        $manifestPath = $rootDir . '/05_CONTROL_CALIDAD/SHA256_MANIFEST.txt';
        if (!is_file($manifestPath)) {
            throw new \RuntimeException('INTEGRIDAD RECHAZADA: No se encontró 05_CONTROL_CALIDAD/SHA256_MANIFEST.txt en el paquete.');
        }

        $lineas = file($manifestPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $hashesVerificados = [];

        foreach ($lineas as $linea) {
            if (!preg_match('/^([a-f0-9]{64})\s+(.+)$/i', trim($linea), $m)) {
                continue;
            }
            $hashEsperado = strtolower($m[1]);
            $relPath = trim($m[2]);
            $fullPath = $rootDir . '/' . $relPath;

            if (!is_file($fullPath)) {
                throw new \RuntimeException("INTEGRIDAD RECHAZADA: Falta el archivo requerido por el manifiesto: {$relPath}");
            }

            $hashReal = strtolower(hash_file('sha256', $fullPath));
            if (!hash_equals($hashEsperado, $hashReal)) {
                throw new \RuntimeException(
                    "INTEGRIDAD RECHAZADA: El hash SHA-256 del archivo '{$relPath}' ({$hashReal}) no coincide con el manifiesto ({$hashEsperado})."
                );
            }

            $hashesVerificados[$relPath] = $hashReal;
        }

        if (empty($hashesVerificados)) {
            throw new \RuntimeException('INTEGRIDAD RECHAZADA: El manifiesto SHA-256 está vacío o mal formado.');
        }

        return [
            'root_dir' => $rootDir,
            'hashes' => $hashesVerificados,
        ];
    }

    /**
     * Construye el índice en memoria de usuarios y trayectorias institucionales sin modificar la BD.
     */
    public function construirIndiceInstitucional(): array
    {
        $usuarios = User::query()
            ->with( array_merge(GrupoFormacionService::RELACIONES, ['seguimientoTitulacion.estadoTitulacion', 'seguimientoTitulacion.resoluciones', 'seguimientoTitulacion.actas']))
            ->get();

        $porId = [];
        $porDni = [];
        $porCorreo = [];
        $porNombreTokens = [];
        $porApellidos = [];

        foreach ($usuarios as $u) {
            $porId[$u->id] = $u;

            $dnis = array_filter([
                $this->normalizarDni($u->dni),
                $this->normalizarDni($u->alumno?->dni),
                $this->normalizarDni($u->alumnoB?->dni),
            ]);
            foreach (array_unique($dnis) as $d) {
                $porDni[$d][] = $u->id;
            }

            $correos = array_filter([
                $this->normalizarCorreo($u->email),
                $this->normalizarCorreo($u->alumno?->email),
                $this->normalizarCorreo($u->alumnoB?->email),
            ]);
            foreach (array_unique($correos) as $c) {
                $porCorreo[$c][] = $u->id;
            }

            $claveTokens = $this->normalizarNombreTokens($u->name . ' ' . $u->apellidos);
            if ($claveTokens !== '') {
                $porNombreTokens[$claveTokens][] = $u->id;
            }

            $claveApe = $this->normalizarTexto($u->apellidos ?? '');
            if ($claveApe !== '') {
                $porApellidos[$claveApe][] = $u->id;
            }
        }

        return [
            'por_id' => $porId,
            'por_dni' => $porDni,
            'por_correo' => $porCorreo,
            'por_nombre_tokens' => $porNombreTokens,
            'por_apellidos' => $porApellidos,
        ];
    }

    /**
     * Evalúa el cumplimiento de créditos curriculares de una persona.
     * REGLA DE DOMINIO (T08): No inferir egreso por suma de créditos si no existen planes de estudio,
     * convalidaciones, homologaciones y validación institucional verificable en el esquema.
     */
    public function evaluarCumplimientoCreditos(User $user): array
    {
        $existeTablaPlanes = Schema::hasTable('planes_estudio') && Schema::hasTable('convalidaciones');
        $tieneEventoEgreso = $user->trayectoriaEventos->contains(fn ($e) => $e->tipo === 'egreso');

        if ($tieneEventoEgreso) {
            return [
                'estado' => 'acreditado_institucional',
                'egreso_acreditado' => true,
                'motivo' => 'Cuenta con evento institucional de egreso registrado en trayectoria_eventos.',
            ];
        }

        if (!$existeTablaPlanes) {
            return [
                'estado' => 'no_verificado',
                'egreso_acreditado' => false,
                'motivo' => 'No existen reglas de plan de estudios ni tabla de convalidaciones verificables; prohibido decretar egreso por suma simple de créditos.',
            ];
        }

        return [
            'estado' => 'no_verificado',
            'egreso_acreditado' => false,
            'motivo' => 'Cumplimiento curricular pendiente de verificación oficial por secretaría académica.',
        ];
    }

    /**
     * Obtiene el resumen de hitos oficiales separados (Egreso, Titulación y Grado/Licenciatura) y el resumen académico por trayectoria (T09, T10).
     */
    public function obtenerHitosOficialesSeparados(User $user): array
    {
        $user->loadMissing(array_merge(GrupoFormacionService::RELACIONES, [
            'seguimientoTitulacion.estadoTitulacion',
            'seguimientoTitulacion.resoluciones',
            'seguimientoTitulacion.actas',
            'seguimientoTitulacion.pti',
            'seguimientoTitulacion.tesis',
        ]));

        $eventosEgreso = $user->trayectoriaEventos->where('tipo', 'egreso');
        $egresoAcreditado = $eventosEgreso->isNotEmpty();

        $seg = $user->seguimientoTitulacion;
        $claveTit = $seg?->estadoTitulacion?->clave;
        $avanceTit = $seg ? new \App\Services\Titulacion\AvanceTitulacion($seg) : null;

        $estadoTitulacion = 'No verificado en el sistema';
        $tituloAcreditado = false;
        if ($seg) {
            if ($claveTit === 'licenciado' || ($avanceTit && ($avanceTit->hitos['licenciado']['logrado'] ?? false))) {
                $estadoTitulacion = 'Título acreditado';
                $tituloAcreditado = true;
            } elseif (in_array($claveTit, ['pti_en_proceso', 'pti_aprobado', 'ti_aprobado', 'tesis_aprobada', 'bachiller'], true)) {
                $estadoTitulacion = 'En trámite (' . ($seg->estadoTitulacion?->nombre ?? $claveTit) . ')';
            } else {
                $estadoTitulacion = $seg->estadoTitulacion?->nombre ?? 'Registrado sin título acreditado';
            }
        }

        // Grado / Bachiller: evidencia específica (o hito SUNEDU en tesis)
        $tieneEvidenciaGrado = $claveTit === 'bachiller'
            || ($avanceTit && ($avanceTit->hitos['bachiller']['logrado'] ?? false))
            || ($seg && $seg->resoluciones && $seg->resoluciones->contains(fn ($r) => str_contains(strtolower((string) $r->tipo), 'bachiller') || str_contains(strtolower((string) $r->tipo), 'grado')));

        $clasificacion = $this->grupoFormacionService->resolver($user);
        $historialGeneral = \App\Services\HistorialAcademico::de($user);
        $periodosTodos = $historialGeneral['periodos'] ?? collect();

        $cursosPpdCiclo = $user->alumnoB ? \App\Services\HistorialAcademico::cursosPpdPorCiclo($user) : collect();
        $todosPpd = $cursosPpdCiclo->flatMap(fn ($b) => $b->cursos);
        $conNotaPpd = $todosPpd->filter(fn ($c) => $c->nota !== null);
        $aprobadosPpd = $conNotaPpd->where('desaprobado', false);
        $creditosPpd = $aprobadosPpd->sum(fn ($c) => (float) ($c->curso->creditos ?? 0));

        $periodosFid = $periodosTodos->where('modalidad', 'FID');
        $cursosFidOficiales = $periodosFid->flatMap(fn ($p) => $p->cursos)->reject(fn ($c) => $c->en_curso);
        $aprobadosFid = $cursosFidOficiales->filter(fn ($c) => $c->nota !== null && !$c->desaprobado);
        $creditosFid = $aprobadosFid->sum(fn ($c) => (float) ($c->curso?->creditos ?? 0));

        $trayectoriasDetalle = [];
        foreach (['FID', 'PPD'] as $mod) {
            $tieneFicha = $mod === 'FID' ? (bool) $user->alumno : (bool) $user->alumnoB;
            $enGrupos = in_array($mod, $clasificacion['grupos'] ?? [], true);
            if (!$tieneFicha && !$enGrupos) {
                continue;
            }
            $prog = $this->grupoFormacionService->programa($user, $mod);
            $evEgreso = $eventosEgreso->first(function ($e) use ($mod) {
                $p = $e->cicloOrigen?->programa ?? $e->cicloDestino?->programa;
                return $p?->modalidad?->value === $mod;
            });
            $esPpd = $mod === 'PPD';
            $cursosAprob = $esPpd ? $aprobadosPpd->count() : $aprobadosFid->count();
            $cursosTotal = $esPpd ? $todosPpd->count() : $cursosFidOficiales->count();
            $creditosAprob = $esPpd ? round($creditosPpd, 1) : round($creditosFid, 1);
            $promedioMod = $esPpd
                ? ($conNotaPpd->isNotEmpty() ? round($conNotaPpd->avg('nota'), 2) : null)
                : ($cursosFidOficiales->pluck('nota')->filter(fn ($n) => $n !== null)->isNotEmpty()
                    ? round($cursosFidOficiales->pluck('nota')->filter(fn ($n) => $n !== null)->avg(), 2)
                    : null);
            $ciclosCursados = $esPpd ? $cursosPpdCiclo->count() : $periodosFid->count();

            $trayectoriasDetalle[$mod] = [
                'modalidad' => $mod,
                'programa_id' => $prog?->id,
                'programa_nombre' => $prog?->nombre ?? ($esPpd ? 'Programa de Profesionalización Docente' : 'Formación Inicial Docente'),
                'ficha_id' => $esPpd ? $user->alumnoB?->id : $user->alumno?->id,
                'egreso_acreditado' => (bool) $evEgreso,
                'fecha_egreso' => $evEgreso?->fecha_inicio?->format('Y-m-d'),
                'resolucion_egreso' => $evEgreso?->resolucion ?: $evEgreso?->documento,
                'ciclos_o_periodos' => $ciclosCursados,
                'cursos_aprobados' => $cursosAprob,
                'cursos_registrados' => $cursosTotal,
                'creditos_aprobados' => $creditosAprob,
                'promedio_ponderado' => $promedioMod,
                'fuente_academica' => $cursosTotal > 0
                    ? ($esPpd ? 'Historial oficial PPD (periodo_ppds)' : 'Historial oficial FID (periodos)')
                    : 'Expediente previo a 2024-II (sin detalle de notas por curso en base digital)',
            ];
        }

        $documentosTitulacion = [];
        if ($seg) {
            foreach ($seg->resoluciones ?? [] as $rd) {
                if (filled($rd->numero_rd)) {
                    $documentosTitulacion[] = [
                        'tipo' => \App\Models\Titulacion\Resolucion::nombreTipo($rd->tipo),
                        'referencia' => 'RD ' . $rd->numero_rd,
                        'fecha' => $rd->fecha?->format('d/m/Y'),
                    ];
                }
            }
            foreach ($seg->actas ?? [] as $acta) {
                if (filled($acta->numero_acta)) {
                    $documentosTitulacion[] = [
                        'tipo' => 'Acta de ' . ($acta->tipo === 'tesis' ? 'Sustentación de Tesis' : 'Exposición de TI'),
                        'referencia' => 'Acta ' . $acta->numero_acta,
                        'fecha' => $acta->fecha?->format('d/m/Y'),
                    ];
                }
            }
        }

        return [
            'condicion_cuenta' => $user->condicion ?? 'No consignada',
            'clasificacion_grupo' => $clasificacion,
            'trayectorias' => $trayectoriasDetalle,
            'creditos_evaluacion' => $this->evaluarCumplimientoCreditos($user),
            'egreso' => [
                'estado' => $egresoAcreditado ? 'acreditado_institucional' : 'no_verificado',
                'acreditado' => $egresoAcreditado,
                'fechas' => $eventosEgreso->map(fn ($e) => $e->fecha_inicio?->format('Y-m-d'))->filter()->values()->all(),
                'fuente' => $egresoAcreditado ? 'Registrado por la institución (trayectoria_eventos)' : ($user->condicion === 'Egresado' ? 'Condición de cuenta Egresado (fecha exacta por verificar)' : 'No verificado en el sistema'),
            ],
            'titulacion' => [
                'estado' => $tituloAcreditado ? 'acreditado_institucional' : ($seg ? 'en_tramite' : 'no_verificado'),
                'titulo_acreditado' => $tituloAcreditado,
                'detalle' => $estadoTitulacion,
                'expediente' => $seg?->numero_expediente,
                'fecha_sunedu' => $seg?->tesis?->fecha_titulo_sunedu?->format('d/m/Y'),
                'registro_sunedu' => $seg?->tesis?->titulo_sunedu,
                'documentos' => $documentosTitulacion,
            ],
            'grado_bachiller' => [
                'estado' => $tieneEvidenciaGrado ? 'acreditado_institucional' : 'no_verificado',
                'acreditado' => $tieneEvidenciaGrado,
                'detalle' => $tieneEvidenciaGrado
                    ? ('Acreditado' . ($seg?->tesis?->bachiller_sunedu ? ' · SUNEDU: ' . $seg->tesis->bachiller_sunedu : ' (evidencia registrada en expediente)'))
                    : 'No verificado en el sistema',
                'fecha_sunedu' => $seg?->tesis?->fecha_bachiller_sunedu?->format('d/m/Y'),
            ],
        ];
    }

    /**
     * Concilia una fila individual separando estrictamente:
     * Nivel 1: Identidad canónica (`users.id`).
     * Nivel 2: Trayectoria académica (`FID` / `PPD` / `TRANSVERSAL` / `PENDIENTE`).
     */
    public function conciliarFila(array $fila, string $fuenteCodigo, string $filaOrigen, array $indice): array
    {
        $userIdExplicito = isset($fila['user_id']) && is_numeric($fila['user_id']) ? (int) $fila['user_id'] : null;
        $dni = $this->normalizarDni($fila['dni'] ?? $fila['DNI'] ?? null);
        $correo = $this->normalizarCorreo($fila['correo'] ?? $fila['email'] ?? null);
        $nombreLiteral = trim((string) ($fila['Nombre'] ?? $fila['nombre'] ?? ''));
        $programaTexto = trim((string) ($fila['Programa'] ?? $fila['programa'] ?? ''));

        $candidaturas = [];
        $userIdConfirmado = null;
        $decision = 'sin_correspondencia';
        $motivoDecision = 'Sin identificador institucional ni candidatos por nombre en la base actual.';
        $evidenciaDecision = null;

        // 1. Identificador institucional exacto (`user_id`)
        if ($userIdExplicito && isset($indice['por_id'][$userIdExplicito])) {
            $userIdConfirmado = $userIdExplicito;
            $decision = 'vinculo_confirmado';
            $motivoDecision = 'Identificador institucional users.id verificado.';
            $evidenciaDecision = 'users.id=' . $userIdExplicito;
            $candidaturas[] = [
                'user_id_candidato' => $userIdExplicito,
                'metodo' => 'id_institucional_exacto',
                'evidencia_referencia' => 'Coincidencia exacta users.id',
                'confianza' => 'alta_exacta',
                'estado_revision' => 'aprobado_exacto',
            ];
        }
        // 2. DNI exacto y único
        elseif ($dni && isset($indice['por_dni'][$dni])) {
            $idsDni = array_values(array_unique($indice['por_dni'][$dni]));
            if (count($idsDni) === 1) {
                $userIdConfirmado = $idsDni[0];
                $decision = 'vinculo_confirmado';
                $motivoDecision = 'Coincidencia única e inequívoca de DNI institucional.';
                $evidenciaDecision = 'dni_unico_verificado';
                $candidaturas[] = [
                    'user_id_candidato' => $userIdConfirmado,
                    'metodo' => 'dni_exacto_unico',
                    'evidencia_referencia' => 'Coincidencia única por DNI en registro académico',
                    'confianza' => 'alta_exacta',
                    'estado_revision' => 'aprobado_exacto',
                ];
            } else {
                $decision = 'candidato_ambiguo';
                $motivoDecision = 'El DNI figura en más de una cuenta de usuario; requiere depuración humana previa.';
                foreach ($idsDni as $idCand) {
                    $candidaturas[] = [
                        'user_id_candidato' => $idCand,
                        'metodo' => 'dni_duplicado_ambiguo',
                        'evidencia_referencia' => 'DNI compartido por múltiples registros en users',
                        'confianza' => 'baja_ambigua',
                        'estado_revision' => 'pendiente_revision_humana',
                    ];
                }
            }
        }
        // 3. Correo institucional exacto y único
        elseif ($correo && isset($indice['por_correo'][$correo])) {
            $idsCorreo = array_values(array_unique($indice['por_correo'][$correo]));
            if (count($idsCorreo) === 1) {
                $userIdConfirmado = $idsCorreo[0];
                $decision = 'vinculo_confirmado';
                $motivoDecision = 'Coincidencia única e inequívoca de correo registrado en la base institucional.';
                $evidenciaDecision = 'correo_institucional_unico';
                $candidaturas[] = [
                    'user_id_candidato' => $userIdConfirmado,
                    'metodo' => 'correo_institucional_unico',
                    'evidencia_referencia' => 'Correo institucional único en users/alumnos/ppds',
                    'confianza' => 'alta_exacta',
                    'estado_revision' => 'aprobado_exacto',
                ];
            } else {
                $decision = 'candidato_ambiguo';
                $motivoDecision = 'El correo figura en más de una cuenta de usuario; prohibido vincular automáticamente.';
                foreach ($idsCorreo as $idCand) {
                    $candidaturas[] = [
                        'user_id_candidato' => $idCand,
                        'metodo' => 'correo_compartido_ambiguo',
                        'evidencia_referencia' => 'Correo asociado a múltiples cuentas',
                        'confianza' => 'baja_ambigua',
                        'estado_revision' => 'pendiente_revision_humana',
                    ];
                }
            }
        }
        // 4. Candidatos por normalización de nombre: NUNCA autoriza vinculación automática (Regla cardinal / T04)
        elseif ($nombreLiteral !== '') {
            $claveTokens = $this->normalizarNombreTokens($nombreLiteral);
            $idsTokens = isset($indice['por_nombre_tokens'][$claveTokens])
                ? array_values(array_unique($indice['por_nombre_tokens'][$claveTokens]))
                : [];

            if (count($idsTokens) === 1) {
                $decision = 'candidato_revision';
                $motivoDecision = 'Candidato único por tokens de nombre; requiere aprobación humana (prohibido auto-vincular por nombre).';
                $candidaturas[] = [
                    'user_id_candidato' => $idsTokens[0],
                    'metodo' => 'nombre_tokens_revision',
                    'evidencia_referencia' => 'Coincidencia de tokens de nombre (sujeto a revisión humana)',
                    'confianza' => 'media_revision_humana',
                    'estado_revision' => 'pendiente_revision_humana',
                ];
            } elseif (count($idsTokens) > 1) {
                $decision = 'candidato_ambiguo';
                $motivoDecision = 'Existen múltiples usuarios con nombres equivalentes u homónimos; cero vínculos automáticos.';
                foreach ($idsTokens as $idCand) {
                    $candidaturas[] = [
                        'user_id_candidato' => $idCand,
                        'metodo' => 'nombre_parcial_ambiguo',
                        'evidencia_referencia' => 'Homónimo o coincidencia múltiple de nombre',
                        'confianza' => 'baja_ambigua',
                        'estado_revision' => 'pendiente_revision_humana',
                    ];
                }
            } else {
                // Buscar candidatos parciales por coincidencia de al menos 2 tokens si comparte apellido
                $similares = $this->buscarCandidatosSimilaresPorNombre($nombreLiteral, $indice);
                if (count($similares) > 1) {
                    $decision = 'candidato_ambiguo';
                    $motivoDecision = 'Múltiples candidatos con apellidos/nombres parecidos; retenido en cuarentena para revisión humana.';
                    foreach ($similares as $idCand) {
                        $candidaturas[] = [
                            'user_id_candidato' => $idCand,
                            'metodo' => 'nombre_parcial_ambiguo',
                            'evidencia_referencia' => 'Similitud parcial de apellidos/nombres',
                            'confianza' => 'baja_ambigua',
                            'estado_revision' => 'pendiente_revision_humana',
                        ];
                    }
                } elseif (count($similares) === 1) {
                    $decision = 'candidato_revision';
                    $motivoDecision = 'Candidato parcial por apellidos/nombres; retenido para revisión humana.';
                    $candidaturas[] = [
                        'user_id_candidato' => $similares[0],
                        'metodo' => 'nombre_parcial_ambiguo',
                        'evidencia_referencia' => 'Coincidencia parcial de apellidos y primer nombre',
                        'confianza' => 'baja_ambigua',
                        'estado_revision' => 'pendiente_revision_humana',
                    ];
                }
            }
        }

        // Nivel 2: Si la identidad users.id está confirmada, resolver trayectoria FID/PPD por separado (T06, T07)
        $trayectoriaTipo = null;
        $programaId = null;
        $calidadObservacion = 'verificado_en_fuente';
        $tieneMultiplesTrayectorias = false;

        if ($userIdConfirmado !== null) {
            /** @var User $userObj */
            $userObj = $indice['por_id'][$userIdConfirmado];
            $metaGrupo = $this->grupoFormacionService->resolver($userObj);
            $gruposInstitucionales = $metaGrupo['grupos'];
            $tieneMultiplesTrayectorias = count($gruposInstitucionales) > 1;

            $modalidadFuente = $this->inferirModalidadDeclaradaEnFuente($programaTexto);

            if ($tieneMultiplesTrayectorias) {
                // Persona con FID y PPD: solo asignar trayectoria si la fuente la identifica inequívocamente (T06)
                if ($modalidadFuente && in_array($modalidadFuente, $gruposInstitucionales, true)) {
                    $trayectoriaTipo = $modalidadFuente;
                    $programaId = $this->grupoFormacionService->programa($userObj, $modalidadFuente)?->id;
                    $decision = 'vinculo_confirmado';
                } else {
                    $trayectoriaTipo = 'TRANSVERSAL';
                    $programaId = null;
                    $decision = 'identidad_confirmada_trayectoria_pendiente';
                    $motivoDecision = 'Identidad confirmada; la persona posee trayectorias FID y PPD y la observación se vincula a nivel de persona sin forzar un único programa.';
                }
            } elseif (count($gruposInstitucionales) === 1) {
                $unicoGrupo = $gruposInstitucionales[0];
                $nivelInstitucional = strtolower((string) ($this->grupoFormacionService->programa($userObj, $unicoGrupo)?->nivel?->value ?? ''));
                $nivelFuente = $this->inferirNivelDeclaradoEnFuente($programaTexto);

                if ($nivelFuente && $nivelInstitucional && $nivelFuente !== $nivelInstitucional) {
                    $trayectoriaTipo = 'PENDIENTE';
                    $programaId = null;
                    $decision = 'identidad_confirmada_trayectoria_pendiente';
                    $motivoDecision = 'Identidad confirmada, pero el nivel declarado en la fuente difiere del programa institucional registrado.';
                } else {
                    $trayectoriaTipo = $unicoGrupo;
                    $programaId = $this->grupoFormacionService->programa($userObj, $unicoGrupo)?->id;
                    $decision = 'vinculo_confirmado';
                }
            } else {
                $trayectoriaTipo = 'PENDIENTE';
                $programaId = null;
                $decision = 'identidad_confirmada_trayectoria_pendiente';
                $motivoDecision = 'Identidad confirmada; trayectoria académica institucional pendiente de verificar.';
            }

            // Verificar si la fuente declara egreso pero la institución no lo acredita (T07)
            $egresoConfirmadoEnFuente = filter_var($fila['egreso_confirmado'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || !empty($fila['Cohorte_egreso'])
                || !empty($fila['Cohorte_egreso_2026']);
            $esEgresadoEnBd = ($userObj->condicion === 'Egresado')
                || $userObj->trayectoriaEventos->contains(fn ($e) => $e->tipo === 'egreso');

            if ($egresoConfirmadoEnFuente && !$esEgresadoEnBd) {
                $calidadObservacion = 'discrepancia_egreso';
            }
        }

        // Separar texto sensible de bienestar y normalizar observación sin inventar fechas ni episodios (T11, T12, T14)
        $bienestarOriginal = trim((string) ($fila['Bienestar_2026_original'] ?? ''));
        $copiaSinBienestar = $fila;
        unset($copiaSinBienestar['Bienestar_2026_original']);

        $situacionLaboral = $this->normalizarSituacionLaboral(
            $fila['Trabaja_2026'] ?? $fila['Situación_laboral'] ?? null
        );

        $observacionNormalizada = [
            'observada_en' => null, // Fecha exacta de respuesta no consignada en los archivos; prohibido usar fecha de importación
            'fecha_corte' => self::FUENTES_DEFINIDAS[$fuenteCodigo]['fecha_corte_etiqueta'] ?? 'Histórico / 2026',
            'naturaleza' => 'declarado_por_persona',
            'calidad' => $calidadObservacion,
            'situacion_laboral_declarada' => $situacionLaboral,
            'insercion_educativa_declarada' => $this->limpiarCampoTexto($fila['Insercion_educativa_2026'] ?? $fila['Inserción_educativa'] ?? null, 80),
            'sector_declarado' => $this->limpiarCampoTexto($fila['Sector_2026'] ?? $fila['Sector'] ?? null, 100),
            'tipo_practica_declarada' => $this->limpiarCampoTexto($fila['Tipo_practica_2026'] ?? $fila['Modalidad_contrato_archivo'] ?? null, 120),
            'cargo_declarado' => $this->limpiarCampoTexto($fila['Cargo_responsabilidad_2026'] ?? $fila['Cargo_área_actual'] ?? null, 255),
            'lugar_trabajo_texto_origen' => $this->limpiarCampoTexto($fila['Lugar_trabajo_2026'] ?? null, 255),
            'satisfaccion_historica_archivo' => $this->limpiarCampoTexto($fila['Satisfaccion_laboral_archivo'] ?? $fila['Satisfacción_laboral_archivo'] ?? null, 120),
            'aporte_formacion_original' => $this->limpiarCampoTexto($fila['Aporte_formacion_archivo_original'] ?? $fila['Aporte_formación_original'] ?? null, 4000),
            'necesidades_formacion_original' => $this->limpiarCampoTexto($fila['Necesidades_archivo_original'] ?? $fila['Necesidades_original'] ?? null, 4000),
            'sugerencias_original' => $this->limpiarCampoTexto($fila['Sugerencias_archivo_original'] ?? $fila['Sugerencias_original'] ?? null, 4000),
            'tiene_bienestar_restringido' => $bienestarOriginal !== '' && strtolower($bienestarOriginal) !== 'sin dato',
            'atributos_normalizados' => [
                'fuente_codigo' => $fuenteCodigo,
                'fila_origen' => $filaOrigen,
                'cohorte_declarada_fuente' => $fila['Cohorte_egreso'] ?? $fila['Cohorte_egreso_2026'] ?? $fila['egreso'] ?? null,
                'programa_declarado_fuente' => $programaTexto !== '' ? $programaTexto : null,
                'situacion_academica_fuente' => $fila['Situacion_academica'] ?? $fila['Situación_académica'] ?? $fila['condicion'] ?? null,
                'egreso_confirmado_en_padron_historico' => isset($fila['egreso_confirmado'])
                    ? filter_var($fila['egreso_confirmado'], FILTER_VALIDATE_BOOLEAN)
                    : null,
                'periodo_referencia_fuente' => $fila['periodo_referencia'] ?? $fila['periodos_en_listas'] ?? null,
                'modalidad_contrato_historico' => $fila['Modalidad_contrato_archivo'] ?? null,
                'ubicacion_pendiente_ubigeo' => !empty($fila['Lugar_trabajo_2026']),
                'observacion_calidad_fuente' => $fila['Observacion_calidad'] ?? $fila['Observaciones_calidad'] ?? $fila['advertencia'] ?? null,
            ],
        ];

        return [
            'fila_origen' => $filaOrigen,
            'registro_hash' => hash('sha256', json_encode($fila, JSON_UNESCAPED_UNICODE)),
            'payload_sin_bienestar' => $copiaSinBienestar,
            'bienestar_original' => ($bienestarOriginal !== '' && strtolower($bienestarOriginal) !== 'sin dato') ? $bienestarOriginal : null,
            'decision' => $decision,
            'motivo_decision' => $motivoDecision,
            'evidencia_decision' => $evidenciaDecision,
            'user_id_vinculado' => $userIdConfirmado,
            'trayectoria_tipo' => $trayectoriaTipo,
            'programa_id' => $programaId,
            'tiene_multiples_trayectorias' => $tieneMultiplesTrayectorias,
            'candidaturas' => $candidaturas,
            'observacion' => $observacionNormalizada,
        ];
    }

    /**
     * Ejecuta la conciliación completa de las 3 fuentes del paquete (por defecto dry-run con 0 escrituras).
     */
    public function ejecutarPaquete(
        string $sourcePath,
        bool $apply = false,
        bool $confirmIsolated = false,
        ?string $expectedDb = null,
        ?int $actorId = null
    ): array {
        $dbEfectiva = $this->verificarBaseDestinoAislada($apply, $confirmIsolated, $expectedDb);
        $verificacion = $this->verificarPaqueteFuente($sourcePath);
        $rootDir = $verificacion['root_dir'];
        $hashes = $verificacion['hashes'];

        $indice = $this->construirIndiceInstitucional();
        $reporteFuentes = [];

        foreach (self::FUENTES_DEFINIDAS as $codigo => $metaFuente) {
            $relPath = $metaFuente['archivo_relativo'];
            $csvPath = $rootDir . '/' . $relPath;
            $sha256Archivo = $hashes[$relPath] ?? hash_file('sha256', $csvPath);
            $filas = $this->leerCsvAsociativo($csvPath);

            $hashesVistosEnFuente = [];
            $clavesPersonaFuente = [];
            $conteos = [
                'fuente_codigo' => $codigo,
                'archivo' => $relPath,
                'sha256' => $sha256Archivo,
                'filas_leidas' => count($filas),
                'personas_unicas_en_fuente' => 0,
                'duplicados_descartados_en_lote' => 0,
                'vinculo_confirmado' => 0,
                'identidad_confirmada_trayectoria_pendiente' => 0,
                'candidato_revision' => 0,
                'candidato_ambiguo' => 0,
                'sin_correspondencia' => 0,
                'identidades_con_multiples_trayectorias' => 0,
                'asignaciones_trayectoria_confirmadas' => 0,
                'observaciones_importables' => 0,
                'observaciones_bloqueadas_cuarentena' => 0,
                'discrepancias_egreso_fuente_vs_bd' => 0,
            ];

            $resultadosFilas = [];
            foreach ($filas as $idx => $fila) {
                $rawId = trim((string) ($fila['id'] ?? $fila['ID_origen'] ?? ''));
                if ($codigo === 'maestra_historica_2026') {
                    // En base_maestra_254, ID_origen es el número de fila dentro de cada libro de promoción (1..78 repetido por cohorte)
                    $idOrigen = str_starts_with($rawId, 'M-')
                        ? $rawId
                        : sprintf('M-%03d-ORIG-%s', $idx + 1, $rawId !== '' ? $rawId : 'NA');
                } elseif ($rawId !== '') {
                    $idOrigen = $rawId;
                } else {
                    $idOrigen = 'FILA-' . ($idx + 1);
                }
                $hashFila = hash('sha256', json_encode($fila, JSON_UNESCAPED_UNICODE));

                $claveNom = $this->normalizarNombreTokens((string) ($fila['Nombre'] ?? $fila['nombre'] ?? $idOrigen));
                if ($claveNom !== '') {
                    $clavesPersonaFuente[$claveNom] = true;
                }

                if (isset($hashesVistosEnFuente[$hashFila])) {
                    $conteos['duplicados_descartados_en_lote']++;
                    $resultadosFilas[] = [
                        'fila_origen' => $idOrigen,
                        'registro_hash' => $hashFila,
                        'payload_sin_bienestar' => $fila,
                        'bienestar_original' => null,
                        'decision' => 'descartado_duplicado',
                        'motivo_decision' => 'Fila idéntica duplicada dentro del mismo lote de origen.',
                        'evidencia_decision' => 'hash_fila_duplicado',
                        'user_id_vinculado' => null,
                        'trayectoria_tipo' => null,
                        'programa_id' => null,
                        'tiene_multiples_trayectorias' => false,
                        'candidaturas' => [],
                        'observacion' => null,
                    ];
                    continue;
                }
                $hashesVistosEnFuente[$hashFila] = true;

                $conciliado = $this->conciliarFila($fila, $codigo, $idOrigen, $indice);
                $dec = $conciliado['decision'];
                if (isset($conteos[$dec])) {
                    $conteos[$dec]++;
                }
                if ($conciliado['tiene_multiples_trayectorias']) {
                    $conteos['identidades_con_multiples_trayectorias']++;
                }
                if (in_array($conciliado['trayectoria_tipo'], ['FID', 'PPD'], true)) {
                    $conteos['asignaciones_trayectoria_confirmadas']++;
                }
                if ($conciliado['user_id_vinculado'] !== null) {
                    $conteos['observaciones_importables']++;
                } else {
                    $conteos['observaciones_bloqueadas_cuarentena']++;
                }
                if (($conciliado['observacion']['calidad'] ?? '') === 'discrepancia_egreso') {
                    $conteos['discrepancias_egreso_fuente_vs_bd']++;
                }

                $resultadosFilas[] = $conciliado;
            }

            $conteos['personas_unicas_en_fuente'] = count($clavesPersonaFuente);

            if ($apply) {
                $this->persistirLoteConciliado($metaFuente, $sha256Archivo, $dbEfectiva, $actorId, $conteos, $resultadosFilas);
            }

            $reporteFuentes[$codigo] = $conteos;
        }

        return [
            'modo' => $apply ? 'apply_aislado' : 'dry_run',
            'base_datos_efectiva' => $dbEfectiva,
            'entorno' => app()->environment(),
            'hashes_verificados' => count($hashes),
            'fuentes' => $reporteFuentes,
        ];
    }

    /**
     * Persiste de manera transaccional e idempotente el lote de una fuente en la BD de ensayo aislada.
     * Garantiza 0 mutaciones en users, alumnos, ppds, matriculas, notas, trayectoria_eventos y episodios laborales.
     */
    private function persistirLoteConciliado(
        array $metaFuente,
        string $sha256Archivo,
        string $dbEfectiva,
        ?int $actorId,
        array $conteos,
        array $resultadosFilas
    ): void {
        DB::transaction(function () use ($metaFuente, $sha256Archivo, $dbEfectiva, $actorId, $conteos, $resultadosFilas) {
            $ahora = now();

            DB::table('egresado_historico_fuentes')->updateOrInsert(
                ['codigo' => $metaFuente['codigo']],
                [
                    'nombre' => $metaFuente['nombre'],
                    'tipo' => $metaFuente['tipo'],
                    'archivo_relativo' => $metaFuente['archivo_relativo'],
                    'sha256_esperado' => $sha256Archivo,
                    'fecha_corte_conocida' => $metaFuente['fecha_corte_conocida'],
                    'notas_calidad' => $metaFuente['notas_calidad'],
                    'updated_at' => $ahora,
                    'created_at' => $ahora,
                ]
            );

            $fuenteRow = DB::table('egresado_historico_fuentes')->where('codigo', $metaFuente['codigo'])->first();

            DB::table('egresado_historico_lotes')->updateOrInsert(
                [
                    'fuente_id' => $fuenteRow->id,
                    'archivo_hash_sha256' => $sha256Archivo,
                    'base_datos' => $dbEfectiva,
                ],
                [
                    'version' => '2026-11-17',
                    'entorno' => app()->environment(),
                    'estado' => 'procesado',
                    'total_filas' => $conteos['filas_leidas'],
                    'actor_id' => $actorId,
                    'importado_en' => $ahora,
                    'revertido_en' => null,
                    'revertido_por' => null,
                    'motivo_reversion' => null,
                    'resumen_json' => json_encode($conteos, JSON_UNESCAPED_UNICODE),
                    'updated_at' => $ahora,
                    'created_at' => $ahora,
                ]
            );

            $loteRow = DB::table('egresado_historico_lotes')
                ->where('fuente_id', $fuenteRow->id)
                ->where('archivo_hash_sha256', $sha256Archivo)
                ->where('base_datos', $dbEfectiva)
                ->first();

            foreach ($resultadosFilas as $item) {
                $bienestarCifrado = $item['bienestar_original'] !== null
                    ? Crypt::encryptString($item['bienestar_original'])
                    : null;

                DB::table('egresado_historico_registros')->updateOrInsert(
                    [
                        'lote_id' => $loteRow->id,
                        'fila_origen' => $item['fila_origen'],
                    ],
                    [
                        'fuente_codigo' => $metaFuente['codigo'],
                        'registro_hash' => $item['registro_hash'],
                        'payload_json' => json_encode($item['payload_sin_bienestar'], JSON_UNESCAPED_UNICODE),
                        'bienestar_sensible_cifrado' => $bienestarCifrado,
                        'fecha_observacion' => null,
                        'fecha_fuente' => $metaFuente['fecha_corte_etiqueta'],
                        'estado_conciliacion' => $item['decision'],
                        'user_id_vinculado' => $item['user_id_vinculado'],
                        'trayectoria_tipo_vinculada' => $item['trayectoria_tipo'],
                        'programa_id_vinculado' => $item['programa_id'],
                        'updated_at' => $ahora,
                        'created_at' => $ahora,
                    ]
                );

                $regRow = DB::table('egresado_historico_registros')
                    ->where('lote_id', $loteRow->id)
                    ->where('fila_origen', $item['fila_origen'])
                    ->first();

                foreach ($item['candidaturas'] as $cand) {
                    DB::table('egresado_historico_candidaturas')->updateOrInsert(
                        [
                            'registro_id' => $regRow->id,
                            'user_id_candidato' => $cand['user_id_candidato'],
                            'metodo' => $cand['metodo'],
                        ],
                        [
                            'trayectoria_tipo_candidata' => $item['trayectoria_tipo'],
                            'programa_id_candidato' => $item['programa_id'],
                            'evidencia_referencia' => $cand['evidencia_referencia'],
                            'confianza' => $cand['confianza'],
                            'estado_revision' => $cand['estado_revision'],
                            'updated_at' => $ahora,
                            'created_at' => $ahora,
                        ]
                    );
                }

                // Registrar decisión auditable inicial si aún no existe una decisión manual posterior
                $existeDecisionManual = DB::table('egresado_historico_decisiones')
                    ->where('registro_id', $regRow->id)
                    ->whereNotNull('validado_por_user_id')
                    ->exists();

                if (!$existeDecisionManual) {
                    DB::table('egresado_historico_decisiones')->where('registro_id', $regRow->id)->delete();
                    DB::table('egresado_historico_decisiones')->insert([
                        'registro_id' => $regRow->id,
                        'user_id_validado' => $item['user_id_vinculado'],
                        'trayectoria_tipo_validada' => $item['trayectoria_tipo'],
                        'programa_id_validado' => $item['programa_id'],
                        'decision' => $item['decision'],
                        'motivo' => $item['motivo_decision'],
                        'evidencia' => $item['evidencia_decision'],
                        'validado_por_user_id' => $actorId,
                        'validado_en' => $ahora,
                        'version' => 1,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                }

                if ($item['user_id_vinculado'] !== null && $item['observacion'] !== null) {
                    $obs = $item['observacion'];
                    DB::table('egresado_historico_observaciones')->updateOrInsert(
                        ['registro_origen_id' => $regRow->id],
                        [
                            'fuente_id' => $fuenteRow->id,
                            'user_id' => $item['user_id_vinculado'],
                            'trayectoria_tipo' => $item['trayectoria_tipo'],
                            'programa_id' => $item['programa_id'],
                            'observada_en' => $obs['observada_en'],
                            'fecha_corte' => $obs['fecha_corte'],
                            'naturaleza' => $obs['naturaleza'],
                            'calidad' => $obs['calidad'],
                            'situacion_laboral_declarada' => $obs['situacion_laboral_declarada'],
                            'insercion_educativa_declarada' => $obs['insercion_educativa_declarada'],
                            'sector_declarado' => $obs['sector_declarado'],
                            'tipo_practica_declarada' => $obs['tipo_practica_declarada'],
                            'cargo_declarado' => $obs['cargo_declarado'],
                            'lugar_trabajo_texto_origen' => $obs['lugar_trabajo_texto_origen'],
                            'satisfaccion_historica_archivo' => $obs['satisfaccion_historica_archivo'],
                            'aporte_formacion_original' => $obs['aporte_formacion_original'],
                            'necesidades_formacion_original' => $obs['necesidades_formacion_original'],
                            'sugerencias_original' => $obs['sugerencias_original'],
                            'tiene_bienestar_restringido' => $obs['tiene_bienestar_restringido'],
                            'atributos_normalizados' => json_encode($obs['atributos_normalizados'], JSON_UNESCAPED_UNICODE),
                            'version' => 1,
                            'updated_at' => $ahora,
                            'created_at' => $ahora,
                        ]
                    );
                }
            }
        });
    }

    /**
     * Aprueba o rechaza manualmente un registro en cuarentena con trazabilidad de revisor, fecha y versión.
     */
    public function resolverConciliacionManual(
        int $registroId,
        string $accion,
        ?int $userIdValidado,
        ?string $trayectoriaTipo,
        int $revisorUserId,
        string $motivo
    ): array {
        return DB::transaction(function () use ($registroId, $accion, $userIdValidado, $trayectoriaTipo, $revisorUserId, $motivo) {
            $reg = DB::table('egresado_historico_registros')->lockForUpdate()->find($registroId);
            if (!$reg) {
                throw new \RuntimeException('Registro histórico de staging no encontrado.');
            }

            $ultimaVersion = (int) DB::table('egresado_historico_decisiones')
                ->where('registro_id', $registroId)
                ->max('version');
            $nuevaVersion = $ultimaVersion + 1;
            $ahora = now();

            if ($accion === 'rechazar') {
                DB::table('egresado_historico_registros')->where('id', $registroId)->update([
                    'estado_conciliacion' => 'sin_correspondencia',
                    'user_id_vinculado' => null,
                    'trayectoria_tipo_vinculada' => null,
                    'programa_id_vinculado' => null,
                    'updated_at' => $ahora,
                ]);
                DB::table('egresado_historico_observaciones')->where('registro_origen_id', $registroId)->delete();
                DB::table('egresado_historico_candidaturas')->where('registro_id', $registroId)->update([
                    'estado_revision' => 'rechazado_humano',
                    'updated_at' => $ahora,
                ]);
                DB::table('egresado_historico_decisiones')->insert([
                    'registro_id' => $registroId,
                    'user_id_validado' => null,
                    'trayectoria_tipo_validada' => null,
                    'programa_id_validado' => null,
                    'decision' => 'sin_correspondencia',
                    'motivo' => $motivo,
                    'evidencia' => 'revision_humana_rechazada',
                    'validado_por_user_id' => $revisorUserId,
                    'validado_en' => $ahora,
                    'version' => $nuevaVersion,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);

                return ['estado' => 'sin_correspondencia', 'version' => $nuevaVersion];
            }

            $userObj = User::with(GrupoFormacionService::RELACIONES)->findOrFail($userIdValidado);
            $grupos = $this->grupoFormacionService->resolver($userObj)['grupos'];
            $trayectoriaFinal = $trayectoriaTipo ?: (count($grupos) === 1 ? $grupos[0] : 'TRANSVERSAL');
            $programaId = in_array($trayectoriaFinal, ['FID', 'PPD'], true)
                ? $this->grupoFormacionService->programa($userObj, $trayectoriaFinal)?->id
                : null;
            $decisionEstado = in_array($trayectoriaFinal, ['FID', 'PPD'], true)
                ? 'vinculo_confirmado'
                : 'identidad_confirmada_trayectoria_pendiente';

            DB::table('egresado_historico_registros')->where('id', $registroId)->update([
                'estado_conciliacion' => $decisionEstado,
                'user_id_vinculado' => $userObj->id,
                'trayectoria_tipo_vinculada' => $trayectoriaFinal,
                'programa_id_vinculado' => $programaId,
                'updated_at' => $ahora,
            ]);

            DB::table('egresado_historico_candidaturas')
                ->where('registro_id', $registroId)
                ->where('user_id_candidato', $userObj->id)
                ->update(['estado_revision' => 'aprobado_humano', 'updated_at' => $ahora]);

            DB::table('egresado_historico_decisiones')->insert([
                'registro_id' => $registroId,
                'user_id_validado' => $userObj->id,
                'trayectoria_tipo_validada' => $trayectoriaFinal,
                'programa_id_validado' => $programaId,
                'decision' => $decisionEstado,
                'motivo' => $motivo,
                'evidencia' => 'aprobacion_humana_autorizada',
                'validado_por_user_id' => $revisorUserId,
                'validado_en' => $ahora,
                'version' => $nuevaVersion,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            $lote = DB::table('egresado_historico_lotes')->find($reg->lote_id);
            $payload = json_decode($reg->payload_json, true) ?: [];
            $situacionLaboral = $this->normalizarSituacionLaboral(
                $payload['Trabaja_2026'] ?? $payload['Situación_laboral'] ?? null
            );

            DB::table('egresado_historico_observaciones')->updateOrInsert(
                ['registro_origen_id' => $reg->id],
                [
                    'fuente_id' => $lote->fuente_id,
                    'user_id' => $userObj->id,
                    'trayectoria_tipo' => $trayectoriaFinal,
                    'programa_id' => $programaId,
                    'observada_en' => $reg->fecha_observacion,
                    'fecha_corte' => $reg->fecha_fuente,
                    'naturaleza' => 'declarado_por_persona',
                    'calidad' => 'verificado_en_fuente',
                    'situacion_laboral_declarada' => $situacionLaboral,
                    'insercion_educativa_declarada' => $this->limpiarCampoTexto($payload['Insercion_educativa_2026'] ?? $payload['Inserción_educativa'] ?? null, 80),
                    'sector_declarado' => $this->limpiarCampoTexto($payload['Sector_2026'] ?? $payload['Sector'] ?? null, 100),
                    'tipo_practica_declarada' => $this->limpiarCampoTexto($payload['Tipo_practica_2026'] ?? $payload['Modalidad_contrato_archivo'] ?? null, 120),
                    'cargo_declarado' => $this->limpiarCampoTexto($payload['Cargo_responsabilidad_2026'] ?? $payload['Cargo_área_actual'] ?? null, 255),
                    'lugar_trabajo_texto_origen' => $this->limpiarCampoTexto($payload['Lugar_trabajo_2026'] ?? null, 255),
                    'satisfaccion_historica_archivo' => $this->limpiarCampoTexto($payload['Satisfaccion_laboral_archivo'] ?? $payload['Satisfacción_laboral_archivo'] ?? null, 120),
                    'aporte_formacion_original' => $this->limpiarCampoTexto($payload['Aporte_formacion_archivo_original'] ?? $payload['Aporte_formación_original'] ?? null, 4000),
                    'necesidades_formacion_original' => $this->limpiarCampoTexto($payload['Necesidades_archivo_original'] ?? $payload['Necesidades_original'] ?? null, 4000),
                    'sugerencias_original' => $this->limpiarCampoTexto($payload['Sugerencias_archivo_original'] ?? $payload['Sugerencias_original'] ?? null, 4000),
                    'tiene_bienestar_restringido' => !empty($reg->bienestar_sensible_cifrado),
                    'atributos_normalizados' => json_encode([
                        'fuente_codigo' => $reg->fuente_codigo,
                        'fila_origen' => $reg->fila_origen,
                        'conciliado_manualmente_por' => $revisorUserId,
                    ], JSON_UNESCAPED_UNICODE),
                    'version' => $nuevaVersion,
                    'updated_at' => $ahora,
                    'created_at' => $ahora,
                ]
            );

            return ['estado' => $decisionEstado, 'version' => $nuevaVersion];
        });
    }

    /**
     * Reversión controlada de un lote de ensayo sin tocar jamás tablas académicas ni de usuarios (T17).
     */
    public function revertirLoteEnsayo(int $loteId, int $actorId, string $motivo): array
    {
        $this->verificarBaseDestinoAislada(true, true);

        return DB::transaction(function () use ($loteId, $actorId, $motivo) {
            $lote = DB::table('egresado_historico_lotes')->lockForUpdate()->find($loteId);
            if (!$lote) {
                throw new \RuntimeException("No existe el lote histórico #{$loteId}.");
            }

            $registroIds = DB::table('egresado_historico_registros')
                ->where('lote_id', $loteId)
                ->pluck('id');

            $obsEliminadas = DB::table('egresado_historico_observaciones')
                ->whereIn('registro_origen_id', $registroIds)
                ->delete();

            DB::table('egresado_historico_candidaturas')
                ->whereIn('registro_id', $registroIds)
                ->delete();

            DB::table('egresado_historico_decisiones')
                ->whereIn('registro_id', $registroIds)
                ->delete();

            $regsEliminados = DB::table('egresado_historico_registros')
                ->where('lote_id', $loteId)
                ->delete();

            DB::table('egresado_historico_lotes')->where('id', $loteId)->update([
                'estado' => 'revertido',
                'revertido_en' => now(),
                'revertido_por' => $actorId,
                'motivo_reversion' => $motivo,
                'updated_at' => now(),
            ]);

            return [
                'lote_id' => $loteId,
                'estado' => 'revertido',
                'registros_revertidos' => $regsEliminados,
                'observaciones_revertidas' => $obsEliminadas,
            ];
        });
    }

    /**
     * Recupera los Antecedentes de seguimiento históricos de una persona respetando la privacidad de bienestar (T14, T15).
     */
    public function obtenerAntecedentesPersona(int $userId, ?User $consultante = null): array
    {
        if (!Schema::hasTable('egresado_historico_observaciones')) {
            return [];
        }

        $consultante = $consultante ?: auth()->user();
        $puedeVerBienestar = $consultante && $this->autorizacionService->puedeVerNotasConfidencialesBienestar($consultante);

        $filas = DB::table('egresado_historico_observaciones as o')
            ->join('egresado_historico_fuentes as f', 'f.id', '=', 'o.fuente_id')
            ->join('egresado_historico_registros as r', 'r.id', '=', 'o.registro_origen_id')
            ->where('o.user_id', $userId)
            ->orderByDesc('o.id')
            ->get([
                'o.*',
                'f.codigo as fuente_codigo',
                'f.nombre as fuente_nombre',
                'r.fila_origen',
                'r.bienestar_sensible_cifrado',
            ]);

        return $filas->map(function ($fila) use ($puedeVerBienestar) {
            $textoBienestar = null;
            if ($fila->tiene_bienestar_restringido) {
                if ($puedeVerBienestar && !empty($fila->bienestar_sensible_cifrado)) {
                    try {
                        $textoBienestar = Crypt::decryptString($fila->bienestar_sensible_cifrado);
                    } catch (\Throwable $e) {
                        $textoBienestar = '[Error al descifrar nota reservada]';
                    }
                } else {
                    $textoBienestar = '[Información reservada de bienestar institucional]';
                }
            }

            return [
                'id' => $fila->id,
                'fuente_codigo' => $fila->fuente_codigo,
                'fuente_nombre' => $fila->fuente_nombre,
                'fila_origen' => $fila->fila_origen,
                'trayectoria_tipo' => $fila->trayectoria_tipo ?? 'TRANSVERSAL',
                'observada_en' => $fila->observada_en ?: 'Fecha exacta desconocida',
                'fecha_corte' => $fila->fecha_corte ?: 'Sin fecha de corte',
                'naturaleza' => $fila->naturaleza,
                'calidad' => $fila->calidad,
                'situacion_laboral_declarada' => $fila->situacion_laboral_declarada ?? 'Sin dato',
                'insercion_educativa_declarada' => $fila->insercion_educativa_declarada,
                'sector_declarado' => $fila->sector_declarado,
                'tipo_practica_declarada' => $fila->tipo_practica_declarada,
                'cargo_declarado' => $fila->cargo_declarado,
                'lugar_trabajo_texto_origen' => $fila->lugar_trabajo_texto_origen,
                'satisfaccion_historica_archivo' => $fila->satisfaccion_historica_archivo,
                'aporte_formacion_original' => $fila->aporte_formacion_original,
                'necesidades_formacion_original' => $fila->necesidades_formacion_original,
                'sugerencias_original' => $fila->sugerencias_original,
                'tiene_bienestar_restringido' => (bool) $fila->tiene_bienestar_restringido,
                'bienestar_restringido' => $textoBienestar,
                'atributos_normalizados' => json_decode($fila->atributos_normalizados ?? '{}', true),
            ];
        })->all();
    }

    public function normalizarSituacionLaboral(?string $valor): string
    {
        $v = strtolower(trim((string) $valor));
        if ($v === '' || $v === 'sin dato' || $v === 'no responde' || $v === 'n/a') {
            return 'Sin dato';
        }
        if (str_starts_with($v, 'no trabaja') || $v === 'no') {
            return 'No trabaja';
        }
        if (str_starts_with($v, 'trabaja') || $v === 'si' || $v === 'sí') {
            return 'Trabaja';
        }
        return 'Sin dato';
    }

    private function inferirModalidadDeclaradaEnFuente(string $programaTexto): ?string
    {
        $p = strtoupper(trim($programaTexto));
        if ($p === '' || $p === 'POR VERIFICAR' || str_contains($p, 'BACH')) {
            return null;
        }
        if (str_contains($p, 'PPD') || str_contains($p, 'PROFESIONALIZAC')) {
            return 'PPD';
        }
        if (str_contains($p, 'FID')) {
            return 'FID';
        }
        return null;
    }

    private function inferirNivelDeclaradoEnFuente(string $programaTexto): ?string
    {
        $p = strtolower(trim($programaTexto));
        if ($p === '' || $p === 'por verificar' || str_contains($p, 'bach')) {
            return null;
        }
        if (str_contains($p, 'inicial')) {
            return 'inicial';
        }
        if (str_contains($p, 'primaria')) {
            return 'primaria';
        }
        return null;
    }

    private function buscarCandidatosSimilaresPorNombre(string $nombreLiteral, array $indice): array
    {
        $tokensFuente = explode(' ', $this->normalizarNombreTokens($nombreLiteral));
        if (count($tokensFuente) < 2) {
            return [];
        }

        $candidatos = [];
        foreach ($indice['por_id'] as $uId => $u) {
            $tokensUser = explode(' ', $this->normalizarNombreTokens($u->name . ' ' . $u->apellidos));
            $comunes = array_intersect($tokensFuente, $tokensUser);
            $tokensApe = array_filter(explode(' ', $this->normalizarTexto($u->apellidos ?? '')));
            $comparteApellido = !empty($tokensApe) && count(array_intersect($tokensApe, $tokensFuente)) >= 1;

            if ($comparteApellido && count($comunes) >= 2) {
                $candidatos[] = $uId;
            }
        }

        return array_values(array_unique($candidatos));
    }

    private function normalizarDni(?string $dni): ?string
    {
        $d = preg_replace('/[^0-9]/', '', (string) $dni);
        return strlen($d) === 8 ? $d : null;
    }

    private function normalizarCorreo(?string $correo): ?string
    {
        $c = strtolower(trim((string) $correo));
        return filter_var($c, FILTER_VALIDATE_EMAIL) ? $c : null;
    }

    private function normalizarTexto(string $texto): string
    {
        $s = mb_strtolower(trim($texto), 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
        $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
        return preg_replace('/\s+/', ' ', trim($s));
    }

    private function normalizarNombreTokens(string $nombre): string
    {
        $limpio = $this->normalizarTexto($nombre);
        if ($limpio === '') {
            return '';
        }
        $tokens = array_values(array_filter(explode(' ', $limpio)));
        sort($tokens);
        return implode(' ', $tokens);
    }

    private function limpiarCampoTexto($valor, int $maxLen): ?string
    {
        $v = trim((string) $valor);
        if ($v === '' || strtolower($v) === 'sin dato' || $v === '-') {
            return null;
        }
        return mb_substr($v, 0, $maxLen);
    }

    private function leerCsvAsociativo(string $ruta): array
    {
        $fp = fopen($ruta, 'r');
        if (!$fp) {
            throw new \RuntimeException("No se pudo abrir el archivo CSV: {$ruta}");
        }
        $cabeceras = fgetcsv($fp);
        if (!$cabeceras) {
            fclose($fp);
            return [];
        }
        $cabeceras[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $cabeceras[0]);
        $cabeceras = array_map('trim', $cabeceras);

        $filas = [];
        while (($fila = fgetcsv($fp)) !== false) {
            if (count($fila) === count($cabeceras)) {
                $filas[] = array_combine($cabeceras, $fila);
            }
        }
        fclose($fp);
        return $filas;
    }

    /**
     * Catálogo oficial de localidades y distritos (INEI / MINEDU) para el Visor Territorial.
     */
    public function asegurarCatalogoLugaresTerritoriales(): array
    {
        $catalogo = [
            // Cusco - Cusco
            ['clave' => 'cusco', 'nombre' => 'Cusco', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Cusco', 'distrito' => 'Cusco', 'ubigeo' => '080101', 'latitud' => -13.5167, 'longitud' => -71.9788],
            ['clave' => 'san_sebastian', 'nombre' => 'San Sebastián', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Cusco', 'distrito' => 'San Sebastián', 'ubigeo' => '080105', 'latitud' => -13.5319, 'longitud' => -71.9369],
            ['clave' => 'san_jeronimo', 'nombre' => 'San Jerónimo', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Cusco', 'distrito' => 'San Jerónimo', 'ubigeo' => '080104', 'latitud' => -13.5447, 'longitud' => -71.8836],
            ['clave' => 'santiago', 'nombre' => 'Santiago', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Cusco', 'distrito' => 'Santiago', 'ubigeo' => '080106', 'latitud' => -13.5292, 'longitud' => -71.9869],
            ['clave' => 'wanchaq', 'nombre' => 'Wanchaq', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Cusco', 'distrito' => 'Wanchaq', 'ubigeo' => '080108', 'latitud' => -13.5256, 'longitud' => -71.9647],
            ['clave' => 'saylla', 'nombre' => 'Saylla', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Cusco', 'distrito' => 'Saylla', 'ubigeo' => '080107', 'latitud' => -13.5681, 'longitud' => -71.8294],
            ['clave' => 'poroy', 'nombre' => 'Poroy', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Cusco', 'distrito' => 'Poroy', 'ubigeo' => '080103', 'latitud' => -13.4936, 'longitud' => -72.0439],
            // Cusco - Anta
            ['clave' => 'anta', 'nombre' => 'Anta', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Anta', 'distrito' => 'Anta', 'ubigeo' => '080301', 'latitud' => -13.4614, 'longitud' => -72.1483],
            ['clave' => 'izcuchaca', 'nombre' => 'Izcuchaca (Anta)', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Anta', 'distrito' => 'Anta', 'ubigeo' => '080301', 'latitud' => -13.4586, 'longitud' => -72.1442],
            ['clave' => 'cachimayo', 'nombre' => 'Cachimayo', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Anta', 'distrito' => 'Cachimayo', 'ubigeo' => '080303', 'latitud' => -13.4750, 'longitud' => -72.0631],
            // Cusco - Urubamba
            ['clave' => 'urubamba', 'nombre' => 'Urubamba', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Urubamba', 'distrito' => 'Urubamba', 'ubigeo' => '081301', 'latitud' => -13.3047, 'longitud' => -72.1158],
            ['clave' => 'chinchero', 'nombre' => 'Chinchero', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Urubamba', 'distrito' => 'Chinchero', 'ubigeo' => '081302', 'latitud' => -13.3922, 'longitud' => -72.0481],
            ['clave' => 'ollantaytambo', 'nombre' => 'Ollantaytambo', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Urubamba', 'distrito' => 'Ollantaytambo', 'ubigeo' => '081306', 'latitud' => -13.2583, 'longitud' => -72.2633],
            ['clave' => 'yucay', 'nombre' => 'Yucay', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Urubamba', 'distrito' => 'Yucay', 'ubigeo' => '081307', 'latitud' => -13.3183, 'longitud' => -72.0889],
            // Cusco - Calca
            ['clave' => 'calca', 'nombre' => 'Calca', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Calca', 'distrito' => 'Calca', 'ubigeo' => '080401', 'latitud' => -13.3208, 'longitud' => -71.9547],
            ['clave' => 'pisac', 'nombre' => 'Pisac', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Calca', 'distrito' => 'Pisac', 'ubigeo' => '080405', 'latitud' => -13.4225, 'longitud' => -71.8467],
            ['clave' => 'taray', 'nombre' => 'Taray', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Calca', 'distrito' => 'Taray', 'ubigeo' => '080407', 'latitud' => -13.4092, 'longitud' => -71.8744],
            ['clave' => 'coya', 'nombre' => 'Coya', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Calca', 'distrito' => 'Coya', 'ubigeo' => '080402', 'latitud' => -13.3867, 'longitud' => -71.9022],
            // Cusco - Quispicanchi
            ['clave' => 'urcos', 'nombre' => 'Urcos', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Quispicanchi', 'distrito' => 'Urcos', 'ubigeo' => '081201', 'latitud' => -13.6869, 'longitud' => -71.6228],
            ['clave' => 'andahuaylillas', 'nombre' => 'Andahuaylillas', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Quispicanchi', 'distrito' => 'Andahuaylillas', 'ubigeo' => '081202', 'latitud' => -13.6731, 'longitud' => -71.6775],
            ['clave' => 'lucre', 'nombre' => 'Lucre', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Quispicanchi', 'distrito' => 'Lucre', 'ubigeo' => '081208', 'latitud' => -13.6308, 'longitud' => -71.7375],
            ['clave' => 'ocongate', 'nombre' => 'Ocongate', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Quispicanchi', 'distrito' => 'Ocongate', 'ubigeo' => '081210', 'latitud' => -13.6264, 'longitud' => -71.3853],
            ['clave' => 'oropesa', 'nombre' => 'Oropesa', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Quispicanchi', 'distrito' => 'Oropesa', 'ubigeo' => '081211', 'latitud' => -13.5936, 'longitud' => -71.7589],
            ['clave' => 'quiquijana', 'nombre' => 'Quiquijana', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Quispicanchi', 'distrito' => 'Quiquijana', 'ubigeo' => '081212', 'latitud' => -13.8228, 'longitud' => -71.5403],
            // Cusco - Canchis
            ['clave' => 'sicuani', 'nombre' => 'Sicuani', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Canchis', 'distrito' => 'Sicuani', 'ubigeo' => '080601', 'latitud' => -14.2694, 'longitud' => -71.2261],
            ['clave' => 'tinta', 'nombre' => 'Tinta', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Canchis', 'distrito' => 'Tinta', 'ubigeo' => '080608', 'latitud' => -14.1450, 'longitud' => -71.4069],
            // Cusco - Canas
            ['clave' => 'layo', 'nombre' => 'Layo (Canas)', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Canas', 'distrito' => 'Layo', 'ubigeo' => '080505', 'latitud' => -14.4936, 'longitud' => -71.1550],
            // Puno
            ['clave' => 'pucara_puno', 'nombre' => 'Pucará (Lampa, Puno)', 'tipo' => 'localidad', 'departamento' => 'Puno', 'provincia' => 'Lampa', 'distrito' => 'Pucará', 'ubigeo' => '210707', 'latitud' => -15.0419, 'longitud' => -70.3678],
            ['clave' => 'nunoa_puno', 'nombre' => 'Nuñoa (Melgar, Puno)', 'tipo' => 'localidad', 'departamento' => 'Puno', 'provincia' => 'Melgar', 'distrito' => 'Nuñoa', 'ubigeo' => '210806', 'latitud' => -14.4783, 'longitud' => -70.6358],
            // Cusco - Acomayo
            ['clave' => 'acomayo', 'nombre' => 'Acomayo', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Acomayo', 'distrito' => 'Acomayo', 'ubigeo' => '080201', 'latitud' => -13.9189, 'longitud' => -71.6836],
            ['clave' => 'acopia', 'nombre' => 'Acopia', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Acomayo', 'distrito' => 'Acopia', 'ubigeo' => '080202', 'latitud' => -14.0586, 'longitud' => -71.4919],
            ['clave' => 'pomacanchi', 'nombre' => 'Pomacanchi', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Acomayo', 'distrito' => 'Pomacanchi', 'ubigeo' => '080205', 'latitud' => -14.0319, 'longitud' => -71.5694],
            // Cusco - Paruro
            ['clave' => 'paruro', 'nombre' => 'Paruro', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Paruro', 'distrito' => 'Paruro', 'ubigeo' => '081001', 'latitud' => -13.7631, 'longitud' => -71.8475],
            ['clave' => 'paccaritambo', 'nombre' => 'Paccaritambo', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Paruro', 'distrito' => 'Paccaritambo', 'ubigeo' => '081006', 'latitud' => -13.7583, 'longitud' => -71.9461],
            ['clave' => 'yaurisque', 'nombre' => 'Yaurisque', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Paruro', 'distrito' => 'Yaurisque', 'ubigeo' => '081009', 'latitud' => -13.6639, 'longitud' => -71.9169],
            ['clave' => 'huanoquite', 'nombre' => 'Huanoquite', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Paruro', 'distrito' => 'Huanoquite', 'ubigeo' => '081004', 'latitud' => -13.6797, 'longitud' => -72.0303],
            // Cusco - Paucartambo
            ['clave' => 'paucartambo', 'nombre' => 'Paucartambo', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Paucartambo', 'distrito' => 'Paucartambo', 'ubigeo' => '081101', 'latitud' => -13.3156, 'longitud' => -71.5947],
            ['clave' => 'challabamba', 'nombre' => 'Challabamba', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Paucartambo', 'distrito' => 'Challabamba', 'ubigeo' => '081103', 'latitud' => -13.2189, 'longitud' => -71.6531],
            // Cusco - Canas
            ['clave' => 'tupac_amaru', 'nombre' => 'Túpac Amaru (Canas)', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Canas', 'distrito' => 'Túpac Amaru', 'ubigeo' => '080508', 'latitud' => -14.1722, 'longitud' => -71.4728],
            // Cusco - Chumbivilcas
            ['clave' => 'chumbivilcas', 'nombre' => 'Santo Tomás (Chumbivilcas)', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Chumbivilcas', 'distrito' => 'Santo Tomás', 'ubigeo' => '080701', 'latitud' => -14.4481, 'longitud' => -72.0836],
            ['clave' => 'capacmarca', 'nombre' => 'Capacmarca', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Chumbivilcas', 'distrito' => 'Capacmarca', 'ubigeo' => '080702', 'latitud' => -14.0089, 'longitud' => -72.0083],
            ['clave' => 'velille', 'nombre' => 'Velille', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'Chumbivilcas', 'distrito' => 'Velille', 'ubigeo' => '080708', 'latitud' => -14.5125, 'longitud' => -71.8906],
            // Cusco - La Convención
            ['clave' => 'quillabamba', 'nombre' => 'Quillabamba (Santa Ana)', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'La Convención', 'distrito' => 'Santa Ana', 'ubigeo' => '080901', 'latitud' => -12.8633, 'longitud' => -72.6933],
            ['clave' => 'quellouno', 'nombre' => 'Quellouno', 'tipo' => 'localidad', 'departamento' => 'Cusco', 'provincia' => 'La Convención', 'distrito' => 'Quellouno', 'ubigeo' => '080906', 'latitud' => -12.6369, 'longitud' => -72.5567],
            // Apurímac - Cotabambas
            ['clave' => 'tambobamba', 'nombre' => 'Tambobamba (Cotabambas)', 'tipo' => 'localidad', 'departamento' => 'Apurímac', 'provincia' => 'Cotabambas', 'distrito' => 'Tambobamba', 'ubigeo' => '030501', 'latitud' => -13.9458, 'longitud' => -72.1742],
            ['clave' => 'chalhuahuacho', 'nombre' => 'Chalhuahuacho', 'tipo' => 'localidad', 'departamento' => 'Apurímac', 'provincia' => 'Cotabambas', 'distrito' => 'Chalhuahuacho', 'ubigeo' => '030504', 'latitud' => -14.1172, 'longitud' => -72.2483],
            ['clave' => 'mara', 'nombre' => 'Mara (Cotabambas)', 'tipo' => 'localidad', 'departamento' => 'Apurímac', 'provincia' => 'Cotabambas', 'distrito' => 'Mara', 'ubigeo' => '030505', 'latitud' => -14.0844, 'longitud' => -72.1061],
            // Madre de Dios
            ['clave' => 'puerto_maldonado', 'nombre' => 'Puerto Maldonado', 'tipo' => 'localidad', 'departamento' => 'Madre de Dios', 'provincia' => 'Tambopata', 'distrito' => 'Tambopata', 'ubigeo' => '170101', 'latitud' => -12.5933, 'longitud' => -69.1892],
            ['clave' => 'boca_colorado', 'nombre' => 'Boca Colorado (Madre de Dios)', 'tipo' => 'localidad', 'departamento' => 'Madre de Dios', 'provincia' => 'Manu', 'distrito' => 'Madre de Dios', 'ubigeo' => '170203', 'latitud' => -12.6186, 'longitud' => -70.3867],
            // Arequipa
            ['clave' => 'cerro_colorado', 'nombre' => 'Cerro Colorado (Arequipa)', 'tipo' => 'localidad', 'departamento' => 'Arequipa', 'provincia' => 'Arequipa', 'distrito' => 'Cerro Colorado', 'ubigeo' => '040104', 'latitud' => -16.3767, 'longitud' => -71.5614],
            // Loreto
            ['clave' => 'ramon_castilla', 'nombre' => 'Ramón Castilla (Loreto)', 'tipo' => 'localidad', 'departamento' => 'Loreto', 'provincia' => 'Mariscal Ramón Castilla', 'distrito' => 'Ramón Castilla', 'ubigeo' => '160401', 'latitud' => -4.2219, 'longitud' => -69.9606],
        ];

        $mapa = [];
        foreach ($catalogo as $item) {
            $clave = $item['clave'];
            unset($item['clave']);
            $codigoLugar = 'LUG-' . strtoupper($clave);
            DB::table('seguimiento_lugares')->updateOrInsert(
                ['codigo' => $codigoLugar],
                $item + ['fuente' => 'INEI / MINEDU Padrón Territorial', 'fecha_fuente' => '2026-01-01', 'updated_at' => now(), 'created_at' => now()]
            );
            $row = DB::table('seguimiento_lugares')->where('codigo', $codigoLugar)->first();
            $mapa[$clave] = (array) $row;
        }

        return $mapa;
    }

    /**
     * Interpreta un texto libre de lugar de trabajo (Encuesta 2026-I) y lo vincula al catálogo oficial si corresponde.
     */
    public function resolverLugarDesdeTexto(?string $textoLibre, array $catalogoLugares): array
    {
        $raw = trim((string) $textoLibre);
        if ($raw === '' || strtolower($raw) === 'sin dato' || strtolower($raw) === 'ninguna') {
            return [
                'departamento' => null,
                'provincia' => null,
                'distrito' => null,
                'localidad' => null,
                'comunidad_tipo' => 'no_se',
                'comunidad_nombre' => null,
                'lugar_id' => null,
                'latitud' => null,
                'longitud' => null,
                'precision' => 'sin_coordenadas',
                'fuente' => 'declaracion_egresado',
            ];
        }

        $t = $this->normalizarTexto($raw);
        $comunidadTipo = 'no_se';
        $comunidadNombre = null;

        if (str_contains($t, 'chacamachay')) {
            $comunidadTipo = 'campesina';
            $comunidadNombre = 'Comunidad Campesina de Chacamachay';
        } elseif (str_contains($t, 'urinccoscco') || str_contains($t, 'callatiac')) {
            $comunidadTipo = 'campesina';
            $comunidadNombre = 'Comunidades Indígenas de Urinccoscco y Callatiac';
        } elseif (str_contains($t, 'comunidad campesina')) {
            $comunidadTipo = 'campesina';
            $comunidadNombre = $raw;
        } elseif (str_contains($t, 'comunidad nativa') || str_contains($t, 'comunidades indigenas')) {
            $comunidadTipo = 'nativa';
            $comunidadNombre = $raw;
        }

        // Reglas de correspondencia del texto libre hacia la localidad oficial del catálogo
        $reglas = [
            'chalhuahuacho' => 'chalhuahuacho',
            'challhuahuacho' => 'chalhuahuacho',
            'tambobamba' => 'tambobamba',
            'chacamachay' => 'mara',
            'mara cotabambas' => 'mara',
            'cotabambas' => 'tambobamba',
            'apurimac' => 'tambobamba',
            'apurimaq' => 'tambobamba',
            'boca colorado' => 'boca_colorado',
            'puerto maldonado' => 'puerto_maldonado',
            'madre de dios' => 'puerto_maldonado',
            'cerro colorado' => 'cerro_colorado',
            'arequipa' => 'cerro_colorado',
            'ramon castilla' => 'ramon_castilla',
            'loreto' => 'ramon_castilla',
            'quellouno' => 'quellouno',
            'quillabamba' => 'quillabamba',
            'la convencion' => 'quillabamba',
            'la convencio' => 'quellouno',
            'capacmarca' => 'capacmarca',
            'velille' => 'velille',
            'chumbivilcas' => 'chumbivilcas',
            'tupac amaru' => 'tupac_amaru',
            'canas' => 'tupac_amaru',
            'challabamba' => 'challabamba',
            'paucartambo' => 'paucartambo',
            'paccarictambo' => 'paccaritambo',
            'paccaritambo' => 'paccaritambo',
            'yaurisque' => 'yaurisque',
            'huanoquite' => 'huanoquite',
            'paruro' => 'paruro',
            'acopia' => 'acopia',
            'pomacanchi' => 'pomacanchi',
            'acomayo' => 'acomayo',
            'ocongate' => 'ocongate',
            'andahuaylillas' => 'andahuaylillas',
            'lucre' => 'lucre',
            'oropesa' => 'oropesa',
            'quiquijana' => 'quiquijana',
            'urcos' => 'urcos',
            'sicuani' => 'sicuani',
            'tinta' => 'tinta',
            'layo' => 'layo',
            'nunoa' => 'nunoa_puno',
            'pucara' => 'pucara_puno',
            'pisaq' => 'pisac',
            'pisac' => 'pisac',
            'taray' => 'taray',
            'coya' => 'coya',
            'calca' => 'calca',
            'chinchero' => 'chinchero',
            'ollantaytambo' => 'ollantaytambo',
            'yucay' => 'yucay',
            'urubamba' => 'urubamba',
            'cachimayo' => 'cachimayo',
            'izcuchaca' => 'izcuchaca',
            'anta' => 'anta',
            'poroy' => 'poroy',
            'saylla' => 'saylla',
            'angostura' => 'saylla',
            'wanchaq' => 'wanchaq',
            'hilario mendivil' => 'san_sebastian',
            'aeropuerto' => 'san_sebastian',
            'kari grande' => 'san_sebastian',
            'santa maria' => 'san_sebastian',
            'la planicie' => 'san_sebastian',
            'alto qosqo' => 'san_sebastian',
            'alto cusco' => 'san_sebastian',
            'santiago' => 'santiago',
            'huancaro' => 'santiago',
            'puquin' => 'santiago',
            'independencia' => 'santiago',
            'san jeronimo' => 'san_jeronimo',
            'san sebastian' => 'san_sebastian',
            'enaco' => 'san_sebastian',
            'tikapata' => 'san_sebastian',
            'teneria' => 'san_sebastian',
            'san blas' => 'cusco',
            'ticatica' => 'cusco',
            'arco ticatica' => 'cusco',
            'arcoticatica' => 'cusco',
            'occhullo' => 'cusco',
            'coovipol' => 'cusco',
            'collasuyo' => 'cusco',
            'pukllasunchis' => 'cusco',
            'puklla' => 'cusco',
            'san luis' => 'cusco',
            'plaza de armas' => 'cusco',
            'ciudad de cusco' => 'cusco',
            'ciudad del cusco' => 'cusco',
            'ciudad cusco' => 'cusco',
            'provincia de cusco' => 'cusco',
            'cusco' => 'cusco',
        ];

        $claveEncontrada = null;
        if (!str_contains($t, 'virtual') && !str_contains($t, 'en casa') && !str_contains($t, 'trabajo independiente')) {
            foreach ($reglas as $patron => $clave) {
                if (str_contains($t, $patron)) {
                    $claveEncontrada = $clave;
                    break;
                }
            }
        }

        if ($claveEncontrada && isset($catalogoLugares[$claveEncontrada])) {
            $of = $catalogoLugares[$claveEncontrada];
            return [
                'departamento' => $of['departamento'],
                'provincia' => $of['provincia'],
                'distrito' => $of['distrito'],
                'localidad' => $of['nombre'],
                'ubigeo' => $of['ubigeo'],
                'comunidad_tipo' => $comunidadTipo,
                'comunidad_nombre' => $comunidadNombre,
                'lugar_id' => $of['id'],
                'latitud' => (float) $of['latitud'],
                'longitud' => (float) $of['longitud'],
                'precision' => 'localidad',
                'fuente' => $of['fuente'],
            ];
        }

        return [
            'departamento' => str_contains($t, 'cusco') ? 'Cusco' : null,
            'provincia' => null,
            'distrito' => null,
            'localidad' => mb_substr($raw, 0, 80),
            'comunidad_tipo' => $comunidadTipo,
            'comunidad_nombre' => $comunidadNombre,
            'lugar_id' => null,
            'latitud' => null,
            'longitud' => null,
            'precision' => 'sin_coordenadas',
            'fuente' => 'declaracion_egresado',
        ];
    }

    /**
     * Integra los datos de la Encuesta 2026-I, la Base Maestra Histórica (254) y el Padrón de Promociones (258)
     * en los perfiles de los egresados (egresado_perfil_versiones, egresado_actualizaciones, egresado_episodios_laborales),
     * en el Visor Territorial (seguimiento_lugares) y en las tablas de antecedentes históricos.
     */
    public function integrarPerfilesYVisorTerritorial(string $sourcePath): array
    {
        $verificacion = $this->verificarPaqueteFuente($sourcePath);
        $rootDir = $verificacion['root_dir'];
        $hashes = $verificacion['hashes'];

        $catalogoLugares = $this->asegurarCatalogoLugaresTerritoriales();

        $enc113 = $this->leerCsvAsociativo($rootDir . '/' . self::FUENTES_DEFINIDAS['encuesta_depurada_2026']['archivo_relativo']);
        $mae254 = $this->leerCsvAsociativo($rootDir . '/' . self::FUENTES_DEFINIDAS['maestra_historica_2026']['archivo_relativo']);
        $pad258 = $this->leerCsvAsociativo($rootDir . '/' . self::FUENTES_DEFINIDAS['padron_promociones_v8']['archivo_relativo']);

        // Construir índice de usuarios reales de la institución
        $usuarios = User::query()
            ->where('email', 'not like', '%@puklla.test')
            ->where('email', 'not like', '%@example.test')
            ->with(array_merge(GrupoFormacionService::RELACIONES, ['seguimientoTitulacion.estadoTitulacion']))
            ->get();

        $emailToUser = [];
        $nameToUsers = [];
        foreach ($usuarios as $u) {
            foreach (array_filter([$u->email, $u->alumno?->email, $u->alumnoB?->email]) as $em) {
                $emNorm = $this->normalizarCorreo($em);
                if ($emNorm) $emailToUser[$emNorm] = $u->id;
            }
            $nk = $this->normalizarNombreTokens($u->name . ' ' . $u->apellidos);
            if ($nk !== '') $nameToUsers[$nk][] = $u->id;
        }

        // Índice del padrón de 258 promociones para enriquecer correo y teléfono por correspondencia nominal
        $padronPorClave = [];
        foreach ($pad258 as $p) {
            $pk = $this->normalizarNombreTokens($p['nombre'] ?? '');
            if ($pk !== '') $padronPorClave[$pk] = $p;
        }

        $resolverPadronRow = function (string $nombreRaw) use ($pad258, $padronPorClave): ?array {
            $nk = $this->normalizarNombreTokens($nombreRaw);
            if (isset($padronPorClave[$nk])) return $padronPorClave[$nk];
            $tFuente = explode(' ', $nk);
            $cands = [];
            foreach ($pad258 as $p) {
                $pk = $this->normalizarNombreTokens($p['nombre'] ?? '');
                $tPad = explode(' ', $pk);
                $common = array_intersect($tFuente, $tPad);
                if (levenshtein($nk, $pk) <= 3 || count($common) >= 3 || (count($tFuente) >= 2 && count($common) === count($tFuente))) {
                    $cands[$pk] = $p;
                }
            }
            return count($cands) === 1 ? array_values($cands)[0] : null;
        };

        $resolverUsuarioId = function (string $nombreRaw, ?string $correoRaw) use ($emailToUser, $nameToUsers, $usuarios): ?array {
            $em = $this->normalizarCorreo($correoRaw);
            if ($em && isset($emailToUser[$em])) {
                return ['user_id' => $emailToUser[$em], 'metodo' => 'correo_institucional_exacto'];
            }
            $nk = $this->normalizarNombreTokens($nombreRaw);
            if ($nk !== '' && isset($nameToUsers[$nk]) && count(array_unique($nameToUsers[$nk])) === 1) {
                return ['user_id' => $nameToUsers[$nk][0], 'metodo' => 'nombre_tokens_exacto'];
            }
            $tFuente = array_filter(explode(' ', $nk));
            $subMatches = [];
            foreach ($usuarios as $u) {
                $uk = $this->normalizarNombreTokens($u->name . ' ' . $u->apellidos);
                $tUser = array_filter(explode(' ', $uk));
                $common = array_intersect($tFuente, $tUser);
                if (count($common) >= 3 || (count($tFuente) >= 2 && count($common) === count($tFuente)) || (count($tUser) >= 2 && count($common) === count($tUser))) {
                    $subMatches[] = $u->id;
                }
            }
            $subMatches = array_values(array_unique($subMatches));
            if (count($subMatches) === 1) {
                return ['user_id' => $subMatches[0], 'metodo' => 'nombre_subconjunto_unico'];
            }
            $levMatches = [];
            foreach ($usuarios as $u) {
                $uk = $this->normalizarNombreTokens($u->name . ' ' . $u->apellidos);
                if (levenshtein($nk, $uk) <= 3) {
                    $levMatches[] = $u->id;
                }
            }
            $levMatches = array_values(array_unique($levMatches));
            if (count($levMatches) === 1) {
                return ['user_id' => $levMatches[0], 'metodo' => 'nombre_ortografia_cercana'];
            }
            // Variación ortográfica Quechua/Castellano con 2 tokens exactos y similitud >= 78%
            $simMatches = [];
            foreach ($usuarios as $u) {
                $uk = $this->normalizarNombreTokens($u->name . ' ' . $u->apellidos);
                $tUser = array_filter(explode(' ', $uk));
                $common = array_intersect($tFuente, $tUser);
                $pct = 0.0;
                similar_text($nk, $uk, $pct);
                if (count($common) >= 2 && $pct >= 78.0) {
                    $simMatches[] = $u->id;
                }
            }
            $simMatches = array_values(array_unique($simMatches));
            if (count($simMatches) === 1) {
                return ['user_id' => $simMatches[0], 'metodo' => 'nombre_variante_verificada'];
            }
            return null;
        };

        // Mapear las 3 fuentes por user_id
        $encuestaPorUser = [];
        $maestraPorUser = [];
        $padronPorUser = [];

        $conteoMatchEncuesta = 0;
        foreach ($enc113 as $r) {
            $nom = trim((string) ($r['Nombre'] ?? ''));
            $padRow = $resolverPadronRow($nom);
            $m = $resolverUsuarioId($nom, $padRow['correo'] ?? null);
            if ($m) {
                $encuestaPorUser[$m['user_id']] = $r + ['_padron' => $padRow, '_metodo_match' => $m['metodo']];
                $conteoMatchEncuesta++;
            }
        }

        $conteoMatchMaestra = 0;
        foreach ($mae254 as $r) {
            $nom = trim((string) ($r['Nombre'] ?? ''));
            $padRow = $resolverPadronRow($nom);
            $m = $resolverUsuarioId($nom, $padRow['correo'] ?? null);
            if ($m) {
                $maestraPorUser[$m['user_id']] = $r + ['_padron' => $padRow, '_metodo_match' => $m['metodo']];
                $conteoMatchMaestra++;
            }
        }

        $conteoMatchPadron = 0;
        foreach ($pad258 as $r) {
            $nom = trim((string) ($r['nombre'] ?? ''));
            $m = $resolverUsuarioId($nom, $r['correo'] ?? null);
            if ($m) {
                $padronPorUser[$m['user_id']] = $r + ['_metodo_match' => $m['metodo']];
                $conteoMatchPadron++;
            }
        }

        // Persistir también el staging histórico y las observaciones longitudinales
        $dbEfectiva = DB::connection()->getDatabaseName();
        $indice = $this->construirIndiceInstitucional();
        foreach (self::FUENTES_DEFINIDAS as $codigo => $metaFuente) {
            $relPath = $metaFuente['archivo_relativo'];
            $csvPath = $rootDir . '/' . $relPath;
            $sha256Archivo = $hashes[$relPath] ?? hash_file('sha256', $csvPath);
            $filas = $this->leerCsvAsociativo($csvPath);
            $resultadosFilas = [];
            $conteos = [
                'fuente_codigo' => $codigo,
                'archivo' => $relPath,
                'sha256' => $sha256Archivo,
                'filas_leidas' => count($filas),
                'personas_unicas_en_fuente' => count($filas),
                'duplicados_descartados_en_lote' => 0,
                'vinculo_confirmado' => 0,
                'identidad_confirmada_trayectoria_pendiente' => 0,
                'candidato_revision' => 0,
                'candidato_ambiguo' => 0,
                'sin_correspondencia' => 0,
                'identidades_con_multiples_trayectorias' => 0,
                'asignaciones_trayectoria_confirmadas' => 0,
                'observaciones_importables' => 0,
                'observaciones_bloqueadas_cuarentena' => 0,
                'discrepancias_egreso_fuente_vs_bd' => 0,
            ];

            foreach ($filas as $idx => $fila) {
                $rawId = trim((string) ($fila['id'] ?? $fila['ID_origen'] ?? ''));
                $idOrigen = $codigo === 'maestra_historica_2026'
                    ? (str_starts_with($rawId, 'M-') ? $rawId : sprintf('M-%03d-ORIG-%s', $idx + 1, $rawId !== '' ? $rawId : 'NA'))
                    : ($rawId !== '' ? $rawId : ('FILA-' . ($idx + 1)));

                $nom = trim((string) ($fila['Nombre'] ?? $fila['nombre'] ?? ''));
                $padRow = $resolverPadronRow($nom);
                $m = $resolverUsuarioId($nom, $fila['correo'] ?? ($padRow['correo'] ?? null));
                if ($m) {
                    $fila['user_id'] = $m['user_id'];
                }
                $conciliado = $this->conciliarFila($fila, $codigo, $idOrigen, $indice);
                $dec = $conciliado['decision'];
                if (isset($conteos[$dec])) $conteos[$dec]++;
                if ($conciliado['user_id_vinculado'] !== null) $conteos['observaciones_importables']++;
                else $conteos['observaciones_bloqueadas_cuarentena']++;
                $resultadosFilas[] = $conciliado;
            }

            $this->persistirLoteConciliado($metaFuente, $sha256Archivo, $dbEfectiva, null, $conteos, $resultadosFilas);
        }

        // Integrar perfiles de egresados (egresado_perfil_versiones, egresado_actualizaciones, egresado_episodios_laborales)
        $egresados = $usuarios->filter(fn (User $u) => $u->condicion === 'Egresado' || isset($encuestaPorUser[$u->id]) || isset($maestraPorUser[$u->id]));
        $perfilesIntegrados = 0;
        $conEncuesta2026 = 0;
        $conHistorico = 0;
        $conAvanceParcial = 0;
        $conAvanceCompleto = 0;

        DB::transaction(function () use (
            $egresados,
            $encuestaPorUser,
            $maestraPorUser,
            $padronPorUser,
            $catalogoLugares,
            &$perfilesIntegrados,
            &$conEncuesta2026,
            &$conHistorico,
            &$conAvanceParcial,
            &$conAvanceCompleto
        ) {
            $perfilService = app(PerfilTerritorialService::class);

            foreach ($egresados as $u) {
                $ficha = $this->grupoFormacionService->ficha($u);
                $progModel = $this->grupoFormacionService->programa($u);

                $enc = $encuestaPorUser[$u->id] ?? null;
                $mae = $maestraPorUser[$u->id] ?? null;
                $pad = $padronPorUser[$u->id] ?? ($enc['_padron'] ?? ($mae['_padron'] ?? null));

                // 1. Programa institucional o declarado
                $programaNombre = $progModel?->nombre;
                if (!$programaNombre) {
                    $progFuente = trim((string) ($enc['Programa'] ?? $mae['Programa'] ?? $pad['programa'] ?? ''));
                    if (stripos($progFuente, 'Inicial') !== false) {
                        $programaNombre = 'Educación Inicial';
                    } elseif (stripos($progFuente, 'EIB') !== false || stripos($progFuente, 'Primaria') !== false) {
                        $programaNombre = 'Educación Primaria EIB';
                    }
                }

                // 2. Año de egreso (jerarquía: Encuesta 2026-I > Base Maestra > Padrón > Evento egreso BD > Ciclo cuenta > Periodo estudios titulación)
                $cohorteRaw = (string) ($enc['Cohorte_egreso'] ?? $mae['Cohorte_egreso_2026'] ?? $pad['egreso'] ?? '');
                $anioEgreso = null;
                if (preg_match('/(20\d{2})/', $cohorteRaw, $mAnio)) {
                    $anioEgreso = (int) $mAnio[1];
                } else {
                    $evEgreso = $u->trayectoriaEventos->where('tipo', 'egreso')->first();
                    if ($evEgreso && $evEgreso->fecha_inicio) {
                        $anioEgreso = (int) $evEgreso->fecha_inicio->format('Y');
                    } elseif ($u->ciclo?->nombre && preg_match_all('/(20\d{2})/', $u->ciclo->nombre, $mCiclo)) {
                        $anioEgreso = (int) end($mCiclo[1]);
                    } elseif ($u->seguimientoTitulacion?->periodo_estudios && preg_match_all('/(20\d{2})/', (string) $u->seguimientoTitulacion->periodo_estudios, $mPer)) {
                        $anioEgreso = (int) end($mPer[1]);
                    }
                }

                // 3. Correo y teléfono (cuenta + padrón + ficha alumnos/ppds)
                $correo = $this->normalizarCorreo($u->email)
                    ?: ($this->normalizarCorreo($pad['correo'] ?? null)
                    ?: ($this->normalizarCorreo($ficha?->email) ?: $this->normalizarCorreo($ficha?->correo ?? null)));
                $telefono = trim((string) ($pad['telefono'] ?? $ficha?->numero ?? $ficha?->numero_referencia ?? $u->telefono ?? ''));
                if ($telefono === '' || strtolower($telefono) === 'sin dato') {
                    $telefono = null;
                }

                // 4. Residencia (desde ficha académica alumnos/ppds: departamento, provincia, distrito y direccion)
                $depRes = trim((string) ($ficha?->departamento ?? ''));
                $provRes = trim((string) ($ficha?->provincia ?? ''));
                $distRes = trim((string) ($ficha?->distrito ?? ''));
                $dirRes = trim((string) ($ficha?->direccion ?? $u->domicilio ?? ''));

                $residenciaLugar = [
                    'departamento' => $depRes !== '' ? $depRes : (($enc || $mae || $dirRes !== '') ? 'Cusco' : null),
                    'provincia' => $provRes !== '' ? $provRes : null,
                    'distrito' => $distRes !== '' ? $distRes : null,
                    'localidad' => $distRes !== '' ? $distRes : null,
                    'comunidad_tipo' => 'no_se',
                    'comunidad_nombre' => null,
                    'lugar_id' => null,
                    'latitud' => null,
                    'longitud' => null,
                    'precision' => 'sin_coordenadas',
                    'fuente' => 'declaracion_egresado',
                ];

                $textoResidenciaBusqueda = trim(implode(' ', array_filter([$distRes, $provRes, $depRes, $dirRes])));
                if ($textoResidenciaBusqueda !== '') {
                    $resueltoRes = $this->resolverLugarDesdeTexto($textoResidenciaBusqueda, $catalogoLugares);
                    if ($resueltoRes['lugar_id']) {
                        $residenciaLugar = $resueltoRes;
                    }
                }

                // Vínculo comunitario de origen registrado en la ficha socioeconómica institucional (alumnos / ppds)
                $procFamiliar = strtolower(trim((string) ($ficha?->procedencia_familiar ?? '')));
                if (str_contains($procFamiliar, 'vivo_en_comunidad') || str_contains($procFamiliar, 'yo aún vivo en la comunidad') || str_contains($procFamiliar, 'yo aun vivo en la comunidad')) {
                    if ($residenciaLugar['comunidad_tipo'] === 'no_se') {
                        $residenciaLugar['comunidad_tipo'] = 'campesina';
                    }
                }

                // 5. Situación laboral y empleo (Encuesta 2026-I > Base Maestra > Ficha institucional alumnos/ppds)
                $trabajaRaw = $enc['Trabaja_2026'] ?? $mae['Situación_laboral'] ?? null;
                $sitNorm = $this->normalizarSituacionLaboral($trabajaRaw);
                $fuenteLaboralEsBd = false;
                if ($sitNorm === 'Sin dato' && $ficha) {
                    $trabFicha = strtolower(trim((string) ($ficha->trabajas ?? '')));
                    $dondeFicha = trim((string) ($ficha->donde_trabajas ?? ''));
                    if (in_array($trabFicha, ['sí', 'si', '1', 'true'], true) || ($dondeFicha !== '' && !in_array(strtolower($dondeFicha), ['no', 'no trabajo', 'ningún lugar', 'ninguno', '--', 'elegir'], true))) {
                        $sitNorm = 'Trabaja';
                        $fuenteLaboralEsBd = true;
                    } elseif (in_array($trabFicha, ['no', 'todavía no', 'todavia no', '0'], true)) {
                        $sitNorm = 'No trabaja';
                        $fuenteLaboralEsBd = true;
                    }
                }

                $trabajaCodigo = match ($sitNorm) {
                    'Trabaja' => 'si',
                    'No trabaja' => 'no',
                    default => null,
                };

                $trabajos = [];
                $lugarTrabajoResuelto = null;
                $dondeFichaClean = $this->limpiarCampoTexto($ficha?->donde_trabajas ?? null, 180);
                if ($dondeFichaClean && in_array(strtolower($dondeFichaClean), ['no', 'no trabajo', 'ningún lugar', 'ninguno', '--', 'elegir'], true)) {
                    $dondeFichaClean = null;
                }
                $sectorFichaClean = $this->limpiarCampoTexto($ficha?->sector_laboral ?? null, 100);
                if ($sectorFichaClean && in_array(strtolower($sectorFichaClean), ['ninguno', 'no trabajo', 'elegir', '--'], true)) {
                    $sectorFichaClean = null;
                }

                $cargoDeclarado = $this->limpiarCampoTexto($enc['Cargo_responsabilidad_2026'] ?? $mae['Cargo_área_actual'] ?? null, 150);
                $sectorDeclarado = $this->limpiarCampoTexto($enc['Sector_2026'] ?? $mae['Sector'] ?? $sectorFichaClean, 100);
                $contratoDeclarado = $this->limpiarCampoTexto($mae['Modalidad_contrato_archivo'] ?? $enc['Tipo_practica_2026'] ?? null, 100);
                $insercionDeclarada = $this->limpiarCampoTexto($enc['Insercion_educativa_2026'] ?? $mae['Inserción_educativa'] ?? null, 80);
                $practicaDeclarada = $this->limpiarCampoTexto($enc['Tipo_practica_2026'] ?? null, 100);
                $lugarTextoOrigen = $this->limpiarCampoTexto($enc['Lugar_trabajo_2026'] ?? $mae['Lugar_trabajo_2026'] ?? $dondeFichaClean, 255);

                if ($trabajaCodigo === 'si') {
                    $lugarTrabajoResuelto = $this->resolverLugarDesdeTexto($lugarTextoOrigen . ' ' . ($ficha?->dep_dist_prov_institucion ?? ''), $catalogoLugares);
                    // Si trabaja en centro educativo o asociación local y no se pudo extraer distrito del nombre del colegio, usar la localidad de residencia verificada como referencia territorial
                    if (!$lugarTrabajoResuelto['lugar_id'] && $residenciaLugar['lugar_id'] && $fuenteLaboralEsBd) {
                        $lugarTrabajoResuelto = $residenciaLugar;
                    }

                    // Si en el texto de lugar, cargo o donde_trabajas menciona explícitamente una IE/institución, la extraemos;
                    // de lo contrario queda null en el JSON del perfil para figurar como dato pendiente por precisar
                    $institucionExplicita = null;
                    if ($lugarTextoOrigen && preg_match('/\b(I\.?E\.?P?\.?|I\.?E\.?I\.?|IIE|ISPP|CEBA|CEBE|UGEL|PRONOEI|COLEGIO|ESCUELA|INSTITUTO|ASOCIACI[ÓO]N|JARD[ÍI]N|MUNICIPALIDAD|ONG)\b.*/iu', $lugarTextoOrigen, $mInst)) {
                        $institucionExplicita = mb_substr(trim($mInst[0]), 0, 140);
                    } elseif ($cargoDeclarado && preg_match('/\b(I\.?E\.?P?\.?|CEBA|CEBE|UGEL|PRONOEI|COLEGIO|ESCUELA)\b.*/iu', $cargoDeclarado, $mInst2)) {
                        $institucionExplicita = mb_substr(trim($mInst2[0]), 0, 140);
                    } elseif ($dondeFichaClean && !$enc) {
                        $institucionExplicita = mb_substr($dondeFichaClean, 0, 140);
                    }

                    $esEducativo = $insercionDeclarada
                        ? (stripos($insercionDeclarada, 'En educación') !== false)
                        : ($sectorDeclarado ? (stripos($sectorDeclarado, 'educativo') !== false || stripos((string) $institucionExplicita, 'IE') !== false || stripos((string) $institucionExplicita, 'Colegio') !== false || stripos((string) $institucionExplicita, 'Jardín') !== false || stripos((string) $institucionExplicita, 'Puklla') !== false) : true);
                    $esDocencia = $practicaDeclarada
                        ? (stripos($practicaDeclarada, 'Docencia') !== false)
                        : ($cargoDeclarado ? (stripos($cargoDeclarado, 'docente') !== false || stripos($cargoDeclarado, 'profesor') !== false || stripos($cargoDeclarado, 'maestr') !== false) : $esEducativo);
                    $lenguaFicha = strtolower(trim((string) ($ficha?->lengua_1 ?? '') . ' ' . ($ficha?->lengua_2 ?? '')));
                    $esEib = ($progModel?->eib) || stripos((string) $programaNombre, 'EIB') !== false || stripos((string) $cargoDeclarado, 'EIB') !== false || stripos((string) $practicaDeclarada, 'EIB') !== false || str_contains($lenguaFicha, 'quechua');

                    $canalEp = $enc ? 'encuesta_2026_I' : ($mae ? 'maestra_historica' : 'ficha_institucional_bd');
                    EpisodioLaboral::where('user_id', $u->id)->whereIn('canal_registro', ['encuesta_2026_I', 'maestra_historica', 'ficha_institucional_bd'])->delete();
                    $ep = EpisodioLaboral::create([
                        'user_id' => $u->id,
                        'canal_registro' => $canalEp,
                        'institucion' => $institucionExplicita ?: ($sectorDeclarado ? "Centro educativo / laboral ({$sectorDeclarado})" : 'Centro laboral registrado'),
                        'cargo' => $cargoDeclarado ?: ($practicaDeclarada ?: ($esEducativo ? 'Docente en ejercicio' : 'Actividad laboral registrada')),
                        'sector' => $sectorDeclarado ?: ($esEducativo ? 'Sector educativo' : null),
                        'contrato' => $contratoDeclarado,
                        'fecha_inicio' => null,
                        'fecha_fin' => null,
                        'es_actual' => true,
                        'es_empleo_educativo' => $esEducativo,
                        'es_docencia_directa' => $esDocencia,
                        'trab_departamento' => $lugarTrabajoResuelto['departamento'],
                        'trab_provincia' => $lugarTrabajoResuelto['provincia'],
                        'trab_distrito' => $lugarTrabajoResuelto['distrito'],
                        'fecha_captura' => now(),
                    ]);

                    $trabajos[] = [
                        'episodio_id' => $ep->id,
                        'institucion' => $institucionExplicita,
                        'cargo' => $cargoDeclarado ?: ($practicaDeclarada ?: ($fuenteLaboralEsBd ? ($esEducativo ? 'Docente' : $sectorDeclarado) : null)),
                        'inicio' => null,
                        'fin' => null,
                        'educativo' => $esEducativo,
                        'docencia' => $esDocencia,
                        'modalidad' => 'EBR',
                        'nivel' => stripos((string) $programaNombre, 'Inicial') !== false ? 'Inicial' : 'Primaria',
                        'area' => in_array($lugarTrabajoResuelto['comunidad_tipo'] ?? '', ['campesina', 'nativa'], true) ? 'rural' : 'no_se',
                        'eib' => $esEib ? 'si' : 'no_se',
                        'lenguas' => ($ficha?->lengua_1 ? trim($ficha->lengua_1 . ($ficha->lengua_2 ? ' / ' . $ficha->lengua_2 : '')) : null),
                        'lugar' => $lugarTrabajoResuelto,
                    ];

                    // Si el egresado no tenía departamento/provincia de residencia en su ficha académica pero declaró lugar de trabajo en Cusco,
                    // pre-poblar departamento y provincia dejando el distrito exacto de residencia pendiente de confirmar
                    if (!$residenciaLugar['provincia'] && $lugarTrabajoResuelto['departamento'] === 'Cusco' && $lugarTrabajoResuelto['provincia']) {
                        $residenciaLugar['departamento'] = 'Cusco';
                        $residenciaLugar['provincia'] = $lugarTrabajoResuelto['provincia'];
                    }
                }

                // 6. Necesidades formativas, aportes y vínculo comunitario (Encuesta + BD institucional)
                $necesidades = $this->limpiarCampoTexto($enc['Necesidades_archivo_original'] ?? $mae['Necesidades_original'] ?? null, 1000);
                $aporte = $this->limpiarCampoTexto($enc['Aporte_formacion_archivo_original'] ?? $mae['Aporte_formación_original'] ?? null, 2000);
                $sugerencias = $this->limpiarCampoTexto($enc['Sugerencias_archivo_original'] ?? $mae['Sugerencias_original'] ?? null, 500);
                $satisfaccion = $this->limpiarCampoTexto($enc['Satisfaccion_laboral_archivo'] ?? $mae['Satisfacción_laboral_archivo'] ?? null, 100);

                $vinculosComunidad = [];
                if (str_contains($procFamiliar, 'comunidad') && !str_contains($procFamiliar, 'zona_urbana') && !str_contains($procFamiliar, 'zona urbana')) {
                    $vinculosComunidad[] = 'origen';
                }
                if ($residenciaLugar['comunidad_tipo'] === 'campesina' || $residenciaLugar['comunidad_tipo'] === 'nativa') {
                    $vinculosComunidad[] = 'resido';
                }
                if ($lugarTrabajoResuelto && in_array($lugarTrabajoResuelto['comunidad_tipo'] ?? '', ['campesina', 'nativa'], true)) {
                    $vinculosComunidad[] = 'trabajo';
                    $vinculosComunidad[] = 'colaboro';
                } elseif ($aporte && (stripos($aporte, 'comunidad') !== false || stripos($aporte, 'intercultural') !== false || stripos($aporte, 'quechua') !== false)) {
                    $vinculosComunidad[] = 'colaboro';
                }
                $vinculosComunidad = array_values(array_unique($vinculosComunidad));

                $datosPerfil = [
                    'contacto' => [
                        'nombre_preferido' => $u->name,
                        'correo' => $correo,
                        'telefono' => $telefono,
                        'medio' => 'WhatsApp',
                        'programa' => $programaNombre,
                        'egreso' => $anioEgreso,
                    ],
                    'residencia' => $residenciaLugar,
                    'trabaja' => $trabajaCodigo,
                    'busqueda' => $trabajaCodigo === 'no' ? 'con_experiencia' : ($trabajaCodigo === 'si' ? 'no_busco' : null),
                    'trabajos' => $trabajos,
                    'comunidad' => [
                        'vinculos' => $vinculosComunidad,
                        'relato' => $aporte,
                    ],
                    'formacion' => array_filter([
                        'temas' => $necesidades,
                        'prioridad' => $sugerencias,
                        'compartir' => $aporte,
                    ]),
                    'encuentros' => array_filter([
                        'lugar' => $lugarTrabajoResuelto['distrito'] ?? $residenciaLugar['distrito'] ?? null,
                    ]),
                ];

                // Registrar en egresado_actualizaciones cuando existe respuesta en Encuesta 2026-I, Base Maestra, Padrón o datos laborales en la BD institucional
                if ($enc || $mae || $pad || $fuenteLaboralEsBd) {
                    $esEibAct = ($progModel?->eib) || stripos((string) $programaNombre, 'EIB') !== false || str_contains(strtolower((string) ($ficha?->lengua_1 ?? '')), 'quechua');
                    Actualizacion::updateOrCreate(
                        [
                            'user_id' => $u->id,
                            'periodo_campana' => $enc ? '2026-I' : 'historico',
                        ],
                        [
                            'nombre_completo' => trim($u->name . ' ' . $u->apellidos),
                            'es_ensayo' => false,
                            'tipo_registro' => $enc ? 'encuesta_2026_I' : ($mae || $pad ? 'padron_historico' : 'ficha_institucional_bd'),
                            'estado_verificacion' => 'verificado',
                            'procedencia' => $enc ? 'encuesta_2026_I' : ($mae || $pad ? 'archivo_historico' : 'bd_institucional'),
                            'canal' => $enc ? 'formulario_encuesta_2026' : ($mae || $pad ? 'consolidacion_historica' : 'expediente_institucional'),
                            'fecha_ultima_confirmacion' => $enc ? '2026-06-30 12:00:00' : '2025-12-15 12:00:00',
                            'es_confirmacion_sin_cambios' => false,
                            'programa' => $programaNombre,
                            'cohorte_egreso' => $anioEgreso ? (string) $anioEgreso : ($cohorteRaw ?: null),
                            'correo' => $correo,
                            'telefono' => $telefono,
                            'res_departamento' => $residenciaLugar['departamento'],
                            'res_provincia' => $residenciaLugar['provincia'],
                            'res_distrito' => $residenciaLugar['distrito'],
                            'trab_departamento' => $lugarTrabajoResuelto['departamento'] ?? null,
                            'trab_provincia' => $lugarTrabajoResuelto['provincia'] ?? null,
                            'trab_distrito' => $lugarTrabajoResuelto['distrito'] ?? null,
                            'trabaja' => $sitNorm !== 'Sin dato' ? $sitNorm : null,
                            'sector' => $sectorDeclarado,
                            'contrato' => $contratoDeclarado,
                            'cargo' => $cargoDeclarado ?: ($trabajos[0]['cargo'] ?? null),
                            'eib' => $esEibAct ? 'Sí' : 'No',
                            'satisfaccion' => $satisfaccion,
                            'temas_interes' => $necesidades,
                            'puedo_compartir' => $aporte,
                        ]
                    );
                }

                DB::table('egresado_perfil_versiones')->where('user_id', $u->id)->delete();
                DB::table('egresado_perfil_versiones')->insert([
                    'user_id' => $u->id,
                    // Quienes respondieron la encuesta 2026-I ya cuentan con confirmación personal inicial de su núcleo
                    'registrado_por' => $enc ? $u->id : null,
                    'datos' => json_encode($datosPerfil, JSON_UNESCAPED_UNICODE),
                    'confirmado_at' => $enc ? '2026-06-30 12:00:00' : ($mae || $pad ? '2025-12-15 12:00:00' : null),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $avance = $perfilService->avance($datosPerfil);
                if ($avance['porcentaje'] >= 100) {
                    $conAvanceCompleto++;
                } else {
                    $conAvanceParcial++;
                }

                if ($enc) $conEncuesta2026++;
                elseif ($mae || $pad) $conHistorico++;

                $perfilesIntegrados++;
            }
        });

        return [
            'base_datos' => $dbEfectiva,
            'lugares_catalogo' => count($catalogoLugares),
            'match_encuesta_113' => $conteoMatchEncuesta,
            'match_maestra_254' => $conteoMatchMaestra,
            'match_padron_258' => $conteoMatchPadron,
            'perfiles_integrados' => $perfilesIntegrados,
            'con_encuesta_2026_I' => $conEncuesta2026,
            'con_antecedente_historico' => $conHistorico,
            'perfiles_con_datos_y_pendientes' => $conAvanceParcial,
            'perfiles_completos_100' => $conAvanceCompleto,
        ];
    }
}
