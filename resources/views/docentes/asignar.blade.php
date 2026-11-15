@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Asignar cursos · ' . $docente->nombre)

@section('contenido')
    @include('alumnos.partials.ui')
    @php
        $asignados = $docente->cursos->pluck('id')->flip();
        $iniciales = collect(preg_split('/\s+/', trim($docente->nombre)))->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
        // Los ciclos PPD ya se llaman «Ciclo I»; los FID solo «I»
        $nomCiclo = fn ($c) => $c ? (preg_match('/^ciclo/i', $c->nombre) ? $c->nombre : 'Ciclo ' . $c->nombre) : 'Sin ciclo';
        $n = 0;
        // Cursos asignados agrupados: «Programa · Ciclo»
        $porGrupo = $docente->cursos
            ->sortBy(fn ($c) => sprintf('%05d-%05d-%s', $c->ciclo?->programa_id ?? 0, $c->ciclo_id ?? 0, $c->nombre))
            ->groupBy(fn ($c) => ($c->ciclo?->programa?->nombre ?? 'Sin programa') . ' · ' . $nomCiclo($c->ciclo));
        $programasConCursos = $programas->filter(fn ($p) => $p->ciclos->contains(fn ($c) => $c->cursos->isNotEmpty()));
        $nProgramas = $docente->cursos->pluck('ciclo.programa_id')->filter()->unique()->count();
    @endphp

    <div class="container-fluid py-3 al-ui">
        <header class="al-hero">
            <div class="al-hero-fila">
                @if ($docente->foto)
                    <img src="{{ asset('docentes/fotos/' . $docente->foto) }}" alt="Foto de {{ $docente->nombre }}" class="al-hero-foto">
                @else
                    <div class="al-hero-foto al-hero-foto-vacia" aria-hidden="true">{{ $iniciales }}</div>
                @endif
                <div class="al-hero-texto">
                    <div class="al-kicker">Asignación de cursos</div>
                    <h1>{{ $docente->nombre }}</h1>
                    <div class="al-chips">
                        @if ($docente->email)
                            <span class="al-chip"><i class="fas fa-envelope"></i>{{ $docente->email }}</span>
                        @endif
                        <span class="al-chip {{ $docente->cursos->isEmpty() ? 'is-warn' : 'is-ok' }}">
                            <i class="fas fa-book"></i>{{ $docente->cursos->count() }} {{ $docente->cursos->count() === 1 ? 'curso asignado' : 'cursos asignados' }}
                        </span>
                        @if ($nProgramas)
                            <span class="al-chip"><i class="fas fa-graduation-cap"></i>{{ $nProgramas }} {{ $nProgramas === 1 ? 'programa' : 'programas' }}</span>
                        @endif
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('docente.index') }}" class="al-btn al-btn-claro">
                        <i class="fas fa-arrow-left"></i> Volver a docentes
                    </a>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="al-aviso is-ok" role="status"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
        @endif
        @if (session('error'))
            <div class="al-aviso is-warn" role="alert"><i class="fas fa-exclamation-triangle"></i><div>{{ session('error') }}</div></div>
        @endif
        @if ($errors->any())
            <div class="al-aviso is-mal" role="alert"><i class="fas fa-times-circle"></i><div>{{ $errors->first() }}</div></div>
        @endif

        <div class="row">
            {{-- ── Agregar cursos ── --}}
            <div class="col-xl-7 mb-4 order-xl-2">
                <form class="al-card mb-0 da-agregar" action="{{ route('cursos.asignar', $docente->id) }}" method="POST" id="daForm">
                    @csrf
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-plus"></i></span>
                        <div>
                            <h2>Agregar cursos</h2>
                            <p>Elige el programa y el ciclo, o busca por nombre. Puedes marcar varios a la vez.</p>
                        </div>
                    </div>

                    <div class="al-filtros">
                        <select class="al-campo" id="daPrograma" aria-label="Programa">
                            <option value="">Todos los programas</option>
                            @foreach ($programasConCursos as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        <select class="al-campo" id="daCiclo" aria-label="Ciclo" disabled>
                            <option value="">Todos los ciclos</option>
                        </select>
                        <input type="search" class="al-campo" id="daBuscar" placeholder="Buscar curso…" aria-label="Buscar curso">
                    </div>

                    <div class="da-lista" id="daLista">
                        @foreach ($programasConCursos as $p)
                            @foreach ($p->ciclos->filter(fn ($c) => $c->cursos->isNotEmpty()) as $ciclo)
                                <div class="da-bloque" data-programa="{{ $p->id }}" data-ciclo="{{ $ciclo->id }}" data-ciclo-nombre="{{ $nomCiclo($ciclo) }}">
                                    <div class="al-grupo">
                                        <span>{{ $p->nombre }} · {{ $nomCiclo($ciclo) }}</span>
                                        <span class="al-badge is-muted">{{ $ciclo->cursos->count() }}</span>
                                    </div>
                                    <ul class="al-cursos">
                                        @foreach ($ciclo->cursos as $curso)
                                            @php
                                                $yaTiene = $asignados->has($curso->id);
                                                $otros = $curso->docentes->where('id', '!=', $docente->id)->pluck('nombre');
                                            @endphp
                                            <li>
                                                <label class="al-curso da-opcion {{ $yaTiene ? 'is-tiene' : '' }}" data-texto="{{ mb_strtolower($curso->nombre . ' ' . $otros->implode(' ')) }}">
                                                    <input type="checkbox" name="cursos[]" value="{{ $curso->id }}" class="da-check"
                                                        {{ $yaTiene ? 'checked disabled' : '' }}>
                                                    <span class="al-curso-texto">
                                                        <span class="al-curso-nombre">{{ $curso->nombre }}</span>
                                                        <span class="al-curso-meta d-block">
                                                            @if ($curso->creditos)<i class="fas fa-layer-group"></i>{{ $curso->creditos }} créd.@endif
                                                            @if ($curso->horas) · <i class="far fa-clock"></i>{{ $curso->horas }} h @endif
                                                            @if ($otros->isNotEmpty())
                                                                · <i class="fas fa-chalkboard-teacher"></i>{{ $otros->implode(', ') }}
                                                            @endif
                                                        </span>
                                                    </span>
                                                    <span class="al-curso-accion">
                                                        @if ($yaTiene)
                                                            <span class="al-badge is-ok"><i class="fas fa-check"></i> Ya asignado</span>
                                                        @elseif ($otros->isEmpty())
                                                            <span class="al-badge is-warn">Sin docente</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        @endforeach

                        <div class="al-vacio" id="daVacio" hidden>
                            <i class="fas fa-search"></i>
                            No hay cursos que coincidan con la búsqueda.
                        </div>
                    </div>

                    <div class="al-form-pie da-pie">
                        <span class="al-suave mr-auto" id="daContador" aria-live="polite">Ningún curso marcado</span>
                        <button type="button" class="al-btn al-btn-borde al-btn-sm" id="daLimpiar" hidden>Desmarcar</button>
                        <button type="submit" class="al-btn al-btn-oro" id="daEnviar" disabled>
                            <i class="fas fa-check"></i> Asignar cursos
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Cursos asignados ── --}}
            <div class="col-xl-5 mb-4 order-xl-1">
                <section class="al-card mb-0">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-book-open"></i></span>
                        <div>
                            <h2>Cursos asignados</h2>
                            <p>Los que el docente ve en su intranet</p>
                        </div>
                        <div class="al-card-cab-extra">
                            <span class="al-badge is-muted">{{ $docente->cursos->count() }}</span>
                        </div>
                    </div>

                    @forelse ($porGrupo as $grupo => $cursos)
                        <div class="al-grupo"><span>{{ $grupo }}</span></div>
                        <ul class="al-cursos">
                            @foreach ($cursos as $curso)
                                <li class="al-curso">
                                    <span class="al-curso-num">{{ ++$n }}</span>
                                    <div class="al-curso-texto">
                                        <div class="al-curso-nombre">{{ $curso->nombre }}</div>
                                        <div class="al-curso-meta">
                                            @if ($curso->creditos)<i class="fas fa-layer-group"></i>{{ $curso->creditos }} créd.@endif
                                            @if ($curso->horas) · <i class="far fa-clock"></i>{{ $curso->horas }} h @endif
                                        </div>
                                    </div>
                                    <div class="al-curso-accion">
                                        <form action="{{ route('docente.curso.eliminar', [$docente->id, $curso->id]) }}" method="POST" class="da-quitar"
                                            data-curso="{{ $curso->nombre }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="al-btn al-btn-borde al-btn-sm da-btn-quitar" title="Quitar este curso">
                                                <i class="fas fa-times"></i><span class="sr-only">Quitar {{ $curso->nombre }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @empty
                        <div class="al-vacio">
                            <i class="fas fa-book"></i>
                            Aún no tiene cursos asignados.<br>
                            <span style="font-size: .8rem;">Márcalos en «Agregar cursos».</span>
                        </div>
                    @endforelse
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var programa = document.getElementById('daPrograma');
            var ciclo = document.getElementById('daCiclo');
            var buscar = document.getElementById('daBuscar');
            var bloques = Array.prototype.slice.call(document.querySelectorAll('.da-bloque'));
            var vacio = document.getElementById('daVacio');
            var contador = document.getElementById('daContador');
            var enviar = document.getElementById('daEnviar');
            var limpiar = document.getElementById('daLimpiar');
            var norm = function (s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); };

            function llenarCiclos() {
                ciclo.innerHTML = '<option value="">Todos los ciclos</option>';
                ciclo.disabled = !programa.value;
                bloques.forEach(function (b) {
                    if (b.dataset.programa !== programa.value) return;
                    var o = document.createElement('option');
                    o.value = b.dataset.ciclo;
                    o.textContent = b.dataset.cicloNombre;
                    ciclo.appendChild(o);
                });
            }

            function filtrar() {
                var q = norm(buscar.value.trim());
                var visibles = 0;
                bloques.forEach(function (b) {
                    var enFiltro = (!programa.value || b.dataset.programa === programa.value)
                        && (!ciclo.value || b.dataset.ciclo === ciclo.value);
                    var hay = 0;
                    b.querySelectorAll('.da-opcion').forEach(function (op) {
                        var ok = enFiltro && (!q || norm(op.dataset.texto).indexOf(q) !== -1);
                        op.parentNode.hidden = !ok;
                        if (ok) hay++;
                    });
                    b.hidden = hay === 0;
                    visibles += hay;
                });
                vacio.hidden = visibles > 0;
            }

            function contar() {
                var n = document.querySelectorAll('.da-check:checked:not(:disabled)').length;
                contador.textContent = n === 0 ? 'Ningún curso marcado' : (n === 1 ? '1 curso marcado' : n + ' cursos marcados');
                enviar.disabled = n === 0;
                limpiar.hidden = n === 0;
                document.querySelectorAll('.da-opcion').forEach(function (op) {
                    var c = op.querySelector('.da-check');
                    op.classList.toggle('is-elegido', c.checked && !c.disabled);
                });
            }

            programa.addEventListener('change', function () { llenarCiclos(); filtrar(); });
            ciclo.addEventListener('change', filtrar);
            buscar.addEventListener('input', filtrar);
            document.getElementById('daLista').addEventListener('change', contar);
            limpiar.addEventListener('click', function () {
                document.querySelectorAll('.da-check:checked:not(:disabled)').forEach(function (c) { c.checked = false; });
                contar();
            });
            document.getElementById('daForm').addEventListener('submit', function () { enviar.disabled = true; });

            // Quitar curso: confirmación con el diseño del sistema (o confirm() si no está cargado)
            document.querySelectorAll('.da-quitar').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    var titulo = '¿Quitar «' + form.dataset.curso + '»?';
                    var texto = 'El docente dejará de ver este curso en su intranet. Las notas ya registradas no se borran.';
                    if (!window.PkAlert) {
                        if (!confirm(titulo + '\n' + texto)) e.preventDefault();
                        return;
                    }
                    e.preventDefault();
                    PkAlert.confirmar({ icon: 'warning', title: titulo, text: texto, confirmButtonText: 'Sí, quitar' })
                        .then(function (ok) { if (ok) form.submit(); });
                });
            });

            contar();
            filtrar();
        })();
    </script>
@endpush
