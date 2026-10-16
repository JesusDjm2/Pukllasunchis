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

    public static function ruta(string $nombre): ?string
    {
        if (! preg_match('/^[0-9a-f\-]{36}\.pdf$/i', $nombre) || ! Storage::disk('local')->exists(self::CARPETA.'/'.$nombre)) {
            return null;
        }

        return Storage::disk('local')->path(self::CARPETA.'/'.$nombre);
    }
}
