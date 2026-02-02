<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

use toubilib\core\application\ports\api\ServicePraticienInterface;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\infra\repositories\PraticienRepository;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;

use toubilib\api\actions\ListerPraticienAction;
use toubilib\api\actions\RechercherPraticienAction;
use toubilib\api\actions\AfficherPraticienAction;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv ->load();

return [

    // ==============================
    // Configuration générale
    // ==============================
    'displayErrorDetails' => true,
    'logs.dir' => __DIR__ . '/../var/logs',
    
    // ==============================
    // Configuration PDO Praticien
    // ==============================
    'praticien.pdo' => function (ContainerInterface $c) {
        $dsn = sprintf(
            "%s:host=%s;dbname=%s",
            $_ENV['prat.driver'],
            $_ENV['prat.host'],
            $_ENV['prat.database']
        );
        return new PDO(
            $dsn,
            $_ENV['prat.username'],
            $_ENV['prat.password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    },

    // ==============================
    // Repositories
    // ==============================
    PraticienRepository::class => fn(ContainerInterface $c) =>
        new PraticienRepository($c->get('praticien.pdo')),
    PraticienRepositoryInterface::class => fn(ContainerInterface $c) =>
        $c->get(PraticienRepository::class),

    // ==============================
    // Services
    // ==============================
    ServicePraticienInterface::class => fn(ContainerInterface $c) =>
        new ServicePraticien($c->get(PraticienRepositoryInterface::class)),

    // ==============================
    // Actions API
    // ==============================
    ListerPraticienAction::class => fn(ContainerInterface $c) =>
        new ListerPraticienAction($c->get(ServicePraticienInterface::class)),

    RechercherPraticienAction::class => fn(ContainerInterface $c) =>
        new RechercherPraticienAction($c->get(ServicePraticienInterface::class)),
        
    AfficherPraticienAction::class => fn(ContainerInterface $c) =>
        new AfficherPraticienAction($c->get(ServicePraticienInterface::class)),

];
