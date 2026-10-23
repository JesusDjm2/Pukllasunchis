{{-- Seguimiento académico del ciclo (panel del tutor): cómo van los cursos, quién está
     desaprobando, en qué cursos y con qué nota. Datos: App\Services\SeguimientoCiclo.
     Compacto: acordeones por curso y por alumno (<details class="al-acc">), la tabla completa plegada.
     Solo clases del sistema compartido (public/css/pk-ui.css). Espera $seguimiento, $ciclo y $contactos. --}}
@php
    use Illuminate\Support\Str;

    $r = $seguimiento['resumen'];
    $periodo = $seguimiento['periodo'];
    $cursosCiclo = $seguimiento['cursos']->where('del_ciclo', true)->values();
    $nota = fn ($valor) => $valor === null ? null : rtrim(rtrim(number_format($valor, 1), '0'), '.');
    $estadoNota = fn ($valor) => $valor === null ? '' : ($valor < \App\Services\HistorialAcademico::NOTA_MINIMA ? 'is-mal' : 'is-ok');
    $nombre = fn ($a) => $a->apellidos.', '.(Str::before($a->nombres, ' ') ?: $a->nombres);
    $whatsapp = function ($alumno, $cursos) use ($contactos, $ciclo) {
        $contacto = $contactos[$alumno->id] ?? null;

        return $contacto?->whatsapp('Hola '.$contacto->primerNombre().', soy tu tutor(a) del Ciclo '.$ciclo->nombre.'. Quería conversar contigo sobre cómo vas en: '.$cursos.'.');
    };
@endphp

<div class="al-aviso is-info mb-3">
    <i class="fas fa-info-circle"></i>
    <div class="small">
        <strong>Período {{ $periodo?->nombre ?? 'en curso' }}</strong> · notas que van cargando los docentes; por curso cuenta la más reciente (Parcial 1, Parcial 2 o Final). Desaprobado: menor a 11.
        @if ($periodo)
            Los alumnos ya ven: Parcial 1 <strong>{{ $r['publicado'][1] ? 'sí' : 'aún no' }}</strong>, Parcial 2 <strong>{{ $r['publicado'][2] ? 'sí' : 'aún no' }}</strong>.
        @endif
    </div>
</div>

{{-- Indicadores = filtros: muestran solo la sección que interesa (ver script al final). --}}
<div class="al-stats" role="group" aria-label="Filtrar el seguimiento">
    <a href="#" class="al-stat text-decoration-none is-activa" data-filtro="todo" aria-pressed="true" title="Ver todo">
        <span class="al-stat-icono"><i class="fas fa-user-graduate"></i></span>
        <div><div class="al-stat-valor">{{ $r['con_notas'] }}<small class="text-muted"> / {{ $r['alumnos'] }}</small></div><div class="al-stat-etiqueta">Alumnos con notas · ver todo</div></div>
    </a>
    <a href="#" class="al-stat text-decoration-none" data-filtro="riesgo" aria-pressed="false" title="Ver solo a quienes desaprueban">
        <span class="al-stat-icono {{ $r['en_riesgo'] ? 'is-mal' : 'is-ok' }}"><i class="fas fa-exclamation-triangle"></i></span>
        <div><div class="al-stat-valor">{{ $r['en_riesgo'] }}</div><div class="al-stat-etiqueta">Desaprobando algún curso</div></div>
    </a>
    <a href="#" class="al-stat text-decoration-none" data-filtro="cursos" aria-pressed="false" title="Ver solo los cursos con desaprobados">
        <span class="al-stat-icono {{ $r['desaprobados'] ? 'is-mal' : 'is-ok' }}"><i class="fas fa-times-circle"></i></span>
        <div><div class="al-stat-valor">{{ $r['desaprobados'] }}</div><div class="al-stat-etiqueta">Notas desaprobatorias</div></div>
    </a>
    <a href="#" class="al-stat text-decoration-none" data-filtro="tabla" aria-pressed="false" title="Ver todas las notas en tabla">
        <span class="al-stat-icono is-oro"><i class="fas fa-chart-line"></i></span>
        <div><div class="al-stat-valor">{{ $nota($r['promedio']) ?? '—' }}</div><div class="al-stat-etiqueta">Promedio del ciclo · tabla</div></div>
    </a>
