<?php
namespace toubilib\core\application\ports\api\dtos;

use toubilib\core\domain\entities\praticien\Specialite;

class SpecialiteDTO
{
    public int $id;
    public string $libelle;
    public ?string $description;

    public function __construct(int $id, string $libelle, ?string $description)
    {
        $this->id = $id;
        $this->libelle = $libelle;
        $this->description = $description;
    }

    /**
     * Fabrique un DTO à partir de l’entité domaine Specialite
     */
    public static function fromEntity(Specialite $specialite): self
    {
        return new self(
            $specialite->getId(),
            $specialite->getLibelle(),
            $specialite->getDescription()
        );
    }

    /**
     * Convertit le DTO en tableau (utile pour JSON)
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'description' => $this->description,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setLibelle(string $libelle): void
    {
        $this->libelle = $libelle;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
