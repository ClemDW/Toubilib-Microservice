<?php

namespace toubilib\api\middlewares;

use toubilib\api\services\AuthzPraticienService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpUnauthorizedException;

class AuthzPraticienMiddleware {
    private AuthzPraticienService $authzPraticien;

    public function __construct(AuthzPraticienService $authzPraticien) {
        $this->authzPraticien = $authzPraticien;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $authDto = $request->getAttribute('authenticated_user') ?? throw new HttpUnauthorizedException($request, "not authenticated");
        $routeContext = RouteContext::fromRequest($request);

        $this->authzPraticien->isGranted($authDto->ID, $authDto->role, $routeContext->getRoute()->getArgument('id'));

        return $handler->handle($request);
    }
}
