<?php
 namespace toubilib\core\application\ports\spi\repositoryInterfaces;

use toubilib\core\domain\entities\praticien\Praticien;

interface PraticienRepository{
    /**
     * @return Praticien[]
     */
    public function listerPraticiens(): array;

    public function PraticienParId(string $id): ?Praticien;

    public function praticienParSpecialite(string $specialite): array;

    public function praticienParVille(string $ville): array;
}

