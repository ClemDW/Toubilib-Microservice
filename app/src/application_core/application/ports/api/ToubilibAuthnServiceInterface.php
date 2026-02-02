<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\application\ports\api\dtos\CredentialsDTO;
use toubilib\core\application\ports\api\dtos\AuthDTO;
use toubilib\core\application\ports\api\dtos\ProfileDTO;  

interface ToubilibAuthnServiceInterface {
    public function byCredentials(CredentialsDTO $credentials): ProfileDTO;
}