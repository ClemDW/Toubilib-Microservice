<?php
declare(strict_types=1);

namespace toubilib\core\domain\entities\auth;


class User
{
    public const ROLE_PATIENT = 1;
    public const ROLE_PRATICIEN = 10;

    private ?string $id = null;
    private string $email;
    private ?string $password = null;
    private int $role;

    public function __construct(string $email, int $role, ?string $password = null)
    {
        $this->email = $email;
        $this->role = $role;
        $this->password = $password;
    }

    public function getId(): ?string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getEmail(): string { return $this->email; }
    public function getPassword(): ?string { return $this->password; }
    public function getRole(): int { return $this->role; }

    public function getRoleName(): string
    {
        return match($this->role) {
            self::ROLE_PATIENT => 'patient',
            self::ROLE_PRATICIEN => 'praticien',
            default => 'unknown'
        };
    }

    public function isPatient(): bool
    {
        return $this->role === self::ROLE_PATIENT;
    }

    public function isPraticien(): bool
    {
        return $this->role === self::ROLE_PRATICIEN;
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

    public static function fromJwtPayload(array $payload): self
    {
        $user = new self(
            $payload['email'] ?? '',
            (int) ($payload['role'] ?? 0)
        );
        $user->setId($payload['sub'] ?? $payload['id'] ?? '');
        return $user;
    }
}
