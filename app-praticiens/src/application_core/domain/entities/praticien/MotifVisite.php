<?php
namespace toubilib\core\domain\entities\praticien;

class MotifVisite
{

    public function __construct(
        private int $id,
        private int $specialite_id,
        private string $libelle
    ) {}

    public function getId(): int { return $this->id; }
    public function getLibelle(): string { return $this->libelle; }
    public function getSpecialiteId(): int { return $this->specialite_id; }
}