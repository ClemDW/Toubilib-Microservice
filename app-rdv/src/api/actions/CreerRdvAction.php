<?php

namespace toubilib\api\actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\api\dtos\InputRendezVousDTO;


class CreerRdvAction
{
    private ServiceRdvInterface $rdvService;

    public function __construct(ServiceRdvInterface $rdvService)
    {
        $this->rdvService = $rdvService;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $body = (array)$request->getParsedBody();

            // Vérification des champs
            $required = ['praticienId', 'patientId', 'dateHeure', 'motifVisite', 'duree'];
            foreach ($required as $field) {
                if (empty($body[$field] ?? null)) {
                    $response->getBody()->write(json_encode(['error' => 'Champs manquants']));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
            }

            // Création du DTO
            $dto = new InputRendezVousDTO(
                $body['praticienId'],
                $body['patientId'],
                new \DateTimeImmutable($body['dateHeure']),
                $body['motifVisite'],
                (int)$body['duree']
            );

            $rdvCree = $this->rdvService->creerRendezVous($dto);

            $payload = [
                'message' => 'Rendez-vous créé avec succès',
                'rdv' => [
                    'id' => $rdvCree->getId(),
                    'praticienId' => $rdvCree->getPraticienId(),
                    'patientId' => $rdvCree->getPatientId(),
                    'dateDebut' => $rdvCree->getDateHeureDebut()->format('Y-m-d H:i:s'),
                    'dateFin' => $rdvCree->getDateHeureFin()?->format('Y-m-d H:i:s'),
                    'duree' => $rdvCree->getDuree(),
                    'statut' => $rdvCree->getStatus(),
                    'motifVisite' => $rdvCree->getMotifVisite()
                ]
            ];

            $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\DomainException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(422);

        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Erreur interne', 'details' => $e->getMessage()], JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
