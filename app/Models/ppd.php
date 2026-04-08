<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ppd extends Model
{
    use HasFactory;

    protected $table = 'ppds';

    protected $fillable = [
        'email',
        'dni',
        'programa_id',
        'ciclo_id',
        'nombres',
        'apellidos',
        'numero',
        'numero_referencia',
        'procedencia_familiar',
        'direccion',
        'lugar_nacimiento',
        'permanencia_vivienda',
        'te_consideras',
        'lengua_1',
        'lengua_2',
        'estado_civil',
        'p_m_soltero',
        'num_hijos',
        'sector_socioeconomico',
        'num_comprobante',
        'convivientes',
        'quien_mantiene',
        'cant_dependientes_child',
        'cant_dependientes_old',
        'cant_dependientes_otros',
        'carrera_procedencia',
        'ano_culminaste',
        'institucion_procedencia',
        'gestion_institucion',
        'direccion_institucion',
        'dep_dist_prov_institucion',
        'estudio_beca',
        'origen_beca',
        'postulaciones_eesp',
        'postulaciones_inst_uni',
        'postulaciones_otros',
        'tipo_preparacion',
        'motivo_estudio_eesp',
        'motivo_docencia',
        'motivo_especialidad',
        'internet',
        'internet_lugar',
        'servicio_internet',
        'dispositivo_internet',
        'propio_compartido',
        'correo',
        'num_hrs_estudio',
        'forma_estudio',
        'trabajas',
        'sector_laboral',
        'donde_trabajas',
        'ingreso_mensual',
        'egreso',
        'hrs_laboradas_sem',
        'ayuda_economica',
        'tiempo_ayuda',
        'tipo_apoyo_formacion',
        'tipo_vivienda',
        'situacion_vivienda',
        'dormitorios_vivienda',
        'banos_vivienda',
        'material_vivienda',
        'hrs_disponibles_agua',
        'hrs_disponibles_desague',
        'hrs_disponibles_luz',
        'bienes_vivienda',
        'otros_servicios',
        'problemas_salud',
        'ultima_consulta',
        'tipo_seguro',
        'familiar_salud',
        'frecuencia_lectura',
        'acceso_lectura',
        'visitas_museos',
        'actividades_internet',
        'habilidades',
        'tiempo_libre',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacionesppd::class);
    }

    public static function asociarPorEmail($email)
    {
        $ppd = static::where('email', $email)->first(); // Buscar en la tabla 'ppds'
        if ($ppd) {
            $usuario = User::where('email', $email)->first();
            if ($usuario) {
                $ppd->user_id = $usuario->id;
                $ppd->save();
            }
        }
    }

    public static function getValidationRules($updating = false, $id = null)
    {
        $rules = [
            // Datos básicos
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'email' => 'required|email|unique:ppds,email',
            'dni' => 'required|unique:ppds,dni',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'genero' => 'nullable|string|max:50', // ← AGREGADO
            'numero' => 'required|string|max:255',
            'numero_referencia' => 'required|string|max:255',
            'departamento' => 'nullable|string|max:255', // ← AGREGADO
            'provincia' => 'nullable|string|max:255', // ← AGREGADO
            'distrito' => 'nullable|string|max:255', // ← AGREGADO
            'fecha_nacimiento' => 'nullable|date', // ← AGREGADO
            'procedencia_familiar' => 'required',
            'permanencia_vivienda' => 'nullable|string|max:255',
            'sector_laboral' => 'nullable|string|max:255',
            'otro_sector' => 'nullable|string|max:255',
            'lugar_nacimiento' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'te_consideras' => 'required',
            'lengua_1' => 'required',
            'lengua_2' => 'required',
            'estado_civil' => 'required',
            'p_m_soltero' => 'required|boolean',
            'num_hijos' => 'required|integer|min:0',
            'sector_socioeconomico' => 'required',
            'num_comprobante' => 'nullable|string|max:255', // ← AGREGADO

            // Datos Familiares
            'convivientes' => 'required|string|max:255',
            'quien_mantiene' => 'required|string|max:255',
            'cant_dependientes_child' => 'required|integer|min:0', // ← CAMBIADO A INTEGER
            'cant_dependientes_old' => 'required|integer|min:0', // ← CAMBIADO A INTEGER
            'cant_dependientes_otros' => 'required|integer|min:0', // ← CAMBIADO A INTEGER

            // Datos Educativos
            'carrera_procedencia' => 'nullable|string|max:255', // ← AGREGADO
            'ano_culminaste' => 'nullable|integer|min:1900|max:'.date('Y'), // ← AGREGADO
            'institucion_procedencia' => 'nullable|string|max:255', // ← AGREGADO
            'gestion_institucion' => 'nullable|string|max:255', // ← AGREGADO
            'direccion_institucion' => 'nullable|string|max:255', // ← AGREGADO
            'dep_dist_prov_institucion' => 'nullable|string|max:255', // ← AGREGADO
            'estudio_beca' => 'required',
            'origen_beca' => 'nullable|string|max:255',
            'postulaciones_eesp' => 'nullable|integer|min:0', // ← CAMBIADO A INTEGER
            'postulaciones_inst_uni' => 'nullable|integer|min:0', // ← CAMBIADO A INTEGER
            'postulaciones_otros' => 'nullable|integer|min:0', // ← CAMBIADO A INTEGER
            'tipo_preparacion' => 'required',
            'motivo_estudio_eesp' => 'required',
            'motivo_docencia' => 'required',
            'motivo_especialidad' => 'required',
            'internet' => 'required|boolean',
            'internet_lugar' => 'nullable|string|max:255',
            'servicio_internet' => 'required|string|max:255',
            'dispositivo_internet' => 'required|string|max:255',
            'propio_compartido' => 'required|boolean',
            'correo' => 'required|boolean',
            'num_hrs_estudio' => 'required|integer|min:0',
            'forma_estudio' => 'required|string|max:255',

            // Aspectos Socioeconómicos
            'trabajas' => 'required|string|max:255',
            'donde_trabajas' => 'nullable|string|max:255',
            'ingreso_mensual' => 'nullable|string|min:0', // ← CAMBIADO A NUMERIC
            'egreso' => 'required|string|max:255',
            'hrs_laboradas_sem' => 'required|integer|min:0|max:168',
            'ayuda_economica' => 'required|boolean',
            'tiempo_ayuda' => 'required|string|max:255',
            'tipo_apoyo_formacion' => 'required|string|max:255',

            // Aspectos Vivienda
            'tipo_vivienda' => 'required',
            'situacion_vivienda' => 'required',
            'dormitorios_vivienda' => 'required|integer|min:0', // ← CAMBIADO A INTEGER
            'banos_vivienda' => 'required|integer|min:0', // ← CAMBIADO A INTEGER
            'material_vivienda' => 'required',
            'bienes_vivienda' => 'nullable|array',
            'hrs_disponibles_agua' => 'required|string|max:255',
            'hrs_disponibles_desague' => 'required|string|max:255',
            'hrs_disponibles_luz' => 'required|string|max:255',
            'otros_servicios' => 'nullable|array',

            // Aspectos Salud
            'problemas_salud' => 'required|boolean',
            'ultima_consulta' => 'nullable|boolean',
            'motivo_consulta' => 'nullable|string|max:500',
            'tipo_seguro' => 'required|string|max:255',
            'familiar_salud' => 'required|boolean',

            // Aspectos Culturales
            'frecuencia_lectura' => 'required',
            'acceso_lectura' => 'required',
            'visitas_museos' => 'required',

            // Información Adicional
            'actividades_internet' => 'required',
            'habilidades' => 'nullable|array',
            'tiempo_libre' => 'required|boolean',
            'guardados' => 'nullable|boolean',
        ];

        if ($updating) {
            $rules['email'] .= ','.$id;
            $rules['dni'] .= ','.$id;
        }

        return $rules;
    }

    // Agregar mensajes personalizados
    public static function getValidationMessages()
    {
        return [
            // Ejemplos de mensajes amigables
            'ano_culminaste.required' => 'El año de culminación es obligatorio.',
            'ano_culminaste.integer' => 'El año debe ser un número válido.',
            'ano_culminaste.min' => 'El año debe ser mayor o igual a 1900.',
            'ano_culminaste.max' => 'El año no puede ser mayor a :max.',

            'dni.required' => 'El DNI es obligatorio.',
            'dni.unique' => 'Este DNI ya está registrado.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',

            'num_hijos.integer' => 'El número de hijos debe ser un valor numérico.',
            'num_hijos.min' => 'El número de hijos no puede ser negativo.',
        ];
    }
}
