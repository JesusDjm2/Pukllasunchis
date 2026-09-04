<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlumnoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'dni' => $this->dni,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'genero' => $this->genero,
            'foto' => $this->foto,
            'numero' => $this->numero,
            'numero_referencia' => $this->numero_referencia,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'lugar_nacimiento' => $this->lugar_nacimiento,
            'departamento' => $this->departamento,
            'provincia' => $this->provincia,
            'distrito' => $this->distrito,
            'direccion' => $this->direccion,
            'permanencia_vivienda' => $this->permanencia_vivienda,
            'estado_civil' => $this->estado_civil,
            'p_m_soltero' => $this->p_m_soltero,
            'num_hijos' => $this->num_hijos,
            'sector_socioeconomico' => $this->sector_socioeconomico,
            'trabajas' => $this->trabajas,
            'donde_trabajas' => $this->donde_trabajas,
            'ingreso_mensual' => $this->ingreso_mensual,
            'egreso' => $this->egreso,
            'hrs_laboradas_sem' => $this->hrs_laboradas_sem,
            'sector_laboral' => $this->sector_laboral,
            'ayuda_economica' => $this->ayuda_economica,
            'tiempo_ayuda' => $this->tiempo_ayuda,
            'tipo_apoyo_formacion' => $this->tipo_apoyo_formacion,
            'convivientes' => $this->convivientes,
            'quien_mantiene' => $this->quien_mantiene,
            'cant_dependientes_child' => $this->cant_dependientes_child,
            'cant_dependientes_old' => $this->cant_dependientes_old,
            'cant_dependientes_otros' => $this->cant_dependientes_otros,
            'tipo_vivienda' => $this->tipo_vivienda,
            'situacion_vivienda' => $this->situacion_vivienda,
            'dormitorios_vivienda' => $this->dormitorios_vivienda,
            'banos_vivienda' => $this->banos_vivienda,
            'material_vivienda' => $this->material_vivienda,
            'bienes_vivienda' => $this->bienes_vivienda,
            'hrs_disponibles_agua' => $this->hrs_disponibles_agua,
            'hrs_disponibles_desague' => $this->hrs_disponibles_desague,
            'hrs_disponibles_luz' => $this->hrs_disponibles_luz,
            'otros_servicios' => $this->otros_servicios,
            'tipo_seguro' => $this->tipo_seguro,
            'estudio_beca' => $this->estudio_beca,
            'programa_id' => $this->programa_id,
            'ciclo_id' => $this->ciclo_id,
        ];
    }
}
