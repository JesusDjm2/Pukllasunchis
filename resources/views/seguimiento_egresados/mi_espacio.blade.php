@extends('layouts.seguimiento')
@section('titulo', $esEntrevista ? 'Entrevista de campaña · '.$titular->name.' '.$titular->apellidos : 'Actualizar mis datos y solicitar apoyo · Seguimiento de Egresados')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/seguimiento-egresados.css') }}?v={{ filemtime(public_path('css/seguimiento-egresados.css')) }}">
@endpush
@section('contenido')
<div class="seguimiento-panel"><div class="seg-contenedor">
<header>
@if($esEntrevista)
    <a href="{{ route('seguimiento-egresados.mis-asignaciones', array_filter(['campana_id' => $campanaId, 'grupo_formacion' => $grupoFormacion ?? null])) }}">← Volver a Mis entrevistas</a>
    <h1>Entrevista · {{ $titular->name }} {{ $titular->apellidos }}</h1>
    @if(!empty($campana))
        <p><span class="seg-origen-chip seg-origen-institucional"><i class="fas fa-bullhorn"></i> Campaña: {{ $campana->nombre }} ({{ $campana->codigo }})</span>
        <span class="seg-origen-chip seg-origen-confirmado">Titular: {{ $titular->name }} {{ $titular->apellidos }} · DNI {{ $titular->dni ?: ($titular->alumno?->dni ?: ($titular->alumnoB?->dni ?: 'Sin DNI')) }}</span></p>
    @endif
@else
    <a href="{{ route('egresado.intranet') }}">← Volver a Mi trayectoria y titulación</a>
    <h1>Actualizar mis datos y solicitar apoyo · Mi espacio</h1>
@endif
<p><strong>Grupo de formación: {{ $clasificacion['etiqueta'] }}</strong> ·
@if(($clasificacion['fuente'] ?? '') === 'trayectoria_evento_egreso')
    <span class="seg-origen-chip seg-origen-institucional">Registrado por la institución (evento de egreso)</span>
@elseif(($clasificacion['fuente'] ?? '') === 'programa_institucional' || ($clasificacion['fuente'] ?? '') === 'ficha_academica')
    <span class="seg-origen-chip seg-origen-institucional">Registrado por la institución (expediente académico)</span>
@else
    <span class="seg-origen-chip seg-origen-verificar">Por verificar por secretaría académica</span>
@endif
</p>
<p>Queremos saber cómo te está yendo y en qué podemos acompañarte. Revisa primero la trayectoria oficial registrada y luego confirma o actualiza los datos de seguimiento.</p>
<p>Tu información es privada para ti y el equipo autorizado. Las respuestas sobre comunidad, apoyo y encuentros son voluntarias.</p>
<strong id="avance-texto"></strong><progress id="avance" max="100" value="0"></progress><small id="pendientes"></small>
<p>Última confirmación en seguimiento: <span id="confirmacion">{{ $ultima ?: 'Aún no confirmada' }}</span>
@if($ultima)
    <span class="seg-origen-chip seg-origen-confirmado">Confirmado en seguimiento</span>
@else
    <span class="seg-origen-chip seg-origen-verificar">Por confirmar en campaña</span>
@endif
</p>
@if($borrador)<button type="button" id="restaurar">Continuar mi borrador guardado</button>@endif
</header>

@if(!empty($esPiloto))
    <div id="banner-piloto-beta" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: #fff; padding: 18px 22px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.25);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <span class="badge" style="background: #fbbf24; color: #78350f; font-weight: 800; padding: 4px 10px; border-radius: 12px; margin-bottom: 6px; display: inline-block;">
                    🚀 PILOTO DE LA RED: {{ $codigoPiloto ?? 'PUKLLA-BETA-PILOTO' }}
                </span>
                <h3 style="margin: 4px 0; color: #fff; font-size: 1.15rem;">Bienvenido/a al Piloto de la Red de Egresados Pukllasunchis</h3>
                <p style="margin: 0; font-size: 0.9rem; opacity: 0.95; max-width: 650px;">
                    Tu participación ayuda a consolidar la Red. Si la trayectoria oficial registrada y tus datos precargados son correctos, puedes confirmar tu participación en 1 solo clic sin alterar tu expediente.
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <button type="button" id="btn-confirmar-sin-cambios" style="background: #10b981; color: #fff; font-weight: 700; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem;">
                    <i class="fas fa-check-circle"></i> Confirmar mis datos sin cambios
                </button>
                <a href="{{ route('red.inicio') }}" id="btn-ir-red-puklla" style="background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.4); font-weight: 600; padding: 10px 18px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem;">
                    Continuar a Red Puklla →
                </a>
            </div>
        </div>
        <div id="piloto-feedback" style="margin-top: 12px; font-size: 0.88rem; display: none; background: rgba(0,0,0,0.2); padding: 8px 12px; border-radius: 6px;"></div>
    </div>
