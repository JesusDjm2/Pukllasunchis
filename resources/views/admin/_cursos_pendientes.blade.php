{{-- Cursos pendientes (a cargo) en crear/editar usuario: se eligen de los cursos reales del
     programa del alumno (select de ciclo → select de curso → Agregar), varios a la vez. Se
     guardan como "Nombre - Ciclo X" separados por " | " (User::cursosPendientes los lee).
     Los textos escritos a mano antes se reconocen si coinciden con un curso; si no, quedan
     como "texto anterior" para conservarlos o quitarlos.
     Espera: $actuales (array de textos), $mostrar (bool). Usa los selects #programa_id del form. --}}
@php
    $etiquetaCicloPend = fn ($c) => preg_match('/^[IVX]+$/', trim($c->nombre)) ? 'Ciclo '.trim($c->nombre) : $c->nombre;
    $catalogoPend = \App\Models\Programa::with(['ciclos.cursos' => fn ($q) => $q->orderBy('nombre')])->get()
        ->mapWithKeys(fn ($p) => [$p->id => $p->ciclos
            ->filter(fn ($c) => $c->cursos->isNotEmpty())
            ->sortBy(fn ($c) => $c->ordenCiclo())
            ->values()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $etiquetaCicloPend($c),
                'cursos' => $c->cursos->map(fn ($cu) => ['id' => $cu->id, 'nombre' => trim($cu->nombre)])->values(),
            ])]);
    $actualesPend = old('pendientes', $actuales ?? []);
@endphp
<div class="col-md-9 mb-3" id="cursos-pendientes" style="{{ ($mostrar ?? false) ? '' : 'display:none;' }}">
    <label class="reg-label" for="pendCiclo">Cursos pendientes</label>
    <div class="pend-chips" id="pendChips" aria-live="polite"></div>
    <div class="pend-fila">
        <select id="pendCiclo" class="form-control reg-input-solo" aria-label="Ciclo del curso pendiente"></select>
        <select id="pendCurso" class="form-control reg-input-solo" aria-label="Curso pendiente" disabled>
            <option value="">Elija primero el ciclo</option>
        </select>
        <button type="button" class="btn btn-outline-primary" id="pendAgregar" disabled><i class="fas fa-plus mr-1"></i>Agregar</button>
    </div>
    <small class="text-muted d-block mt-1" id="pendAyuda">Los cursos se toman del programa elegido arriba. Puede agregar varios.</small>
    <div id="pendInputs"></div>
    @error('pendientes')<div class="text-danger small">{{ $message }}</div>@enderror
</div>

