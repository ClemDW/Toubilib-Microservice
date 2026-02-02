<?php 
namespace toubilib\core\application\ports\api\dto;

use DateTime;
use toubilib\core\domain\entities\rdv\RendezVous;

class RdvDTO{
    public ?string $id;
    public DateTime $dateHeureDebut;
    public DateTime $dateHeureFin;
    public string $praticienId;
    public string $patientId;
    public string $motifVisite;
    public int $status;

    public function __construct(RendezVous $rdv){
        $this->id = $rdv->getId();
        $this->dateHeureDebut = $rdv->getDateHeureDebut();
        $this->dateHeureFin = $rdv->getDateHeureFin();
        $this->praticienId = $rdv->getPraticienId();
        $this->patientId = $rdv->getPatientId();
        $this->motifVisite = $rdv->getMotifVisite();
        $this->status = $rdv->getStatus();
    }
}