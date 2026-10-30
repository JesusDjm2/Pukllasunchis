<?php

namespace App\Models\RicardoPalma;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpImportacion extends Model
{
    use HasFactory;

    protected $table = 'rp_importaciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'archivo_nombre',
        'archivo_path',
        'periodo_id',
        'programa_id',
        'numero_semestre',
        'total_filas',
        'filas_exitosas',
        'filas_con_error',
        'detalles_errores',
        'estado',
    ];

    protected $casts = [
        'total_filas' => 'integer',
        'filas_exitosas' => 'integer',
        'filas_con_error' => 'integer',
        'detalles_errores' => 'array',
        'numero_semestre' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(RpPeriodo::class, 'periodo_id');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(RpPrograma::class, 'programa_id');
    }
}
