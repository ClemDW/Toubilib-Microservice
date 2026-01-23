<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\api\dtos\InputRendezVousDTO;
use toubilib\core\application\ports\spi\exceptions\CreneauInvalideException;
use toubilib\core\application\ports\spi\exceptions\MotifInvalideException;
use toubilib\core\application\ports\spi\exceptions\PatientNonTrouveException;
use toubilib\core\application\ports\spi\exceptions\PraticienIndisponibleException;
use toubilib\core\application\ports\spi\exceptions\PraticienNonTrouveException;

class CreerRdvAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface{

        $dto = $request->getAttribute('inputRdvDto');
        if (!$dto instanceof InputRendezVousDTO) {
            $response->getBody()->write(json_encode(['error' => 'DTO de rendez-vous manquant ou invalide'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $this->serviceRdv->creerRendezVous($dto);

            $payload = ['success' => true, 'message' => 'Rendez-vous créé'];
            $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');

        } catch (PraticienNonTrouveException|PatientNonTrouveException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');

        } catch (MotifInvalideException|CreneauInvalideException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');

        } catch (PraticienIndisponibleException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}