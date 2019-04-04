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

function myLog(string $type, string $text): void {
    fwrite(fopen('php://stdout', 'wb'), $type . ' : ' . $text . PHP_EOL);
}

$db     = new Db(__DIR__ . DIRECTORY_SEPARATOR . 'database.sql3');
$server = new Server($db);
$env    = new Environment();

$params = SatelliteParametersFactory::createFromJson(SatelliteParameters::class, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'parameters.json'));

$cmd = trim(strrchr($_SERVER['REQUEST_URI'], '/'), '/');
myLog('REQUEST_URI', $_SERVER['REQUEST_URI']);
myLog('CMD', '"'.$cmd.'"');
switch ($cmd) {
    case 'reset':
        $db->reset($params);
        $result = ['OK'];
        myLog('DATABASE', 'RESET');
        break;
    case '':
        $content = file_get_contents('php://input');
        myLog('SET INPUT', $content);
        $result = $server->setParams($content);
        break;
    default:
        $metrics = explode(',', $cmd);
        myLog('GET INPUT', $cmd);
        $result = $server->getParams($metrics);
        break;
}

header('Content-Type: application/json; charset=utf-8');
myLog('RESPONSE', json_encode($result, JSON_UNESCAPED_UNICODE));
echo json_encode($result, JSON_UNESCAPED_UNICODE);