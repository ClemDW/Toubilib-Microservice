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

    $app->get('/praticiens/{id}/rdvs', ListerRdvAction::class)->setName('ListerRdvPraticien');

    $app->post('/rdvs', CreerRdvAction::class)->add(CreateRdvDtoMiddleware::class)->setName('CreerRdv');

    $app->delete('/rdvs/{id}', AnnulerRdvAction::class)->setName('AnnulerRdv');

    $app->patch('/rdvs/{id}/honorer', MarquerRdvHonoreAction::class)->setName('MarquerRdvHonore');
    $app->patch('/rdvs/{id}/non-honore', MarquerRdvNonHonoreAction::class)->setName('MarquerRdvNonHonore');

    $app->get('/rdvs/{id}', ListerRdvAction::class)->setName('AfficherRdv');

    $app->get('/patients/{id}/rdvs', \toubilib\api\actions\ListerRdvPatientAction::class)->setName('ListerRdvPatient');

    $app->post('/patients', CreerPatientAction::class)->setName('CreerPatient');
    $app->post('/signin', SigninAction::class)->setName('Signin');

    return $app;
};