</div>
<div class="al-aviso is-warn mb-3" data-filtro-estado hidden>
    <i class="fas fa-filter"></i>
    <div class="small">Mostrando: <strong data-filtro-texto></strong>. <a href="#" data-filtro="todo">Ver todo</a></div>
</div>


{{-- ── Por curso ── --}}
<div class="al-card" data-seccion="cursos">
    <div class="al-card-cab">
        <span class="al-card-cab-icono"><i class="fas fa-book"></i></span>
        <div>
            <h2>Por curso</h2>
            <p>Primero los que tienen más desaprobados. Despliega un curso para ver quiénes y con qué nota.</p>
        </div>
    </div>
    @forelse ($seguimiento['cursos'] as $c)
        @php
            $notasCurso = $seguimiento['alumnos']->map(fn ($f) => [$f, $f->notas->get($c->curso->id)])->filter(fn ($x) => $x[1]);
            $malos = $notasCurso->filter(fn ($x) => $x[1]->desaprobado)->sortBy(fn ($x) => $x[1]->nota);
            $buenos = $notasCurso->reject(fn ($x) => $x[1]->desaprobado)->sortBy(fn ($x) => $x[0]->alumno->apellidos);
            $docentes = $c->curso->docentes->map(fn ($d) => $d->nombre)->filter()->implode(', ');
        @endphp
        <details class="al-acc" data-desaprobados="{{ $c->desaprobados }}" @if ($loop->first) open @endif>
            <summary class="al-curso">
                <span class="al-curso-num {{ $c->desaprobados ? 'is-mal' : ($c->con_nota ? 'is-ok' : '') }}" title="Desaprobados">{{ $c->desaprobados ?: ($c->con_nota ? '✓' : '–') }}</span>
                <span class="al-curso-texto">
                    <span class="al-curso-nombre">{{ $c->curso->nombre }}</span>
                    @if (! $c->del_ciclo) <span class="al-badge is-muted">otro ciclo</span> @endif
                    <span class="al-curso-meta d-block">
                        @if ($docentes) <i class="fas fa-chalkboard-teacher"></i>{{ $docentes }} · @endif
                        {{ $c->con_nota }}/{{ $r['alumnos'] }} con nota
                    </span>
                    @if ($malos->isNotEmpty())
                        {{-- Las notas desaprobatorias a la vista, sin desplegar --}}
                        {{-- Agrupadas por valor ([1] × 28): sin repetir lo que lista el desplegable. --}}
                        <span class="al-notas-mal" title="Notas desaprobatorias">
                            @foreach ($malos->groupBy(fn ($x) => $nota($x[1]->nota))->sortKeys() as $valor => $grupo)
                                <span class="al-nota-curso"><span class="cal-nota is-mal is-sm">{{ $valor }}</span>× {{ $grupo->count() }}</span>
                            @endforeach
                        </span>
                    @endif
                </span>
                <span class="al-curso-accion">
                    @if ($c->desaprobados) <span class="al-badge is-mal d-none d-sm-inline-flex">{{ $c->porcentaje }}%</span> @endif
                    <span class="cal-nota {{ $estadoNota($c->promedio) }}" title="Promedio del curso">{{ $nota($c->promedio) ?? '—' }}</span>
                    <i class="fas fa-chevron-down al-acc-chev"></i>
                </span>
            </summary>
            <div class="al-acc-cuerpo">
                @if ($notasCurso->isEmpty())
                    <p class="small text-muted mb-0 mt-2">Todavía no hay notas en este curso.</p>
                @else
                    @if ($malos->isNotEmpty())
                        <div class="al-acc-sub">Desaprobando ({{ $malos->count() }})</div>
                        @foreach ($malos as [$f, $n])
                            @php $wa = $whatsapp($f->alumno, $c->curso->nombre); @endphp
                            <div class="al-acc-fila">
                                <span class="cal-nota is-mal">{{ $nota($n->nota) }}</span>
                                <span>{{ $nombre($f->alumno) }} <small>· {{ $n->de }}</small></span>
                                @if ($wa)
                                    <span class="al-curso-accion"><a href="{{ $wa }}" target="_blank" rel="noopener" class="al-btn al-btn-sm al-btn-borde" title="WhatsApp"><i class="fab fa-whatsapp"></i></a></span>
                                @endif
                            </div>
                        @endforeach
                    @endif
                    @if ($buenos->isNotEmpty())
                        <div class="al-acc-sub">Aprobando ({{ $buenos->count() }})</div>
                        <div class="al-acc-chips">
                            @foreach ($buenos as [$f, $n])
                                <span title="{{ $n->de }}"><span class="cal-nota is-ok">{{ $nota($n->nota) }}</span>{{ $f->alumno->apellidos }}</span>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </details>
    @empty
        <div class="al-vacio"><i class="fas fa-book-open"></i>Este ciclo todavía no tiene cursos con notas.</div>
    @endforelse
