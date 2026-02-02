<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use toubilib\core\application\ports\api\AuthzRdvServiceInterface;
use toubilib\core\domain\entities\auth\User;
use toubilib\core\domain\exceptions\AuthorizationException;
use toubilib\core\domain\exceptions\AuthenticationException;


class AuthzMiddleware implements MiddlewareInterface
{
    private AuthzRdvServiceInterface $authzService;

    public function __construct(AuthzRdvServiceInterface $authzService)
    {
        $this->authzService = $authzService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            // Extraction et décodage du token JWT
            $user = $this->extractUser($request);
            
            if (!$user) {
                throw new AuthenticationException("Token JWT manquant ou invalide");
            }

            // Récupération des informations de la route
            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            
            if (!$route) {
                return $handler->handle($request);
            }

            // Vérification de l'autorisation selon la route
            $this->checkAuthorization($request, $user, $route->getArguments());

            // Ajout de l'utilisateur dans les attributs de la requête pour l'action
            $request = $request->withAttribute('user', $user);

            return $handler->handle($request);

        } catch (AuthenticationException $e) {
            return $this->createErrorResponse($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->createErrorResponse($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->createErrorResponse('Erreur d\'autorisation: ' . $e->getMessage(), 500);
        }
    }

    
    private function extractUser(ServerRequestInterface $request): ?User
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return null;
        }

        if (!preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];
        
        // Décodage du payload JWT 
        $payload = $this->decodeJwtPayload($token);
        
        if (!$payload) {
            return null;
        }

        return User::fromJwtPayload($payload);
    }

    
    private function decodeJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }

        $payload = $parts[1];
        
        // Décodage base64url
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $payload = base64_decode($payload);
        
        if ($payload === false) {
            return null;
        }

        $decoded = json_decode($payload, true);
        
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Vérifie l'autorisation selon la route appelée
     */
    private function checkAuthorization(
        ServerRequestInterface $request,
        User $user,
        array $routeArguments
    ): void {
        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();

        // Route: GET /praticiens/{id}/agenda
        if (preg_match('#^/praticiens/([^/]+)/agenda$#', $uri, $matches)) {
            $praticienId = $matches[1];
            $this->authzService->canAccessPraticienAgenda($user, $praticienId);
            return;
        }

        // Route: GET /rdvs/{id}
        if (preg_match('#^/rdvs/([^/]+)$#', $uri, $matches) && $method === 'GET') {
            $rdvId = $matches[1];
            $this->authzService->canAccessRendezVousDetail($user, $rdvId);
            return;
        }

        // Route: POST /rdvs
        if ($uri === '/rdvs' && $method === 'POST') {
            $contentType = $request->getHeaderLine('Content-Type');
            if (strpos($contentType, 'application/json') !== false) {
                $body = $request->getBody();
                $body->rewind();
                $rdvData = json_decode($body->getContents(), true) ?? [];
                $body->rewind();
            } else {
                $rdvData = $request->getParsedBody() ?? [];
            }
            $this->authzService->canCreateRendezVous($user, $rdvData);
            return;
        }

    }

    /**
     * Crée une réponse d'erreur JSON
     */
    private function createErrorResponse(string $message, int $statusCode): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        
        $code = match($statusCode) {
            401 => 'AUTHENTICATION_ERROR',
            403 => 'ACCESS_DENIED',
            default => 'ERROR'
        };

        $payload = json_encode([
            'error' => $message,
            'code' => $code
        ]);

        $response->getBody()->write($payload);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
