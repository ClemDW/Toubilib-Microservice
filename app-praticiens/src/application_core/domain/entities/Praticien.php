<?php

namespace toubilib\core\domain\entities;

use Ramsey\Uuid\Uuid;

class Praticien
{
    private string $id;
    private string $nom;
    private string $prenom;
    private string $specialite;
    private string $ville;
    private string $email;
    private string $telephone;
    private array $motif_visite;
    private array $moyen_payement;

    public function __construct(string $id, string $nom, string $prenom, string $specialite, string $ville, string $email, string $telephone="", array $motif_visite=[], array $moyen_payement=[])
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->specialite = $specialite;
        $this->ville = $ville;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->motif_visite = $motif_visite;
        $this->moyen_payement = $moyen_payement;
    }

    /* GETTERS */

    public function getId(): string
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getSpecialite(): string
    {
        return $this->specialite;
    }

    public function getVille(): string
    {
        return $this->ville;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function getMotifVisite(): array
    {
        return $this->motif_visite;
    }

    public function getMoyenPayement(): array
    {
        return $this->moyen_payement;
    }
}