<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeguimientoImportarLugares extends Command
{
    protected $signature = 'seguimiento:importar-lugares {archivo : CSV UTF-8 normalizado}';
    protected $description = 'Carga catálogo territorial versionado sin inventar coordenadas ni identificadores oficiales';

    public function handle(): int
    {
        $file = fopen($this->argument('archivo'), 'r');
        if (!$file) { $this->error('No se pudo abrir el archivo.'); return self::FAILURE; }
        try {
            $header = fgetcsv($file);
            if (!$header) throw new \RuntimeException('CSV vacío.');
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $rows = []; $line = 1;
            while (($values = fgetcsv($file)) !== false) {
                $line++;
                if ($values === [null]) continue;
                if (count($header) !== count($values)) throw new \RuntimeException("Columnas incorrectas en línea $line.");
                $row = array_combine($header, array_map(fn ($v) => $v === '' ? null : trim($v), $values));
                $v = Validator::make($row, [
                    'fuente' => 'required|string|max:80', 'codigo' => 'required|string|max:100',
                    'tipo' => 'required|in:localidad,comunidad,escuela,sede', 'nombre' => 'required|string|max:255',
                    'departamento' => 'required|string|max:255','provincia' => 'required|string|max:255','distrito' => 'required|string|max:255',
                    'ubigeo' => 'nullable|regex:/^[0-9]{6}$/', 'latitud' => 'nullable|numeric|between:-90,90',
                    'longitud' => 'nullable|numeric|between:-180,180', 'fecha_fuente' => 'required|date_format:Y-m-d',
                ]);
                if ($v->fails()) throw new \RuntimeException("Línea $line: ".$v->errors()->first());
                $data = $v->validated();
                if (isset($data['latitud']) !== isset($data['longitud'])) throw new \RuntimeException("Línea $line: se requiere el par de coordenadas.");
                $rows[] = $data;
            }
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) DB::table('seguimiento_lugares')->updateOrInsert(
                    array_intersect_key($row, array_flip(['fuente','codigo','tipo'])),
                    $row + ['created_at' => now(), 'updated_at' => now()]
                );
            });
            $this->info(count($rows).' lugares importados. No se han reinterpretado comunidades como centros poblados.');
            return self::SUCCESS;
        } catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
        finally { fclose($file); }
    }
}
