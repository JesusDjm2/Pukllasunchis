<?php

namespace App\Services\Titulacion;

use App\Models\Proyecto;
use App\Models\Titulacion\Resolucion;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Correo de Secretaría con una RD: a quiénes va (cada integrante tiene su propia fila con el mismo
 * tipo y número) y el asunto y mensaje sugeridos según el tipo. El mensaje es HTML (el gestor lo
 * edita con formato en CKEditor) y se limpia al enviar.
 */
class MensajesRd
{
    /** @return Collection<int, Resolucion> la RD de cada persona, con su seguimiento y usuario; la indicada primero */
    public static function destinatarios(Resolucion $res): Collection
    {
        $clave = GrupoTitulacion::claveRd($res->numero_rd);

        return Resolucion::with('seguimiento.user')->where('tipo', $res->tipo)->whereNotNull('numero_rd')->get()
            ->filter(fn ($r) => $r->id === $res->id || ($clave !== '' && GrupoTitulacion::claveRd($r->numero_rd) === $clave))
            ->filter(fn ($r) => $r->seguimiento?->user)
            ->sortBy(fn ($r) => [$r->id === $res->id ? 0 : 1, (string) $r->seguimiento->user->apellidos])
            ->values();
    }

    /** Documento de la RD de esta persona; si no lo tiene, el de la RD desde la que se envía (es el mismo documento). */
    public static function documento(Resolucion $deLaPersona, Resolucion $original): ?string
    {
        return $deLaPersona->archivo ?: $original->archivo;
    }

    /** ¿La RD tiene un documento que se pueda enviar? Un PDF del sistema debe seguir existiendo en el disco. */
    public static function documentoDisponible(Resolucion $res): bool
    {
        if (blank($res->archivo)) {
            return false;
        }
        $archivo = ArchivosRd::nombreDesdeUrl($res->archivo);

        return ! $archivo || (bool) ArchivosRd::ruta($archivo);
    }

    /** Número para leer ("RD 24-2021 PTI Ana.pdf", importado de una planilla → "24-2021 PTI Ana"). */
    public static function numero(?string $numero): string
    {
        return trim(preg_replace(['/\.pdf\s*$/i', '/^\s*R\.?\s?D\b[\s.\-:]*/iu'], '', (string) $numero));
    }

    public static function nombreArchivo(Resolucion $res): string
    {
        $nombre = trim(preg_replace('/[\\\\\/:*?"<>|\s]+/u', ' ', self::numero($res->numero_rd)));

        return 'RD '.Str::limit($nombre, 80, '').' - '.Resolucion::nombreTipo($res->tipo).'.pdf';
    }

    /**
     * HTML del editor listo para el correo: solo formato básico, sin scripts ni estilos. En una sola
     * línea porque el correo pasa por Markdown: una línea en blanco o una sangría lo romperían.
     */
    public static function limpiar(?string $html): string
    {
        return trim(preg_replace('/>\s+</u', '><', Proyecto::limpiarHtml($html)));
    }

