<?php
declare(strict_types=1);

namespace toubilib\core\application\ports\spi\repositoryInterfaces;
use toubilib\core\domain\entities\auth\User;

interface AuthRepositoryInterface
{
    public function byEmail(string $email): User;
    public function save(User $user): string;
}
