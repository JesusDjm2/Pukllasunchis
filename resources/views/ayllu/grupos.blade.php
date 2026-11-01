@extends('ayllu.layout')

@section('ayllu_contenido')
{{-- Encabezado de Comunidades y Grupos Interculturales --}}
<div class="ayllu-card p-4" style="border-top: 4px solid #c78b3a;">
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 0.75rem;">
        <div>
            <span class="ayllu-badge ayllu-badge-comunitario mb-2">
                <i class="fas fa-users mr-1"></i> Ayllus y Círculos de Estudio
            </span>
            <h2 class="h4 font-weight-bold mb-1" style="color: #161c22;">
                Comunidades y Grupos Interculturales
            </h2>
            <p class="small text-muted mb-0">
                Espacios de aprendizaje colaborativo, talleres (tinkuy), círculos de quechua, danza, música y acompañamiento entre ciclos.
            </p>
        </div>
        <button class="btn btn-sm font-weight-bold text-white px-3 py-2" type="button" data-toggle="collapse" data-target="#crearGrupoCollapse" style="background: #9e3d22; border-radius: 999px;">
            <i class="fas fa-plus-circle mr-1"></i> Crear nueva comunidad
        </button>
    </div>

    {{-- Formulario colapsable para crear nueva comunidad --}}
    <div class="collapse mt-3" id="crearGrupoCollapse">
        <div class="p-3 rounded" style="background: #f5f2ed; border: 1px solid #e6e0d6;">
            <h3 class="h6 font-weight-bold mb-2" style="color: #161c22;">Propuesta de nueva comunidad Ayllu</h3>
            <form action="{{ route('ayllu.grupos.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-5 mb-2">
                        <label class="small font-weight-bold mb-1">Nombre de la comunidad *</label>
                        <input type="text" name="nombre" class="form-control form-control-sm" required maxlength="140" placeholder="Ej. Círculo de Lectura Pedagógica EIB">
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label class="small font-weight-bold mb-1">Nombre en Quechua / Originario (opcional)</label>
                        <input type="text" name="nombre_quechua" class="form-control form-control-sm" maxlength="140" placeholder="Ej. Kuska Ñawinchasun">
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label class="small font-weight-bold mb-1">Tipo *</label>
                        <select name="tipo" class="form-control form-control-sm" required>
                            <option value="intercultural">Intercultural / Lenguas</option>
                            <option value="circulo_estudio">Círculo de Estudio</option>
                            <option value="taller">Taller / Expresión</option>
                            <option value="investigacion">Semillero / Investigación</option>
                            <option value="tutoria">Comunidad de Tutoría</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label class="small font-weight-bold mb-1">Propósito comunitario *</label>
                    <textarea name="descripcion" rows="2" class="form-control form-control-sm" required placeholder="Describe qué aprenderán y compartirán en esta comunidad..."></textarea>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-sm font-weight-bold text-white px-4" style="background: #9e3d22; border-radius: 999px;">
                        Abrir comunidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Selector de Comunidades Activas --}}
