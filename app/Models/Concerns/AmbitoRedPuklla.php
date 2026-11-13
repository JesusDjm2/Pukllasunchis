<?php
namespace App\Models\Concerns;
use Illuminate\Database\Eloquent\Builder;
trait AmbitoRedPuklla {
 protected static function bootAmbitoRedPuklla():void {static::addGlobalScope('red-puklla',fn(Builder $q)=>$q->where($q->getModel()->qualifyColumn('red_egresados'),false));}
 public function scopeDeRedPuklla(Builder $q):Builder{return $q->withoutGlobalScope('red-puklla')->where($q->getModel()->qualifyColumn('red_egresados'),true);}
}
