<?php 
namespace toubilib\core\domain\entities\praticien;

class MotifVisite
{
    private int $id;
    private int $specialite_id;
    private string $libelle;

    public function __construct(int $id, int $specialite_id, string $libelle)
    {
        $this->id = $id;
        $this->specialite_id = $specialite_id;
        $this->libelle = $libelle;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getSpecialiteId(): int
    {
        return $this->specialite_id;    
    }
    public function getLibelle(): string
    {
        return $this->libelle;
    }
}