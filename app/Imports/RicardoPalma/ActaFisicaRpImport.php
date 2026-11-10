<?php

namespace App\Imports\RicardoPalma;

use App\Exports\RicardoPalma\PlantillaActaFisicaRp;

/**
 * Lee hojas con la forma del acta física (PlantillaActaFisicaRp): encabezado con rótulos,
 * asignaturas en columnas y un alumno por fila. Cada hoja se convierte en las filas «una por
 * alumno y curso» que ya procesa ActaConsolidadaRpImport, que valida y guarda todo.
 */
class ActaFisicaRpImport
{
    private const SEMESTRES = [
        'PRIMERO' => 1, 'PRIMER' => 1, 'SEGUNDO' => 2, 'TERCERO' => 3, 'TERCER' => 3, 'CUARTO' => 4,
        'QUINTO' => 5, 'SEXTO' => 6, 'SEPTIMO' => 7, 'SETIMO' => 7, 'OCTAVO' => 8, 'NOVENO' => 9, 'DECIMO' => 10,
        'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10,
    ];

    /** ¿La hoja (filas crudas, sin encabezado) tiene la forma del acta física? */
    public static function esFormatoActa(array $filas): bool
    {
        return self::ubicar($filas, 'AÑO ACADEMICO', 12) !== null && self::ubicar($filas, 'ASIGNATURAS', 15, true) !== null;
    }

    /**
     * Convierte las hojas con forma de acta en filas para ActaConsolidadaRpImport.
     * Los errores de lectura (puntaje que no da nota entera, nombre sin coma…) se anotan en $errores.
     *
     * @param  array<string, array>  $hojas  nombre de hoja => filas crudas
     */
    public static function aFilas(array $hojas, array &$errores): array
    {
        $resultado = [];
        foreach ($hojas as $nombreHoja => $filas) {
            if (! self::esFormatoActa($filas)) {
                continue;
            }
            $resultado = array_merge($resultado, self::leerHoja((string) $nombreHoja, $filas, $errores));
        }

        return $resultado;
    }

    /** Lee la hoja y la pasa a la estructura común de desdeActa(). */
    private static function leerHoja(string $hoja, array $filas, array &$errores): array
    {
        // ── Tabla: «ASIGNATURAS» marca la primera columna de asignaturas; debajo van nombres, créditos y números ──
        [$fCab, $cIni] = self::ubicar($filas, 'ASIGNATURAS', 15, true);
        $fCursos = $fCab + 1;
        $fCreditos = $fCab + 2;
        $fDatos = $fCab + 4;
        $cMatricula = $cIni - 2;
        $cNombre = $cIni - 1;
        $cObs = self::columnaRotulo($filas, $fCab, 'OBSERVACIONES');
        $cSexo = self::columnaRotulo($filas, $fCab, 'SEXO');
        $cDni = self::columnaRotulo($filas, $fCab, 'DNI');

        $asignaturas = []; // n.º => [nombre, créditos tal como están]
        for ($n = 1; $n <= PlantillaActaFisicaRp::ASIGNATURAS; $n++) {
            $c = $cIni + $n - 1;
            $asignaturas[$n] = [
                'nombre' => (string) ($filas[$fCursos][$c] ?? ''),
                'creditos' => (string) ($filas[$fCreditos][$c] ?? ''),
            ];
        }

        $alumnos = [];
        for ($f = $fDatos; $f < count($filas); $f++) {
            $fila = $filas[$f];
            $valores = [];
            for ($n = 1; $n <= PlantillaActaFisicaRp::ASIGNATURAS; $n++) {
                $valores[$n] = (string) ($fila[$cIni + $n - 1] ?? '');
            }
            $alumnos[] = [
                'ref' => 'fila '.($f + 1),
                'matricula' => (string) ($fila[$cMatricula] ?? ''),
                'dni' => $cDni !== null ? (string) ($fila[$cDni] ?? '') : '',
                'sexo' => $cSexo !== null ? (string) ($fila[$cSexo] ?? '') : '',
                'nombre' => (string) ($fila[$cNombre] ?? ''),
                'obs' => $cObs !== null ? (string) ($fila[$cObs] ?? '') : '',
                'valores' => $valores,
            ];
        }

        $modo = (string) self::valorJunto($filas, 'CASILLAS CON');

        return self::desdeActa([
            'periodo' => self::valorJunto($filas, 'AÑO ACADEMICO'),
            'semestre' => self::valorJunto($filas, 'SEMESTRE'),
            'fecha' => self::valorJunto($filas, 'FECHA'),
            'programa' => self::valorJunto($filas, 'ESPECIALIDAD'),
            'turno' => self::valorJunto($filas, 'TURNO'),
            'seccion' => self::valorJunto($filas, 'SECCION'),
            'nro_acta' => self::valorJunto($filas, 'N° DE ACTA') ?? self::valorJunto($filas, 'NRO DE ACTA'),
            'en_puntaje' => ! str_starts_with(self::normalizar($modo), 'NOTA'),
            'asignaturas' => $asignaturas,
            'alumnos' => $alumnos,
        ], "Hoja «{$hoja}»", $errores);
    }

