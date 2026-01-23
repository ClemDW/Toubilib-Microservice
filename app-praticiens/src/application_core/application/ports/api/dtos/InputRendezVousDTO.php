<?php

namespace toubilib\core\application\ports\api\dtos;

class InputRendezVousDTO
{
    public string $praticienId;
    public string $patientId;
    public \DateTime $dateHeureDebut;

    public int $duree;
    public ?string $motifVisite;

    public function __construct(string $praticienId, string $patientId, \DateTime $dateHeureDebut, int $duree = 30, ?string $motifVisite = null) {
        $this->praticienId = $praticienId;
        $this->patientId = $patientId;
        $this->dateHeureDebut = $dateHeureDebut;
        $this->duree = $duree;
        $this->motifVisite = $motifVisite;
    }

    public function getPraticienId(): string
    {
        return $this->praticienId;
    }

    public function getPatientId(): string
    {
        return $this->patientId;
    }

    public function getDateHeureDebut(): \DateTime
    {
        return $this->dateHeureDebut;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function getMotifVisite(): ?string
    {
        return $this->motifVisite;
    }
}
