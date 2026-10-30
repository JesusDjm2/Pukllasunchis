@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Programas y Carreras - IESP Ricardo Palma')

@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid py-3 al-ui rp-modulo">
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.dashboard') }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Dashboard Ricardo Palma
                        </a>
                    </div>
                    <h1>Carreras Profesionales y Planes de Estudio</h1>
                    <p class="al-hero-sub">
                        Estructura académica de las 4 especialidades base, cada una diseñada en 10 semestres académicos.
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

        <div class="row">
            @foreach ($programas as $prog)
                <div class="col-md-6 mb-4">
                    <div class="al-card h-100 mb-0">
                        <div class="al-card-cab">
                            <div class="al-card-cab-icono">
                                <i class="fas fa-award"></i>
                            </div>
                            <div>
                                <h2>{{ $prog->nombre }}</h2>
                                <p>Formación Inicial Docente · Nivel: {{ $prog->nivel }}</p>
                            </div>
                            <div class="al-card-cab-extra">
                                <span class="al-badge is-info">{{ $prog->codigo }}</span>
                                <span class="al-badge is-muted">10 Semestres</span>
                            </div>
                        </div>
                        <div class="al-card-cuerpo">
                            <div class="al-stats mb-3" style="grid-template-columns: 1fr 1fr;">
                                <div class="al-stat py-2 px-3">
                                    <div class="al-stat-icono is-oro" style="width: 36px; height: 36px; font-size: .85rem;">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <div class="al-stat-valor" style="font-size: 1.15rem;">{{ $prog->cursos_count }}</div>
                                        <div class="al-stat-etiqueta">Cursos Registrados</div>
                                    </div>
                                </div>
                                <div class="al-stat py-2 px-3">
                                    <div class="al-stat-icono is-ok" style="width: 36px; height: 36px; font-size: .85rem;">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <div class="al-stat-valor" style="font-size: 1.15rem;">{{ $prog->estudiantes_count }}</div>
                                        <div class="al-stat-etiqueta">Estudiantes</div>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('rp.programas.show', $prog) }}" class="al-btn al-btn-borde w-100 justify-content-center">
                                <i class="fas fa-layer-group"></i> Ver Malla Curricular (I al X)
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
