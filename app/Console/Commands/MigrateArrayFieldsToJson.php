<?php

namespace App\Console\Commands;

use App\Models\Alumno;
use Illuminate\Console\Command;

class MigrateArrayFieldsToJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:array-fields-to-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convierte campos de string a JSON en la tabla alumnos (bienes_vivienda, otros_servicios, habilidades)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migración de datos de string a JSON...');
        
        $processed = 0;
        $errors = 0;
        
        // Procesar cada alumno
        Alumno::all()->each(function ($alumno) use (&$processed, &$errors) {
            try {
                // Convertir bienes_vivienda
                if ($alumno->bienes_vivienda) {
                    if (is_string($alumno->bienes_vivienda)) {
                        $alumno->bienes_vivienda = array_filter(explode(',', $alumno->bienes_vivienda));
                    }
                } else {
                    $alumno->bienes_vivienda = [];
                }
                
                // Convertir otros_servicios
                if ($alumno->otros_servicios) {
                    if (is_string($alumno->otros_servicios)) {
                        $alumno->otros_servicios = array_filter(explode(',', $alumno->otros_servicios));
                    }
                } else {
                    $alumno->otros_servicios = [];
                }
                
                // Convertir habilidades (usa '-' como separador)
                if ($alumno->habilidades) {
                    if (is_string($alumno->habilidades)) {
                        $alumno->habilidades = array_filter(explode('-', $alumno->habilidades));
                    }
                } else {
                    $alumno->habilidades = [];
                }
                
                // Guardar
                $alumno->save();
                $processed++;
                
                $this->line("✓ Alumno {$alumno->id} ({$alumno->nombres} {$alumno->apellidos}) procesado correctamente");
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Error procesando alumno {$alumno->id}: " . $e->getMessage());
            }
        });
        
        $this->info("========================================");
        $this->info("Migración completada!");
        $this->info("Alumnos procesados: $processed");
        $this->info("Errores: $errors");
        $this->info("========================================");
        
        return $processed > 0 ? self::SUCCESS : self::FAILURE;
    }
}
