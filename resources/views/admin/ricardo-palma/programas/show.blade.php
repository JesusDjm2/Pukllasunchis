@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', $programa->nombre . ' - Malla Curricular (10 Semestres)')

@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid py-3 al-ui rp-modulo">
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.programas.index') }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Volver a Carreras
                        </a>
                    </div>
                    <h1>{{ $programa->nombre }}</h1>
                    <p class="al-hero-sub">
                        Malla curricular distribuida en 10 semestres académicos con asignaturas y créditos históricos (1995 - 2015).
                    </p>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('rp.importar.index') }}" class="al-btn al-btn-oro">
                        <i class="fas fa-file-excel"></i> Carga Masiva
                    </a>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        {{-- Desglose por los 10 Semestres --}}
        <div class="row">
            @for ($sem = 1; $sem <= 10; $sem++)
                @php
                    $cursosSem = $cursosPorSemestre->get($sem, collect());
                    $romanos = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
                @endphp
                <div class="col-lg-6 mb-4">
                    <div class="al-card h-100 mb-0">
                        <div class="al-card-cab">
                            <div class="al-card-cab-icono">
                                <i class="fas fa-bookmark"></i>
                            </div>
                            <div>
                                <h2>Semestre {{ $romanos[$sem] }}</h2>
                                <p>{{ $cursosSem->count() }} {{ $cursosSem->count() == 1 ? 'asignatura registrada' : 'asignaturas registradas' }}</p>
                            </div>
                            <div class="al-card-cab-extra">
                                <span class="al-badge is-muted">Ciclo {{ $sem }}</span>
                            </div>
                        </div>

                        @if ($cursosSem->count() > 0)
                            <ul class="al-cursos">
                                @foreach ($cursosSem as $c)
                                    <li class="al-curso">
                                        <div class="al-curso-num font-weight-bold">
                                            {{ $loop->iteration }}
                                        </div>
                                        <div class="al-curso-texto">
                                            <div class="al-curso-nombre">
                                                {{ $c->nombre }}
                                            </div>
                                            @if ($c->codigo)
                                                <div class="al-curso-meta">Código: {{ $c->codigo }}</div>
                                            @endif
                                        </div>
                                        <div class="al-curso-accion">
                                            <span class="al-badge is-info">
                                                {{ $c->creditos }} {{ $c->creditos == 1 ? 'crédito' : 'créditos' }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="al-vacio py-4">
                                <i class="fas fa-book-open"></i>
                                <span style="font-size: .84rem;">Se poblará automáticamente al importar las actas de este semestre.</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </div>
@endsection
