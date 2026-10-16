{{-- Ficha de una persona en proceso de titulación para gestores (super-admin,
     gestor-titulacion), en 3 pestañas: Proceso (PTI → TI → Tesis con sus RD y
     acciones), Datos y grupo, e Historial (todas las RD, actas y documentos).
     Compartida entre la página completa (titulacion.show, acceso directo) y el
     acordeón de cada fila en /titulacion. $enModal quita el padding/borde de página
     completa; $enAcordeon además oculta el encabezado con el nombre (ya está en la
     fila). Pestañas solo con CSS (ver _tabs_estilos). Al volver de guardar
     (?ver={id}&tab=…) se abre la pestaña donde se trabajaba; las claves antiguas
     (pti, tesis, actas, resoluciones, grupo…) abren la pestaña que las contiene.
     Espera $egresado, $seguimiento, $docentes; opcional $integrantes. --}}
@php
    use App\Services\Titulacion\AvanceTitulacion;
    use App\Services\Titulacion\VigenciaPti;

    $enModal = $enModal ?? false;
    $enAcordeon = $enAcordeon ?? false;
    $integrantes = collect($integrantes ?? []);

    $pti = $seguimiento->pti;
    $tesis = $seguimiento->tesis;
    $avance = $seguimiento->avance();
    $estadoClave = $avance->estadoClave();
    $siguiente = $avance->siguientePaso();
    $etapasAvance = $avance->etapas();
    $vigencia = new VigenciaPti($pti);
    $sugerido = $vigencia->avisoSugerido();

    // Solo la ficha que se acaba de editar muestra mensajes y abre otra pestaña.
    $fichaActiva = ! $enAcordeon || (int) request('ver') === $egresado->id;
    $pestanas = ['proceso', 'datos', 'historial'];
    $tabPedida = [
        'resumen' => 'proceso', 'pti' => 'proceso', 'avisos' => 'proceso', 'tesis' => 'proceso',
        'grupo' => 'datos', 'actas' => 'historial', 'resoluciones' => 'historial',
    ][request('tab')] ?? request('tab');
    $tabActiva = $fichaActiva && in_array($tabPedida, $pestanas, true) ? $tabPedida : 'proceso';
    $tabId = 'ft'.$egresado->id;

    // Datos que faltan registrar o requieren atención (pestaña Proceso).
    $alertas = [];
    foreach ($avance->lineaDeTiempo() as $etapa) {
        foreach ($etapa['hitos'] as $hito) {
            if ($hito['situacion'] === 'sin_registro') {
                $alertas[] = ['warning', 'Falta registrar: <strong>'.e($hito['label']).'</strong> (ya hay un paso posterior logrado).', 'proceso'];
            }
        }
    }
    if ($vigencia->conocida() && in_array($estadoClave, ['pti_en_proceso', 'pti_aprobado', 'ti_aprobado', 'bachiller'], true)) {
        if ($vigencia->vencido() || $pti?->estado === 'vencido') {
            $alertas[] = ['danger', 'El PTI <strong>venció</strong> el '.$vigencia->vencimiento->format('d/m/Y').'.', 'proceso'];
        } elseif ($sugerido === 'urgente') {
            $alertas[] = ['danger', 'El PTI vence el <strong>'.$vigencia->vencimiento->format('d/m/Y').'</strong> (quedan '.e($vigencia->tiempoRestanteTexto()).').', 'proceso'];
        }
    }
    if (blank($seguimiento->numero_expediente)) {
        $alertas[] = ['secondary', 'Sin N° de expediente.', 'datos'];
    }
    if ($pti && ! $pti->asesor_id && ! $pti->asesor_externo_id) {
        $alertas[] = ['secondary', 'El PTI no tiene asesor(a) asignado(a).', 'proceso'];
    }
    $rdSinPdf = $seguimiento->resoluciones->filter(fn ($r) => filled($r->numero_rd) && blank($r->archivo))->count();
    if ($rdSinPdf) {
        $alertas[] = ['secondary', $rdSinPdf.' resolución(es) sin su PDF.', 'historial'];
    }

    // Última RD registrada de un tipo (la del casillero de cada etapa).
    $rdDe = fn (string $tipo) => $seguimiento->resoluciones
        ->where('tipo', $tipo)
        ->filter(fn ($r) => filled($r->numero_rd))
        ->sortByDesc(fn ($r) => ($r->fecha?->format('Y-m-d') ?? '0000').'|'.str_pad($r->id, 9, '0', STR_PAD_LEFT))
        ->first();
    // Campo del formulario completo de TI/Tesis: vacío y con el paso anterior pendiente → deshabilitado
    // (ReglasProceso::REQUISITO_CAMPO). Lo ya registrado se puede corregir siempre.
    $bloqCampo = function (string $campo) use ($tesis, $avance) {
        if (filled($tesis?->{$campo})) {
            return '';
        }
        $falta = \App\Services\Titulacion\ReglasProceso::falta($avance, \App\Services\Titulacion\ReglasProceso::REQUISITO_CAMPO[$campo] ?? null);

        return $falta ? 'disabled title="'.e('Disponible cuando esté: '.$falta).'"' : '';
    };
    $estadoEtapa = fn (string $clave) => [
        'completo' => ['Completo', 'success'], 'en_proceso' => ['En proceso', 'primary'], 'pendiente' => ['Pendiente', 'light border'],
    ][$etapasAvance[$clave]['estado']] ?? ['—', 'light border'];

    // Selects de docentes livianos: solo traen la opción elegida; la lista
    // completa (una sola copia por página, <template>) se carga al enfocarlos.
    $docentesPorId = $docentes->keyBy('id');
    $asesores = collect($asesores ?? []);
    $asesoresPorId = $asesores->keyBy('id');
    $selectDocente = function (string $name, ?string $valor, string $vacio = '—', bool $requerido = false) use ($docentesPorId, $asesoresPorId) {
        $opciones = '<option value="">'.e($vacio).'</option>';
        if (filled($valor)) {
            if (str_starts_with($valor, 'a')) {
                $a = $asesoresPorId[(int) substr($valor, 1)] ?? null;
                $texto = $a ? $a->nombreCompleto().' (asesor/a registrado/a)' : 'Asesor/a #'.$valor;
            } else {
                $d = $docentesPorId[(int) $valor] ?? null;
                $texto = $d ? $d->name.' '.$d->apellidos : 'Docente #'.$valor;
            }
            $opciones .= '<option value="'.e($valor).'" selected>'.e($texto).'</option>';
        }

        return '<select name="'.$name.'" class="form-control form-control-sm js-docentes" data-vacio="'.e($vacio).'" data-sel="'.e($valor ?? '').'"'
            .($requerido ? ' required' : '').'>'.$opciones.'</select>';
    };
