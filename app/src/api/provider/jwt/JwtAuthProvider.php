<?php

namespace toubilib\api\provider\jwt;

use toubilib\api\provider\AuthProviderInterface;
use toubilib\core\application\ports\api\dtos\CredentialsDTO;
use toubilib\core\application\ports\api\dtos\AuthDTO;
use toubilib\core\application\ports\api\dtos\ProfileDTO;
use toubilib\core\application\ports\api\ToubilibAuthnServiceInterface;
use toubilib\api\provider\jwt\JwtManagerInterface;


class JwtAuthProvider implements AuthProviderInterface {
    private JwtManagerInterface $jwtManager;

    public function __construct(JwtManagerInterface $jwtManager) {
        $this->jwtManager = $jwtManager;
    }

    public function signin(CredentialsDTO $credentials): AuthDTO {
        throw new \RuntimeException("Signin operation is not supported in this service.");
    }

    public function getSignedInUser(string $token): ProfileDTO {
        $payload = $this->jwtManager->decode($token);
        return new ProfileDTO($payload['upr']['id'], $payload['upr']['email'], $payload['upr']['role']);
    }
}
