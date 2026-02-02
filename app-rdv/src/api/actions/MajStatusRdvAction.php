<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use DomainException;

class MajStatusRdvAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $idRdv = $args['id'];
        $data = $request->getParsedBody();
        $status = $data['status'] ?? null;

        try {
            if ($status === 'honore') {
                $this->serviceRdv->marquerRdvHonore($idRdv);
            } elseif ($status === 'non_honore') {
                $this->serviceRdv->marquerRdvNonHonore($idRdv);
            } else {
                throw new DomainException("Status invalide. Utilisez 'honore' ou 'non_honore'.");
            }

            $response->getBody()->write(json_encode(['status' => 'success', 'message' => "Rendez-vous marqué comme $status"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (DomainException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
