<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostulantesPpd extends Model
{
    use HasFactory;

    protected $table = 'postulantes_ppds';

    protected $fillable = [
        // Datos principales
        'email',
        'programa',
        'apellidos',
        'nombres',
        'dni',
        'genero',

        // Datos personales
        'apto',
        'apto2',
        'convertido',
        'estadoCivil',
        'vecesPostulo',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'departamento_nacimiento',
        'provincia_nacimiento',
        'distrito_nacimiento',
        'edad',
        'hijos',
        'lengua_1',
        'lengua_2',
        'trabaja',
        'lugar_trabajo',
        'cargo',
        'opinionEespp',
        'carrera',

        // Contacto
        'domicilio',
        'telefono',

        // Procedencia académica
        'tipo_institucion',
        'nombre_institucion',
        'gestion_institucion',
        'direccion_institucion',
        'departamento_institucion',
        'provincia_institucion',
        'distrito_institucion',
        'anio_conclusion',
        'medio_conocimiento',

        // Archivos (se guarda la ruta, no el archivo)
        'dni_adjunto',
        'certificado',
        'foto',
        'titulo',
        'voucher',
        'admin_ppds_id',
    ];

    public function scopeMasRecientes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeOrdenarPorNombre($query)
    {
        return $query->orderBy('apellidos', 'asc')
            ->orderBy('nombres', 'asc');
    }

    public static function generarConstancia(): string
    {
        $prefijo = 'PPD';
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

    public function adminPpd()
    {
        return $this->belongsTo(AdminPpd::class, 'admin_ppds_id');
    }
}
