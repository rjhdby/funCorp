<?php
include 'test/environment.php';
ini_set('display_errors', 'On');

use environment\Environment;
use exchanger\Exchanger;
use mq\QueryManager;
use program\FlyProgram;
use program\FlyProgramFactory;
use satellite\SatelliteParameters;
use satellite\SatelliteParametersFactory;
use telemetry\Telemetry;
use test\SatelliteMock;

error_reporting(E_ALL);
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file  = __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

function prepareJson($file, $delay = 3) {
    $time = time() + $delay;

    return str_replace('"time"', $time, file_get_contents($file));
}

$mq        = new QueryManager();
$env       = new Environment();
$telemetry = new Telemetry();
$exchange  = new Exchanger($env, $telemetry);
$params    = SatelliteParametersFactory::createFromJson(SatelliteParameters::class, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'parameters.json'));
$satellite = new SatelliteMock($telemetry, $exchange, $mq, $params);
$exchange->reset();

echo PHP_EOL . '===== Correct plan =====' . PHP_EOL . PHP_EOL;
try {
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, prepareJson($env->getFlyProgramLocation()), $params, $env->getTelemetryFreq());
    $satellite->run($program);
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

echo PHP_EOL . '===== Critical operation check fails =====' . PHP_EOL . PHP_EOL;
try {
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, prepareJson('test/badPlan.json'), $params, $env->getTelemetryFreq());
    $satellite->run($program);
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}