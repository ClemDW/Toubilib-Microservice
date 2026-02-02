<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\api\dtos\CredentialsDTO;
use toubilib\core\application\ports\api\dtos\ProfileDTO;
use toubilib\core\application\ports\api\ToubilibAuthnServiceInterface;
use toubilib\core\application\exceptions\AuthenticationFailedException;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;

class ToubilibAuthnService implements ToubilibAuthnServiceInterface {
    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository) {
        $this->authRepository = $authRepository;
    }

    public function byCredentials(CredentialsDTO $credentials): ProfileDTO {
        $user = $this->authRepository->byEmail($credentials->email);
        if (!password_verify($credentials->password, $user->getPassword())) {
            throw new AuthenticationFailedException('Invalid credentials');
        }
        return new ProfileDTO($user->getID(), $user->getEmail(), $user->getRole());
    }
}
