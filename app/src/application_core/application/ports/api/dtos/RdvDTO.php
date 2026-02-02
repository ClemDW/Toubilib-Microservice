<?php

namespace toubilib\core\application\ports\api\dtos;
use toubilib\core\domain\entities\Rdv;

class RdvDTO
{
  public string $id;
  public string $praticienId;
  public string $patientId;
  public ?string $patientEmail;
  public string $dateHeureDebut;
  public int $status;
  public int $duree;
  public ?string $dateHeureFin;
  public ?string $dateCreation;
  public ?string $motifVisite;

  public function __construct(
    string $id,
    string $praticienId,
    string $patientId,
    ?string $patientEmail,
    string $dateHeureDebut,
    int $status = 0,
    int $duree = 30,
    ?string $dateHeureFin = null,
    ?string $dateCreation = null,
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

  public static function fromEntity(Rdv $rdv): self
  {
    return new self(
      $rdv->getId(),
      $rdv->getPraticienId(),
      $rdv->getPatientId(),
      $rdv->getPatientEmail(),
      $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
      $rdv->getStatus(),
      $rdv->getDuree(),
      $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
      $rdv->getDateCreation()?->format('Y-m-d H:i:s'),
      $rdv->getMotifVisite()
    );
  }
}