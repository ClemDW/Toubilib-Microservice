<?php

namespace toubilib\core\domain\entities\auth;


interface AuthServiceInterface
{
   
    public function authenticate(string $email, string $password): UserProfile;

   
    public function userExists(string $email): bool;
}