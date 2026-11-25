@extends($layout)
@section('titulo', 'Solicitud de Ampliación de PTI')
@section('contenido')
@include('alumnos.partials.ui')
@php
    use App\Models\Titulacion\Solicitud;
    $v = fn ($campo) => old($campo, $valores[$campo] ?? '');
    $estado = $ultima?->estado;
    $caducadoAmpliacion = $vigencia->debeReiniciarProceso();
    // Máximo $maximo ampliaciones por PTI: con una ya aprobada se puede pedir la siguiente; con todas, ya no.
    $limiteAlcanzado = $previas >= $maximo;
    $mostrarForm = $institucional && $ptiAprobado && ! $limiteAlcanzado && (! $caducadoAmpliacion || $estado === 'observada')
        && (! $ultima || in_array($estado, ['observada', 'atendida'], true));
    $fmt = fn ($d) => $d ? $d->format('d/m/Y') : '—';
@endphp

<div class="al-ui cr">
    <a href="{{ $volver }}#titulacion" class="cr-volver"><i class="fas fa-arrow-left fa-xs mr-1"></i>Volver a mi proceso de titulación</a>

    <section class="al-hero cr-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <span class="al-kicker">Proceso de titulación · Trabajo de Investigación</span>
                <h1>Solicitud de extensión de plazo de investigación</h1>
                <p class="al-hero-sub">Solicita la ampliación del plazo de tu Plan de Trabajo de Investigación (derecho de trámite: Cód. 11 · S/ 50.00; se puede solicitar como máximo 2 veces). La respuesta y la nueva R.D. llegarán a tu intranet y a tu correo institucional.</p>
            </div>
            @if ($ultima)
                <span class="al-badge {{ ['recibida' => 'is-info', 'atendida' => 'is-ok', 'observada' => 'is-warn'][$estado] }} cr-hero-estado">
                    <i class="fas {{ ['recibida' => 'fa-hourglass-half', 'atendida' => 'fa-check-circle', 'observada' => 'fa-exclamation-circle'][$estado] }}"></i>
                    {{ Solicitud::ESTADOS[$estado] ?? $estado }}
                </span>
            @endif
        </div>
    </section>

    @if (session('success'))
        <div class="al-aviso is-ok"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
    @endif
    @if (session('error'))
        <div class="al-aviso is-mal"><i class="fas fa-times-circle"></i><div>{{ session('error') }}</div></div>
    @endif

    @if (! $institucional)
        <div class="al-aviso is-mal">
            <i class="fas fa-user-lock"></i>
            <div>
                <strong>Este formulario es institucional.</strong> Solo se puede enviar con tu correo <strong>&#64;eesppukllasunchis.edu.pe</strong>,
                y tu cuenta usa <strong>{{ $user->email }}</strong>. Comunícate con Secretaría para actualizarlo.
            </div>
        </div>
    @elseif (! $ptiAprobado)
        <div class="al-aviso is-warn">
            <i class="fas fa-lock"></i>
            <div><strong>Todavía no puedes solicitar una ampliación.</strong> La ampliación de plazo se solicita cuando tu Plan de Trabajo de Investigación (PTI) ya cuenta con R.D. de aprobación emitida y se encuentra próximo a vencer o vencido.</div>
        </div>
    @elseif ($caducadoAmpliacion && (! $ultima || $estado !== 'observada'))
        <div class="al-aviso is-mal">
            <i class="fas fa-calendar-times"></i>
            <div>
                <strong>El plazo para solicitar ampliación ha vencido.</strong><br>
                Tu Plan de Trabajo de Investigación venció el <strong>{{ $fmt($vigencia->vencimiento) }}</strong>. Reglamentariamente, solo se permite solicitar ampliación hasta 2 días después de la fecha de vencimiento (el plazo improrrogable finalizó el <strong>{{ $fmt($vigencia->limiteSolicitarAmpliacion()) }}</strong>).<br>
                Al haber expirado este plazo, debes <strong>iniciar nuevamente el proceso de titulación desde cero</strong> a través de una nueva solicitud de PTI. Tus RDs anteriores quedan archivadas como antecedentes en tu historial.<br>
                <div class="mt-2 pt-1">
                    <a href="{{ route('mi-titulacion.solicitud-pti') }}" class="btn btn-sm btn-danger font-weight-bold">
                        <i class="fas fa-redo-alt mr-1"></i>Iniciar nueva solicitud de PTI
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Estado actual cuando ya envió la solicitud y está en revisión o atendida --}}
    @if ($ultima && $estado !== 'observada')
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white font-weight-bold">
                <i class="fas {{ $estado === 'atendida' ? 'fa-check-circle text-success' : 'fa-clock text-primary' }} mr-2"></i>
                {{ $estado === 'atendida' ? 'Solicitud de ampliación aprobada' : 'Tu solicitud de ampliación está en revisión' }}
            </div>
            <div class="card-body">
                @if ($estado === 'recibida')
                    <p class="text-muted mb-3">
                        Enviada el <strong>{{ $fmt($ultima->created_at) }}</strong>. Secretaría General está evaluando tu justificativo y comprobante de pago. Cuando se emita la R.D. de ampliación, se actualizará tu fecha de vigencia.
                    </p>
                @else
                    <p class="text-success font-weight-bold mb-3">
                        <i class="fas fa-check-circle mr-1"></i>Tu ampliación fue aprobada y registrada en el sistema.
                        @if ($ultima->datos['numero_rd'] ?? null) R.D. N° {{ $ultima->datos['numero_rd'] }}. @endif
                    </p>
                @endif

                <dl class="row mb-0 small">
                    @if ($ultima->datos['vez'] ?? null)
                        <dt class="col-sm-3 text-muted">Solicitud:</dt>
                        <dd class="col-sm-9 font-weight-bold">{{ (int) $ultima->datos['vez'] }}.ª vez</dd>
                    @endif
                    @if ($ultima->datos['meses_solicitados'] ?? null)
                        <dt class="col-sm-3 text-muted">Tiempo solicitado:</dt>
                        <dd class="col-sm-9 font-weight-bold">{{ $ultima->datos['meses_solicitados'] }} meses</dd>
                    @endif
                    <dt class="col-sm-3 text-muted">Investigación:</dt>
                    <dd class="col-sm-9">“{{ $ultima->titulo }}”@if ($ultima->datos['fecha_aprobacion_pti'] ?? null) · PTI aprobado el {{ \Carbon\Carbon::parse($ultima->datos['fecha_aprobacion_pti'])->format('d/m/Y') }}@endif</dd>

                    <dt class="col-sm-3 text-muted">Razones:</dt>
                    <dd class="col-sm-9">{{ $ultima->datos['motivo'] ?? '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Comprobante:</dt>
                    <dd class="col-sm-9">
                        @php $comprobantes = Solicitud::enlaces($ultima->comprobante, fn ($n) => route('mi-titulacion.ampliacion-pti.archivo', [$ultima->id, 'comprobante', $n])); @endphp
                        @foreach ($comprobantes as $e)
                            <a href="{{ $e }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary py-0">
                                <i class="fas fa-file-invoice-dollar mr-1"></i>Ver comprobante de pago
                            </a>
                        @endforeach
                    </dd>

                    @if ($ultima->archivo)
                        <dt class="col-sm-3 text-muted">RD de aprobación del PTI:</dt>
                        <dd class="col-sm-9">
                            @php $docs = Solicitud::enlaces($ultima->archivo, fn ($n) => route('mi-titulacion.ampliacion-pti.archivo', [$ultima->id, 'archivo', $n])); @endphp
                            @foreach ($docs as $e)
                                <a href="{{ $e }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary py-0">
                                    <i class="fas fa-paperclip mr-1"></i>Ver RD
                                </a>
                            @endforeach
                        </dd>
                    @endif
                </dl>
            </div>
        </div>
    @endif

    @if ($ptiAprobado && $limiteAlcanzado)
        <div class="al-aviso is-mal mb-4">
            <i class="fas fa-ban"></i>
            <div><strong>Ya se aprobaron las {{ $maximo }} ampliaciones permitidas.</strong> La ampliación de plazo del PTI solo puede solicitarse hasta {{ $maximo }} veces; no es posible enviar otra solicitud.</div>
        </div>
    @endif

    {{-- Si fue observada, mostrar el motivo para subsanar --}}
    @if ($ultima && $estado === 'observada')
        <div class="al-aviso is-warn mb-4">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Tu solicitud fue observada por Secretaría:</strong>
                <div class="mt-1 font-weight-bold">{{ $ultima->observacion }}</div>
                <div class="small mt-1 text-muted">Por favor realiza las correcciones solicitadas a continuación y vuelve a enviar el formulario.</div>
            </div>
        </div>
    @endif

    {{-- Formulario para enviar o subsanar --}}
    @if ($mostrarForm)
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">
                <i class="fas fa-edit mr-2 text-primary"></i>{{ $estado === 'atendida' ? 'Solicitar la '.($previas + 1).'.ª ampliación de plazo' : 'Datos para la ampliación de plazo' }}
            </div>
            <div class="card-body">
                {{-- Encabezado del formulario oficial «Solicitud de extensión de plazo de investigación» --}}
                <div class="al-aviso is-info mb-3">
                    <i class="fas fa-info-circle"></i>
                    <div><strong>Nota:</strong> la justificación que se presente en esta solicitud será evaluada por la Comisión de Investigación; de ser considerada válida, se emitirá la RD correspondiente.</div>
                </div>
                <p class="mb-3 small">
                    <strong>Señor:</strong> Richard Suárez Sánchez<br>
                    Director de la Escuela de Educación Superior Pedagógica Pukllasunchis.
                </p>

                {{-- Grupo, programa y vigencia: el sistema ya los conoce, no se vuelven a escribir --}}
                <div class="bg-light p-3 rounded mb-4 border">
                    <h6 class="font-weight-bold mb-2 text-dark"><i class="fas fa-users text-primary mr-1"></i>Grupo de investigación</h6>
                    <div class="row small">
                        <div class="col-md-8 mb-1">
                            <span class="text-muted">Integrantes (según el sistema, en orden alfabético):</span>
                            <div class="font-weight-bold">
                                {{ collect([$user])->merge($companeros)->sortBy(fn ($c) => \Illuminate\Support\Str::ascii(mb_strtolower($c->apellidos.' '.$c->name)))->map(fn ($c) => $c->apellidos.', '.$c->name)->implode('; ') }}
                            </div>
                            <span class="text-muted d-block mt-2">Programa de estudios:</span>
                            <div class="font-weight-bold">{{ $programa ?: '—' }}</div>
                        </div>
                        <div class="col-md-4 mb-1">
                            <span class="text-muted">Vigencia actual:</span>
                            <div>
                                @if ($vigencia->conocida())
                                    <strong>Vence: {{ $fmt($vigencia->vencimiento) }}</strong>
                                    <span class="badge {{ $vigencia->vencido() ? 'badge-danger' : 'badge-warning' }} ml-1">
                                        {{ $vigencia->vencido() ? 'Vencido' : 'Quedan '.$vigencia->tiempoRestanteTexto() }}
                                    </span>
                                @else
                                    <span class="text-muted">Pendiente de registro de fecha</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 mt-2 text-muted">Si algún dato no coincide, avísanos desde «¿Algún dato no coincide?» en tu proceso de titulación.</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('mi-titulacion.ampliacion-pti.enviar') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold small d-block">¿Es la primera o la segunda vez que solicita la ampliación? <span class="text-danger">*</span></label>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons" role="group" aria-label="Número de solicitud">
                            @foreach (['1' => '1.ª vez', '2' => '2.ª vez'] as $num => $texto)
                                @php $noDisponible = (int) $num <= $previas; @endphp
                                <label class="btn btn-outline-primary btn-sm px-3 {{ $v('vez') === $num ? 'active' : '' }} {{ $noDisponible ? 'disabled' : '' }}" @if ($noDisponible) title="Ya tienes {{ $previas }} ampliación aprobada" @endif>
                                    <input type="radio" name="vez" value="{{ $num }}" autocomplete="off" @checked($v('vez') === $num) @disabled($noDisponible) required> {{ $texto }}
                                </label>
                            @endforeach
                        </div>
                        <small class="d-block text-muted mt-1">
                            La ampliación del PTI se puede solicitar <strong>como máximo {{ $maximo }} veces</strong>.
                            @if ($previas) Ya tienes {{ $previas }} aprobada en el sistema, así que esta es la {{ $previas + 1 }}.ª. @endif
                        </small>
                        @error('vez') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small d-block">Ciclo <span class="text-danger">*</span></label>
                        <div class="btn-group btn-group-toggle flex-wrap" data-toggle="buttons" role="group" aria-label="Ciclo">
                            @foreach (Solicitud::CICLOS as $c)
                                <label class="btn btn-outline-primary btn-sm px-3 {{ $v('ciclo') === $c ? 'active' : '' }}">
                                    <input type="radio" name="ciclo" value="{{ $c }}" autocomplete="off" @checked($v('ciclo') === $c) required> {{ $c }}
                                </label>
                            @endforeach
                        </div>
                        <small class="d-block text-muted mt-1">Indica en qué ciclo estás o si eres egresado(a).</small>
                        @error('ciclo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Domicilio actual (Barrio o Comunidad / Distrito / Provincia / Departamento) <span class="text-danger">*</span></label>
                        <input type="text" name="domicilio" value="{{ $v('domicilio') }}" maxlength="300" class="form-control @error('domicilio') is-invalid @enderror" required>
                        <small class="text-muted">Detalla claramente la dirección: calle-número / barrio-lote / nombre de la comunidad / distrito / provincia / departamento.</small>
                        @error('domicilio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Con el debido respeto me presento y solicito se me permita extender el plazo para realizar mi investigación titulada: <span class="text-danger">*</span></label>
                        <textarea name="titulo" rows="3" maxlength="1000" class="form-control @error('titulo') is-invalid @enderror" required>{{ $v('titulo') }}</textarea>
                        <small class="text-muted">Escribe el nombre con el que se aprobó tu investigación.</small>
                        @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small d-block">Fecha de aprobación del PTI <span class="text-danger">*</span></label>
                        @if ($fechaPtiSistema)
                            {{-- La conoce el sistema (RD del PTI o fecha del PTI): no se escribe; el servidor usa la misma. --}}
                            <div class="d-inline-flex align-items-center border rounded bg-light px-3 py-2" style="gap:.5rem;">
                                <i class="fas fa-lock text-muted fa-xs"></i>
                                <strong>{{ \Carbon\Carbon::parse($fechaPtiSistema)->format('d/m/Y') }}</strong>
                            </div>
                            <small class="d-block text-muted mt-1">Según tu proceso de titulación. Si no es correcta, avísanos desde «¿Algún dato no coincide?».</small>
                        @else
                            <input type="date" name="fecha_pti" value="{{ $v('fecha_pti') }}" max="{{ now()->toDateString() }}" class="form-control @error('fecha_pti') is-invalid @enderror" style="max-width:220px;" required>
                            <small class="text-muted">Escribe la fecha de la RD de aprobación del PTI.</small>
                        @endif
                        @error('fecha_pti') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Las razones que tengo para solicitar la extensión de plazo de investigación son las siguientes: <span class="text-danger">*</span></label>
                        <textarea name="motivo" rows="4" maxlength="2000" class="form-control @error('motivo') is-invalid @enderror" required>{{ $v('motivo') }}</textarea>
                        <small class="text-muted">Detalla claramente las razones que te llevaron a realizar esta solicitud.</small>
                        @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <p class="small mb-3">Para este fin remito a su despacho la R.D. de aprobación del Plan de Trabajo de Investigación y asignación de asesor, en versión digital. Agradecido(a) por su deferencia, quedo de usted.</p>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small d-flex align-items-end" style="min-height:2.9em;"><span>Anexo 1: RD de aprobación de PTI y asignación de asesor <span class="text-danger">*</span></span></label>
                            <div class="custom-file">
                                <input type="file" name="rd_pti" class="custom-file-input @error('rd_pti') is-invalid @enderror" accept=".pdf,application/pdf" required id="rdPtiFile">
                                <label class="custom-file-label small" for="rdPtiFile" data-browse="Elegir">Adjuntar la RD (PDF)...</label>
                            </div>
                            <small class="text-muted d-block mt-1">1 archivo PDF (hasta 10 MB).</small>
                            @error('rd_pti') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small d-flex align-items-end" style="min-height:2.9em;"><span>Anexo 2: Comprobante de pago por derecho de extensión de plazo de investigación <span class="text-danger">*</span></span></label>
                            <div class="custom-file">
                                <input type="file" name="comprobante" class="custom-file-input @error('comprobante') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required id="cpFile">
                                <label class="custom-file-label small" for="cpFile" data-browse="Elegir">Adjuntar el comprobante...</label>
                            </div>
                            <small class="text-muted d-block mt-1">Cód. 11 · S/ 50.00. 1 archivo PDF o imagen (hasta 10 MB).</small>
                            @error('comprobante') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="custom-control custom-checkbox my-3">
                        <input type="checkbox" name="declaracion" value="1" class="custom-control-input @error('declaracion') is-invalid @enderror" id="decVeracidad" required>
                        <label class="custom-control-label small" for="decVeracidad">
                            <strong>Declaración de veracidad.</strong> Declaro bajo juramento que la información registrada expresa la verdad.
                        </label>
                        @error('declaracion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-3" style="gap:.5rem;">
                        <a href="{{ $volver }}#titulacion" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-paper-plane mr-1"></i>{{ $estado === 'observada' ? 'Reenviar solicitud subsanada' : 'Enviar solicitud' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
document.querySelectorAll('.custom-file-input').forEach(function(input) {
    input.addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'Elegir archivo...';
        var label = input.nextElementSibling;
        if (label) label.textContent = fileName;
    });
});
</script>
@endsection
