{{-- Excepciones de Secretaría (HabilitacionPtiController): habilitar a un alumno para solicitar la aprobación de su PTI
     antes de lo que marca su ciclo. Buscar por DNI o apellidos, habilitar con un motivo, quitar la habilitación. --}}
@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    @include('alumnos.partials.ui')
    @php
        use App\Models\Titulacion\Habilitacion;
        use App\Http\Controllers\Titulacion\TitulacionController as T;
        $nombre = fn ($u) => trim(($u?->apellidos ?? '').', '.($u?->name ?? ''), ', ');
        $ciclo = fn ($u) => $u?->ciclo ? (preg_match('/^ciclo\s/i', $u->ciclo->nombre) ? $u->ciclo->nombre : 'Ciclo '.$u->ciclo->nombre) : 'Sin ciclo';
    @endphp

    <div class="container-fluid tl">
        <header class="tl-hero">
            <div class="tl-hero__texto">
                <div class="tl-hero__kicker"><i class="fas fa-user-check"></i> Titulación · Solicitudes · Secretaría</div>
                <h1 class="tl-hero__titulo">Habilitar solicitud de PTI</h1>
                <p class="tl-hero__sub">Excepciones: alumnos autorizados a solicitar la aprobación de su PTI antes del Ciclo IX (por ejemplo, con autorización de Dirección). Al habilitarlo, ve el formulario en su intranet y recibe un aviso en su campanita.</p>
            </div>
            <div class="tl-hero__acciones">
                <a href="{{ route('titulacion.solicitudes-pti.index') }}" class="tl-hero__btn"><i class="fas fa-arrow-left"></i>Volver a las solicitudes de PTI</a>
            </div>
            <div class="tl-hero__stats">
                <div class="tl-hero__pista">
                    <span class="tl-stat tl-stat--oro"><span class="tl-stat__num">{{ $habilitados->count() }}</span><span class="tl-stat__eti">Habilitados</span></span>
                    <span class="tl-stat"><span class="tl-stat__num">{{ $habilitados->filter(fn ($h) => $h->user?->seguimientoTitulacion)->count() }}</span><span class="tl-stat__eti">Ya con proceso iniciado</span></span>
                </div>
            </div>
        </header>

        <div class="al-ui">
            @include('titulacion.solicitudes._pestanas')
            @if (session('success'))<div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>@endif
            @if (session('error'))<div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ session('error') }}</div></div>@endif
            @if ($errors->any())<div class="al-aviso is-mal"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>@endif
            @unless ($puedeGestionar)
                <div class="al-aviso is-info"><i class="fas fa-eye"></i><div>Vista de solo lectura: las habilitaciones las da Secretaría.</div></div>
            @endunless
        </div>

        @if ($puedeGestionar)
            <section class="tl-tabla-card">
                <form method="GET" action="{{ route('titulacion.habilitaciones.index') }}" class="tl-filtros ca-filtros">
                    <div class="tl-filtro tl-filtro--buscar">
                        <label for="hb-q">Buscar al alumno</label>
                        <div class="tl-buscar"><i class="fas fa-search"></i><input type="search" id="hb-q" name="q" value="{{ $q }}" class="form-control" placeholder="DNI o apellidos (mínimo 3 caracteres)" minlength="3" autofocus></div>
                    </div>
                    <div class="tl-filtro ca-filtros__botones"><button type="submit" class="btn tp-btn"><i class="fas fa-search mr-1"></i>Buscar</button></div>
                </form>
                @if (mb_strlen($q) >= 3)
                    <div class="table-responsive">
                        <table class="table titulacion-tabla ca-tabla">
                            <thead><tr><th>Alumno</th><th>Ciclo</th><th>Situación</th><th>Habilitar</th></tr></thead>
                            <tbody>
                                @forelse ($resultados as $u)
                                    @php
                                        $habilitado = Habilitacion::tiene($u);
                                        $situacion = match (true) {
                                            (bool) $u->seguimientoTitulacion => ['is-ok', 'Ya tiene su proceso iniciado'],
                                            $habilitado => ['is-ok', 'Habilitado'],
                                            T::puedeIniciar($u) => ['is-info', 'Ya puede solicitar (cumple el ciclo)'],
                                            default => ['is-muted', 'Aún no puede solicitar'],
                                        };
                                        $ofrecer = ! $u->seguimientoTitulacion && ! $habilitado && ! T::puedeIniciar($u);
                                    @endphp
                                    <tr>
                                        <td><div class="ca-persona"><strong>{{ $nombre($u) }}</strong><small>DNI {{ $u->dni ?: '—' }} · {{ $u->programa->nombre ?? '—' }}</small></div></td>
                                        <td><small>{{ $ciclo($u) }}</small></td>
                                        <td><span class="al-badge {{ $situacion[0] }}">{{ $situacion[1] }}</span></td>
                                        <td>
                                            @if ($ofrecer)
                                                <form method="POST" action="{{ route('titulacion.habilitaciones.store') }}" class="d-flex flex-wrap" style="gap:.4rem;">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                                    <input type="text" name="motivo" required minlength="5" maxlength="500" class="form-control form-control-sm" style="min-width:14rem; flex:1;" placeholder="Motivo (p. ej. autorización de Dirección, Oficio N°…)">
                                                    <button type="submit" class="btn btn-sm tp-btn"><i class="fas fa-user-check mr-1"></i>Habilitar</button>
                                                </form>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No se encontró ningún estudiante con «{{ $q }}».</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif

        <section class="tl-tabla-card">
            <div class="tl-resumen"><span><strong>{{ $habilitados->count() }}</strong> <span class="tl-resumen__suave">{{ $habilitados->count() === 1 ? 'alumno habilitado' : 'alumnos habilitados' }} · del más reciente al más antiguo</span></span></div>
            <div class="table-responsive">
                <table class="table titulacion-tabla ca-tabla">
                    <thead><tr><th>Alumno</th><th>Ciclo</th><th>Motivo</th><th>Habilitado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        @forelse ($habilitados as $h)
                            <tr>
                                <td>
                                    <div class="ca-persona"><strong>{{ $nombre($h->user) }}</strong><small>DNI {{ $h->user?->dni ?: '—' }}</small></div>
                                    @if ($h->user?->seguimientoTitulacion)<span class="al-badge is-ok">Proceso iniciado</span>@endif
                                </td>
                                <td><small>{{ $ciclo($h->user) }}</small></td>
                                <td><small>{{ $h->motivo }}</small></td>
                                <td><small>{{ $h->created_at->format('d/m/Y') }}{{ $h->habilitadoPor ? ' · '.$h->habilitadoPor->name : '' }}</small></td>
                                <td class="ca-acciones">
                                    @if ($puedeGestionar && ! $h->user?->seguimientoTitulacion)
                                        <form method="POST" action="{{ route('titulacion.habilitaciones.destroy', $h->id) }}" class="js-hb-quitar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm tp-btn tp-btn--claro" title="Quitar la habilitación"><i class="fas fa-user-times"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Todavía no hay alumnos habilitados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
    document.querySelectorAll('.js-hb-quitar').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (f.dataset.ok === '1') return;
            e.preventDefault();
            var seguir = function () { f.dataset.ok = '1'; f.submit(); };
            var texto = 'El alumno ya no podrá enviar la solicitud de PTI hasta que cumpla su ciclo.';
            if (window.PkAlert) PkAlert.confirmar({ title: '¿Quitar la habilitación?', text: texto, confirmButtonText: 'Sí, quitar' }).then(function (ok) { if (ok) seguir(); });
            else if (confirm(texto)) seguir();
        });
    });
    </script>
@endsection
