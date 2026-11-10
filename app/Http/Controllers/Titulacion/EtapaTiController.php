<?php

namespace App\Http\Controllers\Titulacion;

use App\Models\Titulacion\Participante;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Tesis;
use App\Models\User;
use App\Services\Titulacion\ReglasProceso;
use Illuminate\Http\Request;

/**
 * Pasos de la etapa TI con el PTI ya aprobado (el PTI queda de solo lectura):
 * informe ITI del asesor (enlace), asesores (1 o más) y jurado (1 o más, cargo opcional).
 * La fecha de exposición, la RD, el repositorio y el Bachiller van por tesisGuardar / RD.
 * Cada paso puede aplicarse también a todo el grupo.
 */
class EtapaTiController extends TitulacionController
{
    /** Hito que debe estar logrado para registrar asesores/jurado de cada etapa. */
    private const REQUISITO_ETAPA = ['ti' => 'pti_carta', 'tesis' => 'bachiller'];

    /** Informe ITI del asesor: enlace externo (Drive, etc.) y el día en que lo compartió (plazo de
     * 8 días hábiles, ver PasosProceso::plazoInforme). Se guarda en el PTI vigente (link_iti, fecha_iti). */
    public function informe(Request $request, User $egresado)
    {
        $data = $request->validate(['link_iti' => 'nullable|url|max:500', 'fecha_iti' => 'nullable|date'], [
            'link_iti.url' => 'El informe debe ser un enlace completo, por ejemplo https://drive.google.com/…',
        ]);
        $seguimiento = SeguimientoTitulacion::with('pti')->where('user_id', $egresado->id)->firstOrFail();
        if ($motivo = $this->bloqueo($seguimiento, 'ti')) {
            return $this->volverAlAcordeon($egresado->id, $motivo, 'proceso', 'error');
        }
        abort_unless($seguimiento->pti, 404);
        $seguimiento->pti->update($this->datosInforme($seguimiento->pti, $data));

        [$aplicados, $omitidos] = [0, []];
        foreach ($this->companerosDestino($seguimiento, $request) as $otro) {
            if (! $otro->pti || ($motivo = $this->bloqueo($otro, 'ti'))) {
                $omitidos[] = $otro->user?->apellidos.' ('.($motivo ?? 'sin PTI').')';
                continue;
            }
            $otro->pti->update($this->datosInforme($otro->pti, $data));
            $aplicados++;
        }

        return $this->volverAlAcordeon($egresado->id, 'Informe ITI guardado'.$this->textoGrupo($aplicados, $omitidos), 'proceso', $omitidos ? 'warning' : 'success');
    }

    /** Enlace del informe y su fecha: la indicada, la que ya tenía o la de hoy; sin enlace, sin fecha. */
    private function datosInforme(\App\Models\Titulacion\Pti $pti, array $data): array
    {
        $link = $data['link_iti'] ?? null;

        return [
            'link_iti' => $link,
            'fecha_iti' => $link ? ($data['fecha_iti'] ?? $pti->fecha_iti?->toDateString() ?? now()->toDateString()) : null,
        ];
    }

    /**
     * Correo al jurado (exposición del TI, o dictaminantes / jurado de la sustentación de la Tesis):
     * uno por destinatario elegido, con el asunto y el
     * mensaje que escribe el gestor (la ficha propone uno con grupo, título, fecha y enlaces).
     */
    public function correoJurado(Request $request, User $egresado)
    {
        $data = $request->validate([
            'destinatarios' => 'required|array|min:1',
            'destinatarios.*' => 'integer',
            'asunto' => 'required|string|max:200',
            'mensaje' => 'required|string|max:5000',
            'etapa' => 'nullable|in:ti,tesis',
        ], [
            'destinatarios.required' => 'Elija al menos un integrante del jurado.',
            'asunto.required' => 'Escriba el asunto del correo.',
            'mensaje.required' => 'Escriba el mensaje.',
        ]);
        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        $jurado = Participante::with(['docente', 'asesorExterno'])
            ->where('seguimiento_id', $seguimiento->id)->where('etapa', $data['etapa'] ?? 'ti')->where('funcion', 'jurado')
            ->whereIn('id', $data['destinatarios'])->get();

        [$enviados, $fallidos] = [[], []];
        foreach ($jurado as $p) {
            $nombre = (string) $p->asesorNombre();
            if (! ($correo = $p->correo())) {
                $fallidos[] = $nombre.' (sin correo registrado)';
                continue;
            }
            try {
                \Illuminate\Support\Facades\Mail::mailer(\App\Mail\Titulacion\Remitente::mailer('investigacion'))->to($correo)->send(new \App\Mail\Titulacion\CorreoJuradoMail(
                    $data['asunto'], $data['mensaje'], $nombre
                ));
                $enviados[] = $nombre;
            } catch (\Throwable $e) {
                report($e);
                $fallidos[] = $nombre.' (no se pudo enviar)';
            }
        }

        $texto = $enviados ? 'Correo enviado a '.implode(', ', $enviados).'.' : 'No se envió ningún correo.';
        if ($fallidos) {
            $texto .= ' Sin enviar: '.implode('; ', $fallidos).'.';
        }

        return $this->volverAlAcordeon($egresado->id, $texto, 'proceso', $fallidos ? ($enviados ? 'warning' : 'error') : 'success');
    }

