<?php

declare(strict_types=1);

namespace Gateway\Api\Actions;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\ClientInterface;

class ListerPraticienRdvsAction
{
    private ClientInterface $remote_rdvs_service;

    public function __construct(ClientInterface $remote_rdvs_service)
    {
        $this->remote_rdvs_service = $remote_rdvs_service;
    }

    public function __invoke(ServerRequestInterface $request,ResponseInterface $response, array $args): ResponseInterface {
        try {
            $response = $this->remote_rdvs_service->get("/praticiens/" . $args['id'] . "/rdvs");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new HttpNotFoundException($request, "Praticien introuvable");
            }
            throw $e;
        }
        return $response;
    }
}