@endif

{{-- 0 · TRAYECTORIA REGISTRADA POR LA INSTITUCIÓN (P0: visible ANTES del formulario de actualización) --}}
<section id="trayectoria-institucional" aria-labelledby="titulo-trayectoria-oficial">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
    <h2 id="titulo-trayectoria-oficial" class="mb-0">0 · Trayectoria registrada por la institución (expediente oficial)</h2>
    <span class="seg-origen-chip seg-origen-institucional"><i class="fas fa-university"></i> Fuente: Actas, resoluciones y fichas académicas</span>
</div>
<p class="mb-2"><small>Verifica estos datos antes de actualizar la ficha. Los hitos oficiales provienen exclusivamente del expediente institucional y no se sobrescriben desde el formulario de campaña.</small></p>

<div class="seg-hitos-grid">
    <div class="seg-hito-card">
        <strong>Egreso institucional</strong>
        <div>
            @if($hitosOficiales['egreso']['acreditado'] ?? false)
                <span class="seg-origen-chip seg-origen-institucional">Registrado por la institución</span>
                <div>Acreditado ({{ implode(', ', $hitosOficiales['egreso']['fechas'] ?? []) ?: 'Con evento oficial' }})</div>
            @else
                <span class="seg-origen-chip seg-origen-verificar">Por verificar</span>
                <div>{{ $hitosOficiales['egreso']['fuente'] ?? 'No verificado en el sistema' }}</div>
            @endif
        </div>
    </div>
    <div class="seg-hito-card">
        <strong>Grado / Bachiller</strong>
        <div>
            @if($hitosOficiales['grado_bachiller']['acreditado'] ?? false)
                <span class="seg-origen-chip seg-origen-institucional">Registrado por la institución</span>
            @else
                <span class="seg-origen-chip seg-origen-verificar">Por verificar / En trámite</span>
            @endif
            <div>{{ $hitosOficiales['grado_bachiller']['detalle'] ?? 'No verificado en el sistema' }}</div>
        </div>
    </div>
    <div class="seg-hito-card">
        <strong>Título / Licenciatura</strong>
        <div>
            @if($hitosOficiales['titulacion']['titulo_acreditado'] ?? false)
                <span class="seg-origen-chip seg-origen-institucional">Registrado por la institución</span>
            @else
                <span class="seg-origen-chip seg-origen-verificar">{{ ($hitosOficiales['titulacion']['estado'] ?? '') === 'en_tramite' ? 'Trámite en curso' : 'Por verificar' }}</span>
            @endif
            <div>{{ $hitosOficiales['titulacion']['detalle'] ?? 'No verificado en el sistema' }}</div>
            @if(!empty($hitosOficiales['titulacion']['expediente']))
                <small>Expediente: {{ $hitosOficiales['titulacion']['expediente'] }}</small>
            @endif
        </div>
    </div>
</div>

@if(!empty($hitosOficiales['trayectorias']))
    @foreach($hitosOficiales['trayectorias'] as $mod => $tray)
        <div class="job my-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <strong>Trayectoria {{ $mod }} · {{ $tray['programa_nombre'] }}</strong>
                <span class="seg-origen-chip seg-origen-institucional">{{ $tray['fuente_academica'] }}</span>
            </div>
            <p class="mb-1 mt-1">
                <strong>Avance académico registrado:</strong>
                @if($tray['cursos_registrados'] > 0)
                    {{ $tray['cursos_aprobados'] }} / {{ $tray['cursos_registrados'] }} cursos aprobados
                    · <strong>{{ $tray['creditos_aprobados'] }} créditos aprobados</strong>
                    · {{ $tray['ciclos_o_periodos'] }} {{ $mod === 'PPD' ? 'ciclos' : 'periodos' }} registrados
                    @if($tray['promedio_ponderado']) · Promedio oficial: <strong>{{ $tray['promedio_ponderado'] }}</strong>@endif
                @else
                    <span class="text-muted">Cohorte previa a 2024-II (expediente físico/histórico sin desglose de cursos en tabla digital).</span>
                @endif
            </p>
            @if($tray['fecha_egreso'] || $tray['resolucion_egreso'])
                <p class="mb-0"><small>Fecha de egreso registrada: {{ $tray['fecha_egreso'] ?: '—' }} @if($tray['resolucion_egreso']) · Documento: {{ $tray['resolucion_egreso'] }} @endif</small></p>
            @endif
        </div>
    @endforeach
