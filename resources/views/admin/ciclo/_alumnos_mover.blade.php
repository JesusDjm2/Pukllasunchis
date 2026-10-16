{{--
    Alumnos del ciclo + cambio de ciclo masivo. Lo usan la rama regular y la de "Egresados" de
    admin/ciclo/show. FID ($alumnos: fichas Alumno con ->user) y PPD ($alumnosB: User) se
    normalizan a UNA forma de fila, así las columnas siempre coinciden con el encabezado.
--}}
@php
    $filas = collect();
    foreach ($alumnos as $a) {
        if (! $a->user) {
            continue;
        }
        $filas->push((object) [
            'id' => $a->user->id,
            'tipo' => 'FID',
            'nombre' => trim($a->apellidos . ', ' . $a->nombres, ', '),
            'dni' => $a->dni,
            'telefono' => $a->numero,
            'email' => $a->email ?: $a->user->email,
            'perfil' => $a->user->perfil,
            'inhabilitado' => $a->user->hasRole('inhabilitado'),
        ]);
    }
    foreach ($alumnosB as $u) {
        $filas->push((object) [
            'id' => $u->id,
            'tipo' => 'PPD',
            'nombre' => trim($u->apellidos . ', ' . $u->name, ', '),
            'dni' => $u->dni,
            'telefono' => $u->telefono ?: $u->alumnoB?->numero,
            'email' => $u->email,
            'perfil' => $u->perfil,
            'inhabilitado' => $u->hasRole('inhabilitado'),
        ]);
    }
    $filas = $filas->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values();
    // Mismas reglas que CicloController::updateCicloAlumnos: con licencia o inhabilitado no se mueve.
    foreach ($filas as $f) {
        $f->bloqueo = $f->perfil === 'Licencia' ? 'Con licencia: no se cambia de ciclo'
            : ($f->inhabilitado ? 'Inhabilitado' . ($f->perfil ? ' (' . $f->perfil . ')' : '') . ': no se cambia de ciclo' : null);
    }
    $movibles = $filas->whereNull('bloqueo')->count();
    $destinos = $ciclosDisponibles->where('id', '!=', $ciclo->id)
        ->sortBy(fn ($c) => [$c->ordenCiclo(), $c->nombre]);
@endphp