    /** Agrega un asesor o un integrante del jurado (docente o del catálogo), con cargo opcional. */
    public function participanteGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'etapa' => 'required|in:ti,tesis',
            'funcion' => 'required|in:'.implode(',', array_keys(Participante::FUNCIONES)),
            'docentes' => 'required|array|min:1|max:8',
            'docentes.*' => ['required', 'regex:/^a?\d+$/'],
            'cargo' => 'nullable|string|max:40',
        ], [
            'docentes.required' => 'Elija al menos un docente.',
        ]);

        $seguimiento = SeguimientoTitulacion::with(['pti', 'ptis', 'tesis', 'actas', 'resoluciones', 'participantes'])
            ->where('user_id', $egresado->id)->firstOrFail();
        if ($motivo = $this->bloqueo($seguimiento, $data['etapa'])) {
            return $this->volverAlAcordeon($egresado->id, $motivo, 'proceso', 'error');
        }

        $agregados = $this->agregar($seguimiento, $data);

        [$aplicados, $omitidos] = [0, []];
        foreach ($this->companerosDestino($seguimiento, $request) as $otro) {
            if ($motivo = $this->bloqueo($otro, $data['etapa'])) {
                $omitidos[] = $otro->user?->apellidos.' ('.$motivo.')';
                continue;
            }
            $this->agregar($otro, $data);
            $aplicados++;
        }

        $quien = $data['funcion'] === 'asesor' ? 'Asesor(es)' : 'Jurado';
        $mensaje = $agregados ? $quien.' agregado(s)' : 'Ya estaban registrados';

        return $this->volverAlAcordeon($egresado->id, $mensaje.$this->textoGrupo($aplicados, $omitidos), 'proceso', $omitidos ? 'warning' : 'success');
    }

    public function participanteEliminar(Participante $participante)
    {
        $seguimiento = $participante->seguimiento;
        $participante->delete();
        if ($participante->funcion === 'asesor') {
            $this->sincronizarAsesor($seguimiento);
        }

        return $this->volverAlAcordeon($seguimiento->user_id, 'Quitado de la lista.', 'proceso');
    }

    /** Agrega los docentes elegidos que aún no estén (misma etapa y función). Devuelve cuántos. */
    private function agregar(SeguimientoTitulacion $seguimiento, array $data): int
    {
        // En la Tesis, dictaminantes y jurado del acto son listas distintas: un docente puede estar en ambas.
        $dictaminante = \App\Services\Titulacion\PasosProceso::esDictaminante($data['cargo'] ?? null);
        $existentes = $seguimiento->participantes()
            ->where('etapa', $data['etapa'])->where('funcion', $data['funcion'])->get()
            ->filter(fn (Participante $p) => \App\Services\Titulacion\PasosProceso::esDictaminante($p->cargo) === $dictaminante)
            ->map->asesorValor();
        $n = 0;
        foreach (array_unique($data['docentes']) as $valor) {
            if ($existentes->contains($valor)) {
                continue;
            }
            $columnas = Participante::columnasAsesor($valor, 'docente_id');
            $this->verificarAsesorExterno($columnas['asesor_externo_id']);
            $seguimiento->participantes()->create($columnas + [
                'etapa' => $data['etapa'],
                'funcion' => $data['funcion'],
                'cargo' => filled($data['cargo'] ?? null) ? trim($data['cargo']) : null,
            ]);
            $n++;
        }
        if ($n && $data['funcion'] === 'asesor') {
            $this->sincronizarAsesor($seguimiento);
        }

        return $n;
    }

    /**
     * El TI/Tesis guarda un asesor principal (lo usan los listados, la vista del estudiante y las
     * RD): es el primero de la lista de asesores. Si la lista queda vacía, no se borra el que
     * tuviera (puede venir de los datos importados).
     */
    private function sincronizarAsesor(SeguimientoTitulacion $seguimiento): void
    {
        $primero = $seguimiento->participantes()->where('funcion', 'asesor')
            ->orderByRaw("etapa = 'tesis' desc")->orderBy('id')->first();
        if (! $primero) {
            return;
        }
        Tesis::updateOrCreate(['seguimiento_id' => $seguimiento->id], Tesis::columnasAsesor($primero->asesorValor()));
    }

    /** Mensaje si la etapa todavía no está habilitada para esta persona. */
    private function bloqueo(SeguimientoTitulacion $seguimiento, string $etapa): ?string
    {
        $falta = ReglasProceso::falta($seguimiento->avance(), self::REQUISITO_ETAPA[$etapa]);

        return $falta ? 'Todavía no se puede: primero debe estar registrado «'.$falta.'».' : null;
    }
}
