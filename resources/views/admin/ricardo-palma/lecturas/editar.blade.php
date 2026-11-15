@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Corregir acta escaneada - IESP Ricardo Palma')

@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid py-3 al-ui rp-modulo">
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.importar.index') }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Carga de actas
                        </a>
                    </div>
                    <h1>Corregir acta escaneada</h1>
                    <p class="al-hero-sub">
                        {{ $meta['archivo'] ?? 'escaneo.pdf' }}
                        @if (! empty($meta['leido_con'])) · leída con {{ $meta['leido_con'] }} @endif
                        · Nada se guarda en el sistema hasta que pulses «Cargar acta».
                    </p>
                </div>
                <div class="al-hero-acciones">
                    @if ($iaConfigurada)
                        <form method="POST" action="{{ route('rp.lecturas.releer', $borrador->id) }}" class="js-rp-releer">
                            @csrf
                            <button type="submit" class="al-btn al-btn-claro"><i class="fas fa-redo"></i> Volver a leer con IA</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('rp.lecturas.descartar', $borrador->id) }}" class="js-rp-descartar">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="al-btn al-btn-claro"><i class="fas fa-trash-alt"></i> Descartar</button>
                    </form>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        @if (session('success'))
            <div class="al-aviso is-ok mb-3"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
        @endif
        @if (session('error'))
            <div class="al-aviso is-mal mb-3"><i class="fas fa-times-circle"></i><div>{{ session('error') }}</div></div>
        @endif
        @if (! empty($meta['error_lectura']))
            <div class="al-aviso is-mal mb-3">
                <i class="fas fa-robot"></i>
                <div>
                    <strong>La IA no pudo leer el acta.</strong> {{ $meta['error_lectura'] }}
                    <div class="al-suave mt-1">Puedes volver a intentarlo o llenar la tabla a mano mirando el escaneo.</div>
                </div>
            </div>
        @endif
        @if (session('rp_errores_carga'))
            <div class="al-aviso is-mal mb-3">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>No se cargó el acta: hay {{ count(session('rp_errores_carga')) }} problema(s).</strong>
                    <ul class="mb-0 mt-1 pl-3">
                        @foreach (session('rp_errores_carga') as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="rp-lec" id="rpLector"
             data-guardar="{{ route('rp.lecturas.autoguardar', $borrador->id) }}"
             data-stream="{{ route('rp.lecturas.stream', $borrador->id) }}"
             data-necesita-lectura="{{ $necesitaLectura ? '1' : '0' }}"
             data-releer="{{ route('rp.lecturas.releer', $borrador->id) }}"
             data-csrf="{{ csrf_token() }}">

            {{-- Escaneo --}}
            <aside class="rp-lec-pdf">
                <div class="rp-lec-pdf-barra">
                    <span><i class="fas fa-file-pdf"></i> Escaneo</span>
                    <a href="{{ route('rp.lecturas.pdf', $borrador->id) }}" target="_blank" class="al-btn al-btn-sm al-btn-claro" title="Abrir en otra pestaña (útil con dos pantallas)">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <button type="button" class="al-btn al-btn-sm al-btn-claro js-rp-ocultar-pdf" title="Ocultar el escaneo para ver la tabla completa">
                        <i class="fas fa-compress-alt"></i>
                    </button>
                </div>
                <iframe src="{{ route('rp.lecturas.pdf', $borrador->id) }}#view=FitH" title="Acta escaneada"></iframe>
            </aside>

            <section class="rp-lec-editor al-form">
                @if ($necesitaLectura)
                    <div id="rpLectorProgresoIa" class="al-card mb-4 rp-lec-progreso-box">
                        <div class="al-card-cuerpo text-center py-4">
                            <div class="rp-lec-progreso-icono mb-3">
                                <div class="spinner-border" style="width: 3.2rem; height: 3.2rem;" role="status">
                                    <span class="sr-only">Leyendo...</span>
                                </div>
                            </div>
                            <h3 class="font-weight-bold mb-1" id="rpLectorProgresoTitulo">Leyendo acta con Inteligencia Artificial</h3>
                            <p class="rp-lec-progreso-fase mb-3" id="rpLectorProgresoFase">Conectando con la IA de Gemini...</p>

                            <div class="progress mb-3 mx-auto" style="height: 10px; max-width: 480px; border-radius: 5px; overflow: hidden;">
                                <div id="rpLectorProgresoBarra" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 15%;"></div>
                            </div>

                            <div class="d-flex justify-content-center align-items-center mb-3 rp-lec-progreso-meta" style="gap: 1.5rem; font-size: .88rem;">
                                <span><i class="far fa-clock"></i> Tiempo transcurrido: <strong id="rpLectorProgresoTiempo">00:00</strong></span>
                                <span><i class="far fa-file-pdf"></i> {{ $meta['archivo'] ?? 'escaneo.pdf' }}</span>
                            </div>

                            <div class="al-aviso is-info mx-auto mb-3 text-left" style="max-width: 580px; font-size: .85rem;">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    El escaneo ya está cargado a la izquierda. La IA está transcribiendo automáticamente los encabezados, asignaturas, lista de estudiantes y notas.
                                    Este proceso suele tomar entre <strong>30 y 90 segundos</strong>. No es necesario recargar.
                                </div>
                            </div>

                            <div class="mt-2" id="rpLectorAccionesProgreso">
                                <button type="button" class="al-btn al-btn-sm al-btn-claro js-rp-cancelar-ia">
                                    <i class="fas fa-pen"></i> Abrir editor manual sin esperar
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="rpLectorFormulario" @if ($necesitaLectura) style="display: none;" @endif>
                    {{-- Resumen y acciones (fijo arriba) --}}
                    <div class="rp-lec-resumen">
                        <button type="button" class="al-btn al-btn-sm al-btn-claro js-rp-mostrar-pdf" hidden>
                            <i class="fas fa-file-pdf"></i> Ver escaneo
                        </button>
                        <span class="rp-lec-chip"><i class="fas fa-users"></i> <b data-cuenta="total-alumnos">0</b> alumnos</span>
                        <span class="rp-lec-chip is-ok" title="Filas que cuadran"><i class="fas fa-check"></i> <b data-cuenta="ok">0</b> cuadran</span>
                        <button type="button" class="rp-lec-chip is-mal js-rp-ir" data-ir="mal" title="Ir a la siguiente fila con error">
                            <i class="fas fa-times"></i> <b data-cuenta="mal">0</b> con error
                        </button>
                        <button type="button" class="rp-lec-chip is-duda js-rp-ir" data-ir="duda" title="Ir a la siguiente casilla dudosa">
                            <i class="fas fa-question"></i> <b data-cuenta="duda">0</b> por revisar
                        </button>
                        <span class="rp-lec-guardado al-suave" data-estado-guardado>Borrador guardado</span>
                        <form method="POST" action="{{ route('rp.lecturas.cargar', $borrador->id) }}" class="m-0 ml-auto js-rp-cargar">
                            @csrf
                            <input type="hidden" name="datos">
                            <button type="submit" class="al-btn al-btn-oro"><i class="fas fa-cloud-upload-alt"></i> Cargar acta</button>
                        </form>
                    </div>

                {{-- Encabezado --}}
                <article class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-file-invoice"></i></span>
                        <div>
                            <h2>Encabezado del acta</h2>
                            <p data-texto-especialidad>Revisa que coincida con el escaneo.</p>
                        </div>
                    </div>
                    <div class="al-card-cuerpo">
                        <div class="al-form-grid rp-lec-cab">
                            <label>Año académico *<input class="al-campo" data-enc="periodo" placeholder="1993-I"></label>
                            <label>Semestre *<input class="al-campo" data-enc="semestre" placeholder="PRIMER"></label>
                            <label>Especialidad *
                                <select class="al-campo" data-enc="programa">
                                    <option value="">— Elegir —</option>
                                    @foreach ($programas as $p)
                                        <option value="{{ $p->codigo }}">{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Turno<input class="al-campo" data-enc="turno" placeholder="MAÑANA"></label>
                            <label>Sección<input class="al-campo" data-enc="seccion" placeholder="ÚNICA"></label>
                            <label>Fecha<input class="al-campo" data-enc="fecha" placeholder="08-09-93"></label>
                            <label>N° de acta<input class="al-campo" data-enc="nro_acta" placeholder="Opcional"></label>
                            <label>Las casillas tienen
                                <select class="al-campo" data-en-puntaje>
                                    <option value="1">Puntaje (nota × créditos)</option>
                                    <option value="0">Nota (0 a 20)</option>
                                </select>
                            </label>
                        </div>
                        <div class="rp-lec-avisos" data-avisos-cab></div>
                    </div>
                </article>

                {{-- Tabla del acta --}}
                <article class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-table"></i></span>
                        <div>
                            <h2>Alumnos y puntajes</h2>
                            <p>
                                <span class="rp-lec-leyenda is-mal">no cuadra</span>
                                <span class="rp-lec-leyenda is-duda">la IA dudó</span>
                                Debajo de cada puntaje va la nota que resulta (en rojo si es menor de 11, igual que la tinta del acta).
                                Total y promedio del acta se comparan con lo calculado.
                            </p>
                        </div>
                    </div>
                    <div class="rp-lec-tabla-marco">
                        <table class="rp-lec-tabla">
                            <thead data-cabecera></thead>
                            <tbody data-cuerpo></tbody>
                        </table>
                    </div>
                    <div class="al-form-pie d-flex align-items-center justify-content-between flex-wrap" style="gap: .75rem;">
                        <div class="d-flex align-items-center flex-wrap" style="gap: .5rem;">
                            <button type="button" class="al-btn al-btn-sm al-btn-claro js-rp-agregar-alumno"><i class="fas fa-user-plus"></i> Agregar alumno</button>
                            <button type="button" class="al-btn al-btn-sm al-btn-claro js-rp-agregar-asignatura"><i class="fas fa-plus"></i> Agregar asignatura</button>
                            <span class="al-suave rp-lec-ayuda">Enter baja a la misma casilla del siguiente alumno.</span>
                        </div>
                        <div class="d-flex align-items-center flex-wrap ml-auto" style="gap: .5rem;">
                            <button type="button" class="al-btn al-btn-sm al-btn-verde js-rp-aprobar-todo" title="Aprobar todo el bloque leído (quita las dudas de la IA en todas las filas)">
                                <i class="fas fa-check-double"></i> Aprobar bloque leído
                            </button>
                            <button type="button" class="al-btn al-btn-sm al-btn-oro js-rp-cargar-trigger" title="Guardar todos los registros leídos en el sistema">
                                <i class="fas fa-cloud-upload-alt"></i> Guardar todos los registros en bloque
                            </button>
                        </div>
                    </div>
                </article>
                </div> {{-- fin #rpLectorFormulario --}}
            </section>
        </div>

        <script type="application/json" id="rpLectorDatos">@json($datos)</script>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/rp-lector.js') }}?v={{ @filemtime(public_path('js/rp-lector.js')) }}"></script>
@endpush
