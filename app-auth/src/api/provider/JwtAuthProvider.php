<?php

namespace toubilib\api\provider;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\core\domain\entities\auth\AuthServiceInterface;
use toubilib\core\domain\entities\auth\AuthTokenDTO;
use toubilib\core\domain\entities\auth\UserProfile;
use toubilib\core\domain\exceptions\AuthenticationException;


class JwtAuthProvider implements AuthProviderInterface
{
    private AuthServiceInterface $authService;
    private string $jwtSecret;
    private string $jwtAlgorithm;
    private int $accessTokenExpiry;
    private int $refreshTokenExpiry;

    public function __construct(
        AuthServiceInterface $authService,
        string $jwtSecret = 'your-secret-key',
        string $jwtAlgorithm = 'HS256',
        int $accessTokenExpiry = 3600,  
        int $refreshTokenExpiry = 86400
    ) {
        $this->authService = $authService;
        $this->jwtSecret = $jwtSecret;
        $this->jwtAlgorithm = $jwtAlgorithm;
        $this->accessTokenExpiry = $accessTokenExpiry;
        $this->refreshTokenExpiry = $refreshTokenExpiry;
    }

   
    public function signin(string $email, string $password): AuthTokenDTO
    {
        $userProfile = $this->authService->authenticate($email, $password);

        $accessToken = $this->generateAccessToken($userProfile);
        $refreshToken = $this->generateRefreshToken($userProfile);

        return new AuthTokenDTO(
            $userProfile,
            $accessToken,
            $refreshToken,
            $this->accessTokenExpiry
        );
    }

   
    public function refresh(string $refreshToken): AuthTokenDTO
    {
        try {
            $decoded = JWT::decode($refreshToken, new Key($this->jwtSecret, $this->jwtAlgorithm));
            
            if ($decoded->type !== 'refresh') {
                throw new AuthenticationException('Token de rafraîchissement invalide');
            }

            $userProfile = new UserProfile(
                $decoded->sub,
                $decoded->email,
                $decoded->role
            );

            $newAccessToken = $this->generateAccessToken($userProfile);
            $newRefreshToken = $this->generateRefreshToken($userProfile);

            return new AuthTokenDTO(
                $userProfile,
                $newAccessToken,
                $newRefreshToken,
                $this->accessTokenExpiry
            );

        } catch (\Exception $e) {
            throw new AuthenticationException('Token de rafraîchissement invalide ou expiré');
        }
    }

    
    public function validateToken(string $accessToken): AuthTokenDTO
    {
        try {
            $decoded = JWT::decode($accessToken, new Key($this->jwtSecret, $this->jwtAlgorithm));
            
            if ($decoded->type !== 'access') {
                throw new AuthenticationException('Token d\'accès invalide');
            }

            $userProfile = new UserProfile(
                $decoded->sub,
                $decoded->email,
                $decoded->role
            );

            return new AuthTokenDTO(
                $userProfile,
                $accessToken,
                '',
                $this->accessTokenExpiry
            );

        } catch (\Exception $e) {
            throw new AuthenticationException('Token d\'accès invalide ou expiré');
        }
    }

    
    private function generateAccessToken(UserProfile $userProfile): string
    {
        $now = time();
        $payload = [
            'iss' => 'toubilib-api',
            'sub' => $userProfile->getId(),
            'iat' => $now,
            'exp' => $now + $this->accessTokenExpiry,
            'type' => 'access',
            'email' => $userProfile->getEmail(),
            'role' => $userProfile->getRole(),
            'roleName' => $userProfile->getRoleName()
        ];

        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }

    private function generateRefreshToken(UserProfile $userProfile): string
    {
        $now = time();
        $payload = [
            'iss' => 'toubilib-api',
            'sub' => $userProfile->getId(),
            'iat' => $now,
            'exp' => $now + $this->refreshTokenExpiry,
            'type' => 'refresh',
            'type' => 'refresh',               
            'email' => $userProfile->getEmail(),
            'role' => $userProfile->getRole()
        ];

        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);
    }

    public function getSignedInUser(string $accessToken): \toubilib\core\application\ports\api\dtos\ProfileDTO
    {
        $tokenData = $this->validateToken($accessToken);
        $userProfile = $tokenData->getUserProfile();
        return new \toubilib\core\application\ports\api\dtos\ProfileDTO(
            $userProfile->getId(),
            $userProfile->getEmail(),
            $userProfile->getRole()
        );
    }


}