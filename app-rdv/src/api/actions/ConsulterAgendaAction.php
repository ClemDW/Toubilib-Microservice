<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\domain\entities\praticien\Rdv;
use toubilib\core\domain\entities\praticien\Indisponibilite;

class ConsulterAgendaAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $praticienId = $args['id'] ?? null;
        if (!$praticienId) {
            $response->getBody()->write(json_encode(['error' => 'ID du praticien manquant']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Contrôle d'accès désactivé
        // $user = $request->getAttribute('authenticated_user');
        // if (!$user || !isset($user->role) || $user->role != 10) {
        //    $response->getBody()->write(json_encode(['error' => 'Accès interdit : rôle insuffisant']));
        //    return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        // }

        $queryParams = $request->getQueryParams();
        try {
            $dateDebut = isset($queryParams['dateDebut']) ? new \DateTime($queryParams['dateDebut']) : null;
            $dateFin   = isset($queryParams['dateFin']) ? new \DateTime($queryParams['dateFin']) : null;
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Format de date invalide']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $items = $this->serviceRdv->consulterAgenda($praticienId, $dateDebut, $dateFin);

        $data = array_map(function($item) {
            if ($item instanceof Rdv) {
                return [
                    'type'           => 'rdv',
                    'id'             => $item->getId(),
                    'dateHeureDebut' => $item->getDateHeureDebut()->format('Y-m-d H:i'),
                    'dateHeureFin'   => $item->getDateHeureFin()?->format('Y-m-d H:i'),
                    'duree'          => $item->getDuree(),
                    'motifVisite'    => $item->getMotifVisite(),
                    'status'         => $item->getStatus() === 0 ? 'prévu' : 'annulé',
                    'patientId'      => $item->getPatientId(),
                    '_links'         => [
                        'self'    => ['href' => "/rdvs/{$item->getId()}"],
                        'patient' => ['href' => "/patients/{$item->getPatientId()}"],
                        'annuler' => ['href' => "/rdvs/{$item->getId()}", 'method' => 'DELETE']
                    ]
                ];
            } elseif ($item instanceof Indisponibilite) {
                return [
                    'type'           => 'indisponibilite',
                    'id'             => $item->getId(),
                    'dateHeureDebut' => $item->getDateDebut()->format('Y-m-d H:i'),
                    'dateHeureFin'   => $item->getDateFin()->format('Y-m-d H:i'),
                    'motif'          => $item->getMotif()
                ];
            }
            return null;
        }, $items);

        $response->getBody()->write(json_encode([
            'praticienId' => $praticienId,
            'dateDebut'   => $dateDebut?->format('Y-m-d'),
            'dateFin'     => $dateFin?->format('Y-m-d'),
            'agenda'      => array_filter($data)
        ], JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
