<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriculaPpd extends Model
{
    use HasFactory;

    protected $table = 'matriculas_ppd';

    protected $fillable = [
        'ppd_id',
        'periodo_actual_ppd_id',
        'fecha_completado',
        'estado',
        'comprobante',
        'voucher_imagen',
        'voucher_verificado',
        'voucher_verificado_at',
        'ficha_enviada_at',
    ];

    protected $casts = [
        'fecha_completado' => 'datetime',
        'voucher_verificado' => 'boolean',
        'voucher_verificado_at' => 'datetime',
        'ficha_enviada_at' => 'datetime',
    ];

    public function ppd()
    {
        return $this->belongsTo(ppd::class, 'ppd_id');
    }

    public function periodoActualPpd()
    {
        return $this->belongsTo(PeriodoActualPpd::class);
    }
}
