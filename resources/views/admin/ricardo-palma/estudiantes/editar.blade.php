@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Editar estudiante: ' . $estudiante->nombre_completo)

@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid py-3 al-ui rp-modulo">
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.estudiantes.show', $estudiante) }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Volver al récord
                        </a>
                    </div>
                    <h1>Editar estudiante</h1>
                    <p class="al-hero-sub">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</p>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        @if ($errors->any())
            <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i>
                <div>@foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
            </div>
        @endif

        <form method="POST" action="{{ route('rp.estudiantes.actualizar', $estudiante) }}" class="al-card al-form">
            @csrf
            @method('PUT')
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas fa-user-edit"></i></span>
                <div>
                    <h2>Datos del estudiante</h2>
                    <p>Los cambios se reflejan en todas las actas donde figura.</p>
                </div>
            </div>
            <div class="al-card-cuerpo">
                <div class="al-form-grid">
                    <label>Apellidos *<input type="text" name="apellidos" class="al-campo" required maxlength="150" value="{{ old('apellidos', $estudiante->apellidos) }}"></label>
                    <label>Nombres *<input type="text" name="nombres" class="al-campo" required maxlength="150" value="{{ old('nombres', $estudiante->nombres) }}"></label>
                    <label>N° de matrícula<input type="text" name="numero_matricula" class="al-campo" maxlength="50" value="{{ old('numero_matricula', $estudiante->numero_matricula) }}"></label>
                    <label>DNI<input type="text" name="dni" class="al-campo" maxlength="15" value="{{ old('dni', $estudiante->dni) }}"></label>
                    <label>Carrera
                        <select name="programa_id" class="al-campo">
                            <option value="">Sin carrera</option>
                            @foreach ($programas as $p)
                                <option value="{{ $p->id }}" @selected(old('programa_id', $estudiante->programa_id) == $p->id)>{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Sexo
                        <select name="sexo" class="al-campo">
                            <option value="">—</option>
                            <option value="F" @selected(old('sexo', $estudiante->sexo) === 'F')>Femenino</option>
                            <option value="M" @selected(old('sexo', $estudiante->sexo) === 'M')>Masculino</option>
                        </select>
                    </label>
                    <label>Teléfono<input type="text" name="telefono" class="al-campo" maxlength="50" value="{{ old('telefono', $estudiante->telefono) }}"></label>
                    <label>Año de ingreso<input type="number" name="anio_ingreso" class="al-campo" min="1950" max="2030" value="{{ old('anio_ingreso', $estudiante->anio_ingreso) }}"></label>
                    <label>Estado *
                        <select name="estado" class="al-campo" required>
                            @foreach ($estados as $e)
                                <option value="{{ $e }}" @selected(old('estado', $estudiante->estado) === $e)>{{ $e }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="al-form-ancho">Observaciones<textarea name="observaciones" class="al-campo" rows="3" maxlength="2000">{{ old('observaciones', $estudiante->observaciones) }}</textarea></label>
                </div>
            </div>
            <div class="al-form-pie">
                <a href="{{ route('rp.estudiantes.show', $estudiante) }}" class="al-btn al-btn-borde">Cancelar</a>
                <button type="submit" class="al-btn al-btn-oro"><i class="fas fa-save"></i> Guardar cambios</button>
            </div>
        </form>

        {{-- Zona de peligro --}}
        <form method="POST" action="{{ route('rp.estudiantes.eliminar', $estudiante) }}" class="al-card al-form-peligro"
            data-confirm="Se eliminará a {{ $estudiante->apellidos }}, {{ $estudiante->nombres }} y TODAS sus notas, de todas las actas. No se puede deshacer."
            data-confirm-danger data-confirm-titulo="¿Eliminar estudiante?">
            @csrf
            @method('DELETE')
            <div class="al-card-cuerpo">
                <div>
                    <strong>Eliminar estudiante</strong>
                    <p class="al-suave mb-0">Borra al estudiante y todas sus notas. Úsalo solo para registros duplicados o cargados por error.</p>
                </div>
                <button type="submit" class="al-btn al-btn-borde"><i class="fas fa-trash-alt"></i> Eliminar</button>
            </div>
        </form>
    </div>
@endsection