<div class="row mb-3">
    @foreach($grupos as $g)
        @php
            $esActivo = $grupoActual && $grupoActual->id === $g->id;
            $soyMiembro = in_array($g->id, $misGruposIds ?? [], true);
        @endphp
        <div class="col-md-6 mb-2">
            <div class="ayllu-card p-3 mb-0 h-100 d-flex flex-column justify-content-between" style="{{ $esActivo ? 'border: 2px solid #9e3d22; background: #fffcf9;' : '' }}">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <a href="{{ route('ayllu.grupos', ['grupo' => $g->id]) }}" class="font-weight-bold" style="color: #161c22; font-size: 0.94rem;">
                            <i class="{{ $g->icono ?? 'fas fa-users' }} mr-1" style="color: {{ $g->color ?? '#9e3d22' }};"></i>
                            {{ $g->nombre }}
                        </a>
                        @if($g->es_oficial)
                            <span class="ayllu-badge ayllu-badge-comunitario">Oficial</span>
                        @endif
                    </div>
                    @if($g->nombre_quechua)
                        <div class="small font-italic mb-1" style="color: #7c2d19; font-size: 0.78rem;">
                            «{{ $g->nombre_quechua }}»
                        </div>
                    @endif
                    <p class="small text-muted mb-2" style="font-size: 0.8rem;">
                        {{ \Illuminate\Support\Str::limit($g->descripcion, 120) }}
                    </p>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color: #ece6dc !important;">
                    <span class="small text-muted" style="font-size: 0.74rem;">
                        <i class="fas fa-user-friends mr-1"></i> {{ $g->miembros_count ?? 0 }} integrantes · {{ $g->publicaciones_count ?? 0 }} aportes
                    </span>
                    <div class="d-flex align-items-center" style="gap: 0.35rem;">
                        <a href="{{ route('ayllu.grupos', ['grupo' => $g->id]) }}" class="btn btn-sm py-0 px-2 {{ $esActivo ? 'btn-dark' : 'btn-outline-secondary' }}" style="font-size: 0.74rem; border-radius: 999px;">
                            Ver muro
                        </a>
                        <form action="{{ route('ayllu.grupos.miembro', $g->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm py-0 px-2 {{ $soyMiembro ? 'btn-outline-danger' : 'btn-outline-primary' }}" style="font-size: 0.74rem; border-radius: 999px;">
                                {{ $soyMiembro ? 'Salir' : 'Unirme' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Muro y Compositor de la Comunidad Seleccionada --}}
@if($grupoActual)
    <div class="ayllu-card p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
            <div>
                <h3 class="h6 font-weight-bold mb-0" style="color: #161c22;">
                    Muro de la comunidad: {{ $grupoActual->nombre }}
                </h3>
                <span class="small text-muted">
                    Todos los estudiantes y docentes pueden compartir saberes, preguntas y recursos en esta comunidad.
                </span>
            </div>
            <a href="{{ route('ayllu.chat', ['tipo' => 'grupo', 'id' => $grupoActual->id]) }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 999px; font-size: 0.76rem;">
                <i class="fas fa-comments mr-1"></i> Abrir chat de {{ $grupoActual->nombre }}
            </a>
        </div>

        <form action="{{ route('ayllu.publicaciones.store') }}" method="POST" class="mt-2">
            @csrf
            <input type="hidden" name="ambito" value="grupo">
            <input type="hidden" name="ayllu_grupo_id" value="{{ $grupoActual->id }}">

            <div class="form-row">
                <div class="col-md-4 mb-2">
                    <select name="tipo" class="form-control form-control-sm" required>
                        <option value="dialogo_saber">Diálogo de saberes / Reflexión</option>
                        <option value="recurso">Recurso o material compartido</option>
                        <option value="pregunta">Pregunta a la comunidad</option>
                        <option value="reconocimiento">Reconocimiento comunitario</option>
                    </select>
                </div>
                <div class="col-md-8 mb-2">
                    <input type="text" name="titulo" class="form-control form-control-sm" placeholder="Título de tu aporte (opcional)" maxlength="180">
                </div>
            </div>
            <div class="form-group mb-2">
                <textarea name="contenido" rows="2" class="form-control form-control-sm" required placeholder="Comparte una experiencia, saber o recurso con {{ $grupoActual->nombre }}..."></textarea>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 0.5rem;">
                <input type="url" name="enlace_externo" class="form-control form-control-sm" style="max-width: 340px;" placeholder="Enlace de Drive, video o lectura (opcional)">
                <button type="submit" class="btn btn-sm font-weight-bold text-white px-4" style="background: #9e3d22; border-radius: 999px;">
                    <i class="fas fa-paper-plane mr-1"></i> Publicar en comunidad
                </button>
            </div>
        </form>
    </div>

    @forelse($publicacionesGrupo as $publicacion)
        @include('ayllu.partials.publicacion_card', [
            'publicacion' => $publicacion,
            'catalogoReacciones' => $catalogoReacciones,
            'puedeModerar' => $puedeModerar,
            'user' => $user,
        ])
    @empty
        <div class="ayllu-card p-4 text-center">
            <p class="small text-muted mb-0">
                Esta comunidad aún no tiene publicaciones. ¡Inicia el diálogo compartiendo un saber o pregunta!
            </p>
        </div>
    @endforelse
@endif
@endsection
