<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\usecases\ServiceRdv;

class ListerRdvPatientAction
{
    private ServiceRdv $serviceRdv;

    public function __construct(ServiceRdv $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $patientId = $request->getAttribute('id');
        $rdvs = $this->serviceRdv->listerRdvPatient($patientId);

        $response->getBody()->write(json_encode($rdvs, JSON_PRETTY_PRINT));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