@endif

@if(!empty($hitosOficiales['titulacion']['documentos']))
    <p class="mb-1"><small><strong>Actas y resoluciones vinculadas:</strong>
        @foreach($hitosOficiales['titulacion']['documentos'] as $doc)
            <span class="badge badge-light border mr-1">{{ $doc['tipo'] }}: {{ $doc['referencia'] }} {{ $doc['fecha'] ? '('.$doc['fecha'].')' : '' }}</span>
        @endforeach
    </small></p>
@endif
</section>

<form id="perfil-form">
<section>
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <h2>1 · Contacto y residencia</h2>
        <span class="seg-origen-chip {{ $ultima ? 'seg-origen-confirmado' : 'seg-origen-verificar' }}">
            {{ $ultima ? 'Precargado desde última confirmación' : 'Precargado desde ficha / encuesta (por confirmar)' }}
        </span>
    </div>
    <div class="grid" id="contacto"></div>
    <div id="residencia"></div>
    <details class="mt-2">
        <summary>¿Algún dato oficial de programa, año de egreso o titulación presenta discrepancia?</summary>
        <p><small>Reporta aquí cualquier diferencia respecto a tu expediente oficial para que Coordinación y Secretaría Académica lo verifiquen sin alterar directamente las actas.</small></p>
        <div id="discrepancia"></div>
    </details>
    @unless($esEntrevista)
        <details><summary>Contacto alternativo para campañas (opcional)</summary><p>Un contacto de emergencia no se usa para campañas sin tu autorización específica.</p><div id="alterno"></div></details>
    @endunless
</section>

<section>
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <h2>2 · Trabajo y trayectoria laboral</h2>
        <span class="seg-origen-chip seg-origen-confirmado">Episodios longitudinales activos e históricos</span>
    </div>
    @if(!empty($antecedentesHistoricos))
        <details class="mb-3">
            <summary>Antecedentes declarados en encuestas y cortes previos ({{ count($antecedentesHistoricos) }}) — Referencia para contraste</summary>
            @foreach($antecedentesHistoricos as $ant)
                <div class="job">
                    <p class="mb-1"><strong>{{ $ant['fuente_nombre'] }}</strong> · Corte: {{ $ant['fecha_corte'] }} · Fecha de observación: {{ $ant['observada_en'] }}
                        <span class="seg-origen-chip seg-origen-confirmado">{{ $ant['naturaleza'] }} ({{ $ant['calidad'] }}) · Vínculo: {{ $ant['trayectoria_tipo'] }}</span>
                    </p>
                    <p class="mb-1">Situación declarada en ese corte: <strong>{{ $ant['situacion_laboral_declarada'] }}</strong>
                        @if($ant['insercion_educativa_declarada']) · {{ $ant['insercion_educativa_declarada'] }}@endif
                        @if($ant['sector_declarado']) · Sector: {{ $ant['sector_declarado'] }}@endif
                        @if($ant['cargo_declarado']) · Cargo/función: {{ $ant['cargo_declarado'] }}@endif
                        @if($ant['lugar_trabajo_texto_origen']) · Lugar (texto origen): {{ $ant['lugar_trabajo_texto_origen'] }}@endif
                    </p>
                    @if($ant['satisfaccion_historica_archivo'])<p class="mb-0"><small>Satisfacción histórica (archivo previo): {{ $ant['satisfaccion_historica_archivo'] }}</small></p>@endif
                </div>
            @endforeach
        </details>
    @endif
    <div id="situacion"></div>
    <p>Conservamos tus empleos anteriores. Para concluir un empleo, registra su fecha de fin; puedes tener varios empleos actuales.</p>
    <div id="trabajos"></div>
    <button type="button" id="agregar">Añadir un empleo</button>
</section>

