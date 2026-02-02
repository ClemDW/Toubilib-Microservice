<?php
namespace toubilib\core\application\ports\api\dtos;

use toubilib\core\domain\entities\praticien\MotifVisite;

class MotifVisiteDTO
{
    public int $id;
    public int $specialiteId;
    public string $libelle;

    public function __construct(int $id, int $specialiteId, string $libelle)
    {
        $this->id = $id;
        $this->specialiteId = $specialiteId;
        $this->libelle = $libelle;
    }

    /**
     * Crée un DTO à partir de l’entité domaine MotifVisite
     */
    public static function fromEntity(MotifVisite $motif): self
    {
        return new self(
            $motif->getId(),
            $motif->getSpecialiteId(),
            $motif->getLibelle()
        );
    }

    /**
     * Convertit le DTO en tableau pour JSON
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'specialiteId' => $this->specialiteId,
            'libelle' => $this->libelle,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSpecialiteId(): int
    {
        return $this->specialiteId;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }
}
