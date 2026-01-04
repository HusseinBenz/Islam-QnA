<?php
declare(strict_types=1);

/** @var \App\AppContext $app */
$app = require __DIR__ . '/src/bootstrap.php';

$controller = new \App\Http\Controllers\AdminController($app);
$controller->handle();
