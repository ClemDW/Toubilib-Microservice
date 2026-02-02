<?php 
namespace toubilib\core\application\ports\api\dto;


use toubilib\core\domain\entities\praticien\MoyenPaiement;

class MoyenPaiementDTO {
    public int $id; 
    public string $libelle;

    public function __construct(MoyenPaiement $moyenPaiement) {
        $this->id = $moyenPaiement->getId();
        $this->libelle = $moyenPaiement->getLibelle();
    }
}