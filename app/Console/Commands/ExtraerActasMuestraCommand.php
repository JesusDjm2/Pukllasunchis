<?php

namespace App\Console\Commands;

use App\Imports\RicardoPalma\ActaConsolidadaRpImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExtraerActasMuestraCommand extends Command
{
    protected $signature = 'rp:cargar-muestra-pdf';
    protected $description = 'Genera el archivo Excel a partir de los escaneos adjuntos (1996 Inicial y 2006 Secundaria Comunicación) y lo importa al sistema';

    public function handle(): int
    {
        $this->info("Iniciando generación de datos estructurados a partir de los escaneos...");

        $filas = [];

        // =========================================================================
        // ACTA 1: AÑO 1996 - II | EDUCACION INICIAL | SEMESTRE CUARTO (4)
        // Acta N° 002906 | Fecha: 24/01/1997 | Sección: UNICA | Turno: TARDE
        // =========================================================================
        $cursos1996 = [
            1 => ['nombre' => 'EDUCACION FISICA II', 'creditos' => 1],
            2 => ['nombre' => 'GEOG. DEL PERU Y MUNDO I', 'creditos' => 3],
            3 => ['nombre' => 'HIST. DEL PERU Y MUNDO I', 'creditos' => 3],
            4 => ['nombre' => 'TECNOLOGIA EDUCATIVA II', 'creditos' => 3],
            5 => ['nombre' => 'SOCIOLOG. DE LA EDUCACION', 'creditos' => 3],
            6 => ['nombre' => 'ESTIMULACION TEMPRANA II', 'creditos' => 3],
            7 => ['nombre' => 'APRESTAMIENTO I', 'creditos' => 4],
            8 => ['nombre' => 'TECNOLOG. APLIC. EDUC. INIC.', 'creditos' => 2],
        ];

        $alumnos1996 = [
            ['mat' => '351', 'ape' => 'ACURIO SALAZAR', 'nom' => 'Yovana', 'sex' => 'F', 'notas' => [15, 16, 14, 12, 14, 13, 14, 12]],
            ['mat' => '352', 'ape' => 'CARLOS GONZALES', 'nom' => 'Greta', 'sex' => 'F', 'notas' => [15, 18, 16, 13, 15, 16, 15, 14]],
            ['mat' => '353', 'ape' => 'CCOLQUE FARFAN', 'nom' => 'Natividad', 'sex' => 'F', 'notas' => [14, 15, 12, 12, 13, 13, 14, 12]],
            ['mat' => '354', 'ape' => 'CONDORI SALAZAR', 'nom' => 'Roxana', 'sex' => 'F', 'notas' => [14, 12, 12, 12, 12, 12, 12, 11]],
            ['mat' => '355', 'ape' => 'CURASCO CONDE', 'nom' => 'German', 'sex' => 'M', 'notas' => [16, 16, 14, 12, 14, 15, 15, 13]],
            ['mat' => '356', 'ape' => 'FLORES VILLACORTA', 'nom' => 'Carmen Janet', 'sex' => 'F', 'notas' => [14, 15, 12, 11, 12, 12, 13, 11]],
            ['mat' => '357', 'ape' => 'GAMARRA SARAYA', 'nom' => 'Mónica', 'sex' => 'F', 'notas' => [15, 16, 12, 12, 12, 11, 13, 12]],
            ['mat' => '358', 'ape' => 'GOMEZ ROMERO', 'nom' => 'Tula Mirtha', 'sex' => 'F', 'notas' => [13, 16, 14, 15, 12, 16, 15, 14]],
            ['mat' => '359', 'ape' => 'HUAMANI JALIRE', 'nom' => 'Luzmila', 'sex' => 'F', 'notas' => [15, 15, 12, 12, 12, 12, 12, 13]],
            ['mat' => '360', 'ape' => 'HUAÑEC LEON', 'nom' => 'Semiramis', 'sex' => 'F', 'notas' => [15, 14, 12, 13, 12, 13, 13, 12]],
            ['mat' => '361', 'ape' => 'OBLITAS VALENCIA', 'nom' => 'Herlinda', 'sex' => 'F', 'notas' => [15, 14, 12, 12, 13, 12, 13, 12]],
            ['mat' => '362', 'ape' => 'PRADA CABEZAS', 'nom' => 'Maria Julieta', 'sex' => 'F', 'notas' => [16, 14, 12, 12, 14, 12, 12, 12]],
            ['mat' => '363', 'ape' => 'PUMA URIOSTE', 'nom' => 'Rosa Dolores', 'sex' => 'F', 'notas' => [15, 15, 12, 11, 13, 13, 13, 12]],
            ['mat' => '364', 'ape' => 'TACURI AYTE', 'nom' => 'Maria Elena', 'sex' => 'F', 'notas' => [16, 15, 12, 12, 12, 10, 13, 12]],
            ['mat' => '365', 'ape' => 'VERA GUZMAN', 'nom' => 'Hilda', 'sex' => 'F', 'notas' => [14, 13, 12, 12, 12, 11, 12, 11]],
        ];

        foreach ($alumnos1996 as $al) {
            foreach ($al['notas'] as $i => $nota) {
                $cur = $cursos1996[$i + 1];
                $filas[] = [
                    'PERIODO' => '1996-II',
                    'PROGRAMA' => 'INICIAL',
                    'SEMESTRE' => 4,
                    'NRO_MATRICULA' => $al['mat'],
                    'DNI' => null,
                    'APELLIDOS' => $al['ape'],
                    'NOMBRES' => $al['nom'],
                    'SEXO' => $al['sex'],
                    'CURSO' => $cur['nombre'],
                    'CREDITOS' => $cur['creditos'],
                    'NOTA' => $nota,
                    'OBSERVACION' => $nota >= 11 ? 'APROBADO' : 'DESAPROBADO',
                    'NRO_ACTA' => '002906',
                    'TURNO' => 'TARDE',
                    'SECCION' => 'UNICA',
                ];
            }
        }

        // =========================================================================
        // ACTA 2: AÑO 2006 - I | SECUNDARIA COMUNICACION | SEMESTRE I (1)
        // Fecha: 19/08/2006 | Turno: TARDE | Ciclo: I
        // =========================================================================
        $cursos2006 = [
            1 => ['nombre' => 'COMUNICACIÓN INTEGRAL I', 'creditos' => 10],
            2 => ['nombre' => 'INVESTIGACIÓN I', 'creditos' => 2],
            3 => ['nombre' => 'PSICOLOGÍA I', 'creditos' => 3],
            4 => ['nombre' => 'PRÁCTICA I', 'creditos' => 2],
            5 => ['nombre' => 'SOCIEDAD I', 'creditos' => 2],
            6 => ['nombre' => 'EDUCACIÓN RELIGIOSA I', 'creditos' => 2],
            7 => ['nombre' => 'MATEMÁTICA I', 'creditos' => 3],
        ];

        $alumnos2006 = [
            ['mat' => '01-06', 'ape' => 'ABRIL ESTRADA', 'nom' => 'Medallat', 'sex' => 'F', 'notas' => [14, 12, 14, 13, 13, 12, 13], 'obs' => 'APROBADO'],
            ['mat' => '02-06', 'ape' => 'ANCCO ZEVALLOS', 'nom' => 'Joel James', 'sex' => 'M', 'notas' => [15, 16, 15, 13, 15, 16, 16], 'obs' => 'APROBADO'],
            ['mat' => '03-06', 'ape' => 'CARHUALLAY ASTO', 'nom' => 'Dionicio Gilmer', 'sex' => 'M', 'notas' => [13, 17, 15, 15, 15, 15, 15], 'obs' => 'APROBADO'],
            ['mat' => '04-06', 'ape' => 'CASAS PUMA', 'nom' => 'Hector', 'sex' => 'M', 'notas' => [13, 13, 14, 16, 14, 14, 12], 'obs' => 'APROBADO'],
            ['mat' => '05-06', 'ape' => 'CONDORI FLORES', 'nom' => 'Juvenal', 'sex' => 'M', 'notas' => [14, 13, 14, 15, 15, 14, 13], 'obs' => 'APROBADO'],
            ['mat' => '07-06', 'ape' => 'DAVALOS HUAYLLAS', 'nom' => 'Mariela Gehovanni', 'sex' => 'F', 'notas' => [16, 16, 16, 15, 17, 16, 19], 'obs' => 'APROBADO'],
            ['mat' => '08-06', 'ape' => 'FLORES CURI', 'nom' => 'Rosa', 'sex' => 'F', 'notas' => [], 'obs' => 'RETIRADO'],
            ['mat' => '09-06', 'ape' => 'LAIZA MAXIMILIANO', 'nom' => 'Mártires', 'sex' => 'M', 'notas' => [14, 15, 15, 14, 14, 14, 15], 'obs' => 'APROBADO'],
            ['mat' => '10-06', 'ape' => 'MOLINA RODRIGUEZ', 'nom' => 'Chanel', 'sex' => 'F', 'notas' => [12, 15, 16, 11, 12, 12, 13], 'obs' => 'APROBADO'],
            ['mat' => '11-06', 'ape' => 'MONGE QUISPE', 'nom' => 'Estanislao', 'sex' => 'M', 'notas' => [], 'obs' => 'RETIRADO'],
            ['mat' => '12-06', 'ape' => 'OLIVERA BERRIO', 'nom' => 'Orlando', 'sex' => 'M', 'notas' => [], 'obs' => 'RETIRADO'],
            ['mat' => '13-06', 'ape' => 'PEÑA QUISPE', 'nom' => 'Bernardina', 'sex' => 'F', 'notas' => [], 'obs' => 'RETIRADO'],
            ['mat' => '14-06', 'ape' => 'PEREZ CARAZAS', 'nom' => 'Jonathan Edgar', 'sex' => 'M', 'notas' => [14, 15, 13, 14, 13, 12, 13], 'obs' => 'APROBADO'],
            ['mat' => '15-06', 'ape' => 'PUMA MARIN', 'nom' => 'Pablo', 'sex' => 'M', 'notas' => [], 'obs' => 'RETIRADO'],
            ['mat' => '16-06', 'ape' => 'SULLANI BOLIVAR', 'nom' => 'Guillermo', 'sex' => 'M', 'notas' => [13, 13, 16, 12, 12, 14, 15], 'obs' => 'APROBADO'],
            ['mat' => '17-06', 'ape' => 'TACCA SUYO', 'nom' => 'Daniel Angel', 'sex' => 'M', 'notas' => [15, 13, 12, 14, 13, 13, 14], 'obs' => 'APROBADO'],
            ['mat' => '18-06', 'ape' => 'UMERES ALATRISTA', 'nom' => 'Yurina', 'sex' => 'F', 'notas' => [14, 16, 14, 12, 12, 13, 15], 'obs' => 'APROBADO'],
            ['mat' => '19-06', 'ape' => 'VILLANUEVA TINTA', 'nom' => 'Miriam Elva', 'sex' => 'F', 'notas' => [16, 14, 14, 15, 16, 15, 13], 'obs' => 'APROBADO'],
            ['mat' => '20-06', 'ape' => 'ZUÑIGA CAYLLAHUA', 'nom' => 'Jeferson', 'sex' => 'M', 'notas' => [13, 12, 13, 15, 13, 14, 14], 'obs' => 'APROBADO'],
        ];

        foreach ($alumnos2006 as $al) {
            if ($al['obs'] === 'RETIRADO' || empty($al['notas'])) {
                // Registrar para el primer curso con observación RETIRADO para que figure en el acta
                $cur = $cursos2006[1];
                $filas[] = [
                    'PERIODO' => '2006-I',
                    'PROGRAMA' => 'SEC-COM',
                    'SEMESTRE' => 1,
                    'NRO_MATRICULA' => $al['mat'],
                    'DNI' => null,
                    'APELLIDOS' => $al['ape'],
                    'NOMBRES' => $al['nom'],
                    'SEXO' => $al['sex'],
                    'CURSO' => $cur['nombre'],
                    'CREDITOS' => $cur['creditos'],
                    'NOTA' => null,
                    'OBSERVACION' => 'RETIRADO',
                    'NRO_ACTA' => 'S/N',
                    'TURNO' => 'TARDE',
                    'SECCION' => 'UNICA',
                ];
            } else {
                foreach ($al['notas'] as $i => $nota) {
                    $cur = $cursos2006[$i + 1];
                    $filas[] = [
                        'PERIODO' => '2006-I',
                        'PROGRAMA' => 'SEC-COM',
                        'SEMESTRE' => 1,
                        'NRO_MATRICULA' => $al['mat'],
                        'DNI' => null,
                        'APELLIDOS' => $al['ape'],
                        'NOMBRES' => $al['nom'],
                        'SEXO' => $al['sex'],
                        'CURSO' => $cur['nombre'],
                        'CREDITOS' => $cur['creditos'],
                        'NOTA' => $nota,
                        'OBSERVACION' => $al['obs'],
                        'NRO_ACTA' => 'S/N',
                        'TURNO' => 'TARDE',
                        'SECCION' => 'UNICA',
                    ];
                }
            }
        }

        $this->info("Total de registros generados: " . count($filas));

        // 1. Guardar archivo Excel en storage/app/
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Actas Consolidadas');

        $headers = array_keys($filas[0]);
        $sheet->fromArray([$headers], null, 'A1');

        $dataRows = array_map('array_values', $filas);
        $sheet->fromArray($dataRows, null, 'A2');

        $rutaExcel = storage_path('app/actas_ricardo_palma_muestra_extraida.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($rutaExcel);

        $this->info("Archivo Excel guardado en: {$rutaExcel}");

        // 2. Importar automáticamente a la base de datos de Puklla2026
        $this->info("Importando a las tablas rp_* ...");
        $importador = new ActaConsolidadaRpImport('actas_ricardo_palma_muestra_extraida.xlsx', null);
        Excel::import($importador, $rutaExcel);

        $resultado = $importador->getResultado();
        $this->info("¡Importación completada!");
        $this->info("- Registros procesados con éxito: {$resultado['exitosos']}");
        $this->info("- Registros fallidos: {$resultado['fallidos']}");

        return self::SUCCESS;
    }
}
