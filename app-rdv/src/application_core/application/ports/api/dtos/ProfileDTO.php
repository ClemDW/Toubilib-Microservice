<?php

namespace toubilib\core\application\ports\api\dtos;

class ProfileDTO {
    public string $ID;
    public string $email;
    public int $role;

    public function __construct(string $ID, string $email, int $role) {
        $this->ID = $ID;
        $this->email = $email;
        $this->role = $role;
    }
}
