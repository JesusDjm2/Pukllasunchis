<?php

namespace App\Services\SeguimientoEgresado;

class EstadisticaSeguraService
{
    /**
     * Umbral mínimo configurable (1 = muestra todos los datos tal cual sin ocultar menores a 5).
     */
    protected int $umbralMinimo = 1;

    public function __construct(int $umbralMinimo = 1)
    {
        $this->umbralMinimo = $umbralMinimo;
    }

    /**
     * Devuelve el conteo exacto y su porcentaje sobre el total (sin ocultar valores menores a 5 cuando umbralMinimo = 1).
     */
    public function protegerConteo(int $conteo, ?int $total = null): array
    {
        if ($this->umbralMinimo > 1 && $conteo > 0 && $conteo < $this->umbralMinimo) {
            return [
                'valor' => '< ' . $this->umbralMinimo,
                'suprimido' => true,
                'porcentaje' => null,
            ];
        }

        $pct = ($total && $total > 0) ? round(($conteo / $total) * 100, 1) : null;

        return [
            'valor' => $conteo,
            'suprimido' => false,
            'porcentaje' => $pct,
        ];
    }

    /**
     * Procesa una matriz 2D asociativa devolviendo los conteos reales y porcentajes de fila/columna.
     */
    public function protegerMatriz2D(array $conteos, array $filas, array $columnas): array
    {
        $totalesFilas = [];
        $totalesColumnas = [];
        $totalGeneral = 0;

        foreach ($filas as $f) {
            $totalesFilas[$f] = 0;
            foreach ($columnas as $c) {
                $cnt = (int) ($conteos[$f][$c] ?? 0);
                $totalesFilas[$f] += $cnt;
                if (!isset($totalesColumnas[$c])) {
                    $totalesColumnas[$c] = 0;
                }
                $totalesColumnas[$c] += $cnt;
                $totalGeneral += $cnt;
            }
        }

        $segura = $this->procesarMatrizSegura($conteos, $totalesFilas, $totalesColumnas, $totalGeneral);

        $celdas = [];
        foreach ($filas as $f) {
            $celdas[$f] = [];
            $totF = (int) ($totalesFilas[$f] ?? 0);
            foreach ($columnas as $c) {
                $totC = (int) ($totalesColumnas[$c] ?? 0);
                $item = $segura['matriz'][$f][$c] ?? ['valor' => 0, 'suprimido' => false];
                $valNum = is_numeric($item['valor']) ? (int) $item['valor'] : 0;
                $celdas[$f][$c] = [
                    'conteo' => $item['valor'],
                    'suprimido' => $item['suprimido'],
                    'porcentaje_fila' => (!$item['suprimido'] && $totF > 0) ? round(($valNum / $totF) * 100, 1) : null,
                    'porcentaje_columna' => (!$item['suprimido'] && $totC > 0) ? round(($valNum / $totC) * 100, 1) : null,
                ];
            }
        }

        $totFilasSeg = [];
        foreach ($filas as $f) {
            $item = $segura['totales_filas'][$f] ?? ['valor' => 0, 'suprimido' => false];
            $totFilasSeg[$f] = [
                'conteo' => $item['valor'],
                'suprimido' => $item['suprimido'],
            ];
        }

        $totColsSeg = [];
        foreach ($columnas as $c) {
            $item = $segura['totales_columnas'][$c] ?? ['valor' => 0, 'suprimido' => false];
            $totColsSeg[$c] = [
                'conteo' => $item['valor'],
                'suprimido' => $item['suprimido'],
            ];
        }

        return [
            'celdas' => $celdas,
            'totales_fila' => $totFilasSeg,
            'totales_columna' => $totColsSeg,
            'total_general' => $segura['total_general']['valor'],
            'suprimido_general' => $segura['total_general']['suprimido'],
        ];
    }

