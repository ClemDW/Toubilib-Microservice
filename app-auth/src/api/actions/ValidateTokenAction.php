<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\provider\AuthProviderInterface;

class ValidateTokenAction
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif ($request->hasHeader('X-Auth-Token')) {
                $authHeader = 'Bearer ' . $request->getHeaderLine('X-Auth-Token');
            }
        }

        $token = null;

        if (!empty($authHeader) && preg_match('/^Bearer\s+(\S+)$/', $authHeader, $matches)) {
            $token = $matches[1];
        } 

        if (!$token && !empty($authHeader) && substr_count($authHeader, '.') === 2) {
             $token = $authHeader;
        }
        
        if (!$token) {
            $params = $request->getParsedBody();
            if (is_array($params) && isset($params['token'])) {
                $token = $params['token'];
            }
        }

        if (!$token) {
            $response->getBody()->write(json_encode([
                'error' => 'Authorization header missing or malformed.'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        $jwt = $token;

        try {
            $this->authProvider->validateToken($jwt);
            
            $response->getBody()->write(json_encode([
                'message' => 'Token is valid.'
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
