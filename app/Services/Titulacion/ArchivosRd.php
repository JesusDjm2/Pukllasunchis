<?php

namespace App\Services\Titulacion;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * PDF de las RD (generadas por el sistema o subidas firmadas). Se guardan en
 * storage/app/titulacion/rd (no públicos) y titulacion_resoluciones.archivo guarda la URL
 * de la ruta titulacion.rd.archivo, que verifica quién puede abrirlo. Así las RD con enlace
 * de Drive y las del sistema se muestran igual en todas las vistas.
 */
class ArchivosRd
{
    private const CARPETA = 'titulacion/rd';

    public static function guardarContenido(string $pdf): string
    {
        $nombre = Str::uuid().'.pdf';
        Storage::disk('local')->put(self::CARPETA.'/'.$nombre, $pdf);

        return self::url($nombre);
    }

    public static function guardarSubido(UploadedFile $archivo): string
    {
        $nombre = Str::uuid().'.pdf';
        $archivo->storeAs(self::CARPETA, $nombre, 'local');

        return self::url($nombre);
    }

    public static function url(string $nombre): string
    {
        return route('titulacion.rd.archivo', $nombre);
    }

    /** Nombre del archivo si la URL es de un PDF guardado por el sistema; null si es un enlace externo. */
    public static function nombreDesdeUrl(?string $url): ?string
    {
        return $url && preg_match('~/titulacion/rd/archivo/([0-9a-f\-]{36}\.pdf)$~i', $url, $m) ? $m[1] : null;
    }

    /** Texto del botón que abre el documento de una RD: «Ver PDF» (del sistema), «Ver en Drive» u «Ver enlace». */
    public static function etiqueta(?string $url): string
    {
        return match (true) {
            (bool) self::nombreDesdeUrl($url) => 'Ver PDF',
            str_contains((string) parse_url((string) $url, PHP_URL_HOST), 'google.com') => 'Ver en Drive',
            default => 'Ver enlace',
        };
    }

    /**
     * ¿El PDF lo generó el sistema (y no se subió firmado/escaneado)? No hay columna que lo diga:
     * los PDF de dompdf llevan «Producer (dompdf … + CPDF)» en sus primeros bytes (en UTF-16).
     */
    public static function generadoPorSistema(?string $url): bool
    {
        $nombre = self::nombreDesdeUrl($url);
        $ruta = $nombre ? self::ruta($nombre) : null;
        if (! $ruta || ! ($f = @fopen($ruta, 'rb'))) {
            return false;
        }
        $inicio = (string) fread($f, 4096);
        fclose($f);

        return stripos(str_replace("\0", '', $inicio), 'dompdf') !== false;
    }

    public static function ruta(string $nombre): ?string
    {
        if (! preg_match('/^[0-9a-f\-]{36}\.pdf$/i', $nombre) || ! Storage::disk('local')->exists(self::CARPETA.'/'.$nombre)) {
            return null;
        }

        return Storage::disk('local')->path(self::CARPETA.'/'.$nombre);
    }
}
