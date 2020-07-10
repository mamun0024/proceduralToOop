<?php

declare(strict_types=1);

require_once __DIR__ . '/oop/CalculateCommission.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Oop\CalculateCommission;

$dot_env = Dotenv\Dotenv::createMutable(__DIR__ . '/../');
$dot_env->load();

$app = new CalculateCommission([
    'settings' => [
        'file_name' => $_ENV['INPUT_FILE_NAME'],
        'bin_check_url' => $_ENV['BIN_URL'],
        'rate_url' => $_ENV['RATE_URL']
    ]
]);
