{{-- Secretaría corrige el grupo de titulación (CambioGrupoController): 1) elegir el cambio, 2) revisar
     «Así quedará» (no guarda nada) y 3) guardar con motivo, verificación y alerta. Paso delicado: los datos
     se están pasando del Excel al sistema y pueden no estar sincronizados. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
@php
    $volver = route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'datos']);
    $nombre = fn ($u) => trim($u->apellidos.', '.$u->name);
    $pti = $s->pti;
    $r = $revision;
@endphp
@include('titulacion._rdg_estilos')
<link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">

<div class="container-fluid py-3 rdg">
    <a href="{{ $volver }}" class="small text-muted"><i class="fas fa-arrow-left fa-xs mr-1"></i> Volver a la ficha</a>
    <h4 class="font-weight-bold mt-2 mb-1"><i class="fas fa-people-arrows mr-2" style="color:#4e73df;"></i>Corregir grupo · {{ $nombre($egresado) }}</h4>
    <p class="rdg-ayuda mb-3">
        Para registrar que esta persona <strong>se retiró de su grupo</strong> o <strong>se incorporó a otro</strong> cuando la RD ya se emitió.
        Si la RD todavía no existe, genérela desde la ficha (Trámites extraordinarios): al emitirla, el grupo cambia solo.
    </p>

    <div class="gc-alerta">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Paso delicado: revise antes de guardar.</strong>
            Los registros se están pasando del Excel al sistema y algunos datos aún no están sincronizados. Antes de cambiar el grupo,
            compárelo con el expediente y la RD que lo dispone. El cambio afecta a esta persona y a los grupos de origen y de destino.
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- 1. Situación actual --}}
    <div class="rdg-card">
        <div class="rdg-h"><span class="n">1</span> Grupo actual</div>
        <div class="rdg-elegidos">
            @foreach (collect([$egresado])->concat($companeros) as $u)
                <div class="rdg-chip {{ $u->id === $egresado->id ? 'is-titular' : '' }}">
                    <span class="rdg-chip-ini">{{ mb_substr($u->apellidos ?: $u->name, 0, 1) }}</span>
                    <span class="rdg-chip-txt"><strong>{{ $nombre($u) }}</strong><small>DNI {{ $u->dni ?: '—' }}{{ $u->id === $egresado->id ? ' · esta persona' : '' }}</small></span>
                </div>
            @endforeach
        </div>
        @if ($companeros->isEmpty())
            <div class="rdg-ayuda">Hoy figura <strong>sin grupo</strong> (individual o el grupo no está registrado).</div>
        @endif
        <div class="rdg-ayuda mt-1">
            <strong>PTI:</strong> {{ $pti?->titulo ? '«'.\Illuminate\Support\Str::limit($pti->titulo, 140).'»' : 'sin título registrado' }}
            · RD {{ $pti?->rd_pti ?: '—' }} · Asesor(a): {{ $pti?->asesorNombre() ?: '—' }}
        </div>
    </div>

    {{-- 2. Elegir el cambio (GET: solo muestra la revisión) --}}
    <form method="GET" action="{{ route('titulacion.grupo.editar', $egresado->id) }}" class="rdg-card" id="gcElegir">
        <div class="rdg-h"><span class="n">2</span> ¿Qué cambio?</div>
        <div class="gc-opciones">
            <label class="gc-opcion"><input type="radio" name="accion" value="mover" @checked($accion !== 'retirar') required>
                <span><i class="fas fa-exchange-alt"></i><strong>Pasar a otro grupo</strong><small>Se retiró de su grupo y se incorporó a otro.</small></span></label>
            <label class="gc-opcion"><input type="radio" name="accion" value="retirar" @checked($accion === 'retirar')>
                <span><i class="fas fa-user-minus"></i><strong>Dejar sin grupo</strong><small>Se retiró y sigue de forma individual (o con un PTI nuevo).</small></span></label>
        </div>

        <div class="gc-mover" @if ($accion === 'retirar') hidden @endif>
            <label for="gcBuscar" class="mt-3 mb-1">Grupo al que se incorpora: elija a uno de sus integrantes</label>
            <input type="search" id="gcBuscar" class="form-control form-control-sm mb-1" placeholder="Filtrar por nombre o DNI…" autocomplete="off">
            <select name="destino" id="gcDestino" class="form-control form-control-sm" size="7">
                @foreach ($candidatos as $c)
                    <option value="{{ $c->id }}" @selected((int) ($filtros['destino'] ?? 0) === $c->id)
                            data-buscar="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($c->apellidos.' '.$c->name.' '.$c->dni)) }}">
                        {{ $nombre($c) }} · DNI {{ $c->dni ?: '—' }}{{ $c->seguimientoTitulacion?->numero_expediente ? ' · Exp. '.$c->seguimientoTitulacion->numero_expediente : '' }}
                    </option>
                @endforeach
            </select>
            <div class="rdg-ayuda">Solo personas del mismo programa con proceso de titulación. Al revisar verá a todo ese grupo.</div>
        </div>

        <label class="gc-check mt-3">
            <input type="hidden" name="adoptar_pti" value="0">
            <input type="checkbox" name="adoptar_pti" value="1" @checked($filtros['adoptar_pti'] ?? true)>
            <span class="js-gc-pti" data-mover="Adopta el PTI del grupo al que se incorpora (título, RD, asesor y vigencia); su PTI actual pasa a su historial."
                  data-retirar="Su PTI actual (el del grupo que deja) pasa a su historial.">
                {{ $accion === 'retirar' ? 'Su PTI actual (el del grupo que deja) pasa a su historial.' : 'Adopta el PTI del grupo al que se incorpora (título, RD, asesor y vigencia); su PTI actual pasa a su historial.' }}
            </span>
        </label>

        <div class="mt-3"><button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search mr-1"></i>Revisar el cambio</button>
            <span class="rdg-ayuda ml-2">Todavía no se guarda nada.</span></div>
    </form>

    {{-- 3. Revisión y confirmación --}}
    @if ($r)
        <form method="POST" action="{{ route('titulacion.grupo.cambiar', $egresado->id) }}" class="rdg-card gc-revision" id="gcAplicar">
            @csrf
            <input type="hidden" name="accion" value="{{ $accion }}">
            <input type="hidden" name="destino" value="{{ $r['destinoUser']?->id }}">
            <input type="hidden" name="adoptar_pti" value="{{ $r['adoptarPti'] ? 1 : 0 }}">
            <div class="rdg-h"><span class="n">3</span> Así quedará</div>

            <div class="gc-antes-despues">
                <div>
                    <div class="gc-col-titulo">Ahora</div>
                    <ul>
                        @forelse ($companeros as $u)<li>{{ $nombre($u) }}</li>@empty<li class="text-muted">Sin compañeros</li>@endforelse
                        <li class="gc-el"><i class="fas fa-user"></i> {{ $nombre($egresado) }}</li>
                    </ul>
                </div>
                <div class="gc-flecha"><i class="fas fa-arrow-right"></i></div>
                <div>
                    <div class="gc-col-titulo">{{ $r['destino'] ? 'Después' : 'Después: sin grupo' }}</div>
                    <ul>
                        @foreach ($r['miembrosDestino'] as $u)<li>{{ $nombre($u) }}</li>@endforeach
                        <li class="gc-el is-nuevo"><i class="fas fa-user-plus"></i> {{ $nombre($egresado) }}</li>
                    </ul>
                </div>
            </div>

            <ul class="gc-consecuencias">
                @if ($companeros->isNotEmpty())
                    <li>Deja el grupo de <strong>{{ $companeros->map($nombre)->implode(', ') }}</strong>. Sus RD grupales (PTI, TI, Tesis) se conservan, pero ya no lo unen a ese grupo.</li>
                @endif
                @if ($r['destino'])
                    <li>Integra el grupo de <strong>{{ $r['miembrosDestino']->map($nombre)->implode(', ') }}</strong>.</li>
                @endif
                @if ($r['adoptarPti'] && $r['destino'])
                    @if ($r['ptiDestino'])
                        <li>Toma el PTI del grupo: <strong>«{{ \Illuminate\Support\Str::limit((string) $r['ptiDestino']->titulo, 120) ?: 'sin título' }}»</strong> · RD {{ $r['ptiDestino']->rd_pti ?: '—' }} · Asesor(a): {{ $r['ptiDestino']->asesorNombre() ?: '—' }}. Su PTI actual pasa a su historial.</li>
                    @else
                        <li class="gc-ojo">El grupo destino <strong>no tiene PTI registrado</strong>: no se le copiará ninguno.</li>
                    @endif
                @elseif ($r['adoptarPti'])
                    <li>Su PTI actual pasa a su historial (vencido).</li>
                @else
                    <li>Conserva su PTI actual sin cambios.</li>
                @endif
                <li>Queda constancia en las observaciones del expediente: quién, cuándo, de qué grupo a cuál y el motivo.</li>
            </ul>

            @if ($r['actasCompartidas']->isNotEmpty())
                <div class="gc-aviso"><i class="fas fa-info-circle"></i> Comparte el acta {{ $r['actasCompartidas']->implode(', ') }} con su grupo actual: por esa acta seguirá apareciendo junto a ellos hasta que se corrija el acta.</div>
            @endif
            @if ($r['destino'] && $r['ptiDestino'] && blank($r['ptiDestino']->rd_pti))
                <div class="gc-aviso"><i class="fas fa-info-circle"></i> El PTI del grupo destino todavía no tiene RD registrada: verifique ese dato en el expediente.</div>
            @endif

            <div class="form-row mt-3">
                <div class="form-group col-md-3">
                    <label for="gcRetN">RD de retiro (si ya se emitió)</label>
                    <input type="text" name="retiro_numero" id="gcRetN" class="form-control form-control-sm" value="{{ old('retiro_numero') }}" placeholder="Ej.: 045-2026">
                </div>
                <div class="form-group col-md-3">
                    <label for="gcRetF">Fecha de esa RD</label>
                    <input type="date" name="retiro_fecha" id="gcRetF" class="form-control form-control-sm" value="{{ old('retiro_fecha') }}">
                </div>
                @if ($r['destino'])
                    <div class="form-group col-md-3">
                        <label for="gcIncN">RD de incorporación (si ya se emitió)</label>
                        <input type="text" name="incorporacion_numero" id="gcIncN" class="form-control form-control-sm" value="{{ old('incorporacion_numero') }}" placeholder="Ej.: 046-2026">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="gcIncF">Fecha de esa RD</label>
                        <input type="date" name="incorporacion_fecha" id="gcIncF" class="form-control form-control-sm" value="{{ old('incorporacion_fecha') }}">
                    </div>
                @endif
                <div class="form-group col-12">
                    <label for="gcMotivo">Motivo o sustento del cambio <span class="text-danger">*</span></label>
                    <textarea name="motivo" id="gcMotivo" rows="2" class="form-control form-control-sm" required minlength="10" maxlength="1000"
                              placeholder="Ej.: RD N° 045-2026 de retiro y 046-2026 de incorporación; dato corregido según el expediente físico.">{{ old('motivo') }}</textarea>
                    <div class="rdg-ayuda">Las RD que indique se registran sin PDF; puede subirlo después desde la ficha.</div>
                </div>
            </div>

            <label class="gc-verificado">
                <input type="checkbox" name="verificado" value="1" required>
                <span>Verifiqué en el expediente y en la RD que este cambio es correcto.</span>
            </label>

            <div class="rdg-acciones mt-3">
                <span class="rdg-ayuda">Revise «Así quedará» antes de guardar.</span>
                <a href="{{ $volver }}" class="btn btn-sm btn-light ml-auto">Cancelar</a>
                <button type="submit" class="btn btn-sm btn-danger" id="gcGuardar"><i class="fas fa-people-arrows mr-1"></i> Guardar cambio de grupo</button>
            </div>
        </form>
    @endif
