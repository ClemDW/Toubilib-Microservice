<?php

use DI\ContainerBuilder;
use DI\Bridge\Slim\Bridge;

require __DIR__ . '/../vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/dependencies.php');
$container = $builder->build();

$app = Bridge::create($container);

// Middleware de routing (nécessaire pour les routes)
$app->addRoutingMiddleware();

// Middleware de gestion des erreurs avec réponses JSON
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Handler personnalisé pour les erreurs HTTP
$errorMiddleware->setDefaultErrorHandler(function (
    \Psr\Http\Message\ServerRequestInterface $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    
    $statusCode = 500;
    $errorCode = 'INTERNAL_ERROR';
    $message = 'Une erreur interne est survenue';
    
    if ($exception instanceof \Slim\Exception\HttpException) {
        $statusCode = $exception->getCode();
        $message = $exception->getMessage();
        
        $errorCode = match($statusCode) {
            401 => 'AUTHENTICATION_REQUIRED',
            403 => 'ACCESS_DENIED',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            default => 'HTTP_ERROR'
        };
    }
    
    $payload = json_encode([
        'error' => $message,
        'code' => $errorCode,
        'status' => $statusCode
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    $response->getBody()->write($payload);
    
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($statusCode);
});

(require __DIR__ . '/routes.php')($app);

return $app;
