<?php
declare(strict_types=1);

session_start();

$config = require dirname(__DIR__) . '/config/config.php';
$GLOBALS['config'] = $config;

require dirname(__DIR__) . '/core/Database.php';
require dirname(__DIR__) . '/core/Auth.php';
require dirname(__DIR__) . '/core/ApiResponse.php';
require __DIR__ . '/functions.php';

$pdo = Database::conn($config['db']);
