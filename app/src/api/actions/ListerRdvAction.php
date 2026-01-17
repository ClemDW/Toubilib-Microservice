<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\usecases\ServiceRdv;

class ListerRdvAction
{
    private ServiceRdv $serviceRdv;

    public function __construct(ServiceRdv $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $routeContext = \Slim\Routing\RouteContext::fromRequest($request);
        $routeName = $routeContext->getRoute()->getName();
        $queryParams = $request->getQueryParams();

        try {
            if ($routeName === 'AfficherRdv') {
                $data = $this->serviceRdv->getRdv($args['id']);
                
                // Ajout des liens HATEOAS
                $routeParser = $routeContext->getRouteParser();
                $data['links'] = [
                    'self' => ['href' => $routeParser->urlFor('AfficherRdv', ['id' => $args['id']])],
                    'praticien' => ['href' => $routeParser->urlFor('AfficherPraticien', ['id' => $data['rdv']->getPraticienId()])]
                ];
            } elseif ($routeName === 'ListerRdvPraticien') {
                $praticienId = $args['id'];
                
                if (isset($queryParams['debutPeriode'], $queryParams['finPeriode'])) {
                    $data = $this->serviceRdv->getRdvPraticienPeriode($praticienId, $queryParams['debutPeriode'], $queryParams['finPeriode']);
                } else {
                    $data = $this->serviceRdv->getRdvPraticien($praticienId);
                }
            } else {
                // Fallback ou autre cas par défaut
                $data = $this->serviceRdv->listerRdv();
            }

            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found or Server Error',
                'message' => $e->getMessage()
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }
}
