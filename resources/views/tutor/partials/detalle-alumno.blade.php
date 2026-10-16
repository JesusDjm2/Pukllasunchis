{{-- Ficha completa del alumno (para el tutor). Espera: $alumno (App\Models\Alumno). --}}
@php
    $siNo = function ($v) {
        if ($v === null || $v === '') {
            return '—';
        }

        return ($v === 1 || $v === '1' || $v === true) ? 'Si' : 'No';
    };

    $formateaLista = function ($raw, string $sep = ', ') {
        if (is_array($raw)) {
            $items = array_filter(array_map('trim', $raw));
        } else {
            $items = array_filter(array_map('trim', explode($sep, (string) $raw)));
        }

        return count($items) ? implode(', ', $items) : '—';
    };

    $fechaNac = $alumno->fechaNacimientoResueltaFormateada();
    $trabajaTxt = match (true) {
        $alumno->trabajas === 1, $alumno->trabajas === '1' => 'Si',
        $alumno->trabajas === 0, $alumno->trabajas === '0' => 'No',
        default => ($alumno->trabajas !== null && $alumno->trabajas !== '') ? (string) $alumno->trabajas : '—',
    };

    $seccionesDetalle = [
        [
            'titulo' => 'Datos Personales',
            'campos' => [
                ['k' => 'Nombres', 'v' => $alumno->nombres ?? '—'],
                ['k' => 'Apellidos', 'v' => $alumno->apellidos ?? '—'],
                ['k' => 'DNI', 'v' => $alumno->dni ?? '—'],
                ['k' => 'Genero', 'v' => $alumno->genero ?? '—'],
                ['k' => 'Fecha de nacimiento', 'v' => $fechaNac !== '' ? $fechaNac : '—'],
                ['k' => 'Lugar de nacimiento', 'v' => $alumno->lugar_nacimiento ?? '—'],
                ['k' => 'Estado civil', 'v' => $alumno->estado_civil ?? '—'],
                ['k' => 'Te consideras', 'v' => $alumno->te_consideras ?? '—'],
            ],
        ],
        [
            'titulo' => 'Contacto y Ubicacion',
            'campos' => [
                ['k' => 'Email', 'v' => $alumno->email ?? '—'],
                ['k' => 'Numero', 'v' => $alumno->numero ?? '—'],
                ['k' => 'Numero referencia', 'v' => $alumno->numero_referencia ?? '—'],
                ['k' => 'Departamento', 'v' => $alumno->departamento ?? '—'],
                ['k' => 'Provincia', 'v' => $alumno->provincia ?? '—'],
                ['k' => 'Distrito', 'v' => $alumno->distrito ?? '—'],
                ['k' => 'Direccion', 'v' => $alumno->direccion ?? '—'],
                ['k' => 'Permanencia vivienda', 'v' => $alumno->permanencia_vivienda ?? '—'],
            ],
        ],
        [
            'titulo' => 'Datos Academicos',
            'campos' => [
                ['k' => 'Programa', 'v' => optional($alumno->programa)->nombre ?? '—'],
                ['k' => 'Ciclo', 'v' => optional($alumno->ciclo)->nombre ?? '—'],
                ['k' => 'Condicion', 'v' => $alumno->condicion ?? '—'],
                ['k' => 'Procedencia familiar', 'v' => $alumno->procedencia_familiar ?? '—'],
                ['k' => 'Sector laboral', 'v' => $alumno->sector_laboral ?? '—'],
                ['k' => 'Lengua 1', 'v' => $alumno->lengua_1 ?? '—'],
                ['k' => 'Lengua 2', 'v' => $alumno->lengua_2 ?? '—'],
                ['k' => 'Num. comprobante', 'v' => $alumno->num_comprobante ?? '—'],
            ],
        ],
        [
            'titulo' => 'Aspectos Educativos',
            'campos' => [
                ['k' => 'Estudio/Beca', 'v' => $alumno->estudio_beca ?? '—'],
                ['k' => 'Origen beca', 'v' => $alumno->origen_beca ?? '—'],
                ['k' => 'Postulaciones EESP', 'v' => $alumno->postulaciones_eesp ?? '—'],
                ['k' => 'Postulaciones Inst/Uni', 'v' => $alumno->postulaciones_inst_uni ?? '—'],
                ['k' => 'Postulaciones otros', 'v' => $alumno->postulaciones_otros ?? '—'],
                ['k' => 'Tipo preparacion', 'v' => $alumno->tipo_preparacion ?? '—'],
                ['k' => 'Motivo estudio EESP', 'v' => $alumno->motivo_estudio_eesp ?? '—'],
                ['k' => 'Motivo docencia', 'v' => $alumno->motivo_docencia ?? '—'],
                ['k' => 'Motivo especialidad', 'v' => $alumno->motivo_especialidad ?? '—'],
                ['k' => 'Internet en casa', 'v' => $siNo($alumno->internet)],
                ['k' => 'Lugar de internet', 'v' => $alumno->internet_lugar ?? '—'],
                ['k' => 'Servicio internet', 'v' => $alumno->servicio_internet ?? '—'],
                ['k' => 'Dispositivo internet', 'v' => $alumno->dispositivo_internet ?? '—'],
                ['k' => 'Equipo propio/compartido', 'v' => $siNo($alumno->propio_compartido)],
                ['k' => 'Usa correo', 'v' => $siNo($alumno->correo)],
                ['k' => 'Horas de estudio', 'v' => $alumno->num_hrs_estudio ?? '—'],
                ['k' => 'Forma de estudio', 'v' => $alumno->forma_estudio ?? '—'],
            ],
        ],
        [
            'titulo' => 'Aspectos Familiares y Socioeconomicos',
            'campos' => [
                ['k' => 'Convivientes', 'v' => $alumno->convivientes ?? '—'],
                ['k' => 'Quien mantiene', 'v' => $alumno->quien_mantiene ?? '—'],
                ['k' => 'Dep. menores', 'v' => $alumno->cant_dependientes_child ?? '—'],
                ['k' => 'Dep. adultos mayores', 'v' => $alumno->cant_dependientes_old ?? '—'],
                ['k' => 'Dep. otros', 'v' => $alumno->cant_dependientes_otros ?? '—'],
                ['k' => 'Padre/Madre soltero(a)', 'v' => $siNo($alumno->p_m_soltero)],
                ['k' => 'Num. hijos', 'v' => $alumno->num_hijos ?? '—'],
                ['k' => 'Sector socioeconomico', 'v' => $alumno->sector_socioeconomico ?? '—'],
                ['k' => 'Trabaja', 'v' => $trabajaTxt],
                ['k' => 'Donde trabaja', 'v' => $alumno->donde_trabajas ?? '—'],
                ['k' => 'Ingreso mensual', 'v' => $alumno->ingreso_mensual ?? '—'],
                ['k' => 'Egreso', 'v' => $alumno->egreso ?? '—'],
                ['k' => 'Horas laboradas/sem.', 'v' => $alumno->hrs_laboradas_sem ?? '—'],
                ['k' => 'Ayuda economica', 'v' => $siNo($alumno->ayuda_economica)],
                ['k' => 'Tiempo de ayuda', 'v' => $alumno->tiempo_ayuda ?? '—'],
                ['k' => 'Tipo apoyo formacion', 'v' => $alumno->tipo_apoyo_formacion ?? '—'],
            ],
        ],
        [
            'titulo' => 'Vivienda, Salud y Adicional',
            'campos' => [
                ['k' => 'Tipo vivienda', 'v' => $alumno->tipo_vivienda ?? '—'],
                ['k' => 'Situacion vivienda', 'v' => $alumno->situacion_vivienda ?? '—'],
                ['k' => 'Dormitorios', 'v' => $alumno->dormitorios_vivienda ?? '—'],
                ['k' => 'Banos', 'v' => $alumno->banos_vivienda ?? '—'],
                ['k' => 'Material vivienda', 'v' => $alumno->material_vivienda ?? '—'],
                ['k' => 'Bienes vivienda', 'v' => $formateaLista($alumno->bienes_vivienda)],
                ['k' => 'Horas agua', 'v' => $alumno->hrs_disponibles_agua ?? '—'],
                ['k' => 'Horas desague', 'v' => $alumno->hrs_disponibles_desague ?? '—'],
                ['k' => 'Horas luz', 'v' => $alumno->hrs_disponibles_luz ?? '—'],
                ['k' => 'Otros servicios', 'v' => $formateaLista($alumno->otros_servicios)],
                ['k' => 'Problemas de salud', 'v' => $siNo($alumno->problemas_salud)],
                ['k' => 'Ultima consulta', 'v' => $siNo($alumno->ultima_consulta)],
                ['k' => 'Motivo consulta', 'v' => $alumno->motivo_consulta ?? '—'],
                ['k' => 'Tipo seguro', 'v' => $alumno->tipo_seguro ?? '—'],
                ['k' => 'Familiar con salud delicada', 'v' => $siNo($alumno->familiar_salud)],
                ['k' => 'Frecuencia lectura', 'v' => $alumno->frecuencia_lectura ?? '—'],
                ['k' => 'Acceso lectura', 'v' => $alumno->acceso_lectura ?? '—'],
                ['k' => 'Visitas museos', 'v' => $alumno->visitas_museos ?? '—'],
                ['k' => 'Actividades internet', 'v' => $alumno->actividades_internet ?? '—'],
                ['k' => 'Habilidades', 'v' => $formateaLista(is_array($alumno->habilidades) ? $alumno->habilidades : str_replace('-', ',', (string) $alumno->habilidades))],
                ['k' => 'Tiempo libre', 'v' => $siNo($alumno->tiempo_libre)],
            ],
        ],
    ];
@endphp
<div class="row">
    @foreach ($seccionesDetalle as $seccion)
        <div class="col-md-6 col-xl-3 mb-2">
            <div class="detalle-card h-100 px-2 py-2">
                <div class="detalle-card-title mb-1">{{ $seccion['titulo'] }}</div>
                @foreach ($seccion['campos'] as $campo)
                    <div class="detalle-row">
                        <span class="detalle-k">{{ $campo['k'] }}</span>
                        <span class="detalle-v">{{ $campo['v'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
