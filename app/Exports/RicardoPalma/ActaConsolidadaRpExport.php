<?php

namespace App\Exports\RicardoPalma;

use App\Models\RicardoPalma\RpActa;
use App\Models\RicardoPalma\RpCalificacion;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActaConsolidadaRpExport implements FromArray, WithEvents, WithTitle, WithProperties
{
    protected const COLOR_NAVY = '1F2B3A';
    protected const COLOR_PETROLEO = '253449';
    protected const COLOR_AZUL_SUB = '33445A';
    protected const COLOR_ORO = 'CD9244';
    protected const COLOR_ORO_SUAVE = 'FDF8F0';
    protected const COLOR_FONDO_SUB = 'E8EEF6';
    protected const COLOR_ZEBRA = 'F9FAFB';
    protected const COLOR_BORDE = 'CBD5E1';
    protected const COLOR_BORDE_OSCURO = '94A3B8';
    protected const COLOR_ROJO = 'C0392B';
    protected const COLOR_ROJO_FONDO = 'FDECEA';
    protected const COLOR_VERDE = '0E9467';
    protected const COLOR_VERDE_FONDO = 'E6F4EC';
    protected const COLOR_GRIS = '64748B';

    protected RpActa $acta;
    protected $cursos;
    protected $estudiantes;
    protected array $cuadro;

    public function __construct(RpActa $acta)
    {
        $this->acta = $acta;

        // Mismos datos que la pantalla y la impresión del acta
        $matriz = $acta->matriz();
        $this->cursos = $matriz['cursos'];
        $this->estudiantes = $matriz['estudiantes'];
        $this->cuadro = $matriz['cuadro'];
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        $numero = $this->acta->numero_acta ?: 'Acta_' . $this->acta->id;
        $periodo = $this->acta->periodo?->codigo ?: 'Histórico';
        $limpio = "Acta_{$numero}_{$periodo}";
        return mb_substr(preg_replace('/[\[\]:*?\/\\\\]/', '_', $limpio), 0, 31);
    }

    public function properties(): array
    {
        return [
            'creator' => 'IESP Ricardo Palma - Sistema Académico Puklla',
            'lastModifiedBy' => 'IESP Ricardo Palma',
            'title' => 'Acta Consolidada de Evaluación - ' . ($this->acta->numero_acta ?: '#' . $this->acta->id),
            'subject' => ($this->acta->programa?->nombre ?? '') . ' · Periodo ' . ($this->acta->periodo?->codigo ?? ''),
            'company' => 'IESP Ricardo Palma',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $hoja = $event->sheet->getDelegate();
                $hoja->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9.5);
                $hoja->setShowGridlines(true);

                // Configuración de página para impresión oficial (A4 Horizontal)
                $hoja->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $hoja->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $hoja->getPageSetup()->setFitToWidth(1);
                $hoja->getPageSetup()->setFitToHeight(0);
                $hoja->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.4)->setRight(0.4);

                $this->construirActaExcel($hoja);
            },
        ];
    }

    protected function construirActaExcel(Worksheet $hoja): void
    {
        $numCursos = $this->cursos->count();
        $totalCols = 3 + ($numCursos * 2) + 4; // Orden, Matrícula, Nombre + 2*cursos + Pjt, Crd, Prom, Obs
        $ultimaCol = Coordinate::stringFromColumnIndex($totalCols);

        // ═════════════════════════════════════════════════════════════════════
        // 1. ENCABEZADO INSTITUCIONAL
        // ═════════════════════════════════════════════════════════════════════
        $hoja->setCellValue('A1', 'INSTITUTO DE EDUCACIÓN SUPERIOR PEDAGÓGICO "RICARDO PALMA"');
        $hoja->mergeCells("A1:{$ultimaCol}1");
        $hoja->getRowDimension(1)->setRowHeight(30);
        $hoja->getStyle("A1:{$ultimaCol}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $hoja->setCellValue('A2', 'ACTA CONSOLIDADA DE EVALUACIÓN DEL RENDIMIENTO ACADÉMICO'
            . '  ·  Autorización de funcionamiento ' . RpActa::AUTORIZACION . '  ·  D.R.E. ' . RpActa::DRE);
        $hoja->mergeCells("A2:{$ultimaCol}2");
        $hoja->getRowDimension(2)->setRowHeight(22);
        $hoja->getStyle("A2:{$ultimaCol}2")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PETROLEO]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // ═════════════════════════════════════════════════════════════════════
        // 2. FICHA TÉCNICA DEL ACTA (Filas 3, 4 y 5)
        // ═════════════════════════════════════════════════════════════════════
        $mitadColIdx = max(4, intdiv($totalCols, 2));
        $mitadColLetra = Coordinate::stringFromColumnIndex($mitadColIdx);
        $mitadColSiguiente = Coordinate::stringFromColumnIndex($mitadColIdx + 1);

        $datosFicha = [
            3 => [
                'izq' => ['etiqueta' => 'CARRERA / PROGRAMA:', 'valor' => $this->acta->programa?->nombre ?: '—'],
                'der' => ['etiqueta' => 'PERIODO ACADÉMICO:', 'valor' => $this->acta->periodo?->codigo ?: '—'],
            ],
            4 => [
                'izq' => ['etiqueta' => 'SEMESTRE / CICLO:', 'valor' => 'Semestre ' . $this->acta->numero_semestre],
                'der' => ['etiqueta' => 'SECCIÓN Y TURNO:', 'valor' => "Sección: {$this->acta->seccion}  |  Turno: {$this->acta->turno}"],
            ],
            5 => [
                'izq' => ['etiqueta' => 'N° DE ACTA OFICIAL:', 'valor' => $this->acta->numero_acta ?: 'S/N (#' . $this->acta->id . ')'],
                'der' => ['etiqueta' => 'FECHA DE VISACIÓN:', 'valor' => $this->acta->fecha_emision ? $this->acta->fecha_emision->format('d/m/Y') : '—'],
            ],
        ];

        foreach ($datosFicha as $fila => $d) {
            $hoja->getRowDimension($fila)->setRowHeight(20);

            // Bloque Izquierdo (Col A hasta mitad)
            $hoja->setCellValue("A{$fila}", $d['izq']['etiqueta']);
            $hoja->getStyle("A{$fila}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8.5, 'color' => ['rgb' => self::COLOR_GRIS]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_FONDO_SUB]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            ]);

            $rangoValorIzq = "B{$fila}:{$mitadColLetra}{$fila}";
            $hoja->mergeCells($rangoValorIzq);
            $hoja->setCellValueExplicit("B{$fila}", (string) $d['izq']['valor'], DataType::TYPE_STRING);
            $hoja->getStyle($rangoValorIzq)->applyFromArray([
                'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => self::COLOR_NAVY]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            ]);

            // Bloque Derecho (mitad+1 hasta final)
            $colDerEti = $mitadColSiguiente;
            $colDerVal = Coordinate::stringFromColumnIndex($mitadColIdx + 2);
            $hoja->setCellValue("{$colDerEti}{$fila}", $d['der']['etiqueta']);
            $hoja->getStyle("{$colDerEti}{$fila}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8.5, 'color' => ['rgb' => self::COLOR_GRIS]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_FONDO_SUB]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            ]);

            $rangoValorDer = "{$colDerVal}{$fila}:{$ultimaCol}{$fila}";
            $hoja->mergeCells($rangoValorDer);
            $hoja->setCellValueExplicit("{$colDerVal}{$fila}", (string) $d['der']['valor'], DataType::TYPE_STRING);
            $hoja->getStyle($rangoValorDer)->applyFromArray([
                'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => self::COLOR_NAVY]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            ]);

            // Bordes de la ficha
            $hoja->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDE);
        }

        // ═════════════════════════════════════════════════════════════════════
        // 3. CABECERAS DE LA TABLA MATRIZ (Filas 7 y 8)
        // ═════════════════════════════════════════════════════════════════════
        $hoja->getRowDimension(7)->setRowHeight(28);
        $hoja->getRowDimension(8)->setRowHeight(18);

        // Fila 7: Cabeceras principales fijas
        $hoja->setCellValue('A7', 'N°');
        $hoja->mergeCells('A7:A8');
        $hoja->setCellValue('B7', 'N° MATRÍCULA');
        $hoja->mergeCells('B7:B8');
        $hoja->setCellValue('C7', 'APELLIDOS Y NOMBRES');
        $hoja->mergeCells('C7:C8');

        $estiloCabPrincipal = [
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PETROLEO]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDE_OSCURO]]],
        ];

        $hoja->getStyle('A7:A8')->applyFromArray($estiloCabPrincipal);
        $hoja->getStyle('B7:B8')->applyFromArray($estiloCabPrincipal);
        $hoja->getStyle('C7:C8')->applyFromArray($estiloCabPrincipal);

        // Cursos (2 columnas cada uno: CLF y PJT)
        $curColIdx = 4;
        foreach ($this->cursos as $c) {
            $col1 = Coordinate::stringFromColumnIndex($curColIdx);
            $col2 = Coordinate::stringFromColumnIndex($curColIdx + 1);

            // Cabecera superior con nombre del curso y créditos
            $hoja->setCellValue("{$col1}7", "{$c->nombre}\n({$c->creditos} crd)");
            $hoja->mergeCells("{$col1}7:{$col2}7");
            $hoja->getStyle("{$col1}7:{$col2}7")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8.5, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_AZUL_SUB]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDE_OSCURO]]],
            ]);

            // Subcabeceras CLF y PJT
            $hoja->setCellValue("{$col1}8", 'CLF');
            $hoja->setCellValue("{$col2}8", 'PJT');

            $estiloSub = [
                'font' => ['bold' => true, 'size' => 7.5, 'color' => ['rgb' => self::COLOR_NAVY]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_FONDO_SUB]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDE_OSCURO]]],
            ];
            $hoja->getStyle("{$col1}8")->applyFromArray($estiloSub);
            $hoja->getStyle("{$col2}8")->applyFromArray($estiloSub);

            $curColIdx += 2;
        }

        // Columnas finales de resumen
        $colPjt = Coordinate::stringFromColumnIndex($curColIdx);
        $colCrd = Coordinate::stringFromColumnIndex($curColIdx + 1);
        $colProm = Coordinate::stringFromColumnIndex($curColIdx + 2);
        $colObs = Coordinate::stringFromColumnIndex($curColIdx + 3);

        $hoja->setCellValue("{$colPjt}7", "PUNTAJE\nTOTAL");
        $hoja->mergeCells("{$colPjt}7:{$colPjt}8");
        $hoja->setCellValue("{$colCrd}7", "TOTAL\nCRÉDITOS");
        $hoja->mergeCells("{$colCrd}7:{$colCrd}8");
        $hoja->setCellValue("{$colProm}7", "PROMEDIO\nPONDERADO");
        $hoja->mergeCells("{$colProm}7:{$colProm}8");
        $hoja->setCellValue("{$colObs}7", "SITUACIÓN /\nOBSERVACIÓN");
        $hoja->mergeCells("{$colObs}7:{$colObs}8");

        $hoja->getStyle("{$colPjt}7:{$colObs}8")->applyFromArray($estiloCabPrincipal);

        // ═════════════════════════════════════════════════════════════════════
        // 4. FILAS DE DATOS DE ESTUDIANTES
        // ═════════════════════════════════════════════════════════════════════
        $filaActual = 9;

        foreach ($this->estudiantes as $idx => $item) {
            $hoja->getRowDimension($filaActual)->setRowHeight(21);
            $esZebra = ($idx % 2 === 1);
            $fondoFila = $esZebra ? self::COLOR_ZEBRA : 'FFFFFF';

            // N° Orden
            $hoja->setCellValue("A{$filaActual}", $idx + 1);
            $hoja->getStyle("A{$filaActual}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => self::COLOR_GRIS]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // N° Matrícula
            $hoja->setCellValueExplicit("B{$filaActual}", (string) ($item->estudiante?->numero_matricula ?: '—'), DataType::TYPE_STRING);
            $hoja->getStyle("B{$filaActual}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => self::COLOR_NAVY]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Apellidos y Nombres
            $nombreCompleto = mb_strtoupper(($item->estudiante?->apellidos ?? '') . ', ' . ($item->estudiante?->nombres ?? ''));
            $hoja->setCellValue("C{$filaActual}", $nombreCompleto);
            $hoja->getStyle("C{$filaActual}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            ]);

            // Calificaciones por curso
            $cIdx = 4;
            foreach ($this->cursos as $c) {
                $colC1 = Coordinate::stringFromColumnIndex($cIdx);
                $colC2 = Coordinate::stringFromColumnIndex($cIdx + 1);

                $calif = $item->notas->get($c->id);
                $nota = $calif?->calificacion;
                $pjt = $calif?->puntaje;

                // Calificación
                if ($nota !== null) {
                    $hoja->setCellValue("{$colC1}{$filaActual}", (int) $nota);
                    $esAprobado = ($nota >= 11);
                    $hoja->getStyle("{$colC1}{$filaActual}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => $esAprobado ? self::COLOR_NAVY : self::COLOR_ROJO]],
                        'fill' => $esAprobado ? [] : ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_ROJO_FONDO]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                } else {
                    $hoja->setCellValue("{$colC1}{$filaActual}", '—');
                    $hoja->getStyle("{$colC1}{$filaActual}")->applyFromArray([
                        'font' => ['color' => ['rgb' => self::COLOR_GRIS]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }

                // Puntaje
                $hoja->setCellValue("{$colC2}{$filaActual}", $pjt !== null ? (int) $pjt : '—');
                $hoja->getStyle("{$colC2}{$filaActual}")->applyFromArray([
                    'font' => ['size' => 8.5, 'color' => ['rgb' => self::COLOR_GRIS]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $cIdx += 2;
            }

            // Totales y Promedios
            $hoja->setCellValue("{$colPjt}{$filaActual}", $item->puntaje_total);
            $hoja->getStyle("{$colPjt}{$filaActual}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => self::COLOR_NAVY]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $hoja->setCellValue("{$colCrd}{$filaActual}", $item->total_creditos);
            $hoja->getStyle("{$colCrd}{$filaActual}")->applyFromArray([
                'font' => ['color' => ['rgb' => self::COLOR_GRIS]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Promedio Ponderado
            $prom = $item->promedio_ponderado;
            $hoja->setCellValue("{$colProm}{$filaActual}", $prom !== null ? number_format($prom, 2, '.', '') : '—');
            $promAprobado = $prom !== null && $prom >= 11;
            $hoja->getStyle("{$colProm}{$filaActual}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $promAprobado ? self::COLOR_VERDE : self::COLOR_ROJO]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $promAprobado ? self::COLOR_VERDE_FONDO : self::COLOR_ROJO_FONDO]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Situación / Observación
            // La observación del acta física (p. ej. «RETIRADA R.D. N° 86-03…») manda sobre la situación calculada
            $obs = $item->observacion
                ?: $item->situacion . ($item->cursos_desaprobados ? " ({$item->cursos_desaprobados})" : '');
            $hoja->setCellValue("{$colObs}{$filaActual}", $obs);
            $hoja->getStyle("{$colObs}{$filaActual}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8.5, 'color' => ['rgb' => $item->situacion === 'APROBADO' ? self::COLOR_VERDE : self::COLOR_ROJO]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);

            // Aplicar fondo zebra y bordes
            if ($fondoFila !== 'FFFFFF') {
                $hoja->getStyle("A{$filaActual}:C{$filaActual}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fondoFila);
                $hoja->getStyle("{$colPjt}{$filaActual}:{$colCrd}{$filaActual}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fondoFila);
            }
            $hoja->getStyle("A{$filaActual}:{$ultimaCol}{$filaActual}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDE);

            $filaActual++;
        }

        // ═════════════════════════════════════════════════════════════════════
        // 5. CUADRO ESTADÍSTICO (como el del acta física) Y FECHA DE VISACIÓN
        // ═════════════════════════════════════════════════════════════════════
        $filaActual += 1;
        $cabCuadro = ['CUADRO ESTADÍSTICO', 'H', '%', 'M', '%', 'T', '%'];
        foreach ($cabCuadro as $i => $texto) {
            $hoja->setCellValue(Coordinate::stringFromColumnIndex($i + 3) . $filaActual, $texto);
        }
        $rangoCuadro = 'C' . $filaActual . ':I' . ($filaActual + count($this->cuadro));
        $hoja->getStyle("C{$filaActual}:I{$filaActual}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 8.5, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PETROLEO]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        foreach ($this->cuadro as $nombre => $f) {
            $filaActual++;
            $hoja->fromArray([$nombre, $f['h'], $f['h_pct'], $f['m'], $f['m_pct'], $f['t'], $f['t_pct']], null, "C{$filaActual}", true);
            $hoja->getStyle("D{$filaActual}:I{$filaActual}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle("C{$filaActual}")->getFont()->setBold(true);
        }
        $hoja->getStyle($rangoCuadro)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDE);

        $filaActual += 2;
        $fechaVisacion = $this->acta->fecha_emision
            ? $this->acta->fecha_emision->locale('es')->translatedFormat('j \d\e F \d\e Y')
            : '____ de ______________ de ______';
        $hoja->setCellValue("A{$filaActual}", 'FECHA DE VISACIÓN: ' . $fechaVisacion);
        $hoja->mergeCells("A{$filaActual}:{$ultimaCol}{$filaActual}");
        $hoja->getStyle("A{$filaActual}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => self::COLOR_NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // ═════════════════════════════════════════════════════════════════════
        // 6. ANCHOS DE COLUMNAS (Legibilidad perfecta)
        // ═════════════════════════════════════════════════════════════════════
        $hoja->getColumnDimension('A')->setWidth(6);
        $hoja->getColumnDimension('B')->setWidth(15);
        $hoja->getColumnDimension('C')->setWidth(35);

        for ($ci = 4; $ci < $curColIdx; $ci += 2) {
            $hoja->getColumnDimension(Coordinate::stringFromColumnIndex($ci))->setWidth(6.5);
            $hoja->getColumnDimension(Coordinate::stringFromColumnIndex($ci + 1))->setWidth(6.5);
        }

        $hoja->getColumnDimension($colPjt)->setWidth(11);
        $hoja->getColumnDimension($colCrd)->setWidth(11);
        $hoja->getColumnDimension($colProm)->setWidth(13);
        $hoja->getColumnDimension($colObs)->setWidth(15);
    }
}
