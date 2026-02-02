<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;
use toubilib\core\domain\entities\praticien\Rdv;

interface RdvRepositoryInterface
{
    public function getCreneauxOccupes(string $praticienId, string $dateDebut, string $dateFin): array;

    public function findById(string $id): ?Rdv;

    public function save(Rdv $rdv): void;

    public function update(Rdv $rdv): void;

    public function findRdvsByPatientId(string $patientId): array;
}