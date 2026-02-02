<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\core\domain\exceptions\AuthenticationException;

/**
 * Middleware d'authentification JWT
 */
class AuthnMiddleware implements MiddlewareInterface
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            // Extraction du token JWT depuis l'en-tête Authorization
            $token = $this->extractTokenFromRequest($request);
            
            if (!$token) {
                return $this->createErrorResponse('Token d\'authentification manquant', 401);
            }

            // Validation du token via le provider
            $authTokenDTO = $this->authProvider->validateToken($token);
            
            // Ajout du profil utilisateur dans les attributs de la requête
            $request = $request->withAttribute('userProfile', $authTokenDTO->getUserProfile());
            // Ajout du token d'authentification dans les attributs de la requête
            $request = $request->withAttribute('authToken', $authTokenDTO);
            

            // Passage à l'action suivante
            return $handler->handle($request);

        } catch (AuthenticationException $e) {
            return $this->createErrorResponse($e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->createErrorResponse('Erreur d\'authentification', 500);
        }
    }

    /**
     * Extrait le token JWT de l'en-tête Authorization
     */
    private function extractTokenFromRequest(ServerRequestInterface $request): ?string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return null;
        }

        // Format attendu: "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        return $matches[1] ?? null;
    }

    /**
     * Crée une réponse d'erreur JSON
     */
    private function createErrorResponse(string $message, int $statusCode): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        
        $payload = json_encode([
            'error' => $message,
            'code' => 'AUTHENTICATION_REQUIRED'
        ]);

        $response->getBody()->write($payload);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}