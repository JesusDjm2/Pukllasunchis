<?php

namespace App\Services\Titulacion;

/**
 * Reglas para interpretar la celda de nombre de los archivos de titulación.
 */
class NombresImportados
{
    /**
     * La hoja de secretaría pone a todos los integrantes de una exposición
     * grupal en UNA celda, uno por línea ("Lloclla Quehuarucho, María
     * Virginia\nSamata Cruz, Nilda"). Devuelve un nombre por integrante;
     * si la celda es de una sola persona, devuelve [ella].
     *
     * @return string[]
     */
    public static function integrantes(?string $celda): array
    {
        $lineas = array_values(array_filter(
            array_map(fn ($l) => trim(preg_replace('/\s+/u', ' ', $l)), preg_split('/\R+/u', (string) $celda)),
            fn ($l) => $l !== ''
        ));

        return $lineas ?: [];
    }

    /**
     * El archivo general a veces pone al grupo en una sola línea ("Ana Ccolque Cusi, Kely Huanca
     * Mamani y Luz Puma"). Solo se separa si hay un « y »: una coma sola puede ser
     * "Apellidos, Nombres" o una coma colgada al final.
     *
     * @return string[]
     */
    public static function integrantesEnLinea(?string $celda): array
    {
        $personas = [];
        foreach (self::integrantes($celda) as $linea) {
            $partes = preg_match('/\s+y\s+/iu', $linea) ? preg_split('/\s+y\s+|,/iu', $linea) : [$linea];
            foreach ($partes as $parte) {
                if (($parte = trim($parte, " \t,")) !== '') {
                    $personas[] = $parte;
                }
            }
        }

        return $personas;
    }

    /**
     * Como mismaPersona(), tolerando una letra de diferencia en palabras de 5+ letras
     * ("Carillo" / "Carrillo"). Quien lo use debe exigir además que el candidato sea único.
     */
    public static function mismaPersonaConErratas(string $a, string $b): bool
    {
        $pa = self::palabras($a);
        $pb = self::palabras($b);
        [$corto, $largo] = count($pa) <= count($pb) ? [$pa, $pb] : [$pb, $pa];
        $parecida = fn ($x, $y) => $x === $y || (strlen($x) >= 5 && strlen($y) >= 5 && levenshtein($x, $y) <= 1);
        $iguales = count(array_filter($corto, fn ($x) => array_filter($largo, fn ($y) => $parecida($x, $y)) !== []));

        return $iguales >= 2 && $iguales === count($corto);
    }

    /**
     * Filas que no son personas: los expedientes separan cada promoción con
     * una fila cuyo "nombre" es el periodo ("2016-I al 2021-II") y a veces
     * repiten la fila de títulos ("APELLIDOS Y NOMBRES" / "DNI"). Nunca
     * aplica si la fila trae un DNI real.
     */
    public static function esFilaDeEncabezado(?string $nombre, ?string $dni = null): bool
    {
        if (preg_match('/\d{8}/', (string) $dni)) {
            return false;
        }
        $nombre = trim((string) $nombre);

        return preg_match('/\b(19|20)\d{2}\b/', $nombre)
            || strcasecmp(\Illuminate\Support\Str::ascii($nombre), 'APELLIDOS Y NOMBRES') === 0;
    }

    /**
     * ¿Dos nombres de archivo distintos son la misma persona? Compara
     * palabras (sin tildes, 3+ letras): al menos 2, y que cubran todo uno
     * de los dos nombres ("Avella, Natasha Luisa" vs "Avella Bedford,
     * Natasha Luisa"). Quien lo use debe exigir además que el candidato
     * sea único.
     */
    public static function mismaPersona(string $a, string $b): bool
    {
        $pa = self::palabras($a);
        $pb = self::palabras($b);
        $comunes = count(array_intersect($pa, $pb));

        // Todas las palabras del nombre más corto deben estar en el otro: tolera
        // que falte un apellido o nombre, pero no que haya uno distinto
        // ("Ore Gamarra, María Guadalupe" ≠ "Ore Gamarra, Ángela María").
        return $comunes >= 2 && $comunes === min(count($pa), count($pb));
    }

    private static function palabras(string $texto): array
    {
        $texto = strtoupper(\Illuminate\Support\Str::ascii($texto));
        $texto = preg_replace('/[^A-Z\s]/', ' ', $texto);

        return array_values(array_unique(array_filter(
            explode(' ', $texto),
            fn ($p) => strlen($p) > 2 && ! in_array($p, ['DEL', 'LOS', 'LAS'], true)
        )));
    }

    /**
     * "Apellidos, Nombres" con exactamente una coma es un formato sin
     * ambigüedad. Sin coma ("Ángela María Ore Gamarra") no se sabe dónde
     * termina el nombre y empieza el apellido: null, se confirma a mano.
     *
     * @return array{apellidos:string, nombres:string}|null
     */
    public static function separarApellidosNombres(?string $nombre): ?array
    {
        $nombre = trim((string) $nombre, " \t\n\r\0\x0B,");
        if (substr_count($nombre, ',') !== 1) {
            return null;
        }

        [$apellidos, $nombres] = array_map('trim', explode(',', $nombre));
        if ($apellidos === '' || $nombres === '') {
            return null;
        }

        return ['apellidos' => $apellidos, 'nombres' => $nombres];
    }
}
