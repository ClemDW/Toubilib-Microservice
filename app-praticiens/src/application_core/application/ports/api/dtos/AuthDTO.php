<?php

namespace toubilib\core\application\ports\api\dtos;

class AuthDTO
{
    public ProfileDTO $profile;
    public string $accessToken;
    public string $refreshToken;

    public function __construct(ProfileDTO $profile, string $accessToken, string $refreshToken)
    {
        $this->profile = $profile;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function getProfile(): ProfileDTO
    {
        return $this->profile;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}