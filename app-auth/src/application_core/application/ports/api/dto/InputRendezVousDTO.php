<?php
namespace toubilib\core\application\ports\api\dto;
use DateTime;
use toubilib\core\domain\entities\rdv\RendezVous;

class InputRendezVousDTO{
    public DateTime $dateHeureDebut;
    public DateTime $dateHeureFin;
    public string $praticienId;  
    public string $patientId;   
    public string $motifVisite; 

    public function __construct(DateTime $dateHeureDebut, DateTime $dateHeureFin, string $praticienId, string $patientId, string $motifVisite){
        $this->dateHeureDebut = $dateHeureDebut;
        $this->dateHeureFin = $dateHeureFin;
        $this->praticienId = $praticienId;
        $this->patientId = $patientId;
        $this->motifVisite = $motifVisite;
    }
}
