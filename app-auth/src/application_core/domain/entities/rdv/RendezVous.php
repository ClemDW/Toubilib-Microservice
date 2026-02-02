<?php

namespace toubilib\core\domain\entities\rdv;

use DateTime;

class RendezVous{
    private ?string $id;
    private string $praticien_id;
    private string $patient_id;
    private DateTime $dateHeureDebut;
    private DateTime $dateHeureFin;
    private string $motif_visite;
    private int $status; // 0 = actif, 1 = annulé

    public function __construct(?string $id, string $praticien_id, string $patient_id, DateTime $dateHeureDebut, DateTime $dateHeureFin, string $motif_visite, int $status = 0){
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->patient_id = $patient_id;
        $this->dateHeureDebut = $dateHeureDebut;
        $this->dateHeureFin = $dateHeureFin;
        $this->motif_visite = $motif_visite;
        $this->status = $status;
    }
    public function getStatus(): int {
        return $this->status;
    }

    public function annuler(): void {
        if ($this->status === 1) {
            throw new \Exception("Le rendez-vous est déjà annulé.");
        }
        $now = new \DateTime();
        if ($this->dateHeureDebut <= $now) {
            throw new \Exception("Impossible d'annuler un rendez-vous passé ou en cours.");
        }
        $this->status = 1;
    }
    public function getId(): ?string{
        return $this->id;
    }
    public function getPraticienId(): string{
        return $this->praticien_id;
    }
    public function getPatientId(): string{
        return $this->patient_id;
    }
    public function getDateHeureDebut(): DateTime{
        return $this->dateHeureDebut;
    }
    public function getDateHeureFin(): DateTime{
        return $this->dateHeureFin;
    }
    public function getMotifVisite(): string{
        return $this->motif_visite;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }
}