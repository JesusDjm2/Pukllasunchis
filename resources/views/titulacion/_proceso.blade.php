{{-- Pestaña Proceso de la ficha del gestor: a la izquierda la línea de tiempo de todo el proceso
     (PTI → TI → Tesis, ver PasosProceso) y a la derecha solo el panel del paso elegido; por defecto,
     el que toca ahora. Navegación con JS delegado (_ficha_recursos): funciona también en las fichas
     que se cargan por AJAX. Al guardar se vuelve al mismo paso (campo volver_paso → ?paso=).
     Hereda las variables de _show_contenido ($seguimiento, $avance, $pti, $tesis, $vigencia, $rdDe,
     $selectDocente, $bloqCampo, $integrantes, $alertas, $tabId…). --}}
@php
    $pasos = new \App\Services\Titulacion\PasosProceso($seguimiento, $avance);
    $idPaso = fn (string $paso) => 'acc-'.$paso.'-'.$egresado->id;
    $extras = array_filter([
        'avisos' => $pti ? ['Avisos de vencimiento', 'fa-envelope', $sugerido ? ($sugerido === 'urgente' ? 'urgente' : 'sugerido') : null] : null,
        'corregir' => $avance->hitos['pti_aprobado']['logrado'] ? ['Corregir datos de TI y Tesis', 'fa-sliders-h', null] : null,
    ]);
    $pedido = $fichaActiva ? request('paso') : null;
    $visible = ($pedido && (isset($pasos->pasos[$pedido]) || isset($extras[$pedido]))) ? $pedido : ($pasos->actual ?? 'tesis_licenciado');
    // Recién guardado un paso que quedó hecho: se pasa solo al siguiente (salvo listas donde se
    // suele agregar más de uno, o los datos del PTI que se siguen consultando).
    $avanzo = null;
    if ($pedido && Session::has('success') && $pasos->actual && $pedido !== $pasos->actual
        && ($pasos->pasos[$pedido]['estado'] ?? null) === 'hecho' && ! in_array($pedido, ['ti_asesores', 'ti_jurado', 'pti_datos'], true)) {
        $avanzo = $pasos->pasos[$pedido]['titulo'];
        $visible = $pasos->actual;
    }
    $iconoEstado = ['hecho' => 'fa-check', 'actual' => 'fa-arrow-right', 'espera' => 'fa-hourglass-half', 'pendiente' => '', 'bloqueado' => 'fa-lock', 'sin_registro' => 'fa-minus'];
    $textoEstado = ['hecho' => 'Hecho', 'actual' => 'Ahora toca', 'espera' => 'En espera', 'pendiente' => 'Pendiente', 'bloqueado' => 'Bloqueado', 'sin_registro' => 'Sin registrar'];
    $asesoresTi = $seguimiento->participantes->where('etapa', 'ti')->where('funcion', 'asesor');
    $juradoTi = $seguimiento->participantes->where('etapa', 'ti')->where('funcion', 'jurado');
    $juradoTesis = $seguimiento->participantes->where('etapa', 'tesis')->where('funcion', 'jurado');
    $dictaminantes = $juradoTesis->filter(fn ($p) => \App\Services\Titulacion\PasosProceso::esDictaminante($p->cargo));
    $juradoActo = $juradoTesis->reject(fn ($p) => \App\Services\Titulacion\PasosProceso::esDictaminante($p->cargo));
    $cargosActo = array_values(array_filter(\App\Models\Titulacion\Participante::CARGOS['jurado'], fn ($c) => ! \App\Services\Titulacion\PasosProceso::esDictaminante($c)));
    $historialPti = $seguimiento->ptis ?? collect();
    $tiAprobado = $avance->hitos['ti_aprobado']['logrado'];
    $tiYaAprobado = $avance->hitos['ti_aprobado']['logrado'] || $avance->hitos['bachiller']['logrado'] || $avance->hitos['licenciado']['logrado'];
    $ptiVencido = ! $tiYaAprobado && (bool) $pti?->estaVencido();

    // Resoluciones para acceso directo con 1 clic junto a los pasos del proceso
    $todasResoluciones = $seguimiento->resoluciones ?? collect();
    $rdsPti = $todasResoluciones->filter(fn ($r) => in_array($r->tipo, ['PTI', 'AmpliacionPti', 'CambioAsesor', 'CambioTema', 'RetiroGrupo', 'IncorporacionGrupo', 'AnulacionPti'], true))->values();
    $rdsTiAsesores = $todasResoluciones->filter(fn ($r) => in_array($r->tipo, ['CambioAsesor'], true))->values();
    $rdsTiRd = $todasResoluciones->filter(fn ($r) => in_array($r->tipo, ['Exposicion'], true))->values();
    $rdsTesisFecha = $todasResoluciones->filter(fn ($r) => in_array($r->tipo, ['FechaSustentacion', 'Extension'], true))->values();
    $rdsTesisRd = $todasResoluciones->filter(fn ($r) => in_array($r->tipo, ['Tesis'], true))->values();

    $rdCampoPaso = function (string $clave) use ($pti, $tesis, $rdsPti, $rdsTiRd, $rdsTesisRd) {
        return match ($clave) {
            'pti_datos' => ($pti?->rd_pti && ! $rdsPti->contains(fn ($r) => \App\Services\Titulacion\GrupoTitulacion::claveRd($r->numero_rd) === \App\Services\Titulacion\GrupoTitulacion::claveRd($pti->rd_pti)))
                ? (object)['numero_rd' => $pti->rd_pti, 'tipo' => 'PTI', 'archivo' => null] : null,
            'ti_rd' => ($tesis?->rd_ti && ! $rdsTiRd->contains(fn ($r) => \App\Services\Titulacion\GrupoTitulacion::claveRd($r->numero_rd) === \App\Services\Titulacion\GrupoTitulacion::claveRd($tesis->rd_ti)))
                ? (object)['numero_rd' => $tesis->rd_ti, 'tipo' => 'Exposicion', 'archivo' => null] : null,
            'tesis_rd' => ($tesis?->rd_tesis && ! $rdsTesisRd->contains(fn ($r) => \App\Services\Titulacion\GrupoTitulacion::claveRd($r->numero_rd) === \App\Services\Titulacion\GrupoTitulacion::claveRd($tesis->rd_tesis)))
                ? (object)['numero_rd' => $tesis->rd_tesis, 'tipo' => 'Tesis', 'archivo' => null] : null,
            default => null,
        };
    };

    $obtenerRdsDelPaso = function (string $clave) use ($rdsPti, $rdsTiAsesores, $rdsTiRd, $rdsTesisFecha, $rdsTesisRd, $rdCampoPaso) {
        $lista = match ($clave) {
            'pti_datos' => $rdsPti,
            'ti_asesores' => $rdsTiAsesores,
            'ti_rd' => $rdsTiRd,
            'tesis_fecha' => $rdsTesisFecha,
            'tesis_rd' => $rdsTesisRd,
            default => collect(),
        };
        if ($extra = $rdCampoPaso($clave)) {
            $lista = $lista->concat([$extra]);
        }
        return $lista;
    };

    $tagRd = function ($tipo) {
        return match ($tipo) {
            'PTI' => 'RD PTI',
            'AmpliacionPti' => 'Ampliación',
            'CambioAsesor' => 'Cambio asesor',
            'CambioTema' => 'Cambio tema',
            'RetiroGrupo' => 'Retiro',
            'IncorporacionGrupo' => 'Incorporación',
            'AnulacionPti' => 'Anulación',
            'Exposicion' => 'RD Exposición',
            'FechaSustentacion' => 'RD Sustentación',
            'Extension' => 'Extensión',
            'Tesis' => 'RD Tesis',
            default => $tipo,
        };
    };
