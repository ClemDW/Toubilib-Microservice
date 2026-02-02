<?php

namespace toubilib\core\application\ports\api\dtos;

class InputPatientDTO
{
    public string $nom;
    public string $prenom;
    public ?string $dateNaissance;
    public ?string $adresse;
    public ?string $codePostal;
    public ?string $ville;
    public string $email;
    public string $telephone;

    public function __construct(
        string $nom,
        string $prenom,
        string $email,
        string $telephone,
        ?string $dateNaissance = null,
        ?string $adresse = null,
        ?string $codePostal = null,
        ?string $ville = null
    ) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->dateNaissance = $dateNaissance;
        $this->adresse = $adresse;
        $this->codePostal = $codePostal;
        $this->ville = $ville;
    }
}
