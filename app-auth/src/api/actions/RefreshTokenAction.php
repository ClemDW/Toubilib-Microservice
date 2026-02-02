<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\core\domain\exceptions\AuthenticationException;


class RefreshTokenAction
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['refreshToken']) || empty($data['refreshToken'])) {
                $payload = json_encode([
                    'error' => 'Refresh token requis'
                ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            if (!is_string($data['refreshToken'])) {
                $payload = json_encode([
                    'error' => 'Le refresh token doit être une chaîne de caractères'
                ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            $authToken = $this->authProvider->refresh($data['refreshToken']);

            $payload = json_encode([
                'success' => true,
                'message' => 'Tokens rafraîchis avec succès',
                'data' => $authToken->toArray()
            ]);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (AuthenticationException $e) {
            $payload = json_encode([
                'error' => $e->getMessage(),
                'code' => 'TOKEN_REFRESH_FAILED'
            ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
                
        } catch (\Exception $e) {
            $payload = json_encode([
                'error' => 'Erreur interne du serveur',
                'code' => 'INTERNAL_ERROR'
            ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}