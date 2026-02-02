<?php

namespace toubilib\core\application\usecases;

class AuthzPraticienService {
    const ROLE_PRATICIEN = 10;
    const ROLE_ADMIN = 100;
    const OPERATION_READ = 1;

    public function isGranted(string $user_id, int $role, string $ressource_id, int $operation = self::OPERATION_READ): bool {
        if ($role < self::ROLE_PRATICIEN) throw new \Exception('Invalid Role');
        if ($user_id !== $ressource_id) throw new \Exception('Not Owner');
        return true;
    }
}