<style>
    .mv-bar { display:flex; flex-wrap:wrap; align-items:flex-end; gap:.75rem 1rem; margin-bottom:.9rem; }
    .mv-bar .pg-select { min-width:220px; }
    .mv-search { position:relative; flex:1 1 220px; max-width:320px; }
    .mv-search i { position:absolute; left:.65rem; top:50%; transform:translateY(-50%); color:var(--pg-muted); font-size:.75rem; }
    .mv-search input { width:100%; height:34px; padding:0 .7rem 0 1.9rem; font-size:.82rem; border:1px solid var(--pg-border); border-radius:.5rem; background:var(--pg-card); color:var(--pg-text); }
    .mv-count { font-size:.8rem; color:var(--pg-muted); white-space:nowrap; }
    .mv-count strong { color:var(--pg-text); }
    .mv-table th, .mv-table td { vertical-align:middle; }
    .mv-table th.mv-sel, .mv-table td.mv-sel { width:42px; text-align:center; }
    .mv-table th.mv-num, .mv-table td.mv-num { width:44px; color:var(--pg-muted); }
    .mv-table input[type=checkbox] { width:16px; height:16px; cursor:pointer; accent-color:#4f46e5; }
    .mv-table input[type=checkbox]:disabled { cursor:not-allowed; }
    .mv-table tr.is-sel td { background:rgba(79,70,229,.06); }
    .mv-table tr[hidden] { display:none; }
    .mv-nombre { font-weight:600; color:var(--pg-text); }
    .mv-sub { font-size:.74rem; color:var(--pg-muted); line-height:1.5; }
    .mv-tag { display:inline-block; font-size:.64rem; font-weight:700; padding:.05rem .45rem; border-radius:999px; margin-left:.3rem; vertical-align:middle; }
    .mv-tag.fid { background:rgba(37,99,235,.1); color:#1d4ed8; }
    .mv-tag.ppd { background:rgba(217,119,6,.12); color:#b45309; }
    .mv-tag.warn { background:rgba(220,38,38,.1); color:#b91c1c; }
    .dark-mode .mv-tag.fid, .dim-mode .mv-tag.fid { color:#93c5fd; }
    .dark-mode .mv-tag.ppd, .dim-mode .mv-tag.ppd { color:#fcd34d; }
    .dark-mode .mv-tag.warn, .dim-mode .mv-tag.warn { color:#fca5a5; }
</style>

<div class="pg-section-lbl" style="opacity:1;">
    <i class="fas fa-users"></i> {{ $titulo ?? 'Alumnos del ciclo' }}
</div>
<div class="pg-move-card mb-4">
    @if ($filas->isEmpty())
        <div class="pg-empty" style="padding:1.5rem;">
            <i class="fas fa-user-slash mb-2" style="opacity:.3;font-size:1.5rem;"></i>
            <p class="mb-0">No hay alumnos en este ciclo.</p>
        </div>
    @else
        <form action="{{ route('ciclo.updateAlumnos') }}" method="POST" id="mvForm">
            @csrf @method('PUT')
            <input type="hidden" name="ciclo_id" value="{{ $ciclo->id }}">

            <div class="mv-bar">
                <div>
                    <label class="pg-label" for="mvDestino">Mover a</label>
                    <select name="nuevo_ciclo_id" id="mvDestino" class="pg-select" required>
                        <option value="" disabled selected>Elige el ciclo destino…</option>
                        @foreach ($destinos as $d)
                            <option value="{{ $d->id }}">{{ preg_match('/^[IVX]+$/', trim($d->nombre)) ? 'Ciclo ' . trim($d->nombre) : $d->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mv-search">
                    <i class="fas fa-search"></i>
                    <input type="search" id="mvBuscar" placeholder="Buscar por nombre o DNI…" autocomplete="off">
                </div>
                <span class="mv-count ml-auto"><strong id="mvSel">0</strong> de {{ $movibles }} seleccionados</span>
                <button type="submit" class="pg-submit" id="mvEnviar" disabled>
                    <i class="fas fa-exchange-alt mr-1"></i> Cambiar de ciclo
                </button>
            </div>

            <div class="table-responsive" style="border:1px solid var(--pg-border);border-radius:.6rem;">
                <table class="pg-table mv-table mb-0">
                    <thead>
                        <tr>
                            <th class="mv-sel"><input type="checkbox" id="mvTodos" title="Seleccionar todos los visibles" aria-label="Seleccionar todos"></th>
                            <th class="mv-num">#</th>
                            <th>Estudiante</th>
                            <th>DNI</th>
                            <th>Contacto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filas as $f)
                            <tr class="{{ $f->bloqueo ? 'pg-table-warn' : '' }}" data-buscar="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($f->nombre . ' ' . $f->dni)) }}">
                                <td class="mv-sel">
                                    <input type="checkbox" name="alumnos[]" value="{{ $f->id }}" class="mv-check"
                                        aria-label="Seleccionar a {{ $f->nombre }}"
                                        @if ($f->bloqueo) disabled title="{{ $f->bloqueo }}" @endif>
                                </td>
                                <td class="mv-num">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="mv-nombre">{{ $f->nombre }}</span>
                                    <span class="mv-tag {{ strtolower($f->tipo) }}">{{ $f->tipo }}</span>
                                    @if ($f->bloqueo)
                                        <span class="mv-tag warn" title="{{ $f->bloqueo }}"><i class="fas fa-lock fa-xs"></i> {{ $f->perfil === 'Licencia' ? 'Licencia' : ($f->perfil ?: 'Inhabilitado') }}</span>
                                    @endif
                                </td>
                                <td>{{ $f->dni ?: '—' }}</td>
                                <td class="mv-sub">
                                    @if ($f->telefono)<div><i class="fas fa-phone fa-xs mr-1"></i>{{ $f->telefono }}</div>@endif
                                    @if ($f->email)<div><i class="fas fa-envelope fa-xs mr-1"></i>{{ $f->email }}</div>@endif
                                    @if (! $f->telefono && ! $f->email)—@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($filas->whereNotNull('bloqueo')->isNotEmpty())
                <p class="mv-sub mt-2 mb-0"><i class="fas fa-lock fa-xs mr-1"></i> Los alumnos con licencia o inhabilitados no se pueden cambiar de ciclo.</p>
            @endif
        </form>

        <script>
        (function () {
            var form = document.getElementById('mvForm');
            var todos = document.getElementById('mvTodos');
            var destino = document.getElementById('mvDestino');
            var enviar = document.getElementById('mvEnviar');
            var cuenta = document.getElementById('mvSel');
            var checks = Array.prototype.slice.call(form.querySelectorAll('.mv-check'));

            function visibles() { return checks.filter(function (c) { return !c.disabled && !c.closest('tr').hidden; }); }
            function actualizar() {
                var marcados = checks.filter(function (c) { return c.checked; }).length;
                var vis = visibles(), visMarcados = vis.filter(function (c) { return c.checked; }).length;
                cuenta.textContent = marcados;
                todos.checked = vis.length > 0 && visMarcados === vis.length;
                todos.indeterminate = visMarcados > 0 && visMarcados < vis.length;
                enviar.disabled = !marcados || !destino.value;
                checks.forEach(function (c) { c.closest('tr').classList.toggle('is-sel', c.checked); });
            }
            todos.addEventListener('change', function () {
                visibles().forEach(function (c) { c.checked = todos.checked; });
                actualizar();
            });
            checks.forEach(function (c) { c.addEventListener('change', actualizar); });
            destino.addEventListener('change', actualizar);
            document.getElementById('mvBuscar').addEventListener('input', function () {
                var t = this.value.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                checks.forEach(function (c) { var tr = c.closest('tr'); tr.hidden = t && tr.dataset.buscar.indexOf(t) === -1; });
                actualizar();
            });
            form.addEventListener('submit', function (e) {
                var n = checks.filter(function (c) { return c.checked; }).length;
                var nombre = destino.options[destino.selectedIndex].text;
                var titulo = '¿Mover ' + n + ' alumno(s) a ' + nombre + '?';
                var texto = 'El cambio queda registrado en la trayectoria de cada uno.';
                if (!window.PkAlert) {
                    if (!confirm(titulo + '\n' + texto)) e.preventDefault();
                    return;
                }
                e.preventDefault();
                PkAlert.confirmar({ icon: 'question', title: titulo, text: texto, confirmButtonText: 'Sí, mover' })
                    .then(function (ok) { if (ok) form.submit(); });
            });
            actualizar();
        })();
        </script>
    @endif
</div>
