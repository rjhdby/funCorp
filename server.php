<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use environment\Environment;
use satellite\SatelliteParameters;
use satellite\SatelliteParametersFactory;
use server\Db;
use server\Server;

error_reporting(E_ALL);
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file  = __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$db     = new Db(__DIR__ . DIRECTORY_SEPARATOR . 'database.sql3');
$server = new Server($db);
$env    = new Environment();

$params    = SatelliteParametersFactory::createFromJson(SatelliteParameters::class, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'parameters.json'));

$cmd = trim(strrchr($_SERVER['REQUEST_URI'], '/'), '/');

if ($cmd === 'reset') {
    $db->reset($params);
    $result = ['OK'];
} elseif ($cmd === '') {
    $content = file_get_contents('php://input');
    fwrite(fopen('php://stdout', 'wb'), 'SET INPUT : '.$content . PHP_EOL);
    $result = $server->setParams($content);
} else {
    $metrics = explode(',', $cmd);
    fwrite(fopen('php://stdout', 'wb'), 'GET INPUT : '.json_encode($metrics, true) . PHP_EOL);
    $result = $server->getParams($metrics);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE);