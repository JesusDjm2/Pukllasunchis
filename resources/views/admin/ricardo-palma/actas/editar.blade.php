@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Editar acta ' . ($acta->numero_acta ?: '#' . $acta->id))

@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid py-3 al-ui rp-modulo">
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.actas.show', $acta) }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Volver al acta
                        </a>
                    </div>
                    <h1>Editar acta {{ $acta->numero_acta ? 'N° ' . $acta->numero_acta : 'sin número' }}</h1>
                    <p class="al-hero-sub">{{ $acta->programa?->nombre }} · Periodo {{ $acta->periodo?->codigo }} · Semestre {{ $acta->numero_semestre }}</p>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        @if ($errors->any())
            <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i>
                <div>@foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
            </div>
        @endif

        <form method="POST" action="{{ route('rp.actas.actualizar', $acta) }}" class="al-form">
            @csrf
            @method('PUT')

            <article class="al-card">
                <div class="al-card-cab">
                    <span class="al-card-cab-icono"><i class="fas fa-file-invoice"></i></span>
                    <div>
                        <h2>Datos del acta</h2>
                        <p>Periodo, carrera y semestre no se cambian aquí: si están mal, elimina el acta y vuelve a cargarla.</p>
                    </div>
                </div>
                <div class="al-card-cuerpo">
                    <div class="al-form-grid">
                        <label>N° de acta<input type="text" name="numero_acta" class="al-campo" maxlength="50" value="{{ old('numero_acta', $acta->numero_acta) }}" placeholder="Vacío si no tiene número"></label>
                        <label>Fecha de visación<input type="date" name="fecha_emision" class="al-campo" value="{{ old('fecha_emision', $acta->fecha_emision?->format('Y-m-d')) }}"></label>
                        <label>Sección *<input type="text" name="seccion" class="al-campo" required maxlength="30" value="{{ old('seccion', $acta->seccion) }}"></label>
                        <label>Turno *<input type="text" name="turno" class="al-campo" required maxlength="30" value="{{ old('turno', $acta->turno) }}"></label>
                        <label class="al-form-ancho">Observaciones del acta<textarea name="observaciones" class="al-campo" rows="2" maxlength="2000">{{ old('observaciones', $acta->observaciones) }}</textarea></label>
                    </div>
                </div>
            </article>

            <article class="al-card">
                <div class="al-card-cab">
                    <span class="al-card-cab-icono"><i class="fas fa-table"></i></span>
                    <div>
                        <h2>Notas, condición y observación</h2>
                        <p>Nota vacía = sin nota (retirado, licencia). El puntaje se recalcula solo. Una observación que empiece con RETIRAD o LICENCIA marca al alumno como retirado o con licencia.</p>
                    </div>
                </div>
                <div class="al-tabla-marco">
                    <table class="al-tabla rp-editar-notas">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                @foreach ($cursos as $c)
                                    <th class="text-center" title="{{ $c->nombre }} ({{ $c->creditos }} créditos)">
                                        <span class="rp-editar-curso">{{ $c->nombre }}</span>
                                        <small>{{ $c->creditos }} crd</small>
                                    </th>
                                @endforeach
                                <th>Observación del alumno</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantes as $item)
                                @php $id = $item->estudiante?->id; @endphp
                                <tr>
                                    <td class="text-nowrap">
                                        <strong>{{ $item->estudiante?->apellidos }}</strong>, {{ $item->estudiante?->nombres }}
                                        <br><small>Mat. {{ $item->estudiante?->numero_matricula ?: '—' }}</small>
                                    </td>
                                    @foreach ($cursos as $c)
                                        @php $calif = $item->notas->get($c->id); @endphp
                                        <td class="text-center">
                                            @if ($calif)
                                                @php $valor = old("notas.{$calif->id}", $calif->calificacion !== null ? rtrim(rtrim(number_format($calif->calificacion, 2, '.', ''), '0'), '.') : ''); @endphp
                                                <input type="number" name="notas[{{ $calif->id }}]" class="al-campo rp-campo-nota {{ $valor !== '' && (float) $valor < 11 ? 'is-mal' : '' }}"
                                                    min="0" max="20" step="0.01" value="{{ $valor }}" aria-label="Nota de {{ $item->estudiante?->apellidos }} en {{ $c->nombre }}">
                                            @else
                                                <span class="al-suave" title="No figura en este curso">·</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td><input type="text" name="obs_alumno[{{ $id }}]" class="al-campo rp-campo-obs" maxlength="255" value="{{ old("obs_alumno.$id", $item->observacion) }}" placeholder="Ej: RETIRADA R.D. N°…"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="al-form-pie">
                    <a href="{{ route('rp.actas.show', $acta) }}" class="al-btn al-btn-borde">Cancelar</a>
                    <button type="submit" class="al-btn al-btn-oro"><i class="fas fa-save"></i> Guardar cambios</button>
                </div>
            </article>
        </form>

        {{-- Zona de peligro --}}
        <form method="POST" action="{{ route('rp.actas.eliminar', $acta) }}" class="al-card al-form-peligro"
            data-confirm="Se eliminará el acta {{ $acta->numero_acta ? 'N° ' . $acta->numero_acta : 'sin número' }} ({{ $acta->periodo?->codigo }}) con todas sus notas. Los estudiantes se conservan. No se puede deshacer."
            data-confirm-danger data-confirm-titulo="¿Eliminar acta?">
            @csrf
            @method('DELETE')
            <div class="al-card-cuerpo">
                <div>
                    <strong>Eliminar acta</strong>
                    <p class="al-suave mb-0">Borra el acta y sus {{ $acta->calificaciones->count() }} notas. Úsalo si se cargó con el periodo, la carrera o el semestre equivocados, y vuelve a subirla.</p>
                </div>
                <button type="submit" class="al-btn al-btn-borde"><i class="fas fa-trash-alt"></i> Eliminar</button>
            </div>
        </form>
    </div>
@endsection
