<?php

declare(strict_types=1);

namespace Gateway\Api\Actions;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpBadRequestException;

class GenericGetPraticiensAction
{
    private ClientInterface $remote_service;
    public function __construct(ClientInterface $remote_service)
    {
        $this->remote_service = $remote_service;
    }
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        $options = [
            'query' => $request->getQueryParams(),
            'body' => $request->getBody(),
        ];

        try {
            $serviceResponse = $this->remote_service->request($method, $path, $options);
            
            foreach ($serviceResponse->getHeaders() as $name => $values) {
                if (strtolower($name) !== 'transfer-encoding') {
                    $response = $response->withHeader($name, $values);
                }
            }

            $response->getBody()->write((string)$serviceResponse->getBody());
            return $response->withStatus($serviceResponse->getStatusCode());

        } catch (ConnectException | ServerException $e) {
            throw new HttpInternalServerErrorException($request, "Le serveur distant est indisponible");
        } catch (ClientException $e ) {
            match($e->getCode()) {
                401 => throw new HttpUnauthorizedException($request, "Vous n'êtes pas autorisé à accéder à cette ressource"),
                403 => throw new HttpForbiddenException($request, "Vous n'êtes pas autorisé à accéder à cette ressource"),
                404 => throw new HttpNotFoundException($request, "La ressource est introuvable"),
                default => throw new HttpBadRequestException($request, $e->getMessage()),
            };
        }
    }

}