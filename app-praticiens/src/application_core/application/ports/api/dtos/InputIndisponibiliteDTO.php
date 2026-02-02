<?php

namespace toubilib\core\application\ports\api\dtos;

class InputIndisponibiliteDTO
{
    public string $praticienId;
    public string $dateDebut;
    public string $dateFin;
    public ?string $motif;

    public function __construct(
        string $praticienId,
        string $dateDebut,
        string $dateFin,
        ?string $motif = null
    ) {
        $this->praticienId = $praticienId;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->motif = $motif;
    }
}