@endphp

@if ($alertas)
    <div class="tp-alertas">
        @foreach ($alertas as [$nivel, $texto, $irA])
            <label for="{{ $tabId }}-{{ $irA }}" class="tp-alerta tp-alerta--{{ $nivel }}" title="Ir a la pestaña">
                <i class="fas {{ $nivel === 'danger' ? 'fa-exclamation-triangle' : ($nivel === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle') }}"></i>
                <span>{!! $texto !!}</span>
            </label>
        @endforeach
    </div>
@endif

<div class="tp" data-tp>
    {{-- ── Línea de tiempo ── --}}
    <nav class="tp-linea" aria-label="Pasos del proceso">
        <div class="tp-progreso {{ $ptiVencido ? 'tp-progreso--vencido' : '' }}" title="Avance del proceso completo">
            <div class="tp-progreso__barra"><span style="width: {{ $pasos->porcentaje() }}%"></span></div>
            <span class="tp-progreso__texto">{{ $pasos->porcentaje() }}%</span>
        </div>
        @foreach (\App\Services\Titulacion\PasosProceso::ETAPAS as $etapa => [$nombreEtapa])
            @php $estEtapa = $pasos->estadoEtapa($etapa); @endphp
            <div class="tp-etapa tp-etapa--{{ $estEtapa }}">
                <div class="tp-etapa__cab">
                    <span class="tp-etapa__nombre">{{ $nombreEtapa }}</span>
                    @if ($estEtapa === 'completa') <span class="tp-etapa__estado"><i class="fas fa-check"></i> Completa</span>
                    @elseif ($estEtapa === 'vencida') <span class="tp-etapa__estado tp-etapa__estado--vencida"><i class="fas fa-hourglass-end"></i> PTI Vencido</span>
                    @elseif ($estEtapa === 'bloqueada') <span class="tp-etapa__estado"><i class="fas fa-lock"></i></span>
                    @endif
                </div>
                <ol class="tp-pasos">
                    @foreach ($pasos->deEtapa($etapa) as $p)
                        @php
                            $rdsDelPaso = $obtenerRdsDelPaso($p['clave']);
                            $primeraRdConArchivo = $rdsDelPaso->first(fn ($r) => filled($r->archivo));
                        @endphp
                        <li>
                            <div class="tp-paso-item {{ $p['clave'] === $visible ? 'is-visible' : '' }} {{ $rdsDelPaso->isNotEmpty() ? 'has-rds' : '' }}">
                                <button type="button" class="tp-paso tp-paso--{{ $p['estado'] }} {{ $p['clave'] === $visible ? 'is-visible' : '' }}"
                                        data-tp-ir="{{ $p['clave'] }}" aria-controls="tp-{{ $p['clave'] }}-{{ $egresado->id }}">
                                    <span class="tp-paso__punto">@if ($iconoEstado[$p['estado']])<i class="fas {{ $iconoEstado[$p['estado']] }}"></i>@endif</span>
                                    <span class="tp-paso__texto">
                                        <span class="tp-paso__titulo">{{ $p['titulo'] }}</span>
                                        @if ($p['espera'])
                                            <span class="tp-paso__resumen tp-paso__resumen--espera">{{ $p['espera'] }}</span>
                                        @elseif ($p['estado'] === 'actual')
                                            <span class="tp-paso__resumen tp-paso__resumen--actual">Ahora toca</span>
                                        @elseif ($p['resumen'] && $p['estado'] === 'hecho')
                                            <span class="tp-paso__resumen" title="{{ $p['resumen'] }}">
                                                @if ($primeraRdConArchivo)
                                                    <i class="fas fa-file-pdf text-danger mr-1" title="Resolución con archivo adjunto"></i>
                                                @endif
                                                {{ $p['resumen'] }}
                                            </span>
                                        @elseif ($p['estado'] === 'sin_registro')
                                            <span class="tp-paso__resumen">No se registró</span>
                                        @endif
                                    </span>
                                </button>
                                @if ($rdsDelPaso->isNotEmpty())
                                    <div class="tp-paso-dock" onclick="event.stopPropagation();">
                                        @foreach ($rdsDelPaso as $r)
                                            @php
                                                $esEnlace = $r->archivo && ! \App\Services\Titulacion\ArchivosRd::nombreDesdeUrl($r->archivo);
                                                $tag = $tagRd($r->tipo);
                                            @endphp
                                            @if ($r->archivo)
                                                <a href="{{ $r->archivo }}" target="_blank" rel="noopener" class="tp-rd-pill" title="Abrir {{ $tag }}: {{ $r->numero_rd }}" onclick="event.stopPropagation();">
                                                    <i class="fas {{ $esEnlace ? 'fa-external-link-alt' : 'fa-file-pdf' }} tp-rd-pill__ico"></i>
                                                    <span class="tp-rd-pill__tag">{{ $tag }}</span>
                                                    <i class="fas fa-external-link-alt tp-rd-pill__salida"></i>
                                                </a>
                                            @else
                                                <span class="tp-rd-pill tp-rd-pill--sin-pdf" title="RD registrada sin archivo: {{ $r->numero_rd }}">
                                                    <i class="far fa-file-alt tp-rd-pill__ico"></i>
                                                    <span class="tp-rd-pill__tag">{{ $tag }}</span>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endforeach
        <div class="tp-extras">
            @foreach ($extras as $clave => [$nombre, $icono, $marca])
                <button type="button" class="tp-extra {{ $clave === $visible ? 'is-visible' : '' }}" data-tp-ir="{{ $clave }}">
                    <i class="fas {{ $icono }} fa-fw"></i><span>{{ $nombre }}</span>
                    @if ($marca) <span class="tp-extra__marca tp-extra__marca--{{ $marca }}">{{ $marca === 'urgente' ? 'Urgente' : 'Sugerido' }}</span> @endif
                </button>
            @endforeach
            @if ($todasResoluciones->isNotEmpty())
                <label for="{{ $tabId }}-resoluciones" class="tp-extra" style="cursor: pointer; margin-bottom: 0;" title="Ver todas las resoluciones en el Historial">
                    <i class="fas fa-gavel fa-fw" style="color: #4e73df;"></i><span>Todas las Resoluciones ({{ $todasResoluciones->count() }})</span>
                </label>
            @endif
        </div>
    </nav>

    {{-- ── Panel del paso elegido ── --}}
    <section class="tp-panel">
        @foreach ($pasos->pasos as $clave => $p)
            <div class="tp-det" id="tp-{{ $clave }}-{{ $egresado->id }}" data-tp-det="{{ $clave }}" @if ($clave !== $visible) hidden @endif>
                <div class="tp-det__cab">
                    <div>
                        <div class="tp-det__eyebrow">{{ \App\Services\Titulacion\PasosProceso::ETAPAS[$p['etapa']][0] }} · Paso {{ $pasos->posicion($clave) }}</div>
                        <h3 class="tp-det__titulo">{{ $p['titulo'] }}</h3>
                    </div>
                    <div class="d-flex align-items-center" style="gap: .45rem; flex-wrap: wrap;">
                        @php $rdsPanel = $obtenerRdsDelPaso($clave); @endphp
                        @if ($rdsPanel->isNotEmpty())
                            @foreach ($rdsPanel as $r)
                                @if ($r->archivo)
                                    @php
                                        $esEnlace = $r->archivo && ! \App\Services\Titulacion\ArchivosRd::nombreDesdeUrl($r->archivo);
                                        $tag = $tagRd($r->tipo);
                                    @endphp
                                    <a href="{{ $r->archivo }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger tp-det__rd-btn" title="Abrir {{ $tag }}: {{ $r->numero_rd }}">
                                        <i class="fas {{ $esEnlace ? 'fa-external-link-alt' : 'fa-file-pdf' }} mr-1"></i>
                                        <span>{{ $tag }}</span>
                                        <i class="fas fa-external-link-alt ml-1 fa-xs"></i>
                                    </a>
                                @endif
                            @endforeach
                        @endif
                        <span class="tp-chip tp-chip--{{ $p['estado'] }}">
                            @if ($iconoEstado[$p['estado']])<i class="fas {{ $iconoEstado[$p['estado']] }}"></i>@endif {{ $textoEstado[$p['estado']] }}
                        </span>
                    </div>
                </div>

                @if ($avanzo && $clave === $visible)
                    <div class="tp-aviso tp-aviso--ok"><i class="fas fa-check-circle"></i><div><strong>¡Listo!</strong> «{{ $avanzo }}» quedó registrado. Ahora toca: <strong>{{ $p['titulo'] }}</strong>.</div></div>
                @endif

                @if ($p['estado'] === 'bloqueado')
                    @php
                        $bloqPorPtiVencido = $ptiVencido && in_array($p['etapa'], ['ti', 'tesis'], true);
                        $requisito = $avance->hitos[\App\Services\Titulacion\PasosProceso::ETAPAS[$p['etapa']][1]]['label'] ?? '';
                    @endphp
                    @if ($bloqPorPtiVencido)
                        @php
                            $puedeAmpliar = $vigencia->puedeSolicitarAmpliacion();
                            $limiteAmp = $vigencia->limiteSolicitarAmpliacion();
                        @endphp
                        <div class="tp-vacio tp-vacio--accion" style="border: 2px dashed #e74a3b; background: #fff8f8; border-radius: .5rem; padding: 1.5rem; text-align: center;">
                            <i class="fas fa-hourglass-end text-danger mb-2" style="font-size: 2rem;"></i>
                            <h4 class="text-danger font-weight-bold mb-1" style="font-size: 1.1rem;">Trámite detenido: El PTI venció{{ $vigencia->conocida() ? ' el '.$vigencia->vencimiento->format('d/m/Y') : '' }}</h4>
                            <p class="text-dark mb-2">
                                @if (! $puedeAmpliar)
                                    El plazo reglamentario para solicitar ampliación (hasta 2 días tras vencer) expiró el <strong>{{ $limiteAmp?->format('d/m/Y') }}</strong>.<br>
                                    El trámite no puede continuarse y debe <strong>reiniciarse desde cero</strong> mediante un nuevo PTI (PTI N° {{ ($pti->numero ?? 1) + 1 }}).
                                @else
                                    Para continuar con el <strong>Trabajo de Investigación (TI)</strong> y habilitar este paso, se requiere registrar una Resolución de Ampliación de plazo (plazo hasta el {{ $limiteAmp?->format('d/m/Y') }}) o Iniciar un nuevo proceso (PTI N° {{ ($pti->numero ?? 1) + 1 }}).
                                @endif
                            </p>
                            <div class="d-flex align-items-center justify-content-center mt-3" style="gap: .75rem; flex-wrap: wrap;">
                                <a href="{{ route('titulacion.inicio.crear', $egresado->id) }}" class="btn btn-primary">
                                    <i class="fas fa-play mr-1"></i>Iniciar Nuevo PTI (PTI N° {{ ($pti->numero ?? 1) + 1 }})
                                </a>
                                @if ($puedeAmpliar)
                                    <button type="button" class="btn btn-outline-danger" data-tp-ir="pti_datos">
                                        <i class="fas fa-hourglass-half mr-1"></i>Registrar Ampliación de PTI
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="tp-vacio">
                            <i class="fas fa-lock"></i>
                            <p>Este paso se habilita cuando esté registrado <strong>«{{ $requisito }}»</strong>.</p>
                            <p class="tp-vacio__sub">{{ $p['ayuda'] }}</p>
                        </div>
                    @endif
            </div>{{-- cierra el .tp-det: @continue salta el cierre del final del bucle --}}
                    @continue
                @endif

                @if ($p['espera'] && ! in_array($clave, ['ti_informe', 'tesis_rd'], true)) {{-- estos pasos muestran su propio aviso --}}
                    <div class="tp-aviso tp-aviso--espera"><i class="fas fa-hourglass-half"></i><div><strong>{{ $p['espera'] }}.</strong> {{ $p['ayuda'] }}</div></div>
                @elseif ($p['estado'] === 'actual' || $p['estado'] === 'pendiente')
                    <p class="tp-ayuda">{{ $p['ayuda'] }}</p>
                @elseif ($p['estado'] === 'sin_registro')
                    <div class="tp-aviso"><i class="fas fa-info-circle"></i><div>Este dato no se registró, pero ya hay un paso posterior hecho. Puede completarlo si lo tiene.</div></div>
                @endif

                @switch($clave)
                    @case('pti_datos')
                        @if (! $pti)
                            <div class="tp-vacio tp-vacio--accion">
                                <i class="fas fa-play-circle"></i>
                                <p>Aún no tiene PTI. Al iniciar el proceso se registran los datos del PTI, se genera su <strong>N° de expediente</strong> y se arma el grupo (o se hace individual).</p>
                                <a href="{{ route('titulacion.inicio.crear', $egresado->id) }}" @unless (\App\Services\Titulacion\AreasTitulacion::puede('investigacion')) hidden @endunless class="btn tp-btn"><i class="fas fa-play mr-1"></i>Iniciar proceso</a>
                            </div>
                        @else
                            @php
                                $ptiAprobado = $avance->hitos['pti_aprobado']['logrado'];
                                // Todas las ampliaciones, de la más antigua a la más reciente: cada una es una RD
                                // nueva y ninguna reemplaza a la de aprobación ni a las anteriores.
                                $ampliaciones = $seguimiento->resoluciones->where('tipo', 'AmpliacionPti')->filter(fn ($r) => filled($r->numero_rd))
                                    ->sortBy(fn ($r) => ($r->fecha?->format('Y-m-d') ?? '0000').'|'.str_pad($r->id, 9, '0', STR_PAD_LEFT))->values();
                                $ampliacionSoloCampo = filled($pti->rd_ampliacion)
                                    && ! $ampliaciones->contains(fn ($r) => \App\Services\Titulacion\GrupoTitulacion::claveRd($r->numero_rd) === \App\Services\Titulacion\GrupoTitulacion::claveRd($pti->rd_ampliacion));
                            @endphp

                            @if ($ptiVencido)
                                @php
                                    $puedeAmpliar = $vigencia->puedeSolicitarAmpliacion();
                                    $limiteAmp = $vigencia->limiteSolicitarAmpliacion();
                                @endphp
                                <div class="alert alert-danger d-flex align-items-start shadow-sm mb-3" style="border-left: 4px solid #e74a3b;">
                                    <i class="fas fa-exclamation-triangle fa-lg mr-2 mt-1 text-danger"></i>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold">
                                            El Plan de Trabajo de Investigación (PTI) venció el {{ $vigencia->conocida() ? $vigencia->vencimiento->format('d/m/Y') : '' }}.
                                            @if (! $puedeAmpliar)
                                                <span class="badge badge-danger ml-1">Plazo de ampliación caducado</span>
                                            @endif
                                        </div>
                                        <div class="small mt-1 text-dark">
                                            @if (! $puedeAmpliar)
                                                El plazo reglamentario para solicitar ampliación (hasta 2 días tras vencer) finalizó el <strong>{{ $limiteAmp?->format('d/m/Y') }}</strong>.
                                                Corresponde <strong>iniciar nuevamente el proceso desde cero (PTI N° {{ ($pti->numero ?? 1) + 1 }})</strong> a través de una nueva solicitud de PTI. Las RDs previas quedan archivadas en su historial como antecedentes.
                                            @else
                                                La etapa de Trabajo de Investigación (TI) se encuentra en pausa reglamentaria. El egresado tiene plazo hasta el <strong>{{ $limiteAmp?->format('d/m/Y') }}</strong> (hasta 2 días tras vencer) para regularizar mediante una <strong>Resolución de Ampliación</strong> o dar inicio a un <strong>Nuevo Proceso (PTI N° {{ ($pti->numero ?? 1) + 1 }})</strong>.
                                            @endif
                                        </div>
                                        <div class="mt-2 d-flex align-items-center" style="gap: .5rem; flex-wrap: wrap;">
                                            <a href="{{ route('titulacion.inicio.crear', $egresado->id) }}" class="btn btn-sm btn-primary py-1">
                                                <i class="fas fa-play mr-1"></i>Iniciar Nuevo PTI (PTI N° {{ ($pti->numero ?? 1) + 1 }})
                                            </a>
                                            @if ($puedeAmpliar)
                                                <a href="#{{ $idPaso('ampliacionpti') }}" class="btn btn-sm btn-outline-danger py-1" onclick="document.getElementById('{{ $idPaso('ampliacionpti') }}').open = true;">
                                                    <i class="fas fa-hourglass-half mr-1"></i>Registrar Ampliación (R.D.)
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <dl class="tp-datos">
                                <div class="tp-datos__ancho"><dt>Título</dt><dd>{{ $pti->titulo ?: '—' }}</dd></div>
                                <div><dt>Inscripción</dt><dd>{{ $pti->fecha_pti?->format('d/m/Y') ?? '—' }}</dd></div>
                                <div><dt>Vence</dt><dd>{{ $vigencia->conocida() ? $vigencia->vencimiento->format('d/m/Y') : '—' }}@if ($pti->vencimiento_ampliado) <span class="tp-mini">(ampliado)</span>@endif</dd></div>
                                <div><dt>PTI</dt><dd>N° {{ $pti->numero }} · {{ \App\Models\Titulacion\Pti::ETAPAS[$pti->etapa] ?? $pti->etapa }}</dd></div>
                            </dl>

                            <div class="tp-subtitulo">RD de aprobación del PTI</div>
                            @include('titulacion._rd_slot', [
                                'etiqueta' => 'RD de aprobación', 'res' => $rdDe('PTI'), 'plantilla' => 'aprobacion_pti',
                                'numeroCampo' => $pti->rd_pti, 'fechaCampo' => $avance->hitos['pti_aprobado']['fecha'],
                            ])
                            @include('titulacion._rd_pti_completar')

                            <div class="tp-subtitulo">Ampliaciones del PTI</div>
                            @forelse ($ampliaciones as $n => $amp)
                                @include('titulacion._rd_slot', ['etiqueta' => 'Ampliación '.($n + 1).($amp->detalle ? ' · '.\Illuminate\Support\Str::after($amp->detalle, 'ampliado ') : ''), 'res' => $amp, 'plantilla' => 'extension_plazo_pti'])
                            @empty
                                @unless ($ampliacionSoloCampo)
                                    <p class="small text-muted mb-1">Sin ampliaciones. Si se otorga una, regístrela abajo: es una RD nueva y la de aprobación se mantiene.</p>
                                @endunless
                            @endforelse
                            @if ($ampliacionSoloCampo)
                                @include('titulacion._rd_slot', ['etiqueta' => 'Ampliación', 'res' => null, 'plantilla' => 'extension_plazo_pti', 'tipoRd' => 'AmpliacionPti',
                                    'numeroCampo' => $pti->rd_ampliacion, 'fechaCampo' => $pti->fecha_ampliacion])
                            @endif
                            <details class="tp-mas ft-solo-editor" id="{{ $idPaso('ampliacionpti') }}">
                                <summary>Registrar una ampliación del PTI</summary>
                                <form method="POST" action="{{ route('titulacion.pti.ampliacion', $egresado->id) }}" enctype="multipart/form-data" class="tp-form mt-2">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.pti.ampliacion'))>
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group col-sm-4 mb-2"><label class="tp-label">N° de RD *</label><input type="text" name="numero_rd" class="form-control" required></div>
                                        <div class="form-group col-sm-4 mb-2"><label class="tp-label">Fecha de la RD</label><input type="date" name="fecha" class="form-control"></div>
                                        <div class="form-group col-sm-4 mb-2"><label class="tp-label">Nuevo vencimiento *</label><input type="date" name="vencimiento_ampliado" class="form-control" required></div>
                                        <div class="form-group col-sm-6 mb-2"><label class="tp-label">PDF de la RD</label><input type="file" name="pdf" accept="application/pdf" class="form-control-file"></div>
                                        <div class="form-group col-sm-6 mb-2"><label class="tp-label">…o enlace</label><input type="url" name="archivo" class="form-control" placeholder="https://drive.google.com/…"></div>
                                    </div>
                                    @include('titulacion._aplicar_grupo', ['idCasilla' => $idPaso('ampliacionpti').'-grupo'])
                                    <button type="submit" class="btn tp-btn"><i class="fas fa-hourglass-half mr-1"></i>Registrar ampliación</button>
                                </fieldset>
