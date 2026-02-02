<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

//use toubilib\core\application\ports\api\ServicePraticienInterface;
//use toubilib\core\application\usecases\ServicePraticien;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\usecases\ServiceRdv;
use toubilib\core\application\ports\api\ServicePatientInterface;
use toubilib\core\application\usecases\ServicePatient;
use toubilib\infra\repositories\RdvRepository;
//use toubilib\infra\repositories\PraticienRepository;
use toubilib\infra\repositories\PatientRepository;
use toubilib\infra\repositories\IndisponibiliteRepository;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\IndisponibiliteRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;


// === Authn / Authz ===
/*
use toubilib\core\application\usecases\ToubilibAuthnService;
use toubilib\core\application\ports\api\ToubilibAuthnServiceInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\infra\repositories\AuthRepository;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\api\provider\jwt\JwtAuthProvider;
use toubilib\api\provider\jwt\JwtManagerInterface;
use toubilib\api\provider\jwt\JwtManager;
use toubilib\api\middlewares\AuthnMiddleware;
*/

//use toubilib\api\actions\SigninAction;
//use toubilib\api\actions\ListerPraticienAction;
use toubilib\api\actions\ListerPraticienRdvAction;
use toubilib\api\actions\ConsulterRdvAction;
use toubilib\api\actions\CreerRdvAction;
use toubilib\api\actions\CreerPatientAction;
use toubilib\api\actions\CreerIndisponibiliteAction;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

