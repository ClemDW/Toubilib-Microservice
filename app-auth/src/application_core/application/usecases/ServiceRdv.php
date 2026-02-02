<?php 
namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepository;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepository;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepository;
use toubilib\core\domain\entities\rdv\RendezVous;
use toubilib\core\application\ports\api\dto\InputRendezVousDTO;
use toubilib\core\application\ports\api\dto\RdvDTO;
use toubilib\core\application\exceptions\PraticienNotFoundException;
use toubilib\core\application\exceptions\PatientNotFoundException;
use toubilib\core\application\exceptions\InvalidMotifVisiteException;
use toubilib\core\application\exceptions\InvalideCreneauException;
use toubilib\core\application\exceptions\PraticienNotAvailableException;
use DateTime;

class ServiceRdv implements ServiceRdvInterface {
    public function listerAgendaPraticienParPeriode(string $praticienId, ?\DateTime $debut = null, ?\DateTime $fin = null): array {
        // Par défaut, période = journée en cours
        if ($debut === null) {
            $debut = new \DateTime('today 00:00:00');
        }
        if ($fin === null) {
            $fin = new \DateTime('today 23:59:59');
        }
        $rdvs = $this->rdvRepository->listerRdvOcuppePraticienParDate($debut, $fin, $praticienId);
        $rdvsDTO = [];
        foreach ($rdvs as $rdv) {
            $rdvsDTO[] = new \toubilib\core\application\ports\api\dto\RdvDTO($rdv);
        }
        return $rdvsDTO;
    }
    public function annulerRendezVous(string $idRdv): void {
        $rdv = $this->rdvRepository->consulterRendezVousParId($idRdv);
        if (!$rdv) {
            throw new \Exception("Le rendez-vous n'existe pas.");
        }
        $rdv->annuler();
        $this->rdvRepository->sauvegarderRendezVous($rdv);
    }
    private RdvRepository $rdvRepository;
    private PraticienRepository $praticienRepository;
    private PatientRepository $patientRepository;

    public function __construct(
        RdvRepository $rdvRepository,
        PraticienRepository $praticienRepository,
        PatientRepository $patientRepository
    ) {
        $this->rdvRepository = $rdvRepository;
        $this->praticienRepository = $praticienRepository;
        $this->patientRepository = $patientRepository;
    }

    public function listerRdvOcuppePraticienParDate(DateTime $debut, DateTime $fin, string $practicien_id): array {
        $rdvs = $this->rdvRepository->listerRdvOcuppePraticienParDate($debut, $fin, $practicien_id);
        $rdvsDTO = [];
        foreach ($rdvs as $rdv) {
            $rdvsDTO[] = new RdvDTO($rdv);
        }
        return $rdvsDTO;
    }

    public function consulterRendezVousParId(string $id): ?RdvDTO {
        $rdv = $this->rdvRepository->consulterRendezVousParId($id);
        if ($rdv) {
            return new RdvDTO($rdv);
        }
        return null;
    }

    public function creerRendezVous(InputRendezVousDTO $input): RdvDTO {
        // 1. Validation du praticien
        $praticien = $this->praticienRepository->PraticienParId($input->praticienId);
        if ($praticien === null) {
            throw new PraticienNotFoundException($input->praticienId);
        }

        // 2. Validation du patient
        if (!$this->patientRepository->patientExists($input->patientId)) {
            throw new PatientNotFoundException($input->patientId);
        }

        // 3. Validation du motif de visite
        $this->validateMotifVisite($praticien, $input->motifVisite);

        // 4. Validation du créneau horaire
        $this->validateTimeSlot($input->dateHeureDebut, $input->dateHeureFin);

        // 5. Validation de la disponibilité du praticien
        $this->validatePraticienAvailability($input->praticienId, $input->dateHeureDebut, $input->dateHeureFin);

        // Créer le rendez-vous si toutes les validations passent
        $rdv = new RendezVous(
            null,
            $input->praticienId,
            $input->patientId,
            $input->dateHeureDebut,
            $input->dateHeureFin,
            $input->motifVisite
        );

        $createdRdv = $this->rdvRepository->creerRendezVous($rdv);
        return new RdvDTO($createdRdv);
    }

    /**
     * Valider que le motif de visite est autorisé pour ce praticien
     */
    private function validateMotifVisite($praticien, string $motifVisite): void {
        $motifsTrouves = array_filter($praticien->getMotifVisite(), function($motif) use ($motifVisite) {
            return $motif->getLibelle() === $motifVisite;
        });

        if (empty($motifsTrouves)) {
            throw new InvalidMotifVisiteException($motifVisite, $praticien->getId());
        }
    }

    /**
     * Valider le créneau horaire (jour ouvré et heures de travail)
     */
    private function validateTimeSlot(DateTime $debut, DateTime $fin): void {
        // Vérifier que la fin est après le début
        if ($fin <= $debut) {
            throw InvalideCreneauException::pourDuréeInvalide($debut, $fin);
        }

        // Vérifier que c'est un jour ouvré (lundi = 1, dimanche = 7)
        $jourSemaine = (int)$debut->format('N');
        if ($jourSemaine >= 6) { // samedi = 6, dimanche = 7
            throw InvalideCreneauException::pourweekend($debut);
        }

        // Vérifier les heures de travail (8h-19h)
        $heureDebut = (int)$debut->format('H');
        $heureFin = (int)$fin->format('H');
        
        if ($heureDebut < 8 || $heureDebut >= 19 || $heureFin < 8 || $heureFin > 19) {
            throw InvalideCreneauException::pourheuresinvalides($debut);
        }
    }

    /**
     * Valider que le praticien est disponible pour le créneau
     */
    private function validatePraticienAvailability(string $praticienId, DateTime $debut, DateTime $fin): void {
        $rdvsExistants = $this->rdvRepository->listerRdvOcuppePraticienParDate($debut, $fin, $praticienId);
        
        foreach ($rdvsExistants as $rdvExistant) {
            // Vérifier s'il y a chevauchement
            if ($this->hasOverlap($debut, $fin, $rdvExistant->getDateHeureDebut(), $rdvExistant->getDateHeureFin())) {
                throw new PraticienNotAvailableException($praticienId, $debut, $fin);
            }
        }
    }

    /**
     * Vérifier s'il y a chevauchement entre deux créneaux
     */
    private function hasOverlap(DateTime $debut1, DateTime $fin1, DateTime $debut2, DateTime $fin2): bool {
        return $debut1 < $fin2 && $debut2 < $fin1;
    }

    private function rdvHonore(string $rdvId): void {
        $this->rdvRepository->rdvHonore($rdvId);
    }

    private function rdvRefuse(string $rdvId): void {
        $this->rdvRepository->rdvRefuse($rdvId);
    }

}