<?php

namespace App\Services\Titulacion;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Resuelve el nombre libre de un asesor/jurado (tal como viene en los
 * archivos de Carlos/Marilyn) a un docente real del sistema.
 *
 * Los archivos traen el mismo nombre de muchas formas: con títulos
 * ("Mag.", "Lic.", "Psicól.", a veces repetidos o pegados: "Mag.Amable"),
 * en orden "Apellidos, Nombres" o "Nombres Apellidos", con o sin segundo
 * nombre, con errores de tipeo ("Savedra", "Zanbrano") y a veces con dos
 * personas ("Ana Corzo / Kiara Eguiluz", "... - co-asesor: ...").
 *
 * Criterio: se toma la primera persona, se comparan palabras (sin tildes
 * ni títulos) tolerando 1 letra de diferencia en palabras de 4+ letras, y
 * se exige al menos 2 palabras en común. Solo se devuelve un docente si
 * es el único con el mejor puntaje — nunca se adivina entre dos.
 */
class ResolverDocente
{
    private const TITULOS = [
        'MG', 'MAG', 'MAGISTER', 'MGTR', 'MTRO', 'MTRA', 'DR', 'DRA', 'DOC', 'LIC', 'LICDA',
        'PROF', 'BACH', 'PSICOL', 'PSIC', 'ING', 'NG', 'SR', 'SRA', 'ASESOR', 'ASESORA', 'CO',
    ];

    private const CONECTORES = ['DE', 'DEL', 'LA', 'LOS', 'LAS', 'Y'];

    /** @var array<int, array{id:int, palabras:array<int,string>}>|null */
    private static ?array $docentes = null;

    public static function resolver(?string $nombre): ?int
    {
        if (blank($nombre)) {
            return null;
        }

        // Solo la primera persona si vienen varias (asesor principal).
        $primero = preg_split('#\s*[/\n]\s*|\s+-\s+|\s+y\s+|\s*co-?asesora?\s*:#iu', trim($nombre))[0] ?? '';
        $buscadas = self::palabras($primero);
        if (count($buscadas) < 2) {
            return null;
        }

        $puntajes = [];
        foreach (self::docentes() as $docente) {
            $comunes = self::palabrasEnComun($buscadas, $docente['palabras']);
            // 2 palabras bastan si cubren todo uno de los dos nombres
            // ("Richard Suarez" vs "Suarez Sanchez Richard"); si no, 3.
            $cubre = $comunes === count($buscadas) || $comunes === count($docente['palabras']);
            if ($comunes >= 3 || ($comunes >= 2 && $cubre)) {
                $puntajes[$docente['id']] = $comunes;
            }
        }

        if (empty($puntajes)) {
            return null;
        }

        $mejor = max($puntajes);
        $ganadores = array_keys($puntajes, $mejor, true);

        return count($ganadores) === 1 ? $ganadores[0] : null;
    }

    /** Solo para pruebas: fuerza recargar la lista de docentes. */
    public static function reiniciar(): void
    {
        self::$docentes = null;
    }

    private static function docentes(): array
    {
        return self::$docentes ??= User::role('docente')->get(['id', 'name', 'apellidos'])
            ->map(fn (User $d) => ['id' => $d->id, 'palabras' => self::palabras($d->apellidos.' '.$d->name)])
            ->all();
    }

    private static function palabras(string $texto): array
    {
        $texto = strtoupper(Str::ascii($texto));
        $texto = preg_replace('/[^A-Z\s]/', ' ', $texto);
        $palabras = explode(' ', preg_replace('/\s+/', ' ', trim($texto)));

        return array_values(array_unique(array_filter(
            $palabras,
            fn ($p) => strlen($p) > 1 && ! in_array($p, self::TITULOS, true) && ! in_array($p, self::CONECTORES, true)
        )));
    }

    private static function palabrasEnComun(array $buscadas, array $delDocente): int
    {
        $comunes = 0;
        $disponibles = $delDocente;
        foreach ($buscadas as $palabra) {
            foreach ($disponibles as $i => $candidata) {
                $igual = $palabra === $candidata
                    || (min(strlen($palabra), strlen($candidata)) >= 4 && levenshtein($palabra, $candidata) <= 1);
                if ($igual) {
                    $comunes++;
                    unset($disponibles[$i]);
                    break;
                }
            }
        }

        return $comunes;
    }
}
