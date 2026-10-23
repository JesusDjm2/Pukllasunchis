{{-- Iniciar el proceso de titulación (InicioProcesoController): datos del PTI (el asesor se asigna
     en el TI), N° de expediente correlativo de cada integrante y el grupo (o individual). Los
     integrantes son estudiantes o egresados que ya existen en el sistema (FID o PPD); se muestran
     sus celulares de la ficha. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
@php
    $volver = route('titulacion.index', ['ver' => $persona->id]);
    $previos = collect(old('integrantes', []))->map(fn ($id) => \App\Models\User::find($id))->filter()
        ->map(fn ($u) => \App\Http\Controllers\Titulacion\InicioProcesoController::datosPersona($u))->values();
@endphp
@include('titulacion._rdg_estilos')
<style>
    .ini-exp { font-family: SFMono-Regular, Consolas, monospace; font-weight: 700; color: #4338ca; background: #eef2ff; border-radius: 6px; padding: .05rem .4rem; font-size: .78rem; white-space: nowrap; }
    .dark-mode .ini-exp, .dim-mode .ini-exp { background: rgba(165, 180, 252, .15); color: #c7d2fe; }
    .ini-tel { display: inline-flex; align-items: center; gap: .25rem; margin-right: .6rem; }
    .ini-tel a { color: inherit; }
    .ini-buscador { display: flex; gap: .5rem; }
    .ini-buscador input { flex: 1 1 auto; min-width: 0; }
    .ini-buscador select { flex: 0 0 auto; width: auto; min-width: 16rem; max-width: 45%; }
    @media (max-width: 575.98px) { .ini-buscador { flex-direction: column; } }
    .ini-modalidad { display: inline-block; margin-left: .35rem; font-size: .66rem; font-weight: 800; letter-spacing: .04em; border-radius: 6px; padding: .1rem .4rem; vertical-align: middle; }
    .ini-modalidad--fid { color: #3b5bdb; background: #edf2ff; }
    .ini-modalidad--ppd { color: #b45309; background: #fff4dc; }
    .rdg-res-grupo { position: sticky; top: 0; z-index: 1; padding: .35rem .75rem; font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #64748b; background: #f6f8fb; border-bottom: 1px solid #e6e9f0; }
    .rdg-res-grupo span { font-weight: 600; letter-spacing: 0; text-transform: none; margin-left: .25rem; }
    .dark-mode .rdg-res-grupo, .dim-mode .rdg-res-grupo { background: rgba(255, 255, 255, .05); border-color: rgba(255, 255, 255, .1); color: inherit; }
    .dark-mode .ini-modalidad, .dim-mode .ini-modalidad { background: rgba(255, 255, 255, .1); color: inherit; }
</style>

<div class="container-fluid py-3 rdg">
    <a href="{{ $volver }}" class="small text-muted"><i class="fas fa-arrow-left fa-xs mr-1"></i> Volver</a>
    <h4 class="font-weight-bold mt-2 mb-1"><i class="fas fa-play-circle mr-2" style="color:#4e73df;"></i>Iniciar proceso de titulación</h4>
    <p class="rdg-ayuda mb-3">
        Se registran los datos del PTI, se genera el N° de expediente de cada integrante y queda armado el grupo.
        El asesor se asigna después, en la etapa del TI.
    </p>

    @if (session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('titulacion.inicio.guardar', $persona->id) }}" id="iniForm"
          data-confirm="Se registrará el PTI y se asignarán los números de expediente." data-confirm-titulo="¿Iniciar el proceso?" data-confirm-boton="Sí, iniciar">
        @csrf

        {{-- 1. Datos del PTI --}}
        <div class="rdg-card">
            <div class="rdg-h"><span class="n">1</span> Datos del PTI</div>
            <div class="form-row">
                <div class="form-group col-12">
                    <label for="iniTitulo">Título del Plan de Trabajo de Investigación</label>
                    <textarea name="titulo" id="iniTitulo" rows="2" class="form-control" required maxlength="1000">{{ old('titulo') }}</textarea>
                </div>
                <div class="form-group col-sm-6 col-md-4">
                    <label for="iniFecha">Fecha de inscripción del PTI</label>
                    <input type="date" name="fecha_pti" id="iniFecha" class="form-control" value="{{ old('fecha_pti', now()->toDateString()) }}" required>
                </div>
                <div class="form-group col-sm-6 col-md-4">
                    <label for="iniVence">Fecha de vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="iniVence" class="form-control"
                           value="{{ old('fecha_vencimiento', \Carbon\Carbon::parse(old('fecha_pti', now()->toDateString()))->addYears(2)->toDateString()) }}">
                    <div class="rdg-ayuda"><i class="fas fa-magic fa-xs mr-1"></i>Se calcula sola: 2 años desde la inscripción.</div>
                </div>
            </div>
        </div>

        {{-- 2. Integrantes y expedientes --}}
        <div class="rdg-card">
            <div class="rdg-h"><span class="n">2</span> Integrantes y N° de expediente</div>
            <div class="rdg-elegidos" id="iniElegidos"></div>
            <div id="iniInputs"></div>

            <div class="rdg-agregar">
                <label for="iniBuscar" class="mb-1">
                    Agregar integrante
                    <span class="ini-modalidad ini-modalidad--{{ strtolower($titular['modalidad']) }}">Solo {{ $titular['modalidad'] }}</span>
                    <span class="rdg-ayuda font-weight-normal">— opcional: también puede hacerse de forma individual</span>
                </label>
                <div class="ini-buscador">
                    <input type="search" id="iniBuscar" class="form-control" placeholder="Apellidos, nombre o DNI (mín. 2 letras)" autocomplete="off">
                    <select id="iniGrupo" class="form-control" aria-label="Promoción o ciclo">
                        <option value="">Todas las promociones y ciclos</option>
                        @foreach ($grupos as $nombreGrupo => $totalGrupo)
                            <option value="{{ $nombreGrupo }}">{{ $nombreGrupo }} ({{ $totalGrupo }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="rdg-resultados" id="iniResultados" hidden></div>
            </div>
            <div class="rdg-ayuda mt-2">
                Solo aparecen estudiantes y egresados de <strong>{{ $titular['modalidad'] }}</strong> que pueden iniciar
                @if ($titular['modalidad'] === 'FID') (egresados o desde el Ciclo IX)@endif.
                Elija una promoción o ciclo para ver a todos los de ese grupo.
                Los números de expediente se asignan al guardar, correlativos al último registrado:
                {{ $titular['modalidad'] }} desde <span class="ini-exp">N° {{ $titular['modalidad'] === 'PPD' ? $expedientes['ppd'] : $expedientes['fid'] }}</span>.
                Quien ya tenía un expediente lo conserva.
            </div>
        </div>

        <div class="rdg-acciones">
            <span class="rdg-ayuda"><strong id="iniCuenta">1</strong> integrante(s)</span>
            <a href="{{ $volver }}" class="btn btn-light border ml-auto">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-play mr-1"></i>Iniciar proceso</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var titular = @json($titular);
    var elegidos = [titular].concat(@json($previos));
    var urlBuscar = @json(route('titulacion.inicio.buscar'));
    var siguiente = @json($expedientes);
    var caja = document.getElementById('iniElegidos');
    var cajaInputs = document.getElementById('iniInputs');
    var buscar = document.getElementById('iniBuscar');
    var resultados = document.getElementById('iniResultados');
    var cuenta = document.getElementById('iniCuenta');

    function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; }
    function iniciales(n) { return (n || '?').split(/[ ,]+/).filter(Boolean).slice(0, 2).map(function (p) { return p[0]; }).join('').toUpperCase(); }

    // N° que recibirá cada uno: el suyo si ya tenía; si no, el siguiente de su serie (FID / PPD).
    function expedientes() {
        var usados = { FID: 0, PPD: 0 };
        return elegidos.map(function (p) {
            if (p.expediente) return { texto: p.expediente, nuevo: false };
            var base = p.modalidad === 'PPD' ? siguiente.ppd : siguiente.fid;
            var num = parseInt(base, 10) + usados[p.modalidad]++;
            return { texto: String(num).padStart(4, '0') + base.replace(/^\d+/, ''), nuevo: true };
        });
    }

    function telefonos(p) {
        var t = [];
        if (p.celular) t.push('<span class="ini-tel"><i class="fas fa-mobile-alt"></i><a href="tel:' + esc(p.celular) + '">' + esc(p.celular) + '</a></span>');
        if (p.celular_ref) t.push('<span class="ini-tel" title="Número de referencia"><i class="fas fa-phone"></i>Ref. ' + esc(p.celular_ref) + '</span>');
        return t.length ? t.join('') : '<span class="ini-tel">Sin celular en su ficha</span>';
    }

    function pintar() {
        var exps = expedientes();
        caja.innerHTML = elegidos.map(function (p, i) {
            var exp = exps[i];
            return '<div class="rdg-chip' + (i === 0 ? ' is-titular' : '') + '">'
                + '<span class="rdg-chip-ini">' + esc(iniciales(p.nombre)) + '</span>'
                + '<div class="rdg-chip-txt"><strong>' + esc(p.nombre) + '</strong>'
                + '<small>' + esc(p.modalidad) + ' · ' + esc(p.programa || 'Sin programa') + ' · ' + esc(p.situacion) + (p.dni ? ' · DNI ' + esc(p.dni) : '') + '</small>'
                + '<small>' + telefonos(p) + '</small>'
                + '<span class="ini-exp" title="' + (exp.nuevo ? 'Se asignará al guardar' : 'Ya tenía este expediente') + '">N° ' + esc(exp.texto) + (exp.nuevo ? '' : ' (actual)') + '</span>'
                + '</div>'
                + (i === 0 ? '<span class="rdg-chip-lock" title="Quien inicia el proceso"><i class="fas fa-lock"></i></span>'
                           : '<button type="button" data-quitar="' + p.id + '" title="Quitar" aria-label="Quitar">&times;</button>')
                + '</div>';
        }).join('');
        cajaInputs.innerHTML = elegidos.slice(1).map(function (p) { return '<input type="hidden" name="integrantes[]" value="' + p.id + '">'; }).join('');
        cuenta.textContent = elegidos.length;
    }

    caja.addEventListener('click', function (e) {
        var b = e.target.closest('[data-quitar]');
        if (!b) return;
        var id = parseInt(b.dataset.quitar, 10);
        elegidos = elegidos.filter(function (p) { return p.id !== id; });
        pintar();
    });

    // Vencimiento del PTI: 2 años desde la inscripción, recalculado al cambiar la fecha.
    var fechaPti = document.getElementById('iniFecha');
    var vence = document.getElementById('iniVence');
    fechaPti.addEventListener('change', function () {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(fechaPti.value);
        if (!m) return;
        var d = new Date(Date.UTC(+m[1] + 2, +m[2] - 1, +m[3]));
        if (d.getUTCMonth() !== +m[2] - 1) d.setUTCDate(0); // 29 de febrero → 28
        vence.value = d.toISOString().slice(0, 10);
    });

    // Buscador de integrantes: misma modalidad que el titular, agrupado por promoción o ciclo.
    var grupo = document.getElementById('iniGrupo');
    var temporizador = null;
    function consultar() {
        clearTimeout(temporizador);
        var q = buscar.value.trim();
        if (q.length < 2 && !grupo.value) { resultados.hidden = true; return; }
        temporizador = setTimeout(function () {
            var params = new URLSearchParams({ q: q, grupo: grupo.value, titular: titular.id });
            elegidos.forEach(function (p) { params.append('excluir[]', p.id); });
            fetch(urlBuscar + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (lista) {
                    var actual = null, html = '';
                    lista.forEach(function (p) {
                        if (p.grupo !== actual) {
                            actual = p.grupo;
                            var total = lista.filter(function (x) { return x.grupo === actual; }).length;
                            html += '<div class="rdg-res-grupo">' + esc(actual) + '<span>· ' + total + '</span></div>';
                        }
                        html += '<button type="button" class="rdg-res' + (p.motivo ? ' is-bloqueado' : '') + '" data-persona="' + esc(JSON.stringify(p)) + '"' + (p.motivo ? ' disabled' : '') + '>'
                            + '<strong>' + esc(p.nombre) + '</strong> <small>' + esc(p.programa || '') + (p.dni ? ' · DNI ' + esc(p.dni) : '') + '</small>'
                            + (p.celular ? ' <small>· ' + esc(p.celular) + '</small>' : '')
                            + (p.motivo ? '<span class="rdg-res-falta">No se puede agregar: ' + esc(p.motivo) + '</span>' : '')
                            + '</button>';
                    });
                    resultados.innerHTML = html || '<div class="rdg-res-vacio">Sin coincidencias entre estudiantes y egresados de ' + esc(titular.modalidad) + ' que puedan iniciar.</div>';
                    resultados.hidden = false;
                });
        }, 250);
    }
    buscar.addEventListener('input', consultar);
    grupo.addEventListener('change', consultar);

    resultados.addEventListener('click', function (e) {
        var b = e.target.closest('[data-persona]');
        if (!b || b.disabled) return;
        elegidos.push(JSON.parse(b.dataset.persona));
        buscar.value = '';
        pintar();
        // Con un grupo elegido la lista sigue abierta (sin el recién agregado) para sumar a otros.
        if (grupo.value) consultar(); else { resultados.hidden = true; buscar.focus(); }
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.rdg-agregar')) resultados.hidden = true;
    });

    pintar();
})();
</script>
@endpush
@endsection
