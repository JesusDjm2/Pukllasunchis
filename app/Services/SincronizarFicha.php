<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * La CUENTA (users) y la FICHA de matrícula (alumnos FID / ppds PPD) repiten algunos datos:
 * nombres, apellidos, DNI, programa y ciclo. Antes se editaban por separado y podían quedar
 * distintos (p. ej. cambiar el ciclo en "Editar cuenta" no tocaba la ficha). Aquí se
 * mantienen iguales en ambas direcciones. El correo NO se sincroniza: en la ficha suele ser
 * el personal y en la cuenta el institucional.
 */
class SincronizarFicha
{
    /** cuenta (users) => ficha (alumnos / ppds) */
    private const CAMPOS = [
        'name' => 'nombres',
        'apellidos' => 'apellidos',
        'dni' => 'dni',
        'programa_id' => 'programa_id',
        'ciclo_id' => 'ciclo_id',
    ];

    /** Evita el rebote: al copiar en un sentido no se vuelve a copiar en el otro. */
    private static bool $sincronizando = false;

    public static function desdeCuenta(User $user): void
    {
        $cambios = array_intersect_key(self::CAMPOS, $user->getChanges());
        if (! $cambios || self::$sincronizando) {
            return;
        }

        self::ejecutar(function () use ($user, $cambios) {
            foreach ([$user->alumno()->first(), $user->alumnoB()->first()] as $ficha) {
                if (! $ficha) {
                    continue;
                }
                $datos = [];
                foreach ($cambios as $campoCuenta => $campoFicha) {
                    $datos[$campoFicha] = $user->{$campoCuenta};
                }
                $ficha->fill($datos);
                if ($ficha->isDirty()) {
                    $ficha->save();
                }
            }
        }, ['user_id' => $user->id]);
    }

    public static function desdeFicha(Model $ficha): void
    {
        $inversa = array_flip(self::CAMPOS);
        $cambios = array_intersect_key($inversa, $ficha->getChanges());
        if (! $cambios || self::$sincronizando || ! $ficha->user_id) {
            return;
        }

        self::ejecutar(function () use ($ficha, $cambios) {
            $user = User::find($ficha->user_id);
            if (! $user) {
                return;
            }
            foreach ($cambios as $campoFicha => $campoCuenta) {
                // Un dato vacío en la ficha no borra el de la cuenta.
                if (filled($ficha->{$campoFicha})) {
                    $user->{$campoCuenta} = $ficha->{$campoFicha};
                }
            }
            if ($user->isDirty()) {
                $user->save(); // dispara el registro de trayectoria si cambió el ciclo/programa
            }
        }, ['ficha' => get_class($ficha), 'id' => $ficha->getKey()]);
    }

    private static function ejecutar(callable $fn, array $contexto): void
    {
        self::$sincronizando = true;
        try {
            $fn();
        } catch (\Throwable $e) {
            // Sincronizar nunca debe impedir guardar lo que el usuario editó.
            Log::error('SincronizarFicha: no se pudo sincronizar', $contexto + ['error' => $e->getMessage()]);
        } finally {
            self::$sincronizando = false;
        }
    }
}
