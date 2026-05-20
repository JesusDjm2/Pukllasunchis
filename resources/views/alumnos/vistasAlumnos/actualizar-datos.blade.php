@extends('layouts.alumno')
@section('titulo', 'Actualizar mis datos')
@section('contenido')

@php
    $a = $alumno;
    $u = auth()->user();
    $bienesActuales = $a->bienes_vivienda ? explode(',', $a->bienes_vivienda) : [];
    $serviciosActuales = $a->otros_servicios ? explode(',', $a->otros_servicios) : [];
    $esBecado = (bool) $u->beca;

    /* Clase para resaltar campos vacíos */
    function campoVacio($valor): string {
        return (is_null($valor) || trim((string)$valor) === '') ? ' border-warning' : '';
    }
    function fc($valor, $extra = ''): string {
        return 'form-control form-control-sm' . campoVacio($valor) . ($extra ? " $extra" : '');
    }
    function fsel($valor, $extra = ''): string {
        return 'form-control form-control-sm' . campoVacio($valor) . ($extra ? " $extra" : '');
    }
@endphp

@php
    $headerActions =
        '<a class="btn btn-sm btn-secondary shadow-sm" href="' . e(route('alumnos.index')) . '">' .
        '<i class="fas fa-arrow-left mr-1"></i> Volver a mi perfil</a>';
    if ($u->alumno) {
        $headerActions .=
            ' <a class="btn btn-sm btn-info shadow-sm" href="' .
            e(route('ficha-matricula', ['alumno' => $a->id])) .
            '"><i class="fas fa-file-alt mr-1"></i> Ficha de matrícula (PDF)</a>';
    }
@endphp

@include('partials.alumno-page-header', [
    'title'    => 'Completar ficha de matrícula',
    'subtitle' => 'Actualiza tus datos para el nuevo período. Los campos con borde naranja están pendientes de completar.',
    'actions'  => $headerActions,
])

