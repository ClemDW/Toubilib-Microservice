<?php

namespace toubilib\core\application\ports\api\dtos;

class PatientDTO
{
    public string $id;
    public string $nom;
    public string $prenom;
    public ?string $dateNaissance;
    public ?string $adresse;
    public ?string $codePostal;
    public ?string $ville;
    public ?string $email;
    public string $telephone;
    public string $password;

    public function __construct(string $id, string $nom, string $prenom, ?string $dateNaissance, ?string $adresse, ?string $codePostal, ?string $ville, ?string $email, string $telephone, string $password) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->dateNaissance = $dateNaissance;
        $this->adresse = $adresse;
        $this->codePostal = $codePostal;
        $this->ville = $ville;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->password = $password;
    }
}