    /**
     * Convierte un acta ya leída (de la hoja Excel o de la pantalla de corrección del escaneo)
     * en filas para ActaConsolidadaRpImport. Valida lo mismo en ambos casos y, si el alumno trae
     * el puntaje total o el promedio copiados del acta, comprueba que cuadren con sus casillas.
     *
     * $acta: periodo, semestre (texto), fecha, programa, turno, seccion, nro_acta, en_puntaje,
     *   asignaturas [n.º => [nombre, creditos]], alumnos [[ref, matricula, dni, sexo,
     *   nombre «APELLIDOS,Nombres», obs, valores [n.º => texto], total_acta?, creditos_acta?, promedio_acta?]]
     */
    public static function desdeActa(array $acta, string $origen, array &$errores): array
    {
        $err = function (?string $ref, string $msg) use ($origen, &$errores) {
            $errores[] = $origen.($ref !== null ? ", {$ref}" : '').": {$msg}";
        };
        $txt = fn ($v) => trim((string) ($v ?? ''));

        // ── Encabezado ──
        $periodo = $txt($acta['periodo'] ?? '');
        $programa = $txt($acta['programa'] ?? '');
        $semestre = self::semestre($txt($acta['semestre'] ?? ''));
        $enPuntaje = (bool) ($acta['en_puntaje'] ?? true);

        $faltan = [];
        if ($periodo === '') {
            $faltan[] = 'AÑO ACADÉMICO';
        }
        if ($programa === '') {
            $faltan[] = 'ESPECIALIDAD';
        }
        if (! $semestre) {
            $faltan[] = 'SEMESTRE';
        }
        if ($faltan) {
            $err(null, 'falta llenar '.implode(', ', $faltan).' en el encabezado; no se cargó esta acta.');

            return [];
        }

        $cursos = []; // n.º => [nombre, créditos]
        foreach ((array) ($acta['asignaturas'] ?? []) as $n => $a) {
            $nombre = preg_replace('/\s+/u', ' ', $txt($a['nombre'] ?? ''));
            if ($nombre === '') {
                continue;
            }
            $cred = $txt($a['creditos'] ?? '');
            if ($cred === '' || ! is_numeric($cred) || (int) $cred < 1) {
                $err(null, "la asignatura {$n} ({$nombre}) no tiene CRÉDITOS válidos; no se cargó esta acta.");

                return [];
            }
            $cursos[$n] = [$nombre, (int) $cred];
        }
        if (! $cursos) {
            $err(null, 'no tiene nombres de asignaturas; no se cargó esta acta.');

            return [];
        }
        $creditosActa = array_sum(array_column($cursos, 1));

        $base = [
            'periodo' => $periodo,
            'programa' => $programa,
            'semestre' => $semestre,
            'turno' => $txt($acta['turno'] ?? ''),
            'seccion' => $txt($acta['seccion'] ?? ''),
            'nro_acta' => $txt($acta['nro_acta'] ?? ''),
            'fecha_visacion' => $txt($acta['fecha'] ?? ''),
        ];

        // ── Alumnos ──
        $salida = [];
        foreach ((array) ($acta['alumnos'] ?? []) as $i => $al) {
            $ref = $txt($al['ref'] ?? '') ?: 'alumno '.($i + 1);
            $nombreCompleto = $txt($al['nombre'] ?? '');
            if ($nombreCompleto === '') {
                continue;
            }
            if (! str_contains($nombreCompleto, ',')) {
                $err($ref, "«{$nombreCompleto}»: separa apellidos y nombres con una coma (APELLIDOS,Nombres).");

                continue;
            }
            [$apellidos, $nombres] = array_map('trim', explode(',', $nombreCompleto, 2));

            $obs = $txt($al['obs'] ?? '');
            if (in_array($obs, ['/', '-', '—'], true)) {
                $obs = '';
            }

            $alumno = $base + [
                'nro_matricula' => $txt($al['matricula'] ?? ''),
                'dni' => $txt($al['dni'] ?? ''),
                'sexo' => $txt($al['sexo'] ?? ''),
                'apellidos' => $apellidos,
                'nombres' => $nombres,
                'obs_alumno' => $obs,
            ];

            $notas = [];
            $suma = 0.0;
            $filaValida = true;
            foreach ($cursos as $n => [$curso, $creditos]) {
                $valor = str_replace(',', '.', $txt($al['valores'][$n] ?? ''));
                if ($valor === '') {
                    continue;
                }
                if (! is_numeric($valor)) {
                    $err($ref, "{$apellidos}: «{$valor}» en la asignatura {$n} no es un número.");
                    $filaValida = false;

                    continue;
                }
                $nota = (float) $valor;
                if ($enPuntaje) {
                    $suma += $nota;
                    $nota = $nota / $creditos;
                    if (abs($nota - round($nota)) > 0.001) {
                        $err($ref, "{$apellidos}: el puntaje {$valor} de la asignatura {$n} no se divide exacto entre sus {$creditos} créditos.");
                        $filaValida = false;

                        continue;
                    }
                    $nota = round($nota);
                } else {
                    $suma += $nota * $creditos;
                }
                if ($nota < 0 || $nota > 20) {
                    $err($ref, "{$apellidos}: «{$valor}» en la asignatura {$n} da nota {$nota}, fuera de 0 a 20.");
                    $filaValida = false;

                    continue;
                }
                $notas[$n] = $nota;
            }

            // Cotejo con lo que dice el papel (solo si se copió)
            if ($filaValida && $notas) {
                $totalActa = str_replace(',', '.', $txt($al['total_acta'] ?? ''));
                if ($totalActa !== '' && (! is_numeric($totalActa) || abs((float) $totalActa - $suma) > 0.001)) {
                    $err($ref, "{$apellidos}: las casillas suman {$suma}, pero el acta dice puntaje total {$totalActa}. Alguna casilla está mal copiada.");
                    $filaValida = false;
                }
                $credAlumno = $txt($al['creditos_acta'] ?? '');
                $divisor = is_numeric($credAlumno) && (int) $credAlumno > 0 ? (int) $credAlumno : $creditosActa;
                $promActa = str_replace(',', '.', $txt($al['promedio_acta'] ?? ''));
                if ($filaValida && $promActa !== '' && ! self::promedioCuadra($suma, $divisor, $promActa)) {
                    $err($ref, "{$apellidos}: {$suma} ÷ {$divisor} créditos no da el promedio ".$txt($al['promedio_acta'])." del acta.");
                    $filaValida = false;
                }
            }
            if (! $filaValida) {
                continue; // se corrige la fila completa y se vuelve a cargar
            }

            if (! $notas) {
                if ($obs === '') {
                    $err($ref, "{$apellidos}: no tiene notas ni observación.");

                    continue;
                }
                // Retirado / licencia: figura en todas las asignaturas del acta, sin nota
                foreach ($cursos as $n => $_) {
                    $notas[$n] = null;
                }
            }

            foreach ($notas as $n => $nota) {
                [$curso, $creditos] = $cursos[$n];
                $salida[] = $alumno + [
                    '_fila' => "{$origen}, {$ref}",
                    'curso' => $curso,
                    'creditos' => $creditos,
                    'nota' => $nota,
                ];
            }
        }

        if (! $salida) {
            $err(null, 'no tiene alumnos para cargar.');
        }

        return $salida;
    }

