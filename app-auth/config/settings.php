<?php

use Psr\Container\ContainerInterface;
use toubilib\core\domain\entities\auth\AuthServiceInterface;
use toubilib\core\application\usecases\AuthService;
use toubilib\core\application\ports\spi\repositoryInterfaces\UserRepositoryInterface;
use toubilib\infra\repositories\PDOUserRepository;
use toubilib\api\actions\SigninAction;
use toubilib\api\actions\RefreshTokenAction;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\api\provider\JwtAuthProvider;
use toubilib\api\middlewares\AuthnMiddleware;
use toubilib\api\actions\ValidateTokenAction;

return [

    // settings
    "displayErrorDetails" => true,
    "logs.dir" => __DIR__ . "/../../var/logs",
    "env.config" => __DIR__ . "/toubilib.db.ini",


    SigninAction::class => function (ContainerInterface $c) {
        return new SigninAction($c->get(AuthProviderInterface::class));
    },

    RefreshTokenAction::class => function (ContainerInterface $c) {
        return new RefreshTokenAction($c->get(AuthProviderInterface::class));
    },

    ValidateTokenAction::class => function (ContainerInterface $c) {
        return new ValidateTokenAction($c->get(AuthProviderInterface::class));
    },

    AuthServiceInterface::class => function (ContainerInterface $c) {
        return new AuthService($c->get(UserRepositoryInterface::class));
    },

    AuthProviderInterface::class => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get("env.config"));
        $secret = $config["auth.jwt.key"] ?? getenv("AUTH_JWT_KEY") ?? null;
        if (!$secret) {
            $secret = "your_secret_key"; 
        }

        return new JwtAuthProvider(
            $c->get(AuthServiceInterface::class),
            $secret,
            "HS256",
            3600,
            86400
        );
    },

    AuthnMiddleware::class => function (ContainerInterface $c) {
        return new AuthnMiddleware($c->get(AuthProviderInterface::class));
    },

    UserRepositoryInterface::class => fn(ContainerInterface $c) => new PDOUserRepository($c->get("auth.pdo")),

    "auth.pdo" => function (ContainerInterface $c) {
        $config = parse_ini_file($c->get("env.config"));
        $dsn = "{$config["driver"]}:host={$config["host"]};dbname={$config["database"]}";
        $user = $config["username"];
        $password = $config["password"];
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

];
