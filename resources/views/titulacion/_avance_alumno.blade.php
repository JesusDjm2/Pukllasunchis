{{-- "Mi proceso de titulación": lo que ve el propio egresado/alumno (FID de
     Ciclo IX+ con proceso iniciado, o egresado). Solo lectura: línea de
     tiempo PTI → TI (Bachiller) → Tesis (Licenciatura) calculada de sus datos
     (AvanceTitulacion), su siguiente paso, la vigencia del PTI, su grupo, las
     guías por etapa (config/titulacion.php) y un aviso "algún dato no coincide"
     a los gestores. Se incluye en la intranet de alumno FID y en /egresado.
     Espera $seguimiento. --}}
@php
    use App\Services\Titulacion\AvanceTitulacion;
    use App\Services\Titulacion\GrupoTitulacion;
    use App\Services\Titulacion\VigenciaPti;
    use App\Services\Titulacion\Expediente;

    $seguimiento->loadMissing(['pti.asesor', 'pti.asesorExterno', 'ptis', 'tesis.asesor', 'tesis.asesorExterno', 'actas', 'resoluciones']);
    $pti = $seguimiento->pti;
    $tesis = $seguimiento->tesis;
    $fmt = fn ($f) => $f ? $f->format('d/m/Y') : null;

    $avance = $seguimiento->avance();
    $estadoClave = $avance->estadoClave();
    $lineaTiempo = $avance->lineaDeTiempo();
    $siguiente = $avance->siguientePaso();
    $guias = config('titulacion.guias', []);
    $companeros = GrupoTitulacion::companeros($seguimiento);

    // Vigencia del PTI: importa mientras falte llegar a la Tesis aprobada.
    $vigencia = new VigenciaPti($pti);
    $mostrarVigencia = $vigencia->conocida()
        && in_array($estadoClave, ['pti_en_proceso', 'pti_aprobado', 'ti_aprobado', 'bachiller'], true);
    $extensionHasta = $tesis?->fecha_extension;
    $conExtension = $mostrarVigencia && $extensionHasta && $extensionHasta->gt($vigencia->vencimiento);
    $venceEl = $conExtension ? $extensionHasta : $vigencia->vencimiento;
    $ptiVencido = $mostrarVigencia && ($venceEl->copy()->endOfDay()->isPast() || $pti?->estado === 'vencido');
    $avanceVigencia = ($mostrarVigencia && $vigencia->inscripcion && $venceEl->gt($vigencia->inscripcion))
        ? (int) round(min(100, max(0, $vigencia->inscripcion->diffInDays(now()) / $vigencia->inscripcion->diffInDays($venceEl) * 100)))
        : null;
    $quedan = $mostrarVigencia && ! $ptiVencido ? (int) now()->startOfDay()->diffInMonths($venceEl, false) : null;

    $otrasRd = $seguimiento->resoluciones->whereNotIn('tipo', GrupoTitulacion::TIPOS_RD_GRUPALES)
        ->filter(fn ($r) => filled($r->numero_rd));
    $nombreRd = \App\Models\Titulacion\Resolucion::TIPOS;

    // Dueño de la ficha (para la pestaña "Mi grupo").
    $persona = $seguimiento->user()->with("seguimientoTitulacion.estadoTitulacion")->first(["id", "name", "apellidos", "email", "dni", "telefono", "foto", "programa_id"]);
    $tabId = 'mt'.$seguimiento->id;
@endphp

