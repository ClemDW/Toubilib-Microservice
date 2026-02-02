<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use Exception;

class AnnulerRdvAction
{
    private ServiceRdvInterface $serviceRendezVous;

    public function __construct(ServiceRdvInterface $serviceRendezVous)
    {
        $this->serviceRendezVous = $serviceRendezVous;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $idRdv = $args['id'] ?? null;

        if (!$idRdv) {
            $response->getBody()->write(json_encode(['error' => 'ID du rendez-vous manquant']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $rdv = $this->serviceRendezVous->getRdvById($idRdv);
            if (!$rdv) {
                 throw new Exception("Rendez-vous introuvable.");
            }

            $this->serviceRendezVous->annulerRendezVous($idRdv);

            $response->getBody()->write(json_encode([
            'message' => 'Rendez-vous annulé avec succès',
            'id'      => $idRdv,
            '_links'  => [
                'self'     => ['href' => "/rdvs/{$idRdv}"],
                'praticien'=> ['href' => "/praticiens/{$rdv->getPraticienId()}"],
                'patient'  => ['href' => "/patients/{$rdv->getPatientId()}"]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
