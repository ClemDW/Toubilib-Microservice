<?php 
namespace toubilib\core\application\ports\api\dto;

use toubilib\core\domain\entities\praticien\MotifVisite;

class MotifVisiteDTO {
    public int $id; 
    public int $specialite_id;
    public string $libelle;

    public function __construct(MotifVisite $motifVisite) {
        $this->id = $motifVisite->getId();
        $this->specialite_id = $motifVisite->getSpecialiteId();
        $this->libelle = $motifVisite->getLibelle();
    }
}