<?php

namespace toubilib\api\actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServicePatientInterface;
use toubilib\core\application\ports\api\dtos\InputPatientDTO;

class CreerPatientAction
{
    private ServicePatientInterface $servicePatient;

    public function __construct(ServicePatientInterface $servicePatient)
    {
        $this->servicePatient = $servicePatient;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['telephone'])) {
                $response->getBody()->write(json_encode(['error' => 'Champs obligatoires manquants (nom, prenom, email, telephone)']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $dto = new InputPatientDTO(
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $data['telephone'],
                $data['dateNaissance'] ?? null,
                $data['adresse'] ?? null,
                $data['codePostal'] ?? null,
                $data['ville'] ?? null
            );

            $patient = $this->servicePatient->createPatient($dto);

            $responseData = [
                'id' => $patient->getId(),
                'nom' => $patient->getNom(),
                'prenom' => $patient->getPrenom(),
                'email' => $patient->getEmail()
            ];

            $response->getBody()->write(json_encode($responseData));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);

        } catch (Exception $e) {
            $error = ['error' => 'Erreur lors de la création du patient: ' . $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
