<?php

namespace toubilib\core\application\usecases;

use DateTime;
use DateTimeZone;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;
use toubilib\core\application\ports\api\dtos\InputRendezVousDTO;
use toubilib\core\application\ports\api\dtos\RdvDTO;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\spi\exceptions\CreneauInvalideException;
use toubilib\core\application\ports\spi\exceptions\MotifInvalideException;
use toubilib\core\application\ports\spi\exceptions\PatientNonTrouveException;
use toubilib\core\application\ports\spi\exceptions\PraticienIndisponibleException;
use toubilib\core\application\ports\spi\exceptions\PraticienNonTrouveException;
use toubilib\core\application\ports\spi\exceptions\RendezVousNonTrouveException;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\domain\entities\Rdv;
use Ramsey\Uuid\Uuid;

class ServiceRdv implements ServiceRdvInterface
{
    private RdvRepositoryInterface $rdvRepository;
    private PraticienRepositoryInterface $praticienRepository;
    private PatientRepositoryInterface $patientRepository;

    public function __construct(RdvRepositoryInterface $rdvRepository, PraticienRepositoryInterface $praticienRepository, PatientRepositoryInterface $patientRepository)
    {
        $this->rdvRepository = $rdvRepository;
        $this->praticienRepository = $praticienRepository;
        $this->patientRepository = $patientRepository;
    }

    public function listerRdv(): array
    {
        $rdvs = $this->rdvRepository->findAll();

        return array_map(function ($rdv) {
            return new RdvDTO(
                $rdv->getId(),
                $rdv->getPraticienId(),
                $rdv->getPatientId(),
                $rdv->getPatientEmail(),
                $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                $rdv->getStatus(),
                $rdv->getDuree(),
                $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
                $rdv->getDateCreation()->format('Y-m-d H:i:s'),
                $rdv->getMotifVisite()
            );
        }, $rdvs);
    }


    public function getRdvPraticienPeriode(string $praticienId, string $debutPeriode, string $finPeriode): array
    {
        // Vérifier si le praticien existe
        $this->praticienRepository->get($praticienId);

        // Validation des paramètres
        $validation = $this->validatePraticienPeriod($praticienId, $debutPeriode, $finPeriode);
        if (!$validation['valid']) {
            throw new Exception($validation['message']);
        }

        // transforme la date locale en UTC
        $dateDebut = new \DateTime($debutPeriode . ' 00:00:00', new \DateTimeZone('Europe/Paris'));
        $dateFin = new \DateTime($finPeriode . ' 23:59:59', new \DateTimeZone('Europe/Paris'));

        // Convertit en UTC pour la BDD
        $dateDebut->setTimezone(new \DateTimeZone('UTC'));
        $dateFin->setTimezone(new \DateTimeZone('UTC'));

        // Récupération des RDV
        $rdvs = $this->rdvRepository->getRdvByPraticienAndPeriod($praticienId, $dateDebut, $dateFin);

        return [
            'type' => 'rdv_praticien_periode',
            'praticien_id' => $praticienId,
            'data' => $rdvs,
            'periode' => [
                'debut' => $dateDebut->format('Y-m-d H:i:s'),
                'fin' => $dateFin->format('Y-m-d H:i:s')
            ],
            'count' => count($rdvs)
        ];
    }

    public function getRdv(string $id): array
    {
        $rdv = $this->rdvRepository->getRdv($id);
        return [
            'rdv' => $rdv
        ];
    }

    public function getRdvPraticien(string $praticienId): array
    {
        // Vérifier si le praticien existe
        $this->praticienRepository->get($praticienId);

        // Logique pour récupérer les RDV du praticien sans période (ex: prochains RDV)
        // Période par défaut : 30 prochains jours
        $dateDebut = new DateTime('today');
        $dateFin = new DateTime('+30 days');

        $rdvs = $this->rdvRepository->getRdvByPraticienAndPeriod($praticienId, $dateDebut, $dateFin);

        return [
            'type' => 'rdv_praticien_defaut',
            'praticien_id' => $praticienId,
            'data' => $rdvs,
            'periode' => [
                'debut' => $dateDebut->format('Y-m-d'),
                'fin' => $dateFin->format('Y-m-d')
            ],
            'count' => count($rdvs),
            'message' => count($rdvs) === 0 ? 'Aucun rendez-vous de prevu pour les 30 prochains jours' : 'Période par défaut : 30 prochains jours'
        ];
    }

    public function validatePraticienPeriod($praticienId, $debutPeriode, $finPeriode): array
    {
        // Vérifier le format UUID du praticien_id
        if (!$this->isValidUuid($praticienId)) {
            return [
                'valid' => false,
                'message' => 'Format de praticien invalide. UUID attendu'
            ];
        }

        // Vérifier le format des dates
        if (!$this->isValidDateFormat($debutPeriode)) {
            return [
                'valid' => false,
                'message' => 'Format de debutPeriode invalide. Utilisez YYYY-MM-DD'
            ];
        }

        if (!$this->isValidDateFormat($finPeriode)) {
            return [
                'valid' => false,
                'message' => 'Format de finPeriode invalide. Utilisez YYYY-MM-DD'
            ];
        }

        try {
            $dateDebut = new DateTime($debutPeriode);
            $dateFin = new DateTime($finPeriode);

            // Vérifier que la date de début est antérieure ou égale à la date de fin
            if ($dateDebut > $dateFin) {
                return [
                    'valid' => false,
                    'message' => 'La date de début doit être antérieure ou égale à la date de fin'
                ];
            }

            // Vérifier que les dates ne sont pas trop anciennes
            $dateMin = new DateTime('-1 year');
            if ($dateDebut < $dateMin) {
                return [
                    'valid' => false,
                    'message' => 'La date de début ne peut pas être antérieure à 1 an'
                ];
            }

            // Vérifier que les dates ne sont pas trop dans le futur
            $dateMax = new DateTime('+2 years');
            if ($dateFin > $dateMax) {
                return [
                    'valid' => false,
                    'message' => 'La date de fin ne peut pas être supérieure à 2 ans dans le futur'
                ];
            }

        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => 'Dates invalides : ' . $e->getMessage()
            ];
        }

