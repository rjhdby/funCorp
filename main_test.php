<?php
include 'test/environment.php';

use exchanger\Exchanger;
use mq\QueryManager;
use program\FlyProgram;
use satellite\SatelliteParameters;
use telemetry\Telemetry;
use test\EnvironmentMock;
use test\SatelliteMock;

error_reporting(E_ALL);
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file  = __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$mq = new QueryManager();

$env       = new EnvironmentMock();
$params    = $env->getSatelliteParameters(SatelliteParameters::class, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'parameters.json'));
$telemetry = new Telemetry();
$exchange  = new Exchanger($env, $telemetry);
$satellite = new SatelliteMock($params, $telemetry, $exchange, $mq);

$exchange->reset();

$satellite->initialize($env);
$program = new FlyProgram();

/* Good way */
try {
    $program->prepare($env->getFlyProgramJson(), $params);
    $satellite->run($program);
} catch (\RuntimeException $e) {
    $telemetry->error($e->getMessage());
}

///* Exchanger timeouts */
//try {
//    $exchange->setTimeouts([2, 4]);
//    $program->prepare($env->getFlyProgramJson(), $params);
//    $satellite->run($program);
//} catch (\RuntimeException $e) {
//    $telemetry->error($e->getMessage());
//}
//
///* Failed operations */
//try {
//    $exchange->setTimeouts([]);
//    $exchange->setFailed([2, 4]);
//    $program->prepare($env->getFlyProgramJson(), $params);
//    $satellite->run($program);
//} catch (\RuntimeException $e) {
//    $telemetry->error($e->getMessage());
//}
//
///* Tainted parameters */
//try {
//    $exchange->setTimeouts([]);
//    $exchange->setFailed([]);
//    $exchange->setTainted([2, 4]);
//    $program->prepare($env->getFlyProgramJson(), $params);
//    $satellite->run($program);
//} catch (\RuntimeException $e) {
//    $telemetry->error($e->getMessage());
//}
//
///* Wrong fly plan */
//try {
//    putenv('FLIGHT_PROGRAM=test/badPlan.json');
//    $exchange->setTimeouts([]);
//    $exchange->setFailed([]);
//    $program->prepare($env->getFlyProgramJson(), $params);
//    $satellite->run($program);
//} catch (\RuntimeException $e) {
//    $telemetry->error($e->getMessage());
//}
