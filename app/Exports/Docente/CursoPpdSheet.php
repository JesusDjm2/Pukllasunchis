<?php

namespace App\Exports\Docente;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Curso PPD: valoración final de cada competencia (Proceso 40% + Final 60%), nivel de
 * desempeño, calificación del curso, calificación en el sistema y observaciones — los mismos
 * rótulos de la pantalla de calificar PPD.
 */
class CursoPpdSheet extends HojaBase
{
    private const NARANJA_PPD = 'B5651D';

    protected function dibujar(Worksheet $hoja): void
    {
        $nComp = max(1, $this->c['competencias']->count());
        $cab = self::FILA_TABLA;
        $primera = $cab + 1;

        $columnas = [['N°', 5], ['Estudiante', 34], ['Estado', 14]];
        for ($k = 1; $k <= $nComp; $k++) {
            $columnas[] = ["Competencia {$k}\n(valoración)", 13];
        }
        array_push($columnas, ['Nivel de desempeño', 12], ['Calificación del curso', 12], ['Calificación en el sistema', 12], ['Observaciones', 40]);
        $ultimaColIdx = count($columnas);
        $ultimaCol = $this->col($ultimaColIdx);

        foreach ($columnas as $i => [$titulo, $ancho]) {
            $hoja->getColumnDimension($this->col($i + 1))->setWidth($ancho);
        }
        $this->encabezado($hoja, $ultimaColIdx);

        foreach ($columnas as $i => [$titulo]) {
            $hoja->setCellValue($this->col($i + 1).$cab, $titulo);
        }
        $hoja->getRowDimension($cab)->setRowHeight(36);
        $this->estiloCabeceraTabla($hoja, "A{$cab}:C{$cab}", self::AZUL_OSCURO);
        $this->estiloCabeceraTabla($hoja, 'D'.$cab.':'.$this->col(3 + $nComp).$cab, self::NARANJA_PPD);
        $this->estiloCabeceraTabla($hoja, $this->col(4 + $nComp)."{$cab}:{$ultimaCol}{$cab}", self::AZUL);

        $f = $primera;
        foreach ($this->c['filas'] as $i => $fila) {
            $hoja->setCellValue("A{$f}", $i + 1);
            $hoja->setCellValue("B{$f}", $fila['nombre']);
            $hoja->setCellValue("C{$f}", $fila['estado'] ?: 'Matriculado');
            $hoja->getStyle("C{$f}")->getFont()->setBold((bool) $fila['estado'])->getColor()->setRGB($fila['estado'] ? self::ROJO : self::GRIS);

            for ($k = 0; $k < $nComp; $k++) {
                $this->nota($hoja, $this->col(4 + $k).$f, $fila['comps'][$k] ?? null);
                $hoja->getStyle($this->col(4 + $k).$f)->getFont()->setBold(false);
            }
            $base = 4 + $nComp;
            $fila['nivel'] !== null ? $hoja->setCellValue($this->col($base).$f, $fila['nivel']) : $this->vacio($hoja, $this->col($base).$f);
            if ($fila['calificacion'] !== null) {
                $hoja->setCellValue($this->col($base + 1).$f, (float) $fila['calificacion']);
                $hoja->getStyle($this->col($base + 1).$f)->getNumberFormat()->setFormatCode('0.00');
            } else {
                $this->vacio($hoja, $this->col($base + 1).$f);
            }
            $this->nota($hoja, $this->col($base + 2).$f, $fila['nota']);
            $hoja->setCellValue("{$ultimaCol}{$f}", $fila['observaciones'] ?: '');
            $f++;
        }
        $ultimaFila = max($primera, $f - 1);

        if ($this->c['filas']) {
            $this->estiloCuerpo($hoja, "A{$primera}:{$ultimaCol}{$ultimaFila}");
            $this->cebra($hoja, $primera, $ultimaFila, $ultimaCol);
            $hoja->getStyle("A{$primera}:A{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle("C{$primera}:".$this->col(6 + $nComp).$ultimaFila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle("{$ultimaCol}{$primera}:{$ultimaCol}{$ultimaFila}")->getAlignment()->setWrapText(true);
            $hoja->getStyle("{$ultimaCol}{$primera}:{$ultimaCol}{$ultimaFila}")->getFont()->setSize(9);
            $hoja->setAutoFilter("A{$cab}:{$ultimaCol}{$ultimaFila}");
        } else {
            $hoja->setCellValue("A{$primera}", 'Este curso no tiene estudiantes en el periodo.');
            $hoja->mergeCells("A{$primera}:{$ultimaCol}{$primera}");
            $hoja->getStyle("A{$primera}")->getFont()->setItalic(true)->getColor()->setRGB(self::GRIS);
        }

        $r = $this->resumen($hoja, $ultimaFila + 2, $ultimaCol);
        $hoja->setCellValue("B{$r}", 'Competencias');
        $hoja->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB(self::AZUL_OSCURO);
        foreach ($this->c['competencias']->values() as $i => $comp) {
            $r++;
            $hoja->setCellValue("B{$r}", 'Competencia '.($i + 1).' — '.$comp->nombre);
            $hoja->mergeCells("B{$r}:{$ultimaCol}{$r}");
            $hoja->getStyle("B{$r}")->getFont()->setSize(9)->getColor()->setRGB(self::GRIS);
        }
        $r++;
        $hoja->setCellValue("B{$r}", 'Valoración de cada competencia = Proceso 40% + Final 60%. La nota final es la calificación en el sistema; aprobatoria: '.self::NOTA_MINIMA.' (vigesimal).');
        $hoja->mergeCells("B{$r}:{$ultimaCol}{$r}");
        $hoja->getStyle("B{$r}")->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB(self::GRIS);

        $hoja->freezePane('D'.$primera);
        $this->impresion($hoja, "A1:{$ultimaCol}{$r}", $cab, $cab);
        $hoja->setSelectedCell('A1');
    }
}