</form>
                            </details>

                            <details class="tp-mas">
                                <summary>{{ $ptiAprobado ? 'Fechas y estado del PTI' : 'Editar datos del PTI' }}</summary>
                                <div class="tp-form mt-2" id="ptidatos-{{ $egresado->id }}">
                                    @include('titulacion._form_pti', ['p' => $pti, 'aprobadoPorRd' => $ptiAprobado])
                                </div>
                            </details>
                            @if ($historialPti->count() > 1)
                                <details class="tp-mas">
                                    <summary>Historial de PTI ({{ $historialPti->count() }})</summary>
                                    @foreach ($historialPti as $hp)
                                        @continue($hp->id === $pti->id)
                                        <div class="ft-pti-historial__item">
                                            <strong>PTI N° {{ $hp->numero }}</strong> · {{ \App\Models\Titulacion\Pti::ETAPAS[$hp->etapa] ?? $hp->etapa }}
                                            · {{ $hp->estado === 'vencido' ? 'Vencido' : (filled($hp->rd_pti) ? 'Aprobado' : 'En proceso') }}
                                            <span class="text-muted">· {{ $hp->fecha_pti?->format('d/m/Y') ?? 'sin fecha' }} → {{ ($hp->vencimiento_ampliado ?? $hp->fecha_vencimiento)?->format('d/m/Y') ?? '—' }}</span>
                                            <details class="ft-desplegable mt-1"><summary>Editar PTI N° {{ $hp->numero }}</summary><div class="mt-2">@include('titulacion._form_pti', ['p' => $hp])</div></details>
                                        </div>
                                    @endforeach
                                </details>
                            @endif
                            <details class="tp-mas">
                                <summary>Registrar un nuevo PTI (el actual venció)</summary>
                                <p class="small text-muted mt-2">
                                    Si venció <strong>sin haber hecho el TI</strong>, el proceso vuelve a empezar con un PTI para Bachiller; si venció
                                    <strong>después del TI</strong>, el nuevo PTI es para la Licenciatura. Si hubo <strong>ampliación</strong>, no registre uno nuevo:
                                    regístrela arriba, en «Registrar una ampliación del PTI».
                                </p>
                                <form method="POST" action="{{ route('titulacion.pti.nuevo', $egresado->id) }}" class="tp-form"
                                      data-confirm="El PTI N° {{ $pti->numero }} quedará como vencido en el historial." data-confirm-titulo="¿Registrar el nuevo PTI?" data-confirm-boton="Sí, registrar">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.pti.nuevo'))>
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group col-sm-4 mb-2"><label class="tp-label">Para el grado de</label>
                                            <select name="etapa" class="form-control" required>
                                                @foreach (\App\Models\Titulacion\Pti::ETAPAS as $ce => $ne)
                                                    <option value="{{ $ce }}" @selected(($tiAprobado ? 'licenciatura' : 'bachiller') === $ce)>{{ $ne }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4 mb-2"><label class="tp-label">Fecha PTI</label><input type="date" name="fecha_pti" class="form-control"></div>
                                        <div class="form-group col-sm-4 mb-2"><label class="tp-label">Vencimiento</label><input type="date" name="fecha_vencimiento" class="form-control"></div>
                                        <div class="form-group col-12 mb-2"><label class="tp-label">Título</label><input type="text" name="titulo" class="form-control" value="{{ $pti->titulo }}"></div>
                                    </div>
                                    <button type="submit" class="btn tp-btn tp-btn--claro"><i class="fas fa-plus mr-1"></i>Registrar nuevo PTI</button>
                                </fieldset>
</form>
                            </details>
                        @endif
                        @break

                    @case('pti_carta')
                        @include('titulacion._paso_carta', ['id' => $idPaso('carta')])
                        @break

                    @case('ti_asesores')
                        @include('titulacion._participantes', ['lista' => $asesoresTi, 'funcion' => 'asesor', 'etapaP' => 'ti', 'idP' => $idPaso('asesores'), 'plano' => true,
                            'asesorPrevio' => $asesoresTi->isEmpty() ? ($pti?->asesorNombre() ?: $tesis?->asesorNombre()) : null])
                        <div class="tp-subtitulo mt-3">Resolución de cambio de asesor</div>
                        @include('titulacion._rd_slot', ['etiqueta' => 'RD de cambio de asesor', 'res' => $rdDe('CambioAsesor'), 'plantilla' => 'cambio_asesor', 'tabVolver' => 'proceso'])
                        @break

                    @case('ti_enlace')
                        @include('titulacion._paso_enlace', ['etapaEnlace' => 'ti', 'id' => $idPaso('linkti'), 'requiere' => 'pti_carta'])
                        @break

                    @case('ti_informe')
                        @php
                            $plazoIti = \App\Services\Titulacion\PasosProceso::plazoInforme($seguimiento);
                            $nivelPlazo = $plazoIti ? ($plazoIti['restantes'] < 0 ? 'vencido' : ($plazoIti['restantes'] <= 2 ? 'espera' : 'plazo')) : null;
                        @endphp
                        @if ($plazoIti && ! $plazoIti['entregado'])
                            <div class="tp-aviso tp-aviso--{{ $nivelPlazo }}"><i class="fas fa-stopwatch"></i><div>
                                <strong>{{ \App\Services\Titulacion\PasosProceso::textoPlazo($plazoIti) }}</strong> para que el asesor comparta su informe.
                                <span class="tp-mini">{{ \App\Services\Titulacion\PasosProceso::DIAS_INFORME_ITI }} días hábiles desde la aprobación del enlace del TI ({{ $plazoIti['desde']->format('d/m/Y') }}).</span>
                            </div></div>
                        @elseif (! $plazoIti && ! $pti?->link_iti)
                            <div class="tp-aviso"><i class="fas fa-stopwatch"></i><div>El asesor tiene <strong>{{ \App\Services\Titulacion\PasosProceso::DIAS_INFORME_ITI }} días hábiles</strong> para compartir su informe; el plazo empieza cuando aprueba el enlace del TI.</div></div>
                        @endif
                        @if ($pti?->link_iti)
                            <a href="{{ $pti->link_iti }}" target="_blank" rel="noopener noreferrer" class="tp-enlace" title="{{ $pti->link_iti }}">
                                <i class="fas fa-file-alt"></i>
                                <span>Abrir el informe del asesor <span class="tp-mini">{{ \Illuminate\Support\Str::limit($pti->link_iti, 60) }}</span></span>
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <div class="tp-enlace-estados">
                                @if ($pti->fecha_iti) <span class="tp-chip tp-chip--pendiente"><i class="fas fa-calendar-check"></i>Compartido el {{ $pti->fecha_iti->format('d/m/Y') }}</span> @endif
                                @if ($plazoIti && $plazoIti['a_tiempo'] !== null)
                                    <span class="tp-chip tp-chip--{{ $plazoIti['a_tiempo'] ? 'hecho' : 'vencido' }}">
                                        <i class="fas {{ $plazoIti['a_tiempo'] ? 'fa-check' : 'fa-exclamation-triangle' }}"></i>{{ $plazoIti['a_tiempo'] ? 'Dentro del plazo' : 'Fuera del plazo' }} (hasta el {{ $plazoIti['limite']->format('d/m/Y') }})
                                    </span>
                                @endif
                            </div>
                        @endif
                        <details class="tp-mas" @if (! $pti?->link_iti) open @endif>
                            <summary>{{ $pti?->link_iti ? 'Editar el enlace del informe' : 'Registrar el enlace del informe' }}</summary>
                            <form method="POST" action="{{ route('titulacion.ti.informe', $egresado->id) }}" class="tp-form mt-2" id="{{ $idPaso('informe') }}">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.ti.informe'))>
                                @csrf
                                <div class="form-row">
                                    <div class="form-group col-sm-8 mb-2">
                                        <label class="tp-label" for="{{ $idPaso('informe') }}-link">Enlace del informe</label>
                                        <input type="url" name="link_iti" id="{{ $idPaso('informe') }}-link" class="form-control" value="{{ $pti?->link_iti }}" placeholder="https://drive.google.com/…" required>
                                    </div>
                                    <div class="form-group col-sm-4 mb-2">
                                        <label class="tp-label" for="{{ $idPaso('informe') }}-fecha">Compartido el</label>
                                        <input type="date" name="fecha_iti" id="{{ $idPaso('informe') }}-fecha" class="form-control" value="{{ $pti?->fecha_iti?->format('Y-m-d') }}" title="Si lo deja vacío, se registra la fecha de hoy">
                                    </div>
                                </div>
                                @include('titulacion._aplicar_grupo', ['idCasilla' => $idPaso('informe').'-grupo'])
                                <button type="submit" class="btn tp-btn"><i class="fas fa-check mr-1"></i>{{ $pti?->link_iti ? 'Guardar cambios' : 'Guardar' }}</button>
                            </fieldset>
</form>
                        </details>
                        @break

                    @case('ti_jurado')
                        @include('titulacion._participantes', ['lista' => $juradoTi, 'funcion' => 'jurado', 'etapaP' => 'ti', 'idP' => $idPaso('jurado'), 'plano' => true, 'asesorPrevio' => null])
                        @include('titulacion._correo_jurado', ['jurado' => $juradoTi, 'idC' => $idPaso('correojurado')])
                        @break

                    @case('ti_expo')
                        @include('titulacion._paso_form', [
                            'id' => $idPaso('expo'), 'plano' => true, 'requiere' => 'pti_carta', 'titulo' => 'fecha de exposición', 'hecho' => (bool) $tesis?->fecha_exposicion,
                            'campos' => [['fecha_exposicion', 'Fecha de exposición', 'date', $tesis?->fecha_exposicion?->format('Y-m-d')]],
                        ])
                        @break

                    @case('ti_rd')
                        @include('titulacion._rd_slot', [
                            'etiqueta' => 'RD de aprobación de exposición', 'res' => $rdDe('Exposicion'), 'plantilla' => 'aprobacion_exposicion',
                            'numeroCampo' => $tesis?->rd_ti, 'fechaCampo' => $avance->hitos['ti_aprobado']['fecha'],
                        ])
                        @break

                    @case('ti_repo')
                        @if ($tesis?->url_repositorio_ti)
                            <a href="{{ $tesis->url_repositorio_ti }}" target="_blank" rel="noopener" class="tp-enlace"><i class="fas fa-book"></i><span>Ver el trabajo en el repositorio</span><i class="fas fa-external-link-alt"></i></a>
                        @endif
                        @include('titulacion._paso_form', [
                            'id' => $idPaso('repositorio'), 'plano' => true, 'requiere' => 'ti_aprobado', 'titulo' => 'enlace del TI en el repositorio', 'hecho' => filled($tesis?->url_repositorio_ti),
                            'campos' => [['url_repositorio_ti', 'Enlace en el repositorio', 'url', $tesis?->url_repositorio_ti, [], 'https://repositorio…']],
                        ])
                        @break

                    @case('ti_bachiller')
                        @include('titulacion._paso_form', [
                            'id' => $idPaso('bachiller'), 'plano' => true, 'requiere' => 'ti_repositorio', 'titulo' => 'Bachiller inscrito en SUNEDU', 'hecho' => (bool) ($tesis?->fecha_bachiller_sunedu || $tesis?->bachiller_sunedu),
                            'campos' => [
                                ['fecha_bachiller_sunedu', 'Fecha de inscripción', 'date', $tesis?->fecha_bachiller_sunedu?->format('Y-m-d')],
                                ['bachiller_sunedu', 'N° de registro SUNEDU', 'text', $tesis?->bachiller_sunedu],
                            ],
                            'nota' => 'Con el Bachiller termina el TI y se habilita la Tesis (Licenciatura).',
                        ])
                        @break

                    @case('tesis_dictamen')
                        {{-- Uno o más enlaces (un dictamen por dictaminante): filas que se agregan y quitan aquí mismo. --}}
                        <form method="POST" action="{{ route('titulacion.tesis.guardar', $egresado->id) }}" class="tp-form" id="{{ $idPaso('dictamen') }}">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.tesis.guardar'))>
                            @csrf
                            <input type="hidden" name="mensaje_ok" value="paso">
                            <input type="hidden" name="enlaces_dictamen_form" value="1">
                            <div class="tp-enlaces" data-tp-enlaces>
                                @foreach (array_values(($tesis?->enlaces_dictamen ?: []) ?: ['']) as $i => $enlace)
                                    <div class="tp-enlaces__fila">
                                        <span class="tp-enlaces__n">{{ $i + 1 }}</span>
                                        <input type="text" name="enlaces_dictamen[]" class="form-control" value="{{ $enlace }}" placeholder="https://drive.google.com/…" aria-label="Enlace del dictamen {{ $i + 1 }}">
                                        @if (filled($enlace))
                                            <a href="{{ $enlace }}" target="_blank" rel="noopener" class="tp-enlaces__ir" title="Abrir"><i class="fas fa-external-link-alt"></i></a>
                                        @endif
                                        <button type="button" class="tp-enlaces__quitar" data-tp-enlace-quitar title="Quitar este enlace" aria-label="Quitar"><i class="fas fa-times"></i></button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-link btn-sm px-0 mb-2" data-tp-enlace-agregar><i class="fas fa-plus mr-1"></i>Agregar otro enlace</button>
                            @include('titulacion._aplicar_grupo', ['idCasilla' => $idPaso('dictamen').'-grupo'])
                            <button type="submit" class="btn tp-btn"><i class="fas fa-check mr-1"></i>{{ empty($tesis?->enlaces_dictamen) ? 'Guardar' : 'Guardar cambios' }}</button>
                        </fieldset>
</form>
                        @break

                    @case('tesis_dictaminantes')
                        @include('titulacion._participantes', ['lista' => $dictaminantes, 'funcion' => 'jurado', 'etapaP' => 'tesis', 'idP' => $idPaso('dictaminantes'), 'plano' => true,
                            'asesorPrevio' => null, 'cargoFijo' => 'Dictaminante', 'nombreFuncion' => 'dictaminante'])
                        @include('titulacion._correo_jurado', ['jurado' => $dictaminantes, 'idC' => $idPaso('correodictamen'), 'motivo' => 'dictamen'])
                        @break

                    @case('tesis_fecha')
                        @include('titulacion._paso_form', [
                            'id' => $idPaso('sustentacion'), 'plano' => true, 'requiere' => 'bachiller', 'titulo' => 'fecha de sustentación', 'hecho' => (bool) $tesis?->fecha_sustentacion,
                            'campos' => [['fecha_sustentacion', 'Fecha de sustentación', 'date', $tesis?->fecha_sustentacion?->format('Y-m-d') ?? now()->toDateString()]],
                        ])
                        <div class="tp-subtitulo">Resolución que la programa</div>
                        @include('titulacion._rd_slot', ['etiqueta' => 'RD de fecha de sustentación', 'res' => $rdDe('FechaSustentacion'), 'plantilla' => 'fecha_sustentacion'])
                        @if ($rdExt = $rdDe('Extension'))
                            @include('titulacion._rd_slot', ['etiqueta' => 'Ampliación de sustentación', 'res' => $rdExt])
                        @elseif ($tesis?->rd_extension)
                            @include('titulacion._rd_slot', ['etiqueta' => 'Ampliación de sustentación', 'numeroCampo' => $tesis->rd_extension, 'fechaCampo' => $tesis->fecha_extension])
                        @endif
                        <details class="tp-mas">
                            <summary>Ampliación del plazo de sustentación</summary>
                            @include('titulacion._paso_form', [
                                'id' => $idPaso('ampliacion'), 'plano' => true, 'requiere' => 'bachiller', 'titulo' => 'ampliación del plazo de sustentación', 'hecho' => (bool) ($tesis?->rd_extension || $tesis?->fecha_extension),
                                'campos' => [['rd_extension', 'RD de ampliación', 'text', $tesis?->rd_extension], ['fecha_extension', 'Ampliado hasta', 'date', $tesis?->fecha_extension?->format('Y-m-d')]],
                            ])
                        </details>
                        @break

                    @case('tesis_jurado')
                        @include('titulacion._participantes', ['lista' => $juradoActo, 'funcion' => 'jurado', 'etapaP' => 'tesis', 'idP' => $idPaso('juradotesis'), 'plano' => true,
                            'asesorPrevio' => null, 'nombreFuncion' => 'integrante del jurado', 'cargosSugeridos' => $cargosActo])
                        <div class="small text-muted mt-2"><i class="fas fa-info-circle mr-1"></i>El acta con su número y promedio se registra en Historial → Actas y jurados.</div>
                        @include('titulacion._correo_jurado', ['jurado' => $juradoActo, 'idC' => $idPaso('correojuradotesis'), 'motivo' => 'sustentacion'])
                        @break

                    @case('tesis_rd')
                        @include('titulacion._paso_rd_tesis')
                        @break

                    @case('tesis_enlace')
                        @include('titulacion._paso_enlace', ['etapaEnlace' => 'tesis', 'id' => $idPaso('linktesis'), 'requiere' => 'bachiller'])
                        @break

                    @case('tesis_situacion')
                        @include('titulacion._paso_form', [
                            'id' => $idPaso('situacion'), 'plano' => true, 'titulo' => 'situación de la tesis', 'hecho' => $p['estado'] === 'hecho',
                            'campos' => [['estado', 'Situación', 'select', array_key_exists((string) $tesis?->estado, \App\Models\Titulacion\Tesis::SITUACIONES) ? $tesis->estado : 'pendiente', \App\Models\Titulacion\Tesis::SITUACIONES]],
                            'nota' => 'Es informativo: no detiene los pasos siguientes.',
                        ])
                        @break

                    @case('tesis_repo')
                        @if ($tesis?->url_repositorio)
                            <a href="{{ $tesis->url_repositorio }}" target="_blank" rel="noopener" class="tp-enlace"><i class="fas fa-book"></i><span>Ver la tesis en el repositorio</span><i class="fas fa-external-link-alt"></i></a>
                        @endif
                        @include('titulacion._paso_form', [
                            'id' => $idPaso('repotesis'), 'plano' => true, 'requiere' => 'tesis_sustentada', 'titulo' => 'enlace de la tesis en el repositorio', 'hecho' => filled($tesis?->url_repositorio),
                            'campos' => [['url_repositorio', 'URL en el repositorio', 'url', $tesis?->url_repositorio, [], 'https://repositorio…']],
                        ])
                        @break

                    @case('tesis_escuela')
                        @php $bloqueoEscuela = ! $tesis?->licenciatura_escuela ? \App\Services\Titulacion\ReglasProceso::falta($avance, 'tesis_aprobada') : null; @endphp
                        @if ($bloqueoEscuela)
                            <div class="ft-paso-bloqueado mt-2"><i class="fas fa-lock fa-xs mr-1"></i>Disponible cuando esté «{{ $bloqueoEscuela }}».</div>
                        @else
                            <form method="POST" action="{{ route('titulacion.tesis.guardar', $egresado->id) }}" class="tp-form" id="{{ $idPaso('escuela') }}">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.tesis.guardar'))>
                                @csrf
                                <input type="hidden" name="mensaje_ok" value="paso">
                                <input type="hidden" name="licenciatura_escuela" value="0">
                                <div class="form-row align-items-end">
                                    <div class="form-group col-sm-6 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="{{ $idPaso('escuela') }}-ok" name="licenciatura_escuela" value="1" @checked($tesis?->licenciatura_escuela)>
                                            <label class="custom-control-label" for="{{ $idPaso('escuela') }}-ok">Grado de Licenciatura <strong>aceptado por la Escuela</strong></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-6 mb-2">
                                        <label class="tp-label" for="{{ $idPaso('escuela') }}-fecha">Fecha</label>
                                        <input type="date" name="fecha_licenciatura_escuela" id="{{ $idPaso('escuela') }}-fecha" class="form-control"
                                               value="{{ $tesis?->fecha_licenciatura_escuela?->format('Y-m-d') ?? now()->toDateString() }}">
                                    </div>
                                </div>
                                @include('titulacion._aplicar_grupo', ['idCasilla' => $idPaso('escuela').'-grupo'])
                                <button type="submit" class="btn tp-btn"><i class="fas fa-check mr-1"></i>{{ $tesis?->licenciatura_escuela ? 'Guardar cambios' : 'Guardar' }}</button>
                            </fieldset>
</form>
                        @endif
                        @break

                    @case('tesis_licenciado')
                        @php $enSunedu = (bool) ($tesis?->fecha_titulo_sunedu || $tesis?->titulo_sunedu); @endphp
                        @include('titulacion._paso_form', [
                            'id' => $idPaso('licenciado'), 'plano' => true, 'requiere' => 'tesis_aprobada', 'titulo' => 'Licenciado en SUNEDU', 'hecho' => $enSunedu,
                            'campos' => [
                                ['licenciado_sunedu', 'Licenciado en SUNEDU', 'select', $enSunedu ? 'si' : 'pendiente', ['si' => 'Sí', 'pendiente' => 'Pendiente']],
                                ['fecha_titulo_sunedu', 'Fecha de inscripción (opcional)', 'date', $tesis?->fecha_titulo_sunedu?->format('Y-m-d')],
                                ['titulo_sunedu', 'N° de registro SUNEDU (opcional)', 'text', $tesis?->titulo_sunedu === 'Sí' ? null : $tesis?->titulo_sunedu],
                            ],
                            'nota' => '«Pendiente» borra el registro de SUNEDU.',
                        ])
                        @if (! $pasos->actual && $p['estado'] === 'hecho')
                            <div class="tp-vacio tp-vacio--fin"><i class="fas fa-graduation-cap"></i><p><strong>Proceso completo.</strong> Licenciatura inscrita en SUNEDU.</p></div>
                        @endif
                        @break
                @endswitch
            </div>
        @endforeach

        {{-- ── Accesos del pie: avisos, trámites y corrección de datos ── --}}
        @if (isset($extras['avisos']))
            <div class="tp-det" id="tp-avisos-{{ $egresado->id }}" data-tp-det="avisos" @if ($visible !== 'avisos') hidden @endif>
                <div class="tp-det__cab"><div><div class="tp-det__eyebrow">Plan de Trabajo de Investigación</div><h3 class="tp-det__titulo">Avisos de vencimiento</h3></div></div>
                @include('titulacion._avisos_pti')
            </div>
        @endif
        {{-- «Trámites extraordinarios» (cambio de tema/asesor, anulación) es ahora su propia pestaña: ver _show_contenido. --}}
        @if (isset($extras['corregir']))
            <div class="tp-det" id="tp-corregir-{{ $egresado->id }}" data-tp-det="corregir" @if ($visible !== 'corregir') hidden @endif>
                <div class="tp-det__cab"><div><div class="tp-det__eyebrow">Para datos importados con errores</div><h3 class="tp-det__titulo">Corregir datos de TI y Tesis</h3></div></div>
                <p class="tp-ayuda">Todos los datos del TI y la Tesis en un solo formulario. Para el día a día use los pasos de la izquierda.</p>
                @include('titulacion._form_tesis_completo')
            </div>
        @endif
    </section>
</div>