    /** El acta trunca el promedio a 2 decimales; se acepta también redondeado por si alguna acta lo hizo así. */
    public static function promedioCuadra(float $suma, int $divisor, string $promedio): bool
    {
        if (! is_numeric($promedio) || $divisor < 1) {
            return false;
        }
        $exacto = $suma / $divisor;
        $p = (float) $promedio;

        return abs(floor($exacto * 100 + 1e-9) / 100 - $p) < 0.0001 || abs(round($exacto, 2) - $p) < 0.0001;
    }

    /** «DÉCIMO», «X», «10», «10°» → 10. */
    public static function semestre(string $texto): ?int
    {
        $t = self::normalizar($texto);
        $t = trim(preg_replace('/(SEMESTRE|[°º.])/u', '', $t));
        if (ctype_digit($t)) {
            $n = (int) $t;

            return $n >= 1 && $n <= 10 ? $n : null;
        }

        return self::SEMESTRES[$t] ?? null;
    }

    /** Mayúsculas sin tildes, para comparar rótulos. */
    private static function normalizar(string $texto): string
    {
        $t = mb_strtoupper(trim($texto));

        return strtr($t, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'º' => '°']);
    }

    /**
     * Fila y columna de la primera celda cuyo texto empieza con el rótulo (o es igual, si $exacto)
     * dentro de las primeras $hasta filas.
     */
    private static function ubicar(array $filas, string $rotulo, int $hasta, bool $exacto = false): ?array
    {
        $rotulo = self::normalizar($rotulo);
        foreach (array_slice($filas, 0, $hasta, true) as $f => $fila) {
            foreach ((array) $fila as $c => $celda) {
                if (! is_string($celda)) {
                    continue;
                }
                $t = self::normalizar($celda);
                if ($exacto ? $t === $rotulo : str_starts_with($t, $rotulo)) {
                    return [$f, $c];
                }
            }
        }

        return null;
    }

    /** Primer valor no vacío a la derecha del rótulo (las casillas están combinadas). */
    private static function valorJunto(array $filas, string $rotulo): ?string
    {
        $pos = self::ubicar($filas, $rotulo, 8);
        if (! $pos) {
            return null;
        }
        [$f, $c] = $pos;
        $fila = array_values((array) $filas[$f]);
        for ($i = $c + 1; $i < count($fila); $i++) {
            $v = $fila[$i];
            if ($v === null || trim((string) $v) === '') {
                continue;
            }
            // Si lo siguiente es otro rótulo, la casilla quedó vacía
            if (is_string($v) && str_ends_with(trim($v), ':')) {
                return null;
            }

            return trim((string) $v);
        }

        return null;
    }

    private static function columnaRotulo(array $filas, int $fCab, string $rotulo): ?int
    {
        $rotulo = self::normalizar($rotulo);
        foreach (((array) ($filas[$fCab] ?? [])) as $c => $celda) {
            if (is_string($celda) && str_starts_with(self::normalizar($celda), $rotulo)) {
                return $c;
            }
        }

        return null;
    }
}
