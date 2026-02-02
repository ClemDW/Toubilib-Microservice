<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use toubilib\api\actions\CreerPatientAction;

return function (App $app): App {

    $app->get("/", function (Request $request, Response $response) {
        $response->getBody()->write("Patient Service API");
        return $response;
    });

    $app->post("/patients", CreerPatientAction::class);

    return $app;
};
