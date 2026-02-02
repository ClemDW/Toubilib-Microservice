<?php

namespace toubilib\api\actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\api\dtos\RdvDTO;

class ListerHistoriquePatientAction
{
    private ServiceRdvInterface $rdvService;

    public function __construct(ServiceRdvInterface $rdvService)
    {
        $this->rdvService = $rdvService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $patientId = $args['id'] ?? null;

            if (!$patientId) {
                $error = ['error' => 'ID du patient requis'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $rdvs = $this->rdvService->getHistoriquePatient($patientId);

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
            $error = ['error' => 'Erreur lors de la récupération de l\'historique'];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
