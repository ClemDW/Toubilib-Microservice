<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\api\ServiceAuthnInterface;
use toubilib\core\application\ports\api\ServicePraticienInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\application\usecases\ServiceAuthn;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\core\application\usecases\ServiceRdv;
use toubilib\infra\repositories\AuthnRepository;
use toubilib\infra\repositories\PatientRepository;
use toubilib\infra\repositories\PraticienRepository;
use toubilib\infra\repositories\RdvRepository;

return [

    'pdo.prat' => function(ContainerInterface $c): PDO {
        $db = $c->get('settings')['db']['prat'];
        return new PDO(
            "{$db['driver']}:host={$db['host']};dbname={$db['dbname']}",
            $db['user'],
            $db['password']
        );
    },

    'pdo.pat' => function(ContainerInterface $c): PDO {
        $db = $c->get('settings')['db']['pat'];
        return new PDO(
            "{$db['driver']}:host={$db['host']};dbname={$db['dbname']}",
            $db['user'],
            $db['password']
        );
    },

    'pdo.rdv' => function(ContainerInterface $c): PDO {
        $db = $c->get('settings')['db']['rdv'];
        return new PDO(
            "{$db['driver']}:host={$db['host']};dbname={$db['dbname']}",
            $db['user'],
            $db['password']
        );
    },

    'pdo.auth' => function(ContainerInterface $c): PDO {
        $db = $c->get('settings')['db']['auth'];
        return new PDO(
            "{$db['driver']}:host={$db['host']};dbname={$db['dbname']}",
            $db['user'],
            $db['password']
        );
    },

    PraticienRepositoryInterface::class => function($c) {
        return new PraticienRepository($c->get('pdo.prat'));
    },

    ServicePraticienInterface::class => function($c) {
        return new ServicePraticien($c->get(PraticienRepositoryInterface::class));
    },

    RdvRepositoryInterface::class => function($c) {
        return new RdvRepository($c->get('pdo.rdv'));
    },

    ServiceRdvInterface::class => function($c) {
        return new ServiceRdv($c->get(RdvRepositoryInterface::class),$c->get(PraticienRepositoryInterface::class),$c->get(PatientRepositoryInterface::class));
    },

    PatientRepositoryInterface::class => function($c) {
        return new PatientRepository($c->get('pdo.pat'));
    },

    AuthnRepository::class => function($c) {
        return new AuthnRepository($c->get('pdo.auth'));
    },

    ServiceAuthnInterface::class => function($c) {
        return new ServiceAuthn($c->get(AuthnRepository::class));
    }
];
