<?php
declare(strict_types=1);

use DI\ContainerBuilder;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Container\ContainerInterface;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    ClientInterface::class => function (ContainerInterface $c) {
        return new Client(['base_uri' => 'http://api.toubilib']);
    },

    'praticiens.client' => function () {
        return new Client(['base_uri' => 'http://api.praticiens']);
    },

    GenericGetPraticiensAction::class => function ($c) {
        $client = $c->get('praticiens.client');
        return new GenericGetPraticiensAction($client);
    },

    'rdv.client' => function () {
        return new Client(['base_uri' => 'http://api.rdv']);
    },

    ListerPraticienRdvsAction::class => function ($c) {
        return new ListerPraticienRdvsAction($c->get('rdv.client'));
    },
]);

return $containerBuilder->build();