<section>
    <h2>3 · Formación y acompañamiento</h2>
    @if(!empty($antecedentesHistoricos) && collect($antecedentesHistoricos)->contains(fn($a) => !empty($a['aporte_formacion_original']) || !empty($a['necesidades_formacion_original'])))
        <details class="mb-3">
            <summary>Expectativas formativas declaradas en cortes anteriores</summary>
            @foreach($antecedentesHistoricos as $ant)
                @if($ant['aporte_formacion_original'] || $ant['necesidades_formacion_original'])
                    <div class="job">
                        <p class="mb-1"><strong>{{ $ant['fuente_nombre'] }} ({{ $ant['fecha_corte'] }})</strong></p>
                        @if($ant['aporte_formacion_original'])<p class="mb-1"><small>Aporte de formación declarado: {{ $ant['aporte_formacion_original'] }}</small></p>@endif
                        @if($ant['necesidades_formacion_original'])<p class="mb-0"><small>Necesidades formativas declaradas: {{ $ant['necesidades_formacion_original'] }}</small></p>@endif
                    </div>
                @endif
            @endforeach
        </details>
    @endif
    <div id="formacion"></div>
    @unless($esEntrevista)
        <fieldset><legend>¿Hay algo en lo que te gustaría recibir apoyo de Pukllasunchis? (opcional)</legend><div id="apoyo"></div></fieldset>
        <p><a href="{{ route('ayllu.perfil') }}">Editar mi presentación en Ayllu Puklla y regalar una frase o saludo →</a></p>
    @endunless
</section>

<details><summary>4 · Mi vínculo con las comunidades (opcional)</summary><p>Queremos conocer los caminos que has construido con las comunidades, hayas permanecido, regresado o llegado a una comunidad distinta de la tuya.</p><div id="comunidad"></div></details>
<details><summary>5 · Encuentros y talleres (opcional)</summary><div id="encuentros"></div></details>

@if($esEntrevista)
    <section>
        <label><input type="checkbox" id="confirmacion-verbal" required>El egresado confirmó verbalmente los datos registrados en esta entrevista.</label>
        <p>Las observaciones del docente se registran en la llamada, separadas de las respuestas del egresado.</p>
    </section>
@endif

<div class="sticky">
    <button type="submit">{{ $esEntrevista ? 'Confirmar actualización de campaña' : 'Confirmar y guardar mi perfil' }}</button>
    @unless($esEntrevista)
        <button type="button" class="secondary" id="borrador">Guardar y continuar después</button>
    @else
        <a class="button secondary" href="{{ route('seguimiento-egresados.mis-asignaciones', array_filter(['campana_id' => $campanaId, 'grupo_formacion' => $grupoFormacion ?? null])) }}">Volver a Mis entrevistas</a>
    @endunless
    <p id="estado" role="status" aria-live="polite"></p>
</div>
</form>

@unless($esEntrevista)
    <section>
        <h2>Mis solicitudes de apoyo y revisión</h2>
        @forelse($casos as $caso)
            <p><strong>{{ $caso->tema }}</strong> · {{ $caso->estado }} · Próxima gestión: {{ $caso->fecha_gestion?->format('d/m/Y') }}</p>
        @empty
            <p>Aún no tienes solicitudes registradas.</p>
        @endforelse
    </section>
@endunless

<details id="antecedentes-seguimiento">
    <summary>Detalle completo de antecedentes históricos e hitos oficiales</summary>
    <fieldset>
        <legend>Hitos oficiales en el sistema</legend>
        <p><strong>Egreso institucional:</strong> {{ ($hitosOficiales['egreso']['acreditado'] ?? false) ? 'Acreditado ('.implode(', ', $hitosOficiales['egreso']['fechas'] ?? []).')' : 'No verificado en el sistema' }}</p>
        <p><strong>Titulación:</strong> {{ $hitosOficiales['titulacion']['detalle'] ?? 'No verificado en el sistema' }}</p>
        <p><strong>Grado / Bachiller:</strong> {{ $hitosOficiales['grado_bachiller']['detalle'] ?? 'No verificado en el sistema' }}</p>
        <small>Los hitos oficiales provienen exclusivamente de actas, resoluciones y registros académicos institucionales. Las encuestas históricas no modifican tu expediente.</small>
    </fieldset>
    <fieldset>
        <legend>Antecedentes de seguimiento (observaciones históricas declaradas)</legend>
        @forelse($antecedentesHistoricos ?? [] as $ant)
            <div class="job">
                <p><strong>{{ $ant['fuente_nombre'] }}</strong> · Corte: {{ $ant['fecha_corte'] }} · Fecha de observación: {{ $ant['observada_en'] }}</p>
                <p><small>Naturaleza: {{ $ant['naturaleza'] }} · Calidad: {{ $ant['calidad'] }} · Vínculo de trayectoria: {{ $ant['trayectoria_tipo'] }}</small></p>
                <p>Situación declarada en ese corte: <strong>{{ $ant['situacion_laboral_declarada'] }}</strong>
                @if($ant['insercion_educativa_declarada']) · {{ $ant['insercion_educativa_declarada'] }}@endif
                @if($ant['sector_declarado']) · Sector: {{ $ant['sector_declarado'] }}@endif
                @if($ant['cargo_declarado']) · Cargo/función: {{ $ant['cargo_declarado'] }}@endif
                @if($ant['lugar_trabajo_texto_origen']) · Lugar (texto origen): {{ $ant['lugar_trabajo_texto_origen'] }}@endif
                </p>
                @if($ant['satisfaccion_historica_archivo'])<p><small>Satisfacción histórica (archivo previo): {{ $ant['satisfaccion_historica_archivo'] }}</small></p>@endif
                @if($ant['aporte_formacion_original'])<p><small>Aporte de formación declarado: {{ $ant['aporte_formacion_original'] }}</small></p>@endif
                @if($ant['necesidades_formacion_original'])<p><small>Necesidades formativas declaradas: {{ $ant['necesidades_formacion_original'] }}</small></p>@endif
                @if($ant['tiene_bienestar_restringido'] && (!$esEntrevista || app(\App\Services\SeguimientoEgresado\SeguimientoAutorizacionService::class)->puedeVerNotasConfidencialesBienestar()))
                    <p><small>Bienestar (acceso restringido): {{ $ant['bienestar_restringido'] }}</small></p>
                @endif
            </div>
        @empty
            <p>No se registran observaciones históricas previas vinculadas a esta cuenta.</p>
        @endforelse
    </fieldset>
