<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\core\application\ports\spi\exceptions\PraticienNonTrouveException;
use Slim\Exception\HttpNotFoundException;

class AfficherPraticienAction
{
    private ServicePraticien $servicePraticien;

    public function __construct(ServicePraticien $servicePraticien)
    {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $praticien = $this->servicePraticien->afficherPraticien($request->getAttribute('id'));
            $response->getBody()->write(json_encode($praticien, JSON_PRETTY_PRINT));
        } catch (PraticienNonTrouveException $e) {
            throw new HttpNotFoundException($request, $e->getMessage());
        }

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}