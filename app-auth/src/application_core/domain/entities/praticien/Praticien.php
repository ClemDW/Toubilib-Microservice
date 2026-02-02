<?php

namespace toubilib\core\domain\entities\praticien;
use toubilib\core\domain\entities\praticien\Specialite;
use toubilib\core\domain\entities\praticien\MotifVisite;
use toubilib\core\domain\entities\praticien\MoyenPaiement;


class Praticien{
    private string $id;
    private string $nom;
    private string $prenom;
    private string $ville;
    private string $email;
    private Specialite $specialite;
    private array $motifVisite;
    private array $moyenPaiement;


    public function __construct(string $id, string $nom, string $prenom, string $ville, string $email, Specialite $specialite, array $motifVisite, array $moyenPaiement)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->ville = $ville;
        $this->email = $email;
        $this->specialite = $specialite;
        $this->motifVisite = $motifVisite;
        $this->moyenPaiement = $moyenPaiement;

    }
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
    public function getVille(): string
    {
        return $this->ville;    
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getSpecialite(): Specialite
    {
        return $this->specialite;
    }
    public function getMotifVisite(): array
    {
        return $this->motifVisite;
    }
    public function getMoyenPaiement(): array
    {
        return $this->moyenPaiement;
    }
}