<?php

namespace toubilib\gateway\application\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;

class RdvGatewayAction
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $uri = $request->getUri()
            ->withScheme('http')
            ->withHost('service-rdv.toubilib')
            ->withPort(80);
            
        $request = $request->withUri($uri);

        try {
            $guzzleResponse = $this->client->send($request);
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getBody()->getContents();
            
            // Gestion des erreurs d'authentification (401)
            if ($statusCode === 401) {
                throw new HttpUnauthorizedException($request, $this->extractErrorMessage($responseBody, "Non authentifié"));
            }
            
            // Gestion des erreurs d'autorisation (403)
            if ($statusCode === 403) {
                throw new HttpForbiddenException($request, $this->extractErrorMessage($responseBody, "Accès refusé"));
            }
            
            // Gestion des erreurs 404
            if ($statusCode === 404) {
                throw new HttpNotFoundException($request, "Ressource non trouvée");
            }
            
            // Pour les autres erreurs client (4xx), on retransmet la réponse
            $response->getBody()->write($responseBody);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($statusCode);
                
        } catch (\Exception $e) {
            // Si c'est déjà une exception HTTP, on la relance
            if ($e instanceof HttpNotFoundException || 
                $e instanceof HttpUnauthorizedException || 
                $e instanceof HttpForbiddenException) {
                throw $e;
            }
            
            $response->getBody()->write(json_encode([
                'error' => 'Gateway Error',
                'message' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }

        foreach ($guzzleResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $response = $response->withHeader($name, $value);
            }
        }
        
        $response->getBody()->write($guzzleResponse->getBody()->getContents());
        
        return $response->withStatus($guzzleResponse->getStatusCode());
    }
    
    /**
     * Extrait le message d'erreur d'une réponse JSON
     */
    private function extractErrorMessage(string $responseBody, string $defaultMessage): string
    {
        $decoded = json_decode($responseBody, true);
        if (is_array($decoded) && isset($decoded['error'])) {
            return $decoded['error'];
        }
        return $defaultMessage;
    }
}
