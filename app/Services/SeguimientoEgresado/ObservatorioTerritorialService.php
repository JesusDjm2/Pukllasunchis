<?php

namespace App\Services\SeguimientoEgresado;

use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ObservatorioTerritorialService
{
    protected EstadisticaSeguraService $seguridadService;

    public function __construct(EstadisticaSeguraService $seguridadService)
    {
        $this->seguridadService = $seguridadService;
    }

    /**
     * Genera el informe analítico completo del Observatorio Territorial.
     */
    public function obtenerReporteTerritorial(array $filtros = []): array
    {
        // 1. Base consolidada de egresados con sus datos territoriales
        $egresados = $this->obtenerEgresadosConTerritorio($filtros);

        // 2. Distribución por Departamentos (Residencia y Empleo)
        $porDepartamentoResidencia = [];
        $porDepartamentoTrabajo = [];
        $porProvinciaCusco = [];
        $movilidad = [
            'reside_y_trabaja_cusco' => 0,
            'reside_cusco_trabaja_fuera' => 0,
            'reside_fuera_trabaja_cusco' => 0,
            'reside_y_trabaja_fuera' => 0,
            'territorio_incompleto' => 0,
        ];
        $eibPorProvincia = [];
        $ruralUrbano = [
            'rural' => 0,
            'urbano' => 0,
            'sin_especificar' => 0,
        ];

        foreach ($egresados as $eg) {
            $depRes = strtoupper(trim($eg['dep_residencia'] ?: 'NO DECLARADO'));
            $depTrab = strtoupper(trim($eg['dep_trabajo'] ?: 'NO DECLARADO'));
            $provTrab = strtoupper(trim($eg['prov_trabajo'] ?: ($eg['prov_residencia'] ?: 'NO DECLARADO')));

            // Departamentos
            $porDepartamentoResidencia[$depRes] = ($porDepartamentoResidencia[$depRes] ?? 0) + 1;
            if ($depTrab !== 'NO DECLARADO') {
                $porDepartamentoTrabajo[$depTrab] = ($porDepartamentoTrabajo[$depTrab] ?? 0) + 1;
            }

            // Provincias de Cusco (si labora o reside en Cusco)
            if (str_contains($depTrab, 'CUSCO') || str_contains($depRes, 'CUSCO')) {
                $porProvinciaCusco[$provTrab] = ($porProvinciaCusco[$provTrab] ?? 0) + 1;

                if ($eg['es_eib'] || $eg['programa_eib']) {
                    $eibPorProvincia[$provTrab] = ($eibPorProvincia[$provTrab] ?? 0) + 1;
                }
            }

            // Análisis de Movilidad
            $esCuscoRes = str_contains($depRes, 'CUSCO');
            $esCuscoTrab = str_contains($depTrab, 'CUSCO');

            if ($depRes === 'NO DECLARADO' || $depTrab === 'NO DECLARADO') {
                $movilidad['territorio_incompleto']++;
            } elseif ($esCuscoRes && $esCuscoTrab) {
                $movilidad['reside_y_trabaja_cusco']++;
            } elseif ($esCuscoRes && !$esCuscoTrab) {
                $movilidad['reside_cusco_trabaja_fuera']++;
            } elseif (!$esCuscoRes && $esCuscoTrab) {
                $movilidad['reside_fuera_trabaja_cusco']++;
            } else {
                $movilidad['reside_y_trabaja_fuera']++;
            }

            // Ámbito Rural / Urbano
            $ambito = strtolower(trim($eg['ambito'] ?: ''));
            if (str_contains($ambito, 'rural') || str_contains($ambito, 'comunidad')) {
                $ruralUrbano['rural']++;
            } elseif (str_contains($ambito, 'urbano') || str_contains($ambito, 'ciudad')) {
                $ruralUrbano['urbano']++;
            } else {
                $ruralUrbano['sin_especificar']++;
            }
        }

        // Ordenar descendentemente
        arsort($porDepartamentoResidencia);
        arsort($porDepartamentoTrabajo);
        arsort($porProvinciaCusco);
        arsort($eibPorProvincia);

        // Aplicar privacidad estadística
        $depResSeguro = $this->seguridadService->procesarDistribucionSegura($porDepartamentoResidencia);
        $depTrabSeguro = $this->seguridadService->procesarDistribucionSegura($porDepartamentoTrabajo);
        $provCuscoSeguro = $this->seguridadService->procesarDistribucionSegura($porProvinciaCusco);
        $eibProvSeguro = $this->seguridadService->procesarDistribucionSegura($eibPorProvincia);

        // 3. Recomendaciones y Agenda de Acción Institucional Derivada de los Datos
        $agendaAccion = $this->generarAgendaTerritorial($porProvinciaCusco, $eibPorProvincia, $movilidad);

        return [
            'total_analizado' => count($egresados),
            'departamentos' => $depResSeguro,
            'departamento_residencia' => $depResSeguro,
            'departamento_trabajo' => $depTrabSeguro,
            'provincias_cusco' => $provCuscoSeguro,
            'concentracion_eib' => $eibProvSeguro,
            'eib_por_provincia' => $eibProvSeguro,
            'movilidad' => $movilidad,
            'rural_urbano' => $ruralUrbano,
            'agenda_accion' => $agendaAccion,
            'advertencia_metodologica' => 'Los conteos territoriales representan personas únicas. Los recuentos menores a 5 han sido protegidos mediante supresión estadística conforme a la norma de privacidad institucional.',
        ];
    }

    /**
     * Consulta consolidada de egresados con información territorial.
     */
    protected function obtenerEgresadosConTerritorio(array $filtros = []): Collection
    {
        $query = User::query()
            ->where('condicion', 'Egresado')
            ->with(['alumno.programa', 'seguimientoTitulacion']);

        // Aplicar filtros opcionales
        if (!empty($filtros['programa'])) {
            $query->whereHas('alumno.programa', function ($q) use ($filtros) {
                $q->where('nombre', 'like', '%' . $filtros['programa'] . '%');
            });
        }

        $egresados = $query->get();

        // Obtener últimas actualizaciones y episodios
        $userIds = $egresados->pluck('id')->toArray();
        $actualizaciones = Actualizacion::whereIn('user_id', $userIds)->latest()->get()->keyBy('user_id');
        $episodiosActivos = EpisodioLaboral::whereIn('user_id', $userIds)->where('es_actual', true)->latest()->get()->keyBy('user_id');

        return $egresados->map(function ($u) use ($actualizaciones, $episodiosActivos) {
            $alumno = $u->alumno;
            $act = $actualizaciones->get($u->id);
            $ep = $episodiosActivos->get($u->id);

            $depRes = $act?->res_departamento ?: ($alumno?->departamento ?: 'Cusco');
            $provRes = $act?->res_provincia ?: ($alumno?->provincia ?: 'Cusco');
            $distRes = $act?->res_distrito ?: ($alumno?->distrito ?: 'Cusco');

            $depTrab = $ep?->trab_departamento ?: ($act?->trab_departamento ?: null);
            $provTrab = $ep?->trab_provincia ?: ($act?->trab_provincia ?: null);
            $distTrab = $ep?->trab_distrito ?: ($act?->trab_distrito ?: null);

            $esEib = ($ep?->es_empleo_educativo && ($act?->eib === 'Sí' || !empty($act?->quechua))) || ($act?->eib === 'Sí');
            $progNombre = $alumno?->programa?->nombre ?: '';
            $progEib = str_contains($progNombre, 'EIB');

            $ambito = $alumno?->procedencia_familiar ?: '';

            return [
                'user_id' => $u->id,
                'nombre' => trim($u->name . ' ' . $u->apellidos),
                'programa' => $progNombre,
                'programa_eib' => $progEib,
                'dep_residencia' => $depRes,
                'prov_residencia' => $provRes,
                'dist_residencia' => $distRes,
                'dep_trabajo' => $depTrab,
                'prov_trabajo' => $provTrab,
                'dist_trabajo' => $distTrab,
                'es_eib' => $esEib,
                'ambito' => $ambito,
                'trabaja' => $ep ? true : ($act?->trabaja === 'Trabaja'),
            ];
        });
    }

    /**
     * Formula sugerencias de acción institucional derivadas del mapa territorial.
     */
    protected function generarAgendaTerritorial(array $provincias, array $eibProvincias, array $movilidad): array
    {
        $acciones = [];

        // Identificar provincias con mayor concentración de egresados
        foreach ($provincias as $prov => $cant) {
            if ($prov === 'NO DECLARADO' || $cant < 5) continue;

            $eibCant = $eibProvincias[$prov] ?? 0;

            if ($eibCant >= 5) {
                $acciones[] = [
                    'titulo' => 'Taller Descentralizado EIB',
                    'tipo' => 'Taller Descentralizado EIB',
                    'territorio' => 'Provincia de ' . ucwords(strtolower($prov)),
                    'justificacion' => "Concentración significativa de {$eibCant} egresados vinculados a Educación Intercultural Bilingüe.",
                    'descripcion' => 'Programar encuentro pedagógico y círculo de interaprendizaje de didáctica quechua.',
                    'accion_sugerida' => 'Programar encuentro pedagógico y círculo de interaprendizaje de didáctica quechua.',
                    'prioridad' => 'Alta',
                ];
            } elseif ($cant >= 10 && $prov !== 'CUSCO') {
                $acciones[] = [
                    'titulo' => 'Visita y Mentoría Institucional',
                    'tipo' => 'Visita y Mentoría Institucional',
                    'territorio' => 'Provincia de ' . ucwords(strtolower($prov)),
                    'justificacion' => "Presencia de {$cant} egresados en ejercicio profesional fuera de Cusco capital.",
                    'descripcion' => 'Coordinar visita de acompañamiento pedagógico en territorio y alianza con la UGEL local.',
                    'accion_sugerida' => 'Coordinar visita de acompañamiento pedagógico en territorio y alianza con la UGEL local.',
                    'prioridad' => 'Media',
                ];
            }
        }

        // Sugerencia de seguimiento a egresados migrantes
        if ($movilidad['reside_cusco_trabaja_fuera'] > 5) {
            $acciones[] = [
                'titulo' => 'Red Virtual de Apoyo al Egresado Migrante',
                'tipo' => 'Red Virtual de Apoyo al Egresado Migrante',
                'territorio' => 'Regiones Vecinas (Apurímac, Puno, Madre de Dios)',
                'justificacion' => "Se registran egresados residentes de Cusco que laboran en otras regiones.",
                'descripcion' => 'Abrir sesiones virtuales periódicas de acompañamiento emocional y soporte curricular en contextos de desplazamiento.',
                'accion_sugerida' => 'Abrir sesiones virtuales periódicas de acompañamiento emocional y soporte curricular en contextos de desplazamiento.',
                'prioridad' => 'Media',
            ];
        }

        if (empty($acciones)) {
            $acciones[] = [
                'titulo' => 'Mesa de Articulación Territorial Regional',
                'tipo' => 'Mesa de Articulación Territorial Regional',
                'territorio' => 'Región Cusco y Provincias Altoandinas',
                'justificacion' => 'Consolidación de red institucional con UGEL y redes educativas locales.',
                'descripcion' => 'Articular convenios de práctica y empleo docente bilingüe en el ámbito rural.',
                'accion_sugerida' => 'Articular convenios de práctica y empleo docente bilingüe en el ámbito rural.',
                'prioridad' => 'Alta',
            ];
        }

        return $acciones;
    }
}
