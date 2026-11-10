<?php

namespace App\Exports\RicardoPalma;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Plantilla con la misma forma del «Acta consolidada de evaluación del rendimiento académico»
 * escaneada: encabezado, 15 asignaturas en columnas y un alumno por fila, para transcribir
 * mirando el papel. Puntaje total, créditos y promedio se calculan solos (el promedio se TRUNCA
 * a 2 decimales, como en el acta) para cotejarlos con los del escaneo.
 *
 * La lee ActaFisicaRpImport, que ubica los datos por sus rótulos: si se cambian los textos de
 * AÑO ACADÉMICO, SEMESTRE, FECHA, ESPECIALIDAD, TURNO, SECCIÓN, N° DE ACTA, CASILLAS o ASIGNATURAS,
 * hay que cambiarlos también allí.
 */
class PlantillaActaFisicaRp
{
    public const ASIGNATURAS = 15;
    public const FILAS_ALUMNOS = 50;

    // Filas de la tabla
    public const FILA_CAB = 8;      // «ASIGNATURAS»
    public const FILA_CURSOS = 9;   // nombres de las asignaturas (texto vertical)
    public const FILA_CREDITOS = 10;
    public const FILA_NUMEROS = 11; // 1 … 15
    public const FILA_DATOS = 12;

    public const MODO_PUNTAJE = 'PUNTAJE (nota × créditos)';
    public const MODO_NOTA = 'NOTA (0 a 20)';

    private const COLOR_ENTRADA = 'FFF8E1';  // casillas que se llenan
    private const COLOR_CALCULO = 'EEF2F7';  // calculadas, no se tocan
    private const COLOR_EXTRA = 'F1F1F1';    // datos que no están en el acta
    private const COLOR_BORDE = '5A5A5A';

    public static function generar(): Spreadsheet
    {
        $libro = new Spreadsheet();
        $libro->getProperties()->setTitle('Plantilla acta consolidada - Ricardo Palma');

        $hoja = $libro->getActiveSheet();
        $hoja->setTitle('ACTA');
        self::armarActa($hoja);

        $instrucciones = $libro->createSheet();
        $instrucciones->setTitle('INSTRUCCIONES');
        self::armarInstrucciones($instrucciones);

        $libro->setActiveSheetIndex(0);

        return $libro;
    }

    /** Letra de la columna de la asignatura n (1 → D … 15 → R). */
    public static function colAsignatura(int $n): string
    {
        return Coordinate::stringFromColumnIndex(3 + $n);
    }

