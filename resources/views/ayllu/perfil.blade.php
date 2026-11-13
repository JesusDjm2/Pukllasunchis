@extends('ayllu.layout')

@section('ayllu_contenido')
@if($esMiPerfil && auth()->user()->condicion === 'Egresado')
<div class="ayllu-card p-3"><a class="btn btn-primary" href="{{ route('seguimiento.mi-espacio') }}">Mi espacio · Actualizar perfil, trayectoria y territorio</a></div>
@endif
{{-- Encabezado del Perfil Intercultural EIB --}}
<div class="ayllu-card p-4" style="border-top: 4px solid #9e3d22;">
    <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 1rem;">
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <div class="ayllu-post-avatar" style="width: 64px; height: 64px; font-size: 1.35rem;">
                {{ mb_strtoupper(mb_substr($perfilUser->name ?? 'A', 0, 1)) }}
            </div>
            <div>
                <span class="ayllu-badge ayllu-badge-comunitario mb-1">
                    <i class="fas fa-id-badge mr-1"></i> Identidad Pedagógica e Intercultural
                </span>
                <h2 class="h4 font-weight-bold mb-1" style="color: #161c22;">
                    {{ $perfilUser->name }} {{ $perfilUser->apellidos ?? '' }}
                </h2>
                <div class="small font-weight-bold" style="color: #9e3d22;">
                    «{{ $perfilAyllu->saludo_intercultural ?: 'Allillanchu · Kuska yachasunchis' }}»
                </div>
                <div class="small text-muted mt-1">
                    <i class="fas fa-language mr-1"></i>
                    Lenguas en la ficha: <strong>{{ $lengua1 }}</strong>
                    @if($lengua2)
                        · Segunda lengua: <strong>{{ $lengua2 }}</strong>
                    @endif
                    @if($etiquetaPrograma)
                        · {{ $etiquetaPrograma }} ({{ $etiquetaCiclo }})
                    @endif
                </div>
            </div>
        </div>

        @if($esMiPerfil)
            <button class="btn btn-sm btn-outline-primary font-weight-bold" type="button" data-toggle="collapse" data-target="#editarPerfilCollapse" style="border-radius: 999px;">
                <i class="fas fa-pen mr-1"></i> Editar mi perfil intercultural
            </button>
        @endif
    </div>

    @if($perfilAyllu->biografia)
        <div class="mt-3 p-3 rounded small" style="background: #f5f2ed; color: #2b353e; white-space: pre-line;">
            {{ $perfilAyllu->biografia }}
        </div>
    @endif

    @if(!empty($perfilAyllu->saberes_intereses))
        <div class="mt-3 d-flex flex-wrap align-items-center" style="gap: 0.4rem;">
            <span class="small font-weight-bold text-muted mr-1">Saberes e intereses:</span>
            @foreach($perfilAyllu->saberes_intereses as $saber)
                <span class="ayllu-badge ayllu-badge-taqe">{{ $saber }}</span>
            @endforeach
        </div>
    @endif

    @if($esMiPerfil)
        <div class="collapse mt-3" id="editarPerfilCollapse">
            <div class="p-3 rounded" style="background: #f5f2ed; border: 1px solid #e6e0d6;">
                <h3 class="h6 font-weight-bold mb-2" style="color: #161c22;">Actualizar Perfil Intercultural</h3>
                <form action="{{ route('ayllu.perfil.update') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-7 mb-2">
                            <label class="small font-weight-bold mb-1">Regala a la comunidad una frase o saludo para tu perfil (opcional)</label>
                            <input type="text" name="saludo_intercultural" class="form-control form-control-sm" maxlength="180" value="{{ $perfilAyllu->saludo_intercultural }}">
                        </div>
                        <div class="form-group col-md-5 mb-2">
                            <label class="small font-weight-bold mb-1">Preferencia lingüística en la interfaz</label>
                            <select name="lengua_preferida" class="form-control form-control-sm">
                                <option value="castellano_quechua" {{ $perfilAyllu->lengua_preferida === 'castellano_quechua' ? 'selected' : '' }}>Bilingüe Castellano · Quechua</option>
                                <option value="quechua_castellano" {{ $perfilAyllu->lengua_preferida === 'quechua_castellano' ? 'selected' : '' }}>Quechua · Castellano</option>
                                <option value="castellano" {{ $perfilAyllu->lengua_preferida === 'castellano' ? 'selected' : '' }}>Castellano</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1">Presentación pedagógica y comunitaria</label>
                        <textarea name="biografia" rows="3" class="form-control form-control-sm" placeholder="Comparte tu vocación docente, tu comunidad de origen, tus intereses de investigación o práctica EIB...">{{ $perfilAyllu->biografia }}</textarea>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1">Saberes e intereses (separados por comas)</label>
                        <input type="text" name="saberes_texto" class="form-control form-control-sm" value="{{ is_array($perfilAyllu->saberes_intereses) ? implode(', ', $perfilAyllu->saberes_intereses) : '' }}" placeholder="Ej. Educación Intercultural Bilingüe, Quechua Chanka/Collao, Agroecología escolar">
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="mostrarPortafolioCheck" name="mostrar_portafolio_comunidad" value="1" {{ $perfilAyllu->mostrar_portafolio_comunidad ? 'checked' : '' }}>
                        <label class="custom-control-label small" for="mostrarPortafolioCheck">
                            Permitir que docentes y compañeros de la comunidad vean las evidencias que yo seleccione en mi Portafolio Docente EIB
                        </label>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-sm font-weight-bold text-white px-4" style="background: #9e3d22; border-radius: 999px;">
                            Guardar perfil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

