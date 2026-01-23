<?php

namespace toubilib\core\application\usecases;

use Exception;
use toubilib\core\application\ports\api\dtos\CredentialsDTO;
use toubilib\core\application\ports\api\dtos\ProfileDTO;
use toubilib\core\application\ports\api\ServiceAuthnInterface;
use toubilib\core\application\ports\spi\exceptions\InscriptionException;
use toubilib\core\application\ports\spi\exceptions\AuthentificationException;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;

class ServiceAuthn implements ServiceAuthnInterface
{

    private AuthRepositoryInterface $userRepository;

    public function __construct(AuthRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(CredentialsDTO $credentials, int $role): ProfileDTO
    {
        $existing = $this->userRepository->findByEmail($credentials->email);
        if ($existing) {
            throw new InscriptionException("Cet email n'est pas disponible");
        }

        $hashedPassword = password_hash($credentials->password, PASSWORD_BCRYPT);
        $user = $this->userRepository->create($credentials->email, $hashedPassword, $role);

        return new ProfileDTO($user->getId(), $user->getEmail(), $user->getRole());
    }

    public function byCredentials(CredentialsDTO $credentials): ProfileDTO
    {
        $user = $this->userRepository->findByEmail($credentials->email);
        if (!$user) {
            throw new AuthentificationException("Utilisateur ou mot de passe incorrect.");
        }

        if (!password_verify($credentials->password, $user->getPassword())) {
            throw new AuthentificationException("Utilisateur ou mot de passe incorrect.");
        }

        return new ProfileDTO($user->getId(), $user->getEmail(), $user->getRole());
    }

    public function signin(CredentialsDTO $credentials): ProfileDTO
    {
        $user = $this->userRepository->findByEmail($credentials->email);
        if (!$user) {
            throw new Exception("Utilisateur introuvable.");
        }

        if (!password_verify($credentials->password, $user->getPassword())) {
            throw new Exception("Mot de passe incorrect.");
        }

        return new ProfileDTO($user->getId(), $user->getEmail(), $user->getRole());
    }
}