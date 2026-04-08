<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostulantesRegular extends Model
{
    use HasFactory;
    protected $table = 'postulantes_regulars';

    protected $fillable = [
        'email',
        'programa',
        'estudio_beca',
        'apellidos',
        'nombres',
        'dni',
        'genero',
        'direccion',
        'numero',
        'apto',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'distrito_nacimiento',
        'provincia_nacimiento',
        'departamento_nacimiento',
        'contacto',
        'colegio',
        'codigo_colegio',
        'gestion_colegio',
        'direccion_colegio',
        'distrito_colegio',
        'provincia_colegio',
        'departamento_colegio',
        'ano_termino_colegio',
        'promedio_colegio',
        'lengua_1',
        'lengua_2',

        'etnicoidad',
        'convertido',

        'nivel_quechua_hablado',
        'nivel_quechua_escrito',
        'estado_civil',
        'num_hijos',
        'trabajas',
        'donde_trabajas',
        'cargo_trabajas',
        'describe_eespp',
        'dni_pdf',
        'partida_nacimiento_pdf',
        'certificado_secundaria_pdf',
        'foto',
        'voucher_pago',
        'observaciones',
        'admin_fids_id',
        'constancia',
        
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'genero' => 'boolean',
        'trabajas' => 'boolean',
        'declaracion_veracidad' => 'boolean',
        'apto' => 'boolean',
        'apto2' => 'boolean',
        'convertido' => 'boolean',
    ];

    public function getEdadAttribute()
    {
        if (! $this->fecha_nacimiento) {
            return null;
        }

        return Carbon::parse($this->fecha_nacimiento)->age;
    }
    public static function generarConstancia(): string
    {
        $prefijo = 'FID';
        $model = new self;
        $table = $model->getTable();
        $maxNum = DB::table($table)
            ->whereNotNull('constancia')
            ->where('constancia', 'like', $prefijo.'%')
            ->select(DB::raw('MAX(CAST(SUBSTRING(constancia, 4) AS UNSIGNED)) as maxnum'))
            ->value('maxnum');

        $next = ($maxNum ? intval($maxNum) + 1 : 1);

        return $prefijo.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
    public function periodoAdmision()
    {
        return $this->belongsTo(AdminFid::class);
    }
}
