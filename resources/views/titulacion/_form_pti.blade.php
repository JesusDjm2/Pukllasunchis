{{-- Formulario de UN PTI (el vigente o uno del historial). La ampliación
     extiende este mismo PTI: RD + nuevo vencimiento (ver VigenciaPti).
     Espera $p (Pti|null), $egresado, $selectDocente; opcional $aprobadoPorRd. --}}
@php $aprobadoPorRd = $aprobadoPorRd ?? filled($p?->rd_pti); @endphp
<form method="POST" action="{{ route('titulacion.pti.guardar', $egresado->id) }}">
    @csrf
    @if ($p) <input type="hidden" name="pti_id" value="{{ $p->id }}"> @endif
    <div class="form-row">
        <div class="form-group col-md-8">
            <label class="small font-weight-bold">Título</label>
            <textarea name="titulo" class="form-control form-control-sm" rows="2">{{ $p?->titulo ?? '' }}</textarea>
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold">PTI para el grado de</label>
            <select name="etapa" class="form-control form-control-sm">
                @foreach (\App\Models\Titulacion\Pti::ETAPAS as $clave => $nombre)
                    <option value="{{ $clave }}" @selected(($p?->etapa ?? 'bachiller') === $clave)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label class="small font-weight-bold">Asesor/a</label>
            {!! $selectDocente('asesor_id', $p?->asesorValor()) !!}
        </div>
        <div class="form-group col-md-6">
            <label class="small font-weight-bold">Estado</label>
            {{-- Aprobado = tiene RD (se calcula solo); a mano solo "vencido". --}}
            <select name="estado" class="form-control form-control-sm">
                <option value="">{{ $aprobadoPorRd ? 'Aprobado (tiene RD)' : 'En proceso (sin RD)' }}</option>
                <option value="vencido" @selected($p?->estado === 'vencido')>Vencido</option>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label class="small font-weight-bold">Fecha PTI</label>
            <input type="date" name="fecha_pti" class="form-control form-control-sm" value="{{ $p?->fecha_pti?->format('Y-m-d') }}">
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold">Fecha vencimiento</label>
            <input type="date" name="fecha_vencimiento" class="form-control form-control-sm" value="{{ $p?->fecha_vencimiento?->format('Y-m-d') }}">
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold">RD PTI</label>
            <input type="text" name="rd_pti" class="form-control form-control-sm" value="{{ $p?->rd_pti ?? '' }}">
        </div>
    </div>
    <div class="form-group">
        <label class="small font-weight-bold">Link ITI</label>
        <input type="text" name="link_iti" class="form-control form-control-sm" value="{{ $p?->link_iti ?? '' }}">
    </div>

    <div class="ft-subcaja">
        <div class="ft-subcaja__titulo"><i class="fas fa-hourglass-half mr-1"></i>Ampliación del PTI <span class="text-muted font-weight-normal">— si se otorgó con RD, extiende este mismo PTI</span></div>
        <div class="form-row mb-0">
            <div class="form-group col-md-4 mb-2">
                <label class="small font-weight-bold">RD de ampliación</label>
                <input type="text" name="rd_ampliacion" class="form-control form-control-sm" value="{{ $p?->rd_ampliacion ?? '' }}">
            </div>
            <div class="form-group col-md-4 mb-2">
                <label class="small font-weight-bold">Fecha de la RD</label>
                <input type="date" name="fecha_ampliacion" class="form-control form-control-sm" value="{{ $p?->fecha_ampliacion?->format('Y-m-d') }}">
            </div>
            <div class="form-group col-md-4 mb-2">
                <label class="small font-weight-bold">Nuevo vencimiento</label>
                <input type="date" name="vencimiento_ampliado" class="form-control form-control-sm" value="{{ $p?->vencimiento_ampliado?->format('Y-m-d') }}">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-sm btn-primary">Guardar PTI{{ $p ? ' N° '.$p->numero : '' }}</button>
</form>
