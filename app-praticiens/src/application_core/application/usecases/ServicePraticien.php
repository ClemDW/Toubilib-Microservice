<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\api\dtos\PraticienDTO;
use toubilib\core\application\ports\api\ServicePraticienInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;

class ServicePraticien implements ServicePraticienInterface
{
    private PraticienRepositoryInterface $praticienRepository;

    public function __construct(PraticienRepositoryInterface $praticienRepository)
    {
        $this->praticienRepository = $praticienRepository;
    }

    public function listerPraticiens(): array
    {
        $praticiens = $this->praticienRepository->findAll();

        return array_map(function ($praticien) {
            return new PraticienDTO(
                $praticien->getId(),
                $praticien->getNom(),
                $praticien->getPrenom(),
                $praticien->getSpecialite(),
                $praticien->getVille(),
                $praticien->getEmail()
            );
        }, $praticiens);
    }

    public function afficherPraticien(string $id): PraticienDTO
    {
        $praticien = $this->praticienRepository->get($id);

        return new PraticienDTO(
            $praticien->getId(),
            $praticien->getNom(),
            $praticien->getPrenom(),
            $praticien->getSpecialite(),
            $praticien->getVille(),
            $praticien->getEmail(),
            $praticien->getTelephone(),
            $praticien->getMotifVisite(),
            $praticien->getMoyenPayement()
        );

    }
}