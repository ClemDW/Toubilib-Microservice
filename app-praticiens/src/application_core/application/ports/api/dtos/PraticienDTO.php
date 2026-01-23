<?php

namespace toubilib\core\application\ports\api\dtos;

class PraticienDTO
{
    public string $id;
    public string $nom;
    public string $prenom;
    public string $specialite;
    public string $ville;
    public string $email;
    public string $telephone;
    public array $motif_visite;
    public array $moyen_payement;

    public function __construct(string $id, string $nom, string $prenom, string $specialite, string $ville, string $email, string $telephone = '', array $motif_visite = [], array $moyen_payement = [])
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
}
