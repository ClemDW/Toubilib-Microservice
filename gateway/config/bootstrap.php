<?php

use Slim\Factory\AppFactory;
use GuzzleHttp\Client;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ );
$dotenv->load();

$container = require __DIR__ . '/../config/container.php';

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, false, false);

$client = new Client([
    
]);

$app = (require __DIR__ . '/../src/api/routes.php')($app);

return $app;