    private static function armarActa(Worksheet $h): void
    {
        $primera = self::colAsignatura(1);                       // D
        $ultima = self::colAsignatura(self::ASIGNATURAS);         // R
        $cPuntaje = Coordinate::stringFromColumnIndex(4 + self::ASIGNATURAS);   // S
        $cCreditos = Coordinate::stringFromColumnIndex(5 + self::ASIGNATURAS);  // T
        $cPromedio = Coordinate::stringFromColumnIndex(6 + self::ASIGNATURAS);  // U
        $cObs = Coordinate::stringFromColumnIndex(7 + self::ASIGNATURAS);       // V
        $cSexo = Coordinate::stringFromColumnIndex(8 + self::ASIGNATURAS);      // W
        $cDni = Coordinate::stringFromColumnIndex(9 + self::ASIGNATURAS);       // X
        $filaFin = self::FILA_DATOS + self::FILAS_ALUMNOS - 1;

        $h->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(9);
        $h->setShowGridlines(false);

        // Anchos
        $h->getColumnDimension('A')->setWidth(5);
        $h->getColumnDimension('B')->setWidth(9);
        $h->getColumnDimension('C')->setWidth(40);
        for ($n = 1; $n <= self::ASIGNATURAS; $n++) {
            $h->getColumnDimension(self::colAsignatura($n))->setWidth(6.5);
        }
        $h->getColumnDimension($cPuntaje)->setWidth(8);
        $h->getColumnDimension($cCreditos)->setWidth(8);
        $h->getColumnDimension($cPromedio)->setWidth(10);
        $h->getColumnDimension($cObs)->setWidth(32);
        $h->getColumnDimension($cSexo)->setWidth(7);
        $h->getColumnDimension($cDni)->setWidth(12);

        // ── Título ──
        $h->mergeCells("A1:{$cObs}1");
        $h->setCellValue('A1', 'ACTA CONSOLIDADA DE EVALUACIÓN DEL RENDIMIENTO ACADÉMICO');
        $h->mergeCells("A2:{$cObs}2");
        $h->setCellValue('A2', 'FORMACIÓN PROFESIONAL PEDAGÓGICA');
        $h->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $h->getStyle('A2')->getFont()->setBold(true)->setSize(10);
        $h->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $h->getRowDimension(1)->setRowHeight(22);

        // ── Encabezado (rótulo + casilla) ──
        $h->setCellValue('C4', 'INSTITUCIÓN ACADÉMICA:');
        $h->mergeCells("{$primera}4:{$cObs}4");
        $h->setCellValue("{$primera}4", 'INSTITUTO SUPERIOR PEDAGÓGICO PRIVADO "RICARDO PALMA"');

        $campos = [
            // [fila, celda rótulo (rango), texto, rango casilla, ayuda]
            [5, 'C5', 'AÑO ACADÉMICO:', "{$primera}5:F5", 'Ej.: 1997-II'],
            [5, 'G5:H5', 'SEMESTRE:', 'I5:K5', 'Ej.: DÉCIMO, X o 10'],
            [5, 'L5:M5', 'FECHA:', 'N5:P5', 'Fecha que figura en el acta (ej.: 16/01/1998)'],
            [5, 'Q5:S5', 'CASILLAS CON:', "{$cCreditos}5:{$cObs}5", null],
            [6, 'C6', 'ESPECIALIDAD (programa):', "{$primera}6:F6", 'INICIAL, PRIMARIA, SEC-COM o SEC-MAT'],
            [6, 'G6:H6', 'TURNO:', 'I6:K6', null],
            [6, 'L6:M6', 'SECCIÓN:', 'N6:P6', null],
            [6, 'Q6:S6', 'N° DE ACTA:', "{$cCreditos}6:{$cObs}6", 'Opcional: número del acta física'],
        ];
        foreach ($campos as [$fila, $rotulo, $texto, $casilla, $ayuda]) {
            if (str_contains($rotulo, ':')) {
                $h->mergeCells($rotulo);
            }
            $celdaRotulo = explode(':', $rotulo)[0];
            $h->setCellValue($celdaRotulo, $texto);
            $h->getStyle($rotulo)->getFont()->setBold(true);
            $h->getStyle($rotulo)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $h->mergeCells($casilla);
            $celda = explode(':', $casilla)[0];
            $estilo = $h->getStyle($casilla);
            $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ENTRADA);
            $estilo->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDE);
            $estilo->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $estilo->getFont()->setName('Courier New')->setSize(10);
            if ($ayuda) {
                $h->getComment($celda)->getText()->createTextRun($ayuda);
            }
        }
        $h->getStyle('N5')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $h->getStyle("{$primera}4")->getFont()->setName('Courier New')->setSize(10);
        $h->getStyle('C4')->getFont()->setBold(true);
        $h->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Valores por defecto
        $h->setCellValue("{$cCreditos}5", self::MODO_PUNTAJE);
        $h->setCellValue('I6', 'TARDE');
        $h->setCellValue('N6', 'UNICA');

        self::lista($h, "{$primera}6", '"INICIAL,PRIMARIA,SEC-COM,SEC-MAT"');
        self::lista($h, 'I6', '"MAÑANA,TARDE,NOCHE"');
        self::lista($h, "{$cCreditos}5", '"'.self::MODO_PUNTAJE.','.self::MODO_NOTA.'"');
        $h->getComment("{$cCreditos}5")->getText()->createTextRun(
            'PUNTAJE: escribe lo que dice el acta (nota × créditos, ej. 196 = 14 × 14). '
            .'NOTA: escribe la nota de 0 a 20.'
        );

        // ── Cabecera de la tabla ──
        $fCab = self::FILA_CAB;
        $fCur = self::FILA_CURSOS;
        $fCre = self::FILA_CREDITOS;
        $fNum = self::FILA_NUMEROS;

        foreach ([['A', 'N° de Orden'], ['B', 'N° de Matrícula']] as [$col, $texto]) {
            $h->mergeCells("{$col}{$fCab}:{$col}{$fNum}");
            $h->setCellValue("{$col}{$fCab}", $texto);
        }
        $h->mergeCells("C{$fCab}:C{$fCur}");
        $h->setCellValue("C{$fCab}", 'APELLIDOS Y NOMBRES (Riguroso Orden Alfabético)');
        $h->setCellValue("C{$fCre}", 'Créditos de cada asignatura →');
        $h->setCellValue("C{$fNum}", 'Escribe: APELLIDOS,Nombres');
        $h->getStyle("C{$fCre}:C{$fNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $h->getStyle("C{$fCre}:C{$fNum}")->getFont()->setItalic(true)->setBold(false)->setSize(8);

        $h->mergeCells("{$primera}{$fCab}:{$ultima}{$fCab}");
        $h->setCellValue("{$primera}{$fCab}", 'ASIGNATURAS');

        for ($n = 1; $n <= self::ASIGNATURAS; $n++) {
            $col = self::colAsignatura($n);
            $h->setCellValue("{$col}{$fNum}", $n);
        }

        $verticales = [
            [$cPuntaje, 'PUNTAJE TOTAL'],
            [$cCreditos, 'N° DE CRÉDITOS MATRICULADOS'],
            [$cPromedio, 'PROMEDIO PONDERADO SEMESTRAL'],
        ];
        foreach ($verticales as [$col, $texto]) {
            $h->mergeCells("{$col}{$fCab}:{$col}{$fNum}");
            $h->setCellValue("{$col}{$fCab}", $texto);
            $h->getStyle("{$col}{$fCab}")->getAlignment()->setTextRotation(90);
        }
        $h->mergeCells("{$cObs}{$fCab}:{$cObs}{$fNum}");
        $h->setCellValue("{$cObs}{$fCab}", 'OBSERVACIONES');

        // Columnas extra (no figuran en el acta, pero sirven para el cuadro estadístico)
        $h->mergeCells("{$cSexo}7:{$cDni}7");
        $h->setCellValue("{$cSexo}7", 'No figuran en el acta');
        $h->getStyle("{$cSexo}7")->getFont()->setItalic(true)->setSize(7);
        $h->getStyle("{$cSexo}7")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach ([[$cSexo, 'SEXO (M/F)'], [$cDni, 'DNI']] as [$col, $texto]) {
            $h->mergeCells("{$col}{$fCab}:{$col}{$fNum}");
            $h->setCellValue("{$col}{$fCab}", $texto);
        }

        $cabecera = $h->getStyle("A{$fCab}:{$cDni}{$fNum}");
        $cabecera->getFont()->setBold(true);
        $cabecera->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        // Nombres y créditos de las asignaturas: se llenan
        $cursos = $h->getStyle("{$primera}{$fCur}:{$ultima}{$fCur}");
        $cursos->getAlignment()->setTextRotation(90)->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_BOTTOM)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $cursos->getFont()->setBold(false)->setName('Courier New')->setSize(8);
        foreach (["{$primera}{$fCur}:{$ultima}{$fCre}"] as $rango) {
            $h->getStyle($rango)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_ENTRADA);
        }
        $h->getStyle("{$primera}{$fCre}:{$ultima}{$fCre}")->getFont()->setBold(false)->setSize(9);
        $h->getComment("{$primera}{$fCur}")->getText()->createTextRun(
            'Escribe el nombre de cada asignatura tal como figura en el acta. Deja vacías las columnas que no se usan.'
        );
        $h->getComment("{$primera}{$fCre}")->getText()->createTextRun(
            'Créditos de la asignatura (no figuran en el acta; el puntaje dividido entre los créditos da la nota).'
        );
        $h->getRowDimension($fCur)->setRowHeight(120);
        $h->getRowDimension($fCab)->setRowHeight(16);
        $h->getRowDimension($fCre)->setRowHeight(16);
        $h->getRowDimension($fNum)->setRowHeight(16);

        $h->getStyle("{$cSexo}{$fCab}:{$cDni}{$filaFin}")->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB(self::COLOR_EXTRA);

        // ── Filas de alumnos ──
        $rangoCreditos = '$'.$primera.'$'.$fCre.':$'.$ultima.'$'.$fCre;
        $modo = '$'.$cCreditos.'$5';
        for ($i = 0; $i < self::FILAS_ALUMNOS; $i++) {
            $f = self::FILA_DATOS + $i;
            $notas = "{$primera}{$f}:{$ultima}{$f}";
            $h->setCellValue("A{$f}", $i + 1);
            $h->setCellValue("{$cCreditos}{$f}", "=IF(COUNT({$notas})=0,\"\",SUMPRODUCT(--({$notas}<>\"\"),{$rangoCreditos}))");
            $h->setCellValue("{$cPuntaje}{$f}", "=IF(COUNT({$notas})=0,\"\",IF(LEFT({$modo},1)=\"P\",SUM({$notas}),SUMPRODUCT({$notas},{$rangoCreditos})))");
            $h->setCellValue("{$cPromedio}{$f}", "=IF(OR({$cPuntaje}{$f}=\"\",N({$cCreditos}{$f})=0),\"\",TRUNC({$cPuntaje}{$f}/{$cCreditos}{$f},2))");
            $h->getRowDimension($f)->setRowHeight(17);
        }

        $datos = "A".self::FILA_DATOS.":{$cDni}{$filaFin}";
        $h->getStyle($datos)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $h->getStyle("B".self::FILA_DATOS.":C{$filaFin}")->getFont()->setName('Courier New')->setSize(9);
        $h->getStyle("C".self::FILA_DATOS.":C{$filaFin}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $h->getStyle("{$cObs}".self::FILA_DATOS.":{$cObs}{$filaFin}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $h->getStyle("B".self::FILA_DATOS.":B{$filaFin}")->getNumberFormat()->setFormatCode('@');
        $h->getStyle("{$cDni}".self::FILA_DATOS.":{$cDni}{$filaFin}")->getNumberFormat()->setFormatCode('@');
        $h->getStyle("{$cPromedio}".self::FILA_DATOS.":{$cPromedio}{$filaFin}")->getNumberFormat()->setFormatCode('0.00');
        $h->getStyle("{$cPuntaje}".self::FILA_DATOS.":{$cPromedio}{$filaFin}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLOR_CALCULO);
        $h->getStyle("{$cPuntaje}".self::FILA_DATOS.":{$cPromedio}{$filaFin}")->getFont()->getColor()->setRGB('1E3A8A');

        // Bordes de toda la tabla
        $h->getStyle("A{$fCab}:{$cDni}{$filaFin}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::COLOR_BORDE);
        $h->getStyle("A{$fCab}:{$cObs}{$filaFin}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        // Sexo M/F
        self::lista($h, "{$cSexo}".self::FILA_DATOS.":{$cSexo}{$filaFin}", '"M,F"');

        // ── Formato condicional ──
        $p = $primera;
        $fd = self::FILA_DATOS;
        // Nota desaprobatoria en rojo (como en el acta)
        $rojo = new Conditional();
        $rojo->setConditionType(Conditional::CONDITION_EXPRESSION)
            ->addCondition("AND(ISNUMBER({$p}{$fd}),IF(LEFT({$modo},1)=\"P\",{$p}{$fd}/MAX(1,{$p}\${$fCre}),{$p}{$fd})<11)");
        $rojo->getStyle()->getFont()->setBold(true)->getColor()->setRGB('C62828');
        // Puntaje que no se divide exacto entre los créditos: probable error al transcribir
        $naranja = new Conditional();
        $naranja->setConditionType(Conditional::CONDITION_EXPRESSION)
            ->addCondition("AND(LEFT({$modo},1)=\"P\",ISNUMBER({$p}{$fd}),N({$p}\${$fCre})>0,MOD({$p}{$fd},MAX(1,{$p}\${$fCre}))<>0)");
        $naranja->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setRGB('FFCC80');
        // Puntaje mayor que 20 × créditos o nota mayor que 20
        $fuera = new Conditional();
        $fuera->setConditionType(Conditional::CONDITION_EXPRESSION)
            ->addCondition("AND(ISNUMBER({$p}{$fd}),{$p}{$fd}>IF(LEFT({$modo},1)=\"P\",20*MAX(1,{$p}\${$fCre}),20))");
        $fuera->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setRGB('EF9A9A');
        $h->getStyle("{$primera}{$fd}:{$ultima}{$filaFin}")->setConditionalStyles([$fuera, $naranja, $rojo]);

        $promRojo = new Conditional();
        $promRojo->setConditionType(Conditional::CONDITION_EXPRESSION)
            ->addCondition("AND(ISNUMBER({$cPromedio}{$fd}),{$cPromedio}{$fd}<11)");
        $promRojo->getStyle()->getFont()->setBold(true)->getColor()->setRGB('C62828');
        $h->getStyle("{$cPromedio}{$fd}:{$cPromedio}{$filaFin}")->setConditionalStyles([$promRojo]);

        // ── Vista e impresión ──
        $h->freezePane($primera.self::FILA_DATOS);
        $h->setSelectedCell("{$primera}5");
        $h->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(0);
        $h->getPageSetup()->setPrintArea("A1:{$cDni}{$filaFin}");
        $h->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($fCab, $fNum);
        $h->getPageMargins()->setLeft(0.3)->setRight(0.3)->setTop(0.4)->setBottom(0.4);
    }

    private static function armarInstrucciones(Worksheet $h): void
    {
        $h->setShowGridlines(false);
        $h->getColumnDimension('A')->setWidth(4);
        $h->getColumnDimension('B')->setWidth(110);

        $lineas = [
            ['Cómo llenar la plantilla del acta consolidada', 'titulo'],
            ['La hoja ACTA tiene la misma forma que el acta física: llénala mirando el escaneo, casilla por casilla.', null],
            ['', null],
            ['1. Encabezado', 'sub'],
            ['AÑO ACADÉMICO: como en el acta (1997-II). SEMESTRE: DÉCIMO, X o 10. FECHA: la fecha que figura en el acta.', null],
            ['ESPECIALIDAD: elige INICIAL, PRIMARIA, SEC-COM o SEC-MAT. TURNO y SECCIÓN: por defecto TARDE y UNICA.', null],
            ['N° DE ACTA es opcional. Si vuelves a cargar la misma acta (mismo año, especialidad, semestre, turno y sección), se actualiza.', null],
            ['', null],
            ['2. Asignaturas', 'sub'],
            ['Escribe el nombre de cada asignatura en su columna (1 a 15), en el mismo orden del acta. Deja vacías las que no se usan.', null],
            ['Debajo del nombre, escribe sus CRÉDITOS (no figuran en el acta, pero hacen falta para calcular la nota y el promedio).', null],
            ['', null],
            ['3. Puntaje o nota', 'sub'],
            ['«CASILLAS CON» indica qué escribes en las casillas de cada alumno:', null],
            ['   • PUNTAJE (nota × créditos): lo que trae el acta física (ej.: 196 en una asignatura de 14 créditos = nota 14). Es lo normal.', null],
            ['   • NOTA (0 a 20): para actas que ya traen la nota.', null],
            ['', null],
            ['4. Alumnos', 'sub'],
            ['Una fila por alumno, en el mismo orden del acta. APELLIDOS Y NOMBRES separados por una coma: ACOSTUPA SANTI,Sorayda.', null],
            ['Si el alumno no tiene nota en una asignatura, deja la casilla vacía. Si el acta dice «00», escribe 0.', null],
            ['OBSERVACIONES: copia lo que dice el acta. Si empieza con RETIRADO/A o LICENCIA, el alumno cuenta así aunque tenga notas.', null],
            ['SEXO y DNI no figuran en el acta; son opcionales (el sexo sirve para el cuadro estadístico).', null],
            ['', null],
            ['5. Cotejar con el escaneo', 'sub'],
            ['PUNTAJE TOTAL, N° DE CRÉDITOS y PROMEDIO se calculan solos (el promedio se trunca a 2 decimales, como en el acta).', null],
            ['Si no coinciden con los del escaneo, hay una casilla mal transcrita en esa fila.', null],
            ['Casilla en naranja: el puntaje no se divide exacto entre los créditos de la asignatura (revisa el puntaje o los créditos).', null],
            ['Casilla en rojo claro: el puntaje es mayor que 20 × créditos (o la nota mayor que 20). Texto en rojo: desaprobado, como en el acta.', null],
            ['', null],
            ['Varias actas en un archivo', 'sub'],
            ['Puedes copiar la hoja ACTA (clic derecho en la pestaña › Mover o copiar › Crear una copia) y llenar un acta en cada hoja.', null],
            ['Para un acta con más de 50 alumnos, usa una segunda copia de la hoja con el mismo encabezado.', null],
        ];

        $f = 2;
        foreach ($lineas as [$texto, $tipo]) {
            $h->setCellValue("B{$f}", $texto);
            if ($tipo === 'titulo') {
                $h->getStyle("B{$f}")->getFont()->setBold(true)->setSize(14);
                $h->getRowDimension($f)->setRowHeight(22);
            } elseif ($tipo === 'sub') {
                $h->getStyle("B{$f}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('1E3A5F');
            }
            $h->getStyle("B{$f}")->getAlignment()->setWrapText(true);
            $f++;
        }
    }

    private static function lista(Worksheet $h, string $rango, string $opciones): void
    {
        $v = new DataValidation();
        $v->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Valor no válido')
            ->setError('Elige una opción de la lista.')
            ->setFormula1($opciones);
        $h->setDataValidation($rango, $v);
    }
}
