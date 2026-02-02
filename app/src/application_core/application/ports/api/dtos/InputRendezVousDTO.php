<?php

namespace toubilib\core\application\ports\api\dtos;

use toubilib\core\domain\entities\praticien\Rdv;

use DateTimeImmutable;

class InputRendezVousDTO
{
    public string $praticienId;
    public string $patientId;
    public \DateTimeImmutable $dateHeureDebut;
    public string $motifVisite;
    public int $duree;

    public function __construct(
        string $praticienId,
        string $patientId,
        \DateTimeImmutable $dateHeureDebut,
        string $motifVisite,
        int $duree
    ) {
        $this->praticienId    = $praticienId;
        $this->patientId      = $patientId;
        $this->dateHeureDebut = $dateHeureDebut;
        $this->motifVisite    = $motifVisite;
        $this->duree          = $duree;
    }

    public static function fromEntity(Rdv $rdv): self
    {
        return new self(
            $rdv->getPraticienId(),
            $rdv->getPatientId(),
            $rdv->getDateHeureDebut(),
            $rdv->getMotifVisite(),
            $rdv->getDuree()
        );
    }
}

