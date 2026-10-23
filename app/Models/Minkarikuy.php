<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Minkarikuy extends Model
{
    // `imagen` queda solo por los registros antiguos (QR subido a mano): ahora el QR se
    // genera del enlace (`url`) con simple-qrcode.
    protected $fillable = ['nombre', 'fecha', 'hora', 'imagen', 'url', 'activo'];

    protected $casts = [
        'fecha'  => 'date',
        'activo' => 'boolean',
    ];

    public static function activo(): ?self
    {
        return self::where('activo', true)->latest()->first();
    }

    /** Hora en formato 24 h ("19:00"), venga de la BD como H:i:s o H:i. */
    public function getHoraTextoAttribute(): string
    {
        if (! $this->hora) {
            return '';
        }

        return Carbon::parse($this->hora)->format('H:i');
    }

    /** "Martes 21 de abril de 2026 · 19:00" */
    public function getCuandoAttribute(): string
    {
        $fecha = $this->fecha?->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');

        return trim(ucfirst((string) $fecha).($this->hora ? ' · '.$this->hora_texto : ''));
    }

    /** SVG del QR que lleva al enlace (null si no hay enlace). */
    public function qrSvg(int $tamano = 220, array $color = [35, 38, 43]): ?string
    {
        if (! $this->url) {
            return null;
        }

        return (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::size($tamano)
            ->margin(1)
            ->color(...$color)
            ->generate($this->url);
    }
}
