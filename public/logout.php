<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/includes/bootstrap.php';
Auth::logout();
header('Location: index.php');
