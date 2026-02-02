<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;
use toubilib\core\application\ports\api\dtos\InputRendezVousDTO;

class ValidateCreerRdvMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        // Récupérer les données selon le Content-Type
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode((string) $request->getBody(), true);
        } else {
            // Données de formulaire (POST)
            $data = $request->getParsedBody();
        }
        
        error_log('Données reçues : ' . json_encode($data));
        
        // Validation basique
        if (!isset($data['praticienId'], $data['patientId'], $data['dateHeure'], $data['motifVisite'], $data['duree'])) {
            $resp = new SlimResponse();
            $resp->getBody()->write(json_encode(['error' => 'Champs manquants']));
            return $resp->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Convertir le format datetime-local vers Y-m-d H:i:s si nécessaire
        $dateHeure = $data['dateHeure'];
        if (strpos($dateHeure, 'T') !== false) {
            // Format datetime-local : 2024-01-15T14:30
            $dateHeure = str_replace('T', ' ', $dateHeure) . ':00';
        }
        
        if (!\DateTime::createFromFormat('Y-m-d H:i:s', $dateHeure)) {
            $resp = new SlimResponse();
            $resp->getBody()->write(json_encode(['error' => 'Format dateHeure invalide, attendu Y-m-d H:i:s']));
            return $resp->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Convertir la durée en entier si c'est une chaîne
        $duree = is_string($data['duree']) ? (int)$data['duree'] : $data['duree'];
        
        if (!is_int($duree) || $duree <= 0) {
            $resp = new SlimResponse();
            $resp->getBody()->write(json_encode(['error' => 'Durée invalide']));
            return $resp->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Construire le DTO
        $dto = new InputRendezVousDTO(
            $data['praticienId'],
            $data['patientId'],
            new \DateTimeImmutable($dateHeure),
            $data['motifVisite'],
            $duree
        );
        
        // Ajouter le DTO dans les attributs de la requête
        $request = $request->withAttribute('inputRdvDTO', $dto);
        return $handler->handle($request);
    }
}