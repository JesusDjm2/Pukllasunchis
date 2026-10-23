{{-- Acción corta de una etapa ("Registrar exposición", "Registrar Bachiller"…): solo
     los campos de ese paso, contra titulacion.tesis.guardar (actualiza lo enviado y deja
     lo demás). El botón "Siguiente paso" de la ficha la abre por su id.
     Espera: $id, $titulo, $hecho (bool), $campos [[name, label, tipo, valor, opciones?, ayuda?]],
     $egresado; opcional $nota y $plano (formulario abierto, sin desplegable: panel de la ficha). --}}
@php
    // Orden del proceso: si el paso anterior no está registrado (y este tampoco), no se ofrece.
    $bloqueoPaso = (! $hecho && isset($requiere, $avance)) ? \App\Services\Titulacion\ReglasProceso::falta($avance, $requiere) : null;
    $plano = $plano ?? false;
@endphp
@if ($bloqueoPaso)
<div class="ft-paso-bloqueado mt-2" id="{{ $id }}">
    <i class="fas fa-lock fa-xs mr-1"></i>Registrar: {{ $titulo }} <span>· disponible cuando esté «{{ $bloqueoPaso }}»</span>
</div>
@else
@unless ($plano)
<details class="ft-desplegable ft-paso mt-2" id="{{ $id }}">
    <summary>
        <i class="fas {{ $hecho ? 'fa-pen' : 'fa-plus-circle' }} mr-1"></i>{{ $hecho ? 'Editar: ' : 'Registrar: ' }}{{ $titulo }}
    </summary>
@endunless
    <form method="POST" action="{{ route('titulacion.tesis.guardar', $egresado->id) }}" class="{{ $plano ? 'tp-form' : 'mt-2' }}" @if ($plano) id="{{ $id }}" @endif>
        @csrf
        <input type="hidden" name="mensaje_ok" value="paso">
        <div class="form-row">
            @foreach ($campos as $campo)
                @php [$name, $label, $tipo, $valor] = $campo; $opciones = $campo[4] ?? []; $ayuda = $campo[5] ?? null; @endphp
                <div class="form-group {{ $tipo === 'url' ? ($plano ? 'col-12' : 'col-md-6') : ($plano ? 'col-sm-6' : 'col-md-3') }} mb-2">
                    <label class="{{ $plano ? 'tp-label' : 'small font-weight-bold mb-0' }}" for="{{ $id }}-{{ $name }}">{{ $label }}</label>
                    @if ($tipo === 'select')
                        <select name="{{ $name }}" id="{{ $id }}-{{ $name }}" class="form-control {{ $plano ? '' : 'form-control-sm' }}">
                            @foreach ($opciones as $op => $etiqueta)
                                <option value="{{ $op }}" @selected($valor === $op)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="{{ $tipo === 'url' ? 'text' : $tipo }}" name="{{ $name }}" id="{{ $id }}-{{ $name }}"
                               class="form-control {{ $plano ? '' : 'form-control-sm' }}" value="{{ $valor }}" @if ($ayuda) placeholder="{{ $ayuda }}" @endif>
                    @endif
                </div>
            @endforeach
        </div>
        @isset($nota)
            <div class="small text-muted mb-2"><i class="fas fa-info-circle mr-1"></i>{{ $nota }}</div>
        @endisset
        @include('titulacion._aplicar_grupo', ['idCasilla' => $id.'-grupo'])
        <button type="submit" class="btn {{ $plano ? 'tp-btn' : 'btn-sm btn-primary' }}"><i class="fas fa-check mr-1"></i>{{ $plano ? ($hecho ? 'Guardar cambios' : 'Guardar') : 'Guardar' }}</button>
    </form>
@unless ($plano)
</details>
@endunless
@endif
