<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\application\ports\api\dtos\InputRendezVousDTO;
use toubilib\core\application\ports\api\dtos\InputIndisponibiliteDTO;
use toubilib\core\domain\entities\praticien\Rdv;
use toubilib\core\domain\entities\praticien\PraticienDetails;
use toubilib\core\domain\entities\praticien\Patient;

interface ServiceRdvInterface
{
    public function getCreneauxOccupes(string $praticienId, \DateTime $dateDebut, \DateTime $dateFin): array;

    public function getRdvById(string $rdvId): ?Rdv;

    public function getPraticienDetails(string $id): ?PraticienDetails;

    public function getPatientById(string $id): ?Patient;

    public function creerRendezVous(InputRendezVousDTO $dto): Rdv;

    public function annulerRendezVous(string $idRdv): void;

    public function consulterAgenda(string $praticienId, \DateTime $dateDebut, \DateTime $dateFin): array;

    public function getHistoriquePatient(string $patientId): array;

    public function creerIndisponibilite(InputIndisponibiliteDTO $dto): string;

    public function marquerRdvHonore(string $idRdv): void;

    public function marquerRdvNonHonore(string $idRdv): void;
}
