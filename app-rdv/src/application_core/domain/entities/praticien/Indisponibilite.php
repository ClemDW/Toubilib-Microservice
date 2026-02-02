<?php

namespace toubilib\core\domain\entities\praticien;

class Indisponibilite
{
    private string $id;
    private string $praticienId;
    private \DateTimeImmutable $dateDebut;
    private \DateTimeImmutable $dateFin;
    private ?string $motif;

    public function __construct(
        string $id,
        string $praticienId,
        \DateTimeImmutable $dateDebut,
        \DateTimeImmutable $dateFin,
        ?string $motif = null
    ) {
        $this->id = $id;
        $this->praticienId = $praticienId;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->motif = $motif;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPraticienId(): string
    {
        return $this->praticienId;
    }

    public function getDateDebut(): \DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function getDateFin(): \DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }
}
