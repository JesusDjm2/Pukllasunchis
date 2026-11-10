{{-- Normalizar la RD del PTI importada del Excel (RdPtiController / NormalizarRdPti). Vista previa: nada se
     guarda hasta confirmar. Solo se corrigen las que traen el año en el texto; las demás se completan a mano
     desde la ficha (filtro «RD del PTI por completar»). Los casos que cambiarían un grupo no se tocan. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Services\Titulacion\NormalizarRdPti;
        $filas = $plan['filas'];
        $c = $plan['conteo'];
        $aplicar = $filas->filter(fn ($f) => ! empty($f['cambios']));
        $registros = $aplicar->flatMap(fn ($f) => $f['cambios'])->count();
        $conflictos = $filas->where('conflicto', true);
        $aMano = $filas->filter(fn ($f) => in_array($f['fuente'], ['sin_anio', 'pti'], true) && ! $f['conflicto']);
        $persona = fn ($f) => trim(($f['seguimiento']->user?->apellidos ?? '').', '.($f['seguimiento']->user?->name ?? ''));
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-broom"></i> Titulación · Limpieza de datos importados</div>
                <h1 class="tl-hero__titulo">Normalizar la RD del PTI</h1>
                <p class="tl-hero__sub">El Excel guardó la RD del PTI como nombre de archivo («RD- 088 PTI APAZA….pdf»). Aquí se dejan en formato <strong>«088-2021»</strong> las que traen el año. Nada se guarda hasta confirmar y se hace un respaldo de los valores originales.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a Titulación</a>
            </div>
            <div class="tl-hero__stats">
                <div class="tl-hero__pista">
                    <span class="tl-stat"><span class="tl-stat__num">{{ $aplicar->count() }}</span><span class="tl-stat__eti">Se corrigen solas</span></span>
                    <span class="tl-stat tl-stat--oro"><span class="tl-stat__num">{{ $aMano->count() }}</span><span class="tl-stat__eti">Completar a mano</span></span>
                    <span class="tl-stat"><span class="tl-stat__num">{{ $conflictos->count() }}</span><span class="tl-stat__eti">Unirían grupos</span></span>
                    <span class="tl-stat"><span class="tl-stat__num">{{ $c['ya'] ?? 0 }}</span><span class="tl-stat__eti">Ya correctas</span></span>
                    <span class="tl-stat"><span class="tl-stat__num">{{ $c['falta'] ?? 0 }}</span><span class="tl-stat__eti">Sin RD</span></span>
                </div>
            </div>
        </header>

        <div class="al-ui">
            @if (session('success'))
                <div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
            @endif
            <div class="al-aviso is-info"><i class="fas fa-info-circle"></i><div>
                <strong>Por qué no se completan solas las que no traen año:</strong> el año estimado por la fecha de inscripción del PTI acertó solo en el 87% de las RD que sí tienen año (a veces la RD sale al año siguiente).
                Esas {{ $aMano->count() }} se completan desde la ficha de cada persona (filtro «RD del PTI por completar» en Titulación), con el año sugerido ya escrito para verificarlo.
            </div></div>
        </div>

        @if ($aplicar->isNotEmpty())
            <section class="tl-tabla-card">
                <div class="tl-resumen"><span><strong>{{ $aplicar->count() }}</strong> <span class="tl-resumen__suave">personas · {{ $registros }} registros (PTI y resoluciones) quedarán así</span></span></div>
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table titulacion-tabla">
                        <thead><tr><th>Persona</th><th>Hoy dice</th><th>Quedará</th></tr></thead>
                        <tbody>
                            @foreach ($aplicar as $f)
                                <tr><td>{{ $persona($f) }}</td><td class="text-muted small">{{ \Illuminate\Support\Str::limit($f['texto'], 70) }}</td><td><span class="tc-exp">{{ $f['propuesto'] }}</span></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <form method="POST" action="{{ route('titulacion.rdpti.aplicar') }}" class="p-3 d-flex flex-wrap align-items-center" style="gap:.75rem;" id="rnAplicar">
                    @csrf
                    <label class="mb-0 small"><input type="checkbox" name="confirmar" value="1" required class="mr-1">Revisé la lista: aplicar el formato a estos {{ $registros }} registros.</label>
                    <button type="submit" class="btn btn-sm btn-primary ml-auto"><i class="fas fa-check mr-1"></i>Aplicar</button>
                </form>
            </section>
        @endif

        @if ($conflictos->isNotEmpty())
            <section class="tl-tabla-card">
                <div class="tl-resumen"><span><strong>{{ $conflictos->count() }}</strong> <span class="tl-resumen__suave">no se tocan: con el formato nuevo quedarían con el mismo número que otro grupo y se unirían. Revíselas en el expediente.</span></span></div>
                <div class="table-responsive">
                    <table class="table titulacion-tabla">
                        <thead><tr><th>Persona</th><th>Hoy dice</th><th>Quedaría</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($conflictos as $f)
                                <tr><td>{{ $persona($f) }}</td><td class="text-muted small">{{ \Illuminate\Support\Str::limit($f['texto'], 70) }}</td><td>{{ $f['propuesto'] }}</td>
                                    <td><a href="{{ route('titulacion.index', ['ver' => $f['seguimiento']->user_id, 'tab' => 'proceso', 'paso' => 'pti_datos']) }}">Abrir ficha</a></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($aMano->isNotEmpty())
            <section class="tl-tabla-card">
                <div class="tl-resumen"><span><strong>{{ $aMano->count() }}</strong> <span class="tl-resumen__suave">para completar a mano (sin año en el texto)</span></span>
                    <a href="{{ route('titulacion.index', ['tarea' => 'rd_pti']) }}" class="ml-auto small">Ver en Titulación con el filtro</a></div>
                <div class="table-responsive" style="max-height: 360px;">
                    <table class="table titulacion-tabla">
                        <thead><tr><th>Persona</th><th>Hoy dice</th><th>Año sugerido</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($aMano as $f)
                                <tr><td>{{ $persona($f) }}</td><td class="text-muted small">{{ \Illuminate\Support\Str::limit($f['texto'], 70) }}</td>
                                    <td class="small">{{ $f['propuesto'] ? \Illuminate\Support\Str::after($f['propuesto'], '-').' (estimado)' : '—' }}</td>
                                    <td><a href="{{ route('titulacion.index', ['ver' => $f['seguimiento']->user_id, 'tab' => 'proceso', 'paso' => 'pti_datos']) }}">Completar</a></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>

    <script>
    document.getElementById('rnAplicar')?.addEventListener('submit', function (e) {
        var f = this;
        if (f.dataset.ok === '1') return;
        e.preventDefault();
        if (!f.checkValidity()) { f.reportValidity(); return; }
        var seguir = function () { f.dataset.ok = '1'; f.submit(); };
        if (window.PkAlert) PkAlert.confirmar({ title: '¿Aplicar el formato?', text: 'Se cambiará el texto del número de la RD del PTI en {{ $registros }} registros. Se guarda un respaldo con los valores originales.', confirmButtonText: 'Sí, aplicar' }).then(function (ok) { if (ok) seguir(); });
        else if (confirm('¿Aplicar el formato a {{ $registros }} registros?')) seguir();
    });
    </script>
@endsection
