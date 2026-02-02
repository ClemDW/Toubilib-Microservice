<?php

namespace toubilib\api\actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServicePraticienInterface;
use toubilib\core\application\ports\api\dtos\PraticienDTO;

class ListerPraticienAction
{
    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien)
    {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $response = $response->withHeader('X-Served-By', 'Microservice-Praticiens');
            $praticiens = $this->servicePraticien->getAllPraticiens();

            $praticienData = [];
            foreach ($praticiens as $praticien) {
                $praticienData[] = new PraticienDTO(
                    $praticien->getId(),
                    $praticien->getNom(),
                    $praticien->getPrenom(),
                    $praticien->getVille(),
                    $praticien->getEmail(),
                    $praticien->getTelephone(),
                    $praticien->getRppsId(),
                    $praticien->getTitre(),
                    $praticien->isAccepteNouveauPatient(),
                    $praticien->isEstOrganisation(),
                    $praticien->getSpecialite()
                );
            }

            $response->getBody()->write(json_encode($praticienData));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (Exception $e) {
            $error = ['error' => 'Erreur lors de la récupération des praticiens'];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}