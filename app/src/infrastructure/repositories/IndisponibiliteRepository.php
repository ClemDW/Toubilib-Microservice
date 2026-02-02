<?php

namespace toubilib\infra\repositories;

use PDO;
use toubilib\core\application\ports\spi\repositoryInterfaces\IndisponibiliteRepositoryInterface;
use toubilib\core\domain\entities\praticien\Indisponibilite;

class IndisponibiliteRepository implements IndisponibiliteRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Indisponibilite $indisponibilite): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO indisponibilite (id, praticien_id, date_debut, date_fin, motif)
            VALUES (:id, :praticien_id, :date_debut, :date_fin, :motif)
        ');

        $stmt->execute([
            'id' => $indisponibilite->getId(),
            'praticien_id' => $indisponibilite->getPraticienId(),
            'date_debut' => $indisponibilite->getDateDebut()->format('Y-m-d H:i:s'),
            'date_fin' => $indisponibilite->getDateFin()->format('Y-m-d H:i:s'),
            'motif' => $indisponibilite->getMotif()
        ]);
    }

    public function getIndisponibilitesByPraticienAndPeriode(string $praticienId, \DateTimeImmutable $dateDebut, \DateTimeImmutable $dateFin): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM indisponibilite
            WHERE praticien_id = :praticien_id
              AND date_fin > :date_debut
              AND date_debut < :date_fin
        ');

        $stmt->execute([
            'praticien_id' => $praticienId,
            'date_debut' => $dateDebut->format('Y-m-d H:i:s'),
            'date_fin' => $dateFin->format('Y-m-d H:i:s')
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $indisponibilites = [];

        foreach ($rows as $row) {
            $indisponibilites[] = new Indisponibilite(
                $row['id'],
                $row['praticien_id'],
                new \DateTimeImmutable($row['date_debut']),
                new \DateTimeImmutable($row['date_fin']),
                $row['motif'] ?? null
            );
        }

        return $indisponibilites;
    }
}
