<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\application\ports\api\dtos\CredentialsDTO;
use toubilib\core\application\ports\api\dtos\ProfileDTO;

interface ServiceAuthnInterface
{
    public function register(CredentialsDTO $credentials, int $role): profileDTO;
    public function byCredentials(CredentialsDTO $credentials): profileDTO;
    public function signin(CredentialsDTO $credentials): ProfileDTO;
}