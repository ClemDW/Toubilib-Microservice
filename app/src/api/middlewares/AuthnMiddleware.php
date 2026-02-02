<?php

namespace toubilib\api\middlewares;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;
use toubilib\api\provider\AuthProviderInterface;


class AuthnMiddleware {
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider) {
        $this->authProvider = $authProvider;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $token_line = $request->getHeaderLine('Authorization');
        
        if (empty($token_line)) {
            throw new HttpUnauthorizedException($request, "missing authorization header");
        }
        
        $result = sscanf($token_line, "Bearer %s");
        
        if ($result === null || !isset($result[0]) || empty($result[0])) {
            throw new HttpUnauthorizedException($request, "invalid authorization header format");
        }
        
        $token = $result[0];

        try {
            $authDto = $this->authProvider->getSignedInUser($token);
        } catch (\Exception $e) {
            throw new HttpUnauthorizedException($request, "invalid or expired jwt token");
        }

        $request = $request->withAttribute('authenticated_user', $authDto);
        return $handler->handle($request);
    }
}