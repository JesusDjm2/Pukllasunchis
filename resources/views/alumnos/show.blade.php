@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <div class="container-fluid alu-page">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap:.75rem;">
            <h2 class="h4 mb-0 text-gray-800">Ficha del alumno</h2>
            <a href="javascript:history.go(-1)" class="alu-btn-back">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>

        {{-- ========================= CABECERA / PERFIL ========================= --}}
        <div class="alu-profile" data-alu-reveal>
            <div class="alu-profile-photo">
                @if ($alumno->user && $alumno->user->foto)
                    <img src="{{ asset('img/estudiantes/' . $alumno->user->foto) }}"
                        alt="Foto de {{ $alumno->nombres }}">
                @else
                    <div class="alu-profile-photo-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
            </div>
            <div class="alu-profile-info">
                <h3 class="alu-profile-name">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</h3>
                <div class="alu-profile-badges">
                    @if ($alumno->programa)
                        <a href="{{ route('programa.show', ['programa' => $alumno->programa->id]) }}" class="alu-badge alu-badge-primary">
                            <i class="fas fa-graduation-cap"></i> {{ $alumno->programa->nombre }}
                        </a>
                    @endif
                    @if ($alumno->ciclo)
                        <a href="{{ route('ciclo.show', ['ciclo' => $alumno->ciclo->id]) }}" class="alu-badge alu-badge-outline">
                            <i class="fas fa-layer-group"></i> Ciclo {{ $alumno->ciclo->nombre }}
                        </a>
                    @endif
                    <span class="alu-badge alu-badge-muted"><i class="fas fa-id-card"></i> DNI {{ $alumno->dni ?? '—' }}</span>
                    @if (stristr($alumno->num_comprobante, 'Beca'))
                        <span class="alu-badge alu-badge-success"><i class="fas fa-award"></i> Beca</span>
                    @endif
                </div>
                <div class="alu-profile-contact">
                    <span><i class="fas fa-envelope"></i> {{ $alumno->email ?? '—' }}</span>
                    <span><i class="fas fa-phone"></i> {{ $alumno->numero ?? '—' }}</span>
                    @if ($alumno->user)
                        <span><i class="fas fa-user-shield"></i> {{ $alumno->user->email }}</span>
                    @endif
                </div>
                @if (isset($alumno->user->pendiente) && $alumno->user->pendiente)
                    <div class="alu-alert-pendiente">
                        <i class="fas fa-triangle-exclamation"></i>
                        <div>
                            <strong>Curso(s) a cargo:</strong>
                            @php $cursosPendientes = explode(',', $alumno->user->pendiente); @endphp
                            {{ implode(', ', array_map('trim', $cursosPendientes)) }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========================= SECCIONES ========================= --}}
        <div class="alu-sections">
            {{-- CARRERA --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('carrera')">
                    <span class="alu-section-ico"><i class="fas fa-graduation-cap"></i></span>
                    <span class="alu-section-title">Carrera</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-carrera"></i>
                </button>
                <div class="alu-section-body" id="alu-body-carrera">
                    <div class="alu-grid">
                        <div class="alu-field">
                            <div class="alu-field-k">Programa</div>
                            <div class="alu-field-v">
                                @if ($alumno->programa)
                                    <a href="{{ route('programa.show', ['programa' => $alumno->programa->id]) }}">{{ $alumno->programa->nombre }}</a>
                                @else — @endif
                            </div>
                        </div>
                        <div class="alu-field">
                            <div class="alu-field-k">Ciclo</div>
                            <div class="alu-field-v">
                                @if ($alumno->ciclo)
                                    <a href="{{ route('ciclo.show', ['ciclo' => $alumno->ciclo->id]) }}">{{ $alumno->ciclo->nombre }}</a>
                                @else — @endif
                            </div>
                        </div>
                        <div class="alu-field alu-field--wide">
                            <div class="alu-field-k">Cursos</div>
                            <div class="alu-field-v">
                                @php $listaCursos = $alumno->cursos->isNotEmpty() ? $alumno->cursos : optional($alumno->ciclo)->cursos; @endphp
                                @if ($listaCursos && $listaCursos->isNotEmpty())
                                    <div class="alu-chip-list">
                                        @foreach ($listaCursos as $curso)
                                            <a href="{{ route('curso.show', ['curso' => $curso->id]) }}" class="alu-chip">
                                                {{ $curso->nombre }}
                                                @if ($alumno->cursos->isNotEmpty() && $curso->ciclo_id != $alumno->ciclo_id)
                                                    <span class="alu-chip-tag">Ciclo {{ $curso->ciclo->nombre }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @else — @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DATOS PERSONALES --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('personales')">
                    <span class="alu-section-ico"><i class="fas fa-id-card"></i></span>
                    <span class="alu-section-title">Datos personales</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-personales"></i>
                </button>
                <div class="alu-section-body" id="alu-body-personales">
                    <div class="alu-grid">
                        <div class="alu-field"><div class="alu-field-k">Nombre completo</div><div class="alu-field-v">{{ $alumno->nombres }} {{ $alumno->apellidos }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Email</div><div class="alu-field-v">{{ $alumno->email ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">DNI</div><div class="alu-field-v">{{ $alumno->dni ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Género</div><div class="alu-field-v">{{ $alumno->genero ?? '—' }}</div></div>
                        <div class="alu-field">
                            <div class="alu-field-k">Fecha de nacimiento</div>
                            <div class="alu-field-v">
                                @php $fechaNacFmtShow = $alumno->fechaNacimientoResueltaFormateada(); @endphp
                                @if ($fechaNacFmtShow !== '')
                                    {{ $fechaNacFmtShow }}
                                    @if ($alumno->edad !== null)
                                        <span class="text-muted">({{ $alumno->edad }} {{ $alumno->edad === 1 ? 'año' : 'años' }})</span>
                                    @endif
                                @else — @endif
                            </div>
                        </div>
                        <div class="alu-field"><div class="alu-field-k">Celular</div><div class="alu-field-v">{{ $alumno->numero ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Celular de contacto de emergencia</div><div class="alu-field-v">{{ $alumno->numero_referencia ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Lugar de nacimiento</div><div class="alu-field-v">{{ $alumno->lugar_nacimiento ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Permanencia en la vivienda</div><div class="alu-field-v">{{ $alumno->permanencia_vivienda ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Departamento</div><div class="alu-field-v">{{ $alumno->departamento ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Provincia</div><div class="alu-field-v">{{ $alumno->provincia ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Distrito</div><div class="alu-field-v">{{ $alumno->distrito ?? '—' }}</div></div>
                        <div class="alu-field alu-field--wide"><div class="alu-field-k">Domicilio</div><div class="alu-field-v">{{ $alumno->direccion ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">¿Su familia procede de una comunidad?</div><div class="alu-field-v">{{ $procedencia[$alumno->procedencia_familiar] ?? $alumno->procedencia_familiar ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Sector laboral <span class="alu-field-hint">(si trabaja)</span></div><div class="alu-field-v">{{ $alumno->sector_laboral ?: '—' }}</div></div>
                        <div class="alu-field alu-field--wide">
                            <div class="alu-field-k">Usuario del sistema</div>
                            <div class="alu-field-v">
                                @if ($alumno->user)
                                    {{ $alumno->user->email }}
                                    @if ($alumno->user->name)
                                        <span class="text-muted">({{ $alumno->user->name }})</span>
                                    @endif
                                @else
                                    <span class="text-muted">Sin cuenta vinculada</span>
                                @endif
                            </div>
                        </div>
                        <div class="alu-field"><div class="alu-field-k">Se considera</div><div class="alu-field-v">{{ $consideras[$alumno->te_consideras] ?? $alumno->te_consideras ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Lengua materna</div><div class="alu-field-v">{{ $alumno->lengua_1 ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Lengua secundaria</div><div class="alu-field-v">{{ $alumno->lengua_2 ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Estado civil</div><div class="alu-field-v">{{ $alumno->estado_civil ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">¿Es padre/madre soltero(a)?</div><div class="alu-field-v">{{ $alumno->p_m_soltero ? 'Sí' : 'No' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">N° de hijos(as)</div><div class="alu-field-v">{{ $alumno->num_hijos ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Sector socioeconómico</div><div class="alu-field-v">{{ $sector[$alumno->sector_socioeconomico] ?? $alumno->sector_socioeconomico ?? '—' }}</div></div>
                        <div class="alu-field">
                            <div class="alu-field-k">N° de comprobante</div>
                            <div class="alu-field-v">
                                @if (stristr($alumno->num_comprobante, 'Beca'))
                                    <span class="alu-badge alu-badge-success"><i class="fas fa-award"></i> Beca</span>
                                @else
                                    {{ $alumno->num_comprobante ?? '—' }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ASPECTOS FAMILIARES --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('familia')">
                    <span class="alu-section-ico"><i class="fas fa-house-user"></i></span>
                    <span class="alu-section-title">Aspectos familiares</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-familia"></i>
                </button>
                <div class="alu-section-body" id="alu-body-familia">
                    <div class="alu-grid">
                        <div class="alu-field"><div class="alu-field-k">¿Con quién(es) vive?</div><div class="alu-field-v">{{ $alumno->convivientes ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Persona que mantiene el hogar</div><div class="alu-field-v">{{ $alumno->quien_mantiene ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Dependientes en el hogar — niños</div><div class="alu-field-v">{{ $alumno->cant_dependientes_child ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Dependientes en el hogar — tercera edad</div><div class="alu-field-v">{{ $alumno->cant_dependientes_old ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Dependientes en el hogar — otros</div><div class="alu-field-v">{{ $alumno->cant_dependientes_otros ?? '—' }}</div></div>
                    </div>
                </div>
            </div>

            {{-- ASPECTOS EDUCATIVOS --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('educacion')">
                    <span class="alu-section-ico"><i class="fas fa-book"></i></span>
                    <span class="alu-section-title">Aspectos educativos</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-educacion"></i>
                </button>
                <div class="alu-section-body" id="alu-body-educacion">
                    <div class="alu-grid">
                        <div class="alu-field"><div class="alu-field-k">¿Estudió con beca escolar?</div><div class="alu-field-v">{{ $alumno->estudio_beca ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Origen de la beca</div><div class="alu-field-v">{{ $alumno->origen_beca ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">N° postulaciones a EESP Pukllasunchis</div><div class="alu-field-v">{{ $alumno->postulaciones_eesp ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">N° postulaciones a otras instituciones (educación)</div><div class="alu-field-v">{{ $alumno->postulaciones_inst_uni ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">N° postulaciones a otras carreras/instituciones</div><div class="alu-field-v">{{ $alumno->postulaciones_otros ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Tipo de preparación para postular</div><div class="alu-field-v">{{ $alumno->tipo_preparacion ?? '—' }}</div></div>
                        <div class="alu-field alu-field--wide"><div class="alu-field-k">Motivo para estudiar en la EESP Pukllasunchis</div><div class="alu-field-v">{{ $alumno->motivo_estudio_eesp ?? '—' }}</div></div>
                        <div class="alu-field alu-field--wide"><div class="alu-field-k">Motivo para seguir estudios de docencia</div><div class="alu-field-v">{{ $alumno->motivo_docencia ?? '—' }}</div></div>
                        <div class="alu-field alu-field--wide"><div class="alu-field-k">Motivo para elegir su especialidad</div><div class="alu-field-v">{{ $alumno->motivo_especialidad ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">¿Tiene acceso a internet en casa?</div><div class="alu-field-v">{{ $alumno->internet ? 'Sí' : 'No' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Lugar de conexión <span class="alu-field-hint">(si no tiene internet en casa)</span></div><div class="alu-field-v">{{ $alumno->internet_lugar ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Principal servicio de internet</div><div class="alu-field-v">{{ $alumno->servicio_internet ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Dispositivo para conectarse</div><div class="alu-field-v">{{ $alumno->dispositivo_internet ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Uso del dispositivo</div><div class="alu-field-v">{{ $alumno->propio_compartido ? 'Propio' : 'Compartido' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">¿Usa correo electrónico?</div><div class="alu-field-v">{{ $alumno->correo ? 'Sí' : 'No' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Horas de estudio</div><div class="alu-field-v">{{ $alumno->num_hrs_estudio ?? '—' }}</div></div>
                        <div class="alu-field alu-field--wide"><div class="alu-field-k">Forma de estudio preferida</div><div class="alu-field-v">{{ $alumno->forma_estudio ?? '—' }}</div></div>
                    </div>
                </div>
            </div>

            {{-- ASPECTOS SOCIOECONÓMICOS --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('socio')">
                    <span class="alu-section-ico"><i class="fas fa-coins"></i></span>
                    <span class="alu-section-title">Aspectos socioeconómicos</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-socio"></i>
                </button>
                <div class="alu-section-body" id="alu-body-socio">
                    <div class="alu-grid">
                        <div class="alu-field"><div class="alu-field-k">¿Trabaja actualmente?</div><div class="alu-field-v">{{ $alumno->trabajas ?: '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Lugar de trabajo</div><div class="alu-field-v">{{ $alumno->donde_trabajas ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Ingreso mensual promedio</div><div class="alu-field-v">{{ $alumno->ingreso_mensual ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Egreso mensual promedio</div><div class="alu-field-v">{{ $alumno->egreso ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Horas laboradas por semana</div><div class="alu-field-v">{{ $alumno->hrs_laboradas_sem ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">¿Recibe ayuda económica familiar?</div><div class="alu-field-v">{{ $alumno->ayuda_economica ? 'Sí' : 'No' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Frecuencia de la ayuda</div><div class="alu-field-v">{{ $alumno->tiempo_ayuda ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Tipo de apoyo en su formación</div><div class="alu-field-v">{{ $alumno->tipo_apoyo_formacion ?? '—' }}</div></div>
                    </div>
                </div>
            </div>

            {{-- ASPECTOS VIVIENDA --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('vivienda')">
                    <span class="alu-section-ico"><i class="fas fa-building"></i></span>
                    <span class="alu-section-title">Aspectos de vivienda</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-vivienda"></i>
                </button>
                <div class="alu-section-body" id="alu-body-vivienda">
                    <div class="alu-grid">
                        <div class="alu-field"><div class="alu-field-k">Tipo de vivienda</div><div class="alu-field-v">{{ $alumno->tipo_vivienda ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Situación de vivienda</div><div class="alu-field-v">{{ $alumno->situacion_vivienda ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Dormitorios</div><div class="alu-field-v">{{ $alumno->dormitorios_vivienda ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Baños</div><div class="alu-field-v">{{ $alumno->banos_vivienda ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Material de la vivienda</div><div class="alu-field-v">{{ $alumno->material_vivienda ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Horas de agua al día</div><div class="alu-field-v">{{ $alumno->hrs_disponibles_agua ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Horas de desagüe al día</div><div class="alu-field-v">{{ $alumno->hrs_disponibles_desague ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Horas de luz al día</div><div class="alu-field-v">{{ $alumno->hrs_disponibles_luz ?? '—' }}</div></div>
                        <div class="alu-field alu-field--wide">
                            <div class="alu-field-k">Bienes en la vivienda</div>
                            <div class="alu-field-v">
                                @php
                                    $bienesRaw = $alumno->bienes_vivienda;
                                    $bienesList = is_array($bienesRaw) ? $bienesRaw : array_filter(array_map('trim', explode(',', (string) $bienesRaw)));
                                @endphp
                                @if (count($bienesList))
                                    <div class="alu-chip-list">
                                        @foreach ($bienesList as $b)
                                            <span class="alu-chip alu-chip-static">{{ $b }}</span>
                                        @endforeach
                                    </div>
                                @else — @endif
                            </div>
                        </div>
                        <div class="alu-field alu-field--wide">
                            <div class="alu-field-k">Otros servicios en la vivienda</div>
                            <div class="alu-field-v">
                                @php
                                    $otrosRaw = $alumno->otros_servicios;
                                    $otrosList = is_array($otrosRaw) ? $otrosRaw : array_filter(array_map('trim', explode(',', (string) $otrosRaw)));
                                @endphp
                                @if (count($otrosList))
                                    <div class="alu-chip-list">
                                        @foreach ($otrosList as $o)
                                            <span class="alu-chip alu-chip-static">{{ $o }}</span>
                                        @endforeach
                                    </div>
                                @else — @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ASPECTOS SALUD --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('salud')">
                    <span class="alu-section-ico"><i class="fas fa-heart-pulse"></i></span>
                    <span class="alu-section-title">Aspectos de salud</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-salud"></i>
                </button>
                <div class="alu-section-body" id="alu-body-salud">
                    <div class="alu-grid">
                        <div class="alu-field"><div class="alu-field-k">¿Presenta algún problema de salud?</div><div class="alu-field-v">{{ $alumno->problemas_salud ? 'Sí' : 'No' }}</div></div>
                        <div class="alu-field">
                            <div class="alu-field-k">Consulta médica en los últimos 12 meses</div>
                            <div class="alu-field-v">{{ $alumno->ultima_consulta ? 'Sí' : 'No' }}</div>
                        </div>
                        <div class="alu-field alu-field--wide"><div class="alu-field-k">Motivo de la consulta / de no haber consultado</div><div class="alu-field-v">{{ $alumno->motivo_consulta ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Tipo de seguro de salud</div><div class="alu-field-v">{{ $alumno->tipo_seguro ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">¿Algún familiar tiene un problema de salud grave?</div><div class="alu-field-v">{{ $alumno->familiar_salud ? 'Sí' : 'No' }}</div></div>
                    </div>
                </div>
            </div>

            {{-- ASPECTOS CULTURALES --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('cultura')">
                    <span class="alu-section-ico"><i class="fas fa-book-open"></i></span>
                    <span class="alu-section-title">Aspectos culturales</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-cultura"></i>
                </button>
                <div class="alu-section-body" id="alu-body-cultura">
                    <div class="alu-grid">
                        <div class="alu-field"><div class="alu-field-k">Frecuencia de lectura</div><div class="alu-field-v">{{ $alumno->frecuencia_lectura ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">Acceso a lectura</div><div class="alu-field-v">{{ $alumno->acceso_lectura ?? '—' }}</div></div>
                        <div class="alu-field"><div class="alu-field-k">¿Ha visitado museos?</div><div class="alu-field-v">{{ $alumno->visitas_museos ?? '—' }}</div></div>
                    </div>
                </div>
            </div>

            {{-- ASPECTOS ADICIONALES --}}
            <div class="alu-section" data-alu-reveal>
                <button type="button" class="alu-section-hdr" onclick="toggleAluSection('adicional')">
                    <span class="alu-section-ico"><i class="fas fa-star"></i></span>
                    <span class="alu-section-title">Información adicional</span>
                    <i class="fas fa-chevron-down alu-chev" id="alu-chev-adicional"></i>
                </button>
                <div class="alu-section-body" id="alu-body-adicional">
                    <div class="alu-grid">
                        <div class="alu-field alu-field--wide"><div class="alu-field-k">Actividades más realizadas en internet</div><div class="alu-field-v">{{ $alumno->actividades_internet ?? '—' }}</div></div>
                        <div class="alu-field alu-field--wide">
                            <div class="alu-field-k">Habilidades desarrolladas</div>
                            <div class="alu-field-v">
                                @php
                                    $habRaw = $alumno->habilidades;
                                    $habList = is_array($habRaw) ? $habRaw : array_filter(preg_split('/[-,]/', (string) $habRaw) ?: []);
                                @endphp
                                @if (count($habList))
                                    <div class="alu-chip-list">
                                        @foreach ($habList as $h)
                                            <span class="alu-chip alu-chip-static">{{ trim($h) }}</span>
                                        @endforeach
                                    </div>
                                @else — @endif
                            </div>
                        </div>
                        <div class="alu-field"><div class="alu-field-k">¿Dispone de tiempo libre?</div><div class="alu-field-v">{{ $alumno->tiempo_libre ? 'Sí' : 'No' }}</div></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================= HISTORIAL ACADÉMICO ========================= --}}
        <div class="alu-section mt-4" data-alu-reveal>
            <div class="alu-section-hdr alu-section-hdr--static">
                <span class="alu-section-ico"><i class="fas fa-clock-rotate-left"></i></span>
                <span class="alu-section-title">Historial académico</span>
            </div>
            <div class="alu-section-body alu-section-body--open">
                <div class="table-responsive">
                    <table class="table table-sm alu-table mb-0">
                        <tbody>
                            @php
                                $periodosAgrupados = $alumno->periodo->groupBy(
                                    fn($p) => $p->periodoActual->nombre ?? 'Sin periodo definido',
                                );
                            @endphp

                            @forelse ($periodosAgrupados as $nombrePeriodo => $periodos)
                                <tr class="alu-table-periodo">
                                    <td colspan="4"><i class="far fa-calendar-alt mr-1"></i> {{ $nombrePeriodo }}</td>
                                </tr>
                                <tr class="alu-table-cols">
                                    <td>Curso(s)</td>
                                    <td>Valoración</td>
                                    <td>Calificación</td>
                                    <td>Calificación sistema</td>
                                </tr>
                                @foreach ($periodos as $p)
                                    <tr>
                                        <td>
                                            @if ($p->curso)
                                                <a href="{{ route('curso.show', ['curso' => $p->curso->id]) }}">{{ $p->curso->nombre }}</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $p->valoracion_curso ?? '-' }}</td>
                                        <td>{{ $p->calificacion_curso ?? '-' }}</td>
                                        <td>{{ $p->calificacion_sistema ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted fst-italic py-3">
                                        No hay calificaciones registradas aún.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <style>
        .alu-page { padding-bottom: 2rem; }

        .alu-btn-back {
            display: inline-flex;
            align-items: center;
            padding: .5rem 1.1rem;
            border-radius: 999px;
            background: #33445a;
            color: #fff;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity .15s;
        }
        .alu-btn-back:hover { opacity: .85; color: #fff; text-decoration: none; }

        /* ── Perfil ── */
        .alu-profile {
            display: flex;
            gap: 1.25rem;
            align-items: center;
            background: #fff;
            border-radius: .9rem;
            box-shadow: 0 2px 14px rgba(0,0,0,.07);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .alu-profile-photo img,
        .alu-profile-photo-placeholder {
            width: 96px; height: 96px; border-radius: 50%;
            object-fit: cover; border: 3px solid #e9ecef; flex-shrink: 0;
        }
        .alu-profile-photo-placeholder {
            display: flex; align-items: center; justify-content: center;
            background: #f1f3f9; color: #adb5bd; font-size: 2.1rem;
        }
        .alu-profile-info { flex: 1; min-width: 220px; }
        .alu-profile-name { font-size: 1.25rem; font-weight: 800; color: #26314a; margin-bottom: .5rem; }
        .alu-profile-badges { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .55rem; }
        .alu-profile-contact { display: flex; flex-wrap: wrap; gap: 1rem; font-size: .82rem; color: #6c757d; }
        .alu-profile-contact i { margin-right: .35rem; color: #98a2b8; }

        .alu-badge {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .3rem .7rem; border-radius: 999px;
            font-size: .74rem; font-weight: 700; text-decoration: none;
        }
        .alu-badge-primary { background: rgba(78,115,223,.12); color: #4e73df; }
        .alu-badge-outline { background: #fff; color: #52658c; border: 1px solid #dfe2ea; }
        .alu-badge-muted { background: #f1f3f9; color: #6c757d; }
        .alu-badge-success { background: rgba(28,200,138,.14); color: #169b6b; }
        .alu-badge-primary:hover, .alu-badge-outline:hover { opacity: .8; text-decoration: none; }

        .alu-alert-pendiente {
            display: flex; gap: .6rem; align-items: flex-start;
            background: #fdecea; color: #a3283a; border: 1px solid #f6cdd2;
            border-radius: .6rem; padding: .6rem .9rem; font-size: .82rem; margin-top: .6rem;
        }

        /* ── Secciones ── */
        .alu-sections { display: flex; flex-direction: column; gap: .9rem; }
        .alu-section {
            background: #fff; border-radius: .8rem; overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,.06); border: 1px solid #edf0f5;
        }
        .alu-section-hdr {
            width: 100%; border: none; cursor: pointer;
            display: flex; align-items: center; gap: .75rem;
            padding: .9rem 1.15rem;
            background: linear-gradient(135deg, #33445a, #4e73df);
            color: #fff; font-weight: 700; font-size: .92rem; text-align: left;
        }
        .alu-section-hdr--static { cursor: default; }
        .alu-section-ico {
            width: 30px; height: 30px; border-radius: 50%;
            background: rgba(255,255,255,.18);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .82rem;
        }
        .alu-section-title { flex: 1; }
        .alu-chev { transition: transform .1s; opacity: .85; }

        .alu-section-body { display: none; overflow: hidden; }
        .alu-section-body--open { display: block; }
        .alu-section-body-inner { padding: 1.25rem; }

        .alu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1rem 1.5rem;
            padding: 1.25rem;
        }
        .alu-field--wide { grid-column: 1 / -1; }
        .alu-field-k {
            font-size: .72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em; color: #8992a8; margin-bottom: .2rem;
        }
        .alu-field-hint { text-transform: none; letter-spacing: normal; font-weight: 500; }
        .alu-field-v { font-size: .9rem; color: #2f3441; word-break: break-word; }
        .alu-field-v a { color: #4e73df; text-decoration: none; }
        .alu-field-v a:hover { text-decoration: underline; }

        .alu-chip-list { display: flex; flex-wrap: wrap; gap: .4rem; }
        .alu-chip {
            display: inline-flex; align-items: center; gap: .35rem;
            background: #f1f3f9; color: #4e73df; border-radius: 999px;
            padding: .28rem .75rem; font-size: .78rem; font-weight: 600; text-decoration: none;
        }
        .alu-chip:hover { background: #e4e8f5; text-decoration: none; }
        .alu-chip-static { color: #52658c; cursor: default; }
        .alu-chip-static:hover { background: #f1f3f9; }
        .alu-chip-tag { font-size: .68rem; opacity: .75; }

        /* ── Historial ── */
        .alu-table { font-size: .85rem; }
        .alu-table-periodo td {
            background: #eef1f8; font-weight: 700; text-transform: uppercase;
            font-size: .76rem; color: #33445a; letter-spacing: .02em;
        }
        .alu-table-cols td { background: #f8f9fc; font-weight: 700; font-size: .74rem; color: #8992a8; }

        @media (max-width: 575.98px) {
            .alu-profile { flex-direction: column; text-align: center; }
            .alu-profile-badges, .alu-profile-contact { justify-content: center; }
            .alu-grid { grid-template-columns: 1fr; padding: 1rem; }
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        function toggleAluSection(id) {
            const body = document.getElementById('alu-body-' + id);
            const chev = document.getElementById('alu-chev-' + id);
            const isOpen = body.style.display !== 'none' && body.style.display !== '';

            if (isOpen) {
                gsap.to(body, {
                    height: 0, opacity: 0, duration: .28, ease: 'power2.in',
                    onStart: () => { body.style.overflow = 'hidden'; },
                    onComplete: () => { body.style.display = 'none'; body.style.height = 'auto'; }
                });
                gsap.to(chev, { rotation: -90, duration: .22, ease: 'power1.out' });
            } else {
                body.style.display = 'block';
                body.style.overflow = 'hidden';
                body.style.height = '0';
                const h = body.scrollHeight;
                gsap.to(body, {
                    height: h, opacity: 1, duration: .34, ease: 'power2.out',
                    onComplete: () => { body.style.height = 'auto'; body.style.overflow = ''; }
                });
                gsap.to(chev, { rotation: 0, duration: .22, ease: 'power1.out' });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Wrap each .alu-grid in a positioning helper for accurate height measurement, then hide bodies.
            document.querySelectorAll('.alu-section-body:not(.alu-section-body--open)').forEach(function (body) {
                body.style.display = 'none';
            });

            // Abrir "Carrera" por defecto.
            toggleAluSection('carrera');

            if (typeof gsap !== 'undefined') {
                gsap.set('[data-alu-reveal]', { opacity: 0, y: 18 });
                gsap.to('[data-alu-reveal]', {
                    opacity: 1, y: 0, duration: .45, stagger: .06, ease: 'power2.out'
                });
            }
        });
    </script>
@endsection
