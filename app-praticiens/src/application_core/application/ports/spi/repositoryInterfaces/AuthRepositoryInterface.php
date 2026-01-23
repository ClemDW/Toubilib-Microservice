<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;

use toubilib\core\domain\entities\User;

interface AuthRepositoryInterface {
    public function findByEmail(string $email): ?User;
    public function create(string $email, string $password, int $role): User;
}
