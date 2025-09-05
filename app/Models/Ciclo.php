<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Ciclo extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'programa_id'];

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }
    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }
    public function alumnosB()
    {
        return $this->hasMany(ppd::class, 'ciclo_id');
    }
    public function cursos()
    {
        return $this->hasMany(Curso::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function estandares()
    {
        return $this->belongsToMany(Estandares::class, 'ciclo_competencia_estandar')
            ->withPivot('competencia_id')
            ->withTimestamps();
    }

    public function scopeOrdered($query)
    {
        return $query->get()->sortBy(function ($ciclo) {
            return self::romanToInt($ciclo->nombre);
        });
    }
   
    private static function romanToInt($roman)
    {
        $map = [
            'I' => 1,
            'V' => 5,
            'X' => 10,
            'L' => 50,
            'C' => 100,
            'D' => 500,
            'M' => 1000,
        ];

        $result = 0;
        $prev = 0;
        $roman = strtoupper(trim($roman));

        for ($i = strlen($roman) - 1; $i >= 0; $i--) {
            $value = $map[$roman[$i]] ?? 0;
            $result += ($value < $prev) ? -$value : $value;
            $prev = $value;
        }

        return $result;
    }

    public function ordenCiclo()
    {
        $map = [
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8,
            'IX' => 9,
            'X' => 10,
        ];
        return $map[strtoupper(trim($this->nombre))] ?? 999; // default grande por si no calza
    }

}
