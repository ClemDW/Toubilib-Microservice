<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\api\ServicePraticienInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepository;
use toubilib\core\application\ports\api\dto\PraticienDTO;
use toubilib\core\application\ports\api\dto\PraticienIdDTO;

class ServicePraticien implements ServicePraticienInterface
{
    private PraticienRepository $praticienRepository;

    public function __construct(PraticienRepository $praticienRepository)
    {
        $this->praticienRepository = $praticienRepository;
    }

    public function listerPraticiens(): array {
        $praticiens = $this->praticienRepository->listerPraticiens();
        $praticiensDTO = [];
        foreach ($praticiens as $praticien) {
            $praticiensDTO[] = new PraticienDTO($praticien);
        }
        
        return $praticiensDTO;
    }
    public function PraticienParId(string $id): ?PraticienIdDTO {
        $praticien = $this->praticienRepository->PraticienParId($id);
        if ($praticien) {
            return new PraticienIdDTO($praticien);
        }
        return null;
    }
    public function praticienParSpecialite(string $specialite): array {
        $praticiens = $this->praticienRepository->praticienParSpecialite($specialite);
        $praticiensDTO = [];
        foreach ($praticiens as $praticien) {
            $praticiensDTO[] = new PraticienDTO($praticien);
        }
        
        return $praticiensDTO;
    }
    public function praticienParVille(string $ville): array {
        $praticiens = $this->praticienRepository->praticienParVille($ville);
        $praticiensDTO = [];
        foreach ($praticiens as $praticien) {
            $praticiensDTO[] = new PraticienDTO($praticien);
        }
        
        return $praticiensDTO;
    }
}