<?php

namespace toubilib\core\application\ports\api\dtos;

class RdvDTO
{
    public string $id;
    public string $praticien_id;
    public string $patient_id;
    public ?string $patient_email;
    public string $date_heure_debut;
    public int $status;
    public int $duree;
    public string $date_heure_fin;
    public string $date_creation;
    public string $motif_visite;

    public function __construct(string $id, string $praticien_id, string $patient_id, ?string $patient_email, string $date_heure_debut, int $status, int $duree, string $date_heure_fin, string $date_creation, string $motif_visite)
    {
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->patient_id = $patient_id;
        $this->patient_email = $patient_email;
        $this->date_heure_debut = $date_heure_debut;
        $this->status = $status;
        $this->duree = $duree;
        $this->date_heure_fin = $date_heure_fin;
        $this->date_creation = $date_creation;
        $this->motif_visite = $motif_visite;
    }

    public function getId(): string
    {
        return $this->id;
    }
    public function getPraticienId(): string
    {
        return $this->praticien_id;
    }
    public function getPatientId(): string
    {
        return $this->patient_id;
    }
    public function getPatientEmail(): ?string
    {
        return $this->patient_email;
    }
    public function getDateHeureDebut(): mixed
    {
        return new \DateTime($this->date_heure_debut);
    }
    public function getStatus(): int
    {
        return $this->status;
    }
    public function getDuree(): int
    {
        return $this->duree;
    }
    public function getDateHeureFin(): mixed
    {
        return new \DateTime($this->date_heure_fin);
    }
    public function getDateCreation(): mixed
    {
        return new \DateTime($this->date_creation);
    }
    public function getMotifVisite(): string
    {
        return $this->motif_visite;
    }

}
