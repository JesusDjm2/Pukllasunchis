<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;
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

    public static function asociarPorEmail($email)
    {
        $alumno = static::where('email', $email)->first();

        if ($alumno) {
            $usuario = User::where('email', $email)->first();

            if ($usuario) {
                $alumno->user_id = $usuario->id;
                $alumno->save();
            }
        }
    }
    protected $fillable = [
        'email',
        'dni',
        'apellidos',
        'nombres',
        'numero',
        'numero_referencia',
        'user_id',
        'programa_id',
        'ciclo_id',
        'procedencia_familiar',
        'direccion',
        'te_consideras',
        'lengua_1',
        'lengua_2',
        'estado_civil',
        'p_m_soltero',
        'num_hijos',
        'sector_socioeconomico',
        'num_comprobante',
        //Datos Familiares
        'convivientes',
        'quien_mantiene',
        'cant_dependientes_child',
        'cant_dependientes_old',
        'cant_dependientes_otros',
        //Aspectos Educativos
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
        //Aspectos Socieconómicos
        'trabajas',
        'donde_trabajas',
        'ingreso_mensual',
        'egreso',
        'hrs_laboradas_sem',
        'ayuda_economica',
        'tiempo_ayuda',
        'tipo_apoyo_formacion',
        //Aspectos Vivienda
        'tipo_vivienda',
        'situacion_vivienda',
        'dormitorios_vivienda',
        'banos_vivienda',
        'material_vivienda',
        'bienes_vivienda',
        'hrs_disponibles_agua',
        'hrs_disponibles_desague',
        'hrs_disponibles_luz',
        'otros_servicios',
        //Aspectos Salud
        'problemas_salud',
        'ultima_consulta',
        'motivo_consulta',
        'tipo_seguro',
        'familiar_salud',
        //Aspectos Culturales
        'frecuencia_lectura',
        'acceso_lectura',
        'visitas_museos',
        //Adicionales
        'actividades_internet',
        'habilidades',
        'tiempo_libre',
    ];
    public static function getValidationRules($updating = false, $id = null)
    {
        $rules = [
            'programa_id' => 'required|exists:programas,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'email' => 'required|email|unique:alumnos,email',
            'dni' => 'required|unique:alumnos,dni',
            'nombres' => 'required',
            'apellidos' => 'required',
            'numero' => 'required|string|max:255',
            'numero_referencia' => 'required|string|max:255',
            'procedencia_familiar' => 'required',
            'direccion' => 'required|string|max:255',
            'te_consideras' => 'required',
            'lengua_1' => 'required',
            'lengua_2' => 'required',
            'estado_civil' => 'required',
            'p_m_soltero' => 'required|boolean',
            'num_hijos' => 'required|integer',
            'sector_socioeconomico' => 'required',
            //Datos Familiares
            'convivientes' => 'required|string|max:255',
            'quien_mantiene' => 'required|string|max:255',
            'cant_dependientes_child' => 'required|string|max:255',
            'cant_dependientes_old' => 'required|string|max:255',
            'cant_dependientes_otros' => 'required|string|max:255',
            //Datos Educativos
            'estudio_beca' => 'required',
            'origen_beca' => 'nullable|string|max:255',
            'postulaciones_eesp' => 'nullable|string|max:255',
            'postulaciones_inst_uni' => 'nullable|string|max:255',
            'postulaciones_otros' => 'nullable|string|max:255',
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
            'num_hrs_estudio' => 'required|integer',
            'forma_estudio' => 'required|string|max:255',
            //Aspectos Socioeconómicos
            'trabajas' => 'required|boolean',
            'donde_trabajas' => 'nullable|string|max:255',
            'ingreso_mensual' => 'nullable|string|max:255',
            'egreso' => 'required|string|max:255',
            'hrs_laboradas_sem' => 'required|integer',
            'ayuda_economica' => 'required|boolean',
            'tiempo_ayuda' => 'required|string|max:255',
            'tipo_apoyo_formacion' => 'required|string|max:255',
            //Aspectos Vivienda
            'tipo_vivienda' => 'required',
            'situacion_vivienda' => 'required',
            'dormitorios_vivienda' => 'required|string|max:255',
            'banos_vivienda' => 'required|string|max:255',
            'material_vivienda' => 'required',
            'bienes_vivienda' => 'nullable|array',
            'hrs_disponibles_agua' => 'required|string|max:255',
            'hrs_disponibles_desague' => 'required|string|max:255',
            'hrs_disponibles_luz' => 'required|string|max:255',
            'otros_servicios' => 'nullable|array',
            //Aspectos Salud
            'problemas_salud' => 'required|boolean',
            'ultima_consulta' => 'nullable|boolean',
            'motivo_consulta' => 'nullable|string',
            'tipo_seguro' => 'required|string',
            'familiar_salud' => 'required|boolean',
            //Aspectos Culturales
            'frecuencia_lectura' => 'required',
            'acceso_lectura' => 'required',
            'visitas_museos' => 'required',
            //Información Adicional
            'actividades_internet' => 'required',
            'habilidades' => 'nullable|array',
            'tiempo_libre' => 'required|boolean',
        ];

        if ($updating) {
            $rules['email'] .= ',' . $id;
            $rules['dni'] .= ',' . $id;
        }

        return $rules;
    }
}
