<?php

namespace toubilib\core\domain\entities\praticien;

use DateTimeImmutable;
use Exception;  

class Rdv
{
  private string $id;
  private string $praticienId;
  private string $patientId;
  private ?string $patientEmail;
  private \DateTimeImmutable $dateHeureDebut;
  private int $status;
  private int $duree;
  private ?\DateTimeImmutable $dateHeureFin;
  private ?\DateTimeImmutable $dateCreation;
  private ?string $motifVisite;

  public function __construct(
    string $id,
    string $praticienId,
    string $patientId,
    ?string $patientEmail,
    \DateTimeImmutable $dateHeureDebut,
    int $status = 0,
    int $duree = 30,
    ?\DateTimeImmutable $dateHeureFin = null,
    ?\DateTimeImmutable $dateCreation = null,
    ?string $motifVisite = null
  ) {
    $this->id = $id;
    $this->praticienId = $praticienId;
    $this->patientId = $patientId;
    $this->patientEmail = $patientEmail;
    $this->dateHeureDebut = $dateHeureDebut;
    $this->status = $status;
    $this->duree = $duree;
    $this->dateHeureFin = $dateHeureFin;
    $this->dateCreation = $dateCreation;
    $this->motifVisite = $motifVisite;
  }

  public function annuler(): void
    {
        if ($this->status === 1) {
            throw new Exception("Ce rendez-vous est déjà annulé.");
        }

        $now = new DateTimeImmutable();
        if ($this->dateHeureDebut <= $now) {
            throw new Exception("Impossible d'annuler un rendez-vous passé.");
        }

        $this->status = 1; // état "annulé"
    }

    public function marquerCommeHonore(): void
    {
        if ($this->status === 1) {
            throw new Exception("Impossible de marquer un rendez-vous annulé comme honoré.");
        }
        $this->status = 2; // état "honoré"
    }

    public function marquerCommeNonHonore(): void
    {
        if ($this->status === 1) {
            throw new Exception("Impossible de marquer un rendez-vous annulé comme non honoré.");
        }
        $this->status = 3; // état "non honoré"
    }

  public function getId(): string
  {
    return $this->id;
  }

  public function getPraticienId(): string
  {
    return $this->praticienId;
  }

  public function getPatientId(): string
  {
    return $this->patientId;
  }

  public function getPatientEmail(): ?string
  {
    return $this->patientEmail;
  }

  public function getDateHeureDebut(): \DateTimeImmutable
  {
    return $this->dateHeureDebut;
  }

  public function getStatus(): int
  {
    return $this->status;
  }

  public function getDuree(): int
  {
    return $this->duree;
  }

  public function getDateHeureFin(): ?\DateTimeImmutable
  {
    return $this->dateHeureFin;
  }

  public function getDateCreation(): ?\DateTimeImmutable
  {
    return $this->dateCreation;
  }

  public function getMotifVisite(): ?string
  {
    return $this->motifVisite;
  }

}