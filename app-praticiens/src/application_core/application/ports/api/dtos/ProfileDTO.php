<?php

namespace toubilib\core\application\ports\api\dtos;

class ProfileDTO
{
    public string $id;
    public string $email;
    public int $role;

    public function __construct(string $id, string $email, int $role)
    {
        $this->id = $id;
        $this->email = $email;
        $this->role = $role;
    }

    public function getId(): string { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
}