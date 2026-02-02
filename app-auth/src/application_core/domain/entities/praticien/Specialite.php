<?php

namespace toubilib\core\domain\entities\praticien;



class Specialite
{
    private int $id;
    private string $libelle;
    private string $description;

    public function __construct(int $id, string $libelle, string $description)
    {
        $this->id = $id;
        $this->libelle = $libelle;
        $this->description = $description;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getLibelle(): string
    {
        return $this->libelle;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
}