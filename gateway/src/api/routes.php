<?php
declare(strict_types=1);

use Slim\App;
use Gateway\api\actions\ListerPraticiensAction;
use Gateway\api\actions\AfficherPraticienAction;
use Gateway\api\actions\ListerRdvAction;
use Gateway\api\actions\CreerRdvAction;
use Gateway\api\actions\AnnulerRdvAction;
use Gateway\api\actions\MarquerRdvHonoreAction;
use Gateway\api\actions\MarquerRdvNonHonoreAction;
use Gateway\api\actions\AgendaPraticienAction;
use Gateway\api\actions\ListerRdvPatientAction;
use Gateway\api\actions\CreerPatientAction;
use Gateway\api\actions\SigninAction;
use Gateway\api\middlewares\CreateRdvDtoMiddleware;


return function (App $app): App {

    $app->get('/praticiens', ListerPraticiensAction::class)->setName('ListerPraticiens');

    $app->get('/praticiens/{id}', AfficherPraticienAction::class)->setName('AfficherPraticien');

    $app->get('/praticiens/{id}/rdvs', ListerRdvAction::class)->setName('ListerRdvPraticien');

    $app->post('/rdvs', CreerRdvAction::class)->add(CreateRdvDtoMiddleware::class)->setName('CreerRdv');

    $app->delete('/rdvs/{id}', AnnulerRdvAction::class)->setName('AnnulerRdv');

    $app->patch('/rdvs/{id}/honorer', MarquerRdvHonoreAction::class)->setName('MarquerRdvHonore');
    $app->patch('/rdvs/{id}/non-honore', MarquerRdvNonHonoreAction::class)->setName('MarquerRdvNonHonore');

    $app->get('/praticiens/{id}/agenda', AgendaPraticienAction::class)->setName('AgendaPraticien');

    $app->get('/rdvs/{id}', ListerRdvAction::class)->setName('AfficherRdv');

    $app->get('/patients/{id}/rdvs', ListerRdvPatientAction::class)->setName('ListerRdvPatient');

    $app->post('/patients', CreerPatientAction::class)->setName('CreerPatient');
    $app->post('/signin', SigninAction::class)->setName('Signin');

    return $app;
};