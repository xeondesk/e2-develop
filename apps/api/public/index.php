<?php
declare(strict_types=1);

$baseDir = __DIR__;
while (!is_dir($baseDir . '/vendor') && $baseDir !== '/') {
    $baseDir = dirname($baseDir);
}
$vendorPath = $baseDir . '/vendor/autoload.php';
if (!is_file($vendorPath)) {
    // fallback for direct execution from repo root
    $vendorPath = __DIR__ . '/../../../../vendor/autoload.php';
}
require_once $vendorPath;

use Nexo\Api\Application;
use Nexo\Api\Routes;

$app = new Application();
$app->boot();

$routes = new Routes($app->getContainer());

$routes->get('/', function ($container) {
    return [
        'name' => 'Nexo',
        'version' => $container->get('app')->version(),
        'status' => 'foundation-ready',
    ];
});

$routes->get('/health', function () {
    return ['status' => 'ok'];
});

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

header('Content-Type: application/json');
echo json_encode($routes->dispatch($method, $path), JSON_PRETTY_PRINT);