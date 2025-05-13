<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../App/Core/helpers.php';

use App\Core\Router;

// Spustíme router (zpracuje URL a zavolá příslušný kontroler)
$router = new Router();
$router->handleRequest();