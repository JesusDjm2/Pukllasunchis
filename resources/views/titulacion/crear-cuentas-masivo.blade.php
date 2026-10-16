@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-users mr-2" style="color:#4e73df;"></i>Crear cuentas pendientes — vista previa
                </h4>
                <small class="text-muted">Nada se crea todavía. Revisa la lista y confirma abajo.</small>
            </div>
            <a href="{{ route('titulacion.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left fa-sm mr-1"></i>Volver</a>
        </div>

        @if ($elegibles->isEmpty())
            <div class="alert alert-info">No hay pendientes que se puedan crear de forma segura y automática ahora mismo.</div>
        @else
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i>
                <strong>{{ $elegibles->count() }}</strong> persona(s) tienen nombre, correo y programa identificados con certeza — se pueden crear de una sola vez.
                La contraseña inicial de todas será su <strong>DNI</strong>.
            </div>

            <form method="POST" action="{{ route('titulacion.crear-cuentas-masivo.ejecutar') }}"
                  data-confirm="Se crearán {{ $elegibles->count() }} cuentas nuevas. Esta acción no se puede deshacer desde aquí."
                  data-confirm-titulo="¿Crear las cuentas?" data-confirm-boton="Sí, crear cuentas">
                @csrf
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover" style="font-size:13px">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:24px;">
                                    <input type="checkbox" id="marcarTodos" checked>
                                </th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>DNI</th>
                                <th>Correo</th>
                                <th>Programa</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($elegibles as $p)
                                <tr>
                                    <td><input type="checkbox" name="importacion_ids[]" value="{{ $p['importacion_id'] }}" checked class="marcar-fila"></td>
                                    <td>{{ $p['nombres'] }}</td>
                                    <td>{{ $p['apellidos'] }}</td>
                                    <td>{{ $p['dni'] }}</td>
                                    <td>{{ $p['email'] }}</td>
                                    <td>{{ $p['programa'] }}</td>
                                    <td>{{ strtoupper($p['tipo']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-users mr-1"></i>Confirmar y crear cuentas marcadas</button>
            </form>
        @endif

        @if ($excluidos->isNotEmpty())
            <hr class="my-4">
            <h6 class="font-weight-bold text-secondary"><i class="fas fa-exclamation-triangle mr-1 text-warning"></i>{{ $excluidos->count() }} quedaron fuera — necesitan revisión manual</h6>
            <div class="table-responsive">
                <table class="table table-sm" style="font-size:13px">
                    <thead><tr><th>Nombre</th><th>DNI</th><th>Motivo</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($excluidos as $p)
                            <tr>
                                <td>{{ $p['nombre'] }}</td>
                                <td>{{ $p['dni'] ?: '—' }}</td>
                                <td class="text-muted">
                                    {{ $p['user_id'] ? 'Ya tiene cuenta — usar "Completar ficha"' : implode(', ', $p['motivos_no_elegible']) }}
                                </td>
                                <td>
                                    @if ($p['user_id'])
                                        <a href="{{ route('titulacion.importaciones.completar-ficha.form', $p['importacion_id']) }}" class="btn btn-xs btn-outline-warning" style="font-size:.7rem;">Completar ficha</a>
                                    @else
                                        <a href="{{ route('titulacion.importaciones.crear-cuenta.form', $p['importacion_id']) }}" class="btn btn-xs btn-outline-primary" style="font-size:.7rem;">Crear a mano</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        document.getElementById('marcarTodos')?.addEventListener('change', function () {
            document.querySelectorAll('.marcar-fila').forEach(cb => cb.checked = this.checked);
        });
    </script>
@endsection
