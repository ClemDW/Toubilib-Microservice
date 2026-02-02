<?php

namespace toubilib\api\actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\api\dtos\RdvDTO;

class ListerPraticienRdvAction
{
    private ServiceRdvInterface $rdvService;

    public function __construct(ServiceRdvInterface $rdvService)
    {
        $this->rdvService = $rdvService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $praticienId = $args['id'] ?? null; // récupérer depuis la route
            $dateDebut   = $queryParams['date_debut'] ?? $queryParams['dateDebut'] ?? null;
            $dateFin     = $queryParams['date_fin'] ?? $queryParams['dateFin'] ?? null;

            if (!$praticienId || !$dateDebut || !$dateFin) {
                $error = ['error' => 'ID du praticien et date_debut/date_fin sont requis'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            try {
                $dateDebut = new \DateTime($dateDebut);
                $dateFin   = new \DateTime($dateFin);
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode(['error' => 'Format de date invalide']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Ensuite récupérer les rdvs via le service
            $rdvs = $this->rdvService->getCreneauxOccupes($praticienId, $dateDebut, $dateFin);


            $rdvData = [];
            foreach ($rdvs as $rdv) {
                $rdvData[] = new RdvDTO(
                    $rdv->getId(),
                    $rdv->getPraticienId(),
                    $rdv->getPatientId(),
                    $rdv->getPatientEmail(),
                    $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                    $rdv->getStatus(),
                    $rdv->getDuree(),
                    $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
                    $rdv->getDateCreation()?->format('Y-m-d H:i:s'),
                    $rdv->getMotifVisite()
                );
            }


            $response->getBody()->write(json_encode($rdvData)); 
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (Exception $e) {
            $error = [
                'error' => 'Erreur lors de la récupération des rendez-vous',
                'details' => $e->getMessage()
            ];
            $response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
