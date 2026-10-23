<?php

namespace App\Models\Titulacion;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{
    protected $table = 'titulacion_importaciones';

    protected $fillable = [
        'lote_importacion', 'origen', 'archivo_nombre', 'fila_num',
        'dni', 'nombre_estudiante', 'nombre_normalizado', 'user_id',
        'estado_match', 'datos_originales', 'datos_normalizados', 'importado_por',
    ];

    protected $casts = [
        'datos_originales' => 'array',
        'datos_normalizados' => 'array',
    ];

    private ?string $datosCrudos = null;

    private ?array $datosDecodificados = null;

    /**
     * El cast 'array' decodifica el JSON en CADA lectura, y el listado de Titulación lee los datos
     * de ~7.000 filas varias veces cada una: se decodifica una vez y se reutiliza mientras el JSON
     * guardado no cambie. Al asignar, el cast sigue codificando como siempre.
     */
    protected function datosNormalizados(): Attribute
    {
        return Attribute::make(get: function ($valor) {
            if ($valor !== $this->datosCrudos) {
                $this->datosCrudos = $valor;
                $this->datosDecodificados = $valor === null ? null : json_decode($valor, true);
            }

            return $this->datosDecodificados;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function importadoPor()
    {
        return $this->belongsTo(User::class, 'importado_por');
    }
}
