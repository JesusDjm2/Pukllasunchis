@extends($layout)
@section('titulo', 'Solicitud de Ampliación de PTI')
@section('contenido')
@include('alumnos.partials.ui')
@php
    use App\Models\Titulacion\Solicitud;
    $v = fn ($campo) => old($campo, $valores[$campo] ?? '');
    $estado = $ultima?->estado;
    $mostrarForm = $institucional && $ptiAprobado && (! $ultima || $estado === 'observada');
    $fmt = fn ($d) => $d ? $d->format('d/m/Y') : '—';
@endphp

<div class="al-ui cr">
    <a href="{{ $volver }}#titulacion" class="cr-volver"><i class="fas fa-arrow-left fa-xs mr-1"></i>Volver a mi proceso de titulación</a>

    <section class="al-hero cr-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <span class="al-kicker">Proceso de titulación · Trabajo de Investigación</span>
                <h1>Ampliación de Plazo de PTI</h1>
                <p class="al-hero-sub">Solicita a Secretaría General la prórroga de vigencia de tu Plan de Trabajo de Investigación (tasa de Caja Cusco: Cód. 11 · S/ 50.00). La respuesta y nueva R.D. llegarán a tu intranet y correo institucional.</p>
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
                    <dt class="col-sm-3 text-muted">Tiempo solicitado:</dt>
                    <dd class="col-sm-9 font-weight-bold">{{ $ultima->datos['meses_solicitados'] ?? '6' }} meses</dd>

                    <dt class="col-sm-3 text-muted">Motivo / Justificación:</dt>
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
                        <dt class="col-sm-3 text-muted">Documento adicional:</dt>
                        <dd class="col-sm-9">
                            @php $docs = Solicitud::enlaces($ultima->archivo, fn ($n) => route('mi-titulacion.ampliacion-pti.archivo', [$ultima->id, 'archivo', $n])); @endphp
                            @foreach ($docs as $e)
                                <a href="{{ $e }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary py-0">
                                    <i class="fas fa-paperclip mr-1"></i>Ver anexo
                                </a>
                            @endforeach
                        </dd>
                    @endif
                </dl>
            </div>
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
                <i class="fas fa-edit mr-2 text-primary"></i>Datos para la ampliación de plazo
            </div>
            <div class="card-body">
                {{-- Ficha resumen del PTI actual --}}
                <div class="bg-light p-3 rounded mb-4 border">
                    <h6 class="font-weight-bold mb-2 text-dark"><i class="fas fa-info-circle text-primary mr-1"></i>Datos de tu Plan de Trabajo de Investigación</h6>
                    <div class="row small">
                        <div class="col-md-8 mb-1">
                            <span class="text-muted">Título del PTI:</span>
                            <div class="font-weight-bold">“{{ $pti?->titulo ?: 'Plan de Trabajo de Investigación' }}”</div>
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
                        @if ($companeros->isNotEmpty())
                            <div class="col-12 mt-2">
                                <span class="text-muted">Integrantes del grupo:</span>
                                <div>{{ $user->name.' '.$user->apellidos }} (tú), {{ $companeros->map(fn($c) => $c->name.' '.$c->apellidos)->implode(', ') }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('mi-titulacion.ampliacion-pti.enviar') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Tiempo de prórroga solicitado <span class="text-danger">*</span></label>
                            <select name="meses_solicitados" class="form-control @error('meses_solicitados') is-invalid @enderror" required>
                                <option value="6" @selected($v('meses_solicitados') === '6')>6 meses (medio año adicional)</option>
                                <option value="12" @selected($v('meses_solicitados') === '12')>12 meses (1 año adicional)</option>
                            </select>
                            <small class="text-muted">Selecciona el plazo necesario para concluir el trabajo de campo y redacción.</small>
                            @error('meses_solicitados') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Teléfono / WhatsApp de contacto <span class="text-danger">*</span></label>
                            <input type="tel" name="celular" value="{{ $v('celular') }}" class="form-control @error('celular') is-invalid @enderror" placeholder="987 654 321" required>
                            <small class="text-muted">Para coordinaciones de Secretaría.</small>
                            @error('celular') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">Motivo y justificación académica de la ampliación <span class="text-danger">*</span></label>
                        <textarea name="motivo" rows="4" maxlength="2000" class="form-control @error('motivo') is-invalid @enderror" placeholder="Explique brevemente las razones por las cuales requiere la prórroga (por ejemplo: reajuste en la recolección de datos, aplicación de instrumentos en campo, extensión de muestra, redacción del informe final, etc.)." required>{{ $v('motivo') }}</textarea>
                        <small class="text-muted">Mínimo 15 caracteres. Detalla claramente los motivos académicos.</small>
                        @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Comprobante de pago (Caja Cusco) <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" name="comprobante" class="custom-file-input @error('comprobante') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required id="cpFile">
                                <label class="custom-file-label small" for="cpFile" data-browse="Elegir">Adjuntar boleta / voucher...</label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Tasa institucional: <strong>Cód. 11 · S/ 50.00</strong> (Extensión de plazo) en Caja Cusco. Formatos: PDF o imagen (hasta 10 MB).
                            </small>
                            @error('comprobante') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Documento de sustento o anexo <span class="text-muted font-weight-normal">(opcional)</span></label>
                            <div class="custom-file">
                                <input type="file" name="archivo" class="custom-file-input @error('archivo') is-invalid @enderror" accept=".pdf,.doc,.docx" id="anexoFile">
                                <label class="custom-file-label small" for="anexoFile" data-browse="Elegir">FUT o informe de avance...</label>
                            </div>
                            <small class="text-muted d-block mt-1">Si cuentas con FUT institucional o carta de avance de tu asesor, puedes adjuntarlo aquí (PDF o Word).</small>
                            @error('archivo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="custom-control custom-checkbox my-3">
                        <input type="checkbox" name="declaracion" value="1" class="custom-control-input @error('declaracion') is-invalid @enderror" id="decVeracidad" required>
                        <label class="custom-control-label small" for="decVeracidad">
                            Declaro bajo juramento que los datos y el comprobante adjunto son verídicos y corresponden a mi trámite de titulación en la EESP Pukllasunchis.
                        </label>
                        @error('declaracion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-3" style="gap:.5rem;">
                        <a href="{{ $volver }}#titulacion" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-paper-plane mr-1"></i>{{ $estado === 'observada' ? 'Reenviar solicitud subsanada' : 'Enviar solicitud de ampliación' }}
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
