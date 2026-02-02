<?php

namespace toubilib\gateway\application\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

class ListePraticiensAction
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $guzzleResponse = $this->client->get('http://api.toubilib:80/praticiens', [
                'http_errors' => false,
            ]);
        } catch (\Exception $e) {
            $response->getBody()->write("Gateway Error: " . $e->getMessage());
            return $response->withStatus(500);
        }

        foreach ($guzzleResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $response = $response->withHeader($name, $value);
            }
        }
        
        $response->getBody()->write($guzzleResponse->getBody()->getContents());
        
        return $response->withStatus($guzzleResponse->getStatusCode());
    }
}
