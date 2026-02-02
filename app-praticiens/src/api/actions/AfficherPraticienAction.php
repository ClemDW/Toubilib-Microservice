<?php

namespace toubilib\api\actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServicePraticienInterface;
use toubilib\core\application\ports\api\dtos\PraticienDetailDTO;
use toubilib\core\application\ports\api\dtos\SpecialiteDTO;
use toubilib\core\application\ports\api\dtos\StructureDTO;
use toubilib\core\application\ports\api\dtos\MotifVisiteDTO;
use toubilib\core\application\ports\api\dtos\MoyenPaiementDTO;

class AfficherPraticienAction
{
    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien)
    {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'] ?? null;
            if ($id === null) {
                $error = ['error' => 'ID du praticien manquant'];
                $response->getBody()->write(json_encode($error));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            $praticien = $this->servicePraticien->getPraticienDetail($id);
            if (!$praticien) {
                $error = ['error' => 'Praticien introuvable'];
                $response->getBody()->write(json_encode($error));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }
            $specialiteDto = SpecialiteDTO::fromEntity($praticien->getSpecialite());
            $structureDto = StructureDTO::fromEntity($praticien->getStructure());
            $motifVisiteDto = array_map(fn($m) => MotifVisiteDTO::fromEntity($m), $praticien->getMotifs());
            $moyensPaiementDto = array_map(fn($m) => MoyenPaiementDTO::fromEntity($m), $praticien->getMoyens());

            $dto = new PraticienDetailDTO(
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
                $specialiteDto,
                $structureDto,
                $motifVisiteDto,
                $moyensPaiementDto

                

            );

            $response->getBody()->write(json_encode($dto));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (Exception $e) {
            $error = ['error' => 'Erreur lors de la récupération du praticien'];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