{{-- Portafolio Docente EIB visible en el Perfil --}}
<div class="ayllu-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="h6 font-weight-bold mb-0" style="color: #161c22;">
                <i class="fas fa-book-reader text-success mr-1"></i>
                Portafolio Docente EIB de {{ $perfilUser->name }}
            </h3>
            <span class="small text-muted">
                Producciones pedagógicas y reflexiones seleccionadas a lo largo de la formación.
            </span>
        </div>
        @if($esMiPerfil)
            <a href="{{ route('ayllu.proyecto') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 999px; font-size: 0.76rem;">
                Curar evidencias
            </a>
        @endif
    </div>

    @if($esMiPerfil || $perfilAyllu->mostrar_portafolio_comunidad)
        @forelse($portafolioItems as $item)
            <div class="p-3 rounded mb-3" style="border: 1px solid #d5e6dc; background: #fbfdfc;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                    <h4 class="h6 font-weight-bold mb-0" style="color: #161c22;">
                        {{ $item->titulo }}
                    </h4>
                    <span class="ayllu-badge ayllu-badge-formativo">
                        {{ $item->curso?->nombre ?? 'Curso EIB' }}
                    </span>
                </div>
                <p class="small text-muted mb-2">{{ $item->descripcion_reflexion }}</p>
                @if($item->reflexion_portafolio)
                    <div class="p-2 rounded small mb-2" style="background: #eef6f1; color: #205239;">
                        <strong>Reflexión docente:</strong> {{ $item->reflexion_portafolio }}
                    </div>
                @endif
                @if($item->enlace_evidencia)
                    <a href="{{ $item->enlace_evidencia }}" target="_blank" rel="noopener" class="small font-weight-bold" style="color: #9e3d22;">
                        <i class="fas fa-external-link-alt mr-1"></i> Ver evidencia adjunta
                    </a>
                @endif
            </div>
        @empty
            <div class="p-3 rounded text-center" style="background: #f5f2ed;">
                <p class="small text-muted mb-0">
                    Aún no hay evidencias curadas en este Portafolio Docente EIB.
                </p>
            </div>
        @endforelse
    @else
        <div class="p-3 rounded text-center" style="background: #f5f2ed;">
            <p class="small text-muted mb-0">
                El titular de este perfil mantiene su portafolio en modo privado.
            </p>
        </div>
    @endif
</div>
@endsection
