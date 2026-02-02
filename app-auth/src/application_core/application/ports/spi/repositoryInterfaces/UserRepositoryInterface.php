<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;


interface UserRepositoryInterface
{
    
    public function findByEmail(string $email): ?array;

    
    public function existsByEmail(string $email): bool;
}