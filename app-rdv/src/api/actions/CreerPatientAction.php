<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\dtos\PatientDTO;
use toubilib\core\application\ports\api\ServicePatientInterface;
use toubilib\core\application\ports\spi\exceptions\InscriptionException;

class CreerPatientAction
{
    private ServicePatientInterface $servicePatient;

    public function __construct(ServicePatientInterface $servicePatient)
    {
        $this->servicePatient = $servicePatient;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();

        // Basic validation
        $required = ['nom', 'prenom', 'date_naissance', 'adresse', 'code_postal', 'ville', 'email', 'telephone', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $response->getBody()->write(json_encode(['error' => "Champ manquant: $field"]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }

        try {
            $patientDto = new PatientDTO(
                '', 
                $data['nom'],
                $data['prenom'],
                $data['date_naissance'],
                $data['adresse'],
                $data['code_postal'],
                $data['ville'],
                $data['email'],
                $data['telephone'],
                $data['password']
            );

            $this->servicePatient->creerComptePatient($patientDto);

            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Compte patient créé']));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');

        } catch (InscriptionException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
