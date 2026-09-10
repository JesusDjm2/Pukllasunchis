<?php
// BORRAR este archivo después de usarlo
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || ($_GET['token'] ?? '') !== 'puklla2026deploy') {
    http_response_code(403);
    die('Forbidden');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$commands = ['config:clear', 'view:clear', 'cache:clear', 'storage:link'];
echo '<pre>';
foreach ($commands as $cmd) {
    $output = new Symfony\Component\Console\Output\BufferedOutput();
    $status = $kernel->call($cmd, [], $output);
    echo "[" . ($status === 0 ? 'OK' : 'ERR') . "] php artisan {$cmd}\n";
    echo $output->fetch() . "\n";
}
echo '</pre>';
echo '<p style="color:red;font-weight:bold">ELIMINA este archivo del servidor ahora.</p>';
