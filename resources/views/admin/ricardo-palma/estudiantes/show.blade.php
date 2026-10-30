@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Récord Académico: ' . $estudiante->nombre_completo)

@section('contenido')
    @include('alumnos.partials.ui')
    @include('partials.flash-swal')

    <div class="container-fluid py-3 al-ui rp-modulo">
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.estudiantes.index') }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Volver a Estudiantes
                        </a>
                    </div>
                    <h1>{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</h1>
                    <p class="al-hero-sub">
                        {{ $estudiante->programa?->nombre ?: 'Sin carrera' }} · Matrícula: {{ $estudiante->numero_matricula ?: 'S/N' }} · DNI: {{ $estudiante->dni ?: 'S/N' }}
                    </p>
                    <div class="al-chips">
                        <span class="al-chip"><i class="fas fa-venus-mars"></i> Sexo: {{ $estudiante->sexo ?: '—' }}</span>
                        <span class="al-chip"><i class="fas fa-phone"></i> Tel: {{ $estudiante->telefono ?: '—' }}</span>
                        <span class="al-chip is-{{ $estudiante->estado === 'RETIRADO' ? 'warn' : 'ok' }}">
                            <i class="fas fa-check-circle"></i> Estado: {{ $estudiante->estado }}
                        </span>
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('rp.estudiantes.editar', $estudiante) }}" class="al-btn al-btn-oro">
                        <i class="fas fa-pen"></i> Editar
                    </a>
                    <button type="button" class="al-btn al-btn-claro" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Ficha
                    </button>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        {{-- Calificaciones Históricas Agrupadas por Acta / Semestre --}}
        @forelse ($calificacionesPorActa as $actaId => $califs)
            @php
                $acta = $califs->first()?->acta;
                $resumen = \App\Models\RicardoPalma\RpCalificacion::resumen($califs);
                $puntajeTotal = $resumen->puntaje_total;
                $creditosTotal = $resumen->total_creditos;
                $promPonderado = $resumen->promedio_ponderado;
            @endphp
            <div class="al-card">
                <div class="al-card-cab">
                    <div class="al-card-cab-icono">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <h2>Periodo {{ $acta?->periodo?->codigo ?: '—' }} · Semestre {{ $acta?->numero_semestre ?: '—' }}</h2>
                        <p>
                            Acta {{ $acta?->numero_acta ? '№ ' . $acta->numero_acta : '#' . $acta?->id }} · Sección: {{ $acta?->seccion }} · Turno: {{ $acta?->turno }}
                        </p>
                    </div>
                    <div class="al-card-cab-extra">
                        <span class="al-badge is-muted">Puntaje: {{ $puntajeTotal }}</span>
                        <span class="al-badge is-info">Créditos: {{ $creditosTotal }}</span>
                        @if ($promPonderado !== null)
                            <span class="al-badge {{ $promPonderado >= 11 ? 'is-ok' : 'is-mal' }}" style="font-size: .8rem; padding: .25rem .75rem;">
                                Promedio: {{ number_format($promPonderado, 2) }}
                            </span>
                        @else
                            <span class="al-badge is-mal">{{ $resumen->situacion === 'LICENCIA' ? 'Licencia' : 'Retirado' }}</span>
                        @endif
                    </div>
                </div>

                <div class="al-tabla-marco">
                    <table class="al-tabla">
                        <thead>
                            <tr>
                                <th>Asignatura</th>
                                <th class="text-center">Créditos</th>
                                <th class="text-center">Calificativo (CLF)</th>
                                <th class="text-center">Puntaje (PJT)</th>
                                <th class="text-center">Situación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($califs as $c)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $c->curso?->nombre ?: '—' }}</span>
                                    </td>
                                    <td class="text-center al-suave">
                                        {{ $c->curso?->creditos ?: 0 }} crd
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        @if ($c->calificacion !== null)
                                            <span class="rp-nota {{ $c->calificacion < 11 ? 'is-mal' : 'is-ok' }}">
                                                {{ number_format($c->calificacion, 0) }}
                                            </span>
                                        @else
                                            <span class="al-suave">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center al-suave rp-pjt">
                                        {{ $c->puntaje !== null ? number_format($c->puntaje, 0) : '—' }}
                                    </td>
                                    <td class="text-center">
                                        @if ($c->calificacion === null)
                                            <span class="al-badge is-mal">{{ mb_strtoupper((string) $c->observacion) === 'LICENCIA' ? 'Licencia' : 'Retirado' }}</span>
                                        @elseif ($c->calificacion >= 11)
                                            <span class="al-badge is-ok">Aprobado</span>
                                        @else
                                            <span class="al-badge is-warn">Desaprobado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="al-card">
                <div class="al-vacio">
                    <i class="fas fa-folder-open"></i>
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: .4rem;">No hay actas registradas para este estudiante</h3>
                    <p style="font-size: .85rem;">Las calificaciones aparecerán aquí tan pronto como se carguen las actas consolidadas correspondientes.</p>
                </div>
            </div>
        @endforelse
    </div>
@endsection
