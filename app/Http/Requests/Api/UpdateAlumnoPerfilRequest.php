<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlumnoPerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->alumno !== null;
    }

    public function rules(): array
    {
        $alumno = $this->user()->alumno;

        $rules = [
            'numero' => 'required|string|max:20',
            'numero_referencia' => 'required|string|max:20',
            'direccion' => 'required|string|max:255',
            'departamento' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'distrito' => 'nullable|string|max:100',
            'estado_civil' => 'required|string',
            'p_m_soltero' => 'required|boolean',
            'num_hijos' => 'required|integer|min:0',
            'sector_socioeconomico' => 'required|string',
            'trabajas' => 'required|string',
            'donde_trabajas' => 'nullable|string|max:255',
            'ingreso_mensual' => 'nullable|string|max:100',
            'egreso' => 'required|string|max:100',
            'hrs_laboradas_sem' => 'required|integer|min:0',
            'sector_laboral' => 'nullable|string|max:100',
            'ayuda_economica' => 'required|boolean',
            'tiempo_ayuda' => 'required|string|max:100',
            'tipo_apoyo_formacion' => 'required|string|max:100',
            'convivientes' => 'required|string|max:255',
            'quien_mantiene' => 'required|string|max:255',
            'cant_dependientes_child' => 'required|string|max:50',
            'cant_dependientes_old' => 'required|string|max:50',
            'cant_dependientes_otros' => 'required|string|max:50',
            'tipo_vivienda' => 'required|string',
            'situacion_vivienda' => 'required|string',
            'dormitorios_vivienda' => 'required|integer|min:0',
            'banos_vivienda' => 'required|integer|min:0',
            'material_vivienda' => 'required|string',
            'hrs_disponibles_agua' => 'required|integer|min:0',
            'hrs_disponibles_desague' => 'required|integer|min:0',
            'hrs_disponibles_luz' => 'required|integer|min:0',
            'tipo_seguro' => 'required|string',
            'bienes_vivienda' => 'required|array|min:1',
            'otros_servicios' => 'nullable|array',
            'estudio_beca' => 'required',
            'permanencia_vivienda' => 'required|string',
        ];

        if (! $alumno->alumnoTieneFechaNacimientoCapturada()) {
            $rules['fecha_nacimiento'] = 'required|date';
        }
        if (! $alumno->genero) {
            $rules['genero'] = 'required|string';
        }

        return $rules;
    }
}