<div class="card shadow-sm mb-3 mt-card" id="titulacion">
    <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem;">
        <span class="font-weight-bold">
            <i class="fas fa-user-graduate mr-2 text-primary"></i>Mi proceso de titulación
            @if ($seguimiento->numero_expediente)
                <span class="text-muted small font-weight-normal ml-1">· Expediente {{ Expediente::mostrar($seguimiento->numero_expediente) }}</span>
            @endif
        </span>
        <span class="badge badge-{{ AvanceTitulacion::badge($estadoClave) }}">{{ $avance->estadoNombre() }}</span>
    </div>

    <div class="card-body">
        @if (session('titulacion_reporte'))
            <div class="alert alert-success small py-2"><i class="fas fa-check-circle mr-1"></i>{{ session('titulacion_reporte') }}</div>
        @endif
        @if (session('titulacion_reporte_error'))
            <div class="alert alert-warning small py-2">{{ session('titulacion_reporte_error') }}</div>
        @endif

        {{-- Pestañas solo con CSS (radios): /egresado no carga el JS de Bootstrap. --}}
        <div class="mt-tabs">
            <input type="radio" class="mt-tabs__radio" name="{{ $tabId }}" id="{{ $tabId }}-avance" value="avance" checked>
            <input type="radio" class="mt-tabs__radio" name="{{ $tabId }}" id="{{ $tabId }}-datos" value="datos">
            <input type="radio" class="mt-tabs__radio" name="{{ $tabId }}" id="{{ $tabId }}-grupo" value="grupo">
            <div class="mt-tabs__nav" role="tablist">
                <label for="{{ $tabId }}-avance" class="mt-tabs__tab mt-tabs__tab--avance"><i class="fas fa-route mr-1"></i>Mi avance</label>
                <label for="{{ $tabId }}-datos" class="mt-tabs__tab mt-tabs__tab--datos"><i class="fas fa-id-card mr-1"></i>Mis datos</label>
                <label for="{{ $tabId }}-grupo" class="mt-tabs__tab mt-tabs__tab--grupo">
                    <i class="fas fa-users mr-1"></i>Mi grupo
                    @if ($companeros->isNotEmpty()) <span class="mt-tabs__count">{{ $companeros->count() + 1 }}</span> @endif
                </label>
            </div>

        <div class="mt-tabs__panel mt-tabs__panel--avance">
        @include('titulacion._stepper')

        {{-- ── Siguiente paso ── --}}
        @if ($siguiente)
            <div class="mt-siguiente">
                <div class="mt-siguiente__icono"><i class="fas fa-flag"></i></div>
                <div class="flex-grow-1">
                    <div class="mt-siguiente__kicker">Tu siguiente paso</div>
                    <div class="mt-siguiente__texto">{{ $siguiente['falta'] }}</div>
                    <div class="small text-muted">Etapa: {{ AvanceTitulacion::ETAPAS[$siguiente['etapa']] }}</div>
                </div>
                @if (! empty($guias[$siguiente['etapa']]))
                    <a href="{{ $guias[$siguiente['etapa']] }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary align-self-center">
                        <i class="fas fa-book-open mr-1"></i>Ver guía
                    </a>
                @endif
            </div>
        @else
            <div class="mt-siguiente mt-siguiente--completo">
                <div class="mt-siguiente__icono"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <div class="mt-siguiente__kicker">¡Felicitaciones!</div>
                    <div class="mt-siguiente__texto">Completaste tu proceso: tu título de Licenciado está inscrito en SUNEDU.</div>
                </div>
            </div>
        @endif

        {{-- ── Qué hacer ahora: solo los trámites del tramo en curso ── --}}
        @include('titulacion._tramites_ayuda', ['bloque' => \App\Services\Titulacion\GuiaTramites::actual($avance), 'clavePersona' => 's'.$seguimiento->id])

        {{-- ── Vigencia del PTI ── --}}
        @if ($mostrarVigencia)
            <div class="mt-vigencia {{ $ptiVencido ? 'mt-vigencia--vencido' : ($quedan !== null && $quedan <= VigenciaPti::MESES_AVISO_URGENTE ? 'mt-vigencia--urgente' : '') }}">
                <div class="d-flex justify-content-between flex-wrap small" style="gap:.25rem;">
                    <span class="font-weight-bold"><i class="far fa-clock mr-1"></i>Vigencia de tu PTI</span>
                    <span>
                        @if ($ptiVencido)
                            <strong>Venció el {{ $fmt($venceEl) }}</strong>
                        @else
                            Vence el <strong>{{ $fmt($venceEl) }}</strong>
                            @if ($conExtension) <span class="text-muted">(con extensión)</span> @endif
                            · quedan <strong>{{ $conExtension ? ($quedan.' '.($quedan === 1 ? 'mes' : 'meses')) : $vigencia->tiempoRestanteTexto() }}</strong>
                        @endif
                    </span>
                </div>
                @if ($avanceVigencia !== null)
                    <div class="progress mt-2" style="height:6px;" title="Tiempo transcurrido desde tu inscripción ({{ $fmt($vigencia->inscripcion) }})">
                        <div class="progress-bar" role="progressbar" style="width: {{ $ptiVencido ? 100 : $avanceVigencia }}%"></div>
                    </div>
                @endif
                <div class="small mt-2">
                    @if ($ptiVencido)
                        Comunícate con la coordinación de titulación para ver cómo continuar (por ejemplo, una extensión).
                    @else
                        Tu PTI tiene una vigencia de {{ VigenciaPti::ANIOS_VIGENCIA }} años para completar tu investigación.
                    @endif
                </div>
            </div>
        @endif

        {{-- ── Línea de tiempo ── --}}
        <div class="mt-timeline">
            @foreach ($lineaTiempo as $clave => $etapa)
                <div class="mt-etapa mt-etapa--{{ $etapa['estado'] }}">
                    <div class="mt-etapa__marca">
                        {{ $loop->iteration }}
                    </div>
                    <div class="mt-etapa__cuerpo">
                        <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.25rem;">
                            <div class="mt-etapa__titulo">{{ $etapa['titulo'] }}</div>
                            <span class="mt-etapa__chip">
                                {{ ['completo' => 'Completado', 'en_proceso' => 'En curso', 'pendiente' => 'Por venir'][$etapa['estado']] }}
                            </span>
                        </div>

                        {{-- Datos del trabajo de esta etapa --}}
                        @if ($clave === 'pti' && $pti && ($pti->titulo || $pti->asesorNombre()))
                            <div class="mt-etapa__datos">
                                @if ($pti->titulo) <div class="font-weight-bold">“{{ $pti->titulo }}”</div> @endif
                                @if ($pti->asesorNombre()) <div>Asesor(a): {{ $pti->asesorNombre() }}</div> @endif
                            </div>
                        @elseif ($clave === 'tesis' && $tesis && ($tesis->titulo || $tesis->asesorNombre() || $tesis->url_repositorio))
                            <div class="mt-etapa__datos">
                                @if ($tesis->titulo) <div class="font-weight-bold">“{{ $tesis->titulo }}”</div> @endif
                                @if ($tesis->asesorNombre()) <div>Asesor(a): {{ $tesis->asesorNombre() }}</div> @endif
                                @if ($tesis->url_repositorio)
                                    <a href="{{ $tesis->url_repositorio }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt fa-xs mr-1"></i>Ver en el repositorio</a>
                                @endif
                            </div>
                        @endif

                        <ul class="mt-hitos">
                            @foreach ($etapa['hitos'] as $hito)
                                <li class="mt-hito mt-hito--{{ $hito['situacion'] }}">
                                    <i class="mt-hito__icono fas {{ ['logrado' => 'fa-check-circle', 'actual' => 'fa-arrow-circle-right', 'pendiente' => 'fa-circle', 'sin_registro' => 'fa-minus-circle'][$hito['situacion']] }}"></i>
                                    <span class="mt-hito__label">{{ $hito['label'] }}</span>
                                    @if ($hito['logrado'])
                                        <span class="mt-hito__meta">
                                            {{ collect([$hito['detalle'], $fmt($hito['fecha'])])->filter()->implode(' · ') }}
                                        </span>
                                        @if ($hito['enlace'])
                                            <a href="{{ $hito['enlace'] }}" target="_blank" rel="noopener" class="mt-hito__pdf"><i class="fas fa-file-pdf"></i> Ver RD</a>
                                        @endif
                                    @elseif ($hito['situacion'] === 'actual')
                                        <span class="mt-hito__meta mt-hito__meta--actual">Siguiente paso</span>
                                    @elseif ($hito['situacion'] === 'sin_registro')
                                        <span class="mt-hito__meta" title="Ya avanzaste a una etapa posterior; este dato simplemente no está registrado.">Sin registro</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        {{-- Solo cuando hay guía cargada (config/titulacion.php): un "próximamente"
                             repetido en cada etapa se veía como algo sin terminar. --}}
                        @if (! empty($guias[$clave]))
                            <div class="mt-etapa__guia">
                                <a href="{{ $guias[$clave] }}" target="_blank" rel="noopener"><i class="fas fa-book-open mr-1"></i>Guía de esta etapa</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Otras resoluciones (extensión, cambio de asesor) ── --}}
        @if ($otrasRd->isNotEmpty())
            <div class="small mt-2">
                <div class="mt-subtitulo"><i class="fas fa-gavel mr-1"></i>Otras resoluciones</div>
                @foreach ($otrasRd as $rd)
                    <div>
                        {{ $nombreRd[$rd->tipo] ?? $rd->tipo }}: {{ $rd->numero_rd }} @if ($rd->fecha) <span class="text-muted">({{ $fmt($rd->fecha) }})</span> @endif
                        @if ($rd->archivo) <a href="{{ $rd->archivo }}" target="_blank" rel="noopener" class="ml-1"><i class="fas fa-file-pdf"></i> Ver RD</a> @endif
                    </div>
                @endforeach
            </div>
        @endif
        {{-- ── A quién acudir ── --}}
        @php
            $asesorActual = $tesis?->asesorNombre() ?: $pti?->asesorNombre();
        @endphp
        <div class="mt-contacto mt-3">
            <div class="mt-subtitulo"><i class="fas fa-hands-helping mr-1"></i>¿Dudas sobre tu proceso?</div>
            <div class="mt-contacto__grid">
                <div>
                    <div class="mt-contacto__eti">Tu asesor(a)</div>
                    <div>{{ $asesorActual ?: 'Aún no tienes asesor(a) asignado(a).' }}</div>
                </div>
                <div>
                    <div class="mt-contacto__eti">Unidad de Investigación</div>
                    <div><i class="fab fa-whatsapp mr-1"></i><a href="https://wa.me/51972577170" target="_blank" rel="noopener" title="Escribir por WhatsApp">+51 972 577 170</a></div>
                    <div><i class="fas fa-map-marker-alt fa-xs mr-1"></i>Siete Diablitos 222, San Blas, Cusco</div>
                    <div><i class="fas fa-globe fa-xs mr-1"></i><a href="https://www.eesppukllasunchis.edu.pe" target="_blank" rel="noopener">www.eesppukllasunchis.edu.pe</a></div>
                </div>
            </div>
        </div>
        </div>{{-- /panel avance --}}

        {{-- ── Mis datos ── --}}
        <div class="mt-tabs__panel mt-tabs__panel--datos">
            @include('titulacion._datos_personales')
            <div class="small text-muted"><i class="fas fa-info-circle mr-1"></i>Estos datos vienen de tu ficha en el sistema. Si alguno no es correcto, avísanos con el enlace de abajo.</div>
        </div>

        {{-- ── Mi grupo ── --}}
        <div class="mt-tabs__panel mt-tabs__panel--grupo">
            @include('titulacion._grupo', ['persona' => $persona, 'companeros' => $companeros])
        </div>
        </div>{{-- /mt-tabs --}}

        {{-- ── ¿Algún dato no coincide? ── <details> para que funcione igual en
             /egresado (página sin JS de Bootstrap) y en la intranet de alumno. --}}
        <details class="mt-reporte" @if ($errors->has('mensaje')) open @endif>
            <summary><i class="fas fa-exclamation-circle mr-1"></i>¿Algún dato no coincide?</summary>
            <form method="POST" action="{{ route('mi-titulacion.reportar') }}" class="mt-2">
                @csrf
                <textarea name="mensaje" rows="3" maxlength="1000" required minlength="10"
                          class="form-control form-control-sm @error('mensaje') is-invalid @enderror"
                          placeholder="Cuéntanos qué dato no es correcto y cuál es el correcto (por ejemplo: el N° de RD de mi TI es 0245-2024).">{{ old('mensaje') }}</textarea>
                @error('mensaje') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <button type="submit" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="fas fa-paper-plane mr-1"></i>Enviar a la coordinación de titulación
                </button>
            </form>
        </details>
    </div>
