<?php

namespace Database\Seeders;

use App\Models\BolsaTrabajoOferta;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BolsaTrabajoOfertaSeeder extends Seeder
{
    /**
     * Ofertas de demostración del año en curso hasta el mes actual (inclusive).
     */
    public function run(): void
    {
        $this->limpiarArchivosDemoSeed();

        BolsaTrabajoOferta::where('nombre', 'like', 'Demo Bolsa %')->delete();

        $year = (int) now()->year;
        $maxMonth = (int) now()->month;

        $destDir = public_path('img/bolsa-trabajo-ofertas');
        if (! File::isDirectory($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        $plantillasImagen = $this->plantillasImagenDisponibles();

        $plantillas = [
            'Docente de aula — educación inicial',
            'Docente de aula — educación primaria',
            'Coordinación pedagógica',
            'Tutor de prácticas preprofesionales',
            'Asistente de investigación educativa',
            'Personal de secretaría académica',
            'Bibliotecario institucional',
            'Orientación educativa y convivencia',
            'Especialista en educación intercultural bilingüe',
            'Profesor de taller (arte / expresión)',
            'Auxiliar de laboratorio',
            'Psicólogo educativo',
            'Comunicación institucional',
            'Gestión de proyectos sociales',
        ];

        for ($i = 1; $i <= 22; $i++) {
            $mes = random_int(1, $maxMonth);
            $ultimoDia = (int) Carbon::create($year, $mes, 1)->endOfMonth()->day;
            $diaInicio = random_int(1, max(1, $ultimoDia - 10));

            $inicio = Carbon::create($year, $mes, $diaInicio)->startOfDay();
            $fin = (clone $inicio)->addDays(random_int(12, 50));
            if ($fin->lt($inicio)) {
                $fin = (clone $inicio)->addDays(20);
            }

            $base = $plantillas[($i - 1) % count($plantillas)];
            $nombre = 'Demo Bolsa '.$i.' — '.$base;

            $detalles = '<p><strong>Descripción</strong></p><p>Convocatoria de demostración generada por seeder. '
                .'Requisitos: título profesional en educación o afines, experiencia deseable en '
                .'contextos interculturales.</p><ul><li>Modalidad: presencial / híbrida</li>'
                .'<li>Enviar CV y documentos al correo de la institución convocante</li></ul>';

            $rutaImagen = null;
            if ($plantillasImagen !== []) {
                $plantilla = $plantillasImagen[($i - 1) % count($plantillasImagen)];
                $rutaImagen = $this->copiarImagenDemo($plantilla, $destDir, $i);
            }

            BolsaTrabajoOferta::create([
                'nombre' => $nombre,
                'detalles' => $detalles,
                'imagen' => $rutaImagen,
                'fecha_inicio' => $inicio->toDateString(),
                'fecha_fin' => $fin->toDateString(),
            ]);
        }
    }

    /**
     * Elimina copias previas generadas por este seeder.
     */
    private function limpiarArchivosDemoSeed(): void
    {
        $dir = public_path('img/bolsa-trabajo-ofertas');
        if (! File::isDirectory($dir)) {
            return;
        }
        foreach (File::glob($dir.DIRECTORY_SEPARATOR.'demo-seed-*') ?: [] as $ruta) {
            if (is_file($ruta)) {
                File::delete($ruta);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function plantillasImagenDisponibles(): array
    {
        $candidatos = [
            public_path('img/min/Infografia-FID-2026.webp'),
            public_path('img/Infografia-PPD.png'),
            public_path('img/horarios/Horario-2025-II-2.jpg'),
            public_path('img/calendarioppd/1764783329_Biblioteca.jpg'),
            public_path('img/calendarioppd/1764782998_Puklla 01.jpg'),
            public_path('img/calendarioppd/1764869940_controles.jpg'),
        ];

        return array_values(array_filter($candidatos, fn (string $ruta) => File::exists($ruta)));
    }

    private function copiarImagenDemo(string $plantillaAbs, string $destDir, int $indice): ?string
    {
        if (! File::exists($plantillaAbs)) {
            return null;
        }

        $ext = strtolower(pathinfo($plantillaAbs, PATHINFO_EXTENSION) ?: 'jpg');
        $nombre = 'demo-seed-'.$indice.'.'.$ext;
        $destino = $destDir.DIRECTORY_SEPARATOR.$nombre;
        File::copy($plantillaAbs, $destino);

        return 'img/bolsa-trabajo-ofertas/'.$nombre;
    }
}