</div>

{{-- ── Por alumno (solo quienes están desaprobando) ── --}}
<div class="al-card" data-seccion="riesgo">
    <div class="al-card-cab">
        <span class="al-card-cab-icono"><i class="fas fa-user-times"></i></span>
        <div>
            <h2>Alumnos que están desaprobando</h2>
            <p>Primero quienes desaprueban más cursos. Despliega para ver todas sus notas.</p>
        </div>
        @if ($r['en_riesgo']) <span class="al-card-cab-extra"><span class="al-badge is-mal">{{ $r['en_riesgo'] }}</span></span> @endif
    </div>
    @forelse ($seguimiento['en_riesgo'] as $f)
        @php $wa = $whatsapp($f->alumno, $f->desaprobados->map(fn ($c) => $c->curso->nombre)->implode(', ')); @endphp
        <details class="al-acc" @if ($loop->first) open @endif>
            <summary class="al-curso">
                <span class="al-curso-num is-mal" title="Cursos que desaprueba">{{ $f->desaprobados->count() }}</span>
                <span class="al-curso-texto">
                    <span class="al-curso-nombre">{{ $f->alumno->apellidos }}, {{ $f->alumno->nombres }}</span>
                    {{-- Cada curso desaprobado con su nota, a la vista sin desplegar --}}
                    <span class="al-notas-mal">
                        @foreach ($f->desaprobados as $d)
                            <span class="al-nota-curso" title="{{ $d->curso->nombre }} · {{ $d->de }}"><span class="cal-nota is-mal is-sm">{{ $nota($d->nota) }}</span>{{ Str::limit($d->curso->nombre, 30) }}</span>
                        @endforeach
                    </span>
                    @if (! empty($f->pendientes))
                        <span class="al-curso-meta d-block"><span class="is-falta" title="{{ implode(' · ', $f->pendientes) }}"><i class="fas fa-history"></i>{{ count($f->pendientes) }} curso(s) pendiente(s) de ciclos anteriores</span></span>
                    @endif
                </span>
                <span class="al-curso-accion">
                    <span class="cal-nota {{ $estadoNota($f->promedio) }}" title="Promedio">{{ $nota($f->promedio) ?? '—' }}</span>
                    @if ($wa) <a href="{{ $wa }}" target="_blank" rel="noopener" class="al-btn al-btn-sm al-btn-borde" title="WhatsApp"><i class="fab fa-whatsapp"></i></a> @endif
                    <i class="fas fa-chevron-down al-acc-chev"></i>
                </span>
            </summary>
            <div class="al-acc-cuerpo">
                @foreach ($f->notas->sortBy('nota') as $n)
                    <div class="al-acc-fila">
                        <span class="cal-nota {{ $n->desaprobado ? 'is-mal' : 'is-ok' }}">{{ $nota($n->nota) }}</span>
                        <span>{{ $n->curso->nombre }} <small>· {{ $n->de }}</small></span>
                    </div>
                @endforeach
                @if (! empty($f->pendientes))
                    <div class="al-acc-sub">Pendientes de ciclos anteriores</div>
                    <div class="al-acc-chips">@foreach ($f->pendientes as $p)<span><i class="fas fa-history text-muted"></i>{{ $p }}</span>@endforeach</div>
                @endif
            </div>
        </details>
    @empty
        <div class="al-vacio"><i class="fas fa-check-circle"></i>Por ahora nadie está desaprobando un curso.</div>
    @endforelse
