@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <style>
        .pa-wrap { --pa-primary: #2e5c8a; --pa-primary-dark: #1e3a5f; --pa-border: #e3e8ef; --pa-muted: #7a869a; }

        .pa-card {
            background: #fff;
            border: 1px solid var(--pa-border);
            border-radius: .6rem;
            padding: 1.1rem 1.25rem;
            margin-bottom: 1rem;
        }

        .pa-card-title {
            font-size: .82rem;
            font-weight: 700;
            color: var(--pa-primary-dark);
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-bottom: .15rem;
        }

        .pa-card-hint {
            font-size: .76rem;
            color: var(--pa-muted);
            margin-bottom: .9rem;
        }

        .pa-field { margin-bottom: .75rem; }
        .pa-row { display: flex; flex-wrap: wrap; gap: .75rem; margin: 0 -.375rem; }
        .pa-row > .pa-field { flex: 1 1 200px; margin: 0 .375rem .75rem; }
        .pa-row .pa-field-narrow { flex: 1 1 160px; }

        .pa-field label {
            display: block;
            font-size: .76rem;
            font-weight: 600;
            color: #4b5568;
            margin-bottom: .25rem;
        }

        .pa-field .form-control {
            height: 34px;
            font-size: .82rem;
            border-radius: .4rem;
            border-color: var(--pa-border);
        }

        .pa-field .form-control:focus {
            border-color: var(--pa-primary);
            box-shadow: 0 0 0 .15rem rgba(46, 92, 138, .15);
        }

        .pa-check {
            display: flex; align-items: center; gap: .5rem;
            font-size: .82rem; color: #4b5568; margin-bottom: 1rem;
        }

        .pa-check input { width: 16px; height: 16px; margin: 0; }

        .pa-actions { display: flex; gap: .5rem; }

        .pa-btn-primary {
            background: var(--pa-primary); border-color: var(--pa-primary);
            font-size: .84rem; font-weight: 600; border-radius: .4rem;
            padding: .45rem 1.4rem;
        }

        .pa-btn-primary:hover { background: var(--pa-primary-dark); border-color: var(--pa-primary-dark); }
    </style>

    <div class="container-fluid pa-wrap">
        <div class="d-sm-flex align-items-center justify-content-between mb-3 pt-3">
            <h4 class="mb-0 text-primary font-weight-bold">Crear Período Actual</h4>
            <a href="{{ route('periodoactual.index') }}" class="btn btn-sm btn-danger shadow-sm">Volver</a>
        </div>

        <form action="{{ route('periodoactual.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="pa-card">
                <div class="pa-card-title">Datos generales</div>
                <div class="pa-field">
                    <label for="nombre">Nombre del período</label>
                    <input type="text" name="nombre" id="nombre" class="form-control"
                        value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="pa-row">
                    <div class="pa-field">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                            value="{{ old('fecha_inicio') }}">
                        @error('fecha_inicio')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="pa-field">
                        <label for="fecha_cierre">Fecha de cierre</label>
                        <input type="date" name="fecha_cierre" id="fecha_cierre" class="form-control"
                            value="{{ old('fecha_cierre') }}">
                        @error('fecha_cierre')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="pa-field mb-2">
                    <label for="horario">Imagen del horario (JPG, PNG, WEBP — máx. 4MB)</label>
                    <input type="file" name="horario" id="horario" class="form-control"
                        accept=".jpg,.jpeg,.png,.webp">
                    @error('horario')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <label class="pa-check mb-0">
                    <input type="checkbox" name="actual" value="1" {{ old('actual') ? 'checked' : '' }}>
                    Marcar como período actual
                </label>
            </div>

            <div class="pa-card">
                <div class="pa-card-title">Ventanas de calificaciones</div>
                <div class="pa-card-hint">Sin fecha, esa ventana no restringe nada (el docente puede calificar en cualquier momento).</div>
                <div class="pa-row">
                    <div class="pa-field pa-field-narrow">
                        <label for="calificaciones_parcial1_inicio">Parcial 1 — Inicio</label>
                        <input type="date" name="calificaciones_parcial1_inicio" id="calificaciones_parcial1_inicio" class="form-control"
                            value="{{ old('calificaciones_parcial1_inicio') }}">
                        @error('calificaciones_parcial1_inicio')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="pa-field pa-field-narrow">
                        <label for="calificaciones_parcial1_cierre">Parcial 1 — Cierre</label>
                        <input type="date" name="calificaciones_parcial1_cierre" id="calificaciones_parcial1_cierre" class="form-control"
                            value="{{ old('calificaciones_parcial1_cierre') }}">
                        @error('calificaciones_parcial1_cierre')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="pa-field pa-field-narrow">
                        <label for="calificaciones_parcial2_inicio">Parcial 2 / Desempeño — Inicio</label>
                        <input type="date" name="calificaciones_parcial2_inicio" id="calificaciones_parcial2_inicio" class="form-control"
                            value="{{ old('calificaciones_parcial2_inicio') }}">
                        @error('calificaciones_parcial2_inicio')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="pa-field pa-field-narrow">
                        <label for="calificaciones_parcial2_cierre">Parcial 2 / Desempeño — Cierre</label>
                        <input type="date" name="calificaciones_parcial2_cierre" id="calificaciones_parcial2_cierre" class="form-control"
                            value="{{ old('calificaciones_parcial2_cierre') }}">
                        @error('calificaciones_parcial2_cierre')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="pa-actions">
                <button type="submit" class="btn btn-primary pa-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection
