<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;

class ConsulterRdvAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $rdvId = $args['id'] ?? null;

        if (!$rdvId) {
            $response->getBody()->write(json_encode([
                'error' => 'ID du RDV manquant'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $rdv = $this->serviceRdv->getRdvById($rdvId);

        if (!$rdv) {
            $response->getBody()->write(json_encode([
                'error' => 'Rendez-vous introuvable'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $praticien = $this->serviceRdv->getPraticienDetails($rdv->getPraticienId());
        $patient = $this->serviceRdv->getPatientById($rdv->getPatientId());

        // Construction de la réponse JSON avec HATEOAS
        $data = [
            'id'             => $rdv->getId(),
            'dateHeureDebut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i'),
            'dateHeureFin'   => $rdv->getDateHeureFin()?->format('Y-m-d H:i'),
            'duree'          => $rdv->getDuree(),
            'motifVisite'    => $rdv->getMotifVisite(),
            'status'         => $rdv->getStatus() === 0 ? 'prévu' : 'annulé',
            'praticienId'    => $rdv->getPraticienId(),
            'praticien'      => $praticien ? [
                'nom' => $praticien->getNom(),
                'prenom' => $praticien->getPrenom(),
                'specialite' => $praticien->getSpecialite()->getLibelle(),
                'adresse' => $praticien->getStructure() ? $praticien->getStructure()->getAdresse() : null,
                'telephone' => $praticien->getStructure() ? $praticien->getStructure()->getTelephone() : null,
            ] : null,
            'patientId'      => $rdv->getPatientId(),
            'patient'        => $patient ? [
                'nom' => $patient->getNom(),
                'prenom' => $patient->getPrenom(),
                'email' => $patient->getEmail(),
                'telephone' => $patient->getTelephone(),
            ] : null,
            '_links'         => [
                'self'      => [ 'href' => "/rdvs/{$rdv->getId()}" ],
                'praticien' => [ 'href' => "/praticiens/{$rdv->getPraticienId()}" ],
                'patient'   => [ 'href' => "/patients/{$rdv->getPatientId()}" ],
                'annuler'   => [ 'href' => "/rdvs/{$rdv->getId()}", 'method' => 'DELETE' ]
            ]
        ];

        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
