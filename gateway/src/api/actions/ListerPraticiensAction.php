<?php

declare(strict_types=1);

namespace Gateway\Api\Actions;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\ClientInterface;

class ListerPraticiensActionGateway
{
    private ClientInterface $remote_praticien_service;

    public function __construct(ClientInterface $remote_praticien_service)
    {
        $this->remote_praticien_service = $remote_praticien_service;
    }

    public function __invoke(ServerRequestInterface $request,ResponseInterface $response, array $args): ResponseInterface {
        try {
            $response = $this->remote_praticien_service->get("/praticiens");
        } catch (ClientException $e) {
            throw new HttpNotFoundException($request, " … ");
        }
        return $response;
    }
}