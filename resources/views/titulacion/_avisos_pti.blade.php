{{-- Avisos de vencimiento del PTI (Unidad de Investigación): vigencia, envío de recordatorio o aviso
     urgente (a la persona y a su grupo) e historial. Hereda las variables de la ficha. --}}
@if (! $vigencia->conocida())
    <div class="small text-muted">
        Para enviar avisos, registre en «Editar datos del PTI» la <strong>Fecha PTI</strong> o la <strong>Fecha vencimiento</strong> y guarde.
    </div>
@else
    <div class="ft-vigencia mb-3">
        <div class="small">
            Inscrito: <strong>{{ $vigencia->inscripcion?->format('d/m/Y') ?? '—' }}</strong>
            &middot; Vence: <strong>{{ $vigencia->vencimiento->format('d/m/Y') }}</strong>
            <span class="text-muted">({{ $vigencia->origen === 'archivo' ? 'fecha registrada' : 'fecha PTI + 2 años' }})</span>
            &middot;
            @if ($vigencia->vencido())
                <span class="badge badge-dark">Vencido</span>
            @else
                <span class="badge badge-{{ $sugerido === 'urgente' ? 'danger' : 'success' }}">Quedan {{ $vigencia->tiempoRestanteTexto() }}</span>
                <span class="text-muted">· aviso sugerido: <strong>{{ $sugerido === 'urgente' ? 'urgente' : 'recordatorio' }}</strong></span>
            @endif
        </div>
    </div>

    @unless ($vigencia->vencido())
        <form method="POST" action="{{ route('titulacion.aviso-pti.enviar', $egresado->id) }}"
              data-confirm-boton="Sí, enviar"
              onsubmit="var n = 1 + this.querySelectorAll('input[name=&quot;integrantes[]&quot;]:checked').length;
                        this.dataset.confirmTitulo = event.submitter && event.submitter.value === 'urgente' ? '¿Enviar el aviso urgente?' : '¿Enviar el recordatorio?';
                        this.dataset.confirm = 'Se enviará un correo a ' + n + (n === 1 ? ' persona.' : ' personas.');
                        return window.PkAlert ? true : confirm(this.dataset.confirmTitulo + '\n' + this.dataset.confirm);">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.aviso-pti.enviar'))>
            @csrf
            <div class="mt-subtitulo">Destinatarios</div>
            <div class="small mb-2">
                <i class="fas fa-check-square text-muted mr-1"></i>{{ $egresado->apellidos }}, {{ $egresado->name }}
                <span class="text-muted">— {{ $egresado->email ?: 'sin correo' }}</span>
            </div>
            @if ($integrantes->isNotEmpty())
                <div class="small mb-2">
                    <span class="text-muted">Su grupo (un correo personal para cada uno, con sus propias fechas):</span>
                    @foreach ($integrantes as $integrante)
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="integrantes[]" value="{{ $integrante->id }}"
                                   id="aviso-{{ $egresado->id }}-{{ $integrante->id }}" checked>
                            <label class="custom-control-label" for="aviso-{{ $egresado->id }}-{{ $integrante->id }}">
                                {{ $integrante->apellidos }}, {{ $integrante->name }}
                                @if ($integrante->email ?? null) <span class="text-muted">— {{ $integrante->email }}</span> @endif
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="d-flex flex-wrap align-items-center" style="gap:.4rem;">
                <button type="submit" name="tipo" value="recordatorio" class="btn btn-sm {{ $sugerido === 'recordatorio' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-paper-plane mr-1"></i>Enviar recordatorio
                </button>
                <button type="submit" name="tipo" value="urgente" class="btn btn-sm {{ $sugerido === 'urgente' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="fas fa-exclamation-triangle mr-1"></i>Enviar aviso urgente
                </button>
                <span class="small ml-1">
                    Vista previa:
                    <a href="{{ route('titulacion.aviso-pti.vista', [$egresado->id, 'recordatorio']) }}" target="_blank" class="js-vista-aviso" data-titulo="Recordatorio — {{ $egresado->apellidos }}, {{ $egresado->name }}">recordatorio</a> ·
                    <a href="{{ route('titulacion.aviso-pti.vista', [$egresado->id, 'urgente']) }}" target="_blank" class="js-vista-aviso" data-titulo="Aviso urgente — {{ $egresado->apellidos }}, {{ $egresado->name }}">urgente</a>
                </span>
            </div>
        </fieldset>
</form>
    @else
        <div class="small text-muted">
            <i class="fas fa-info-circle mr-1"></i>Los avisos son para <strong>antes</strong> del vencimiento, por eso ya no se muestran los botones de envío.
            Si el PTI se renovó o la fecha está mal, corríjala en «Editar datos del PTI» y volverán a aparecer.
        </div>
    @endunless
@endif

<div class="mt-subtitulo mt-3">Historial de avisos</div>
@forelse ($seguimiento->avisosPti as $aviso)
    <div class="small {{ $aviso->enviado ? '' : 'text-danger' }}">
        <i class="fas {{ $aviso->enviado ? 'fa-check text-success' : 'fa-times' }} mr-1"></i>
        {{ \App\Models\Titulacion\AvisoPti::TIPOS[$aviso->tipo] ?? $aviso->tipo }}
        @if ($aviso->tipo !== 'excel')
            — {{ $aviso->created_at->format('d/m/Y H:i') }}
            @if ($aviso->email) a {{ $aviso->email }} @endif
            @if ($aviso->creadoPor) · por {{ $aviso->creadoPor->name }} @endif
            @unless ($aviso->enviado) · error: {{ \Illuminate\Support\Str::limit($aviso->error, 120) }} @endunless
        @endif
    </div>
@empty
    <div class="small text-muted">Todavía no se envió ningún aviso.</div>
@endforelse