@endphp

<div class="{{ $enModal ? '' : 'container-fluid bg-white' }}">
    @unless ($enAcordeon)
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 {{ $enModal ? '' : 'pt-3 pb-3' }}"
            style="{{ $enModal ? '' : 'border-bottom: 1px dashed #80808078' }}">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-user-graduate mr-2" style="color:#4e73df;"></i>{{ $egresado->apellidos }}, {{ $egresado->name }}
                </h4>
                <small class="text-muted">{{ $egresado->programa->nombre ?? 'Sin programa' }} @if($egresado->ciclo) &middot; {{ $egresado->ciclo->nombre }} @endif</small>
            </div>
            @unless ($enModal)
                <a href="{{ route('titulacion.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left fa-sm mr-1"></i>Volver al listado</a>
            @endunless
        </div>
    @endunless

    @if ($fichaActiva)
        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show {{ Session::has('rd_pdf') ? '' : 'js-flash-toast' }}" role="alert" data-toast="{{ Session::get('success') }}">
                {{ Session::get('success') }}
                @if (Session::has('rd_pdf'))
                    <a href="{{ Session::get('rd_pdf') }}" target="_blank" rel="noopener" class="alert-link ml-1"><i class="fas fa-file-pdf mr-1"></i>Abrir el PDF emitido</a>
                @endif
                <a type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            </div>
        @endif
        @if (Session::has('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ Session::get('error') }}
                <a type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    <div class="mt-tabs ft-ficha">
        @foreach ($pestanas as $clave)
            <input type="radio" class="mt-tabs__radio" name="{{ $tabId }}" id="{{ $tabId }}-{{ $clave }}" value="{{ $clave }}" @checked($clave === $tabActiva)>
        @endforeach
        <div class="mt-tabs__nav" role="tablist">
            <label for="{{ $tabId }}-proceso" class="mt-tabs__tab mt-tabs__tab--proceso" title="PTI → TI → Tesis, con sus resoluciones">
                <i class="fas fa-route mr-1"></i>Proceso
                @if (count($alertas)) <span class="mt-tabs__count mt-tabs__count--alerta" title="Datos por revisar">{{ count($alertas) }}</span> @endif
            </label>
            <label for="{{ $tabId }}-datos" class="mt-tabs__tab mt-tabs__tab--datos" title="Datos personales, expediente y grupo">
                <i class="fas fa-id-card mr-1"></i>Datos y grupo
                @if ($integrantes->isNotEmpty()) <span class="mt-tabs__count" title="Integrantes del grupo">{{ $integrantes->count() + 1 }}</span> @endif
            </label>
            <label for="{{ $tabId }}-historial" class="mt-tabs__tab mt-tabs__tab--historial" title="Todas las resoluciones, actas, jurados y documentos">
                <i class="fas fa-history mr-1"></i>Historial
                @if ($seguimiento->resoluciones->isNotEmpty() || $seguimiento->actas->isNotEmpty())
                    <span class="mt-tabs__count">{{ $seguimiento->resoluciones->count() + $seguimiento->actas->count() }}</span>
                @endif
            </label>
        </div>

        {{-- ═══ Proceso: PTI → TI → Tesis ═══ --}}
        <div class="mt-tabs__panel mt-tabs__panel--proceso">
            @php
                // Qué hacer con el siguiente paso: abrir la acción corta de su etapa o generar su RD.
                $idPaso = fn (string $paso) => 'acc-'.$paso.'-'.$egresado->id;
                $accionSiguiente = match ($siguiente['clave'] ?? null) {
                    'pti_inscrito' => ['abrir', 'ptidatos-'.$egresado->id, 'Registrar datos del PTI', 'fa-pen'],
                    'pti_aprobado' => ['rd', 'aprobacion_pti', 'Generar RD de aprobación del PTI', 'fa-file-signature'],
                    'ti_expuesto' => ['abrir', $idPaso('expo'), 'Registrar exposición', 'fa-chalkboard-teacher'],
                    'ti_aprobado' => ['rd', 'aprobacion_exposicion', 'Generar RD de aprobación del TI', 'fa-file-signature'],
                    'bachiller' => ['abrir', $idPaso('bachiller'), 'Registrar Bachiller (SUNEDU)', 'fa-award'],
                    'tesis_sustentada' => ['abrir', $idPaso('sustentacion'), 'Registrar sustentación', 'fa-chalkboard-teacher'],
                    'tesis_aprobada' => ['rd', 'aprobacion_sustentacion', 'Generar RD de aprobación de la Tesis', 'fa-file-signature'],
                    'licenciado' => ['abrir', $idPaso('licenciado'), 'Registrar Licenciatura (SUNEDU)', 'fa-graduation-cap'],
                    default => null,
                };
                if ($accionSiguiente && $accionSiguiente[0] === 'rd' && ! \App\Services\Titulacion\PlantillasRd::disponible($accionSiguiente[1])) {
                    $accionSiguiente = null;
                }
            @endphp
            <div class="d-flex flex-wrap align-items-center small mb-2" style="gap:.5rem;">
                <span>Estado:</span>
                <span class="badge badge-{{ AvanceTitulacion::badge($estadoClave) }}" style="font-size:.8rem;">{{ $avance->estadoNombre() }}</span>
                <span class="text-muted ml-auto" title="Se calcula solo: toda RD es aprobación y los grados cuentan desde su inscripción en SUNEDU">
                    <i class="fas fa-magic mr-1"></i>El estado se actualiza solo
                </span>
            </div>
            @if ($siguiente)
                <div class="ft-siguiente mb-3">
                    <span class="ft-siguiente__icono"><i class="fas fa-flag"></i></span>
                    <div class="ft-siguiente__texto">
                        <span class="ft-siguiente__eti">Siguiente paso</span>
                        <strong>{{ $siguiente['falta'] }}</strong>
                    </div>
                    @if ($accionSiguiente)
                        @if ($accionSiguiente[0] === 'rd')
                            <a href="{{ route('titulacion.rd.crear', [$egresado->id, $accionSiguiente[1]]) }}" class="btn btn-sm btn-primary ft-siguiente__btn">
                                <i class="fas {{ $accionSiguiente[3] }} mr-1"></i>{{ $accionSiguiente[2] }}
                            </a>
                        @else
                            <button type="button" class="btn btn-sm btn-primary ft-siguiente__btn js-abrir" data-abrir="{{ $accionSiguiente[1] }}">
                                <i class="fas {{ $accionSiguiente[3] }} mr-1"></i>{{ $accionSiguiente[2] }}
                            </button>
                        @endif
                    @endif
                </div>
            @else
                <div class="ft-siguiente ft-siguiente--completo mb-3">
                    <span class="ft-siguiente__icono"><i class="fas fa-check"></i></span>
                    <div class="ft-siguiente__texto"><span class="ft-siguiente__eti">Proceso completo</span><strong>Licenciatura inscrita en SUNEDU.</strong></div>
                </div>
            @endif

            @include('titulacion._stepper')

            @if ($alertas)
                <div class="ft-alertas mb-3">
                    <div class="mt-subtitulo">Por revisar</div>
                    @foreach ($alertas as [$nivel, $texto, $irA])
                        <label for="{{ $tabId }}-{{ $irA }}" class="ft-alerta ft-alerta--{{ $nivel }}" title="Ir a la pestaña">
                            <i class="fas {{ $nivel === 'danger' ? 'fa-exclamation-triangle' : ($nivel === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle') }} mr-1"></i>{!! $texto !!}
                            <i class="fas fa-chevron-right fa-xs ml-auto"></i>
                        </label>
                    @endforeach
                </div>
            @endif

            @php
                // Acordeón por etapas: la que está en curso abierta con sus opciones; las terminadas
                // plegadas en una línea con su resumen; las futuras, una línea con candado y sin
                // opciones (ReglasProceso). El PTI sigue abierto si toca enviar un aviso de vigencia.
                $requisitoEtapa = ['pti' => null, 'ti' => 'pti_aprobado', 'tesis' => 'bachiller'];
                $vistaEtapa = [];
                foreach (['pti', 'ti', 'tesis'] as $claveEtapa) {
                    $estadoE = $etapasAvance[$claveEtapa]['estado'];
                    $faltaE = \App\Services\Titulacion\ReglasProceso::falta($avance, $requisitoEtapa[$claveEtapa]);
                    $vistaEtapa[$claveEtapa] = match (true) {
                        $estadoE === 'pendiente' && $faltaE !== null => ['bloqueada', $faltaE],
                        $estadoE === 'completo' && ! ($claveEtapa === 'pti' && $sugerido) => ['plegada', null],
                        default => ['abierta', null],
                    };
                }
                // Resumen de una etapa plegada: su último paso logrado (con N° y fecha si los tiene).
                $resumenEtapa = function (string $claveEtapa) use ($etapasAvance) {
                    $h = collect($etapasAvance[$claveEtapa]['hitos'])->where('logrado', true)->last();

                    // El N° de RD importado a veces es un nombre de archivo largo: se acorta.
                    return $h ? collect([$h['label'], \Illuminate\Support\Str::limit((string) $h['detalle'], 40), $h['fecha']?->format('d/m/Y')])->filter()->implode(' · ') : null;
                };
            @endphp
            {{-- ① PTI --}}
            @php
                [$estPti, $colPti] = $estadoEtapa('pti');
                $historialPti = $seguimiento->ptis ?? collect();
                $tiAprobado = $avance->hitos['ti_aprobado']['logrado'];
                $etapaSugerida = $tiAprobado ? 'licenciatura' : 'bachiller';
            @endphp
            @if ($vistaEtapa['pti'][0] === 'bloqueada')
                <div class="ft-etapa ft-etapa--bloqueada" title="Se habilita cuando esté: {{ $vistaEtapa['pti'][1] }}">
                    <div class="ft-etapa__h">
                        <span class="ft-etapa__n"><i class="fas fa-lock fa-xs"></i></span>
                        <span class="ft-etapa__titulo">Plan de Trabajo de Investigación (PTI)</span>
                        <span class="ft-etapa__lock ml-auto">Se habilita cuando esté: {{ $vistaEtapa['pti'][1] }}</span>
                    </div>
                </div>
            @else
            <details class="ft-etapa ft-etapa--{{ $etapasAvance['pti']['estado'] }}" @if ($vistaEtapa['pti'][0] === 'abierta') open @endif>
                <summary class="ft-etapa__h">
                    <span class="ft-etapa__n">1</span>
                    <span class="ft-etapa__titulo">Plan de Trabajo de Investigación (PTI)</span>
                    @if ($pti) <span class="ft-etapa__meta">N° {{ $pti->numero }} · {{ \App\Models\Titulacion\Pti::ETAPAS[$pti->etapa] ?? $pti->etapa }}</span> @endif
                    <span class="badge badge-{{ $colPti }} ml-auto">{{ $estPti }}</span>
                    @if ($vistaEtapa['pti'][0] === 'plegada' && ($r = $resumenEtapa('pti')))<span class="ft-etapa__resumen">{{ $r }}</span>@endif
                    <i class="fas fa-chevron-down ft-etapa__chev" aria-hidden="true"></i>
                </summary>
                <dl class="ft-etapa__datos">
                    <div><dt>Título</dt><dd>{{ $pti?->titulo ?: '—' }}</dd></div>
                    <div><dt>Asesor(a)</dt><dd>{{ $pti?->asesorNombre() ?: '—' }}</dd></div>
                    <div>
                        <dt>Vigencia</dt>
                        <dd>
                            @if ($vigencia->conocida())
                                Vence el {{ $vigencia->vencimiento->format('d/m/Y') }}
                                @if ($vigencia->vencido())
                                    <span class="badge badge-dark">Vencido</span>
                                @else
                                    <span class="badge badge-{{ $sugerido === 'urgente' ? 'danger' : 'success' }}">quedan {{ $vigencia->tiempoRestanteTexto() }}</span>
                                @endif
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
                @include('titulacion._rd_slot', [
                    'etiqueta' => 'RD de aprobación', 'res' => $rdDe('PTI'), 'plantilla' => 'aprobacion_pti',
                    'numeroCampo' => $pti?->rd_pti, 'fechaCampo' => $avance->hitos['pti_aprobado']['fecha'],
                ])
                @if ($rdAmpliacion = $rdDe('AmpliacionPti'))
                    @include('titulacion._rd_slot', ['etiqueta' => 'Ampliación de PTI', 'res' => $rdAmpliacion])
                @endif

                <details class="ft-desplegable mt-2" id="ptidatos-{{ $egresado->id }}">
                    <summary><i class="fas fa-pen mr-1"></i>{{ $pti ? 'Editar datos del PTI' : 'Registrar datos del PTI' }}</summary>
                    <div class="mt-2">
                        @if ($historialPti->count() > 1)
                            <div class="ft-pti-historial mb-3">
                                @foreach ($historialPti as $p)
                                    @php
                                        $esVigente = $pti && $p->id === $pti->id;
                                        $estadoP = match (true) {
                                            $p->estado === 'vencido' => ['Vencido', 'dark'],
                                            filled($p->rd_ampliacion) => ['Ampliado', 'warning'],
                                            filled($p->rd_pti) => ['Aprobado', 'primary'],
                                            default => ['En proceso', 'secondary'],
                                        };
                                    @endphp
                                    <div class="ft-pti-historial__item {{ $esVigente ? 'is-vigente' : '' }}">
                                        <div class="d-flex flex-wrap align-items-center" style="gap:.4rem;">
                                            <strong>PTI N° {{ $p->numero }}</strong>
                                            <span class="badge badge-light border">{{ \App\Models\Titulacion\Pti::ETAPAS[$p->etapa] ?? $p->etapa }}</span>
                                            <span class="badge badge-{{ $estadoP[1] }}">{{ $estadoP[0] }}</span>
                                            @if ($esVigente) <span class="badge badge-success">Vigente</span> @endif
                                            <span class="small text-muted ml-auto">
                                                {{ $p->fecha_pti?->format('d/m/Y') ?? 'sin fecha' }}
                                                → vence {{ ($p->vencimiento_ampliado ?? $p->fecha_vencimiento)?->format('d/m/Y') ?? '—' }}
                                            </span>
                                        </div>
                                        <div class="small text-muted">
                                            {{ $p->rd_pti ?: 'Sin RD' }}
                                            @if ($p->asesorNombre()) · Asesor/a: {{ $p->asesorNombre() }} @endif
                                        </div>
                                        @unless ($esVigente)
                                            <details class="ft-desplegable mt-1">
                                                <summary>Editar PTI N° {{ $p->numero }}</summary>
                                                <div class="mt-2">@include('titulacion._form_pti', ['p' => $p])</div>
                                            </details>
                                        @endunless
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-subtitulo">PTI vigente (N° {{ $pti->numero }})</div>
                        @endif

                        @include('titulacion._form_pti', ['p' => $pti, 'aprobadoPorRd' => $avance->hitos['pti_aprobado']['logrado']])
                        @include('titulacion._agregar_asesor', ['tab' => 'pti'])

                        @if ($pti)
                            <details class="ft-desplegable ft-desplegable--accion mt-3">
                                <summary><i class="fas fa-plus-circle mr-1"></i>Registrar un nuevo PTI (el actual venció)</summary>
                                <div class="small text-muted mt-2 mb-2">
                                    Si el PTI venció <strong>sin haber hecho el TI</strong>, el proceso vuelve a empezar con un PTI para Bachiller.
                                    Si venció <strong>después del TI</strong>, el nuevo PTI es para la Licenciatura y se conserva el Bachiller.
                                    Si se otorgó una <strong>ampliación</strong>, no registre uno nuevo: anote la RD de ampliación en el PTI vigente.
                                    @if ($tiAprobado) <br><i class="fas fa-info-circle mr-1"></i>Esta persona ya tiene el TI aprobado: se sugiere Licenciatura. @endif
                                </div>
                                <form method="POST" action="{{ route('titulacion.pti.nuevo', $egresado->id) }}"
                                      data-confirm="El PTI N° {{ $pti->numero }} quedará como vencido en el historial."
                                      data-confirm-titulo="¿Registrar el nuevo PTI?" data-confirm-boton="Sí, registrar">
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group col-md-3 mb-2">
                                            <label class="small font-weight-bold mb-0">Para el grado de</label>
                                            <select name="etapa" class="form-control form-control-sm" required>
                                                @foreach (\App\Models\Titulacion\Pti::ETAPAS as $clave => $nombre)
                                                    <option value="{{ $clave }}" @selected($etapaSugerida === $clave)>{{ $nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3 mb-2">
                                            <label class="small font-weight-bold mb-0">Fecha PTI</label>
                                            <input type="date" name="fecha_pti" class="form-control form-control-sm">
                                        </div>
                                        <div class="form-group col-md-3 mb-2">
                                            <label class="small font-weight-bold mb-0">Fecha vencimiento</label>
                                            <input type="date" name="fecha_vencimiento" class="form-control form-control-sm">
                                        </div>
                                        <div class="form-group col-md-3 mb-2">
                                            <label class="small font-weight-bold mb-0">RD PTI</label>
                                            <input type="text" name="rd_pti" class="form-control form-control-sm">
                                        </div>
                                        <div class="form-group col-md-8 mb-2">
                                            <label class="small font-weight-bold mb-0">Título</label>
                                            <input type="text" name="titulo" class="form-control form-control-sm" value="{{ $pti->titulo }}">
                                        </div>
                                        <div class="form-group col-md-4 mb-2">
                                            <label class="small font-weight-bold mb-0">Asesor/a</label>
                                            {!! $selectDocente('asesor_id', $pti->asesorValor()) !!}
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-plus mr-1"></i>Registrar nuevo PTI</button>
                                </form>
                            </details>
                        @endif
                    </div>
                </details>

                {{-- Avisos de vencimiento del PTI (Unidad de Investigación) --}}
                <details class="ft-desplegable mt-2" id="avisos-{{ $egresado->id }}" @if ($sugerido || ($fichaActiva && request('tab') === 'avisos')) open @endif>
                    <summary>
                        <i class="fas fa-envelope mr-1"></i>Avisos de vencimiento
                        @if ($seguimiento->avisosPti->isNotEmpty()) <span class="ml-1 text-muted font-weight-normal">({{ $seguimiento->avisosPti->count() }} enviado(s))</span> @endif
                        @if ($sugerido)
                            <span class="badge badge-{{ $sugerido === 'urgente' ? 'danger' : 'success' }} ml-1">puede enviar {{ $sugerido === 'urgente' ? 'aviso urgente' : 'recordatorio' }}</span>
                        @elseif ($vigencia->vencido())
                            <span class="ml-1 text-muted font-weight-normal">· PTI vencido</span>
                        @endif
                    </summary>
                    <div class="mt-2">
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
                    </div>
                </details>
            </details>
            @endif

            {{-- ② TI --}}
            @php
                [$estTi, $colTi] = $estadoEtapa('ti');
                $actaExpo = $seguimiento->actas->firstWhere('tipo', 'expo');
            @endphp
            @if ($vistaEtapa['ti'][0] === 'bloqueada')
                <div class="ft-etapa ft-etapa--bloqueada" title="Se habilita cuando esté: {{ $vistaEtapa['ti'][1] }}">
                    <div class="ft-etapa__h">
                        <span class="ft-etapa__n"><i class="fas fa-lock fa-xs"></i></span>
                        <span class="ft-etapa__titulo">Trabajo de Investigación (TI) · Bachiller</span>
                        <span class="ft-etapa__lock ml-auto">Se habilita cuando esté: {{ $vistaEtapa['ti'][1] }}</span>
                    </div>
                </div>
            @else
            <details class="ft-etapa ft-etapa--{{ $etapasAvance['ti']['estado'] }}" @if ($vistaEtapa['ti'][0] === 'abierta') open @endif>
                <summary class="ft-etapa__h">
                    <span class="ft-etapa__n">2</span>
                    <span class="ft-etapa__titulo">Trabajo de Investigación (TI) · Bachiller</span>
                    <span class="badge badge-{{ $colTi }} ml-auto">{{ $estTi }}</span>
                    @if ($vistaEtapa['ti'][0] === 'plegada' && ($r = $resumenEtapa('ti')))<span class="ft-etapa__resumen">{{ $r }}</span>@endif
                    <i class="fas fa-chevron-down ft-etapa__chev" aria-hidden="true"></i>
                </summary>
                <dl class="ft-etapa__datos">
                    <div><dt>Exposición</dt><dd>{{ $tesis?->fecha_exposicion?->format('d/m/Y') ?? $actaExpo?->fecha?->format('d/m/Y') ?? '—' }}@if ($actaExpo?->numero_acta) · Acta {{ $actaExpo->numero_acta }}@endif</dd></div>
                    <div><dt>Bachiller (SUNEDU)</dt><dd>{{ $tesis?->fecha_bachiller_sunedu?->format('d/m/Y') ?? '—' }}@if ($tesis?->bachiller_sunedu) · N° {{ $tesis->bachiller_sunedu }}@endif</dd></div>
                </dl>
                @include('titulacion._rd_slot', [
                    'etiqueta' => 'RD de aprobación de exposición', 'res' => $rdDe('Exposicion'), 'plantilla' => 'aprobacion_exposicion',
                    'numeroCampo' => $tesis?->rd_ti, 'fechaCampo' => $avance->hitos['ti_aprobado']['fecha'],
                ])
                @include('titulacion._paso_form', [
                    'id' => $idPaso('expo'), 'requiere' => 'pti_aprobado', 'titulo' => 'exposición del TI', 'hecho' => (bool) $tesis?->fecha_exposicion,
                    'campos' => [['fecha_exposicion', 'Fecha de exposición', 'date', $tesis?->fecha_exposicion?->format('Y-m-d')]],
                    'nota' => 'El acta con su número, promedio y jurados se registra en Historial → Actas y jurados.',
                ])
                @include('titulacion._paso_form', [
                    'id' => $idPaso('bachiller'), 'requiere' => 'ti_aprobado', 'titulo' => 'Bachiller inscrito en SUNEDU', 'hecho' => (bool) ($tesis?->fecha_bachiller_sunedu || $tesis?->bachiller_sunedu),
                    'campos' => [
                        ['fecha_bachiller_sunedu', 'Fecha de inscripción', 'date', $tesis?->fecha_bachiller_sunedu?->format('Y-m-d')],
                        ['bachiller_sunedu', 'N° de registro SUNEDU', 'text', $tesis?->bachiller_sunedu],
                    ],
                ])
            </details>
            @endif

            {{-- ③ Tesis --}}
            @php
                [$estTesis, $colTesis] = $estadoEtapa('tesis');
                $actaTesis = $seguimiento->actas->firstWhere('tipo', 'tesis');
            @endphp
            @if ($vistaEtapa['tesis'][0] === 'bloqueada')
                <div class="ft-etapa ft-etapa--bloqueada" title="Se habilita cuando esté: {{ $vistaEtapa['tesis'][1] }}">
                    <div class="ft-etapa__h">
                        <span class="ft-etapa__n"><i class="fas fa-lock fa-xs"></i></span>
                        <span class="ft-etapa__titulo">Tesis · Licenciatura</span>
                        <span class="ft-etapa__lock ml-auto">Se habilita cuando esté: {{ $vistaEtapa['tesis'][1] }}</span>
                    </div>
                </div>
            @else
            <details class="ft-etapa ft-etapa--{{ $etapasAvance['tesis']['estado'] }}" @if ($vistaEtapa['tesis'][0] === 'abierta') open @endif>
                <summary class="ft-etapa__h">
                    <span class="ft-etapa__n">3</span>
                    <span class="ft-etapa__titulo">Tesis · Licenciatura</span>
                    <span class="badge badge-{{ $colTesis }} ml-auto">{{ $estTesis }}</span>
                    @if ($vistaEtapa['tesis'][0] === 'plegada' && ($r = $resumenEtapa('tesis')))<span class="ft-etapa__resumen">{{ $r }}</span>@endif
                    <i class="fas fa-chevron-down ft-etapa__chev" aria-hidden="true"></i>
                </summary>
                <dl class="ft-etapa__datos">
                    <div><dt>Sustentación</dt><dd>{{ $tesis?->fecha_sustentacion?->format('d/m/Y') ?? $actaTesis?->fecha?->format('d/m/Y') ?? '—' }}@if ($actaTesis?->numero_acta) · Acta {{ $actaTesis->numero_acta }}@endif</dd></div>
                    <div><dt>Licenciatura (SUNEDU)</dt><dd>{{ $tesis?->fecha_titulo_sunedu?->format('d/m/Y') ?? '—' }}@if ($tesis?->titulo_sunedu) · N° {{ $tesis->titulo_sunedu }}@endif</dd></div>
                    @if ($tesis?->url_repositorio)
                        <div><dt>Repositorio</dt><dd><a href="{{ $tesis->url_repositorio }}" target="_blank" rel="noopener">Abrir</a></dd></div>
                    @endif
                </dl>
                @include('titulacion._rd_slot', ['etiqueta' => 'RD de fecha de sustentación', 'res' => $rdDe('FechaSustentacion'), 'plantilla' => 'fecha_sustentacion'])
                @if ($rdExt = $rdDe('Extension') ?? null)
                    @include('titulacion._rd_slot', ['etiqueta' => 'Ampliación de sustentación', 'res' => $rdExt])
                @elseif ($tesis?->rd_extension)
                    @include('titulacion._rd_slot', ['etiqueta' => 'Ampliación de sustentación', 'numeroCampo' => $tesis->rd_extension, 'fechaCampo' => $tesis->fecha_extension])
                @endif
                @include('titulacion._rd_slot', [
                    'etiqueta' => 'RD de aprobación de sustentación', 'res' => $rdDe('Tesis'), 'plantilla' => 'aprobacion_sustentacion',
                    'numeroCampo' => $tesis?->rd_tesis, 'fechaCampo' => $avance->hitos['tesis_aprobada']['fecha'],
                ])
                @include('titulacion._paso_form', [
                    'id' => $idPaso('sustentacion'), 'requiere' => 'bachiller', 'titulo' => 'sustentación de la Tesis', 'hecho' => (bool) $tesis?->fecha_sustentacion,
                    'campos' => [['fecha_sustentacion', 'Fecha de sustentación', 'date', $tesis?->fecha_sustentacion?->format('Y-m-d')]],
                    'nota' => 'El acta con su número, promedio y jurados se registra en Historial → Actas y jurados.',
                ])
                @include('titulacion._paso_form', [
                    'id' => $idPaso('ampliacion'), 'requiere' => 'bachiller', 'titulo' => 'ampliación del plazo de sustentación', 'hecho' => (bool) ($tesis?->rd_extension || $tesis?->fecha_extension),
                    'campos' => [
                        ['rd_extension', 'RD de ampliación', 'text', $tesis?->rd_extension],
                        ['fecha_extension', 'Ampliado hasta', 'date', $tesis?->fecha_extension?->format('Y-m-d')],
                    ],
                ])
                @include('titulacion._paso_form', [
                    'id' => $idPaso('licenciado'), 'requiere' => 'tesis_aprobada', 'titulo' => 'Licenciatura inscrita en SUNEDU', 'hecho' => (bool) ($tesis?->fecha_titulo_sunedu || $tesis?->titulo_sunedu),
                    'campos' => [
                        ['fecha_titulo_sunedu', 'Fecha de inscripción', 'date', $tesis?->fecha_titulo_sunedu?->format('Y-m-d')],
                        ['titulo_sunedu', 'N° de registro SUNEDU', 'text', $tesis?->titulo_sunedu],
                        ['url_repositorio', 'Enlace en el repositorio', 'url', $tesis?->url_repositorio, [], 'https://…'],
                        ['estado', 'Repositorio', 'select', $tesis?->estado ?? 'pendiente', ['pendiente' => 'Pendiente', 'en_repositorio' => 'En repositorio', 'aprobado' => 'Aprobado']],
                    ],
                ])
            </details>
            @endif

            {{-- TI + Tesis: mismo registro (titulacion_tesis), un solo formulario/endpoint. Oculto mientras el TI esté bloqueado. --}}
            <details class="ft-desplegable mb-3" @if ($vistaEtapa['ti'][0] === 'bloqueada') hidden @endif>
                <summary><i class="fas fa-sliders-h mr-1"></i>Editar todos los datos de TI y Tesis (título, asesor, RD anotadas…)</summary>
                <div class="mt-2">
                    <form method="POST" action="{{ route('titulacion.tesis.guardar', $egresado->id) }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label class="small font-weight-bold">Título</label>
                                <textarea name="titulo" class="form-control form-control-sm" rows="2">{{ $tesis?->titulo ?? '' }}</textarea>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small font-weight-bold">Asesor/a</label>
                                {!! $selectDocente('asesor_id', $tesis?->asesorValor()) !!}
                                <label class="small font-weight-bold mt-2">Repositorio</label>
                                <select name="estado" class="form-control form-control-sm">
                                    @foreach (['pendiente' => 'Pendiente', 'en_repositorio' => 'En repositorio', 'aprobado' => 'Aprobado'] as $op => $etiqueta)
                                        <option value="{{ $op }}" @selected(($tesis?->estado ?? 'pendiente') === $op)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="titulacion-etapa">
                            <div class="titulacion-etapa__titulo">② TI — Trabajo de Investigación (grado de Bachiller)</div>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">Fecha exposición</label>
                                    <input type="date" name="fecha_exposicion" {!! $bloqCampo('fecha_exposicion') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_exposicion?->format('Y-m-d') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">RD TI</label>
                                    <input type="text" name="rd_ti" {!! $bloqCampo('rd_ti') !!} class="form-control form-control-sm" value="{{ $tesis?->rd_ti ?? '' }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">Bachiller: inscripción SUNEDU</label>
                                    <input type="date" name="fecha_bachiller_sunedu" {!! $bloqCampo('fecha_bachiller_sunedu') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_bachiller_sunedu?->format('Y-m-d') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">Bachiller: N° registro SUNEDU</label>
                                    <input type="text" name="bachiller_sunedu" {!! $bloqCampo('bachiller_sunedu') !!} class="form-control form-control-sm" value="{{ $tesis?->bachiller_sunedu ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="titulacion-etapa">
                            <div class="titulacion-etapa__titulo">③ Tesis — Título de Licenciado</div>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">Fecha sustentación</label>
                                    <input type="date" name="fecha_sustentacion" {!! $bloqCampo('fecha_sustentacion') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_sustentacion?->format('Y-m-d') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">RD Extensión</label>
                                    <input type="text" name="rd_extension" {!! $bloqCampo('rd_extension') !!} class="form-control form-control-sm" value="{{ $tesis?->rd_extension ?? '' }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">Extensión hasta</label>
                                    <input type="date" name="fecha_extension" {!! $bloqCampo('fecha_extension') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_extension?->format('Y-m-d') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">RD Tesis</label>
                                    <input type="text" name="rd_tesis" {!! $bloqCampo('rd_tesis') !!} class="form-control form-control-sm" value="{{ $tesis?->rd_tesis ?? '' }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="small font-weight-bold">URL Repositorio</label>
                                    <input type="text" name="url_repositorio" {!! $bloqCampo('url_repositorio') !!} class="form-control form-control-sm" value="{{ $tesis?->url_repositorio ?? '' }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">Licenciatura: inscripción SUNEDU</label>
                                    <input type="date" name="fecha_titulo_sunedu" {!! $bloqCampo('fecha_titulo_sunedu') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_titulo_sunedu?->format('Y-m-d') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold">Licenciatura: N° registro SUNEDU</label>
                                    <input type="text" name="titulo_sunedu" {!! $bloqCampo('titulo_sunedu') !!} class="form-control form-control-sm" value="{{ $tesis?->titulo_sunedu ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">Guardar TI/Tesis</button>
                    </form>
                    @include('titulacion._agregar_asesor', ['tab' => 'tesis'])
                </div>
            </details>

            {{-- Otros trámites (resoluciones que no son de una etapa) --}}
            <details class="ft-etapa ft-etapa--otros" id="tramites-{{ $egresado->id }}">
                <summary class="ft-etapa__h">
                    <span class="ft-etapa__n"><i class="fas fa-gavel fa-xs"></i></span>
                    <span class="ft-etapa__titulo">Otros trámites</span>
                    <span class="ft-etapa__resumen">Cambio de tema, de asesor, anulación de PTI y RD ya emitidas</span>
                    <i class="fas fa-chevron-down ft-etapa__chev" aria-hidden="true"></i>
                </summary>
                @include('titulacion._rd_slot', ['etiqueta' => 'Cambio de tema de investigación', 'res' => $rdDe('CambioTema'), 'plantilla' => 'cambio_tema'])
                @include('titulacion._rd_slot', ['etiqueta' => 'Cambio de asesor', 'res' => $rdDe('CambioAsesor'), 'plantilla' => 'cambio_asesor'])
                @include('titulacion._rd_slot', ['etiqueta' => 'Anulación de PTI', 'res' => $rdDe('AnulacionPti'), 'plantilla' => 'anulacion_pti'])
                <details class="ft-desplegable mt-2" id="rdemitida-{{ $egresado->id }}">
                    <summary><i class="fas fa-plus-circle mr-1"></i>Registrar una RD ya emitida (cualquier tipo, con su PDF o enlace)</summary>
                    <div class="mt-2">@include('titulacion._form_resolucion', ['res' => null])</div>
                </details>
            </details>
        </div>

        {{-- ═══ Datos personales + Expediente + Grupo ═══ --}}
        <div class="mt-tabs__panel mt-tabs__panel--datos">
            <div class="ft-seccion"><i class="fas fa-id-card"></i>Datos personales</div>
            @include('titulacion._datos_personales')
            <div class="small text-muted"><i class="fas fa-info-circle mr-1"></i>Datos de la cuenta y de la ficha del estudiante (se editan desde la gestión de usuarios).</div>

            <div class="ft-seccion mt-4"><i class="fas fa-folder-open"></i>Expediente</div>
            <form method="POST" action="{{ route('titulacion.estado.actualizar', $egresado->id) }}" class="form-row align-items-end">
                @csrf @method('PUT')
                <div class="form-group col-md-3">
                    <label class="small font-weight-bold">N° expediente</label>
                    <input type="text" name="numero_expediente" class="form-control form-control-sm" placeholder="0001-T/PUK"
                           title="Ej.: 0001-T/PUK o 0013-T/PUK-PPD" value="{{ $seguimiento->numero_expediente }}">
                </div>
                <div class="form-group col-md-3">
                    <label class="small font-weight-bold">Periodo de estudios</label>
                    <input type="text" name="periodo_estudios" class="form-control form-control-sm" placeholder="2016-I al 2021-II" value="{{ $seguimiento->periodo_estudios }}">
                </div>
                <div class="form-group col-md-6">
                    <label class="small font-weight-bold">Observaciones</label>
                    <textarea name="observaciones" class="form-control form-control-sm" rows="1">{{ $seguimiento->observaciones }}</textarea>
                </div>
                <div class="form-group col-12 mb-1">
                    <button type="submit" class="btn btn-sm btn-primary">Guardar expediente</button>
                    <small class="text-muted ml-2">
                        Última actualización: {{ $seguimiento->updated_at?->format('d/m/Y H:i') ?? '—' }}
                        @if ($seguimiento->actualizadoPor) por {{ $seguimiento->actualizadoPor->name }} @endif
                    </small>
                </div>
            </form>

            <div class="ft-seccion mt-4">
                <i class="fas fa-users"></i>Grupo
                @if ($integrantes->isNotEmpty()) <span class="ft-seccion__meta">{{ $integrantes->count() + 1 }} integrantes</span> @endif
            </div>
            @include('titulacion._grupo', ['persona' => $egresado, 'companeros' => $integrantes, 'paraGestor' => true])
            <div class="small text-muted mt-2">
                <i class="fas fa-info-circle mr-1"></i>El grupo se reconoce por compartir una RD o un acta. Al generar una RD desde el sistema puede marcar a sus integrantes.
            </div>
        </div>

        {{-- ═══ Historial: resoluciones, actas y jurados, documentos ═══ --}}
        <div class="mt-tabs__panel mt-tabs__panel--historial">
            @include('titulacion._rd_linea')

            <div class="row">
                <div class="col-lg-7 mb-3">
                    <div class="ft-seccion"><i class="fas fa-gavel"></i>Resoluciones registradas</div>
                    <ul class="list-unstyled mb-3">
                        @forelse ($seguimiento->resoluciones->sortBy('fecha') as $res)
                            <li class="d-flex justify-content-between align-items-center border-bottom py-1" style="gap:.5rem;">
                                <span class="small">
                                    <strong>{{ \App\Models\Titulacion\Resolucion::nombreTipo($res->tipo) }}</strong> — {{ $res->numero_rd ?? '—' }}
                                    <span class="text-muted">({{ $res->fecha?->format('d/m/Y') ?? 'sin fecha' }})</span>
                                    @if ($res->archivo)
                                        <a href="{{ $res->archivo }}" target="_blank" rel="noopener" class="ml-1 text-nowrap" title="Abrir PDF de la resolución"><i class="fas fa-file-pdf"></i> PDF</a>
                                    @else
                                        <span class="badge badge-light border ml-1" title="Sin PDF">sin PDF</span>
                                    @endif
                                    @if ($res->detalle) <span class="d-block text-muted">{{ $res->detalle }}</span> @endif
                                    <details class="ft-desplegable mt-1">
                                        <summary>Editar</summary>
                                        <div class="mt-2">@include('titulacion._form_resolucion', ['res' => $res])</div>
                                    </details>
                                </span>
                                <form method="POST" action="{{ route('titulacion.resoluciones.eliminar', $res->id) }}" data-confirm="Se eliminará esta resolución del historial." data-confirm-danger data-confirm-titulo="¿Eliminar la resolución?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="Eliminar"><i class="fa fa-trash fa-xs"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="text-muted small">Sin resoluciones registradas.</li>
                        @endforelse
                    </ul>
                    <button type="button" class="btn btn-link btn-sm p-0 js-abrir" data-abrir="rdemitida-{{ $egresado->id }}" data-tab="{{ $tabId }}-proceso">
                        <i class="fas fa-plus-circle mr-1"></i>Registrar una RD ya emitida
                    </button>
                </div>
                <div class="col-lg-5 mb-3">
                    <div class="ft-seccion"><i class="fas fa-link"></i>Documentos</div>
                    <ul class="list-unstyled mb-3">
                        @forelse ($seguimiento->documentos as $doc)
                            <li class="d-flex justify-content-between align-items-center border-bottom py-1">
                                <span class="small">
                                    <strong>{{ $doc->tipo }}</strong>
                                    @if ($doc->url)
                                        — <a href="{{ $doc->url }}" target="_blank" rel="noopener">
                                            @if (\App\Services\Titulacion\ArchivosRd::nombreDesdeUrl($doc->url)) <i class="fas fa-file-pdf mr-1"></i>PDF @else <i class="fas fa-link mr-1"></i>enlace @endif
                                        </a>
                                    @endif
                                    @if ($doc->descripcion) <span class="d-block text-muted">{{ $doc->descripcion }}</span> @endif
                                </span>
                                <form method="POST" action="{{ route('titulacion.documentos.eliminar', $doc->id) }}" data-confirm="Se eliminará este documento." data-confirm-danger data-confirm-titulo="¿Eliminar el documento?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="Eliminar"><i class="fa fa-trash fa-xs"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="text-muted small">Sin documentos registrados.</li>
                        @endforelse
                    </ul>
                    <details class="ft-desplegable">
                        <summary><i class="fas fa-plus-circle mr-1"></i>Agregar documento</summary>
                        <form method="POST" action="{{ route('titulacion.documentos.guardar', $egresado->id) }}" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-sm-5 mb-2">
                                    <label class="small font-weight-bold mb-0">Tipo</label>
                                    <select name="tipo" class="form-control form-control-sm" required>
                                        @foreach (['ITI', 'Dictamen', 'Tesis', 'Acta', 'Otro'] as $tipoDoc)
                                            <option value="{{ $tipoDoc }}">{{ $tipoDoc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-7 mb-2">
                                    <label class="small font-weight-bold mb-0">Descripción <span class="text-muted font-weight-normal">(opcional)</span></label>
                                    <input type="text" name="descripcion" maxlength="255" class="form-control form-control-sm" placeholder="Ej.: versión final corregida">
                                </div>
                                <div class="form-group col-12 mb-2">
                                    <label class="small font-weight-bold mb-0">Subir PDF <span class="text-muted font-weight-normal">(hasta 20 MB)</span></label>
                                    <input type="file" name="archivo" accept="application/pdf" class="form-control-file form-control-sm">
                                </div>
                                <div class="form-group col-12 mb-2">
                                    <label class="small font-weight-bold mb-0">… o pegar un enlace <span class="text-muted font-weight-normal">(Drive u otro)</span></label>
                                    <input type="text" name="url" class="form-control form-control-sm" placeholder="https://…">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check mr-1"></i>Agregar</button>
                        </form>
                    </details>
                </div>
            </div>

            @if (collect($filasImportadas ?? [])->isNotEmpty())
                <details class="ft-desplegable mb-3">
                    <summary><i class="fas fa-file-import mr-1"></i>Datos de archivos importados ({{ count($filasImportadas) }})</summary>
                    <div class="small text-muted mt-2 mb-2">Tal como vienen en las hojas de secretaría y expedientes. Sirven para completar o verificar la ficha.</div>
                    @include('titulacion._filas_importadas', ['filas' => $filasImportadas])
                </details>
            @endif

            <div class="ft-seccion mt-2"><i class="fas fa-file-signature"></i>Actas y jurados</div>
            @forelse ($seguimiento->actas as $acta)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $acta->tipo === 'expo' ? 'Exposición (TI)' : 'Sustentación (Tesis)' }}</strong> — Acta N° {{ $acta->numero_acta ?? '—' }}
                            <span class="text-muted small d-block">{{ $acta->fecha?->format('d/m/Y') ?? 'Sin fecha' }} · Promedio: {{ $acta->promedio ?? '—' }}</span>
                            @if ($acta->titulo_trabajo) <span class="text-muted small d-block">{{ $acta->titulo_trabajo }}</span> @endif
                            @if ($acta->asesorNombre()) <span class="text-muted small d-block">Asesor: {{ $acta->asesorNombre() }}</span> @endif
                        </div>
                        <form method="POST" action="{{ route('titulacion.actas.eliminar', $acta->id) }}" data-confirm="Se eliminará el acta junto con sus jurados." data-confirm-danger data-confirm-titulo="¿Eliminar el acta?">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar acta"><i class="fa fa-trash fa-xs"></i></button>
                        </form>
                    </div>
                    <div class="mt-2">
                        <span class="small font-weight-bold text-secondary">Jurados:</span>
                        @forelse ($acta->jurados as $jurado)
                            <span class="badge badge-light border mr-1">
                                {{ $jurado->rol }}: {{ $jurado->asesorNombre() ?? '—' }}
                                <form method="POST" action="{{ route('titulacion.jurados.eliminar', $jurado->id) }}" class="d-inline" data-confirm="El jurado se quitará de esta acta." data-confirm-danger data-confirm-titulo="¿Quitar el jurado?" data-confirm-boton="Sí, quitar">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="Quitar"><i class="fa fa-times fa-xs"></i></button>
                                </form>
                            </span>
                        @empty
                            <span class="text-muted small">Ninguno</span>
                        @endforelse
                        <form method="POST" action="{{ route('titulacion.jurados.guardar', $acta->id) }}" class="form-inline mt-2">
                            @csrf
                            <div class="mr-1 mb-1">{!! $selectDocente('docente_id', null, 'Agregar jurado...', true) !!}</div>
                            <select name="rol" class="form-control form-control-sm mr-1 mb-1" required>
                                <option value="presidente">Presidente</option>
                                <option value="jurado1">Jurado 1</option>
                                <option value="jurado2">Jurado 2</option>
                                <option value="jurado3">Jurado 3</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary mb-1">Agregar</button>
                        </form>
                    </div>
                    <details class="ft-desplegable mt-1">
                        <summary><i class="fas fa-pen mr-1"></i>Editar acta</summary>
                        <div class="mt-2">@include('titulacion._form_acta', ['acta' => $acta])</div>
                    </details>
                </div>
            @empty
                <p class="text-muted small">Sin actas registradas.</p>
            @endforelse
            <details class="ft-desplegable mt-1">
                <summary><i class="fas fa-plus-circle mr-1"></i>Nueva acta</summary>
                <div class="mt-2">
                    @include('titulacion._form_acta', ['acta' => null])
                    @include('titulacion._agregar_asesor', ['tab' => 'actas'])
                </div>
            </details>
        </div>
    </div>{{-- /mt-tabs --}}
</div>

@include('titulacion._tabs_estilos')

{{-- Recursos compartidos (lista de docentes, scripts, estilos, popup de vista previa): una
     sola vez por página. La ficha cargada por AJAX los omite: ya están en la lista. --}}
@unless ($sinRecursos ?? false)
    @include('titulacion._ficha_recursos')
@endunless
