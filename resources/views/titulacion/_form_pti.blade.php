{{-- Formulario de UN PTI (el vigente o uno del historial). La ampliación
     extiende este mismo PTI: RD + nuevo vencimiento (ver VigenciaPti).
     PTI aprobado (con RD): sus datos ya no se editan (el título cambia con la RD de cambio de
     tema y el asesor con la de cambio de asesor); solo se registra la ampliación o se marca vencido.
     El informe ITI del asesor se registra en la etapa de TI.
     Espera $p (Pti|null), $egresado, $selectDocente; opcional $aprobadoPorRd. --}}
@php $aprobadoPorRd = $aprobadoPorRd ?? filled($p?->rd_pti); @endphp
<form method="POST" action="{{ route('titulacion.pti.guardar', $egresado->id) }}">
    @csrf
    @if ($p) <input type="hidden" name="pti_id" value="{{ $p->id }}"> @endif
    @if ($aprobadoPorRd && $p)
        <div class="ft-subcaja mb-2">
            <div class="ft-subcaja__titulo"><i class="fas fa-lock mr-1"></i>PTI aprobado <span class="text-muted font-weight-normal">— sus datos ya no se editan</span></div>
            <dl class="ft-etapa__datos mb-0">
                <div><dt>Título</dt><dd>{{ $p->titulo ?: '—' }}</dd></div>
                <div><dt>Fecha PTI</dt><dd>{{ $p->fecha_pti?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt>Vencimiento</dt><dd>{{ $p->fecha_vencimiento?->format('d/m/Y') ?? '—' }}@if ($p->vencimiento_ampliado) · ampliado al {{ $p->vencimiento_ampliado->format('d/m/Y') }}@endif</dd></div>
                <div><dt>RD PTI</dt><dd>{{ $p->rd_pti ?: '—' }}</dd></div>
            </dl>
            <div class="small text-muted mt-1">Para cambiar el título o el asesor se emite la RD correspondiente en «Otros trámites».</div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="small font-weight-bold">Estado</label>
                <select name="estado" class="form-control form-control-sm">
                    <option value="">Aprobado (tiene RD)</option>
                    <option value="vencido" @selected($p->estado === 'vencido')>Vencido</option>
                </select>
            </div>
        </div>
    @else
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
            <label class="small font-weight-bold">Asesor/a <span class="text-muted font-weight-normal">(se suele asignar en el TI)</span></label>
            {!! $selectDocente('asesor_id', $p?->asesorValor()) !!}
        </div>
        <div class="form-group col-md-6">
            <label class="small font-weight-bold">Estado</label>
            {{-- Aprobado = tiene RD (se calcula solo); a mano solo "vencido". --}}
            <select name="estado" class="form-control form-control-sm">
                <option value="">En proceso (sin RD)</option>
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
    @endif

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

    <button type="submit" class="btn btn-sm btn-primary">{{ $aprobadoPorRd && $p ? 'Guardar ampliación / estado' : 'Guardar PTI' }}{{ $p ? ' N° '.$p->numero : '' }}</button>
</form>
