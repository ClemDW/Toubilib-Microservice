<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use toubilib\core\domain\entities\auth\AuthzServiceInterface;
use toubilib\core\domain\entities\auth\UserProfile;
use toubilib\core\domain\exceptions\AuthorizationException;

/**
 * Middleware d'autorisation pour les rendez-vous
 * Vérifie que l'utilisateur authentifié a le droit d'exécuter l'opération
 */
class AuthzMiddleware implements MiddlewareInterface
{
    private AuthzServiceInterface $authzService;

    public function __construct(AuthzServiceInterface $authzService)
    {
        $this->authzService = $authzService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            // Récupération du profil utilisateur injecté par AuthnMiddleware
            $userProfile = $request->getAttribute('userProfile');
            
            if (!$userProfile instanceof UserProfile) {
                return $this->createErrorResponse('Utilisateur non authentifié', 401);
            }

            // Récupération des informations de la route
            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            
            if (!$route) {
                return $this->createErrorResponse('Route non trouvée', 404);
            }

            $routeName = $route->getName();
            $routeArguments = $route->getArguments();

            // Détermination et exécution de la vérification d'autorisation selon la route
            $this->checkAuthorization($request, $userProfile, $routeName, $routeArguments);

            // Si tout est OK, passage à l'action suivante
            return $handler->handle($request);

        } catch (AuthorizationException $e) {
            return $this->createErrorResponse($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->createErrorResponse('Erreur d\'autorisation', 500);
        }
    }

    /**
     * Vérifie l'autorisation selon la route appelée
     */
    private function checkAuthorization(
        ServerRequestInterface $request,
        UserProfile $userProfile, 
        ?string $routeName, 
        array $routeArguments
    ): void {
        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();

        // Vérification selon le pattern de l'URI et la méthode HTTP
        if (preg_match('#^/praticien/([^/]+)/agenda$#', $uri, $matches)) {
            // Route: GET /praticien/{id}/agenda
            $praticienId = $matches[1];
            $this->authzService->canAccessPraticienAgenda($userProfile, $praticienId);
            
        } elseif (preg_match('#^/rdvs/(\d+)$#', $uri, $matches)) {
            // Route: GET /rdvs/{id}
            $rdvId = $matches[1];
            $this->authzService->canAccessRendezVousDetail($userProfile, $rdvId);
                     
        } elseif ($uri === '/rdvs/creer' && $method === 'POST') {
            // Route: POST /rdvs/creer
            $rdvData = $request->getParsedBody() ?? [];
            $this->authzService->canCreateRendezVous($userProfile, $rdvData);
            
        } elseif (preg_match('#^/rdvs/(\d+)/annuler$#', $uri, $matches) && $method === 'POST') {
            // Route: POST /rdvs/{id}/annuler
            $rdvId = $matches[1];
            $this->authzService->canCancelRendezVous($userProfile, $rdvId);
            
        } elseif ($uri === '/rdvs/occupe' && $method === 'GET') {
            // Route: GET /rdvs/occupe - Réservé aux praticiens
            if (!$userProfile->isPraticien()) {
                throw new AuthorizationException("Accès refusé - Réservé aux praticiens");
            }
            
        } else {
            
            return;
        }
    }

    /**
     * Crée une réponse d'erreur JSON
     */
    private function createErrorResponse(string $message, int $statusCode): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        
        $payload = json_encode([
            'error' => $message,
            'code' => $statusCode === 403 ? 'ACCESS_DENIED' : 'AUTHORIZATION_ERROR'
        ]);

        $response->getBody()->write($payload);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}