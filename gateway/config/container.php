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
]);

return $containerBuilder->build();
