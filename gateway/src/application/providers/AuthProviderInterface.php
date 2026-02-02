<?php

namespace toubilib\api\provider;

use toubilib\core\application\ports\api\dtos\CredentialsDTO;
use toubilib\core\application\ports\api\dtos\AuthDTO;
use toubilib\core\application\ports\api\dtos\ProfileDTO;

interface AuthProviderInterface {
    public function signin(CredentialsDTO $credentials): AuthDTO;
    public function getSignedInUser(string $token): ProfileDTO;
}
