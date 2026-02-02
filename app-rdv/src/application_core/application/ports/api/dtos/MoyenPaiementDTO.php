<?php
namespace toubilib\core\application\ports\api\dtos;

use toubilib\core\domain\entities\praticien\MoyenPaiement;

class MoyenPaiementDTO
{
    public int $id;
    public string $libelle;

    public function __construct(int $id, string $libelle)
    {
        $this->id = $id;
        $this->libelle = $libelle;
    }

    /**
     * Crée un DTO à partir de l’entité domaine MoyenPaiement
     */
    public static function fromEntity(MoyenPaiement $moyen): self
    {
        return new self(
            $moyen->getId(),
            $moyen->getLibelle()
        );
    }

    /**
     * Convertit le DTO en tableau pour JSON
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
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
}
