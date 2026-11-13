<details class="rp-muted"><summary>{{ $puedeModerar?'Revisar o reportar contenido':'Reportar contenido' }}</summary><form method="post" action="{{ route('red.moderar') }}">@csrf<input name="tipo" type="hidden" value="{{ $tipo }}"><input name="referencia_id" type="hidden" value="{{ $referencia }}"><label>Acción<select name="accion"><option value="reportar">Reportar para revisión</option>@if($puedeModerar)<option value="ocultar">Ocultar durante la revisión</option><option value="retirar">Retirar de la Red</option><option value="restaurar">Restaurar</option>@endif</select></label><label>Motivo de la intervención y política incumplida<textarea name="motivo" required minlength="10" maxlength="2000"></textarea></label><button>Registrar</button></form></details>
@php
    $autorContenido = $tipo === 'publicacion'
        ? ($p->user_id ?? null)
        : ($oferta->autor_red_id ?? null);

    $estadoContenido = $tipo === 'publicacion'
        ? ($p->estado_red ?? null)
        : ($oferta->estado_red ?? null);
@endphp

@if (
    (int) auth()->id() === (int) $autorContenido
    && in_array($estadoContenido, ['oculto', 'retirado'], true)
)
    <details>
        <summary>Solicitar revisión de esta decisión</summary>

        <form method="POST" action="{{ route('red.moderar') }}">
            @csrf

            <input type="hidden" name="tipo" value="{{ $tipo }}">
            <input
                type="hidden"
                name="referencia_id"
                value="{{ $referencia }}"
            >
            <input
                type="hidden"
                name="accion"
                value="solicitar_revision"
            >

            <label>
                Explica por qué solicitas la revisión
                <textarea
                    name="motivo"
                    required
                    minlength="10"
                    maxlength="2000"
                ></textarea>
            </label>

            <button type="submit">Enviar solicitud de revisión</button>
        </form>
    </details>
@endif
