<?php

namespace App\Services\RicardoPalma;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Borrador de un acta escaneada mientras se corrige: el PDF y los datos leídos/corregidos se
 * guardan en storage/app/rp-lecturas/{id}/ (sin tabla nueva). Al cargar el acta, el PDF pasa a
 * storage/app/rp-escaneos/acta-{id}.pdf para consultarlo desde el acta.
 */
class BorradorLecturaRp
{
    private const DIR = 'rp-lecturas';
    private const DIR_ESCANEOS = 'rp-escaneos';

    public function __construct(public readonly string $id)
    {
    }

    public static function crear(string $rutaPdfSubido, string $archivo, ?int $userId, ?string $usuario): self
    {
        $b = new self(strtolower((string) Str::ulid()));
        Storage::disk('local')->makeDirectory($b->dir());
        copy($rutaPdfSubido, $b->rutaPdf());
        $b->guardarMeta([
            'archivo' => $archivo,
            'user_id' => $userId,
            'usuario' => $usuario,
            'creado' => now()->toIso8601String(),
            'actualizado' => now()->toIso8601String(),
            'leido_con' => null,
            'error_lectura' => null,
        ]);
        $b->guardarDatos(self::vacio());

        return $b;
    }

    public static function buscar(string $id): ?self
    {
        if (! preg_match('/^[0-9a-z]{26}$/', $id)) {
            return null;
        }
        $b = new self($id);

        return Storage::disk('local')->exists($b->dir().'/meta.json') ? $b : null;
    }

    /** Borradores pendientes, del más reciente al más antiguo. */
    public static function todos(): array
    {
        $lista = [];
        foreach (Storage::disk('local')->directories(self::DIR) as $dir) {
            $b = self::buscar(basename($dir));
            if ($b) {
                $meta = $b->meta();
                $datos = $b->datos();
                $lista[] = $meta + [
                    'id' => $b->id,
                    'alumnos' => count($datos['alumnos'] ?? []),
                    'titulo' => trim(($datos['encabezado']['periodo'] ?? '').' · '.($datos['encabezado']['programa'] ?? '').' · '.($datos['encabezado']['semestre'] ?? ''), ' ·'),
                ];
            }
        }
        usort($lista, fn ($a, $b) => strcmp($b['actualizado'] ?? '', $a['actualizado'] ?? ''));

        return $lista;
    }

    public static function vacio(): array
    {
        return [
            'encabezado' => ['periodo' => '', 'semestre' => '', 'fecha' => '', 'programa' => '', 'turno' => '', 'seccion' => '', 'nro_acta' => '', 'texto_especialidad' => ''],
            'en_puntaje' => true,
            'asignaturas' => [],
            'alumnos' => [],
        ];
    }

    public function dir(): string
    {
        return self::DIR.'/'.$this->id;
    }

    public function rutaPdf(): string
    {
        return Storage::disk('local')->path($this->dir().'/escaneo.pdf');
    }

    public function meta(): array
    {
        return json_decode((string) Storage::disk('local')->get($this->dir().'/meta.json'), true) ?: [];
    }

    public function guardarMeta(array $meta): void
    {
        Storage::disk('local')->put($this->dir().'/meta.json', json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function datos(): array
    {
        $datos = json_decode((string) Storage::disk('local')->get($this->dir().'/datos.json'), true);

        return is_array($datos) ? $datos : self::vacio();
    }

    public function guardarDatos(array $datos): void
    {
        Storage::disk('local')->put($this->dir().'/datos.json', json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        if (Storage::disk('local')->exists($this->dir().'/meta.json')) {
            $this->guardarMeta(['actualizado' => now()->toIso8601String()] + $this->meta());
        }
    }

    public function tieneDatos(): bool
    {
        $d = $this->datos();

        return ! empty($d['alumnos']) || ! empty($d['asignaturas']);
    }

    public function necesitaLectura(): bool
    {
        $meta = $this->meta();

        return empty($meta['leido_con']) && empty($meta['error_lectura']) && ! $this->tieneDatos();
    }

    public function reiniciarLectura(): void
    {
        $meta = $this->meta();
        unset($meta['leido_con'], $meta['error_lectura']);
        $meta['actualizado'] = now()->toIso8601String();
        $this->guardarMeta($meta);
        $this->guardarDatos(self::vacio());
    }

    public function eliminar(): void
    {
        Storage::disk('local')->deleteDirectory($this->dir());
    }

    /** Guarda el PDF junto a las actas cargadas y borra el borrador. */
    public function archivarEn(array $actaIds): void
    {
        foreach ($actaIds as $actaId) {
            Storage::disk('local')->put(self::rutaEscaneoActa((int) $actaId), file_get_contents($this->rutaPdf()));
        }
        $this->eliminar();
    }

    public static function rutaEscaneoActa(int $actaId): string
    {
        return self::DIR_ESCANEOS."/acta-{$actaId}.pdf";
    }

    public static function actaTieneEscaneo(int $actaId): bool
    {
        return Storage::disk('local')->exists(self::rutaEscaneoActa($actaId));
    }
}
