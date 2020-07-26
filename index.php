<?php

require_once 'vendor/autoload.php';

use App\Application;

$dot_env = Dotenv\Dotenv::createMutable(__DIR__);
$dot_env->load();

$app = new Application([
    'settings' => [
        'file_name'        => $_ENV['INPUT_FILE_NAME'],
        'file_path'        => $_ENV['INPUT_FILE_PATH'],
        'bin_check_url'    => $_ENV['BIN_URL'],
        'rate_url'         => $_ENV['RATE_URL'],
        'currency'         => $_ENV['CURRENCY'],
        'eu_commission'    => $_ENV['COMMISSION_RATE_FOR_EU'],
        'ex_eu_commission' => $_ENV['COMMISSION_RATE_EXCEPT_EU']
    ]
]);

echo "<pre>";
print_r($app->run());
echo "</pre>";
