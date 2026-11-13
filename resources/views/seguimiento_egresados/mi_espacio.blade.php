@extends('layouts.seguimiento')
@section('titulo','Red Puklla de Egresados')
@section('contenido')
<style>@scope (.seguimiento-panel) {

:scope{--azul:#224db5;--oro:#bf7b16;--fucsia:#ad2474}*{box-sizing:border-box}:scope{margin:0;background:#f1f5fc;color:#25344b;font:16px/1.5 system-ui}main{max-width:920px;margin:auto;padding:20px}header,section,details{background:white;border-radius:16px;padding:22px;margin:16px 0;border:1px solid #dbe3f0}h1,h2{color:var(--azul)}h2{font-size:1.25rem}label{display:block;margin:14px 0 5px;font-weight:600}input,select,textarea,button{font:inherit;padding:10px;border-radius:8px;border:1px solid #b9c8dc;width:100%;background:white;color:inherit}button,.button{background:var(--azul);color:white;cursor:pointer;text-decoration:none;padding:10px 16px;border-radius:8px}button{width:auto;margin:6px 0}.secondary{background:white;color:var(--azul)}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:0 18px}progress{width:100%;height:20px;accent-color:var(--oro)}small{color:#52627a}.job{border-left:4px solid var(--oro);padding:12px;margin:18px 0;background:#fafbfe}fieldset{border:1px solid #dbe3f0;border-radius:10px;padding:14px;margin:18px 0}legend{font-weight:700}input[type=checkbox]{width:auto;margin-right:8px}.sticky{position:sticky;bottom:0;background:#fff;padding:12px;border-top:2px solid var(--oro)}[hidden]{display:none!important}a{color:var(--azul)}#estado{white-space:pre-line}.results button{display:block;width:100%;text-align:left;background:#eef3ff;color:#223f73}summary{cursor:pointer;font-weight:700}

}</style>
<div class="seguimiento-panel"><main>
<header><a href="{{ route('ayllu.inicio') }}">← Volver a Ayllu Puklla</a><h1>{{ $esEntrevista ? "Entrevista · ".$titular->name." ".$titular->apellidos : "Mi espacio · Puklla en Red" }}</h1><p><strong>Grupo de formación: {{ $clasificacion['etiqueta'] }}</strong> · Clasificación institucional. Si falta información, el equipo la verificará.</p><p>Queremos saber cómo te está yendo y en qué podemos acompañarte. Revisa tus datos y completa lo que corresponda a tu situación.</p><p>Tu información es privada para ti y el equipo autorizado. Las respuestas sobre comunidad, apoyo y encuentros son voluntarias.</p><strong id="avance-texto"></strong><progress id="avance" max="100" value="0"></progress><small id="pendientes"></small><p>Última confirmación: <span id="confirmacion">{{ $ultima ?: 'Aún no confirmada' }}</span></p>
@if($borrador)<button type="button" id="restaurar">Continuar mi borrador guardado</button>@endif
</header>
<form id="perfil-form">
<section><h2>1 · Contacto y residencia</h2><div class="grid" id="contacto"></div><div id="residencia"></div>@unless($esEntrevista)<details><summary>Contacto alternativo para campañas (opcional)</summary><p>Un contacto de emergencia no se usa para campañas sin tu autorización específica.</p><div id="alterno"></div></details>@endunless</section>
<section><h2>2 · Trabajo y trayectoria</h2><div id="situacion"></div><p>Conservamos tus empleos anteriores. Para concluir un empleo, registra su fecha de fin; puedes tener varios empleos actuales.</p><div id="trabajos"></div><button type="button" id="agregar">Añadir un empleo</button></section>
<section><h2>3 · Formación y acompañamiento</h2><div id="formacion"></div>@unless($esEntrevista)<fieldset><legend>¿Hay algo en lo que te gustaría recibir apoyo de Pukllasunchis? (opcional)</legend><div id="apoyo"></div></fieldset>@endunless<p><a href="{{ route('ayllu.perfil') }}">Editar mi presentación comunitaria y regalar una frase o saludo →</a></p></section>
<details><summary>4 · Mi vínculo con las comunidades (opcional)</summary><p>Queremos conocer los caminos que has construido con las comunidades, hayas permanecido, regresado o llegado a una comunidad distinta de la tuya.</p><div id="comunidad"></div></details>
<details><summary>5 · Encuentros y talleres (opcional)</summary><div id="encuentros"></div></details>
@if($esEntrevista)<section><label><input type="checkbox" id="confirmacion-verbal" required>El egresado confirmó verbalmente los datos registrados en esta entrevista.</label><p>Las observaciones del docente se registran en la llamada, separadas de las respuestas del egresado.</p></section>@endif
<div class="sticky"><button type="submit">Confirmar y guardar mi perfil</button> @unless($esEntrevista)<button type="button" class="secondary" id="borrador">Guardar y continuar después</button>@endunless<p id="estado" role="status" aria-live="polite"></p></div>
</form>
@unless($esEntrevista)<section><h2>Mis solicitudes de apoyo</h2>@forelse($casos as $caso)<p><strong>{{ $caso->tema }}</strong> · {{ $caso->estado }} · Próxima gestión: {{ $caso->fecha_gestion?->format('d/m/Y') }}</p>@empty<p>Aún no tienes solicitudes registradas.</p>@endforelse</section>@endunless
@unless($esEntrevista)<section><a class="button" href="{{ route('ayllu.inicio') }}">Continuar a la comunidad Ayllu Puklla →</a><p>El acceso se habilita al confirmar los datos principales; no necesitas completar las preguntas voluntarias.</p></section>@endunless
</main></div>
@endsection
@push('scripts')
<script>
window.PUKLLA_PERFIL = {datos: @json($datos), borrador: @json($borrador), esEntrevista: @json($esEntrevista), campanaId: @json($campanaId), guardar: @json($esEntrevista ? route('seguimiento.panel.entrevista.guardar', ['egresado'=>$titular->id]) : route('seguimiento.mi-espacio.guardar')), draft: @json(route('seguimiento.mi-espacio.borrador')), divisiones: @json(route('seguimiento.divisiones')), lugares: @json(route('seguimiento.lugares'))};
</script>
<script src="{{ asset('js/perfil-egresado.js') }}"></script>
@endpush
