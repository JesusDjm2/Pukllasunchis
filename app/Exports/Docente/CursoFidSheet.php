<?php

namespace App\Exports\Docente;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Curso FID: una fila por estudiante con Parcial 1, Parcial 2 y Desempeño final; cada bloque
 * trae la valoración por competencia, la valoración del curso (0–5), el nivel de logro y la
 * nota vigesimal.
 */
class CursoFidSheet extends HojaBase
{
    /** Mismos tonos que las barras de avance del dashboard docente, oscurecidos para texto blanco. */
    private const BLOQUES = [
        'p1' => ['Parcial 1', '3B5BC4'],
        'p2' => ['Parcial 2', '13855C'],
        'p3' => ['Desempeño final', '23899A'],
    ];

    protected function dibujar(Worksheet $hoja): void
    {
        $competencias = $this->c['competencias']->values();
        $nComp = $competencias->count();
        $porBloque = $nComp + 3;
        $ultimaColIdx = 3 + 3 * $porBloque;
        $ultimaCol = $this->col($ultimaColIdx);
        $cab1 = self::FILA_TABLA;
        $cab2 = $cab1 + 1;
        $primera = $cab2 + 1;

        // Anchos antes de la ficha: la ficha se reparte en mitades de la tabla.
        foreach ([['A', 5], ['B', 34], ['C', 14]] as [$col, $ancho]) {
            $hoja->getColumnDimension($col)->setWidth($ancho);
        }
        $this->encabezado($hoja, $ultimaColIdx);

        // ── Cabecera en dos filas ──
        foreach ([['A', 'N°'], ['B', 'Estudiante'], ['C', 'Estado']] as [$col, $titulo]) {
            $hoja->setCellValue("{$col}{$cab1}", $titulo);
            $hoja->mergeCells("{$col}{$cab1}:{$col}{$cab2}");
        }
        $this->estiloCabeceraTabla($hoja, "A{$cab1}:C{$cab2}", self::AZUL_OSCURO);

        $inicio = 4;
        foreach (self::BLOQUES as [$titulo, $color]) {
            $desde = $this->col($inicio);
            $hasta = $this->col($inicio + $porBloque - 1);
            $hoja->setCellValue("{$desde}{$cab1}", $titulo);
            $hoja->mergeCells("{$desde}{$cab1}:{$hasta}{$cab1}");

            $sub = $competencias->keys()->map(fn ($i) => 'C'.($i + 1))->all();
            array_push($sub, 'Valoración (0–5)', 'Nivel de logro', 'Nota');
            foreach ($sub as $k => $texto) {
                $col = $this->col($inicio + $k);
                $hoja->setCellValue("{$col}{$cab2}", $texto);
                $hoja->getColumnDimension($col)->setWidth($k < $nComp ? 13 : ($texto === 'Nivel de logro' ? 14 : ($texto === 'Nota' ? 8 : 11)));
            }
            $this->estiloCabeceraTabla($hoja, "{$desde}{$cab1}:{$hasta}{$cab2}", $color);
            $inicio += $porBloque;
        }
        $hoja->getRowDimension($cab1)->setRowHeight(22);
        $hoja->getRowDimension($cab2)->setRowHeight(30);

        // ── Estudiantes ──
        $f = $primera;
        foreach ($this->c['filas'] as $i => $fila) {
            $hoja->setCellValue("A{$f}", $i + 1);
            $hoja->setCellValue("B{$f}", $fila['nombre']);
            if ($fila['estado']) {
                $hoja->setCellValue("C{$f}", $fila['estado']);
                $hoja->getStyle("C{$f}")->getFont()->setBold(true)->getColor()->setRGB($fila['estado'] === 'Inhabilitado' ? self::ROJO : self::AMBAR);
                $hoja->getStyle("B{$f}")->getFont()->getColor()->setRGB(self::GRIS);
            } else {
                $hoja->setCellValue("C{$f}", 'Matriculado');
                $hoja->getStyle("C{$f}")->getFont()->getColor()->setRGB(self::GRIS);
            }

            $inicio = 4;
            foreach (array_keys(self::BLOQUES) as $clave) {
                $p = $fila[$clave];
                for ($k = 0; $k < $nComp; $k++) {
                    $this->nivel($hoja, $this->col($inicio + $k).$f, $p['comps'][$k] ?? null);
                    $hoja->getStyle($this->col($inicio + $k).$f)->getFont()->setBold(false);
                }
                $celdaVal = $this->col($inicio + $nComp).$f;
                if ($p['valoracion'] !== null) {
                    $hoja->setCellValue($celdaVal, (float) $p['valoracion']);
                    $hoja->getStyle($celdaVal)->getNumberFormat()->setFormatCode('0.00');
                } else {
                    $this->vacio($hoja, $celdaVal);
                }
                $this->nivel($hoja, $this->col($inicio + $nComp + 1).$f, $p['nivel']);
                $this->nota($hoja, $this->col($inicio + $nComp + 2).$f, $p['nota']);
                $inicio += $porBloque;
            }
            $f++;
        }
        $ultimaFila = max($primera, $f - 1);

        if ($this->c['filas']) {
            $this->estiloCuerpo($hoja, "A{$primera}:{$ultimaCol}{$ultimaFila}");
            $this->cebra($hoja, $primera, $ultimaFila, $ultimaCol);
            $hoja->getStyle("A{$primera}:A{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle("C{$primera}:{$ultimaCol}{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->setAutoFilter("A{$cab2}:{$ultimaCol}{$ultimaFila}");
        } else {
            $hoja->setCellValue("A{$primera}", 'Este curso no tiene estudiantes en el periodo.');
            $hoja->mergeCells("A{$primera}:{$ultimaCol}{$primera}");
            $hoja->getStyle("A{$primera}")->getFont()->setItalic(true)->getColor()->setRGB(self::GRIS);
        }

        // Separadores gruesos entre bloques, de la cabecera al final.
        $inicio = 4;
        foreach (self::BLOQUES as $_) {
            $hoja->getStyle($this->col($inicio).$cab1.':'.$this->col($inicio).$ultimaFila)->getBorders()->getLeft()
                ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB(self::AZUL);
            $inicio += $porBloque;
        }

        // ── Resumen y leyenda ──
        $r = $this->resumen($hoja, $ultimaFila + 2, $ultimaCol);
        $hoja->setCellValue("B{$r}", 'Competencias');
        $hoja->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB(self::AZUL_OSCURO);
        foreach ($competencias as $i => $comp) {
            $r++;
            $hoja->setCellValue("B{$r}", 'C'.($i + 1).' — '.$comp->nombre);
            $hoja->mergeCells("B{$r}:{$ultimaCol}{$r}");
            $hoja->getStyle("B{$r}")->getFont()->setSize(9)->getColor()->setRGB(self::GRIS);
        }
        $r++;
        $hoja->setCellValue("B{$r}", 'Niveles: Destacado · Logrado · En Proceso · Inicio · Previo al Inicio. La nota final es la del Desempeño; aprobatoria: '.self::NOTA_MINIMA.' (vigesimal).');
        $hoja->mergeCells("B{$r}:{$ultimaCol}{$r}");
        $hoja->getStyle("B{$r}")->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB(self::GRIS);

        $hoja->freezePane('D'.$primera);
        $this->impresion($hoja, "A1:{$ultimaCol}{$r}", $cab1, $cab2);
        $hoja->setSelectedCell('A1');
    }
}
