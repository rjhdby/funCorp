<?php
putenv('FLIGHT_PROGRAM=goodPlan.json');
putenv('EXCHANGE_URI=http://localhost');
putenv('TELEMETRY_FREQ=5');

error_reporting(E_ALL);
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file  = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});