</div>

@include('titulacion._tabs_estilos')

@once
    <style>
        .mt-siguiente { display: flex; gap: .85rem; padding: .85rem 1rem; border-radius: .6rem; background: #eef2ff; border: 1px solid #c7d2fe; margin-bottom: 1rem; flex-wrap: wrap; }
        .mt-siguiente__icono { width: 2.2rem; height: 2.2rem; border-radius: 50%; background: #4e73df; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .mt-siguiente__kicker { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #4e73df; }
        .mt-siguiente__texto { font-weight: 700; color: #1e293b; }
        .mt-siguiente--completo { background: #e8f8f1; border-color: #a7e3c8; }
        .mt-siguiente--completo .mt-siguiente__icono { background: #1cc88a; }
        .mt-siguiente--completo .mt-siguiente__kicker { color: #13855c; }

        .mt-vigencia { padding: .75rem 1rem; border-radius: .6rem; border: 1px solid #e3e6f0; background: #f8f9fc; margin-bottom: 1rem; }
        .mt-vigencia .progress-bar { background: #4e73df; }
        .mt-vigencia--urgente { background: #fff8e6; border-color: #f6c23e; }
        .mt-vigencia--urgente .progress-bar { background: #f6c23e; }
        .mt-vigencia--vencido { background: #fdecea; border-color: #e74a3b; }
        .mt-vigencia--vencido .progress-bar { background: #e74a3b; }

        .mt-timeline { position: relative; }
        .mt-etapa { display: flex; gap: .85rem; position: relative; padding-bottom: 1.1rem; }
        .mt-etapa:not(:last-child)::before { content: ''; position: absolute; left: 1rem; top: 2.1rem; bottom: 0; width: 2px; background: #e3e6f0; transform: translateX(-50%); }
        .mt-etapa--completo:not(:last-child)::before { background: #1cc88a; }
        .mt-etapa__marca { width: 2rem; height: 2rem; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; background: #e9ecef; color: #6c757d; border: 2px solid #e9ecef; position: relative; z-index: 1; }
        .mt-etapa--en_proceso .mt-etapa__marca { background: #eef2ff; color: #4e73df; border-color: #4e73df; }
        .mt-etapa--completo .mt-etapa__marca { background: #1cc88a; color: #fff; border-color: #1cc88a; }
        .mt-etapa__cuerpo { flex: 1; min-width: 0; padding-top: .2rem; }
        .mt-etapa__titulo { font-weight: 700; color: #1e293b; }
        .mt-etapa--pendiente .mt-etapa__titulo { color: #8892a6; }
        .mt-etapa__chip { font-size: .68rem; font-weight: 700; padding: .1rem .5rem; border-radius: 999px; background: #e9ecef; color: #6c757d; white-space: nowrap; }
        .mt-etapa--en_proceso .mt-etapa__chip { background: #eef2ff; color: #4e73df; }
        .mt-etapa--completo .mt-etapa__chip { background: #e8f8f1; color: #13855c; }
        .mt-etapa__datos { font-size: .82rem; margin-top: .35rem; color: #374151; }
        .mt-hitos { list-style: none; padding: 0; margin: .5rem 0 .35rem; }
        .mt-hito { display: flex; align-items: baseline; gap: .45rem; font-size: .83rem; padding: .12rem 0; flex-wrap: wrap; }
        .mt-hito__icono { font-size: .8rem; width: .9rem; text-align: center; }
        .mt-hito--logrado .mt-hito__icono { color: #1cc88a; }
        .mt-hito--actual .mt-hito__icono { color: #4e73df; }
        .mt-hito--actual .mt-hito__label { font-weight: 700; color: #4e73df; }
        .mt-hito--pendiente .mt-hito__icono { color: #d1d5db; font-size: .55rem; }
        .mt-hito--pendiente .mt-hito__label { color: #8892a6; }
        .mt-hito--sin_registro .mt-hito__icono { color: #c4c9d4; }
        .mt-hito--sin_registro .mt-hito__label { color: #8892a6; }
        .mt-hito__meta { font-size: .75rem; color: #6c757d; }
        .mt-hito__meta--actual { color: #4e73df; font-weight: 600; }
        .mt-contacto { padding: .75rem .9rem; border: 1px solid #e3e6f0; border-radius: .6rem; background: #f8f9fc; font-size: .85rem; }
        .mt-contacto__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: .75rem; }
        .mt-contacto__eti { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #8892a6; margin-bottom: .15rem; }
        .dark-mode .mt-contacto, .dim-mode .mt-contacto { background: rgba(255, 255, 255, .03); border-color: rgba(255, 255, 255, .12); }
        .mt-etapa__guia { font-size: .78rem; }

        .mt-hito__pdf { font-size: .75rem; font-weight: 600; white-space: nowrap; }

        .mt-reporte { margin-top: .75rem; padding-top: .75rem; border-top: 1px dashed #e3e6f0; font-size: .85rem; }
        .mt-reporte summary { cursor: pointer; color: #4e73df; font-weight: 600; outline: none; }

        .dark-mode .mt-card .mt-siguiente, .dim-mode .mt-card .mt-siguiente { background: rgba(78, 115, 223, .14); border-color: rgba(78, 115, 223, .35); }
        .dark-mode .mt-card .mt-siguiente__texto, .dim-mode .mt-card .mt-siguiente__texto,
        .dark-mode .mt-card .mt-etapa__titulo, .dim-mode .mt-card .mt-etapa__titulo,
        .dark-mode .mt-card .mt-etapa__datos, .dim-mode .mt-card .mt-etapa__datos { color: inherit; }
        .dark-mode .mt-card .mt-vigencia, .dim-mode .mt-card .mt-vigencia { background: rgba(255, 255, 255, .04); border-color: rgba(255, 255, 255, .12); }
    </style>
@endonce
