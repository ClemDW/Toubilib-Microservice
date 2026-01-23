<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\spi\exceptions\PraticienNonTrouveException;

class AgendaPraticienAction
{
    private ServiceRdvInterface $serviceRdv;
    public function __construct(ServiceRdvInterface $serviceRdv) {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        $praticienId = $request->getAttribute('id') ?? ($args['id'] ?? null);
        $query = $request->getQueryParams();
        $debut = $query['debut'] ?? null;
        $fin   = $query['fin'] ?? null;

        if (!$praticienId) {
            $response->getBody()->write(json_encode(['error' => 'Identifiant praticien manquant'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $agenda = $this->serviceRdv->agendaPraticien($praticienId, $debut, $fin);
            $response->getBody()->write(json_encode([
                'praticien_id' => $praticienId,
                'periode' => ['debut' => $debut, 'fin' => $fin],
                'data' => $agenda
            ], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (PraticienNonTrouveException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}