<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/db.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => $db_info['host'],
            'database' => $db_info['database'],
            'username' => $db_info['username'],
            'password' => $db_info['password'],
            'charset' => $db_info['charset'],
            'collation' => $db_info['collation'],
            'prefix' => ''
        ]
    ]
]);


$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container["settings"]["db"]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

//configure db
$container["db"] = function($container) use ($capsule) {
    return $capsule;
};

//configure controllers

$container["GreenhouseController"] = function($container) {
    return new \App\Controllers\GreenhouseController($container);
};

$container["GreenhouseDataController"] = function($container) {
    return new \App\Controllers\GreenhouseDataController($container);
};

$container["PresetsController"] = function($container) {
    return new \App\Controllers\PresetsController($container);
};

$container["OptionsController"] = function($container) {
    return new \App\Controllers\OptionsController($container);
};
require __DIR__ . '/../app/routes.php';
