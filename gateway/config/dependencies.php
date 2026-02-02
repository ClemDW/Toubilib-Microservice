<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

use toubilib\gateway\application\actions\PraticienGatewayAction;
use toubilib\gateway\application\actions\RdvGatewayAction;
use toubilib\api\actions\SigninAction;
use toubilib\gateway\application\actions\AuthGatewayAction;

return [
    Client::class => function (ContainerInterface $c) {
        return new Client();
    },

    'praticien.client' => function (ContainerInterface $c) {
        return new Client();
    },

    PraticienGatewayAction::class => function (ContainerInterface $c) {
        return new PraticienGatewayAction($c->get('praticien.client'));
    },

    'rdv.client' => function (ContainerInterface $c) {
        return new Client();
    },

    RdvGatewayAction::class => function (ContainerInterface $c) {
        return new RdvGatewayAction($c->get('rdv.client'));
    },


    'auth.client' => function (ContainerInterface $c) {
        return new Client();
    },

    AuthGatewayAction::class => function (ContainerInterface $c) {
        return new AuthGatewayAction($c->get('auth.client'));
    },

];
