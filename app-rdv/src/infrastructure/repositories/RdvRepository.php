<?php

namespace toubilib\infra\repositories;

use PDO;
use DateInterval;
use DateTime;
use toubilib\core\application\ports\api\dtos\RdvDTO;
use Ramsey\Uuid\Uuid;
use toubilib\core\application\ports\api\dtos\InputRendezVousDTO;
use toubilib\core\application\ports\spi\exceptions\FindAllRdvException;
use toubilib\core\application\ports\spi\exceptions\RdvNotFoundException;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;

class RdvRepository implements RdvRepositoryInterface
{

    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        try {
            $sql = "SELECT id, praticien_id, patient_id, patient_email, date_heure_debut, status, duree, date_heure_fin, date_creation, motif_visite FROM rdv";
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $rdvs = [];
            foreach ($rows as $row) {

                $rdvs[] = new RdvDTO(
                    $row['id'],
                    $row['praticien_id'],
                    $row['patient_id'],
                    $row['patient_email'],
                    $row['date_heure_debut'],
                    $row['status'],
                    $row['duree'],
                    $row['date_heure_fin'],
                    $row['date_creation'],
                    $row['motif_visite']
                );
            }
            return $rdvs;

        } catch (\PDOException $e) {
            throw new FindAllRdvException("Erreur lors de la récupération de tous les rdvs");
        }
    }

    public function getRdv(string $id): RdvDTO
    {
        try {
            $sql = "SELECT * FROM rdv WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new RdvNotFoundException("Rendez-vous non trouvé");
            }

            return new RdvDTO(
                $row['id'],
                $row['praticien_id'],
                $row['patient_id'],
                $row['patient_email'] ?? null,
                $row['date_heure_debut'],
                $row['status'],
                $row['duree'],
                $row['date_heure_fin'],
                $row['date_creation'],
                $row['motif_visite']
            );
        } catch (\PDOException $e) {
            throw new RdvNotFoundException("Erreur lors de la récupération d'un rdv : " . $e->getMessage());
        }
    }

    public function getRdvByPraticienAndPeriod(string $praticienId, DateTime $dateDebut, DateTime $dateFin): array
    {
        try {
            $sql = "SELECT * FROM rdv 
            WHERE praticien_id = :praticien_id 
            AND date_heure_debut >= :date_debut 
            AND date_heure_debut <= :date_fin
            AND status >= 0
            ORDER BY date_heure_debut ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'praticien_id' => $praticienId,
                'date_debut' => $dateDebut->format('Y-m-d H:i:s'),
                'date_fin' => $dateFin->format('Y-m-d H:i:s')
            ]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Conversion en array de données formatées
            $rdvs = [];
            foreach ($results as $row) {
                $rdvs[] = new RdvDTO(
                    $row['id'],
                    $row['praticien_id'],
                    $row['patient_id'],
                    $row['patient_email'] ?? null,
                    $row['date_heure_debut'],
                    $row['status'],
                    $row['duree'],
                    $row['date_heure_fin'],
                    $row['date_creation'],
                    $row['motif_visite']
                );
            }

            return $rdvs;

        } catch (\PDOException $e) {
            error_log("Erreur SQL getRdvByPraticienAndPeriod: " . $e->getMessage());
            throw new \Exception("Erreur lors de la récupération des rendez-vous du praticien");
        }
    }

    public function isPraticienDisponible(string $praticienId, DateTime $dateDebut, int $dureeMinutes): bool
    {
        $dateFin = (clone $dateDebut)->add(new \DateInterval('PT' . $dureeMinutes . 'M'));

        $sql = "SELECT id FROM rdv 
            WHERE praticien_id = :praticien_id 
            AND status >= 0
            AND (
            
                (:date_debut >= date_heure_debut AND :date_debut < date_heure_fin)
                OR
                  
                (:date_fin > date_heure_debut AND :date_fin <= date_heure_fin)
                OR
                
                (:date_debut <= date_heure_debut AND :date_fin >= date_heure_fin)
            )
            LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'praticien_id' => $praticienId,
            'date_debut' => $dateDebut->format('Y-m-d H:i:s'),
            'date_fin' => $dateFin->format('Y-m-d H:i:s')
        ]);

        $conflit = $stmt->fetch();
        return $conflit === false;
    }

    public function create(InputRendezVousDTO $dto): void
    {
        $dateHeureDebut = $dto->getDateHeureDebut();
        $dateHeureFin = clone $dateHeureDebut;
        $dateHeureFin->add(new DateInterval('PT' . $dto->getDuree() . 'M'));

        $sql = "INSERT INTO rdv
            (id, praticien_id, patient_id, date_heure_debut, date_heure_fin, duree, motif_visite, date_creation, status)
            VALUES (:id, :praticien_id, :patient_id, :date_heure_debut, :date_heure_fin, :duree, :motif_visite, :date_creation, 0)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => Uuid::uuid4()->toString(),
            'praticien_id' => $dto->getPraticienId(),
            'patient_id' => $dto->getPatientId(),
            'date_heure_debut' => $dto->getDateHeureDebut()->format('Y-m-d H:i:s'),
            'date_heure_fin' => $dateHeureFin->format('Y-m-d H:i:s'),
            'duree' => $dto->getDuree(),
            'motif_visite' => $dto->getMotifVisite(),
            'date_creation' => date('Y-m-d 00:00:00'),
        ]);
    }

    public function findByIdRaw(string $id): ?array
    {
        $sql = "SELECT * FROM rdv WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateStatus(string $id, int $status): void
    {
        $sql = "UPDATE rdv SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'status' => $status,
            'id' => $id
        ]);
    }

    public function getRdvByPatient(string $patientId): array
    {
        $sql = "SELECT * FROM rdv WHERE patient_id = :patient_id ORDER BY date_heure_debut DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $rdvs = [];
        foreach ($rows as $row) {
            $rdvs[] = new RdvDTO(
                $row['id'],
                $row['praticien_id'],
                $row['patient_id'],
                $row['patient_email'],
                $row['date_heure_debut'],
                $row['status'],
                $row['duree'],
                $row['date_heure_fin'],
                $row['date_creation'],
                $row['motif_visite']
            );
        }
        return $rdvs;
    }
}