<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\spi\exceptions\RdvDejaAnnuleException;
use toubilib\core\application\ports\spi\exceptions\RendezVousNonTrouveException;

class MarquerRdvNonHonoreAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'];

        try {
            $this->serviceRdv->marquerRdvNonHonore($id);
            return $response->withStatus(204); 
        } catch (RendezVousNonTrouveException $e) {
            $response->getBody()->write(json_encode(['error' => 'Rendez-vous introuvable']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        } catch (RdvDejaAnnuleException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