</div>

{{-- ── Tabla completa, plegada ── --}}
@if ($cursosCiclo->isNotEmpty() && $r['con_notas'])
    <details class="al-card al-acc" data-seccion="tabla">
        <summary class="al-card-cab">
            <span class="al-card-cab-icono"><i class="fas fa-th"></i></span>
            <div>
                <h2>Todas las notas en tabla</h2>
                <p>Alumnos × cursos del ciclo. Pasa el cursor sobre una nota para ver de qué parcial es.</p>
            </div>
            <span class="al-card-cab-extra"><i class="fas fa-chevron-down al-acc-chev"></i></span>
        </summary>
        <div class="table-responsive">
            <table class="cal-tabla">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        @foreach ($cursosCiclo as $c)
                            <th title="{{ $c->curso->nombre }}">{{ Str::limit($c->curso->nombre, 14) }}</th>
                        @endforeach
                        <th>Prom.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($seguimiento['alumnos'] as $f)
                        <tr>
                            <td class="text-nowrap">{{ $nombre($f->alumno) }}</td>
                            @foreach ($cursosCiclo as $c)
                                @php $n = $f->notas->get($c->curso->id); @endphp
                                <td>
                                    @if ($n)
                                        <span class="cal-nota {{ $n->desaprobado ? 'is-mal' : 'is-ok' }}" title="{{ $c->curso->nombre }} · {{ $n->de }}">{{ $nota($n->nota) }}</span>
                                    @else
                                        <small>—</small>
                                    @endif
                                </td>
                            @endforeach
                            <td><span class="cal-nota {{ $estadoNota($f->promedio) }}">{{ $nota($f->promedio) ?? '—' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
@endif

@once
    <script>
        // Indicadores = filtros del seguimiento. «todo» muestra todo; los demás dejan una sola sección
        // («cursos» además oculta los cursos sin desaprobados y despliega los que tienen). Un segundo
        // clic en el filtro activo vuelve a «todo».
        document.addEventListener('click', function (e) {
            var boton = e.target.closest('[data-filtro]');
            if (!boton) return;
            e.preventDefault();
            var cont = boton.closest('[data-panel="seguimiento"]') || document;
            var filtro = boton.dataset.filtro;
            var activo = cont.querySelector('.al-stat.is-activa');
            if (activo && activo === boton && filtro !== 'todo') filtro = 'todo';

            cont.querySelectorAll('.al-stat[data-filtro]').forEach(function (s) {
                var on = s.dataset.filtro === filtro;
                s.classList.toggle('is-activa', on);
                s.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            cont.querySelectorAll('[data-seccion]').forEach(function (s) {
                s.hidden = !(filtro === 'todo' || s.dataset.seccion === filtro);
            });
            cont.querySelectorAll('[data-seccion="cursos"] details[data-desaprobados]').forEach(function (d) {
                var conDesaprobados = Number(d.dataset.desaprobados) > 0;
                d.hidden = filtro === 'cursos' && !conDesaprobados;
                if (filtro === 'cursos') d.open = conDesaprobados;
            });
            if (filtro === 'tabla') {
                var tabla = cont.querySelector('[data-seccion="tabla"]');
                if (tabla) tabla.open = true;
            }

            var textos = { riesgo: 'alumnos que desaprueban algún curso', cursos: 'cursos con desaprobados', tabla: 'todas las notas en tabla' };
            var estado = cont.querySelector('[data-filtro-estado]');
            if (estado) {
                estado.hidden = filtro === 'todo';
                estado.querySelector('[data-filtro-texto]').textContent = textos[filtro] || '';
            }
            if (filtro !== 'todo' && estado) estado.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    </script>
@endonce
