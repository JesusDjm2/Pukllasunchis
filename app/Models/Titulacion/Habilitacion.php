<?php

namespace App\Models\Titulacion;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Excepción de Secretaría: el alumno puede solicitar la aprobación de su PTI (e iniciar su proceso) aunque todavía no
 * cumpla la regla del ciclo. La consulta TitulacionController::puedeIniciar(), así vale en todo el sistema:
 * guía de la intranet, formulario de solicitud e «Iniciar proceso».
 */
class Habilitacion extends Model
{
    protected $table = 'titulacion_habilitaciones';

    protected $fillable = ['user_id', 'motivo', 'habilitado_por'];

    /** Ids habilitados, cargados una sola vez por petición (puedeIniciar se llama por cada alumno de un listado). */
    private static ?array $ids = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function habilitadoPor()
    {
        return $this->belongsTo(User::class, 'habilitado_por');
    }

    public static function tiene(User $u): bool
    {
        // Si la tabla aún no existe (SQL sin correr), nadie está habilitado: el sistema sigue igual.
        self::$ids ??= self::tablaLista() ? self::pluck('user_id')->flip()->all() : [];

        return isset(self::$ids[$u->id]);
    }

    /** Tras habilitar o quitar una habilitación, para que la misma petición vea el cambio. */
    public static function olvidar(): void
    {
        self::$ids = null;
    }

    private static function tablaLista(): bool
    {
        static $lista = null;

        return $lista ??= Schema::hasTable('titulacion_habilitaciones');
    }
}
