<?php

namespace toubilib\api\middlewares;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\dtos\CredentialsDTO;
use toubilib\api\provider\AuthnProvider;

class AuthnMiddleware
{
    private AuthnProvider $authnProvider;

    public function __construct(AuthnProvider $authnProvider)
    {
        $this->authnProvider = $authnProvider;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $payload = $request->getParsedBody();
        $email = $payload['email'];
        $password = $payload['password'];
        if ($email == null || $password == null) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['message' => 'Email ou mot de passe manquant']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $credentials = new CredentialsDTO($email, $password);
        $request = $request->withAttribute('credentials', $credentials);
        return $handler->handle($request);
    }

}