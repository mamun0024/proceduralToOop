<?php

spl_autoload_register(function ($class_name) {
    $class_name = str_replace("\\", DIRECTORY_SEPARATOR, $class_name);
    require_once __DIR__ . "/$class_name.php";
});

require_once __DIR__ . '/../vendor/autoload.php';

use Oop\CalculateCommission;

$dot_env = Dotenv\Dotenv::createMutable(__DIR__ . '/../');
$dot_env->load();

$app = new CalculateCommission([
    'settings' => [
        'file_name' => $_ENV['INPUT_FILE_NAME'],
        'bin_check_url' => $_ENV['BIN_URL'],
        'rate_url' => $_ENV['RATE_URL'],
        'currency' => $_ENV['CURRENCY']
    ]
]);