</div>


<script>
(function () {
    // Filtro del selector de grupo destino.
    var buscar = document.getElementById('gcBuscar'), sel = document.getElementById('gcDestino');
    if (buscar && sel) buscar.addEventListener('input', function () {
        var q = buscar.value.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
        Array.prototype.forEach.call(sel.options, function (o) { o.hidden = q.length > 1 && o.dataset.buscar.indexOf(q) === -1; });
    });
    // Mover / retirar: muestra el selector y cambia el texto del PTI.
    var elegir = document.getElementById('gcElegir');
    elegir.addEventListener('change', function (e) {
        if (e.target.name !== 'accion') return;
        var mover = e.target.value === 'mover';
        elegir.querySelector('.gc-mover').hidden = !mover;
        sel.required = mover;
        var t = elegir.querySelector('.js-gc-pti'); t.textContent = mover ? t.dataset.mover : t.dataset.retirar;
    });
    sel.required = elegir.querySelector('input[name="accion"]:checked').value === 'mover';

    // Guardar: alerta final con el resumen. Es un paso delicado (datos aún en migración desde el Excel).
    var form = document.getElementById('gcAplicar');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        if (form.dataset.ok === '1') return;
        e.preventDefault();
        if (!form.checkValidity()) { form.reportValidity(); return; }
        var titulo = '¿Guardar el cambio de grupo?';
        var html = '<div style="text-align:left;font-size:.92rem">'
            + 'Se cambiará el grupo de <strong>{{ e($nombre($egresado)) }}</strong>'
            + @json($r && $r['destino'] ? ' al grupo de '.$r['miembrosDestino']->map($nombre)->implode(', ').'.' : ': quedará sin grupo.')
            + '<br><br>Los datos aún se están pasando del Excel: confirme solo si lo verificó en el expediente y en la RD.</div>';
        var seguir = function () { form.dataset.ok = '1'; form.submit(); };
        if (window.PkAlert) PkAlert.confirmar({ icon: 'warning', title: titulo, html: html, confirmButtonText: 'Sí, cambiar de grupo', cancelButtonText: 'Revisar de nuevo' }).then(function (ok) { if (ok) seguir(); });
        else if (confirm(titulo + '\nConfirme solo si lo verificó en el expediente y en la RD.')) seguir();
    });
})();
</script>
@endsection
