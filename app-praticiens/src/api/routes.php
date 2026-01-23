<?php
declare(strict_types=1);

use Slim\App;
use toubilib\api\actions\AfficherPraticienAction;
use toubilib\api\actions\AgendaPraticienAction;
use toubilib\api\actions\AnnulerRdvAction;
use toubilib\api\actions\CreerPatientAction;
use toubilib\api\actions\CreerRdvAction;
use toubilib\api\actions\ListerPraticiensAction;
use toubilib\api\actions\ListerRdvAction;
use toubilib\api\actions\MarquerRdvHonoreAction;
use toubilib\api\actions\MarquerRdvNonHonoreAction;
use toubilib\api\actions\SigninAction;
use toubilib\api\middlewares\CreateRdvDtoMiddleware;


return function (App $app): App {

    $app->get('/praticiens', ListerPraticiensAction::class)->setName('ListerPraticiens');

    $app->get('/praticiens/{id}', AfficherPraticienAction::class)->setName('AfficherPraticien');

    $app->get('/praticiens/{id}/agenda', AgendaPraticienAction::class)->setName('AgendaPraticien');

    $app->post('/signin', SigninAction::class)->setName('Signin');

    return $app;
};