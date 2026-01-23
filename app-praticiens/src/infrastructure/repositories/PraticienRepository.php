<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\domain\entities\Praticien;
use toubilib\core\application\ports\spi\exceptions\PraticienNonTrouveException;
use Ramsey\Uuid\Uuid;

class PraticienRepository implements PraticienRepositoryInterface
{

    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT id, nom, prenom, ville, email, telephone, specialite_id FROM praticien";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $praticiens = [];
        foreach ($rows as $row) {
            $praticiens[] = new Praticien(
                $row['id'],
                $row['nom'],
                $row['prenom'],
                (string)$row['specialite_id'],
                $row['ville'],
                $row['email'],
                $row['telephone'],
            );
        }

        return $praticiens;
    }

    public function get(string $id): Praticien
    {
        if (!Uuid::isValid($id)) {
            throw new PraticienNonTrouveException("Praticien non trouvé");
        }

        $sql = "SELECT id, nom, prenom, ville, email, telephone, specialite_id FROM praticien WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new PraticienNonTrouveException("Praticien non trouvé");
        }

        $praticien = new Praticien(
            $row['id'],
            $row['nom'],
            $row['prenom'],
            $this->getSpecialite($row['specialite_id']),
            $row['ville'],
            $row['email'],
            $row['telephone'],
            $this->getMotifs($row['id']),
            $this->getMoyens($row['id'])
        );

        return $praticien;
    }

    public function getSpecialite(int $id):string {
        $sql = "SELECT libelle FROM specialite WHERE id = :specialite_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':specialite_id', $id);
        $stmt->execute();
        $specialite = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $specialite['libelle'];
    }

    public function getMotifs(string $id): array{
        $sql = "SELECT motif_id FROM praticien2motif WHERE praticien_id = :praticien_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':praticien_id', $id);
        $stmt->execute();
        $motifsid = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $motifs = [];
        foreach ($motifsid as $motif) {
            $sql = "SELECT libelle FROM motif_visite WHERE id = :motif_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':motif_id', $motif['motif_id']);
            $stmt->execute();
            $motif = $stmt->fetch(\PDO::FETCH_ASSOC);
            $motifs[] = $motif['libelle'];
        }
        return $motifs;
    }

    public function getMoyens(string $id): array{
        $sql = "SELECT moyen_id FROM praticien2moyen WHERE praticien_id = :praticien_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':praticien_id', $id);
        $stmt->execute();
        $moyensid = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $moyens = [];
        foreach ($moyensid as $moyen) {
            $sql = "SELECT libelle FROM moyen_paiement WHERE id = :moyen_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':moyen_id', $moyen['moyen_id']);
            $stmt->execute();
            $moyen = $stmt->fetch(\PDO::FETCH_ASSOC);
            $moyens[] = $moyen['libelle'];
        }
        return $moyens;
    }
}