<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoActual extends Model
{
    use HasFactory;
    protected $table = 'periodo_actual';

    protected $fillable = [
        'nombre',
        'actual',
    ];

    public function periodos()
    {
        return $this->hasMany(Periodo::class);
    }
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->actual) {
                $exists = static::where('id', '!=', $model->id)
                    ->where('actual', true)
                    ->exists();
                if ($exists) {
                    static::where('id', '!=', $model->id)
                        ->update(['actual' => false]);
                    $model->wasReplacing = true;
                }
            }
        });
    }
}