    /**
     * Procesa la matriz bidimensional mostrando todos los valores reales (incluidos los menores a 5) cuando umbralMinimo = 1.
     */
    public function procesarMatrizSegura(array $matriz, array $totalesFilas, array $totalesColumnas, int $totalGeneral): array
    {
        $matrizSegura = [];
        $celdasSuprimidasPorFila = [];

        foreach ($matriz as $filaKey => $cols) {
            $matrizSegura[$filaKey] = [];
            $celdasSuprimidasPorFila[$filaKey] = 0;
            $totF = (int) ($totalesFilas[$filaKey] ?? 0);

            foreach ($cols as $colKey => $valor) {
                $n = (int) $valor;
                $totC = (int) ($totalesColumnas[$colKey] ?? 0);

                if ($this->umbralMinimo > 1 && $n > 0 && $n < $this->umbralMinimo) {
                    $matrizSegura[$filaKey][$colKey] = [
                        'valor' => '< ' . $this->umbralMinimo,
                        'conteo' => '< ' . $this->umbralMinimo,
                        'suprimido' => true,
                        'tipo_supresion' => 'primaria',
                        'porcentaje_fila' => null,
                        'porcentaje_columna' => null,
                    ];
                    $celdasSuprimidasPorFila[$filaKey]++;
                } else {
                    $matrizSegura[$filaKey][$colKey] = [
                        'valor' => $n,
                        'conteo' => $n,
                        'suprimido' => false,
                        'tipo_supresion' => null,
                        'porcentaje_fila' => $totF > 0 ? round(($n / $totF) * 100, 1) : null,
                        'porcentaje_columna' => $totC > 0 ? round(($n / $totC) * 100, 1) : null,
                    ];
                }
            }
        }

        if ($this->umbralMinimo > 1) {
            foreach ($matrizSegura as $filaKey => $cols) {
                if ($celdasSuprimidasPorFila[$filaKey] === 1) {
                    $candidatos = [];
                    foreach ($cols as $colKey => $item) {
                        if (!$item['suprimido'] && (int) $item['valor'] > 0) {
                            $candidatos[$colKey] = (int) $item['valor'];
                        }
                    }
                    if (!empty($candidatos)) {
                        asort($candidatos);
                        $colAEscudar = array_key_first($candidatos);
                        $matrizSegura[$filaKey][$colAEscudar] = [
                            'valor' => '—',
                            'conteo' => '—',
                            'suprimido' => true,
                            'tipo_supresion' => 'complementaria',
                            'porcentaje_fila' => null,
                            'porcentaje_columna' => null,
                        ];
                    }
                }
            }
        }

        $totalesFilasSeguros = [];
        foreach ($totalesFilas as $filaKey => $total) {
            $n = (int) $total;
            if ($this->umbralMinimo > 1 && $n > 0 && $n < $this->umbralMinimo) {
                $totalesFilasSeguros[$filaKey] = [
                    'valor' => '< ' . $this->umbralMinimo,
                    'conteo' => '< ' . $this->umbralMinimo,
                    'suprimido' => true,
                ];
            } else {
                $totalesFilasSeguros[$filaKey] = [
                    'valor' => $n,
                    'conteo' => $n,
                    'suprimido' => false,
                ];
            }
        }

        $totalesColumnasSeguros = [];
        foreach ($totalesColumnas as $colKey => $total) {
            $n = (int) $total;
            if ($this->umbralMinimo > 1 && $n > 0 && $n < $this->umbralMinimo) {
                $totalesColumnasSeguros[$colKey] = [
                    'valor' => '< ' . $this->umbralMinimo,
                    'conteo' => '< ' . $this->umbralMinimo,
                    'suprimido' => true,
                ];
            } else {
                $totalesColumnasSeguros[$colKey] = [
                    'valor' => $n,
                    'conteo' => $n,
                    'suprimido' => false,
                ];
            }
        }

        $totalGeneralSeguro = ($this->umbralMinimo > 1 && $totalGeneral > 0 && $totalGeneral < $this->umbralMinimo)
            ? ['valor' => '< ' . $this->umbralMinimo, 'conteo' => '< ' . $this->umbralMinimo, 'suprimido' => true]
            : ['valor' => $totalGeneral, 'conteo' => $totalGeneral, 'suprimido' => false];

        return [
            'matriz' => $matrizSegura,
            'totales_filas' => $totalesFilasSeguros,
            'totales_columnas' => $totalesColumnasSeguros,
            'total_general' => $totalGeneralSeguro,
            'reglas_aplicadas' => [
                'umbral_minimo' => $this->umbralMinimo,
                'supresion_primaria' => $this->umbralMinimo > 1 ? ('Celdas entre 1 y ' . ($this->umbralMinimo - 1) . ' ocultadas') : 'Conteos exactos mostrados tal cual (incluidos menores a 5)',
                'supresion_complementaria' => $this->umbralMinimo > 1 ? 'Activa' : 'Sin supresión',
            ],
        ];
    }

    /**
     * Procesa un array unidimensional de distribución mostrando todos los valores exactos (incluidos menores a 5) y sus porcentajes.
     */
    public function procesarDistribucionSegura(array $distribucion): array
    {
        $segura = [];
        $total = array_sum(array_map('intval', $distribucion));
        foreach ($distribucion as $key => $valor) {
            $n = (int) $valor;
            if ($this->umbralMinimo > 1 && $n > 0 && $n < $this->umbralMinimo) {
                $segura[$key] = [
                    'etiqueta' => $key,
                    'provincia' => $key,
                    'valor' => '< ' . $this->umbralMinimo,
                    'total' => '< ' . $this->umbralMinimo,
                    'conteo_estimado' => null,
                    'porcentaje' => null,
                    'suprimido' => true,
                ];
            } else {
                $segura[$key] = [
                    'etiqueta' => $key,
                    'provincia' => $key,
                    'valor' => $n,
                    'total' => $n,
                    'conteo_estimado' => $n,
                    'porcentaje' => $total > 0 ? round(($n / $total) * 100, 1) : 0.0,
                    'suprimido' => false,
                ];
            }
        }
        return $segura;
    }
}
