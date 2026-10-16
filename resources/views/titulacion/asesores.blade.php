@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <div class="container-fluid bg-white pb-4">
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap pt-3 pb-3 mb-3" style="border-bottom: 1px dashed #80808078">
            <div>
                <h4 class="mb-0 font-weight-bold"><i class="fas fa-user-tie mr-2 text-primary"></i>Asesores de Titulación</h4>
                <small class="text-muted">
                    Asesores y jurados que no son docentes con cuenta en el sistema (externos u otros). No inician sesión:
                    aparecen junto a los docentes al elegir asesor/a o jurado en la ficha de cada persona.
                </small>
            </div>
            <a href="{{ route('titulacion.index') }}" class="btn btn-sm btn-outline-secondary mt-2 mt-sm-0"><i class="fa fa-arrow-left fa-sm mr-1"></i>Volver al listado</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <div class="mt-subtitulo mb-2 font-weight-bold small text-uppercase text-muted">Agregar asesor/a</div>
                <form method="POST" action="{{ route('titulacion.asesores.guardar') }}" class="form-row">
                    @csrf
                    <div class="form-group col-md-3"><input type="text" name="nombres" class="form-control form-control-sm" placeholder="Nombres *" value="{{ old('nombres') }}" required></div>
                    <div class="form-group col-md-3"><input type="text" name="apellidos" class="form-control form-control-sm" placeholder="Apellidos *" value="{{ old('apellidos') }}" required></div>
                    <div class="form-group col-md-2"><input type="text" name="dni" class="form-control form-control-sm" placeholder="DNI" value="{{ old('dni') }}"></div>
                    <div class="form-group col-md-4"><input type="email" name="email" class="form-control form-control-sm" placeholder="Correo" value="{{ old('email') }}"></div>
                    <div class="form-group col-md-3"><input type="text" name="telefono" class="form-control form-control-sm" placeholder="Teléfono" value="{{ old('telefono') }}"></div>
                    <div class="form-group col-md-6"><input type="text" name="institucion" class="form-control form-control-sm" placeholder="Institución (si es externo)" value="{{ old('institucion') }}"></div>
                    <div class="form-group col-md-3"><button type="submit" class="btn btn-sm btn-primary btn-block"><i class="fas fa-plus mr-1"></i>Agregar</button></div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover" style="font-size:14px;">
                <thead class="thead-dark">
                    <tr><th>Asesor/a</th><th>DNI</th><th>Correo</th><th>Teléfono</th><th>Institución</th><th>Estado</th><th style="width:1%;"></th></tr>
                </thead>
                <tbody>
                    @forelse ($asesores as $a)
                        <tr class="{{ $a->activo ? '' : 'text-muted' }}">
                            <td><strong>{{ $a->apellidos }}, {{ $a->nombres }}</strong></td>
                            <td>{{ $a->dni ?: '—' }}</td>
                            <td>{{ $a->email ?: '—' }}</td>
                            <td>{{ $a->telefono ?: '—' }}</td>
                            <td>{{ $a->institucion ?: '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $a->activo ? 'success' : 'secondary' }}">{{ $a->activo ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="text-nowrap">
                                <details>
                                    <summary class="small text-primary" style="cursor:pointer;">Editar</summary>
                                    <form method="POST" action="{{ route('titulacion.asesores.actualizar', $a->id) }}" class="mt-2" style="min-width:260px;">
                                        @csrf @method('PUT')
                                        <input type="text" name="nombres" class="form-control form-control-sm mb-1" value="{{ $a->nombres }}" placeholder="Nombres" required>
                                        <input type="text" name="apellidos" class="form-control form-control-sm mb-1" value="{{ $a->apellidos }}" placeholder="Apellidos" required>
                                        <input type="text" name="dni" class="form-control form-control-sm mb-1" value="{{ $a->dni }}" placeholder="DNI">
                                        <input type="email" name="email" class="form-control form-control-sm mb-1" value="{{ $a->email }}" placeholder="Correo">
                                        <input type="text" name="telefono" class="form-control form-control-sm mb-1" value="{{ $a->telefono }}" placeholder="Teléfono">
                                        <input type="text" name="institucion" class="form-control form-control-sm mb-1" value="{{ $a->institucion }}" placeholder="Institución">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="activo-{{ $a->id }}" name="activo" value="1" @checked($a->activo)>
                                            <label class="custom-control-label small" for="activo-{{ $a->id }}" title="Inactivo: deja de aparecer en las listas, pero se conserva donde ya fue asignado">Activo (aparece en las listas)</label>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary btn-block">Guardar</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Todavía no hay asesores registrados. Los docentes con cuenta ya aparecen en las listas sin registrarlos aquí.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