    /** Versión de texto plano del mensaje, para los programas de correo que no muestran HTML. */
    public static function aTexto(string $html): string
    {
        $texto = preg_replace(['/<li[^>]*>/i', '/<\/(p|ul|ol|h3|h4|blockquote)>/i', '/<\/li>|<br\s*\/?>/i'], ['• ', "\n\n", "\n"], $html);
        $texto = preg_replace('/\n{3,}/', "\n\n", html_entity_decode(strip_tags($texto), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return trim(str_replace("\u{00A0}", ' ', $texto));
    }

    /** @return array{0: string, 1: string} [asunto, mensaje en HTML] */
    public static function sugerido(Resolucion $res): array
    {
        $seg = $res->seguimiento;
        $numero = self::numero($res->numero_rd);
        $fechaLarga = fn ($f) => $f?->locale('es')->translatedFormat('j \d\e F \d\e Y');
        $rd = '<strong>Resolución Directoral N° '.e($numero).'</strong>'.($res->fecha ? ', de fecha '.$fechaLarga($res->fecha) : '');
        $titulo = $seg?->pti?->titulo;
        $tesis = $seg?->tesis?->titulo ?: $titulo;
        $detalle = $res->detalle;

        [$asunto, $apertura, $datos, $cierre] = match ($res->tipo) {
            'PTI' => [
                'Aprobación de su Plan de Trabajo de Investigación',
                "Nos es grato comunicarle que, mediante la {$rd}, se aprobó su <strong>Plan de Trabajo de Investigación (PTI)</strong>.",
                ['Título' => $titulo, 'Vigente hasta' => $fechaLarga($seg?->pti?->fecha_vencimiento)],
                $seg?->pti?->fecha_vencimiento ? 'Le recomendamos tener presente la fecha de vigencia para planificar el desarrollo de su Trabajo de Investigación.' : null,
            ],
            'AmpliacionPti' => [
                'Ampliación de la vigencia de su Plan de Trabajo de Investigación',
                "Le comunicamos que, mediante la {$rd}, se amplió la <strong>vigencia de su Plan de Trabajo de Investigación (PTI)</strong>.",
                ['Título' => $titulo, 'Nueva vigencia hasta' => $fechaLarga($seg?->pti?->vencimiento_ampliado)],
                'Le recomendamos culminar su Trabajo de Investigación dentro de este nuevo plazo.',
            ],
            'AnulacionPti' => [
                'Anulación de su Plan de Trabajo de Investigación',
                "Le comunicamos que, mediante la {$rd}, se dispuso la <strong>anulación de su Plan de Trabajo de Investigación (PTI)</strong>.",
                ['Título' => $titulo],
                'Si requiere orientación sobre los pasos a seguir, no dude en comunicarse con nosotros.',
            ],
            'Exposicion' => [
                'Aprobación de la exposición de su Trabajo de Investigación',
                "Nos complace comunicarle que, mediante la {$rd}, se aprobó la <strong>exposición de su Trabajo de Investigación</strong>.",
                ['Título' => $titulo],
                'El siguiente paso es la publicación del trabajo en el Repositorio Institucional, requisito previo a la inscripción del grado de Bachiller.',
            ],
            'FechaSustentacion' => [
                'Programación de la sustentación de su tesis',
                "Le comunicamos que, mediante la {$rd}, se programó la <strong>sustentación de su tesis</strong>.",
                ['Tesis' => $tesis, 'Fecha de sustentación' => $fechaLarga($seg?->tesis?->fecha_sustentacion)],
                'Le solicitamos revisar con atención la resolución y prepararse con la debida anticipación.',
            ],
            'Tesis' => [
                'Aprobación de la sustentación de su tesis',
                "Nos complace comunicarle que, mediante la {$rd}, se <strong>aprobó la sustentación de su tesis</strong>. Reciba nuestras felicitaciones por este importante logro académico.",
                ['Tesis' => $tesis],
                'Los siguientes pasos son la publicación de la tesis en el Repositorio Institucional y la inscripción del grado de Licenciatura en SUNEDU.',
            ],
            'Extension' => [
                'Ampliación del plazo para la sustentación de su tesis',
                "Le comunicamos que, mediante la {$rd}, se amplió el <strong>plazo para la sustentación de su tesis</strong>.",
                ['Tesis' => $tesis],
                'Le recomendamos revisar con atención la resolución para conocer el nuevo plazo.',
            ],
            'CambioAsesor' => [
                'Cambio de asesor de su trabajo de investigación',
                "Le comunicamos que, mediante la {$rd}, se aprobó el <strong>cambio de asesor</strong> de su trabajo de investigación.",
                ['Título' => $titulo, 'Detalle' => $detalle],
                null,
            ],
            'CambioGrupo' => [
                'Cambio en su grupo de investigación',
                "Le comunicamos que, mediante la {$rd}, se aprobó el <strong>cambio en su grupo de investigación</strong>.",
                ['Título' => $titulo, 'Detalle' => $detalle],
                null,
            ],
            'RetiroGrupo' => [
                'Retiro de su grupo de investigación',
                "Le comunicamos que, mediante la {$rd}, se formalizó su <strong>retiro del grupo de investigación</strong>.",
                ['Título' => $titulo, 'Detalle' => $detalle],
                'Si requiere orientación sobre los pasos a seguir, no dude en comunicarse con nosotros.',
            ],
            'IncorporacionGrupo' => [
                'Incorporación de un integrante a su grupo de investigación',
                "Le comunicamos que, mediante la {$rd}, se aprobó la <strong>incorporación de un integrante al grupo de investigación</strong>.",
                ['Título' => $titulo, 'Detalle' => $detalle],
                null,
            ],
            'CambioTema' => [
                'Cambio de tema de su investigación',
                "Le comunicamos que, mediante la {$rd}, se aprobó el <strong>cambio de tema de su investigación</strong>.",
                ['Título' => $titulo, 'Detalle' => $detalle],
                null,
            ],
            default => [
                'Resolución Directoral emitida',
                "Le comunicamos que se emitió la {$rd}, relacionada con su proceso de titulación.",
                ['Detalle' => $detalle],
                null,
            ],
        };

        $lista = collect($datos)->filter(fn ($v) => filled($v))->map(fn ($v, $k) => '<li><strong>'.e($k).':</strong> '.e($v).'</li>')->implode('');
        $mensaje = '<p>Reciba un cordial saludo.</p><p>'.$apertura.'</p>'
            .($lista ? '<ul>'.$lista.'</ul>' : '')
            .($cierre ? '<p>'.$cierre.'</p>' : '')
            .'<p>Ante cualquier consulta, puede responder a este correo; con gusto le atenderemos.</p>';

        return [$asunto." — RD N° {$numero}", $mensaje];
    }
}
