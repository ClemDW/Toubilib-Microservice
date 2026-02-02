<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;

use toubilib\core\domain\entities\praticien\Indisponibilite;

interface IndisponibiliteRepositoryInterface
{
    public function save(Indisponibilite $indisponibilite): void;
    public function getIndisponibilitesByPraticienAndPeriode(string $praticienId, \DateTimeImmutable $dateDebut, \DateTimeImmutable $dateFin): array;
}
