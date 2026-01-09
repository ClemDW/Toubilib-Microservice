<?php

namespace toubilib\api\provider;

use Firebase\JWT\JWT;
use toubilib\core\application\ports\api\dtos\ProfileDTO;
use Firebase\JWT\Key;

class JWTManager
{
    public static function createAccessToken(array $user): string
    {
        $payload['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        $payload['exp'] = time() + 15 * 60;
        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }

    public static function decodeToken(string $token): ProfileDTO
    {
        try {
            $token = trim($token);

            if (str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }

            if (empty($token)) {
                throw new \InvalidArgumentException('Token cannot be empty');
            }

            $payload = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $payloadUser = $payload->user;
            if ($payloadUser->id === null || $payloadUser->email === null || $payloadUser->nom === null || $payloadUser->prenom === null) {
                throw new \InvalidArgumentException('Token payload is missing required user information');
            }
            $user = new ProfileDTO(
                $payloadUser->id,
                $payloadUser->email,
                $payloadUser->role
            );

            return $user;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid token: ' . $e->getMessage());
        }
    }

    public static function createRefreshToken(array $payload): string
    {
        $payload['exp'] = time() + 7 * 24 * 60 * 60;
        $refreshToken = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
        return $refreshToken;
    }
}