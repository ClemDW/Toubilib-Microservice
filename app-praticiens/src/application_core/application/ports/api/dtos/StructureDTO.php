<?php
namespace toubilib\core\application\ports\api\dtos;

use toubilib\core\domain\entities\praticien\Structure;

final class StructureDTO
{
    public function __construct(
        public string $id,
        public string $nom,
        public string $adresse,
        public ?string $ville,
        public ?string $codePostal,
        public ?string $telephone
    ) {}

    public static function fromEntity(Structure $structure): self
    {
        return new self(
            $structure->getId(),
            $structure->getNom(),
            $structure->getAdresse(),
            $structure->getVille(),
            $structure->getCodePostal(),
            $structure->getTelephone()
        );
    }

    public function getId(): string { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getAdresse(): string { return $this->adresse; }
    public function getVille(): ?string { return $this->ville; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function getTelephone(): ?string { return $this->telephone; }
}