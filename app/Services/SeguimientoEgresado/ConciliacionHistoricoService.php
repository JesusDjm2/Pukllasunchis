<?php

namespace App\Services\SeguimientoEgresado;

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
     * Obtiene el resumen de hitos oficiales separados (Egreso, Titulación y Grado/Licenciatura) (T09, T10).
     */
    public function obtenerHitosOficialesSeparados(User $user): array
    {
        $user->loadMissing(array_merge(GrupoFormacionService::RELACIONES, [
            'seguimientoTitulacion.estadoTitulacion',
            'seguimientoTitulacion.resoluciones',
            'seguimientoTitulacion.actas',
        ]));

        $eventosEgreso = $user->trayectoriaEventos->where('tipo', 'egreso');
        $egresoAcreditado = $eventosEgreso->isNotEmpty();

        $seg = $user->seguimientoTitulacion;
        $claveTit = $seg?->estadoTitulacion?->clave;

        $estadoTitulacion = 'No verificado en el sistema';
        $tituloAcreditado = false;
        if ($seg) {
            if ($claveTit === 'licenciado') {
                $estadoTitulacion = 'Título acreditado';
                $tituloAcreditado = true;
            } elseif (in_array($claveTit, ['pti_en_proceso', 'pti_aprobado', 'ti_aprobado', 'tesis_aprobada'], true)) {
                $estadoTitulacion = 'En trámite (' . ($seg->estadoTitulacion?->nombre ?? $claveTit) . ')';
            } else {
                $estadoTitulacion = $seg->estadoTitulacion?->nombre ?? 'Registrado sin título acreditado';
            }
        }

        // Grado / Licenciatura: solo si existe evidencia específica (no asumir sinonimia automática con título)
        $tieneEvidenciaGrado = $claveTit === 'bachiller'
            || ($seg && $seg->resoluciones && $seg->resoluciones->contains(fn ($r) => str_contains(strtolower((string) $r->tipo), 'bachiller') || str_contains(strtolower((string) $r->tipo), 'grado')));

        return [
            'condicion_cuenta' => $user->condicion ?? 'No consignada',
            'egreso' => [
                'estado' => $egresoAcreditado ? 'acreditado_institucional' : 'no_verificado',
                'acreditado' => $egresoAcreditado,
                'fechas' => $eventosEgreso->map(fn ($e) => $e->fecha_inicio?->format('Y-m-d'))->filter()->values()->all(),
            ],
            'titulacion' => [
                'estado' => $tituloAcreditacion = ($tituloAcreditado ? 'acreditado_institucional' : ($seg ? 'en_tramite' : 'no_verificado')),
                'titulo_acreditado' => $tituloAcreditado,
                'detalle' => $estadoTitulacion,
            ],
            'grado_bachiller' => [
                'estado' => $tieneEvidenciaGrado ? 'acreditado_institucional' : 'no_verificado',
                'acreditado' => $tieneEvidenciaGrado,
                'detalle' => $tieneEvidenciaGrado ? 'Evidencia de grado registrada en expediente' : 'No verificado en el sistema',
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
}
