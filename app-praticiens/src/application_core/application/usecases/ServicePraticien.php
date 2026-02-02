<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\api\ServicePraticienInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;  


    
class ServicePraticien implements ServicePraticienInterface
{
    private PraticienRepositoryInterface $praticienRepository;

    public function __construct(PraticienRepositoryInterface $praticienRepository)
    {
        $this->praticienRepository = $praticienRepository;
    }

    public function getAllPraticiens(): array {
    	
        return $this->praticienRepository->findAll();
    }


    public function getPraticienDetail(string $id) {
        return $this->praticienRepository->findById($id);
    }

    public function searchPraticiens(string $specialite, string $ville): array {
        return $this->praticienRepository->search($specialite, $ville);
    }
}