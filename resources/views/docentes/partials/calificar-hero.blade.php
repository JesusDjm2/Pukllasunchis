{{--
    Cabecera de la pantalla de calificar (FID y PPD). Reemplaza al encabezado genérico
    (ui-header) + la leyenda vertical: todo lo que el docente necesita en una sola tarjeta.
    Espera: $curso, $docente, $tipo ('FID'|'PPD'), $competencias (Collection), $totalAlumnos (int),
            $avance (array de [etiqueta, porcentaje, color]), $periodo (nombre o null).
    Las competencias abren su detalle con openModal(this) (docentes.partials.competencia-modal).
--}}
@php
    $escala = [
        ['17–20', 'Destacado', '#103b86'],
        ['14–16', 'Logrado', '#0b954e'],
        ['11–13', 'En proceso', '#a8930b'],
        ['6–10', 'Inicio', '#dc3545'],
        ['1–5', 'Previo al inicio', '#dc3545'],
    ];
@endphp
<section class="cal-hero mb-3 mb-md-4">
    <div class="cal-hero-fila">
        @include('docentes.partials.boton-volver', ['url' => route('vistaDocente', $docente->id), 'label' => 'Mis cursos'])
        <div class="cal-hero-titulo">
            <span class="cal-hero-sello {{ $tipo === 'PPD' ? 'is-ppd' : 'is-fid' }}">
                <i class="fas {{ $tipo === 'PPD' ? 'fa-chalkboard-teacher' : 'fa-graduation-cap' }}"></i> Calificaciones {{ $tipo }}
            </span>
            <h1>{{ $curso->nombre }}</h1>
            <div class="cal-hero-chips">
                <span><i class="fas fa-university"></i>{{ $curso->ciclo->programa->nombre ?? '—' }}</span>
                <span><i class="fas fa-layer-group"></i>Ciclo {{ trim(str_ireplace('Ciclo', '', $curso->ciclo->nombre ?? '—')) }}</span>
                @if ($periodo)
                    <span><i class="far fa-calendar-alt"></i>Periodo {{ $periodo }}</span>
                @endif
                <span><i class="fas fa-users"></i>{{ $totalAlumnos }} {{ $totalAlumnos === 1 ? 'estudiante' : 'estudiantes' }}</span>
            </div>
        </div>
        @if (Route::has('docente.curso.excel'))
            <a href="{{ route('docente.curso.excel', ['docente' => $docente->id, 'curso' => $curso->id]) }}" class="cal-hero-excel"
                title="Descargar las calificaciones de este curso en Excel">
                <i class="fas fa-file-excel"></i> Excel
            </a>
        @endif
    </div>

    <div class="cal-hero-cuerpo">
        <div class="cal-hero-bloque">
            <div class="cal-hero-etiqueta">Avance de calificación</div>
            <div class="cal-hero-avance">
                @foreach ($avance as [$etiqueta, $pct, $color])
                    <div class="cal-hero-avance-item">
                        <div class="cal-hero-avance-cab">
                            <b style="color: {{ $color }}">{{ $etiqueta }}</b>
                            <b>{{ number_format($pct, 0) }}%</b>
                        </div>
                        <div class="cal-hero-barra"><i style="width: {{ min(100, $pct) }}%; background: {{ $color }}"></i></div>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($competencias && $competencias->isNotEmpty())
            <div class="cal-hero-bloque">
                <div class="cal-hero-etiqueta">Competencias a calificar <small>— toca una para ver el detalle</small></div>
                <div class="cal-hero-competencias">
                    @foreach ($competencias as $competencia)
                        <a data-id="{{ $competencia->id }}" data-nombre="{{ $competencia->nombre }}"
                            data-descripcion="{{ addslashes($competencia->descripcion) }}"
                            data-capacidades="{!! addslashes($competencia->capacidades) !!}"
                            onclick="openModal(this)" role="button" tabindex="0">
                            {{ $competencia->nombre }} <i class="fas fa-question-circle"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="cal-hero-escala" aria-label="Rango de calificación">
        <b class="cal-hero-etiqueta mb-0">Rango de calificación</b>
        @foreach ($escala as [$rango, $nivel, $color])
            <span style="--c: {{ $color }}"><b>{{ $rango }}</b> {{ $nivel }}</span>
        @endforeach
    </div>
</section>

