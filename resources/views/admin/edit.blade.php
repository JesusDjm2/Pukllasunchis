@extends($layout ?? 'layouts.admin')
@section('contenido')
@include('admin.usuarios._form_estilos')
@php
    $selectedRoles = old('roles', $currentRoles);
    $iniciales = mb_strtoupper(mb_substr(trim((string) $admin->name), 0, 1) . mb_substr(trim((string) $admin->apellidos), 0, 1));
    $esEstudiante = $admin->hasAnyRole(['alumno', 'alumnoB', 'inhabilitado']) || $admin->alumno || $admin->alumnoB;
    // "No", "Ninguno"… escritos en el campo antiguo no cuentan como cursos pendientes.
    $tienePendientes = old('tiene_cursos_pendientes', $admin->cursosPendientes() ? 1 : 0) == 1;
    $condiciones = ['Regular', 'Beca Continua', 'Beca 18', 'Beca Puklla', 'Reincorporación', 'Egresado'];
    $perfilesBolsa = ['Estudiante', 'Bachiller', 'Titulado', 'Egresado'];
    $motivosInhabilitacion = ['Deuda' => 'Deuda', 'Sin matrícula' => 'Sin matrícula', 'Licencia' => 'Licencia', 'Retirado' => 'Retirado'];
    // Ciclos del programa del usuario + siempre su ciclo actual (aunque un dato inconsistente lo deje
    // en otro programa): si no estuviera en la lista, al guardar se le borraría el ciclo.
    $cicloActualId = old('ciclo_id', $currentCicloId);
    $ciclosDelPrograma = $ciclos->filter(fn ($c) => $c->programa_id == old('programa_id', $currentProgramId) || $c->id == $cicloActualId);
