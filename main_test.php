<?php
include 'test/environment.php';

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

$mq = new QueryManager();

$env       = new Environment();
$params    = SatelliteParametersFactory::createFromJson(SatelliteParameters::class, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'parameters.json'));
$telemetry = new Telemetry();
$exchange  = new Exchanger($env, $telemetry);
$satellite = new SatelliteMock($telemetry, $exchange, $mq, $params);

echo PHP_EOL . 'Correct plan' . PHP_EOL . PHP_EOL;
try {
    $exchange->reset();
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, prepareJson($env->getFlyProgramLocation()), $params, $env->getTelemetryFreq());
    $satellite->run($program);
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

echo PHP_EOL . 'Incorrect plan. Wrong SET value for parameter' . PHP_EOL . PHP_EOL;
try {
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, prepareJson('test/badPlan1.json'), $params, $env->getTelemetryFreq());
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

echo PHP_EOL . 'Incorrect plan. Wrong start time' . PHP_EOL . PHP_EOL;
try {
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, prepareJson($env->getFlyProgramLocation(), -10), $params, $env->getTelemetryFreq());
    $satellite->run($program);
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

echo PHP_EOL . 'Incorrect plan. Duplicate ID' . PHP_EOL . PHP_EOL;
try {
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, prepareJson('test/badPlan2.json'), $params, $env->getTelemetryFreq());
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

echo PHP_EOL . 'Incorrect plan. No operations' . PHP_EOL . PHP_EOL;
try {
    $time    = time() + 3;
    $json    = str_replace('"time"', $time, '{"startUp": "time"}');
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, $json, $params, $env->getTelemetryFreq());
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

echo PHP_EOL . 'Incorrect plan. No start time' . PHP_EOL . PHP_EOL;
try {
    $time    = time() + 3;
    $json    = str_replace('"time"', $time, '{"operations": []}');
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, $json, $params, $env->getTelemetryFreq());
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

echo PHP_EOL . 'Incorrect plan. File not found' . PHP_EOL . PHP_EOL;
try {
    $program = FlyProgramFactory::getFromFile(FlyProgram::class, 'noFile', $params, $env->getTelemetryFreq());
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

echo PHP_EOL . 'Incorrect plan. Malformed JSON' . PHP_EOL . PHP_EOL;
try {
    $program = FlyProgramFactory::getFromJson(FlyProgram::class, prepareJson('test/badPlan3.json'), $params, $env->getTelemetryFreq());
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}