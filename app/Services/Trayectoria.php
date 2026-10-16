<?php

namespace App\Services;

use App\Models\Ciclo;
use App\Models\PeriodoActual;
use App\Models\PeriodoActualPpd;
use App\Models\TrayectoriaEvento;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Traduce cambios de users.perfil / condicion / ciclo_id / programa_id en eventos de
 * trayectoria. Lo llaman el observer de User (cambios con save()) y, a mano, las acciones
 * masivas que usan update() en bloque y por eso no disparan eventos de Eloquent
 * (CicloController::updateCicloAlumnos y ::marcarComoEgresados).
 */
class Trayectoria
{
    /** perfil → tipo de evento cuando un estudiante deja de estar activo */
    private const PERFIL_A_TIPO = [
        'Licencia' => 'licencia',
        'Retirado' => 'retiro',
        'Deuda' => 'inhabilitacion',
        'Sin matrícula' => 'inhabilitacion',
    ];

    private const ROLES_ESTUDIANTE = ['alumno', 'alumnoB', 'inhabilitado'];

    /**
     * @param  array  $antes  valores originales: perfil, condicion, ciclo_id, programa_id
     */
    public static function registrarCambios(User $user, array $antes, string $origen = 'automatico'): void
    {
        try {
            if (! self::esEstudiante($user)) {
                return;
            }

            $despues = [
                'perfil' => $user->perfil,
                'condicion' => $user->condicion,
                'ciclo_id' => $user->ciclo_id,
                'programa_id' => $user->programa_id,
            ];
            $cambio = fn ($campo) => array_key_exists($campo, $antes) && (string) $antes[$campo] !== (string) $despues[$campo];

            if ($cambio('perfil')) {
                self::porPerfil($user, $antes['perfil'], $despues['perfil'], $origen);
            }
            if ($cambio('condicion')) {
                self::porCondicion($user, $antes['condicion'], $despues['condicion'], $origen);
            }
            if ($cambio('programa_id')) {
                self::crear($user, 'cambio_programa', $origen, [
                    'valor_anterior' => optional(\App\Models\Programa::find($antes['programa_id']))->nombre,
                    'valor_nuevo' => optional(\App\Models\Programa::find($despues['programa_id']))->nombre,
                    'fecha_inicio' => now()->toDateString(),
                ]);
            }
            if ($cambio('ciclo_id')) {
                self::crear($user, 'cambio_ciclo', $origen, [
                    'ciclo_origen_id' => $antes['ciclo_id'] ?: null,
                    'ciclo_destino_id' => $despues['ciclo_id'] ?: null,
                    'valor_anterior' => optional(Ciclo::find($antes['ciclo_id']))->nombre,
                    'valor_nuevo' => optional(Ciclo::find($despues['ciclo_id']))->nombre,
                    'fecha_inicio' => now()->toDateString(),
                ]);
            }
        } catch (\Throwable $e) {
            // El registro de trayectoria nunca debe impedir guardar al alumno.
            Log::error('Trayectoria: no se pudo registrar el cambio', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    private static function porPerfil(User $user, ?string $antes, ?string $despues, string $origen): void
    {
        $tipo = self::PERFIL_A_TIPO[$despues] ?? null;
        $volvioAActivo = isset(self::PERFIL_A_TIPO[$antes]) && $despues === 'Estudiante';

        // Al salir de una licencia (a Estudiante o a otro motivo) se cierra la licencia abierta
        // y se deja escrito en el historial cuánto duró y cuánto lleva acumulado.
        $notaCierre = null;
        if ($antes === 'Licencia' && $despues !== 'Licencia') {
            $abiertas = TrayectoriaEvento::licenciasActivas()->where('user_id', $user->id)->get();
            foreach ($abiertas as $licencia) {
                $licencia->update(['fecha_fin' => now()->toDateString()]);
            }
            $acum = self::licenciaAcumulada($user);
            $duro = $abiertas->filter(fn ($l) => $l->fecha_inicio)->sum(fn ($l) => $l->fecha_inicio->diffInDays(now()->startOfDay()));
            $notaCierre = sprintf(
                'Fin de licencia (%d días). Acumulado de licencias: %d de %d días%s.',
                $duro, $acum['dias'], $acum['limite'],
                $acum['agotada'] ? ' — LÍMITE ALCANZADO: ya no puede pedir otra licencia' : ' — le quedan ' . $acum['restante'] . ' días'
            );
        }

        if ($tipo) {
            $datos = ['valor_anterior' => $antes, 'valor_nuevo' => $despues, 'fecha_inicio' => now()->toDateString()];
            if ($tipo === 'licencia') {
                $acum = self::licenciaAcumulada($user);
                $datos['motivo'] = sprintf('Saldo de licencia al iniciar: %d de %d días disponibles.', $acum['restante'], $acum['limite']);
            } elseif ($notaCierre) {
                $datos['motivo'] = $notaCierre;
            }
            self::crear($user, $tipo, $origen, $datos);
        } elseif ($volvioAActivo) {
            self::crear($user, 'reincorporacion', $origen, [
                'valor_anterior' => $antes,
                'valor_nuevo' => $despues,
                'fecha_inicio' => now()->toDateString(),
                'motivo' => $notaCierre,
            ]);
        }
    }

    private static function porCondicion(User $user, ?string $antes, ?string $despues, string $origen): void
    {
        $datos = ['valor_anterior' => $antes, 'valor_nuevo' => $despues, 'fecha_inicio' => now()->toDateString()];

        if ($despues === 'Egresado') {
            self::crear($user, 'egreso', $origen, $datos + ['ciclo_origen_id' => $user->ciclo_id]);
        } elseif ($despues === 'Reincorporación') {
            self::crear($user, 'reincorporacion', $origen, $datos);
        } elseif (str_starts_with((string) $despues, 'Beca') || str_starts_with((string) $antes, 'Beca')) {
            self::crear($user, 'beca', $origen, $datos);
        }
    }

    /**
     * Licencia ACUMULADA: el reglamento permite como máximo 2 años sumando todas las
     * licencias del alumno, aunque estén partidas (licencia → reincorporación → licencia…).
     * Cada licencia cuenta desde fecha_inicio hasta fecha_fin, o hasta hoy si sigue abierta.
     * Las registradas sin fecha (las del estado inicial) no se pueden medir: se informan
     * aparte en 'sin_fecha' para que se completen, y no bloquean.
     */
    public static function licenciaAcumulada(User $user): array
    {
        $licencias = TrayectoriaEvento::where('user_id', $user->id)->where('tipo', 'licencia')->get();
        $hoy = now()->startOfDay();
        $limite = TrayectoriaEvento::LICENCIA_MAX_DIAS;

        $dias = $licencias->filter(fn ($l) => $l->fecha_inicio)
            ->sum(fn ($l) => $l->fecha_inicio->diffInDays(($l->fecha_fin ?? $hoy)->copy()->min($hoy)));
        $activa = $licencias->first(fn ($l) => $l->fecha_fin === null);
        $restante = max(0, $limite - $dias);

        return [
            'dias' => $dias,
            'limite' => $limite,
            'restante' => $restante,
            'agotada' => $dias >= $limite,
            'sin_fecha' => $licencias->whereNull('fecha_inicio')->count(),
            'activa' => $activa,
            // Si hay una licencia abierta con fecha: el día en que se acaba el saldo total.
            'fecha_limite' => $activa?->fecha_inicio ? $hoy->copy()->addDays($restante) : null,
        ];
    }

    /** ¿Puede iniciar una licencia nueva? (false si ya consumió los 2 años acumulados) */
    public static function puedeIniciarLicencia(User $user): bool
    {
        return ! self::licenciaAcumulada($user)['agotada'];
    }

    public static function mensajeLicenciaAgotada(User $user): string
    {
        $acum = self::licenciaAcumulada($user);

        return sprintf(
            '%s %s ya usó %d días de licencia (el máximo acumulado es %d años): no puede iniciar otra licencia.',
            $user->name, $user->apellidos, $acum['dias'], TrayectoriaEvento::LICENCIA_MAX_ANIOS
        );
    }

    public static function crear(User $user, string $tipo, string $origen = 'manual', array $datos = []): TrayectoriaEvento
    {
        [$periodoFid, $periodoPpd] = self::periodoDe($user);

        return TrayectoriaEvento::create($datos + [
            'user_id' => $user->id,
            'tipo' => $tipo,
            'origen' => $origen,
            'periodo_actual_id' => $periodoFid,
            'periodo_actual_ppd_id' => $periodoPpd,
            'registrado_por' => auth()->id(),
        ]);
    }

    /** Periodo vigente según la modalidad del alumno (FID usa periodo_actual, PPD el suyo). */
    private static function periodoDe(User $user): array
    {
        $esPpd = $user->programa?->esPpd() ?? $user->alumnoB()->exists();

        return $esPpd
            ? [null, PeriodoActualPpd::actual()?->id]
            : [PeriodoActual::actual()?->id, null];
    }

    private static function esEstudiante(User $user): bool
    {
        return $user->hasAnyRole(self::ROLES_ESTUDIANTE)
            || $user->alumno()->exists()
            || $user->alumnoB()->exists();
    }
}
