<?php

namespace toubilib\api\provider;

use toubilib\core\application\ports\api\dtos\ProfileDTO;
use toubilib\core\domain\entities\auth\AuthTokenDTO;


interface AuthProviderInterface
{
   
    public function signin(string $email, string $password): AuthTokenDTO;

    public function refresh(string $refreshToken): AuthTokenDTO;

    public function validateToken(string $accessToken): AuthTokenDTO;

    public function getSignedInUser(string $accessToken): ProfileDTO;
}
