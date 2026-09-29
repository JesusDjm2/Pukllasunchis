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

        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            Esta persona ya inició sesión antes con el correo <strong>{{ $user->email }}</strong> — eso NO cambia.
            Solo se le va a agregar su historial como egresado(a) encima de la cuenta que ya tiene.
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('titulacion.importaciones.completar-ficha.guardar', $importacion->id) }}">
                    @csrf
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
                                    <option value="{{ $programa->id }}" {{ old('programa_id') == $programa->id ? 'selected' : '' }}>{{ $programa->nombre }}</option>
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
                                <option value="ppd" {{ str_contains($importacion->origen, 'ppd') ? 'selected' : '' }}>PPD</option>
                                <option value="fid" {{ str_contains($importacion->origen, 'fid') ? 'selected' : '' }}>FID</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-user-check mr-1"></i>Completar ficha</button>
                </form>
            </div>
        </div>
    </div>
@endsection
