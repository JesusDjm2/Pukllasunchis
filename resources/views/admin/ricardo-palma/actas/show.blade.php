@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Acta Consolidada: ' . ($acta->numero_acta ? '№ ' . $acta->numero_acta : '#' . $acta->id))

@section('contenido')
    @include('alumnos.partials.ui')
    @include('partials.flash-swal')

    <div class="container-fluid py-3 al-ui rp-modulo">
        {{-- Cabecera con estilo del Acta Oficial --}}
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.actas.index') }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Volver a Actas
                        </a>
                    </div>
                    <h1>Acta Consolidada de Evaluación Académica</h1>
                    <p class="al-hero-sub">
                        IESP "RICARDO PALMA" · {{ $acta->programa?->nombre }} · Periodo: {{ $acta->periodo?->codigo }} · Semestre: {{ $acta->numero_semestre }}
                    </p>
                    <div class="al-chips">
                        <span class="al-chip"><i class="fas fa-file-invoice"></i> Acta: {{ $acta->numero_acta ?: 'S/N' }}</span>
                        <span class="al-chip"><i class="fas fa-layer-group"></i> Sección: {{ $acta->seccion }}</span>
                        <span class="al-chip"><i class="fas fa-clock"></i> Turno: {{ $acta->turno }}</span>
                        <span class="al-chip"><i class="fas fa-calendar-alt"></i> Visación: {{ $acta->fecha_emision ? $acta->fecha_emision->format('d/m/Y') : '—' }}</span>
                        <span class="al-chip is-ok"><i class="fas fa-user-graduate"></i> {{ $estudiantes->count() }} Estudiantes</span>
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('rp.actas.editar', $acta) }}" class="al-btn al-btn-claro">
                        <i class="fas fa-pen"></i> Editar
                    </a>
                    <a href="{{ route('rp.actas.excel', $acta) }}" class="al-btn al-btn-oro">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </a>
                    <a href="{{ route('rp.actas.imprimir', $acta) }}" target="_blank" class="al-btn al-btn-claro">
                        <i class="fas fa-print"></i> Imprimir Formato Oficial
                    </a>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        {{-- Matriz Oficial de Calificaciones --}}
        <div class="rp-matriz-wrapper">
            <div class="rp-swipe-hint">
                <i class="fas fa-arrows-alt-h"></i> Desliza horizontalmente para revisar todas las asignaturas
            </div>

            <table class="rp-matriz">
                <thead>
                    <tr>
                        <th rowspan="2" class="rp-sticky-1">N°</th>
                        <th rowspan="2" class="rp-sticky-2">Matrícula</th>
                        <th rowspan="2" class="rp-sticky-3" style="text-align: left;">Apellidos y Nombres</th>
                        @foreach ($cursos as $c)
                            <th colspan="2" class="text-center" style="min-width: 130px;">
                                <div class="text-truncate" style="max-width: 150px; margin: 0 auto;" title="{{ $c->nombre }}">
                                    {{ $c->nombre }}
                                </div>
                                <span class="rp-curso-badge">{{ $c->creditos }} crd</span>
                            </th>
                        @endforeach
                        <th rowspan="2" class="rp-col-resumen" style="width: 55px;" title="Puntaje Total">Pjt Total</th>
                        <th rowspan="2" class="rp-col-resumen" style="width: 50px;" title="Total de Créditos">Créditos</th>
                        <th rowspan="2" class="rp-col-resumen" style="width: 75px;" title="Promedio Ponderado">Prom. Pond.</th>
                        <th rowspan="2" class="rp-col-resumen" style="width: 95px;">Situación</th>
                    </tr>
                    <tr>
                        @foreach ($cursos as $c)
                            <th class="rp-th-sub" style="width: 36px;" title="Calificación (0 - 20)">CLF</th>
                            <th class="rp-th-sub" style="width: 36px;" title="Puntaje (CLF x Créditos)">PJT</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estudiantes as $index => $item)
                        <tr>
                            <td class="rp-sticky-1 font-weight-bold" style="color: var(--al-suave);">
                                {{ $index + 1 }}
                            </td>
                            <td class="rp-sticky-2 font-weight-bold" style="color: var(--al-oro);">
                                {{ $item->estudiante?->numero_matricula ?: '—' }}
                            </td>
                            <td class="rp-sticky-3">
                                <a href="{{ route('rp.estudiantes.show', $item->estudiante) }}" class="font-weight-bold">
                                    {{ $item->estudiante?->apellidos }}, {{ $item->estudiante?->nombres }}
                                </a>
                                @if ($item->estudiante?->dni)
                                    <div class="al-suave" style="font-size: .7rem; font-weight: normal;">DNI: {{ $item->estudiante->dni }}</div>
                                @endif
                            </td>

                            {{-- Calificaciones por curso --}}
                            @foreach ($cursos as $c)
                                @php
                                    $calif = $item->notas->get($c->id);
                                    $nota = $calif?->calificacion;
                                    $pjt = $calif?->puntaje;
                                @endphp
                                <td class="text-center font-weight-bold">
                                    @if ($nota !== null)
                                        <span class="rp-nota {{ $nota < 11 ? 'is-mal' : 'is-ok' }}">
                                            {{ number_format($nota, 0) }}
                                        </span>
                                    @else
                                        <span style="color: var(--al-suave);">—</span>
                                    @endif
                                </td>
                                <td class="text-center rp-pjt">
                                    {{ $pjt !== null ? number_format($pjt, 0) : '—' }}
                                </td>
                            @endforeach

                            {{-- Totales y Promedios --}}
                            <td class="text-center font-weight-bold rp-col-resumen">{{ $item->puntaje_total }}</td>
                            <td class="text-center rp-col-resumen">{{ $item->total_creditos }}</td>
                            <td class="text-center">
                                @if ($item->promedio_ponderado !== null)
                                    <span class="rp-promedio {{ $item->promedio_ponderado < 11 ? 'is-mal' : 'is-ok' }}">
                                        {{ number_format($item->promedio_ponderado, 2) }}
                                    </span>
                                @else
                                    <span style="color: var(--al-suave);">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($item->retirado)
                                    <span class="al-badge is-mal"><i class="fas fa-user-slash"></i> {{ $item->situacion === 'LICENCIA' ? 'Licencia' : 'Retirado' }}</span>
                                @elseif ($item->situacion === 'APROBADO')
                                    <span class="al-badge is-ok"><i class="fas fa-check"></i> Aprobado</span>
                                @else
                                    <span class="al-badge is-warn" title="Desaprobó {{ $item->cursos_desaprobados }} {{ $item->cursos_desaprobados === 1 ? 'curso' : 'cursos' }}"><i class="fas fa-exclamation"></i> Desaprobado ({{ $item->cursos_desaprobados }})</span>
                                @endif
                                @if ($item->observacion)
                                    <div class="rp-obs-alumno">{{ $item->observacion }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 7 + ($cursos->count() * 2) }}" class="text-center py-5">
                                <div class="al-vacio">
                                    <i class="fas fa-folder-open"></i>
                                    No hay estudiantes registrados en este acta.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cuadro estadístico (como el del acta física) --}}
        <article class="al-card mt-3">
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas fa-chart-bar"></i></span>
                <div>
                    <h2>Cuadro estadístico</h2>
                    <p>Calculado de las notas. Subsanante: desaprobó hasta {{ \App\Models\RicardoPalma\RpActa::MAX_AREAS_SUBSANACION }} áreas; observado: más. % sobre el total de matriculados.</p>
                </div>
            </div>
            @include('admin.ricardo-palma.actas._cuadro', ['clase' => 'al-tabla rp-cuadro'])
        </article>
    </div>
@endsection
