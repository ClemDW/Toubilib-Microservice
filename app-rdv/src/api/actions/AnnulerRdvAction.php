<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\spi\exceptions\RendezVousNonTrouveException;
use toubilib\core\application\ports\spi\exceptions\RdvDejaAnnuleException;
use toubilib\core\application\ports\spi\exceptions\RdvPasseNonAnnulableException;

class AnnulerRdvAction
{
    public function __construct(private ServiceRdvInterface $serviceRdv) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        $id = $request->getAttribute('id') ?? ($args['id'] ?? null);
        if (!$id) {
            $response->getBody()->write(json_encode(['error' => 'Identifiant de rendez-vous manquant'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $this->serviceRdv->annulerRendezVous($id);
            // 204 No Content (soft delete/annulation)
            return $response->withStatus(204);

        } catch (RendezVousNonTrouveException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        } catch (RdvDejaAnnuleException|RdvPasseNonAnnulableException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}