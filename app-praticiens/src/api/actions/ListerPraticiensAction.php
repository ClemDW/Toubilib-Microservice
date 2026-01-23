<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\usecases\ServicePraticien;

class ListerPraticiensAction
{
    private ServicePraticien $servicePraticien;

    public function __construct(ServicePraticien $servicePraticien)
    {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $praticiens = $this->servicePraticien->listerPraticiens();

        $response->getBody()->write(json_encode($praticiens, JSON_PRETTY_PRINT));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
