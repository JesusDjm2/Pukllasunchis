<?php

namespace App\Services\SeguimientoEgresado;

use App\Models\User;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PerfilTerritorialService
{
    public function validar(array $input, bool $borrador = false): array
    {
        $rules = [
            'contacto_alterno'=>'sometimes|array:autorizado,nombre,telefono,correo',
            'contacto_alterno.autorizado'=>'nullable|boolean',
            'contacto_alterno.nombre'=>'nullable|string|max:150',
            'contacto_alterno.telefono'=>'nullable|string|max:30',
            'contacto_alterno.correo'=>'nullable|email|max:150',
            'contacto' => 'sometimes|array:nombre_preferido,correo,telefono,medio,programa,egreso',
            'contacto.nombre_preferido' => 'nullable|string|max:100',
            'contacto.correo' => 'nullable|email|max:150',
            'contacto.telefono' => 'nullable|string|max:30',
            'contacto.medio' => 'nullable|in:WhatsApp,Llamada,Correo',
            'contacto.programa' => 'nullable|string|max:100',
            'contacto.egreso' => 'nullable|integer|min:1950|max:'.date('Y'),
            'trabaja' => 'nullable|in:si,no,prefiero_no_responder',
            'busqueda' => 'nullable|in:primer_empleo,con_experiencia,otras_oportunidades,no_busco,prefiero_no_responder',
            'trabajos' => 'sometimes|array|max:10',
            'trabajos.*' => 'array:episodio_id,institucion,cargo,inicio,fin,educativo,docencia,modalidad,nivel,caracteristica,aula,area,eib,forma_eib,escenario,lenguas,codigo_modular,lugar',
            'trabajos.*.episodio_id' => 'nullable|integer|min:1',
            'trabajos.*.institucion' => 'nullable|string|max:150',
            'trabajos.*.cargo' => 'nullable|string|max:150',
            'trabajos.*.inicio' => 'nullable|date_format:Y-m-d',
            'trabajos.*.fin' => 'nullable|date_format:Y-m-d',
            'trabajos.*.educativo' => 'nullable|boolean',
            'trabajos.*.docencia' => 'nullable|boolean',
            'trabajos.*.modalidad' => 'nullable|in:EBR,EBA,EBE,Otra',
            'trabajos.*.nivel' => 'nullable|string|max:100',
            'trabajos.*.caracteristica' => 'nullable|in:unidocente,polidocente_multigrado,polidocente_completa,no_se',
            'trabajos.*.aula' => 'nullable|in:uniedad,multiedad,un_grado,multigrado,no_corresponde',
            'trabajos.*.area' => 'nullable|in:rural,urbana,no_se',
            'trabajos.*.eib' => 'nullable|in:si,no,no_se',
            'trabajos.*.forma_eib' => 'nullable|in:fortalecimiento,revitalizacion,no_se',
            'trabajos.*.escenario' => 'nullable|integer|between:1,5',
            'trabajos.*.lenguas' => 'nullable|string|max:500',
            'trabajos.*.codigo_modular' => 'nullable|regex:/^[0-9]{7}$/',
            'encuentros' => 'sometimes|array:lugar,laborales,fin_semana,variable',
            'encuentros.lugar' => 'nullable|string|max:500',
            'encuentros.laborales' => 'nullable|string|max:500',
            'encuentros.fin_semana' => 'nullable|string|max:500',
            'encuentros.variable' => 'nullable|boolean',
            'comunidad' => 'sometimes|array:vinculos,actividades,desde,influencia,relato,apoyo',
            'comunidad.vinculos' => 'nullable|array|max:5',
            'comunidad.vinculos.*' => 'in:origen,resido,trabajo,colaboro,otro',
            'comunidad.actividades' => 'nullable|string|max:2000',
            'comunidad.desde' => 'nullable|date_format:Y-m-d',
            'comunidad.influencia' => 'nullable|in:mucho,algo,poco,nada,no_preciso',
            'comunidad.relato' => 'nullable|string|max:2000',
            'comunidad.apoyo' => 'nullable|string|max:2000',
            'formacion' => 'sometimes|array:temas,prioridad,estudios,compartir',
            'formacion.temas' => 'nullable|string|max:1000',
            'formacion.prioridad' => 'nullable|string|max:500',
            'formacion.estudios' => 'nullable|string|max:1000',
            'formacion.compartir' => 'nullable|string|max:2000',
            'apoyo' => 'sometimes|array:solicitar,tema,detalle',
            'apoyo.solicitar' => 'nullable|boolean',
            'apoyo.tema' => 'nullable|in:empleo,pedagogia,titulacion,formacion,bienestar,otro,privado',
            'apoyo.detalle' => 'nullable|string|max:2000',
        ];
        foreach (['residencia', 'trabajos.*.lugar'] as $prefix) {
            $rules[$prefix] = 'sometimes|array:departamento,provincia,distrito,localidad,ubigeo,comunidad_tipo,comunidad_nombre,lugar_id,latitud,longitud';
            foreach (['departamento','provincia','distrito','localidad','comunidad_nombre'] as $field) {
                $rules["$prefix.$field"] = 'nullable|string|max:80';
            }
            $rules["$prefix.ubigeo"] = 'nullable|regex:/^[0-9]{6}$/';
            $rules["$prefix.comunidad_tipo"] = 'nullable|in:campesina,nativa,no,no_se';
            $rules["$prefix.lugar_id"] = 'nullable|integer|exists:seguimiento_lugares,id';
            $rules["$prefix.latitud"] = 'nullable|numeric|between:-90,90';
            $rules["$prefix.longitud"] = 'nullable|numeric|between:-180,180';
        }
        $data = Validator::make($input, $rules)->validate();
        foreach ($data['trabajos'] ?? [] as $i => $job) {
            if (!$borrador && empty($job['episodio_id']) && (empty($job['institucion']) || empty($job['cargo']))) {
                throw ValidationException::withMessages(["trabajos.$i.institucion" => 'Indica institución y función para registrar un empleo nuevo.']);
            }
            if (!empty($job['inicio']) && !empty($job['fin']) && $job['fin'] < $job['inicio']) {
                throw ValidationException::withMessages(["trabajos.$i.fin" => 'El fin debe ser posterior al inicio.']);
            }
            $scenario = $job['escenario'] ?? null;
            $form = $job['forma_eib'] ?? null;
            if ($scenario && (($form === 'fortalecimiento' && $scenario > 2) || ($form === 'revitalizacion' && $scenario < 3))) {
                throw ValidationException::withMessages(["trabajos.$i.escenario" => 'El escenario no corresponde a la forma de atención EIB (RM 389-2024-MINEDU).']);
            }
        }
        return $data;
    }

    public function actual(int $userId): array
    {
        $row = DB::table('egresado_perfil_versiones')->where('user_id', $userId)->latest('id')->first();
        return $row ? json_decode($row->datos, true) : [];
    }

    public function lugar(array $place): array
    {
        $place['precision'] = 'sin_coordenadas';
        $place['fuente'] = 'declaracion_egresado';
        if (!empty($place['lugar_id'])) {
            $official = DB::table('seguimiento_lugares')->find($place['lugar_id']);
            if ($official) {
                foreach (['departamento','provincia','distrito','ubigeo','latitud','longitud','fuente','fecha_fuente'] as $key) {
                    $place[$key] = $official->$key;
                }
                if ($official->tipo === 'localidad') $place['localidad'] = $official->nombre;
                $place['precision'] = $official->tipo;
            }
        } elseif (isset($place['latitud'], $place['longitud'])) {
            $place['precision'] = 'declarada_pendiente';
        }
        if (!isset($place['latitud'], $place['longitud'])) {
            $place['latitud'] = $place['longitud'] = null;
            $place['precision'] = 'sin_coordenadas';
        }
        // La comunidad se declara por separado: una coordenada no acredita pertenencia.
        if (!in_array($place['comunidad_tipo'] ?? null, ['campesina','nativa'])) $place['comunidad_nombre'] = null;
        return $place;
    }

    public function guardar(int $userId, array $input, ?int $campanaId = null): array
    {
        $data = $this->validar($input);
        if (!$campanaId && (int)auth()->id()===$userId) {
            $vigentes=\App\Models\SeguimientoEgresado\AsignacionCampana::activos()->where('egresado_user_id',$userId)->whereHas('campana',fn($q)=>$q->whereIn('estado',['activa','activo'])->where(fn($d)=>$d->whereNull('fecha_inicio')->orWhere('fecha_inicio','<=',now()->toDateString()))->where(fn($d)=>$d->whereNull('fecha_fin')->orWhere('fecha_fin','>=',now()->toDateString())))->pluck('campana_id')->unique();
            // No adivinar atribución cuando concurren varias campañas.
            if($vigentes->count()===1) $campanaId=(int)$vigentes->first();
        }
        return DB::transaction(function () use ($userId, $data, $campanaId) {
            if ($campanaId) {
                $c=\App\Models\SeguimientoEgresado\Campana::lockForUpdate()->findOrFail($campanaId);
                abort_unless(in_array(strtolower($c->estado),['activo','activa']) && (!$c->fecha_inicio || $c->fecha_inicio<=now()->toDateString()) && (!$c->fecha_fin || $c->fecha_fin>=now()->toDateString()),422,'La campaña ya no está vigente.');
            }
            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            $previous = $this->actual($userId);
            $profile = array_replace_recursive($previous, $data);
            if (array_key_exists('trabajos', $data)) $profile['trabajos'] = $data['trabajos'];
            if (isset($data['comunidad']['vinculos'])) $profile['comunidad']['vinculos'] = $data['comunidad']['vinculos'];
            if (isset($data['residencia'])) $profile['residencia'] = $this->lugar($data['residencia']);
            foreach ($data['trabajos'] ?? [] as $i => $job) {
                $job['lugar'] = $this->lugar($job['lugar'] ?? []);
                if (($job['eib'] ?? null) !== 'si') { $job['forma_eib'] = null; $job['escenario'] = null; }
                if (!empty($job['episodio_id'])) {
                    $episode = EpisodioLaboral::where('user_id', $userId)->findOrFail($job['episodio_id']);
                } else {
                    $episode = new EpisodioLaboral(['user_id' => $userId]);
                }
                $episode->fill([
                    'institucion' => $job['institucion'] ?? $episode->institucion, 'cargo' => $job['cargo'] ?? $episode->cargo,
                    'fecha_inicio' => $job['inicio'] ?? null, 'fecha_fin' => $job['fin'] ?? null,
                    'es_actual' => (empty($job['fin']) || $job['fin'] >= now()->toDateString()) && (empty($job['inicio']) || $job['inicio'] <= now()->toDateString()),
                    'es_empleo_educativo' => (bool) ($job['educativo'] ?? false),
                    'es_docencia_directa' => (bool) ($job['docencia'] ?? false),
                    'trab_departamento' => $job['lugar']['departamento'] ?? null,
                    'trab_provincia' => $job['lugar']['provincia'] ?? null,
                    'trab_distrito' => $job['lugar']['distrito'] ?? null,
                    'fecha_captura' => now(), 'canal_registro' => 'mi_espacio',
                ])->save();
                $job['episodio_id'] = $episode->id;
                $profile['trabajos'][$i] = $job;
            }
            // Mantener compatibilidad con padrón y analítica existentes sin duplicar personas.
            $last = Actualizacion::where('user_id', $userId)->where('estado_verificacion', 'verificado')->latest('id')->first();
            $legacy = $last ? $last->replicate() : new Actualizacion(['user_id' => $userId]);
            $legacy->fill([
                'user_id' => $userId, 'nombre_completo' => trim(User::findOrFail($userId)->name.' '.User::findOrFail($userId)->apellidos),
                'campana_id' => $campanaId, 'es_ensayo' => $campanaId ? $c->es_ensayo : false,
                'periodo_campana' => now()->year.'-'.(now()->month <= 6 ? 'I' : 'II'),
                'tipo_registro' => 'respuesta_campana', 'estado_verificacion' => 'verificado',
                'procedencia' => (int) auth()->id() === $userId ? 'intranet_autogestion' : 'entrevista_docente', 'canal' => (int) auth()->id() === $userId ? 'mi_espacio' : 'llamada_docente',
                'fecha_ultima_confirmacion' => now(), 'es_confirmacion_sin_cambios' => false,
                'programa' => $profile['contacto']['programa'] ?? $last?->programa,
                'cohorte_egreso' => $profile['contacto']['egreso'] ?? $last?->cohorte_egreso,
                'correo' => $profile['contacto']['correo'] ?? null, 'telefono' => $profile['contacto']['telefono'] ?? null,
                'medio_preferido' => $profile['contacto']['medio'] ?? null,
                'res_departamento' => $profile['residencia']['departamento'] ?? null,
                'res_provincia' => $profile['residencia']['provincia'] ?? null,
                'res_distrito' => $profile['residencia']['distrito'] ?? null,
                'trabaja' => match ($profile['trabaja'] ?? null) { 'si' => 'Trabaja', 'no' => 'No trabaja', default => 'Prefiere no responder' },
                'temas_interes' => $profile['formacion']['temas'] ?? null,
                'puedo_compartir' => $profile['formacion']['compartir'] ?? null,
            ])->save();
            // Omitir un empleo en el formulario no lo cierra ni lo borra: requiere fecha de fin explícita.
            DB::table('egresado_perfil_versiones')->insert([
                'user_id' => $userId, 'registrado_por' => auth()->id(),
                'datos' => json_encode($profile, JSON_UNESCAPED_UNICODE),
                'confirmado_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $support = $data['apoyo'] ?? [];
            if (!empty($support['solicitar']) && !empty($support['tema']) && $support !== ($previous['apoyo'] ?? [])) {
                $user = User::findOrFail($userId);
                Caso::create([
                    'codigo' => 'APOYO-'.bin2hex(random_bytes(8)), 'user_id' => $userId,
                    'nombre_egresado' => trim($user->name.' '.$user->apellidos),
                    'tema' => $support['tema'], 'responsable' => in_array($support['tema'], ['bienestar','privado']) ? 'Bienestar' : 'Coordinación de Empleabilidad',
                    'fecha_gestion' => now()->addDays(7)->toDateString(), 'estado' => 'Por revisar',
                    'proposito' => $support['detalle'] ?? 'Solicita conversar.',
                    'es_confidencial_bienestar' => in_array($support['tema'], ['bienestar','privado']),
                    'origen' => 'Mi espacio',
                ]);
            }
            if ((int) auth()->id() === $userId) DB::table('egresado_perfil_borradores')->where('user_id', $userId)->delete();
            return $profile;
        });
    }

    public function desdeActualizacion(Actualizacion $actualizacion): void
    {
        if (!$actualizacion->user_id || $actualizacion->estado_verificacion !== 'verificado'
            || !\Illuminate\Support\Facades\Schema::hasTable('egresado_perfil_versiones')) return;
        $p = $this->actual((int) $actualizacion->user_id);
        foreach (['correo','telefono','programa'] as $key) if ($actualizacion->$key !== null) $p['contacto'][$key] = $actualizacion->$key;
        if ($actualizacion->cohorte_egreso) $p['contacto']['egreso'] = (int) $actualizacion->cohorte_egreso;
        foreach (['departamento','provincia','distrito'] as $key) {
            $value = $actualizacion->{'res_'.$key};
            if ($value !== null && $value !== ($p['residencia'][$key] ?? null)) {
                unset($p['residencia']['lugar_id'], $p['residencia']['latitud'], $p['residencia']['longitud']);
                $p['residencia']['precision'] = 'sin_coordenadas';
                $p['residencia'][$key] = $value;
            }
        }
        if ($actualizacion->trabaja) $p['trabaja'] = $actualizacion->trabaja === 'No trabaja' ? 'no' : (str_starts_with($actualizacion->trabaja, 'Trabaja') ? 'si' : 'prefiero_no_responder');
        DB::table('egresado_perfil_versiones')->insert([
            'user_id' => $actualizacion->user_id, 'registrado_por' => auth()->id(),
            'datos' => json_encode($p, JSON_UNESCAPED_UNICODE), 'confirmado_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function avance(array $p): array
    {
        // Solo núcleo pertinente. Comunidad, bienestar, disponibilidad y presentación son voluntarios.
        $fields = ['contacto.programa','contacto.egreso','contacto.correo','residencia.departamento','residencia.provincia','residencia.distrito','trabaja'];
        if (($p['trabaja'] ?? null) === 'si') $fields = array_merge($fields, ['trabajos.0.institucion','trabajos.0.cargo','trabajos.0.lugar.distrito']);
        $pending = array_values(array_filter($fields, fn ($f) => data_get($p, $f) === null || data_get($p, $f) === ''));
        return ['porcentaje' => (int) round(100 * (count($fields) - count($pending)) / count($fields)), 'pendientes' => $pending];
    }
}
