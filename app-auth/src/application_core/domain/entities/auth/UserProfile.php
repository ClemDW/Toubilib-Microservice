<?php

namespace toubilib\core\domain\entities\auth;

class UserProfile
{
    private string $id;
    private string $email;
    private int $role;

    public function __construct(string $id, string $email, int $role)
    {
        $this->id = $id;
        $this->email = $email;
        $this->role = $role;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): int
    {
        return $this->role;
    }

  
    public function getRoleName(): string
    {
        return match($this->role) {
            1 => 'patient',
            10 => 'praticien',
            default => 'unknown'
        };
    }
    public function isPatient(): bool
    {
        return $this->role === 1;
    }

    /**
     * Vérifie si l'utilisateur est praticien
     */
    public function isPraticien(): bool
    {
        return $this->role === 10;
    }



   
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'roleName' => $this->getRoleName(),
            'isPatient' => $this->isPatient(),
            'isPraticien' => $this->isPraticien()
        ];
    }
}