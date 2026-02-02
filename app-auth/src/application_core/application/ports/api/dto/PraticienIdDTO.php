<?php 
namespace toubilib\core\application\ports\api\dto;

use toubilib\core\domain\entities\praticien\Praticien;

class PraticienIdDTO {
    public string $id; 
    public string $nom;
    public string $prenom;
    public string $ville;
    public string $email;
    public SpecialiteDTO $specialite;
    public array $motifVisite;
    public array $moyenPaiement ;

    public function __construct(Praticien $praticien) {
        $this->id = $praticien->getId();
        $this->nom = $praticien->getNom();
        $this->prenom = $praticien->getPrenom();
        $this->ville = $praticien->getVille();
        $this->email = $praticien->getEmail();
        $this->specialite = new SpecialiteDTO($praticien->getSpecialite());
        $this->motifVisite = array_map(fn($motif) => new MotifVisiteDTO($motif), $praticien->getMotifVisite());
        $this->moyenPaiement = array_map(fn($moyen) => new MoyenPaiementDTO($moyen), $praticien->getMoyenPaiement());
    }
}