@once
<style>
    .cal-hero {
        --ch-fondo: #fff; --ch-fondo-2: #f6f7fb; --ch-borde: #e6e9f1; --ch-texto: #2e3448; --ch-suave: #6b7185; --ch-acento: #4e73df;
        background: var(--ch-fondo); border: 1px solid var(--ch-borde); border-radius: .85rem;
        box-shadow: 0 .15rem 1.25rem rgba(46, 52, 72, .07); overflow: hidden;
    }
    body.dark-mode .cal-hero { --ch-fondo: #161b22; --ch-fondo-2: #0d1117; --ch-borde: #30363d; --ch-texto: #e6e8ef; --ch-suave: #9aa3b5; --ch-acento: #8ea6f0; }
    body.dim-mode .cal-hero { --ch-fondo: var(--lm-superficie); --ch-fondo-2: var(--lm-superficie-2); --ch-borde: var(--lm-borde); --ch-texto: var(--lm-texto); --ch-suave: var(--lm-suave); --ch-acento: var(--lm-acento); }

    .cal-hero-fila { display: flex; align-items: center; gap: 1.1rem; padding: 1.1rem 1.4rem; border-bottom: 1px solid var(--ch-borde); }
    .cal-hero-fila .docente-ui-volver { flex: none; width: auto; }
    .cal-hero-titulo { flex: 1; min-width: 0; }
    .cal-hero-titulo h1 { font-size: 1.3rem; font-weight: 800; color: var(--ch-texto); margin: .2rem 0 .4rem; letter-spacing: -.01em; line-height: 1.2; }
    .cal-hero-sello {
        display: inline-flex; align-items: center; gap: .35rem; font-size: .64rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; padding: .18rem .6rem; border-radius: 999px;
    }
    .cal-hero-sello.is-fid { background: rgba(78, 115, 223, .12); color: #2e4fb8; }
    .cal-hero-sello.is-ppd { background: rgba(229, 151, 58, .15); color: #b5651d; }
    body.dark-mode .cal-hero-sello.is-fid { color: #a9bbf5; } body.dark-mode .cal-hero-sello.is-ppd { color: #f5b45f; }
    .cal-hero-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
    .cal-hero-chips span {
        display: inline-flex; align-items: center; gap: .35rem; font-size: .74rem; font-weight: 600; color: var(--ch-suave);
        background: var(--ch-fondo-2); border: 1px solid var(--ch-borde); border-radius: 999px; padding: .18rem .65rem;
    }
    .cal-hero-chips i { color: var(--ch-acento); font-size: .7rem; }
    .cal-hero-excel {
        flex: none; display: inline-flex; align-items: center; gap: .45rem; padding: .5rem 1.1rem; border-radius: 999px;
        background: #1d6f42; color: #fff !important; font-weight: 700; font-size: .82rem; text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(29, 111, 66, .3); transition: background-color .15s ease, transform .15s ease;
    }
    .cal-hero-excel:hover { background: #185c37; transform: translateY(-1px); }

    .cal-hero-cuerpo { display: grid; grid-template-columns: minmax(260px, 2fr) 3fr; gap: 1.25rem 2rem; padding: 1rem 1.4rem; }
    .cal-hero-etiqueta { font-size: .66rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--ch-suave); margin-bottom: .55rem; }
    .cal-hero-etiqueta small { text-transform: none; letter-spacing: normal; font-weight: 500; }
    .cal-hero-avance { display: grid; gap: .55rem; }
    .cal-hero-avance-cab { display: flex; justify-content: space-between; font-size: .76rem; margin-bottom: .2rem; color: var(--ch-texto); }
    .cal-hero-barra { height: 7px; border-radius: 999px; background: var(--ch-fondo-2); overflow: hidden; }
    .cal-hero-barra i { display: block; height: 100%; border-radius: 999px; transition: width .9s cubic-bezier(.22, 1, .36, 1); }
    .cal-hero-competencias { display: flex; flex-wrap: wrap; gap: .45rem; }
    .cal-hero-competencias a {
        display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .8rem; border-radius: .6rem; cursor: pointer;
        background: rgba(78, 115, 223, .08); border: 1px solid rgba(78, 115, 223, .22); color: #2e3f8f !important;
        font-size: .8rem; font-weight: 600; text-decoration: none !important; line-height: 1.3;
        transition: transform .15s ease, background-color .15s ease, box-shadow .15s ease;
    }
    .cal-hero-competencias a i { color: #6f80c9; font-size: .8rem; }
    .cal-hero-competencias a:hover, .cal-hero-competencias a:focus { background: rgba(78, 115, 223, .15); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(78, 115, 223, .2); outline: none; }
    body.dark-mode .cal-hero-competencias a, body.dim-mode .cal-hero-competencias a { color: var(--ch-texto) !important; }

    .cal-hero-escala {
        display: flex; flex-wrap: wrap; align-items: center; gap: .4rem .5rem; padding: .7rem 1.4rem;
        background: var(--ch-fondo-2); border-top: 1px solid var(--ch-borde);
    }
    .cal-hero-escala > b { margin-right: .35rem; }
    .cal-hero-escala span {
        display: inline-flex; align-items: center; gap: .35rem; font-size: .74rem; font-weight: 600; color: var(--ch-texto);
        padding: .15rem .6rem .15rem .45rem; border-radius: 999px; background: var(--ch-fondo); border: 1px solid var(--ch-borde);
    }
    .cal-hero-escala span::before { content: ''; width: .55rem; height: .55rem; border-radius: 50%; background: var(--c); }
    .cal-hero-escala span b { color: var(--c); }

    @media (max-width: 991.98px) {
        .cal-hero-fila { flex-wrap: wrap; }
        .cal-hero-cuerpo { grid-template-columns: 1fr; }
    }
    @media (prefers-reduced-motion: reduce) { .cal-hero-barra i { transition: none; } }
</style>
@endonce
