<?php
require_once __DIR__ . '/vendor/autoload.php';

$dot_env = Dotenv\Dotenv::createMutable(__DIR__);
$dot_env->load();

$file = new SplFileObject("input.txt");
while (!$file->eof()) {
    echo $file->fgets();
}
