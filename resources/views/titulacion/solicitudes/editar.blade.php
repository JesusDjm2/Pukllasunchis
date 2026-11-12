{{-- Editar una solicitud de los formularios de titulación (SolicitudesController::editar), para Secretaría: corregir datos,
     vincular integrantes con su cuenta (DNI) y cambiar el estado. Campos según el tipo (PTI u originalidad). --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Models\Titulacion\Solicitud;
        $esPti = $s->tipo === Solicitud::PTI;
        $v = fn ($campo, $defecto = '') => old($campo, $defecto);
        $filas = collect(old('integrantes', $s->integrantes ?? []))->pad(\App\Http\Controllers\Titulacion\SolicitudesController::MAX_INTEGRANTES, ['nombre' => '', 'user_id' => null]);
        $nombreTipo = $esPti ? 'Solicitud de aprobación de PTI' : 'Revisión de originalidad';
        $ediciones = collect($s->datos['ediciones'] ?? [])->reverse();
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-pen"></i> Titulación · Solicitudes · Secretaría</div>
                <h1 class="tl-hero__titulo">Editar solicitud</h1>
                <p class="tl-hero__sub">{{ $nombreTipo }} recibida el {{ $s->created_at->format('d/m/Y H:i') }}{{ $s->origen === 'formulario' ? ' (formulario de Google)' : '' }}. Corrija lo que haga falta: no se envía ningún aviso a los estudiantes.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ $volver }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver sin guardar</a>
            </div>
        </header>

        <div class="al-ui">
            @if ($errors->any())
                <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div><strong>Revise el formulario:</strong><ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
            @endif

            <form method="POST" action="{{ route('titulacion.solicitudes.actualizar', $s->id) }}" class="al-form">
                @csrf
                @method('PUT')

                <div class="al-card">
                    <div class="al-card-cab"><span class="al-card-cab-icono"><i class="fas fa-users"></i></span>
                        <div><h2>{{ $esPti ? 'Grupo de investigación' : 'Tesistas' }}</h2><p>Escriba el DNI para vincular a la persona con su cuenta. Sin DNI, se conserva la cuenta ya vinculada o se busca por el nombre.</p></div></div>
                    <div class="al-card-cuerpo">
                        @foreach ($filas as $n => $i)
                            @php $cuenta = isset($i['user_id']) ? ($cuentas[$i['user_id']] ?? null) : null; @endphp
                            <div class="al-form-grid mb-2">
                                <label>Integrante {{ $n + 1 }}
                                    <input type="text" name="integrantes[{{ $n }}][nombre]" value="{{ $i['nombre'] ?? '' }}" class="al-campo" maxlength="200" placeholder="Apellidos y nombres">
                                </label>
                                <label>DNI (para vincular)
                                    <input type="text" name="integrantes[{{ $n }}][dni]" value="{{ $i['dni'] ?? '' }}" class="al-campo" inputmode="numeric" maxlength="12" placeholder="{{ $cuenta?->dni ?: 'Opcional' }}">
                                    <small class="text-muted">
                                        @if ($cuenta)
                                            <i class="fas fa-link fa-xs mr-1"></i>Vinculado a {{ $cuenta->apellidos }}, {{ $cuenta->name }}
                                        @elseif (filled($i['nombre'] ?? null))
                                            <i class="fas fa-unlink fa-xs mr-1"></i>Sin cuenta vinculada
                                        @endif
                                    </small>
                                </label>
                            </div>
                        @endforeach
                        <div class="al-form-grid mt-3">
                            <label>Correo<input type="text" name="correo" value="{{ $v('correo', $s->correo) }}" class="al-campo" maxlength="150"></label>
                            <label>Celulares<input type="text" name="celulares" value="{{ $v('celulares', $s->celulares) }}" class="al-campo" maxlength="200"></label>
                        </div>
                    </div>
                </div>

                <div class="al-card">
                    <div class="al-card-cab"><span class="al-card-cab-icono"><i class="fas fa-file-alt"></i></span><div><h2>Solicitud</h2><p>Datos del trabajo.</p></div></div>
                    <div class="al-card-cuerpo">
                        <div class="al-form-grid">
                            @unless ($esPti)
                                <label>Documento
                                    <select name="etapa" class="al-campo" required>
                                        @foreach (Solicitud::ETAPAS as $k => $n)<option value="{{ $k }}" @selected($v('etapa', $s->etapa()) === $k)>{{ $n }}</option>@endforeach
                                    </select>
                                </label>
                            @endunless
                            <label class="al-form-ancho">Título
                                <textarea name="titulo" rows="3" class="al-campo" required maxlength="1000">{{ $v('titulo', $s->titulo) }}</textarea>
                            </label>
                            <label>Programa de Estudios
                                <select name="programa_estudios" class="al-campo">
                                    <option value="">—</option>
                                    @foreach (array_unique(array_filter([...Solicitud::PROGRAMAS_ESTUDIOS, $s->programa_estudios])) as $p)<option value="{{ $p }}" @selected($v('programa_estudios', $s->programa_estudios) === $p)>{{ $p }}</option>@endforeach
                                </select>
                            </label>
                            <label>Ciclo o programa<input type="text" name="ciclo" value="{{ $v('ciclo', $s->ciclo) }}" class="al-campo" maxlength="30" list="se-ciclos"></label>
                            <datalist id="se-ciclos">@foreach (Solicitud::CICLOS as $c)<option value="{{ $c }}">@endforeach</datalist>
                            @if ($esPti)
                                <label>Área de investigación
                                    <select name="area" class="al-campo"><option value="">—</option>@foreach (Solicitud::AREAS as $k => $n)<option value="{{ $k }}" @selected($v('area', $s->datos['area'] ?? '') === $k)>{{ $n }}</option>@endforeach</select>
                                    @if ($s->datos['area_texto'] ?? null)<small class="text-muted">Escribieron: {{ $s->datos['area_texto'] }}</small>@endif
                                </label>
                                <label>Línea de investigación
                                    <select name="linea" class="al-campo"><option value="">—</option>@foreach (Solicitud::LINEAS as $k => $n)<option value="{{ $k }}" @selected($v('linea', $s->datos['linea'] ?? '') === $k)>{{ $n }}</option>@endforeach</select>
                                    @if ($s->datos['linea_texto'] ?? null)<small class="text-muted">Escribieron: {{ $s->datos['linea_texto'] }}</small>@endif
                                </label>
                            @else
                                <label>Fecha de pago<input type="date" name="fecha_pago" value="{{ $v('fecha_pago', $s->datos['fecha_pago'] ?? '') }}" class="al-campo"></label>
                                <label>% de similitud (del reporte)<input type="number" step="0.01" min="0" max="100" name="similitud" value="{{ $v('similitud', $s->similitud()) }}" class="al-campo" placeholder="Sin reporte"></label>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="al-card">
                    <div class="al-card-cab"><span class="al-card-cab-icono"><i class="fas fa-flag"></i></span><div><h2>Estado</h2><p>Por ejemplo, para marcar como atendida una solicitud resuelta fuera del sistema.</p></div></div>
                    <div class="al-card-cuerpo">
                        <div class="al-form-grid">
                            <label>Estado
                                <select name="estado" class="al-campo" required>@foreach (Solicitud::ESTADOS as $k => $n)<option value="{{ $k }}" @selected($v('estado', $s->estado) === $k)>{{ $n }}</option>@endforeach</select>
                            </label>
                            <label class="al-form-ancho">Nota u observación
                                <textarea name="observacion" rows="2" class="al-campo" maxlength="1500">{{ $v('observacion', $s->observacion) }}</textarea>
                                <small class="text-muted">Si la solicitud está observada, es lo que verá el estudiante.</small>
                            </label>
                        </div>
                    </div>
                    <div class="al-form-pie">
                        <a href="{{ $volver }}" class="al-btn al-btn-borde">Cancelar</a>
                        <button type="submit" class="al-btn al-btn-petroleo"><i class="fas fa-save"></i> Guardar cambios</button>
                    </div>
                </div>
            </form>

            @if ($ediciones->isNotEmpty())
                <div class="al-card">
                    <div class="al-card-cab"><span class="al-card-cab-icono"><i class="fas fa-history"></i></span><div><h2>Ediciones anteriores</h2><p>{{ $ediciones->count() }} {{ $ediciones->count() === 1 ? 'edición' : 'ediciones' }}.</p></div></div>
                    <div class="al-tabla-marco"><table class="al-tabla">
                        <thead><tr><th>Fecha</th><th>Por</th><th>Qué cambió</th></tr></thead>
                        <tbody>@foreach ($ediciones as $e)
                            <tr><td>{{ \Carbon\Carbon::parse($e['en'])->format('d/m/Y H:i') }}</td><td>{{ $e['nombre'] ?? '—' }}</td><td><small>{{ implode(', ', $e['campos'] ?? []) }}</small></td></tr>
                        @endforeach</tbody>
                    </table></div>
                </div>
            @endif
        </div>
    </div>
@endsection
