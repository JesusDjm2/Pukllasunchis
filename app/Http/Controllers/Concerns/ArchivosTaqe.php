<?php

namespace App\Http\Controllers\Concerns;

use App\Models\TaqeGrupo;
use App\Models\TaqeTrabajo;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

/**
 * Archivos de Investigaciones guardados en Drive: ver, descargar (uno o en ZIP por
 * grupo) y borrar. Compartido por el alumno (TaqeController), el
 * Investigador y el Super Admin — cada controlador hace su propio chequeo de
 * permisos ANTES de llamar a estos métodos.
 */
trait ArchivosTaqe
{
    private function drive(): GoogleDriveService
    {
        return app(GoogleDriveService::class);
    }

    /**
     * Streaming del archivo desde Drive; con $descargar = true sale como adjunto con su nombre de Drive.
     * Un enlace no tiene archivo: se redirige a su URL.
     */
    private function servirArchivo(TaqeTrabajo $trabajo, bool $descargar = false): Response
    {
        if ($trabajo->esEnlace()) {
            return redirect()->away($trabajo->enlace);
        }

        $drive = $this->drive();

        // Range: el reproductor de video/audio pide el archivo por partes para poder adelantar
        // (Safari, en iPhone y Mac, ni siquiera reproduce sin respuestas 206). Mismo esquema que
        // CeAudioStreamController. En descargas no hace falta.
        $range = $descargar ? null : request()->header('Range');

        try {
            $meta = $drive->getMeta($trabajo->archivo);
            $resultado = $drive->streamFile($trabajo->archivo, $range);
            $body = $resultado['body'];
        } catch (\Exception $e) {
            abort(404);
        }

        if ($resultado['status'] === 416) {
            return response()->stream(fn () => null, 416, ['Content-Range' => 'bytes */'.$meta['size']]);
        }
        $parcial = $range && $resultado['status'] === 206 && $resultado['contentRange'];

        $headers = [
            'Content-Type' => $meta['mimeType'],
            'Content-Length' => $parcial ? ($resultado['contentLen'] ?? $meta['size']) : $meta['size'],
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=3600',
        ];
        if ($parcial) {
            $headers['Content-Range'] = $resultado['contentRange'];
        }
        if ($descargar) {
            $headers['Content-Disposition'] = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT, $meta['name'], Str::ascii($meta['name'])
            );
        }

        return response()->stream(function () use ($body) {
            while (! $body->eof()) {
                echo $body->read(8192);
                flush();
            }
        }, $parcial ? 206 : 200, $headers);
    }

    /**
     * ZIP con los archivos del grupo. $que: evidencias | final | todo — con
     * "todo" las evidencias van en su propia carpeta y el trabajo final en la raíz.
     */
    private function descargarZipGrupo(TaqeGrupo $grupo, string $que): BinaryFileResponse
    {
        $trabajos = $grupo->trabajos()->orderBy('created_at')->get()
            ->filter(fn (TaqeTrabajo $t) => $que === 'todo'
                || ($que === 'final' && $t->esFinal())
                || ($que === 'evidencias' && ! $t->esFinal()));

        if ($trabajos->isEmpty()) {
            abort(404, 'Este grupo no tiene archivos para descargar.');
        }

        @set_time_limit(300);

        $tmp = tempnam(sys_get_temp_dir(), 'taqe');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $drive = $this->drive();
        $usados = [];
        $fallidos = [];

        // Los enlaces no son archivos: van todos juntos en "Enlaces.txt".
        $enlaces = $trabajos->filter(fn (TaqeTrabajo $t) => $t->esEnlace());
        $trabajos = $trabajos->reject(fn (TaqeTrabajo $t) => $t->esEnlace());
        if ($enlaces->isNotEmpty()) {
            $lineas = $enlaces->map(fn (TaqeTrabajo $t) => ($t->esFinal() ? '[Trabajo final] ' : '[Evidencia] ').$t->nombre.': '.$t->enlace);
            $zip->addFromString('Enlaces.txt', $lineas->implode("\r\n")."\r\n");
        }

        foreach ($trabajos as $trabajo) {
            try {
                $nombre = $drive->getMeta($trabajo->archivo)['name'];
                $resultado = $drive->streamFile($trabajo->archivo);
                if ($resultado['status'] !== 200) {
                    throw new \RuntimeException('HTTP '.$resultado['status']);
                }
                $contenido = $resultado['body']->getContents();
            } catch (\Throwable $e) {
                Log::warning("Investigaciones ZIP grupo {$grupo->id}: no se pudo leer el trabajo {$trabajo->id}: ".$e->getMessage());
                $fallidos[] = $trabajo->nombre;

                continue;
            }

            $carpeta = ($que === 'todo' && ! $trabajo->esFinal()) ? 'Evidencias/' : '';
            $ruta = $carpeta.$nombre;
            // Dos archivos con el mismo nombre en Drive (mismo alumno, mismo
            // nombre de trabajo) se pisarían dentro del ZIP.
            for ($n = 2; isset($usados[$ruta]); $n++) {
                $ruta = $carpeta.pathinfo($nombre, PATHINFO_FILENAME)." ({$n}).".pathinfo($nombre, PATHINFO_EXTENSION);
            }
            $usados[$ruta] = true;
            $zip->addFromString($ruta, $contenido);
        }

        if ($fallidos) {
            $zip->addFromString('LEEME - archivos no incluidos.txt',
                "No se pudieron leer desde Drive estos archivos:\r\n- ".implode("\r\n- ", $fallidos)."\r\n");
        }
        $zip->close();

        $etiqueta = ['evidencias' => 'Evidencias', 'final' => 'Trabajo final', 'todo' => 'Completo'][$que];
        $nombreZip = trim(preg_replace('/[\\\\\/:*?"<>|]+/', '-', "Taqe - {$grupo->nombre} - {$etiqueta}")).'.zip';

        return response()->download($tmp, $nombreZip, ['Content-Type' => 'application/zip'])->deleteFileAfterSend();
    }

    /** Borra el archivo en Drive y el registro. Si Drive falla, el registro se borra igual (queda en el log). */
    private function eliminarTrabajo(TaqeTrabajo $trabajo): void
    {
        // Un enlace no tiene archivo en Drive que borrar.
        if ($trabajo->archivo && ! $this->drive()->delete($trabajo->archivo)) {
            Log::warning("Investigaciones: el trabajo {$trabajo->id} se eliminó pero su archivo {$trabajo->archivo} quedó en Drive.");
        }
        $trabajo->delete();
    }

    /** Antes de borrar un grupo: limpia en Drive todos sus archivos (la cascada de BD solo borra los registros). */
    private function eliminarArchivosDelGrupo(TaqeGrupo $grupo): void
    {
        $grupo->trabajos()->get()->each(fn (TaqeTrabajo $t) => $this->eliminarTrabajo($t));
    }
}
