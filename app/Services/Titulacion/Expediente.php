<?php

namespace App\Services\Titulacion;

/**
 * Número de expediente de titulación. Los archivos lo escriben de muchas
 * formas ("Nº 0001-T/PUK", "N°0029-T/PUK", "Nª 145-T/PUK", "Nº 256T/PUK",
 * "Nº 0013-T/PUK-PPD"); se guarda en un solo formato para poder buscarlo
 * y detectar repetidos: "0001-T/PUK" o "0013-T/PUK-PPD".
 */
class Expediente
{
    public static function normalizar(?string $texto): ?string
    {
        $t = strtoupper(trim((string) $texto));
        if (! preg_match('#(\d+)\s*-?\s*T\s*/\s*PUK(\s*-\s*PPD)?#', $t, $m)) {
            return null;
        }

        $numero = (int) $m[1];
        if ($numero === 0) {
            return null; // "N°00-T/PUK": aún sin número asignado
        }

        return sprintf('%04d-T/PUK', $numero).(! empty($m[2]) ? '-PPD' : '');
    }

    /**
     * Expediente de una persona a partir de los datos de una fila importada.
     * En las exposiciones grupales la celda trae los expedientes de todos,
     * en el mismo orden que los integrantes: uno por línea ("Nº 0001-T/PUK-PPD
     * \n Nº 0002-...") o números juntos ("Nº 0001,0003,0002-T/PUK-PPD"). Se
     * toma el de la posición de la persona; si no cuadran las cantidades, null.
     */
    public static function deFila(array $datos, ?string $nombrePersona): ?string
    {
        $celda = null;
        foreach (['expediente', 'num_expediente', 'numero_expediente'] as $clave) {
            if (filled($datos[$clave] ?? null)) {
                $celda = (string) $datos[$clave];
                break;
            }
        }
        if ($celda === null) {
            return null;
        }

        $valores = self::separar($celda);
        if (count($valores) <= 1) {
            return $valores[0] ?? null;
        }

        $integrantes = array_map('trim', explode(';', (string) ($datos['integrantes_grupo'] ?? '')));
        if (count($integrantes) !== count($valores) || ! $nombrePersona) {
            return null;
        }
        foreach ($integrantes as $i => $integrante) {
            if (NombresImportados::mismaPersona($integrante, $nombrePersona)) {
                return $valores[$i];
            }
        }

        return null;
    }

    /** @return string[] expedientes normalizados que contiene una celda, en orden */
    private static function separar(string $celda): array
    {
        $lineas = array_filter(array_map('trim', preg_split('/\R+/u', $celda)));
        if (count($lineas) > 1) {
            return array_values(array_filter(array_map([self::class, 'normalizar'], $lineas)));
        }

        // "Nº 0001,0003,0002-T/PUK-PPD": varios números con un solo sufijo.
        $t = strtoupper($celda);
        if (preg_match('#^[^\d]*([\d\s,]+?)\s*-?\s*(T\s*/\s*PUK(?:\s*-\s*PPD)?)#', $t, $m) && str_contains($m[1], ',')) {
            return array_values(array_filter(array_map(
                fn ($n) => self::normalizar(trim($n).'-'.$m[2]),
                explode(',', $m[1])
            )));
        }

        $uno = self::normalizar($celda);

        return $uno ? [$uno] : [];
    }

    public static function mostrar(?string $normalizado): string
    {
        return $normalizado ? 'N° '.$normalizado : '—';
    }
}
