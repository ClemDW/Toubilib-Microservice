<?php

namespace toubilib\infra\repositories;

use PDO;
use toubilib\core\domain\entities\praticien\Rdv;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;

class RdvRepository implements RdvRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getCreneauxOccupes(string $praticienId, string $dateDebut, string $dateFin): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM rdv
            WHERE praticien_id = :praticien_id
              AND date_heure_fin > :date_debut
              AND date_heure_debut < :date_fin
        ');

        $stmt->execute([
            'praticien_id' => $praticienId,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rdvs = [];

        foreach ($rows as $row) {
        $rdvs[] = new Rdv(
            $row['id'],
            $row['praticien_id'],
            $row['patient_id'],
            $row['patient_email'] ?? null,
            new \DateTimeImmutable($row['date_heure_debut']),
            (int) $row['status'],
            (int) $row['duree'],
            new \DateTimeImmutable($row['date_heure_fin']),   
            isset($row['date_creation']) ? new \DateTimeImmutable($row['date_creation']) : null,
            $row['motif_visite'] ?? null
        );
    }
      return $rdvs;
    }

    
    public function findById(string $id): ?Rdv
{
    $stmt = $this->pdo->prepare('
        SELECT id, praticien_id, patient_id, patient_email, 
               date_heure_debut, date_heure_fin, status, duree, date_creation, motif_visite
        FROM rdv
        WHERE id = :id
        LIMIT 1
    ');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null; // pas trouvé
    }

    return new Rdv(
        $row['id'],
        $row['praticien_id'],
        $row['patient_id'],
        $row['patient_email'] ?? null,
        new \DateTimeImmutable($row['date_heure_debut']),
        (int) $row['status'],
        (int) $row['duree'],
        new \DateTimeImmutable($row['date_heure_fin']),
        isset($row['date_creation']) ? new \DateTimeImmutable($row['date_creation']) : null,
        $row['motif_visite'] ?? null
    );
}
public function save(Rdv $rdv): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO rdv (
                id, praticien_id, patient_id, patient_email,
                date_heure_debut, status, duree,
                date_heure_fin, date_creation, motif_visite
            ) VALUES (
                :id, :praticien_id, :patient_id, :patient_email,
                :date_heure_debut, :status, :duree,
                :date_heure_fin, :date_creation, :motif_visite
            )
        ');

        $stmt->execute([
            'id' => $rdv->getId(),
            'praticien_id' => $rdv->getPraticienId(),
            'patient_id' => $rdv->getPatientId(),
            'patient_email' => $rdv->getPatientEmail(),
            'date_heure_debut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
            'status' => $rdv->getStatus(),
            'duree' => $rdv->getDuree(),
            'date_heure_fin' => $rdv->getDateHeureFin()?->format('Y-m-d H:i:s'),
            'date_creation' => $rdv->getDateCreation()?->format('Y-m-d H:i:s'),
            'motif_visite' => $rdv->getMotifVisite(),
        ]);
    }

    public function update(Rdv $rdv): void
{
    $stmt = $this->pdo->prepare('
        UPDATE rdv SET
            status = :status
        WHERE id = :id
    ');

    $stmt->execute([
        'id' => $rdv->getId(),
        'status' => $rdv->getStatus(),
    ]);
}

public function getRdvByPraticienAndPeriode(string $praticienId, \DateTime $dateDebut, \DateTime $dateFin): array
{
    $dateDebut = (clone $dateDebut)->setTime(0, 0, 0);
    $dateFin   = (clone $dateFin)->setTime(23, 59, 59);

    $sql = "SELECT * FROM rdv 
            WHERE praticien_id = :praticienId 
            AND date_heure_debut BETWEEN :dateDebut AND :dateFin";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        'praticienId' => $praticienId,
        'dateDebut'   => $dateDebut->format('Y-m-d H:i:s'),
        'dateFin'     => $dateFin->format('Y-m-d H:i:s')
    ]);

    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    

    $rdvs = [];
    foreach ($rows as $row) {
        $rdvs[] = new Rdv(
            $row['id'],
            $row['praticien_id'],
            $row['patient_id'],
            $row['patient_email'] ?? null,
            new \DateTimeImmutable($row['date_heure_debut']),
            (int) $row['status'],
            (int) $row['duree'],
            isset($row['date_heure_fin']) ? new \DateTimeImmutable($row['date_heure_fin']) : null,
            isset($row['date_creation']) ? new \DateTimeImmutable($row['date_creation']) : null,
            $row['motif_visite'] ?? null
        );
    }

    return $rdvs;
}

    public function findRdvsByPatientId(string $patientId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM rdv
            WHERE patient_id = :patient_id
            ORDER BY date_heure_debut DESC
        ');

        $stmt->execute(['patient_id' => $patientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rdvs = [];

        foreach ($rows as $row) {
            $rdvs[] = new Rdv(
                $row['id'],
                $row['praticien_id'],
                $row['patient_id'],
                $row['patient_email'] ?? null,
                new \DateTimeImmutable($row['date_heure_debut']),
                (int) $row['status'],
                (int) $row['duree'],
                isset($row['date_heure_fin']) ? new \DateTimeImmutable($row['date_heure_fin']) : null,
                isset($row['date_creation']) ? new \DateTimeImmutable($row['date_creation']) : null,
                $row['motif_visite'] ?? null
            );
        }
        return $rdvs;
    }

}
