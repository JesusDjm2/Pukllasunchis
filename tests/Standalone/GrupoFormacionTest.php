<?php
// Sin Laravel ni BD: ejecuta las reglas reales de clasificación, no una copia en otro lenguaje.
require_once __DIR__.'/../../app/Enums/Modalidad.php';
require_once __DIR__.'/../../app/Services/SeguimientoEgresado/GrupoFormacionService.php';

use App\Services\SeguimientoEgresado\GrupoFormacionService;

$service = new GrupoFormacionService;
$tests = [
    'sin evidencia queda por verificar' => fn () => $service->clasificar(null, [], [])['grupos'] === [],
    'no se asume FID si falta PPD' => fn () => $service->clasificar(null, ['desconocido'], [])['por_verificar'] === true,
    'programa institucional FID' => fn () => $service->clasificar('FID', [], [])['grupos'] === ['FID'],
    'programa institucional PPD' => fn () => $service->clasificar('PPD', [], [])['grupos'] === ['PPD'],
    'fichas contradictorias requieren revisión' => fn () => $service->clasificar(null, ['FID','PPD'], [])['grupos'] === [],
    'dos egresos confirmados conservan ambos grupos' => fn () => $service->clasificar('PPD', ['FID','PPD'], ['FID','PPD','FID'])['grupos'] === ['FID','PPD'],
    'matrícula posterior no crea un segundo egreso' => fn () => $service->clasificar('PPD', ['FID','PPD'], ['FID'])['grupos'] === ['FID'],
    'persona dual pertenece a filtros FID y PPD' => fn () => $service->coincide(['FID','PPD'],'FID') && $service->coincide(['FID','PPD'],'PPD'),
    'PPD queda fuera de filtro FID' => fn () => !$service->coincide(['PPD'],'FID'),
    'todos y comparar incluyen desconocidos' => fn () => $service->coincide([], 'TODOS') && $service->coincide([], 'COMPARAR'),
    'por verificar no equivale a FID o PPD' => fn () => $service->coincide([], 'POR_VERIFICAR') && !$service->coincide([], 'FID'),
    'filtro inválido se rechaza' => function () use ($service) { try { $service->normalizar('administrador'); } catch (InvalidArgumentException $e) { return true; } return false; },
];
foreach ($tests as $label => $test) {
    if (!$test()) throw new RuntimeException('FALLÓ: '.$label);
    echo 'OK: '.$label."\n";
}
echo count($tests)." pruebas superadas.\n";
