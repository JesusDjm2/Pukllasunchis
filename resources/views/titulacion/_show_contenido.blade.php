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
    $pestanas = ['proceso', 'datos', 'historial', 'tramites'];
    // ?paso=tramites venía del panel que antes estaba dentro de Proceso: ahora abre la pestaña.
    $tabPedida = request('paso') === 'tramites' ? 'tramites' : ([
        'resumen' => 'proceso', 'pti' => 'proceso', 'avisos' => 'proceso', 'tesis' => 'proceso',
        'grupo' => 'datos', 'actas' => 'historial', 'resoluciones' => 'historial',
    ][request('tab')] ?? request('tab'));
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
    if ($pti && in_array($estadoClave ?? null, ['pti_aprobado', 'ti_aprobado'], true)
        && ! $pti->asesor_id && ! $pti->asesor_externo_id && ! $tesis?->asesor_id && ! $tesis?->asesor_externo_id) {
        $alertas[] = ['secondary', 'El TI todavía no tiene asesor(a) asignado(a).', 'proceso'];
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
    @unless ($enAcordeon || $enModal)
        <div class="pt-3 pb-2">
            <a href="{{ route('titulacion.index') }}" class="small text-muted"><i class="fa fa-arrow-left fa-sm mr-1"></i>Volver al listado</a>
        </div>
    @endunless

    @include('titulacion._cabecera')
    @unless (\App\Services\Titulacion\AreasTitulacion::puede('investigacion') && \App\Services\Titulacion\AreasTitulacion::puede('secretaria'))
        <div class="tp-aviso tp-aviso--espera mb-3"><i class="fas fa-eye"></i><div>
            @if (\App\Services\Titulacion\AreasTitulacion::puede('secretaria'))
                <strong>Secretaría:</strong> usted gestiona las RD y el pago del trámite de sustentación. Lo demás lo gestiona el Área de Investigación y aquí se muestra en modo lectura.
            @else
                <strong>Área de Investigación:</strong> las RD y el pago del trámite de sustentación los gestiona Secretaría y aquí se muestran en modo lectura.
            @endif
        </div></div>
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
        @if (Session::has('warning'))
            {{-- Guardado, pero no para todo el grupo (a alguien le faltaba un paso anterior) --}}
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-users mr-1"></i>{{ Session::get('warning') }}
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
            <label for="{{ $tabId }}-tramites" class="mt-tabs__tab mt-tabs__tab--tramites" title="Cambio de tema, cambio de asesor, anulación de PTI y otras RD">
                <i class="fas fa-folder-open mr-1"></i>Trámites extraordinarios
            </label>
        </div>

        {{-- ═══ Proceso: PTI → TI → Tesis ═══ --}}
        <div class="mt-tabs__panel mt-tabs__panel--proceso">
            @include('titulacion._proceso')
        </div>

        {{-- ═══ Datos personales + Expediente + Grupo ═══ --}}
        <div class="mt-tabs__panel mt-tabs__panel--datos">
            <div class="ft-seccion"><i class="fas fa-id-card"></i>Datos personales</div>
            @include('titulacion._datos_personales')
            <div class="small text-muted"><i class="fas fa-info-circle mr-1"></i>Datos de la cuenta y de la ficha del estudiante (se editan desde la gestión de usuarios).</div>

            <div class="ft-seccion mt-4"><i class="fas fa-folder-open"></i>Expediente</div>
            <form method="POST" action="{{ route('titulacion.estado.actualizar', $egresado->id) }}" class="form-row align-items-end">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.estado.actualizar'))>
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
            </fieldset>
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

        {{-- ═══ Historial: resoluciones, actas y jurados, documentos ═══
             Cada registro lleva sus acciones (ver, editar, eliminar) en su propia cabecera; editar
             abre el formulario dentro de la misma tarjeta (data-ft-toggle), sin listas repetidas. --}}
        <div class="mt-tabs__panel mt-tabs__panel--historial">
            @include('titulacion._rd_linea')

            {{-- Actas y jurados --}}
            <section class="ft-hist mb-4">
                <header class="ft-hist__cab">
                    <h3 class="ft-hist__titulo"><i class="fas fa-file-signature"></i>Actas y jurados</h3>
                    <span class="ft-hist__meta">{{ $seguimiento->actas->count() }} acta(s)</span>
                    <button type="button" @unless (\App\Services\Titulacion\AreasTitulacion::puede('investigacion')) hidden @endunless class="btn btn-sm btn-primary ft-hist__nuevo" data-ft-toggle="{{ $tabId }}-acta-nueva" aria-expanded="false" aria-controls="{{ $tabId }}-acta-nueva">
                        <i class="fas fa-plus mr-1"></i>Nueva acta
                    </button>
                </header>

                <div id="{{ $tabId }}-acta-nueva" class="ft-hist__panel mb-3" hidden>
                    <div class="ft-hist__panel-titulo">Nueva acta</div>
                    @include('titulacion._form_acta', ['acta' => null])
                    @include('titulacion._agregar_asesor', ['tab' => 'actas'])
                </div>

                @forelse ($seguimiento->actas as $acta)
                    @php
                        $idActa = $tabId.'-acta-'.$acta->id;
                        $esExpo = $acta->tipo === 'expo';
                    @endphp
                    <article class="ft-acta {{ $esExpo ? 'is-expo' : 'is-tesis' }}">
                        <div class="ft-rd-linea__cab">
                            <div class="ft-rd-linea__info">
                                <div class="d-flex flex-wrap align-items-center" style="gap:.35rem;">
                                    <span class="badge badge-{{ $esExpo ? 'info' : 'success' }}">{{ $esExpo ? 'Exposición (TI)' : 'Sustentación (Tesis)' }}</span>
                                    <strong class="small">Acta N° {{ $acta->numero_acta ?? '—' }}</strong>
                                    <span class="ft-rd-linea__fecha">· {{ $acta->fecha?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                                    @if (filled($acta->promedio)) <span class="ft-acta__nota" title="Promedio">{{ $acta->promedio }}</span> @endif
                                </div>
                                @if ($acta->titulo_trabajo) <div class="small ft-rd-linea__numero mt-1">{{ $acta->titulo_trabajo }}</div> @endif
                                @if ($acta->asesorNombre()) <div class="small text-muted">Asesor: {{ $acta->asesorNombre() }}</div> @endif
                            </div>
                            <div class="ft-acciones">
                                <button type="button" class="ft-accion" data-ft-toggle="{{ $idActa }}" aria-expanded="false" aria-controls="{{ $idActa }}" title="Editar acta" aria-label="Editar acta"><i class="fas fa-pen"></i></button>
                                <form method="POST" action="{{ route('titulacion.actas.eliminar', $acta->id) }}" data-confirm="Se eliminará el acta junto con sus jurados." data-confirm-danger data-confirm-titulo="¿Eliminar el acta?">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.actas.eliminar'))>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ft-accion ft-accion--peligro" title="Eliminar acta" aria-label="Eliminar acta"><i class="fas fa-trash-alt"></i></button>
                                </fieldset>
</form>
                            </div>
                        </div>

                        <div id="{{ $idActa }}" class="ft-hist__panel ft-hist__panel--dentro" hidden>
                            @include('titulacion._form_acta', ['acta' => $acta])
                            <button type="button" class="btn btn-sm btn-link text-muted ft-hist__cancelar" data-ft-toggle="{{ $idActa }}">Cancelar</button>
                        </div>

                        <div class="ft-acta__jurados">
                            <span class="ft-acta__eti">Jurados</span>
                            @forelse ($acta->jurados as $jurado)
                                <span class="ft-jurado">
                                    <span class="ft-jurado__rol">{{ $jurado->rol }}</span>{{ $jurado->asesorNombre() ?? '—' }}
                                    <form method="POST" action="{{ route('titulacion.jurados.eliminar', $jurado->id) }}" class="d-inline" data-confirm="El jurado se quitará de esta acta." data-confirm-danger data-confirm-titulo="¿Quitar el jurado?" data-confirm-boton="Sí, quitar">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.jurados.eliminar'))>
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ft-jurado__quitar" title="Quitar" aria-label="Quitar a {{ $jurado->asesorNombre() }}"><i class="fas fa-times"></i></button>
                                    </fieldset>
</form>
                                </span>
                            @empty
                                <span class="text-muted small">Ninguno</span>
                            @endforelse
                            <button type="button" @unless (\App\Services\Titulacion\AreasTitulacion::puede('investigacion')) hidden @endunless class="ft-jurado ft-jurado--agregar" data-ft-toggle="{{ $idActa }}-jurado" aria-expanded="false" aria-controls="{{ $idActa }}-jurado">
                                <i class="fas fa-plus"></i>Jurado
                            </button>
                        </div>
                        <form id="{{ $idActa }}-jurado" method="POST" action="{{ route('titulacion.jurados.guardar', $acta->id) }}" class="form-inline ft-hist__panel ft-hist__panel--dentro" hidden>
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.jurados.guardar'))>
                            @csrf
                            <div class="mr-1 mb-1">{!! $selectDocente('docente_id', null, 'Elegir docente...', true) !!}</div>
                            <select name="rol" class="form-control form-control-sm mr-1 mb-1" required aria-label="Función en el jurado">
                                <option value="presidente">Presidente</option>
                                <option value="jurado1">Jurado 1</option>
                                <option value="jurado2">Jurado 2</option>
                                <option value="jurado3">Jurado 3</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary mb-1">Agregar</button>
                        </fieldset>
</form>
                    </article>
                @empty
                    <p class="ft-hist__vacio">Sin actas registradas.</p>
                @endforelse
            </section>

            {{-- Documentos --}}
            <section class="ft-hist mb-4">
                <header class="ft-hist__cab">
                    <h3 class="ft-hist__titulo"><i class="fas fa-paperclip"></i>Documentos</h3>
                    <span class="ft-hist__meta">{{ $seguimiento->documentos->count() }}</span>
                    <button type="button" @unless (\App\Services\Titulacion\AreasTitulacion::puede('investigacion')) hidden @endunless class="btn btn-sm btn-primary ft-hist__nuevo" data-ft-toggle="{{ $tabId }}-doc-nuevo" aria-expanded="false" aria-controls="{{ $tabId }}-doc-nuevo">
                        <i class="fas fa-plus mr-1"></i>Agregar documento
                    </button>
                </header>

                <form id="{{ $tabId }}-doc-nuevo" method="POST" action="{{ route('titulacion.documentos.guardar', $egresado->id) }}" enctype="multipart/form-data" class="ft-hist__panel mb-3" hidden>
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.documentos.guardar'))>
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-sm-4 mb-2">
                            <label class="small font-weight-bold mb-0">Tipo</label>
                            <select name="tipo" class="form-control form-control-sm" required>
                                @foreach (['ITI', 'Dictamen', 'Tesis', 'Acta', 'Otro'] as $tipoDoc)
                                    <option value="{{ $tipoDoc }}">{{ $tipoDoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-8 mb-2">
                            <label class="small font-weight-bold mb-0">Descripción <span class="text-muted font-weight-normal">(opcional)</span></label>
                            <input type="text" name="descripcion" maxlength="255" class="form-control form-control-sm" placeholder="Ej.: versión final corregida">
                        </div>
                        <div class="form-group col-sm-6 mb-2">
                            <label class="small font-weight-bold mb-0">Subir PDF <span class="text-muted font-weight-normal">(hasta 20 MB)</span></label>
                            <input type="file" name="archivo" accept="application/pdf" class="form-control-file form-control-sm">
                        </div>
                        <div class="form-group col-sm-6 mb-2">
                            <label class="small font-weight-bold mb-0">… o pegar un enlace <span class="text-muted font-weight-normal">(Drive u otro)</span></label>
                            <input type="text" name="url" class="form-control form-control-sm" placeholder="https://…">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check mr-1"></i>Agregar</button>
                    <button type="button" class="btn btn-sm btn-link text-muted" data-ft-toggle="{{ $tabId }}-doc-nuevo">Cancelar</button>
                </fieldset>
</form>

                @if ($seguimiento->documentos->isEmpty())
                    <p class="ft-hist__vacio">Sin documentos registrados.</p>
                @else
                    <ul class="ft-docs">
                        @foreach ($seguimiento->documentos as $doc)
                            @php $esPdf = $doc->url && \App\Services\Titulacion\ArchivosRd::nombreDesdeUrl($doc->url); @endphp
                            <li class="ft-docs__item">
                                <span class="ft-docs__icono"><i class="fas {{ $esPdf ? 'fa-file-pdf' : 'fa-link' }}"></i></span>
                                <div class="ft-rd-linea__info">
                                    <strong class="small">{{ $doc->tipo }}</strong>
                                    @if ($doc->descripcion) <div class="small text-muted">{{ $doc->descripcion }}</div> @endif
                                </div>
                                <div class="ft-acciones">
                                    @if ($doc->url)
                                        <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="ft-accion" title="{{ $esPdf ? 'Ver PDF' : 'Abrir enlace' }}" aria-label="Abrir {{ $doc->tipo }}"><i class="fas fa-external-link-alt"></i></a>
                                    @endif
                                    <form method="POST" action="{{ route('titulacion.documentos.eliminar', $doc->id) }}" data-confirm="Se eliminará este documento." data-confirm-danger data-confirm-titulo="¿Eliminar el documento?">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.documentos.eliminar'))>
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ft-accion ft-accion--peligro" title="Eliminar" aria-label="Eliminar {{ $doc->tipo }}"><i class="fas fa-trash-alt"></i></button>
                                    </fieldset>
</form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @if (collect($filasImportadas ?? [])->isNotEmpty())
                <details class="ft-desplegable mb-3">
                    <summary><i class="fas fa-file-import mr-1"></i>Datos de archivos importados ({{ count($filasImportadas) }})</summary>
                    <div class="small text-muted mt-2 mb-2">Tal como vienen en las hojas de secretaría y expedientes. Sirven para completar o verificar la ficha.</div>
                    @include('titulacion._filas_importadas', ['filas' => $filasImportadas])
                </details>
            @endif
        </div>

        {{-- ═══ Trámites extraordinarios: RD que no son de una etapa (antes al pie de Proceso) ═══ --}}
        <div class="mt-tabs__panel mt-tabs__panel--tramites ft-tramites">
            <div class="ft-seccion"><i class="fas fa-folder-open"></i>Resoluciones que no son de una etapa</div>
            @include('titulacion._rd_slot', ['etiqueta' => 'Cambio de tema de investigación', 'res' => $rdDe('CambioTema'), 'plantilla' => 'cambio_tema', 'tabVolver' => 'tramites'])
            @include('titulacion._rd_slot', ['etiqueta' => 'Cambio de asesor', 'res' => $rdDe('CambioAsesor'), 'plantilla' => 'cambio_asesor', 'tabVolver' => 'tramites'])
            @include('titulacion._rd_slot', ['etiqueta' => 'Retiro del grupo (de esta persona)', 'res' => $rdDe('RetiroGrupo'), 'plantilla' => 'retiro_grupo', 'tabVolver' => 'tramites'])
            @include('titulacion._rd_slot', ['etiqueta' => 'Incorporación de un integrante a este grupo', 'res' => $rdDe('IncorporacionGrupo'), 'plantilla' => 'incorporacion_grupo', 'tabVolver' => 'tramites'])
            @include('titulacion._rd_slot', ['etiqueta' => 'Anulación de PTI', 'res' => $rdDe('AnulacionPti'), 'plantilla' => 'anulacion_pti', 'tabVolver' => 'tramites'])
            <details class="tp-mas" id="rdemitida-{{ $egresado->id }}">
                <summary>Registrar una RD ya emitida (de otro tipo)</summary>
                <div class="mt-2">@include('titulacion._form_resolucion', ['res' => null])</div>
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