<style>
    .pend-fila { display: flex; flex-wrap: wrap; gap: .5rem; }
    .pend-fila select { flex: 1 1 14rem; min-width: 0; }
    .pend-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .5rem; }
    .pend-chips:empty { display: none; }
    .pend-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .3rem .2rem .65rem; border-radius: 999px; background: #fff7e6; border: 1px solid #f6c23e; font-size: .82rem; }
    .pend-chip.is-antiguo { background: #f1f5f9; border-color: #cbd5e1; border-style: dashed; }
    .pend-chip small { color: #92400e; }
    .pend-chip.is-antiguo small { color: #64748b; }
    .pend-chip button { border: 0; background: transparent; width: 20px; height: 20px; border-radius: 50%; line-height: 1; font-size: 1rem; color: inherit; cursor: pointer; }
    .pend-chip button:hover { background: rgba(0, 0, 0, .08); }
    .dark-mode .pend-chip, .dim-mode .pend-chip { background: rgba(246, 194, 62, .1); color: inherit; }
    .dark-mode .pend-chip.is-antiguo, .dim-mode .pend-chip.is-antiguo { background: rgba(255, 255, 255, .05); border-color: rgba(255, 255, 255, .25); }
    .dark-mode .pend-chip small, .dim-mode .pend-chip small { color: #f6c23e; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var CATALOGO = @json($catalogoPend);
    var programaSel = document.getElementById('programa_id');
    var selCiclo = document.getElementById('pendCiclo'), selCurso = document.getElementById('pendCurso');
    var btnAgregar = document.getElementById('pendAgregar');
    var chips = document.getElementById('pendChips'), inputs = document.getElementById('pendInputs');
    var norm = function (t) { return String(t || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/\s+/g, ' ').trim(); };
    var esc = function (t) { return String(t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var etiqueta = function (curso, ciclo) { return curso.nombre + ' - ' + ciclo.nombre; };

    // Elegidos: { texto, antiguo } — antiguo = escrito a mano antes y sin curso equivalente.
    var elegidos = [];
    function reconocer(texto) {
        var n = norm(texto);
        var porNombre = null;
        Object.keys(CATALOGO).forEach(function (pid) {
            CATALOGO[pid].forEach(function (ciclo) {
                ciclo.cursos.forEach(function (curso) {
                    if (norm(etiqueta(curso, ciclo)) === n) porNombre = porNombre || etiqueta(curso, ciclo);
                });
            });
        });
        return porNombre;
    }
    (@json(array_values($actualesPend))).forEach(function (t) {
        var real = reconocer(t);
        elegidos.push(real ? { texto: real, antiguo: false } : { texto: t, antiguo: true });
    });

    function pintar() {
        chips.innerHTML = elegidos.map(function (e, i) {
            var partes = e.antiguo ? [e.texto, 'texto anterior'] : e.texto.split(' - ');
            var ciclo = e.antiguo ? partes[1] : partes.pop();
            var nombre = e.antiguo ? partes[0] : partes.join(' - ');
            return '<span class="pend-chip ' + (e.antiguo ? 'is-antiguo' : '') + '"' + (e.antiguo ? ' title="Escrito a mano antes; no coincide con un curso del sistema"' : '') + '>'
                + esc(nombre) + ' <small>· ' + esc(ciclo) + '</small>'
                + '<button type="button" data-quitar="' + i + '" aria-label="Quitar">&times;</button></span>';
        }).join('');
        inputs.innerHTML = elegidos.map(function (e) { return '<input type="hidden" name="pendientes[]" value="' + esc(e.texto) + '">'; }).join('');
        llenarCursos();
    }
    chips.addEventListener('click', function (ev) {
        var b = ev.target.closest('[data-quitar]');
        if (!b) return;
        elegidos.splice(+b.dataset.quitar, 1);
        pintar();
    });

    function ciclosDelPrograma() { return (programaSel && CATALOGO[programaSel.value]) || []; }
    function llenarCiclos() {
        var ciclos = ciclosDelPrograma();
        selCiclo.innerHTML = '<option value="">' + (programaSel && programaSel.value ? 'Ciclo…' : 'Elija primero el programa') + '</option>'
            + ciclos.map(function (c) { return '<option value="' + c.id + '">' + esc(c.nombre) + '</option>'; }).join('');
        selCiclo.disabled = !ciclos.length;
        llenarCursos();
    }
    function llenarCursos() {
        var ciclo = ciclosDelPrograma().find(function (c) { return String(c.id) === selCiclo.value; });
        if (!ciclo) {
            selCurso.innerHTML = '<option value="">Elija primero el ciclo</option>';
            selCurso.disabled = true; btnAgregar.disabled = true;
            return;
        }
        var ya = elegidos.map(function (e) { return e.texto; });
        selCurso.innerHTML = '<option value="">Curso…</option>' + ciclo.cursos.map(function (cu) {
            var t = etiqueta(cu, ciclo), dentro = ya.indexOf(t) !== -1;
            return '<option value="' + esc(t) + '"' + (dentro ? ' disabled' : '') + '>' + esc(cu.nombre) + (dentro ? ' (ya agregado)' : '') + '</option>';
        }).join('');
        selCurso.disabled = false;
        btnAgregar.disabled = !selCurso.value;
    }
    selCiclo.addEventListener('change', llenarCursos);
    selCurso.addEventListener('change', function () { btnAgregar.disabled = !selCurso.value; });
    btnAgregar.addEventListener('click', function () {
        if (!selCurso.value) return;
        elegidos.push({ texto: selCurso.value, antiguo: false });
        pintar();
        selCurso.focus();
    });
    if (programaSel) programaSel.addEventListener('change', llenarCiclos);

    llenarCiclos();
    pintar();
});
</script>
