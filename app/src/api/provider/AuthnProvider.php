<?php

namespace toubilib\api\provider;

use Exception;
use Firebase\JWT\JWT;
use toubilib\core\application\ports\api\dtos\CredentialsDTO;
use toubilib\core\application\ports\api\ServiceAuthnInterface;
use toubilib\api\provider\JWTManager;

class AuthnProvider implements AuthnProviderInterface
{

    private ServiceAuthnInterface $serviceAuthn;
    private JWTManager $jwtManager;

    public function __construct(ServiceAuthnInterface $serviceAuthn, JWTManager $jwtManager)
    {
        $this->serviceAuthn = $serviceAuthn;
        $this->jwtManager = $jwtManager;
    }

    public function signin(CredentialsDTO $credentials)
    {
        $profile = $this->serviceAuthn->signin($credentials);
        $profile = [
            'id' => $profile->getId(),
            'email' => $profile->getEmail(),
            'role' => $profile->getRole()
        ];
        $acces_token = $this->jwtManager->createAccessToken((array) $profile);
        $refresh_token = $this->jwtManager->createRefreshToken((array) $profile);
        return [
            'profile' => $profile,
            'accessToken' => $acces_token,
            'refreshToken' => $refresh_token
        ];
    }
}
