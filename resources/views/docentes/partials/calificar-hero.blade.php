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
    $progNombre = $curso->ciclo->programa->nombre ?? '';
    $esInicial = stripos($progNombre, 'Inicial') !== false;
    $esPrimaria = stripos($progNombre, 'Primaria') !== false;
    $progClase = $esInicial ? 'is-prog-inicial' : ($esPrimaria ? 'is-prog-primaria' : 'is-prog-default');
    $progIcono = $esInicial ? 'fa-shapes' : ($esPrimaria ? 'fa-book-reader' : 'fa-university');
@endphp
<style>
    .cal-hero-sello.is-prog-inicial {
        background: #e0e7ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
    }
    .cal-hero-sello.is-prog-primaria {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .cal-hero-sello.is-prog-default {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    .cal-hero-chips .cal-hero-chip-prog.is-prog-inicial {
        background: #eef2ff !important;
        color: #3730a3 !important;
        border: 1px solid #c7d2fe !important;
        font-weight: 700 !important;
    }
    .cal-hero-chips .cal-hero-chip-prog.is-prog-primaria {
        background: #ecfdf5 !important;
        color: #065f46 !important;
        border: 1px solid #a7f3d0 !important;
        font-weight: 700 !important;
    }
</style>
<section class="cal-hero mb-3 mb-md-4">
    <div class="cal-hero-fila">
        @include('docentes.partials.boton-volver', ['url' => route('vistaDocente', $docente->id), 'label' => 'Mis cursos'])
        <div class="cal-hero-titulo">
            <div class="d-flex align-items-center flex-wrap mb-1" style="gap: 0.4rem;">
                <span class="cal-hero-sello {{ $tipo === 'PPD' ? 'is-ppd' : 'is-fid' }}">
                    <i class="fas {{ $tipo === 'PPD' ? 'fa-chalkboard-teacher' : 'fa-graduation-cap' }}"></i> Calificaciones {{ $tipo }}
                </span>
                <span class="cal-hero-sello {{ $progClase }}" style="font-weight: 700;">
                    <i class="fas {{ $progIcono }}"></i> {{ $progNombre ?: 'Sin programa' }}
                </span>
            </div>
            <h1>{{ $curso->nombre }}</h1>
            <div class="cal-hero-chips">
                <span class="cal-hero-chip-prog {{ $progClase }}"><i class="fas {{ $progIcono }}"></i>{{ $progNombre ?: '—' }}</span>
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

{{-- Estilos en public/css/pk-ui.css (sección «Cabecera de calificar»). --}}
@include('alumnos.partials.ui')
