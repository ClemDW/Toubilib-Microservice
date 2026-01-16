<?php
declare(strict_types=1);

use Slim\App;
use Gateway\Api\Actions\GenericGetPraticiensAction;
use Gateway\Api\Actions\ListerPraticienRdvsAction;

return function (App $app): App {

    $app->get('/praticiens', GenericGetPraticiensAction::class);
    $app->get('/praticiens/{id}', GenericGetPraticiensAction::class);
    $app->get("/praticiens/{id}/rdvs", ListerPraticienRdvsAction::class);

    return $app;
};