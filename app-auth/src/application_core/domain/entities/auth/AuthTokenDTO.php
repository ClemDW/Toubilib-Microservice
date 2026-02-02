<?php

namespace toubilib\core\domain\entities\auth;


class AuthTokenDTO
{
    private UserProfile $userProfile;
    private string $accessToken;
    private string $refreshToken;
    private int $expiresIn;

    public function __construct(
        UserProfile $userProfile, 
        string $accessToken, 
        string $refreshToken, 
        int $expiresIn = 3600
    ) {
        $this->userProfile = $userProfile;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
    }

    public function getUserProfile(): UserProfile
    {
        return $this->userProfile;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    
    public function toArray(): array
    {
        return [
            'user' => $this->userProfile->toArray(),
            'accessToken' => $this->accessToken,
            'refreshToken' => $this->refreshToken,
            'expiresIn' => $this->expiresIn,
            'tokenType' => 'Bearer'
        ];
    }
}