<?php
namespace toubilib\core\application\ports\api\dto;

use toubilib\core\domain\entities\praticien\Specialite;

class SpecialiteDTO {
    public int $id;
    public string $libelle;
    public string $description;

    public function __construct(Specialite $specialite) {
        $this->id = $specialite->getId();
        $this->libelle = $specialite->getLibelle();
        $this->description = $specialite->getDescription();
    }
}