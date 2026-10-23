{{-- Lista de asesores o del jurado de una etapa (1 o más, cargo opcional) + formulario para agregar.
     Espera $lista (Participante[]), $funcion ('asesor'|'jurado'), $etapaP ('ti'|'tesis'), $idP,
     $egresado, $selectDocente; opcional $asesorPrevio (asesor registrado antes en el PTI/TI) y $plano
     (formulario de agregar siempre visible, sin desplegable: panel de la ficha). --}}
{{-- Opcional $cargoFijo: el cargo va fijo (p. ej. «Dictaminante» en la Tesis) y no se pregunta;
     $nombreFuncion y $cargosSugeridos cambian el texto y la lista de cargos ofrecidos. --}}
@php
    $cargoFijo = $cargoFijo ?? null;
    $nombreFuncion = $nombreFuncion ?? ($funcion === 'asesor' ? 'asesor(a)' : 'integrante del jurado');
    $cargosSugeridos = $cargosSugeridos ?? \App\Models\Titulacion\Participante::CARGOS[$funcion];
    $idLista = $idP.'-cargos';
    $plano = $plano ?? false;
@endphp
@if ($lista->isNotEmpty())
    <ul class="ft-participantes {{ $plano ? 'tp-lista' : '' }}">
        @foreach ($lista as $part)
            <li>
                <i class="fas {{ $funcion === 'asesor' ? 'fa-user-tie' : 'fa-gavel' }} fa-fw text-muted"></i>
                <span>{{ $part->asesorNombre() ?? '—' }}</span>
                @if ($part->cargo && ! $cargoFijo) <span class="badge badge-light border">{{ $part->cargo }}</span> @endif
                <form method="POST" action="{{ route('titulacion.participantes.eliminar', $part->id) }}" class="ml-auto"
                      data-confirm="Se quitará a {{ $part->asesorNombre() }} de la lista." data-confirm-titulo="¿Quitar?" data-confirm-boton="Sí, quitar">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-link btn-sm text-danger p-0" title="Quitar"><i class="fas fa-times"></i></button>
                </form>
            </li>
        @endforeach
    </ul>
@elseif (! empty($asesorPrevio))
    <div class="small text-muted">Registrado antes: <strong>{{ $asesorPrevio }}</strong>. Agréguelo aquí si sigue siendo su asesor(a).</div>
@endif

@unless ($plano)
<details class="ft-desplegable ft-paso mt-1" id="{{ $idP }}">
    <summary><i class="fas fa-plus-circle mr-1"></i>Agregar {{ $nombreFuncion }}</summary>
@else
<div class="tp-subtitulo" id="{{ $idP }}">Agregar {{ $nombreFuncion }}</div>
@endunless
    <form method="POST" action="{{ route('titulacion.participantes.guardar', $egresado->id) }}" class="{{ $plano ? 'tp-form' : 'mt-2' }}">
        @csrf
        <input type="hidden" name="etapa" value="{{ $etapaP }}">
        <input type="hidden" name="funcion" value="{{ $funcion }}">
        <div class="form-row">
            <div class="form-group {{ $cargoFijo ? 'col-12' : 'col-md-7' }} mb-2">
                <label class="small font-weight-bold mb-0">Docente</label>
                @if ($plano)
                    {{-- Buscador: filtra la lista del select de abajo mientras se escribe (_ficha_recursos) --}}
                    <input type="search" class="form-control tp-buscar" data-tp-buscar placeholder="Escriba un nombre para buscar…" autocomplete="off" aria-label="Buscar docente">
                @endif
                {!! $selectDocente('docentes[]', null, 'Elegir docente…', true) !!}
            </div>
            @if ($cargoFijo)
                <input type="hidden" name="cargo" value="{{ $cargoFijo }}">
            @else
                <div class="form-group col-md-5 mb-2">
                    <label class="small font-weight-bold mb-0">Cargo <span class="text-muted font-weight-normal">(opcional)</span></label>
                    <input type="text" name="cargo" maxlength="40" class="form-control form-control-sm" list="{{ $idLista }}"
                           placeholder="{{ $funcion === 'asesor' ? 'Ej.: Coasesor(a)' : 'Ej.: Presidente(a)' }}">
                    <datalist id="{{ $idLista }}">
                        @foreach ($cargosSugeridos as $cargo)
                            <option value="{{ $cargo }}">
                        @endforeach
                    </datalist>
                </div>
            @endif
        </div>
        <div class="small text-muted mb-2">Agregue uno por vez; puede repetir para sumar más.</div>
        @include('titulacion._aplicar_grupo', ['idCasilla' => $idP.'-grupo'])
        <button type="submit" class="btn {{ $plano ? 'tp-btn' : 'btn-sm btn-primary' }}"><i class="fas fa-plus mr-1"></i>Agregar</button>
    </form>
@unless ($plano)
</details>
@endunless