@endphp

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4" style="gap:.75rem;">
        <div class="reg-header">
            <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                <i class="fas fa-user-edit mr-2" style="color:#4e73df;"></i>Editar usuario
            </h4>
            <small class="text-muted">Actualiza los datos de acceso, roles y perfil de <strong>{{ $admin->apellidos }}, {{ $admin->name }}</strong></small>
        </div>
        <a href="{{ route('admin') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:.5rem;">
            <i class="fas fa-arrow-left fa-xs mr-1"></i> Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" style="border-radius:.6rem;">
            <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Revisa los siguientes datos:</div>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success" style="border-radius:.6rem;"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
    @endif

    <form id="editForm" method="POST" action="{{ route('adminUpdate', $admin->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">

        {{-- Columna principal --}}
        <div class="col-xl-8 col-lg-7">

            {{-- Información personal --}}
            <div class="reg-card">
                <div class="reg-card-head">
                    <i class="fas fa-id-card"></i> Información personal
                </div>
                <div class="reg-card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="name">Nombres <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user fa-xs"></i></span>
                                </div>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $admin->name) }}" placeholder="Nombres" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="apellidos">Apellidos <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user fa-xs"></i></span>
                                </div>
                                <input type="text" name="apellidos" id="apellidos"
                                    class="form-control @error('apellidos') is-invalid @enderror"
                                    value="{{ old('apellidos', $admin->apellidos) }}" placeholder="Apellidos" required>
                                @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="dni">DNI <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-id-badge fa-xs"></i></span>
                                </div>
                                <input type="text" name="dni" id="dni"
                                    class="form-control @error('dni') is-invalid @enderror"
                                    value="{{ old('dni', $admin->dni) }}" placeholder="Nº de DNI" required>
                                @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Credenciales --}}
            <div class="reg-card">
                <div class="reg-card-head">
                    <i class="fas fa-lock"></i> Credenciales de acceso
                </div>
                <div class="reg-card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="email">Correo electrónico <span class="text-danger">*</span></label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope fa-xs"></i></span>
                                </div>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $admin->email) }}" placeholder="correo@ejemplo.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="password">Nueva contraseña</label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key fa-xs"></i></span>
                                </div>
                                <input type="password" name="password" id="password" autocomplete="new-password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Mín. 7 caracteres">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <span class="reg-hint">Déjala vacía para mantener la actual.</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="password_confirmation">Confirmar contraseña</label>
                            <div class="input-group reg-input-wrap">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key fa-xs"></i></span>
                                </div>
                                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                                    class="form-control" placeholder="Repite la nueva contraseña">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Roles --}}
            <div class="reg-card">
                <div class="reg-card-head">
                    <i class="fas fa-shield-alt"></i> Roles del usuario
                </div>
                <div class="reg-card-body">
                    @include('admin.usuarios._form_roles', ['seleccionados' => $selectedRoles])

                    {{-- Motivo de inhabilitación: con el rol Inhabilitado, tenga o no además alumno/alumnoB --}}
                    <div id="motivo-inhabilitado-container" class="reg-reveal mt-3" style="display:none; border-color:#fecaca; background:#fef2f2;">
                        <div class="sec-title" style="color:#b91c1c; border-color:#fecaca;">
                            <i class="fas fa-ban"></i> Motivo de inhabilitación
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <select id="perfil_inhabilitado"
                                    class="form-control reg-input-solo @error('perfil') is-invalid @enderror">
                                    <option value="" @selected(! array_key_exists((string) old('perfil', $admin->perfil), $motivosInhabilitacion))>Seleccionar motivo</option>
                                    @foreach ($motivosInhabilitacion as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('perfil', $admin->perfil) === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                @error('perfil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <span class="reg-hint">Las licencias suman como máximo 2 años en total (ver expediente).</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos académicos (alumno / alumnoB) --}}
            <div id="admin-fields" style="display:none;">
                <div class="reg-reveal mb-4">
                    <div class="sec-title">
                        <i class="fas fa-university"></i> Datos académicos
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="programa_id">Programa</label>
                            <select id="programa_id" name="programa_id" class="form-control reg-input-solo @error('programa_id') is-invalid @enderror">
                                <option value="">Seleccionar programa</option>
                                @foreach ($programas as $programa)
                                    <option value="{{ $programa->id }}" @selected(old('programa_id', $currentProgramId) == $programa->id)>{{ $programa->nombre }}</option>
                                @endforeach
                            </select>
                            @error('programa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="ciclo_id">Ciclo</label>
                            <select id="ciclo_id" name="ciclo_id" class="form-control reg-input-solo @error('ciclo_id') is-invalid @enderror">
                                <option value="">Seleccionar ciclo</option>
                                {{-- Solo los ciclos del programa actual (antes salían los de todos los programas mezclados) --}}
                                @foreach ($ciclosDelPrograma as $ciclo)
                                    <option value="{{ $ciclo->id }}" @selected(old('ciclo_id', $currentCicloId) == $ciclo->id)>{{ $ciclo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('ciclo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-label" for="condicion">Condición</label>
                            <select name="condicion" id="condicion" class="form-control reg-input-solo @error('condicion') is-invalid @enderror">
                                <option value="" @selected(old('condicion', $admin->condicion) == '')>Seleccionar</option>
                                @foreach ($condiciones as $c)
                                    <option value="{{ $c }}" @selected(old('condicion', $admin->condicion) === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                            @error('condicion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="reg-label" for="perfil_bolsa">
                                Perfil <small class="text-muted font-weight-normal">(bolsa de trabajo)</small>
                            </label>
                            <select id="perfil_bolsa" class="form-control reg-input-solo @error('perfil') is-invalid @enderror">
                                <option value="" @selected(old('perfil', $admin->perfil) == '')>Seleccionar</option>
                                @foreach ($perfilesBolsa as $p)
                                    <option value="{{ $p }}" @selected(old('perfil', $admin->perfil) === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                            @error('perfil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <span id="perfil_bolsa_aviso" class="reg-hint" style="display:none;">
                                <i class="fas fa-info-circle"></i> No aplica mientras esté inhabilitado: se guarda el motivo de inhabilitación.
                            </span>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="reg-label" for="beca">Beca</label>
                            <select name="beca" id="beca" class="form-control reg-input-solo @error('beca') is-invalid @enderror">
                                <option value="1" @selected(old('beca', $admin->beca) == 1)>Sí</option>
                                <option value="0" @selected(old('beca', $admin->beca) == 0)>No</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="reg-label" for="tiene_cursos_pendientes">¿Cursos pendientes?</label>
                            <select id="tiene_cursos_pendientes" name="tiene_cursos_pendientes" class="form-control reg-input-solo">
                                <option value="1" @selected($tienePendientes)>Sí</option>
                                <option value="0" @selected(! $tienePendientes)>No</option>
                            </select>
                        </div>
                        @include('admin._cursos_pendientes', ['actuales' => $admin->cursosPendientes(), 'mostrar' => $tienePendientes])
                    </div>
                </div>
            </div>

            {{-- Contacto (docente y/o tutor) --}}
            @if ($admin->hasAnyRole(['docente', 'tutor']))
                <div class="reg-card">
                    <div class="reg-card-head">
                        <i class="fas fa-phone"></i> Contacto y notificaciones
                    </div>
                    <div class="reg-card-body">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="reg-label" for="telefono">Teléfono</label>
                                <div class="input-group reg-input-wrap">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone fa-xs"></i></span>
                                    </div>
                                    <input type="text" id="telefono" name="telefono" class="form-control"
                                        value="{{ old('telefono', $admin->telefono) }}" placeholder="984529158">
                                </div>
                                <span class="reg-hint">Número de contacto del docente o tutor.</span>
                            </div>
                            @if ($admin->hasRole('tutor'))
                                <div class="col-md-7 mb-3">
                                    <label class="reg-label" for="whatsapp_key">Clave CallMeBot (WhatsApp)</label>
                                    <div class="input-group reg-input-wrap">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fab fa-whatsapp fa-xs"></i></span>
                                        </div>
                                        <input type="text" id="whatsapp_key" name="whatsapp_key" class="form-control"
                                            value="{{ old('whatsapp_key', $admin->whatsapp_key) }}" placeholder="123456">
                                    </div>
                                    <span class="reg-hint">
                                        El tutor la activa enviando <strong>I allow callmebot to send me messages</strong>
                                        al <strong>+34 644 52 74 61</strong> por WhatsApp; recibe su clave en respuesta.
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>{{-- /col-xl-8 --}}

        {{-- Barra lateral: foto, resumen y guardar --}}
        <div class="col-xl-4 col-lg-5">
            <div style="position:sticky; top:1.25rem;">
                <div class="reg-card">
                    <div class="reg-card-head">
                        <i class="fas fa-user-circle"></i> Usuario
                    </div>
                    <div class="reg-card-body">
                        <div class="text-center">
                            @if ($admin->foto)
                                <img src="{{ asset('img/estudiantes/' . $admin->foto) }}" alt="Foto de {{ $admin->name }}" class="reg-foto" id="preview">
                            @else
                                <div class="reg-foto" id="previewIniciales">{{ $iniciales }}</div>
                                <img src="" alt="Vista previa" class="reg-foto" id="preview" style="display:none;">
                            @endif
                            <div class="reg-resumen-nombre">{{ $admin->apellidos }}, {{ $admin->name }}</div>
                            <div class="reg-resumen-sub">{{ $admin->email }}</div>
                            <label class="reg-foto-btn mt-2 mb-1" for="foto">
                                <i class="fas fa-camera"></i> {{ $admin->foto ? 'Cambiar foto' : 'Subir foto' }}
                            </label>
                            <input type="file" name="foto" id="foto" accept="image/*" class="d-none @error('foto') is-invalid @enderror">
                            @error('foto')<div class="text-danger small">{{ $message }}</div>@enderror
                            <span class="reg-hint" id="fotoNombre"></span>
                        </div>

                        <div class="mt-3">
                            <div class="reg-resumen-dato">
                                <span>Roles</span>
                                <span>
                                    @forelse ($currentRoles as $rol)
                                        <span class="reg-rol-mini">{{ $rol }}</span>
                                    @empty
                                        —
                                    @endforelse
                                </span>
                            </div>
                            @if ($esEstudiante)
                                <div class="reg-resumen-dato"><span>Programa</span><span>{{ $admin->programa->nombre ?? 'No asignado' }}</span></div>
                                <div class="reg-resumen-dato"><span>Ciclo</span><span>{{ optional($admin->ciclo)->nombre ?? '—' }}</span></div>
                                <div class="reg-resumen-dato"><span>Condición</span><span>{{ $admin->condicion ?: '—' }}</span></div>
                                <div class="reg-resumen-dato"><span>Perfil</span><span>{{ $admin->perfil ?: '—' }}</span></div>
                            @endif
                        </div>

                        @if ($esEstudiante && Route::has('expediente.show'))
                            <a href="{{ route('expediente.show', $admin) }}" class="btn btn-sm btn-outline-primary btn-block mt-3" style="border-radius:.45rem;">
                                <i class="fas fa-folder-open fa-xs mr-1"></i> Ver expediente
                            </a>
                        @endif
                    </div>
                </div>

                <div class="reg-card">
                    <div class="reg-card-body">
                        <button type="submit" class="reg-submit mb-2">
                            <i class="fas fa-save mr-2"></i> Guardar cambios
                        </button>
                        <a href="{{ route('admin') }}" class="btn btn-sm btn-light btn-block" style="border-radius:.45rem;">
                            <i class="fas fa-times fa-xs mr-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const adminFields   = document.getElementById('admin-fields');
    const motivoBox     = document.getElementById('motivo-inhabilitado-container');
    const perfilBolsa   = document.getElementById('perfil_bolsa');
    const perfilInhab   = document.getElementById('perfil_inhabilitado');
    const tieneCursos   = document.getElementById('tiene_cursos_pendientes');
    const cursosPend    = document.getElementById('cursos-pendientes');
    const programaSel   = document.getElementById('programa_id');
    const cicloSel      = document.getElementById('ciclo_id');

    function getRoles() {
        return Array.from(document.querySelectorAll('.role-checkbox:checked')).map(cb => cb.value);
    }

    // Hay dos selects de "perfil" (bolsa de trabajo / motivo de inhabilitación) y una sola
    // columna users.perfil: solo uno lleva name="perfil" para que no se pisen al enviar.
    // Con el rol Inhabilitado manda el motivo, aunque además tenga alumno/alumnoB (antes el
    // motivo solo aparecía quitando el rol de alumno).
    function toggleFields() {
        const roles = getRoles();
        const esAlumno = roles.includes('alumno') || roles.includes('alumnoB');
        const esInhabilitado = roles.includes('inhabilitado');

        adminFields.style.display = esAlumno ? 'block' : 'none';
        motivoBox.style.display = esInhabilitado ? 'block' : 'none';
        perfilBolsa.toggleAttribute('name', false);
        perfilInhab.toggleAttribute('name', false);
        perfilInhab.required = esInhabilitado;
        perfilBolsa.disabled = esInhabilitado;
        document.getElementById('perfil_bolsa_aviso').style.display = (esAlumno && esInhabilitado) ? 'block' : 'none';
        if (esInhabilitado) perfilInhab.name = 'perfil';
        else if (esAlumno) perfilBolsa.name = 'perfil';
    }
    document.querySelectorAll('.role-checkbox').forEach(cb => cb.addEventListener('change', toggleFields));
    toggleFields();

    tieneCursos.addEventListener('change', function () {
        cursosPend.style.display = this.value == 1 ? 'block' : 'none';
    });

    // Ciclos del programa elegido
    programaSel.addEventListener('change', function () {
        cicloSel.innerHTML = '<option value="">Seleccionar ciclo</option>';
        if (!this.value) return;
        fetch('{{ url('obtener-ciclos') }}/' + this.value)
            .then(r => r.json())
            .then(data => data.forEach(c => {
                const o = document.createElement('option');
                o.value = c.id; o.text = c.nombre;
                cicloSel.appendChild(o);
            }))
            .catch(() => {});
    });

    // Vista previa de la foto nueva
    document.getElementById('foto').addEventListener('change', function () {
        const archivo = this.files && this.files[0];
        if (!archivo) return;
        const reader = new FileReader();
        reader.onload = function () {
            const prev = document.getElementById('preview');
            prev.src = reader.result;
            prev.style.display = 'flex';
            const ini = document.getElementById('previewIniciales');
            if (ini) ini.style.display = 'none';
        };
        reader.readAsDataURL(archivo);
        document.getElementById('fotoNombre').textContent = archivo.name + ' (se guardará al pulsar «Guardar cambios»)';
    });
});
</script>
@endsection
