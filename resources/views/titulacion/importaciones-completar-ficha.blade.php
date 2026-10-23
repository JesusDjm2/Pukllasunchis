@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-user-check mr-2" style="color:#4e73df;"></i>Completar ficha de egresado
                </h4>
                <small class="text-muted">{{ $user->apellidos }}, {{ $user->name }} ya tiene cuenta en el sistema — solo le falta el perfil de egresado</small>
            </div>
            <a href="{{ route('titulacion.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left fa-sm mr-1"></i>Volver</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($esCuentaDeTrabajo)
            {{-- Personal de la escuela: su cuenta es de trabajo (p. ej. de un puesto). Lo recomendado es una
                 cuenta propia de egresado(a); la de trabajo no se toca (TitulacionController::crearCuentaPropia). --}}
            <div class="alert alert-warning">
                <i class="fas fa-briefcase mr-1"></i>
                <strong>{{ $user->email }}</strong> es una cuenta de trabajo
                ({{ $user->getRoleNames()->map(fn ($r) => ucfirst($r))->implode(', ') }}).
                Si la vuelve egresada, sus avisos de titulación llegarían a ese buzón y aparecería en las listas de estudiantes.
                Si es la cuenta de un puesto (la usará otra persona después), cree su <strong>cuenta propia</strong>.
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i>
                Esta persona ya inició sesión antes con el correo <strong>{{ $user->email }}</strong> — eso NO cambia.
                Solo se le va a agregar su historial como egresado(a) encima de la cuenta que ya tiene.
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('titulacion.importaciones.completar-ficha.guardar', $importacion->id) }}" id="cfForm">
                    @csrf
                    @if ($esCuentaDeTrabajo)
                        @php $modo = old('modo', $modoSugerido); @endphp
                        <div class="form-group">
                            <label class="small font-weight-bold d-block">¿Cómo registrarla?</label>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="cfModoPropia" name="modo" value="cuenta_propia" class="custom-control-input" @checked($modo === 'cuenta_propia')>
                                <label class="custom-control-label" for="cfModoPropia">
                                    <strong>Crear su cuenta propia de egresado(a)</strong> @if ($modoSugerido === 'cuenta_propia')<span class="badge badge-success">Recomendado</span>@endif<br>
                                    <small class="text-muted">Con su correo de estudiante y su DNI (contraseña inicial: su DNI). La cuenta {{ $user->email }} sigue igual
                                        (mismo correo, contraseña y roles); solo su DNI pasa a la cuenta nueva y en la de trabajo queda «OFICINA-{{ $user->id }}».</small>
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="cfModoMisma" name="modo" value="misma" class="custom-control-input" @checked($modo === 'misma')>
                                <label class="custom-control-label" for="cfModoMisma">
                                    Completar la ficha en esta misma cuenta @if ($modoSugerido === 'misma')<span class="badge badge-success">Recomendado</span>@endif<br>
                                    <small class="text-muted">Solo si {{ $user->email }} es su cuenta personal.</small>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="cfCorreo" @if ($modo !== 'cuenta_propia') hidden @endif>
                            <label class="small font-weight-bold" for="cfEmail">Correo de su cuenta propia</label>
                            <input type="email" name="email" id="cfEmail" class="form-control" value="{{ old('email', $correoSugerido) }}" @required($modo === 'cuenta_propia')>
                            <small class="text-muted">Sugerido con la regla de correos de la escuela; corríjalo si ya tiene uno de estudiante.</small>
                        </div>
                        <script>
                            document.addEventListener('change', function (e) {
                                if (e.target.name !== 'modo') return;
                                var propia = e.target.value === 'cuenta_propia';
                                document.getElementById('cfCorreo').hidden = !propia;
                                document.getElementById('cfEmail').required = propia;
                                document.getElementById('cfBoton').innerHTML = propia
                                    ? '<i class="fas fa-user-plus mr-1"></i>Crear cuenta propia' : '<i class="fas fa-user-check mr-1"></i>Completar ficha';
                            });
                        </script>
                    @endif
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Nombres</label>
                            <input type="text" name="nombres" class="form-control" value="{{ old('nombres', $nombresPrefill) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos', $apellidosPrefill) }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">DNI</label>
                            <input type="text" name="dni" class="form-control" value="{{ old('dni', $user->dni) }}" required readonly>
                            <small class="text-muted">Debe coincidir con el DNI ya registrado en su cuenta</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Programa</label>
                            <select name="programa_id" class="form-control" required>
                                <option value="">Selecciona...</option>
                                @foreach ($programas as $programa)
                                    <option value="{{ $programa->id }}" {{ (int) old('programa_id', $programaSugerido) === $programa->id ? 'selected' : '' }}>{{ $programa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="small font-weight-bold">Año</label>
                            <input type="text" name="anio_ingreso" class="form-control" value="{{ old('anio_ingreso', $importacion->datos_normalizados['anio_ingreso'] ?? $importacion->datos_normalizados['periodo'] ?? '') }}" placeholder="2023">
                        </div>
                        <div class="form-group col-md-2">
                            <label class="small font-weight-bold">Tipo</label>
                            <select name="tipo" class="form-control" required>
                                <option value="ppd" @selected(old('tipo', $tipo) === 'ppd')>PPD</option>
                                <option value="fid" @selected(old('tipo', $tipo) === 'fid')>FID</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="cfBoton">
                        @if ($esCuentaDeTrabajo && old('modo', $modoSugerido) === 'cuenta_propia')
                            <i class="fas fa-user-plus mr-1"></i>Crear cuenta propia
                        @else
                            <i class="fas fa-user-check mr-1"></i>Completar ficha
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
