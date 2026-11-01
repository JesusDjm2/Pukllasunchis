@extends('ayllu.layout')

@section('ayllu_contenido')
{{-- Encabezado del Calendario Vivo --}}
<div class="ayllu-card p-4" style="border-top: 4px solid #c78b3a;">
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 0.75rem;">
        <div>
            <span class="ayllu-badge ayllu-badge-comunitario mb-2">
                <i class="fas fa-calendar-alt mr-1"></i> Calendario Vivo Académico y Comunitario
            </span>
            <h2 class="h4 font-weight-bold mb-1" style="color: #161c22;">
                Hitos del Período, Minkarikuy y Actividades EIB
            </h2>
            <p class="small text-muted mb-0">
                Sincronización automática con el período académico vigente ({{ $periodoActual->nombre ?? 'Vigente' }}), Minkarikuy institucional y actividades de cursos.
            </p>
        </div>

        @if($puedePublicarMuroGeneral)
            <button class="btn btn-sm font-weight-bold text-white px-3 py-2" type="button" data-toggle="collapse" data-target="#nuevoEventoCollapse" style="background: #9e3d22; border-radius: 999px;">
                <i class="fas fa-calendar-plus mr-1"></i> Programar actividad
            </button>
        @endif
    </div>

    @if($puedePublicarMuroGeneral)
        <div class="collapse mt-3" id="nuevoEventoCollapse">
            <div class="p-3 rounded" style="background: #f5f2ed; border: 1px solid #e6e0d6;">
                <h3 class="h6 font-weight-bold mb-2" style="color: #161c22;">Agregar actividad o hito al Calendario Vivo</h3>
                <form action="{{ route('ayllu.calendario.store') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-5 mb-2">
                            <label class="small font-weight-bold mb-1">Título de la actividad *</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" required maxlength="180" placeholder="Ej. Socialización de avances del Proyecto Integrador">
                        </div>
                        <div class="form-group col-md-3 mb-2">
                            <label class="small font-weight-bold mb-1">Tipo *</label>
                            <select name="tipo" class="form-control form-control-sm" required>
                                <option value="comunitario">Comunitario / Minkarikuy</option>
                                <option value="academico">Académico</option>
                                <option value="entrega_formativa">Hito de Entrega Formativa</option>
                                <option value="taqe">Proyecto Integrador / Taq'e</option>
                                <option value="tutoria">Tutoría y Bienestar</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2 mb-2">
                            <label class="small font-weight-bold mb-1">Fecha *</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm" required value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="form-group col-md-2 mb-2">
                            <label class="small font-weight-bold mb-1">Hora</label>
                            <input type="text" name="hora" class="form-control form-control-sm" placeholder="Ej. 10:30 am">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5 mb-2">
                            <label class="small font-weight-bold mb-1">Curso asociado (opcional)</label>
                            <select name="curso_id" class="form-control form-control-sm">
                                <option value="">Actividad institucional / transversal</option>
                                @foreach($cursos as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-7 mb-2">
                            <label class="small font-weight-bold mb-1">Descripción o lugar</label>
                            <input type="text" name="descripcion" class="form-control form-control-sm" placeholder="Ej. Patio central EESP Pukllasunchis / Aula virtual">
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-sm font-weight-bold text-white px-4" style="background: #9e3d22; border-radius: 999px;">
                            Guardar en el calendario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

{{-- Línea de Tiempo de Hitos --}}
<div class="ayllu-card p-4">
    <h3 class="h6 font-weight-bold mb-3" style="color: #161c22;">
        <i class="fas fa-stream text-warning mr-1"></i> Agenda Cronológica de Hitos ({{ $hitos->count() }})
    </h3>

    @forelse($hitos as $hito)
        @php
            $badgeMap = [
                'academico' => ['cls' => 'ayllu-badge-taqe', 'icon' => 'fas fa-university', 'label' => 'Académico Oficial'],
                'comunitario' => ['cls' => 'ayllu-badge-comunitario', 'icon' => 'fas fa-hands-helping', 'label' => 'Vida Comunitaria'],
                'entrega_formativa' => ['cls' => 'ayllu-badge-formativo', 'icon' => 'fas fa-seedling', 'label' => 'Acompañamiento Formativo'],
                'taqe' => ['cls' => 'ayllu-badge-taqe', 'icon' => 'fas fa-project-diagram', 'label' => 'Proyecto Integrador / Taq\'e'],
                'tutoria' => ['cls' => 'ayllu-badge-formativo', 'icon' => 'fas fa-heart', 'label' => 'Tutoría y Bienestar'],
            ];
            $meta = $badgeMap[$hito['tipo']] ?? $badgeMap['comunitario'];
        @endphp
        <div class="d-flex align-items-start p-3 rounded mb-2" style="background: #fbf9f6; border: 1px solid #e6e0d6; gap: 0.9rem;">
            <div class="text-center px-3 py-2 rounded" style="background: #fff; border: 1px solid #dfd7cb; min-width: 92px;">
                <div class="small font-weight-bold text-uppercase" style="color: #9e3d22; font-size: 0.72rem;">
                    {{ \Carbon\Carbon::parse($hito['fecha'])->translatedFormat('M Y') }}
                </div>
                <div class="h5 font-weight-bold mb-0" style="color: #161c22;">
                    {{ \Carbon\Carbon::parse($hito['fecha'])->format('d') }}
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                    <h4 class="h6 font-weight-bold mb-0" style="color: #161c22;">
                        {{ $hito['titulo'] }}
                    </h4>
                    <span class="ayllu-badge {{ $meta['cls'] }}">
                        <i class="{{ $meta['icon'] }} mr-1"></i> {{ $meta['label'] }}
                    </span>
                </div>
                @if(!empty($hito['detalle']))
                    <p class="small text-muted mb-0">{{ $hito['detalle'] }}</p>
                @endif
            </div>
        </div>
    @empty
        <div class="p-4 text-center rounded" style="background: #f5f2ed;">
            <p class="small text-muted mb-0">No hay hitos registrados para este período.</p>
        </div>
    @endforelse
</div>
@endsection
