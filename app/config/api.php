<?php
declare(strict_types=1);

use toubilib\api\actions\AfficherPraticienAction;
use toubilib\api\actions\AgendaPraticienAction;
use toubilib\api\actions\AnnulerRdvAction;
use toubilib\api\actions\ListerPraticiensAction;
use toubilib\api\actions\AfficherRdvAction;
use toubilib\api\actions\CreerRdvAction;
use toubilib\api\actions\ListerRdvAction;
use toubilib\api\actions\ListerRdvPatientAction;
use toubilib\api\actions\MarquerRdvHonoreAction;
use toubilib\api\actions\MarquerRdvNonHonoreAction;
use toubilib\api\actions\SigninAction;
use toubilib\api\provider\AuthnProviderInterface;
use toubilib\core\application\ports\api\ServicePraticienInterface;

return [
    ListerPraticiensAction::class => function ($c) {
        return new ListerPraticiensAction($c->get(ServicePraticienInterface::class));
    },
    AfficherPraticienAction::class => function ($c) {
        return new AfficherPraticienAction($c->get(ServicePraticienInterface::class));
    },
    AfficherRdvAction::class => function ($c) {
        return new AfficherRdvAction($c->get(ServicePraticienInterface::class));
    },
    AgendaPraticienAction::class => function ($c) {
        return new AgendaPraticienAction($c->get(ServicePraticienInterface::class));
    },
    AnnulerRdvAction::class => function ($c) {
        return new AnnulerRdvAction($c->get(ServicePraticienInterface::class));
    },
    CreerRdvAction::class => function ($c) {
        return new CreerRdvAction($c->get(ServicePraticienInterface::class));
    },
    ListerRdvAction::class => function ($c) {
        return new ListerRdvAction($c->get(ServicePraticienInterface::class));
    },
    ListerRdvPatientAction::class => function ($c) {
        return new ListerRdvPatientAction($c->get(ServicePraticienInterface::class));
    },
    MarquerRdvHonoreAction::class => function ($c) {
        return new MarquerRdvHonoreAction($c->get(ServicePraticienInterface::class));
    },
    MarquerRdvNonHonoreAction::class => function ($c) {
        return new MarquerRdvNonHonoreAction($c->get(ServicePraticienInterface::class));
    },
    SigninAction::class => function ($c) {
        return new SigninAction($c->get(AuthnProviderInterface::class));
    },
];
