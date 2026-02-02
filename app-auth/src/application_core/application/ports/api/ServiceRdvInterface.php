<?php 
namespace toubilib\core\application\ports\api;
use toubilib\core\domain\entities\rdv\RendezVous;
use toubilib\core\application\ports\api\dto\RdvDTO;
use toubilib\core\application\ports\api\dto\InputRendezVousDTO;
use DateTime;
interface ServiceRdvInterface {
    /**
     * Liste l'agenda d'un praticien sur une période donnée
     * @return RdvDTO[]
     */
    public function listerAgendaPraticienParPeriode(string $praticienId, ?\DateTime $debut = null, ?\DateTime $fin = null): array;
    public function listerRdvOcuppePraticienParDate(DateTime $debut, DateTime $fin, string $practicien_id): array;
    
    public function consulterRendezVousParId(string $id): ?RdvDTO;

    public function creerRendezVous(InputRendezVousDTO $input): RdvDTO;

    public function annulerRendezVous(string $idRdv): void;
}