        return ['valid' => true];
    }

    public function isValidDateFormat($date): bool
    {
        // Vérifier le format YYYY-MM-DD avec une regex
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        // Vérifier que la date est valide
        $parts = explode('-', $date);
        return checkdate($parts[1], $parts[2], $parts[0]);
    }

    public function isValidUuid($uuid): bool
    {
        return Uuid::isValid($uuid);
    }

    public function creerRendezVous(InputRendezVousDTO $dto): void
    {
        // Vérif 1 : praticien existe
        $praticien = $this->praticienRepository->get($dto->getPraticienId());
        if (!$praticien) {
            throw new PraticienNonTrouveException("Praticien inexistant");
        }

        // Vérif 2 : patient existe
        $patient = $this->patientRepository->findById($dto->getPatientId());
        if (!$patient) {
            throw new PatientNonTrouveException("Patient inexistant");
        }

        // Vérif 3 : motif valide pour ce praticien
        if (!in_array($dto->getMotifVisite(), $praticien->getMotifVisite())) {
            throw new MotifInvalideException("Motif invalide pour ce praticien");
        }

        // Vérif 4 : créneau valide (jour ouvré et horaires entre 8h et 19h)
        $dateDebut = $dto->getDateHeureDebut();
        $jour = (int) $dateDebut->format('N'); // 1=lundi, 7=dimanche
        $heure = (int) $dateDebut->format('H');

        if ($jour >= 6) {
            throw new CreneauInvalideException("Les rendez-vous ne sont possibles que du lundi au vendredi");
        }
        if ($heure < 8 || $heure >= 19) {
            throw new CreneauInvalideException("Créneau horaire invalide (doit être entre 8h et 19h)");
        }

        // Vérif 5 : praticien disponible (pas déjà un rdv sur ce créneau)
        if (!$this->rdvRepository->isPraticienDisponible($dto->getPraticienId(), $dto->getDateHeureDebut(), $dto->getDuree())) {
            throw new PraticienIndisponibleException("Le praticien n'est pas disponible pour ce créneau");
        }

        $this->rdvRepository->create($dto);
    }

    public function annulerRendezVous(string $idRdv): void
    {
        $row = $this->rdvRepository->findByIdRaw($idRdv);
        if (!$row) {
            throw new RendezVousNonTrouveException("Rendez-vous introuvable");
        }

        $rdv = Rdv::fromArray($row);
        $rdv->annuler();

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());
    }

    public function agendaPraticien(string $praticienId, ?string $debut = null, ?string $fin = null): array
    {
        $dateDebut = $debut ? new DateTime($debut . ' 00:00:00', new \DateTimeZone('UTC')) : new DateTime('today', new \DateTimeZone('UTC'));
        $dateFin = $fin ? new DateTime($fin . ' 23:59:59', new \DateTimeZone('UTC')) : new DateTime('today 23:59:59', new \DateTimeZone('UTC'));

        $praticien = $this->praticienRepository->get($praticienId);
        if (!$praticien) {
            throw new \toubilib\core\application\ports\spi\exceptions\PraticienNonTrouveException("Praticien inexistant");
        }

        $rows = $this->rdvRepository->getRdvByPraticienAndPeriod($praticienId, $dateDebut, $dateFin);

        return array_map(function (array $r) {
            $r['patient_url'] = '/patient/' . $r['patient_id'];
            $r['annule'] = ((int) $r['status'] === -1);
            return $r;
        }, $rows);
    }



    public function listerRdvPatient(string $patientId): array
    {
        // On pourrait vérifier si le patient existe ici
        // $patient = $this->patientRepository->findById($patientId);
        // if (!$patient) throw ...

        $rdvs = $this->rdvRepository->getRdvByPatient($patientId);

        return array_map(function ($rdv) {
            return new RdvDTO(
                $rdv->getId(),
                $rdv->getPraticienId(),
                $rdv->getPatientId(),
                $rdv->getPatientEmail(),
                $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                $rdv->getStatus(),
                $rdv->getDuree(),
                $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
                $rdv->getDateCreation()->format('Y-m-d H:i:s'),
                $rdv->getMotifVisite()
            );
        }, $rdvs);
    }

    public function marquerRdvHonore(string $idRdv): void
    {
        $row = $this->rdvRepository->findByIdRaw($idRdv);
        if (!$row) {
            throw new RendezVousNonTrouveException("Rendez-vous introuvable");
        }

        $rdv = Rdv::fromArray($row);
        $rdv->honorer();

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());
    }

    public function marquerRdvNonHonore(string $idRdv): void
    {
        $row = $this->rdvRepository->findByIdRaw($idRdv);
        if (!$row) {
            throw new RendezVousNonTrouveException("Rendez-vous introuvable");
        }

        $rdv = Rdv::fromArray($row);
        $rdv->nePasHonorer();

        $this->rdvRepository->updateStatus($rdv->getId(), $rdv->getStatus());
    }
}