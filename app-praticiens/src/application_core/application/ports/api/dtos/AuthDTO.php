<?php

namespace toubilib\core\application\ports\api\dtos;

use toubilib\core\application\ports\api\dtos\UserProfileDTO;

class AuthDTO {
    public ProfileDTO $profile;
    public string $access_token;
    public string $refresh_token;

    public function __construct(ProfileDTO $profile, string $access_token, string $refresh_token) {
        $this->profile = $profile;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }
}

