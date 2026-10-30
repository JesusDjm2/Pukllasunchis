{{-- Paso «Enlace del TI» / «Enlace de la Tesis»: el estudiante comparte el enlace (enviado) y el
     asesor lo aprueba (aprobado). Con el enlace aprobado se habilita la RD de aprobación de la etapa.
     Espera: $etapaEnlace ('ti' | 'tesis'), $id, $requiere (hito que abre la etapa), $tesis, $avance,
     $egresado, $integrantes. Guarda en titulacion.tesis.guardar (link_{etapa}, _estado y fechas). --}}
@php
    $campo = 'link_'.$etapaEnlace;
    $estadoEnl = $tesis?->estadoEnlace($etapaEnlace);
    $enviadoEn = $tesis?->{$campo.'_enviado_en'};
    $aprobadoEn = $tesis?->{$campo.'_aprobado_en'};
    $nombre = $etapaEnlace === 'ti' ? 'del TI' : 'de la Tesis';
    $bloqueoEnl = ! $estadoEnl ? \App\Services\Titulacion\ReglasProceso::falta($avance, $requiere) : null;
@endphp

@if ($estadoEnl)
    <a href="{{ $tesis->{$campo} }}" target="_blank" rel="noopener" class="tp-enlace"><i class="fas fa-file-alt"></i><span>Abrir el enlace {{ $nombre }}</span><i class="fas fa-external-link-alt"></i></a>
    <div class="tp-enlace-estados">
        <span class="tp-chip tp-chip--hecho"><i class="fas fa-paper-plane"></i> Enviado por el estudiante{{ $enviadoEn ? ' · '.$enviadoEn->format('d/m/Y') : '' }}</span>
        @if ($estadoEnl === 'aprobado')
            <span class="tp-chip tp-chip--hecho"><i class="fas fa-check-double"></i> Aprobado por el asesor{{ $aprobadoEn ? ' · '.$aprobadoEn->format('d/m/Y') : '' }}</span>
        @else
            <span class="tp-chip tp-chip--espera"><i class="fas fa-hourglass-half"></i> Falta la aprobación del asesor</span>
        @endif
    </div>
@endif

@if ($bloqueoEnl)
    <div class="ft-paso-bloqueado mt-2" id="{{ $id }}">
        <i class="fas fa-lock fa-xs mr-1"></i>Registrar: enlace {{ $nombre }} <span>· disponible cuando esté «{{ $bloqueoEnl }}»</span>
    </div>
@else
    <form method="POST" action="{{ route('titulacion.tesis.guardar', $egresado->id) }}" class="tp-form" id="{{ $id }}">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.tesis.guardar'))>
        @csrf
        <input type="hidden" name="mensaje_ok" value="paso">
        <div class="form-row">
            <div class="form-group col-12 mb-2">
                <label class="tp-label" for="{{ $id }}-link">Enlace {{ $nombre }} (lo comparte el estudiante)</label>
                <input type="url" name="{{ $campo }}" id="{{ $id }}-link" class="form-control" value="{{ $tesis?->{$campo} }}" placeholder="https://drive.google.com/…">
            </div>
            <div class="form-group col-12 mb-2">
                <span class="tp-label d-block">Estado</span>
                <div class="tp-enlace-opciones">
                    @foreach (\App\Models\Titulacion\Tesis::ESTADOS_ENLACE as $valor => $etiqueta)
                        <label class="tp-enlace-opcion">
                            <input type="radio" name="{{ $campo }}_estado" value="{{ $valor }}" @checked(($estadoEnl ?? 'enviado') === $valor)>
                            <span><i class="fas {{ $valor === 'aprobado' ? 'fa-check-double' : 'fa-paper-plane' }}"></i> {{ $etiqueta }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="form-group col-sm-6 mb-2">
                <label class="tp-label" for="{{ $id }}-env">Fecha de envío</label>
                <input type="date" name="{{ $campo }}_enviado_en" id="{{ $id }}-env" class="form-control" value="{{ $enviadoEn?->format('Y-m-d') }}">
            </div>
            <div class="form-group col-sm-6 mb-2">
                <label class="tp-label" for="{{ $id }}-apr">Fecha de aprobación</label>
                <input type="date" name="{{ $campo }}_aprobado_en" id="{{ $id }}-apr" class="form-control" value="{{ $aprobadoEn?->format('Y-m-d') }}">
            </div>
        </div>
        <div class="small text-muted mb-2"><i class="fas fa-info-circle mr-1"></i>Si deja las fechas vacías se registra la de hoy. Con el enlace aprobado por el asesor se habilita la RD de aprobación {{ $etapaEnlace === 'ti' ? 'de exposición' : 'de sustentación' }}.</div>
        @include('titulacion._aplicar_grupo', ['idCasilla' => $id.'-grupo'])
        <button type="submit" class="btn tp-btn"><i class="fas fa-check mr-1"></i>{{ $estadoEnl ? 'Guardar cambios' : 'Guardar' }}</button>
    </fieldset>
</form>
@endif
