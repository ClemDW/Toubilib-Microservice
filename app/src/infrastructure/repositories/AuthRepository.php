<?php
declare(strict_types=1);

namespace toubilib\infra\repositories;

use PDO;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\core\domain\entities\auth\User;
use toubilib\core\exceptions\RepositoryEntityNotFoundException;

class AuthRepository implements AuthRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère un utilisateur à partir de son email.
     * 
     * @throws RepositoryEntityNotFoundException
     */
    public function byEmail(string $email): User
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password, role FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new RepositoryEntityNotFoundException("Utilisateur introuvable pour l’email : $email");
        }

        $user = new User(
            $row['email'],
            $row['password'],
            (int)$row['role']
        );
        $user->setId((string)$row['id']);

        return $user;
    }

    /**
     * Sauvegarde un nouvel utilisateur dans la base.
     */
    public function save(User $user): string
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password, role) VALUES (:email, :password, :role)');
        $stmt->execute([
            'email' => $user->getEmail(),
            'password' => password_hash($user->getPassword(), PASSWORD_BCRYPT),
            'role' => $user->getRole(),
        ]);
        return (string)$this->pdo->lastInsertId();
    }
}
