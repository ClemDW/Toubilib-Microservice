<?php

namespace toubilib\infra\repositories;

use Ramsey\Uuid\Uuid;
use toubilib\core\application\ports\spi\exceptions\EmailNonTrouveException;
use toubilib\core\application\ports\spi\exceptions\ErreurCreationCompteException;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\core\domain\entities\User;

class AuthnRepository implements AuthRepositoryInterface
{
    private \PDO $pdo;
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email): ?User
    {
        try {

            $sql = "SELECT id, email, password, role FROM users WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            return new User(
                $data['id'],
                $data['email'],
                $data['password'],
                $data['role']
            );
        } catch (\PDOException $e) {
            throw new EmailNonTrouveException("L'email est introuvable");
        }
    }

    public function create(string $email, string $password, int $role): User
    {
        try {
            $id = Uuid::uuid4()->toString();
            $sql = "INSERT INTO users (id, email, password, role) VALUES (:id, :email, :password, :role)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);

            return new User($id, $email, $password, (string)$role);
        } catch (\PDOException $e) {
            throw new ErreurCreationCompteException("La création du compte a échoué");
        }
    }
}