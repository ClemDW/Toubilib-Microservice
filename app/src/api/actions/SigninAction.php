<?php

namespace toubilib\api\actions;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\api\provider\AuthnProvider;
use toubilib\core\application\ports\api\dtos\CredentialsDTO;

class SigninAction
{
    private AuthnProvider $authnProvider;

    public function __construct(AuthnProvider $authnProvider)
    {
        $this->authnProvider = $authnProvider;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $payload = $this->authnProvider->signin($request->getAttribute('credentials'));
            $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['message' => 'Email ou mot de passe incorrect'], JSON_PRETTY_PRINT));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
    }
}