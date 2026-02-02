<?php

namespace toubilib\gateway\application\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;


class AuthGatewayAction
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $uri = $request->getUri()
            ->withScheme('http')
            ->withHost('service-auth.toubilib')
            ->withPort(80);
            
        $request = $request->withUri($uri);

        try {
            $guzzleResponse = $this->client->send($request);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                 $response->getBody()->write($e->getResponse()->getBody()->getContents());
                 return $response->withStatus($e->getResponse()->getStatusCode());
            }
            $response->getBody()->write("Gateway Error: " . $e->getMessage());
            return $response->withStatus(500);
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
