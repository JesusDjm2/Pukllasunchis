@extends('layouts.alumno')
@section('titulo', 'Tutoría y sugerencias')
@section('contenido')
    @include('partials.alumno-page-header', [
        'title' => 'Tutoría y sugerencias',
        'subtitle' => 'Pide una atención personalizada a tu tutor(a) o déjale una sugerencia. Es lo mismo que el código QR del aula, pero sin salir de tu intranet.',
    ])

    <style>
        .at-tutor {
            display: flex; align-items: center; gap: .85rem; flex-wrap: wrap;
            background: var(--alumno-surface); border: 1px solid var(--alumno-border);
            border-radius: var(--alumno-radius); box-shadow: var(--alumno-shadow);
            padding: .9rem 1.2rem; margin-bottom: 1.5rem;
        }
        .at-tutor-icono {
            width: 2.6rem; height: 2.6rem; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(205, 146, 68, .14); color: var(--alumno-accent); font-size: 1.05rem;
        }
        .at-tutor-k { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--alumno-muted); }
        .at-tutor-v { font-weight: 700; color: var(--alumno-teal-dark); }

        .at-card {
            background: var(--alumno-surface); border: 1px solid var(--alumno-border);
            border-radius: var(--alumno-radius); box-shadow: var(--alumno-shadow);
            padding: 1.35rem 1.5rem; height: 100%;
            border-top: 4px solid var(--at-color);
            transition: box-shadow .2s ease;
        }
        .at-card.is-destacada { box-shadow: 0 0 0 3px rgba(205, 146, 68, .35), var(--alumno-shadow-hover); }
        .at-card-tutoria { --at-color: var(--alumno-teal); }
        .at-card-sugerencia { --at-color: var(--alumno-accent); }
        .at-card-cab { display: flex; align-items: center; gap: .75rem; margin-bottom: .35rem; }
        .at-card-icono {
            width: 2.4rem; height: 2.4rem; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: var(--at-color); color: #fff;
        }
        .at-card h2 { font-size: 1.1rem; font-weight: 700; color: var(--alumno-teal-dark); margin: 0; }
        .at-card-desc { font-size: .88rem; color: var(--alumno-muted); margin-bottom: 1rem; }
        .at-label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--alumno-muted); }
        .at-card textarea { border-radius: var(--alumno-radius-sm); resize: vertical; }

            display: block; margin: 0; cursor: pointer; text-align: center;
            border: 1.5px solid var(--alumno-border); border-radius: var(--alumno-radius-sm);
            padding: .55rem .4rem; transition: border-color .15s ease, background-color .15s ease;
        }

        .at-btn {
            border-radius: var(--alumno-radius-sm); font-weight: 700; padding: .55rem 1.2rem;
            background: var(--at-color); border-color: var(--at-color); color: #fff;
        }
        .at-btn:hover { filter: brightness(.94); color: #fff; }

        .at-historial-item {
            display: flex; gap: .85rem; align-items: flex-start;
            padding: .85rem 0; border-bottom: 1px solid var(--alumno-border);
        }
        .at-historial-item:last-child { border-bottom: 0; }
        .at-tipo { font-size: .68rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; padding: .2rem .55rem; border-radius: 999px; white-space: nowrap; }
        .at-tipo.tutoria { background: rgba(51, 68, 90, .1); color: var(--alumno-teal); }
        .at-tipo.sugerencia { background: rgba(205, 146, 68, .15); color: #9a6a2a; }
        .at-estado { font-size: .72rem; font-weight: 700; padding: .2rem .6rem; border-radius: 999px; white-space: nowrap; }
        .at-estado.pendiente { background: #fff4d6; color: #9a6700; }
        .at-estado.listo { background: #e6f4ec; color: #0b7a43; }
        .at-historial-texto { font-size: .9rem; color: var(--alumno-text); white-space: pre-line; word-break: break-word; }
        .at-historial-meta { font-size: .76rem; color: var(--alumno-muted); }
    </style>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (! $alumno)
        <div class="alumno-shell text-center py-5">
            <i class="fas fa-user-clock fa-2x mb-3" style="color: var(--alumno-muted);"></i>
            <p class="font-weight-bold mb-1">Esta sección aún no está disponible para tu cuenta.</p>
            <p class="text-muted mb-0 small">Necesitas tener un ciclo asignado. Si crees que es un error, comunícate con Secretaría Académica.</p>
        </div>
    @else
        @php
            $tutores = $alumno->ciclo->tutores->map->nombreCorto()->filter()->join(', ');
            $tab = request('tab');
        @endphp

        <div class="at-tutor">
            <div class="at-tutor-icono"><i class="fas fa-user-tie"></i></div>
            <div>
                <div class="at-tutor-k">{{ \Illuminate\Support\Str::contains($tutores, ',') ? 'Tus tutores' : 'Tu tutor(a)' }}</div>
                <div class="at-tutor-v">{{ $tutores ?: 'Tu ciclo aún no tiene tutor(a) asignado — igual llegará a la coordinación' }}</div>
            </div>
            <div class="ml-md-auto text-md-right">
                <div class="at-tutor-k">Tu ciclo</div>
                <div class="at-tutor-v">{{ optional($alumno->ciclo->programa)->nombre }} · Ciclo {{ $alumno->ciclo->nombre }}</div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <div class="row">
            {{-- Tutoría individual --}}
            <div class="col-lg-6 mb-4">
                <form method="POST" action="{{ route('alumno.tutoria.store') }}" id="form-tutoria"
                    class="at-card at-card-tutoria {{ $tab === 'tutoria' ? 'is-destacada' : '' }}">
                    @csrf
                    <div class="at-card-cab">
                        <div class="at-card-icono"><i class="fas fa-hands-helping"></i></div>
                        <h2>Solicitar tutoría individual</h2>
                    </div>
                    <p class="at-card-desc">Tu tutor(a) lo recibe al instante y se comunicará contigo.</p>

                    @include('tutorias._conversar', [
                        'docentesElegibles' => $docentesElegibles,
                        'idForm' => 'tutAl',
                        'labelClass' => 'at-label',
                        'filasMotivo' => 4,
                    ])

                    <button type="submit" class="btn at-btn"><i class="fas fa-paper-plane mr-1"></i> Enviar solicitud</button>
                </form>
            </div>

            {{-- Buzón de sugerencias --}}
            <div class="col-lg-6 mb-4">
                <form method="POST" action="{{ route('alumno.sugerencia.store') }}" id="form-sugerencia"
                    class="at-card at-card-sugerencia {{ $tab === 'sugerencia' ? 'is-destacada' : '' }}">
                    @csrf
                    <div class="at-card-cab">
                        <div class="at-card-icono"><i class="fas fa-lightbulb"></i></div>
                        <h2>Buzón de sugerencias</h2>
                    </div>
                    <p class="at-card-desc">¿Una idea para mejorar las clases o un tema que te gustaría trabajar en tutoría? Compártelo con tu tutor(a).</p>

                    <div class="form-group">
                        <label for="mensaje" class="at-label">Tu sugerencia</label>
                        <textarea name="mensaje" id="mensaje" rows="8" class="form-control" required minlength="10" maxlength="3000"
                            placeholder="Ej.: Me gustaría que en tutoría habláramos sobre técnicas de estudio…">{{ old('mensaje') }}</textarea>
                    </div>

                    <button type="submit" class="btn at-btn"><i class="fas fa-paper-plane mr-1"></i> Enviar sugerencia</button>
                </form>
            </div>
        </div>

        {{-- Historial --}}
        @php
            $historial = $tutorias->map(fn ($t) => (object) [
                    'tipo' => 'tutoria', 'etiqueta' => 'Tutoría', 'texto' => $t->motivo ?: (\App\Models\Tutoria::OPCIONES[$t->urgencia][0] ?? ''), 'fecha' => $t->created_at,
                    'listo' => $t->estado === 'atendida', 'estado' => $t->estado === 'atendida' ? 'Atendida' : 'Pendiente',
                    'extra' => $t->urgencia_texto.($t->pideOtroDocente() && $t->docente_solicitado_nombre ? ': '.$t->docente_solicitado_nombre : ''),
                ])
                ->concat($sugerencias->map(fn ($s) => (object) [
                    'tipo' => 'sugerencia', 'etiqueta' => 'Sugerencia', 'texto' => $s->mensaje, 'fecha' => $s->created_at,
                    'listo' => $s->estado === 'revisada', 'estado' => $s->estado === 'revisada' ? 'Revisada' : 'Pendiente',
                    'extra' => null,
                ]))
                ->sortByDesc('fecha')->take(15);
        @endphp
        <div class="alumno-shell">
            <div class="alumno-section-label"><i class="fas fa-history mr-1"></i> Lo que has enviado</div>
            @forelse ($historial as $h)
                <div class="at-historial-item">
                    <span class="at-tipo {{ $h->tipo }}">{{ $h->etiqueta }}</span>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="at-historial-texto">{{ \Illuminate\Support\Str::limit($h->texto, 280) }}</div>
                        <div class="at-historial-meta mt-1">
                            {{ $h->fecha?->format('d/m/Y H:i') }}@if ($h->extra) · {{ $h->extra }}@endif
                        </div>
                    </div>
                    <span class="at-estado {{ $h->listo ? 'listo' : 'pendiente' }}">
                        <i class="fas {{ $h->listo ? 'fa-check' : 'fa-clock' }} mr-1"></i>{{ $h->estado }}
                    </span>
                </div>
            @empty
                <p class="text-muted mb-0 small">Aún no has enviado solicitudes ni sugerencias.</p>
            @endforelse
        </div>

        @if ($tab)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var el = document.getElementById('form-{{ $tab === 'sugerencia' ? 'sugerencia' : 'tutoria' }}');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            </script>
        @endif
    @endif
@endsection
