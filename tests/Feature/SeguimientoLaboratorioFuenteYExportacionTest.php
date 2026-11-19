<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SeguimientoEgresado\SeguimientoAnaliticaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeguimientoLaboratorioFuenteYExportacionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'coordinacion-empleabilidad']);

        $this->adminUser = User::firstOrCreate(
            ['email' => 'pavelchaparro.iesp@pukllavirtual.edu.pe'],
            [
                'name' => 'Pavel Chaparro',
                'apellidos' => 'Administrador',
                'condicion' => 'Docente',
                'dni' => '00000001',
                'password' => bcrypt('password123'),
            ]
        );

        if (!$this->adminUser->hasRole('admin')) {
            $this->adminUser->assignRole('admin');
        }
    }

    /**
     * Prueba 1: Carga de laboratorio con filtro fuente de datos encuesta_depurada_2026
     * Verifica 113 vínculos confirmados, 112 personas únicas, explicación metodológica y denominadores.
     */
    public function test_01_laboratorio_encuesta_depurada_2026_muestra_113_vinculos_y_112_personas(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('seguimiento.laboratorio', [
                'fuente_datos' => 'encuesta_depurada_2026',
                'grupo_formacion' => 'TODOS',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('universos');
        $response->assertViewHas('longitudinalInicial');

        $universos = $response->viewData('universos');
        $this->assertEquals('encuesta_depurada_2026', $universos['fuente_datos']);
        $this->assertEquals(113, $universos['registros_vinculados']);
        $this->assertEquals(112, $universos['personas_unicas']);
        $this->assertEquals(111, $universos['con_info_laboral']);
        $this->assertNotEmpty($universos['diferencia_explicacion']);
        $this->assertStringContainsString('187', $universos['diferencia_explicacion']);

        // Denominadores explícitos
        $this->assertArrayHasKey('denominadores', $universos);
        $this->assertEquals(112, $universos['denominadores']['personas_unicas']);
        $this->assertEquals(113, $universos['denominadores']['vinculos_conciliados']);
        $this->assertEquals(111, $universos['denominadores']['docencia_directa']);
    }

    /**
     * Prueba 2: Comparación FID vs PPD para la encuesta depurada 2026.
     * En la carga existente de la encuesta 2026, todos los vínculos confirmados corresponden a FID.
     * PPD debe arrojar 0 registros y 0 personas únicas.
     */
    public function test_02_comparacion_fid_y_ppd_en_encuesta_depurada_2026(): void
    {
        $analiticaSvc = app(SeguimientoAnaliticaService::class);

        // FID
        $univFid = $analiticaSvc->calcularUniversos([
            'fuente_datos' => 'encuesta_depurada_2026',
            'grupo_formacion' => 'FID',
        ]);
        $this->assertEquals(113, $univFid['registros_vinculados']);
        $this->assertEquals(112, $univFid['personas_unicas']);

        $longFid = $analiticaSvc->generarAnalisisLongitudinal([
            'fuente_datos' => 'encuesta_depurada_2026',
            'grupo_formacion' => 'FID',
        ]);
        $this->assertNotEmpty($longFid['cohortes']);
        $this->assertStringContainsString('112', $longFid['totales']['padron_texto']);
        $this->assertStringContainsString('113', $longFid['totales']['padron_texto']);

        // PPD
        $univPpd = $analiticaSvc->calcularUniversos([
            'fuente_datos' => 'encuesta_depurada_2026',
            'grupo_formacion' => 'PPD',
        ]);
        $this->assertEquals(0, $univPpd['registros_vinculados']);
        $this->assertEquals(0, $univPpd['personas_unicas']);

        $longPpd = $analiticaSvc->generarAnalisisLongitudinal([
            'fuente_datos' => 'encuesta_depurada_2026',
            'grupo_formacion' => 'PPD',
        ]);
        $this->assertEmpty($longPpd['cohortes']);
        $this->assertEquals('0', $longPpd['totales']['padron_texto']);
    }

    /**
     * Prueba 3: Inteligencia Longitudinal sin valores de respaldo artificiales (eliminar 4.2 meses y padrón como respondientes).
     * Muestra 'Sin dato' o 'Sin dato (0 casos)' cuando no haya fechas válidas y cuenta casos reales.
     */
    public function test_03_tabla_longitudinal_elimina_4_2_meses_y_muestra_sin_dato_con_casos(): void
    {
        $analiticaSvc = app(SeguimientoAnaliticaService::class);

        $longitudinal = $analiticaSvc->generarAnalisisLongitudinal([
            'fuente_datos' => 'encuesta_depurada_2026',
            'grupo_formacion' => 'TODOS',
        ]);

        foreach ($longitudinal['cohortes'] as $cohorte => $fila) {
            // No debe existir el número '4.2' o '4,2' asignado por defecto
            $this->assertStringNotContainsString('4.2', $fila['tiempo_primer_empleo_texto']);
            $this->assertStringNotContainsString('4,2', $fila['tiempo_primer_empleo_texto']);

            // Si casos_tiempo_primer_empleo es 0, debe indicar 'Sin dato' con número de casos
            if ($fila['casos_tiempo_primer_empleo'] === 0) {
                $this->assertStringContainsString('Sin dato', $fila['tiempo_primer_empleo_texto']);
                $this->assertStringContainsString('0 casos', $fila['tiempo_primer_empleo_texto']);
            } else {
                $this->assertStringContainsString('casos', $fila['tiempo_primer_empleo_texto']);
            }

            // Respondientes no debe ser un fallback ciego del padrón
            $this->assertArrayHasKey('respondientes', $fila);
            $this->assertArrayHasKey('respondientes_texto', $fila);
        }

        // Totales longitudinales
        $totales = $longitudinal['totales'];
        $this->assertStringNotContainsString('4.2', $totales['tiempo_primer_empleo_texto']);
        $this->assertStringNotContainsString('4,2', $totales['tiempo_primer_empleo_texto']);
    }

    /**
     * Prueba 4: Exportación de informe por cohorte en Excel (.xlsx) para TODOS los grupos.
     * Verifica descarga, cabeceras institucionales, metadatos, fecha de corte y filas consistentes con la pantalla.
     */
    public function test_04_exportar_informe_por_cohorte_xlsx_todos(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('seguimiento.analitica.exportar-cohortes', [
                'fuente_datos' => 'encuesta_depurada_2026',
                'grupo_formacion' => 'TODOS',
                'formato' => 'xlsx',
            ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('openxmlformats', $response->headers->get('Content-Type'));

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_xlsx_') . '.xlsx';
        file_put_contents($tmpFile, $response->streamedContent());

        $spreadsheet = IOFactory::load($tmpFile);
        $sheet = $spreadsheet->getActiveSheet();

        // Verificar metadatos en las primeras filas
        $celdaTitulo = $sheet->getCell('A1')->getValue();
        $this->assertStringContainsString('PUKLLASUNCHIS', $celdaTitulo);

        $fuenteEnReporte = $sheet->getCell('B3')->getValue();
        $this->assertEquals('Encuesta depurada 2026', $fuenteEnReporte);

        $fechaCorte = $sheet->getCell('B4')->getValue();
        $this->assertStringContainsString('Corte 2026', $fechaCorte);

        $vinculos = (int) $sheet->getCell('B9')->getValue();
        $personas = (int) $sheet->getCell('B10')->getValue();
        $this->assertEquals(113, $vinculos);
        $this->assertEquals(112, $personas);

        // Cabecera en fila 14
        $headerCohorte = $sheet->getCell('A14')->getValue();
        $this->assertEquals('Cohorte', $headerCohorte);

        // Fila de totales al final
        $highestRow = $sheet->getHighestRow();
        $totalLabel = $sheet->getCell("A{$highestRow}")->getValue();
        $this->assertEquals('TOTAL', $totalLabel);

        $padronTotal = $sheet->getCell("B{$highestRow}")->getValue();
        $this->assertStringContainsString('112', $padronTotal);
        $this->assertStringContainsString('113', $padronTotal);

        @unlink($tmpFile);
    }

    /**
     * Prueba 5: Exportar informe por cohorte en Excel (.xlsx) para FID y PPD por separado.
     */
    public function test_05_exportar_informe_por_cohorte_xlsx_fid_y_ppd(): void
    {
        // 1. Exportar FID
        $respFid = $this->actingAs($this->adminUser)
            ->get(route('seguimiento.analitica.exportar-cohortes', [
                'fuente_datos' => 'encuesta_depurada_2026',
                'grupo_formacion' => 'FID',
                'formato' => 'xlsx',
            ]));

        $respFid->assertStatus(200);
        $tmpFid = tempnam(sys_get_temp_dir(), 'test_fid_') . '.xlsx';
        file_put_contents($tmpFid, $respFid->streamedContent());
        $sheetFid = IOFactory::load($tmpFid)->getActiveSheet();

        $grupoFid = $sheetFid->getCell('B5')->getValue();
        $this->assertEquals('FID', $grupoFid);
        $vinculosFid = (int) $sheetFid->getCell('B9')->getValue();
        $personasFid = (int) $sheetFid->getCell('B10')->getValue();
        $this->assertEquals(113, $vinculosFid);
        $this->assertEquals(112, $personasFid);
        @unlink($tmpFid);

        // 2. Exportar PPD
        $respPpd = $this->actingAs($this->adminUser)
            ->get(route('seguimiento.analitica.exportar-cohortes', [
                'fuente_datos' => 'encuesta_depurada_2026',
                'grupo_formacion' => 'PPD',
                'formato' => 'xlsx',
            ]));

        $respPpd->assertStatus(200);
        $tmpPpd = tempnam(sys_get_temp_dir(), 'test_ppd_') . '.xlsx';
        file_put_contents($tmpPpd, $respPpd->streamedContent());
        $sheetPpd = IOFactory::load($tmpPpd)->getActiveSheet();

        $grupoPpd = $sheetPpd->getCell('B5')->getValue();
        $this->assertEquals('PPD', $grupoPpd);
        $vinculosPpd = (int) $sheetPpd->getCell('B9')->getValue();
        $personasPpd = (int) $sheetPpd->getCell('B10')->getValue();
        $this->assertEquals(0, $vinculosPpd);
        $this->assertEquals(0, $personasPpd);
        @unlink($tmpPpd);
    }

    /**
     * Prueba 6: Conservación del cruce dinámico en CSV sin fuga de PII y respetando la fuente de datos.
     */
    public function test_06_exportar_cruce_dinamico_csv_conserva_fuente_y_metadatos(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('seguimiento.analitica.exportar', [
                'fuente_datos' => 'encuesta_depurada_2026',
                'fila' => 'programa',
                'columna' => 'situacion_laboral',
                'formato' => 'csv',
            ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csvContent = $response->streamedContent();
        $this->assertStringContainsString('PUKLLASUNCHIS', $csvContent);
        $this->assertStringContainsString('Encuesta depurada 2026', $csvContent);
        $this->assertStringContainsString('Corte 2026', $csvContent);

        // Cero fuga de DNI o correos personales
        $this->assertStringNotContainsString('@puklla.test', $csvContent);
        $this->assertStringNotContainsString('@gmail.com', $csvContent);
    }
}
