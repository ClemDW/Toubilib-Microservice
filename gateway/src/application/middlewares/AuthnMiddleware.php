<?php

namespace toubilib\gateway\application\middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;

class AuthnMiddleware {
    private Client $client;

    public function __construct() {
        $this->client = new Client([
            'base_uri' => 'http://service-auth.toubilib'
        ]);
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $token_line = $request->getHeaderLine('Authorization');
        
        if (empty($token_line)) {
            throw new HttpUnauthorizedException($request, "missing authorization header");
        }
        
        if (!preg_match('/Bearer\s(\S+)/', $token_line, $matches)) {
            throw new HttpUnauthorizedException($request, "invalid authorization header format");
        }
        
        $token = $matches[1];

        $token_line = $request->getHeaderLine('Authorization');

        list($token) = sscanf($token_line, 'Bearer %s');
        
        try {
                $response = $this->client->post('/tokens/validate', [
                'json' => ['token' => $token],
                'headers' => [
                    'Authorization' => "Bearer $token"
                ],
                'timeout' => 5
            ]);

        } catch (ClientException $e) {
            // 400 errors
            throw new HttpUnauthorizedException($request, "invalid or expired jwt token");
        } catch (ConnectException $e) {
             // connection errors
            throw new HttpUnauthorizedException($request, "auth service unavailable");
        } catch (ServerException $e) {
             // 500 errors
             throw new HttpUnauthorizedException($request, "auth service error");
        } catch (\Exception $e) {
             throw new HttpUnauthorizedException($request, "authentication failed");
        }

        return $handler->handle($request);
    }
}