<?php

namespace App\Console\Commands;

use App\Services\AylluContextService;
use Illuminate\Console\Command;

class AylluInicializarSemillaCommand extends Command
{
    /**
     * Comando explícito, seguro e idempotente para inicializar las comunidades
     * oficiales y publicaciones de bienvenida de Ayllu Puklla.
     */
    protected $signature = 'ayllu:inicializar-semilla';

    protected $description = 'Inicializa de forma segura e idempotente las comunidades oficiales y publicaciones de bienvenida de Ayllu Puklla sin ejecutarse durante la navegación ordinaria.';

    public function handle(AylluContextService $contextService): int
    {
        $contextService->asegurarSemillaInstitucional();
        $this->info('Semilla institucional de Ayllu Puklla verificada/inicializada correctamente de forma idempotente.');

        return self::SUCCESS;
    }
}
