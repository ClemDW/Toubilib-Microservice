<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

use toubilib\core\application\ports\api\ServicePatientInterface;
use toubilib\core\application\usecases\ServicePatient;
use toubilib\infra\repositories\PatientRepository;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;

// === Authn / Authz ===
use toubilib\api\provider\AuthProviderInterface;
use toubilib\api\provider\jwt\JwtAuthProvider;
use toubilib\api\provider\jwt\JwtManagerInterface;
use toubilib\api\provider\jwt\JwtManager;
use toubilib\api\middlewares\AuthnMiddleware;

use toubilib\api\actions\CreerPatientAction;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // safeLoad to avoid crash if missing

return [

    // ==============================
    // Configuration générale
    // ==============================
    "displayErrorDetails" => true,
    "logs.dir" => __DIR__ . "/../var/logs",
    "jwt.secret" => $_ENV["JWT_SECRET"] ?? "secret_defaut",
    "jwt.issuer" => $_ENV["JWT_ISSUER"] ?? "toubilib",
    "jwt.access_expiration" => $_ENV["JWT_ACCESS_EXPIRATION"] ?? 3600,
    "jwt.refresh_expiration" => $_ENV["JWT_REFRESH_EXPIRATION"] ?? 86400,

    // ==============================
    // Configurations PDO
    // ==============================
    "pat.pdo" => function (ContainerInterface $c) {
        $driver = $_ENV["pat.driver"] ?? "pgsql";
        $host = $_ENV["pat.host"] ?? "toubipat.db";
        $db = $_ENV["pat.database"] ?? "toubilib";
        $user = $_ENV["pat.username"] ?? "toubilib";
        $pass = $_ENV["pat.password"] ?? "toubilib";

        $dsn = sprintf(
            "%s:host=%s;dbname=%s",
            $driver,
            $host,
            $db
        );
        return new PDO(
            $dsn,
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    },

    // ==============================
    // Repositories
    // ==============================
    PatientRepository::class => fn(ContainerInterface $c) =>
        new PatientRepository($c->get("pat.pdo")),
    PatientRepositoryInterface::class => fn(ContainerInterface $c) =>
        $c->get(PatientRepository::class),

    // ==============================
    ServicePatientInterface::class => fn(ContainerInterface $c) =>
        new ServicePatient($c->get(PatientRepositoryInterface::class)),

    // ==============================
    // JWT Manager et Provider
    // ==============================
    JwtManagerInterface::class => function (ContainerInterface $c) {
        $jwt = new JwtManager(
            $c->get("jwt.secret"),
            (int)$c->get("jwt.access_expiration"),
            (int)$c->get("jwt.refresh_expiration")
        );
        $jwt->setIssuer($c->get("jwt.issuer"));
        return $jwt;
    },

    AuthProviderInterface::class => fn(ContainerInterface $c) =>
        new JwtAuthProvider(
            $c->get(JwtManagerInterface::class)
        ),
    
    // ==============================
    // Middleware
    // ==============================
    AuthnMiddleware::class => fn(ContainerInterface $c) =>
        new AuthnMiddleware($c->get(AuthProviderInterface::class)),

    // ==============================
    // Actions API
    // ==============================
    CreerPatientAction::class => fn(ContainerInterface $c) =>
        new CreerPatientAction($c->get(ServicePatientInterface::class)),

];
