<?php

namespace toubilib\api\actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\api\dtos\InputIndisponibiliteDTO;

class CreerIndisponibiliteAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['praticienId']) || empty($data['dateDebut']) || empty($data['dateFin'])) {
                $response->getBody()->write(json_encode(['error' => 'Champs obligatoires manquants (praticienId, dateDebut, dateFin)']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $dto = new InputIndisponibiliteDTO(
                $data['praticienId'],
                $data['dateDebut'],
                $data['dateFin'],
                $data['motif'] ?? null
            );

            $id = $this->serviceRdv->creerIndisponibilite($dto);

            $response->getBody()->write(json_encode(['id' => $id]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);

        } catch (Exception $e) {
            $error = ['error' => 'Erreur lors de la création de l\'indisponibilité: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
