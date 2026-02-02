<?php

namespace toubilib\core\application\usecases;

use toubilib\core\domain\entities\auth\AuthServiceInterface;
use toubilib\core\domain\entities\auth\UserProfile;
use toubilib\core\domain\exceptions\AuthenticationException;
use toubilib\core\application\ports\spi\repositoryInterfaces\UserRepositoryInterface;


class AuthService implements AuthServiceInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

 
    public function authenticate(string $email, string $password): UserProfile
    {
        if (empty($email) || empty($password)) {
            throw new AuthenticationException("Email et mot de passe requis");
        }

        $userData = $this->userRepository->findByEmail($email);
        
        if (!$userData) {
            throw new AuthenticationException("Utilisateur non trouvé");
        }

        if (!password_verify($password, $userData['password'])) {
            throw new AuthenticationException("Mot de passe incorrect");
        }

        return new UserProfile(
            $userData['id'],
            $userData['email'],
            (int) $userData['role']
        );
    }

    
    public function userExists(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        return $this->userRepository->existsByEmail($email);
    }
}