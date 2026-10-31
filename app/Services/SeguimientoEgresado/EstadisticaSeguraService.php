<?php

namespace App\Services\SeguimientoEgresado;

class EstadisticaSeguraService
{
    /**
     * Umbral mínimo por celda para evitar reidentificación.
     */
    protected int $umbralMinimo = 5;

    public function __construct(int $umbralMinimo = 5)
    {
        $this->umbralMinimo = $umbralMinimo;
    }

    /**
     * Protege un conteo individual bajo secreto estadístico (< 5).
     */
    public function protegerConteo(int $conteo, ?int $total = null): array
    {
        if ($conteo > 0 && $conteo < $this->umbralMinimo) {
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
     * Aplica protección y supresión secundaria sobre una matriz 2D asociativa.
     */
    public function protegerMatriz2D(array $conteos, array $filas, array $columnas): array
    {
        $totalesFilas = [];
        $totalesColumnas = [];
        $totalGeneral = 0;

        foreach ($filas as $f) {
            $totalesFilas[$f] = 0;
            foreach ($columnas as $c) {
                $cnt = $conteos[$f][$c] ?? 0;
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
            foreach ($columnas as $c) {
                $item = $segura['matriz'][$f][$c] ?? ['valor' => 0, 'suprimido' => false];
                $celdas[$f][$c] = [
                    'conteo' => $item['valor'],
                    'suprimido' => $item['suprimido'],
                    'porcentaje_fila' => null,
                    'porcentaje_columna' => null,
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
     * Aplica supresión primaria y complementaria sobre una matriz bidimensional.
     *
     * @param array $matriz Array asociativo [fila => [columna => count]]
     * @param array $totalesFilas [fila => total]
     * @param array $totalesColumnas [columna => total]
     * @param int $totalGeneral
     * @return array
     */
    public function procesarMatrizSegura(array $matriz, array $totalesFilas, array $totalesColumnas, int $totalGeneral): array
    {
        $matrizSegura = [];
        $celdasSuprimidasPorFila = [];
        $celdasSuprimidasPorColumna = [];

        // 1. Supresión primaria: celdas con 1 <= n < umbral
        foreach ($matriz as $filaKey => $cols) {
            $matrizSegura[$filaKey] = [];
            $celdasSuprimidasPorFila[$filaKey] = 0;

            foreach ($cols as $colKey => $valor) {
                if (!isset($celdasSuprimidasPorColumna[$colKey])) {
                    $celdasSuprimidasPorColumna[$colKey] = 0;
                }

                $n = (int) $valor;

                if ($n > 0 && $n < $this->umbralMinimo) {
                    $matrizSegura[$filaKey][$colKey] = [
                        'valor' => '< ' . $this->umbralMinimo,
                        'suprimido' => true,
                        'tipo_supresion' => 'primaria',
                    ];
                    $celdasSuprimidasPorFila[$filaKey]++;
                    $celdasSuprimidasPorColumna[$colKey]++;
                } else {
                    $matrizSegura[$filaKey][$colKey] = [
                        'valor' => $n,
                        'suprimido' => false,
                        'tipo_supresion' => null,
                    ];
                }
            }
        }

        // 2. Supresión complementaria (por fila): si solo hay 1 celda suprimida, suprimir la menor celda no nula restante
        foreach ($matrizSegura as $filaKey => $cols) {
            if ($celdasSuprimidasPorFila[$filaKey] === 1) {
                $candidatos = [];
                foreach ($cols as $colKey => $item) {
                    if (!$item['suprimido'] && (int)$item['valor'] > 0) {
                        $candidatos[$colKey] = (int)$item['valor'];
                    }
                }
                if (!empty($candidatos)) {
                    asort($candidatos);
                    $colAEscudar = array_key_first($candidatos);
                    $matrizSegura[$filaKey][$colAEscudar] = [
                        'valor' => '—', // Supresión complementaria
                        'suprimido' => true,
                        'tipo_supresion' => 'complementaria',
                    ];
                }
            }
        }

        // 3. Procesar totales de filas
        $totalesFilasSeguros = [];
        foreach ($totalesFilas as $filaKey => $total) {
            $n = (int) $total;
            if ($n > 0 && $n < $this->umbralMinimo) {
                $totalesFilasSeguros[$filaKey] = [
                    'valor' => '< ' . $this->umbralMinimo,
                    'suprimido' => true,
                ];
            } else {
                $totalesFilasSeguros[$filaKey] = [
                    'valor' => $n,
                    'suprimido' => false,
                ];
            }
        }

        // 4. Procesar totales de columnas
        $totalesColumnasSeguros = [];
        foreach ($totalesColumnas as $colKey => $total) {
            $n = (int) $total;
            if ($n > 0 && $n < $this->umbralMinimo) {
                $totalesColumnasSeguros[$colKey] = [
                    'valor' => '< ' . $this->umbralMinimo,
                    'suprimido' => true,
                ];
            } else {
                $totalesColumnasSeguros[$colKey] = [
                    'valor' => $n,
                    'suprimido' => false,
                ];
            }
        }

        // 5. Total general
        $totalGeneralSeguro = ($totalGeneral > 0 && $totalGeneral < $this->umbralMinimo)
            ? ['valor' => '< ' . $this->umbralMinimo, 'suprimido' => true]
            : ['valor' => $totalGeneral, 'suprimido' => false];

        return [
            'matriz' => $matrizSegura,
            'totales_filas' => $totalesFilasSeguros,
            'totales_columnas' => $totalesColumnasSeguros,
            'total_general' => $totalGeneralSeguro,
            'reglas_aplicadas' => [
                'umbral_minimo' => $this->umbralMinimo,
                'supresion_primaria' => 'Celdas entre 1 y ' . ($this->umbralMinimo - 1) . ' ocultadas',
                'supresion_complementaria' => 'Protección matemática contra derivación por diferencias marginales',
            ],
        ];
    }

    /**
     * Aplica supresión sobre un array unidimensional de distribución (para gráficos y mapas).
     */
    public function procesarDistribucionSegura(array $distribucion): array
    {
        $segura = [];
        foreach ($distribucion as $key => $valor) {
            $n = (int) $valor;
            if ($n > 0 && $n < $this->umbralMinimo) {
                $segura[$key] = [
                    'etiqueta' => $key,
                    'valor' => '< ' . $this->umbralMinimo,
                    'conteo_estimado' => null,
                    'suprimido' => true,
                ];
            } else {
                $segura[$key] = [
                    'etiqueta' => $key,
                    'valor' => $n,
                    'conteo_estimado' => $n,
                    'suprimido' => false,
                ];
            }
        }
        return $segura;
    }
}