return [

    // ==============================
    // Configuration générale
    // ==============================
    'displayErrorDetails' => true,
    'logs.dir' => __DIR__ . '/../var/logs',
    /*
    'jwt.secret' => $_ENV['JWT_SECRET'],
    'jwt.issuer' => $_ENV['JWT_ISSUER'] ?? 'toubilib',
    'jwt.access_expiration' => $_ENV['JWT_ACCESS_EXPIRATION'] ?? 3600,
    'jwt.refresh_expiration' => $_ENV['JWT_REFRESH_EXPIRATION'] ?? 86400,
    */

    // ==============================
    // Configurations PDO
    // ==============================
    /*
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
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    },*/

    'rdv.pdo' => function (ContainerInterface $c) {
        if (!isset($_ENV['rdv.driver'])) {
            throw new RuntimeException("Configuration RDV manquante dans .env");
        }
        $dsn = sprintf(
            "%s:host=%s;dbname=%s",
            $_ENV['rdv.driver'],
            $_ENV['rdv.host'],
            $_ENV['rdv.database']
        );
        return new PDO(
            $dsn,
            $_ENV['rdv.username'],
            $_ENV['rdv.password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    },

    'pat.pdo' => function (ContainerInterface $c) {
        if (!isset($_ENV['pat.driver'])) {
            throw new RuntimeException("Configuration PATIENT manquante dans .env");
        }
        $dsn = sprintf(
            "%s:host=%s;dbname=%s",
            $_ENV['pat.driver'],
            $_ENV['pat.host'],
            $_ENV['pat.database']
        );
        return new PDO(
            $dsn,
            $_ENV['pat.username'],
            $_ENV['pat.password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    },

    /*
    'auth.pdo' => function (ContainerInterface $c) {
        if (!isset($_ENV['auth.driver'])) {
            throw new RuntimeException("Configuration AUTH manquante dans .env");
        }
        $dsn = sprintf(
            "%s:host=%s;dbname=%s",
            $_ENV['auth.driver'],
            $_ENV['auth.host'],
            $_ENV['auth.database']
        );
        return new PDO(
            $dsn,
            $_ENV['auth.username'],
            $_ENV['auth.password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    },*/

    // ==============================
    // Repositories
    // ==============================
    'praticien.client' => function (ContainerInterface $c) {
        return new \GuzzleHttp\Client([
            'base_uri' => 'http://service-praticiens.toubilib',
            'timeout'  => 5.0,
        ]);
    },

    \toubilib\infra\adapters\HttpPraticienRepository::class => fn(ContainerInterface $c) =>
        new \toubilib\infra\adapters\HttpPraticienRepository($c->get('praticien.client')),

    PraticienRepositoryInterface::class => fn(ContainerInterface $c) =>
        $c->get(\toubilib\infra\adapters\HttpPraticienRepository::class),

    RdvRepository::class => fn(ContainerInterface $c) =>
        new RdvRepository($c->get('rdv.pdo')),

    PatientRepository::class => fn(ContainerInterface $c) =>
        new PatientRepository($c->get('pat.pdo')),
    PatientRepositoryInterface::class => fn(ContainerInterface $c) =>
        $c->get(PatientRepository::class),

    IndisponibiliteRepository::class => fn(ContainerInterface $c) =>
        new IndisponibiliteRepository($c->get('rdv.pdo')),
    IndisponibiliteRepositoryInterface::class => fn(ContainerInterface $c) =>
        $c->get(IndisponibiliteRepository::class),

    // Repository pour Authentification
    /*AuthRepository::class => fn(ContainerInterface $c) =>
        new AuthRepository($c->get('auth.pdo')), // à adapter selon ta table users
    AuthRepositoryInterface::class => fn(ContainerInterface $c) =>
        $c->get(AuthRepository::class),*/ 

    // ==============================
    /*ServicePraticienInterface::class => fn(ContainerInterface $c) =>
        new ServicePraticien($c->get(PraticienRepositoryInterface::class)),*/

    ServiceRdvInterface::class => fn(ContainerInterface $c) =>
        new ServiceRdv(
            $c->get(RdvRepository::class),
            $c->get(PraticienRepositoryInterface::class),
            $c->get(PatientRepository::class),
            $c->get(IndisponibiliteRepositoryInterface::class)
        ),

    ServicePatientInterface::class => fn(ContainerInterface $c) =>
        new ServicePatient($c->get(PatientRepositoryInterface::class)),

    // Service d’authentification
    /*ToubilibAuthnServiceInterface::class => fn(ContainerInterface $c) =>
        new ToubilibAuthnService($c->get(AuthRepositoryInterface::class)),*/

    // ==============================
    // JWT Manager et Provider
    // ==============================
    /*JwtManagerInterface::class => function (ContainerInterface $c) {
        $jwt = new JwtManager(
            $c->get('jwt.secret'),
            (int)$c->get('jwt.access_expiration'),
            (int)$c->get('jwt.refresh_expiration')
        );
        $jwt->setIssuer($c->get('jwt.issuer'));
        return $jwt;
    },

    AuthProviderInterface::class => fn(ContainerInterface $c) =>
        new JwtAuthProvider(
            $c->get(ToubilibAuthnServiceInterface::class),
            $c->get(JwtManagerInterface::class)
        ),*/

    // ==============================
    // Actions API
    // ==============================
    /*ListerPraticienAction::class => fn(ContainerInterface $c) =>
        new ListerPraticienAction($c->get(ServicePraticienInterface::class)),*/

    ListerPraticienRdvAction::class => fn(ContainerInterface $c) =>
        new ListerPraticienRdvAction($c->get(ServiceRdvInterface::class)),

    ConsulterRdvAction::class => fn(ContainerInterface $c) =>
        new ConsulterRdvAction($c->get(ServiceRdvInterface::class)),
    CreerPatientAction::class => fn(ContainerInterface $c) =>
        new CreerPatientAction($c->get(ServicePatientInterface::class)),

    CreerIndisponibiliteAction::class => fn(ContainerInterface $c) =>
        new CreerIndisponibiliteAction($c->get(ServiceRdvInterface::class)),

    \toubilib\api\actions\MajStatusRdvAction::class => fn(ContainerInterface $c) =>
        new \toubilib\api\actions\MajStatusRdvAction($c->get(ServiceRdvInterface::class)),

    // Route /signin
    // Route /signin
    /*SigninAction::class => fn(ContainerInterface $c) =>
        new SigninAction($c->get(AuthProviderInterface::class)),*/

    // ==============================
    // Service d'autorisation
    // ==============================
    \toubilib\core\application\ports\api\AuthzRdvServiceInterface::class => fn(ContainerInterface $c) =>
        new \toubilib\core\application\usecases\AuthzRdvService(
            $c->get(RdvRepository::class)
        ),

    // ==============================
    // Middlewares
    // ==============================
    \toubilib\api\middlewares\AuthzMiddleware::class => fn(ContainerInterface $c) =>
        new \toubilib\api\middlewares\AuthzMiddleware(
            $c->get(\toubilib\core\application\ports\api\AuthzRdvServiceInterface::class)
        ),

    /*AuthnMiddleware::class => fn(ContainerInterface $c) =>
        new AuthnMiddleware($c->get(AuthProviderInterface::class)),*/
];
