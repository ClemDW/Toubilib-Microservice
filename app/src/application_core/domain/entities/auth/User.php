<?php
declare(strict_types=1);

namespace toubilib\core\domain\entities\auth;

class User
{
    private ?string $id = null;
    private string $email;
    private string $password;
    private int $role;

    public function __construct(string $email, string $password, int $role)
    {
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function getID(): ?string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): int { return $this->role; }
}
