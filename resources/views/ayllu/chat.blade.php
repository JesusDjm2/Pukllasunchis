@extends('ayllu.layout')

@section('ayllu_contenido')
@php
    $canalNombreActual = 'Selecciona un canal';
    if ($canalTipo === 'curso') {
        $cursoSel = $cursos->firstWhere('id', $canalId);
        $canalNombreActual = $cursoSel ? ('Curso: ' . $cursoSel->nombre) : 'Canal de Curso';
    } elseif ($canalTipo === 'grupo') {
        $grupoSel = $grupos->firstWhere('id', $canalId);
        $canalNombreActual = $grupoSel ? ('Comunidad: ' . $grupoSel->nombre) : 'Canal de Comunidad';
    }
@endphp

<div class="ayllu-card p-4" style="border-top: 4px solid #33445a;">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 0.75rem;">
        <div>
            <span class="ayllu-badge ayllu-badge-taqe mb-1">
                <i class="fas fa-comments mr-1"></i> Rimanakuy · Chat Académico y Comunitario
            </span>
            <h2 class="h5 font-weight-bold mb-0" style="color: #161c22;">
                {{ $canalNombreActual }}
            </h2>
        </div>
        <span class="small text-muted">
            Coordinación rápida entre estudiantes y docentes por curso o comunidad Ayllu.
        </span>
    </div>

    {{-- Selector rápido de canales (Cursos y Comunidades) --}}
    <div class="mb-3 p-3 rounded" style="background: #f5f2ed; border: 1px solid #e6e0d6;">
        <div class="small font-weight-bold text-uppercase text-muted mb-2">Canales de mis cursos:</div>
        <div class="d-flex flex-wrap mb-2" style="gap: 0.4rem;">
            @forelse($cursos as $c)
                <a href="{{ route('ayllu.chat', ['tipo' => 'curso', 'id' => $c->id]) }}"
                   class="btn btn-sm py-1 px-3 {{ ($canalTipo === 'curso' && $canalId === $c->id) ? 'btn-dark font-weight-bold' : 'btn-outline-secondary bg-white' }}"
                   style="border-radius: 999px; font-size: 0.76rem;">
                    <i class="fas fa-book-open mr-1"></i> {{ \Illuminate\Support\Str::limit($c->nombre, 28) }}
                </a>
            @empty
                <span class="small text-muted">Sin cursos activos.</span>
            @endforelse
        </div>

        <div class="small font-weight-bold text-uppercase text-muted mb-2 mt-2">Canales de comunidades Ayllu:</div>
        <div class="d-flex flex-wrap" style="gap: 0.4rem;">
            @foreach($grupos as $g)
                <a href="{{ route('ayllu.chat', ['tipo' => 'grupo', 'id' => $g->id]) }}"
                   class="btn btn-sm py-1 px-3 {{ ($canalTipo === 'grupo' && $canalId === $g->id) ? 'btn-primary font-weight-bold' : 'btn-outline-secondary bg-white' }}"
                   style="border-radius: 999px; font-size: 0.76rem;">
                    <i class="fas fa-users mr-1"></i> {{ $g->nombre }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Historial de Mensajes del Canal --}}
    <div class="p-3 rounded mb-3" style="background: #fff; border: 1px solid #e6e0d6; min-height: 280px; max-height: 420px; overflow-y: auto;">
        @forelse($mensajes as $msg)
            @php
                $esMio = $msg->emisor_user_id === $user->id;
            @endphp
            <div class="d-flex mb-2 {{ $esMio ? 'justify-content-end' : 'justify-content-start' }}">
                <div class="p-2 px-3 rounded" style="max-width: 78%; background: {{ $esMio ? '#f5e8e3' : '#f5f2ed' }}; border: 1px solid {{ $esMio ? '#e2c2b8' : '#e6e0d6' }};">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="gap: 0.75rem;">
                        <strong class="small" style="color: #161c22; font-size: 0.76rem;">
                            {{ $msg->emisor?->name ?? 'Integrante' }}
                        </strong>
                        <span class="text-muted" style="font-size: 0.68rem;">
                            {{ $msg->created_at?->format('d/m H:i') }}
                        </span>
                    </div>
                    <div class="small" style="color: #1f2830; white-space: pre-line;">{{ $msg->mensaje }}</div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-comment-dots fa-2x mb-2" style="color: #c78b3a;"></i>
                <p class="small mb-0">
                    Aún no hay mensajes en este canal. Escribe el primer mensaje para coordinar con tu grupo o curso.
                </p>
            </div>
        @endforelse
    </div>

    {{-- Formulario de envío --}}
    @if($canalId > 0)
        <form action="{{ route('ayllu.chat.store') }}" method="POST">
            @csrf
            <input type="hidden" name="canal_tipo" value="{{ $canalTipo }}">
            <input type="hidden" name="canal_id" value="{{ $canalId }}">
            <div class="input-group">
                <input type="text" name="mensaje" class="form-control" required maxlength="1500" placeholder="Escribe un mensaje para {{ $canalNombreActual }}..." style="border-radius: 999px 0 0 999px;">
                <div class="input-group-append">
                    <button class="btn font-weight-bold text-white px-4" type="submit" style="background: #9e3d22; border-radius: 0 999px 999px 0;">
                        <i class="fas fa-paper-plane mr-1"></i> Enviar
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
