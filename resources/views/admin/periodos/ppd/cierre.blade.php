@extends($layout)
@section('contenido')
@php
    $egresan = $candidatos->where('egresa', true);
    $noEgresan = $candidatos->where('egresa', false);
@endphp
<style>
    .ci { --ci-card:#fff; --ci-border:#e5e7eb; --ci-text:#1e293b; --ci-muted:#64748b; }
    .dark-mode .ci, .dim-mode .ci { --ci-card:rgba(255,255,255,.035); --ci-border:rgba(255,255,255,.1); --ci-text:#e2e8f0; --ci-muted:#94a3b8; }
    .ci-card { background:var(--ci-card); border:1px solid var(--ci-border); border-radius:14px; box-shadow:0 1px 2px rgba(15,23,42,.05),0 4px 14px rgba(15,23,42,.04); margin-bottom:1rem; }
    .ci-head { display:flex; flex-wrap:wrap; align-items:center; gap:.6rem; padding:.85rem 1.1rem; border-bottom:1px solid var(--ci-border); }
    .ci-title { font-size:.95rem; font-weight:800; color:var(--ci-text); }
    .ci-sub { font-size:.78rem; color:var(--ci-muted); }
    .ci-row { display:flex; align-items:center; gap:.75rem; padding:.55rem 1.1rem; border-bottom:1px solid var(--ci-border); margin:0; cursor:pointer; color:var(--ci-text); }
    .ci-row:last-child { border-bottom:0; }
    .ci-row input { width:17px; height:17px; accent-color:#7c3aed; flex-shrink:0; }
    .ci-name { font-size:.86rem; font-weight:700; }
    .ci-meta { font-size:.72rem; color:var(--ci-muted); }
    .ci-chips { margin-left:auto; display:flex; flex-wrap:wrap; gap:.3rem; justify-content:flex-end; }
    .ci-chip { font-size:.68rem; font-weight:700; padding:.12rem .5rem; border-radius:999px; background:rgba(220,38,38,.1); color:#b91c1c; white-space:nowrap; }
    .ci-chip.is-ok { background:rgba(22,163,74,.1); color:#15803d; }
    .dark-mode .ci-chip, .dim-mode .ci-chip { color:#fca5a5; } .dark-mode .ci-chip.is-ok, .dim-mode .ci-chip.is-ok { color:#86efac; }
    .ci-bar { position:sticky; bottom:0; z-index:5; display:flex; flex-wrap:wrap; align-items:center; gap:.75rem; padding:.8rem 1.1rem; background:var(--ci-card); border:1px solid var(--ci-border); border-radius:14px; box-shadow:0 -6px 20px rgba(15,23,42,.08); }
</style>

<div class="container-fluid py-3 ci">
    <a href="{{ route('periodoactual.index') }}" class="small text-muted"><i class="fas fa-arrow-left fa-xs mr-1"></i> Volver a periodos</a>
    <h4 class="font-weight-bold mt-2 mb-1" style="color:var(--ci-text);">
        <i class="fas fa-user-graduate mr-2" style="color:#7c3aed;"></i>Cierre de {{ $periodo->nombre }}
    </h4>
    <p class="ci-sub mb-3">
        PPD dura un año: al cerrarlo, la promoción {{ $anio }} pasa a <strong>egresada</strong> (condición previa al título) y aparece en Titulación.
        Revisa la lista: nada cambia hasta que confirmes. Cada egreso queda registrado en la trayectoria del estudiante.
    </p>

    @if (! $anio)
        <div class="alert alert-warning">El nombre del periodo no tiene un año (p. ej. «PPD 2026»): no se puede identificar su promoción.</div>
    @elseif ($candidatos->isEmpty())
        <div class="alert alert-info">No hay estudiantes de la promoción {{ $anio }} pendientes de egresar.</div>
    @else
        @unless ($notasArchivadas)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Las notas de {{ $periodo->nombre }} aún <strong>no están archivadas</strong>, así que no se pueden detectar cursos desaprobados.
                Archívalas primero («Crear calificaciones» del periodo) o revisa esos casos a mano.
            </div>
        @endunless

        <form method="POST" action="{{ route('periodos.admin.ppd.cerrar', $periodo) }}" id="formCierre">
            @csrf
            <div class="ci-card">
                <div class="ci-head">
                    <span class="ci-title"><i class="fas fa-check-circle mr-1" style="color:#16a34a;"></i> Egresan ({{ $egresan->count() }})</span>
                    <span class="ci-sub">Matriculados en el 2.º semestre, sin desaprobados ni licencias.</span>
                </div>
                @forelse ($egresan as $c)
                    <label class="ci-row">
                        <input type="checkbox" name="user_ids[]" value="{{ $c->u->id }}" checked class="js-sel">
                        <span>
                            <span class="ci-name d-block">{{ $c->u->apellidos }}, {{ $c->u->name }}</span>
                            <span class="ci-meta">{{ $c->u->programa->nombre ?? '' }} · {{ $c->u->ciclo->nombre ?? 'sin ciclo' }} · DNI {{ $c->u->dni }}</span>
                        </span>
                        <span class="ci-chips"><span class="ci-chip is-ok">Completó el programa</span></span>
                    </label>
                @empty
                    <div class="ci-row ci-sub" style="cursor:default;">Nadie cumple todavía las condiciones.</div>
                @endforelse
            </div>

            @if ($noEgresan->isNotEmpty())
                <div class="ci-card">
                    <div class="ci-head">
                        <span class="ci-title"><i class="fas fa-exclamation-circle mr-1" style="color:#dc2626;"></i> Requieren revisión ({{ $noEgresan->count() }})</span>
                        <span class="ci-sub">No se marcan por defecto. Selecciónalos solo si corresponde que egresen.</span>
                    </div>
                    @foreach ($noEgresan as $c)
                        <label class="ci-row">
                            <input type="checkbox" name="user_ids[]" value="{{ $c->u->id }}" class="js-sel">
                            <span>
                                <span class="ci-name d-block">{{ $c->u->apellidos }}, {{ $c->u->name }}</span>
                                <span class="ci-meta">{{ $c->u->programa->nombre ?? '' }} · {{ $c->u->ciclo->nombre ?? 'sin ciclo' }} · DNI {{ $c->u->dni }}</span>
                            </span>
                            <span class="ci-chips">
                                @foreach ($c->motivos as $m)<span class="ci-chip">{{ $m }}</span>@endforeach
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif

            <div class="ci-bar">
                <span class="ci-sub"><strong id="ciCuenta">{{ $egresan->count() }}</strong> estudiante(s) seleccionados para egresar</span>
                <a href="{{ route('periodoactual.index') }}" class="btn btn-sm btn-light ml-auto">Cancelar</a>
                <button type="submit" class="btn btn-sm font-weight-bold" style="background:#7c3aed;color:#fff;">
                    <i class="fas fa-user-graduate mr-1"></i> Marcar como egresados
                </button>
            </div>
        </form>

        <script>
        (function () {
            var cuenta = document.getElementById('ciCuenta');
            function contar() { cuenta.textContent = document.querySelectorAll('.js-sel:checked').length; }
            document.querySelectorAll('.js-sel').forEach(function (c) { c.addEventListener('change', contar); });
            var form = document.getElementById('formCierre');
            form.addEventListener('submit', function (e) {
                var n = document.querySelectorAll('.js-sel:checked').length;
                var titulo = '¿Marcar a ' + n + ' estudiante(s) como egresados de ' + @json($periodo->nombre) + '?';
                e.preventDefault();
                if (!n) {
                    if (window.PkAlert) PkAlert.aviso('Selecciona al menos un estudiante.', 'Nada seleccionado');
                    return;
                }
                if (!window.PkAlert) {
                    if (confirm(titulo)) form.submit();
                    return;
                }
                PkAlert.confirmar({ icon: 'question', title: titulo, text: 'Cada egreso queda registrado en la trayectoria del estudiante.', confirmButtonText: 'Sí, marcar egresados' })
                    .then(function (ok) { if (ok) form.submit(); });
            });
        })();
        </script>
    @endif
</div>
@endsection
