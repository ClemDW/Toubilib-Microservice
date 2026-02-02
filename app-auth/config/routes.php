<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function( \Slim\App $app):\Slim\App {
    $app->get("/", function (Request $request, Response $response) {
        $response->getBody()->write("Auth Service API");
        return $response;
    });

    $app->post("/tokens/validate", \toubilib\api\actions\ValidateTokenAction::class);
    $app->post("/auth/signin", \toubilib\api\actions\SigninAction::class);

    $app->post("/auth/refresh", \toubilib\api\actions\RefreshTokenAction::class);

    $app->options("/{routes:.+}", function (Request $request, Response $response) {
        return $response;
    });

    return $app;
};