</details>

@unless($esEntrevista)
    @if(Route::has('red.inicio') && app(\App\Services\RedPuklla\AccesoRed::class)->entrar(auth()->user()))
        <section>
            <a class="button" href="{{ route('red.inicio') }}">Continuar a Red Puklla de Egresados →</a>
            <p>El acceso se habilita al confirmar los datos principales; no necesitas completar las preguntas voluntarias.</p>
        </section>
    @endif
@endunless
</div></div>
@endsection
@push('scripts')
<script>
window.PUKLLA_PERFIL = {datos: @json($datos), borrador: @json($borrador), esEntrevista: @json($esEntrevista), campanaId: @json($campanaId), guardar: @json($esEntrevista ? route('seguimiento.panel.entrevista.guardar', ['egresado'=>$titular->id]) : route('seguimiento.mi-espacio.guardar')), draft: @json(route('seguimiento.mi-espacio.borrador')), divisiones: @json(route('seguimiento.divisiones')), lugares: @json(route('seguimiento.lugares'))};

const btnConfirmarSinCambios = document.getElementById('btn-confirmar-sin-cambios');
if (btnConfirmarSinCambios) {
    btnConfirmarSinCambios.addEventListener('click', async function () {
        btnConfirmarSinCambios.disabled = true;
        btnConfirmarSinCambios.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Registrando confirmación...';
        const feedback = document.getElementById('piloto-feedback');

        try {
            const res = await fetch("{{ route('seguimiento.mi-espacio.confirmar-sin-cambios') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    codigo_piloto: "{{ $codigoPiloto ?? 'PUKLLA-BETA-PILOTO' }}"
                })
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                feedback.style.display = 'block';
                feedback.innerHTML = `<span style="color:#fca5a5;">⚠️ ${data.message || 'Error al confirmar datos.'}</span>`;
                btnConfirmarSinCambios.disabled = false;
                btnConfirmarSinCambios.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar mis datos sin cambios';
                return;
            }
            feedback.style.display = 'block';
            feedback.innerHTML = `<span style="color:#6ee7b7;">✅ ${data.message} Puedes continuar a la Red de Egresados cuando desees.</span>`;
            btnConfirmarSinCambios.style.display = 'none';

            const confirmacionSpan = document.getElementById('confirmacion');
            if (confirmacionSpan) {
                confirmacionSpan.textContent = new Date().toLocaleString('es-PE');
            }
            const btnRed = document.getElementById('btn-ir-red-puklla');
            if (btnRed) {
                btnRed.style.background = '#10b981';
                btnRed.style.borderColor = '#10b981';
                btnRed.style.fontWeight = '800';
            }
        } catch (err) {
            feedback.style.display = 'block';
            feedback.innerHTML = `<span style="color:#fca5a5;">⚠️ Error de conexión: ${err.message}</span>`;
            btnConfirmarSinCambios.disabled = false;
            btnConfirmarSinCambios.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar mis datos sin cambios';
        }
    });
}
</script>
<script src="{{ asset('js/perfil-egresado.js') }}?v={{ filemtime(public_path('js/perfil-egresado.js')) }}"></script>
@endpush
