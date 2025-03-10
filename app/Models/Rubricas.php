<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubricas extends Model
{
    use HasFactory;
    protected $fillable = [
        'silabo_id',
        'criterio',
        'destacado',
        'logrado',
        'proceso',
        'inicio'
    ];

    public function silabo()
    {
        return $this->belongsTo(Silabo::class);
    }
}
