<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    protected $fillable = [
        'source',
        'scope',
        'title',
        'content',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];
}