<div class="row" id="contenido-alumno">
    <div class="col-12">
        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Corrige los errores:</strong>
                <ul class="mb-0 mt-1 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    </div>

    <div class="col-12">
        <form action="{{ route('alumnos.actualizarDatos') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ===== VOUCHER / COMPROBANTE ===== --}}
            @if ($formularioHabilitado && $periodoActual)
                @php
                    $comprobanteActual = $matriculaActual?->comprobante;
                    $borderColor = $comprobanteActual ? '#28a745' : '#fd7e14';
                @endphp
                <div class="alumno-shell mb-3 p-3" style="border-left: 4px solid {{ $borderColor }}">
                    <h6 class="fw-semibold mb-2">
                        <i class="fas fa-receipt mr-1"></i> Voucher de matrícula —
                        <span class="text-muted small">{{ $periodoActual->nombre }}</span>
                        @if (!$matriculaActual)
                            <span class="badge badge-warning ml-1" style="font-size:11px">Pendiente de matrícula</span>
                        @elseif ($comprobanteActual)
                            <span class="badge badge-success ml-1" style="font-size:11px">Matriculado</span>
                        @endif
                    </h6>
                    @if ($esBecado)
                        <p class="text-success small mb-0">
                            <i class="fas fa-check-circle mr-1"></i> Eres becado/a — no se requiere voucher.
                            @if (!$matriculaActual)
                                Al guardar quedarás registrado/a en el período actual.
                            @endif
                        </p>
                    @else
                        <div class="row">
                            <div class="col-md-6">
                                <label class="small font-weight-bold">
                                    N° Boleta de pago <span class="text-danger">*</span>
                                    @if ($comprobanteActual)
                                        <span class="text-success small"><i class="fas fa-check-circle"></i> Ya registrado</span>
                                    @endif
                                </label>
                                <input type="text"
                                       name="comprobante"
                                       class="{{ fc(old('comprobante', $comprobanteActual)) }} @error('comprobante') is-invalid @enderror"
                                       value="{{ old('comprobante', $comprobanteActual) }}"
                                       placeholder="Ej: 2026-00123"
                                       required>
                                @error('comprobante')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Al guardar quedarás registrado/a en el período actual.</small>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ===== 1. FOTO Y DATOS DE CONTACTO ===== --}}
            <div class="alumno-shell mb-3">
                <h6 class="alumno-th-section px-3 py-2 mb-3">
                    <i class="fas fa-user mr-1"></i> Foto y datos de contacto
                </h6>
                <div class="px-3 pb-3">
                    <div class="row">
                        {{-- Foto --}}
                        <div class="col-md-3 mb-3 text-center">
                            @if ($u->foto)
                                <img src="{{ asset('img/estudiantes/' . $u->foto) }}"
                                     class="rounded-circle mb-2"
                                     style="width:90px;height:90px;object-fit:cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mb-2 mx-auto"
                                     style="width:90px;height:90px;border:2px dashed #fd7e14">
                                    <i class="fas fa-user fa-2x text-muted"></i>
                                </div>
                            @endif
                            <label class="d-block small font-weight-bold mb-1">
                                {{ $u->foto ? 'Cambiar foto' : 'Subir foto' }}
                            </label>
                            <input type="file" name="foto" accept="image/*"
                                   class="form-control-file form-control-sm @error('foto') is-invalid @enderror">
                            @error('foto')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-9">
                            <div class="row">
                                {{-- Nombres (solo lectura) --}}
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">Nombres</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                           value="{{ $a->nombres }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">Apellidos</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                           value="{{ $a->apellidos }}" readonly>
                                </div>
                                {{-- Teléfono --}}
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">
                                        Celular <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="numero"
                                           class="{{ fc(old('numero', $a->numero)) }} @error('numero') is-invalid @enderror"
                                           value="{{ old('numero', $a->numero) }}" required>
                                    @error('numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                {{-- Número referencia --}}
                                <div class="col-md-6 mb-2">
                                    <label class="small font-weight-bold">
                                        Celular de emergencia <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="numero_referencia"
                                           class="{{ fc(old('numero_referencia', $a->numero_referencia)) }} @error('numero_referencia') is-invalid @enderror"
                                           value="{{ old('numero_referencia', $a->numero_referencia) }}" required>
                                    @error('numero_referencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                {{-- Fecha de nacimiento (solo si está vacía) --}}
                                @if (!$a->alumnoTieneFechaNacimientoCapturada())
                                    <div class="col-md-6 mb-2">
                                        <label class="small font-weight-bold text-warning">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Fecha de nacimiento <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="fecha_nacimiento"
                                               class="form-control form-control-sm border-warning @error('fecha_nacimiento') is-invalid @enderror"
                                               value="{{ old('fecha_nacimiento') }}" required>
                                        @error('fecha_nacimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endif
                                {{-- Género (solo si está vacío) --}}
                                @if (!$a->genero)
                                    <div class="col-md-6 mb-2">
                                        <label class="small font-weight-bold text-warning">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Género <span class="text-danger">*</span>
                                        </label>
                                        <select name="genero"
                                                class="form-control form-control-sm border-warning @error('genero') is-invalid @enderror"
                                                required>
                                            <option value="" disabled selected>Seleccionar</option>
                                            <option value="Masculino" {{ old('genero') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                            <option value="Femenino" {{ old('genero') == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                            <option value="Otro" {{ old('genero') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                        </select>
                                        @error('genero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 2. DOMICILIO ===== --}}
            <div class="alumno-shell mb-3">
                <h6 class="alumno-th-section px-3 py-2 mb-3">
                    <i class="fas fa-map-marker-alt mr-1"></i> Domicilio actual
                </h6>
                <div class="px-3 pb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Departamento</label>
                            <input type="text" name="departamento"
                                   class="{{ fc(old('departamento', $a->departamento)) }} @error('departamento') is-invalid @enderror"
                                   value="{{ old('departamento', $a->departamento) }}"
                                   placeholder="Ej: Cusco">
                            @error('departamento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Provincia</label>
                            <input type="text" name="provincia"
                                   class="{{ fc(old('provincia', $a->provincia)) }} @error('provincia') is-invalid @enderror"
                                   value="{{ old('provincia', $a->provincia) }}"
                                   placeholder="Ej: Cusco">
                            @error('provincia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Distrito</label>
                            <input type="text" name="distrito"
                                   class="{{ fc(old('distrito', $a->distrito)) }} @error('distrito') is-invalid @enderror"
                                   value="{{ old('distrito', $a->distrito) }}"
                                   placeholder="Ej: Santiago">
                            @error('distrito')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="small font-weight-bold">
                                Dirección detallada <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="direccion"
                                   class="{{ fc(old('direccion', $a->direccion)) }} @error('direccion') is-invalid @enderror"
                                   value="{{ old('direccion', $a->direccion) }}"
                                   placeholder="Comunidad, calle, número, barrio..." required>
                            @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold">Permanencia en la vivienda <span class="text-danger">*</span></label>
                            <select name="permanencia_vivienda"
                                    class="{{ fsel(old('permanencia_vivienda', $a->permanencia_vivienda)) }} @error('permanencia_vivienda') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('permanencia_vivienda', $a->permanencia_vivienda) ? 'selected' : '' }}>Selecciona una opción</option>
                                @foreach ([
                                    'Vivo permanentemente en Cusco ciudad',
                                    'Vivo en comunidad y me traslado al Cusco todos los días para estudiar',
                                    'Estoy en Cusco de lunes a viernes y los fines de semana en mi comunidad',
                                    'Vivo en comunidad y me traslado al Cusco fines de semana',
                                ] as $op)
                                    <option value="{{ $op }}" {{ old('permanencia_vivienda', $a->permanencia_vivienda) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('permanencia_vivienda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 3. ESTADO PERSONAL ===== --}}
            <div class="alumno-shell mb-3">
                <h6 class="alumno-th-section px-3 py-2 mb-3">
                    <i class="fas fa-heart mr-1"></i> Estado personal y familiar
                </h6>
                <div class="px-3 pb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Estado civil <span class="text-danger">*</span></label>
                            <select name="estado_civil"
                                    class="{{ fsel(old('estado_civil', $a->estado_civil)) }} @error('estado_civil') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('estado_civil', $a->estado_civil) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['Soltero','Casado','Conviviente','Viudo','Divorciado'] as $op)
                                    <option value="{{ $op }}" {{ old('estado_civil', $a->estado_civil) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('estado_civil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Número de hijos <span class="text-danger">*</span></label>
                            <input type="number" name="num_hijos" min="0"
                                   class="{{ fc(old('num_hijos', $a->num_hijos) !== null ? old('num_hijos', $a->num_hijos) : '') }} @error('num_hijos') is-invalid @enderror"
                                   value="{{ old('num_hijos', $a->num_hijos) }}" required>
                            @error('num_hijos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold d-block">¿Eres padre/madre soltero? <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="p_m_soltero" value="1"
                                       {{ old('p_m_soltero', $a->p_m_soltero) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label">Sí</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="p_m_soltero" value="0"
                                       {{ old('p_m_soltero', $a->p_m_soltero) == '0' ? 'checked' : '' }}>
                                <label class="form-check-label">No</label>
                            </div>
                            @error('p_m_soltero')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        {{-- Convivientes --}}
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Convivientes <span class="text-danger">*</span></label>
                            <input type="text" name="convivientes"
                                   class="{{ fc(old('convivientes', $a->convivientes)) }} @error('convivientes') is-invalid @enderror"
                                   value="{{ old('convivientes', $a->convivientes) }}" required>
                            @error('convivientes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">¿Quién mantiene el hogar? <span class="text-danger">*</span></label>
                            <input type="text" name="quien_mantiene"
                                   class="{{ fc(old('quien_mantiene', $a->quien_mantiene)) }} @error('quien_mantiene') is-invalid @enderror"
                                   value="{{ old('quien_mantiene', $a->quien_mantiene) }}" required>
                            @error('quien_mantiene')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Dependientes menores <span class="text-danger">*</span></label>
                            <input type="text" name="cant_dependientes_child"
                                   class="{{ fc(old('cant_dependientes_child', $a->cant_dependientes_child)) }} @error('cant_dependientes_child') is-invalid @enderror"
                                   value="{{ old('cant_dependientes_child', $a->cant_dependientes_child) }}" required>
                            @error('cant_dependientes_child')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Dependientes adultos mayores <span class="text-danger">*</span></label>
                            <input type="text" name="cant_dependientes_old"
                                   class="{{ fc(old('cant_dependientes_old', $a->cant_dependientes_old)) }} @error('cant_dependientes_old') is-invalid @enderror"
                                   value="{{ old('cant_dependientes_old', $a->cant_dependientes_old) }}" required>
                            @error('cant_dependientes_old')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Otros dependientes <span class="text-danger">*</span></label>
                            <input type="text" name="cant_dependientes_otros"
                                   class="{{ fc(old('cant_dependientes_otros', $a->cant_dependientes_otros)) }} @error('cant_dependientes_otros') is-invalid @enderror"
                                   value="{{ old('cant_dependientes_otros', $a->cant_dependientes_otros) }}" required>
                            @error('cant_dependientes_otros')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 4. SITUACIÓN ECONÓMICA ===== --}}
            <div class="alumno-shell mb-3">
                <h6 class="alumno-th-section px-3 py-2 mb-3">
                    <i class="fas fa-briefcase mr-1"></i> Situación económica
                </h6>
                <div class="px-3 pb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Sector socioeconómico <span class="text-danger">*</span></label>
                            <select name="sector_socioeconomico"
                                    class="{{ fsel(old('sector_socioeconomico', $a->sector_socioeconomico)) }} @error('sector_socioeconomico') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('sector_socioeconomico', $a->sector_socioeconomico) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['popular','medio','medio_alto','alto'] as $op)
                                    <option value="{{ $op }}" {{ old('sector_socioeconomico', $a->sector_socioeconomico) == $op ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$op)) }}</option>
                                @endforeach
                            </select>
                            @error('sector_socioeconomico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">¿Trabajas actualmente? <span class="text-danger">*</span></label>
                            <select name="trabajas"
                                    class="{{ fsel(old('trabajas', $a->trabajas)) }} @error('trabajas') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('trabajas', $a->trabajas) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['Sí','No','Todavía no'] as $op)
                                    <option value="{{ $op }}" {{ old('trabajas', $a->trabajas) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('trabajas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Sector laboral</label>
                            <input type="text" name="sector_laboral"
                                   class="{{ fc(old('sector_laboral', $a->sector_laboral)) }} @error('sector_laboral') is-invalid @enderror"
                                   value="{{ old('sector_laboral', $a->sector_laboral) }}"
                                   placeholder="Ej: Sector educativo">
                            @error('sector_laboral')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Dónde trabajas</label>
                            <input type="text" name="donde_trabajas"
                                   class="{{ fc(old('donde_trabajas', $a->donde_trabajas)) }} @error('donde_trabajas') is-invalid @enderror"
                                   value="{{ old('donde_trabajas', $a->donde_trabajas) }}">
                            @error('donde_trabajas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Ingreso mensual promedio</label>
                            <select name="ingreso_mensual"
                                    class="{{ fsel(old('ingreso_mensual', $a->ingreso_mensual)) }} @error('ingreso_mensual') is-invalid @enderror">
                                <option value="" disabled {{ !old('ingreso_mensual', $a->ingreso_mensual) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['Menos de 500','De s/501 a s/930','De s/931 a s/1200','De s/1201 a s/2000','Más de s/2000'] as $op)
                                    <option value="{{ $op }}" {{ old('ingreso_mensual', $a->ingreso_mensual) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('ingreso_mensual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Egreso mensual promedio <span class="text-danger">*</span></label>
                            <input type="text" name="egreso"
                                   class="{{ fc(old('egreso', $a->egreso)) }} @error('egreso') is-invalid @enderror"
                                   value="{{ old('egreso', $a->egreso) }}" required>
                            @error('egreso')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Horas laboradas por semana <span class="text-danger">*</span></label>
                            <input type="number" name="hrs_laboradas_sem" min="0"
                                   class="{{ fc(old('hrs_laboradas_sem', $a->hrs_laboradas_sem) !== null ? old('hrs_laboradas_sem', $a->hrs_laboradas_sem) : '') }} @error('hrs_laboradas_sem') is-invalid @enderror"
                                   value="{{ old('hrs_laboradas_sem', $a->hrs_laboradas_sem) }}" required>
                            @error('hrs_laboradas_sem')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold d-block">¿Recibe ayuda económica familiar? <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ayuda_economica" value="1"
                                       {{ old('ayuda_economica', $a->ayuda_economica) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label">Sí</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ayuda_economica" value="0"
                                       {{ old('ayuda_economica', $a->ayuda_economica) == '0' ? 'checked' : '' }}>
                                <label class="form-check-label">No</label>
                            </div>
                            @error('ayuda_economica')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Frecuencia de ayuda económica <span class="text-danger">*</span></label>
                            <select name="tiempo_ayuda"
                                    class="{{ fsel(old('tiempo_ayuda', $a->tiempo_ayuda)) }} @error('tiempo_ayuda') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('tiempo_ayuda', $a->tiempo_ayuda) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['Quincenal','Mensual','1 vez al año','Otro'] as $op)
                                    <option value="{{ $op }}" {{ old('tiempo_ayuda', $a->tiempo_ayuda) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('tiempo_ayuda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Tipo de apoyo en formación <span class="text-danger">*</span></label>
                            <select name="tipo_apoyo_formacion"
                                    class="{{ fsel(old('tipo_apoyo_formacion', $a->tipo_apoyo_formacion)) }} @error('tipo_apoyo_formacion') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('tipo_apoyo_formacion', $a->tipo_apoyo_formacion) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['Beca parcial','Beca integral','No recibo ningún beneficio','Otro'] as $op)
                                    <option value="{{ $op }}" {{ old('tipo_apoyo_formacion', $a->tipo_apoyo_formacion) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('tipo_apoyo_formacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold d-block">¿Estudia con beca? <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estudio_beca" value="1"
                                       {{ old('estudio_beca', $a->estudio_beca) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label">Sí</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estudio_beca" value="0"
                                       {{ old('estudio_beca', $a->estudio_beca) == '0' ? 'checked' : '' }}>
                                <label class="form-check-label">No</label>
                            </div>
                            @error('estudio_beca')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 5. VIVIENDA ===== --}}
            <div class="alumno-shell mb-3">
                <h6 class="alumno-th-section px-3 py-2 mb-3">
                    <i class="fas fa-home mr-1"></i> Vivienda
                </h6>
                <div class="px-3 pb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Tipo de vivienda <span class="text-danger">*</span></label>
                            <select name="tipo_vivienda"
                                    class="{{ fsel(old('tipo_vivienda', $a->tipo_vivienda)) }} @error('tipo_vivienda') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('tipo_vivienda', $a->tipo_vivienda) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['Casa independiente','Departamento o edificio','Condominio','Casa de vecindad','Casa de pensión','Cuarto alquilado','Otro'] as $op)
                                    <option value="{{ $op }}" {{ old('tipo_vivienda', $a->tipo_vivienda) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('tipo_vivienda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Situación de vivienda <span class="text-danger">*</span></label>
                            <select name="situacion_vivienda"
                                    class="{{ fsel(old('situacion_vivienda', $a->situacion_vivienda)) }} @error('situacion_vivienda') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('situacion_vivienda', $a->situacion_vivienda) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['Propia','Alquilada','Alquiler venta','Prestada','Otra'] as $op)
                                    <option value="{{ $op }}" {{ old('situacion_vivienda', $a->situacion_vivienda) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('situacion_vivienda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Material de la vivienda <span class="text-danger">*</span></label>
                            <select name="material_vivienda"
                                    class="{{ fsel(old('material_vivienda', $a->material_vivienda)) }} @error('material_vivienda') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('material_vivienda', $a->material_vivienda) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['Cemento/Ladrillo','Adobe','Quincha','Otro'] as $op)
                                    <option value="{{ $op }}" {{ old('material_vivienda', $a->material_vivienda) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('material_vivienda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Dormitorios <span class="text-danger">*</span></label>
                            <input type="number" name="dormitorios_vivienda" min="0"
                                   class="{{ fc(old('dormitorios_vivienda', $a->dormitorios_vivienda) !== null ? old('dormitorios_vivienda', $a->dormitorios_vivienda) : '') }} @error('dormitorios_vivienda') is-invalid @enderror"
                                   value="{{ old('dormitorios_vivienda', $a->dormitorios_vivienda) }}" required>
                            @error('dormitorios_vivienda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold">Baños <span class="text-danger">*</span></label>
                            <input type="number" name="banos_vivienda" min="0"
                                   class="{{ fc(old('banos_vivienda', $a->banos_vivienda) !== null ? old('banos_vivienda', $a->banos_vivienda) : '') }} @error('banos_vivienda') is-invalid @enderror"
                                   value="{{ old('banos_vivienda', $a->banos_vivienda) }}" required>
                            @error('banos_vivienda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Hrs agua/día <span class="text-danger">*</span></label>
                            <input type="number" name="hrs_disponibles_agua" min="0"
                                   class="{{ fc(old('hrs_disponibles_agua', $a->hrs_disponibles_agua) !== null ? old('hrs_disponibles_agua', $a->hrs_disponibles_agua) : '') }} @error('hrs_disponibles_agua') is-invalid @enderror"
                                   value="{{ old('hrs_disponibles_agua', $a->hrs_disponibles_agua) }}" required>
                            @error('hrs_disponibles_agua')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Hrs desagüe/día <span class="text-danger">*</span></label>
                            <input type="number" name="hrs_disponibles_desague" min="0"
                                   class="{{ fc(old('hrs_disponibles_desague', $a->hrs_disponibles_desague) !== null ? old('hrs_disponibles_desague', $a->hrs_disponibles_desague) : '') }} @error('hrs_disponibles_desague') is-invalid @enderror"
                                   value="{{ old('hrs_disponibles_desague', $a->hrs_disponibles_desague) }}" required>
                            @error('hrs_disponibles_desague')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold">Hrs luz/día <span class="text-danger">*</span></label>
                            <input type="number" name="hrs_disponibles_luz" min="0"
                                   class="{{ fc(old('hrs_disponibles_luz', $a->hrs_disponibles_luz) !== null ? old('hrs_disponibles_luz', $a->hrs_disponibles_luz) : '') }} @error('hrs_disponibles_luz') is-invalid @enderror"
                                   value="{{ old('hrs_disponibles_luz', $a->hrs_disponibles_luz) }}" required>
                            @error('hrs_disponibles_luz')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Bienes vivienda --}}
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold d-block">
                                Bienes en la vivienda <span class="text-danger">*</span>
                                @if (empty($bienesActuales))
                                    <span class="text-warning small"><i class="fas fa-exclamation-circle"></i> Sin completar</span>
                                @endif
                            </label>
                            @php
                                $bienesList = ['Cocina a gas','Cocina electrica','Aspiradora','Televisor','DVD','Mini componente','Cámara de video','Computadora','Horno microondas','Lavadora','Secadora de ropa','Automóvil','Bicicleta','Motocicleta','Juego de video','Refrigeradora','Ninguna de las anteriores'];
                                $bienesOld = old('bienes_vivienda', $bienesActuales);
                            @endphp
                            <div class="row">
                                @foreach ($bienesList as $bien)
                                    <div class="col-6">
                                        <label class="form-check small">
                                            <input class="form-check-input" type="checkbox"
                                                   name="bienes_vivienda[]" value="{{ $bien }}"
                                                   {{ in_array($bien, $bienesOld) ? 'checked' : '' }}>
                                            {{ $bien }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('bienes_vivienda')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Otros servicios --}}
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold d-block">Otros servicios en la vivienda</label>
                            @php
                                $serviciosList = ['Empleado(a) doméstico','Servicio de teléfono','Servicio de cable','Servicio de Internet','Ninguna de las anteriores'];
                                $serviciosOld = old('otros_servicios', $serviciosActuales);
                            @endphp
                            @foreach ($serviciosList as $servicio)
                                <label class="form-check small">
                                    <input class="form-check-input" type="checkbox"
                                           name="otros_servicios[]" value="{{ $servicio }}"
                                           {{ in_array($servicio, $serviciosOld) ? 'checked' : '' }}>
                                    {{ $servicio }}
                                </label>
                            @endforeach
                            @error('otros_servicios')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 6. SALUD ===== --}}
            <div class="alumno-shell mb-3">
                <h6 class="alumno-th-section px-3 py-2 mb-3">
                    <i class="fas fa-heartbeat mr-1"></i> Salud
                </h6>
                <div class="px-3 pb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Tipo de seguro de salud <span class="text-danger">*</span></label>
                            <select name="tipo_seguro"
                                    class="{{ fsel(old('tipo_seguro', $a->tipo_seguro)) }} @error('tipo_seguro') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ !old('tipo_seguro', $a->tipo_seguro) ? 'selected' : '' }}>Seleccionar</option>
                                @foreach (['EsSalud','Seguro Ejército','Seguro Integral de Salud (SIS)','Seguro privado','Ninguno'] as $op)
                                    <option value="{{ $op }}" {{ old('tipo_seguro', $a->tipo_seguro) == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            @error('tipo_seguro')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTÓN GUARDAR --}}
            <div class="text-center pb-4">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                    <i class="fas fa-save mr-2"></i> Guardar cambios
                </button>
            </div>

        </form>
    </div>
</div>

<style>
    .border-warning { border-color: #fd7e14 !important; }
</style>

@endsection
