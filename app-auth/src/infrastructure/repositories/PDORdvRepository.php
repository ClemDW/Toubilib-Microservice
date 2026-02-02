<?php 
namespace toubilib\infra\repositories;
use Ramsey\Uuid\Uuid;
use DateTime;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepository;
use toubilib\core\domain\entities\rdv\RendezVous;
use Exception;

class PDORdvRepository implements RdvRepository {
    public function sauvegarderRendezVous(RendezVous $rdv): void {
        $query = 'UPDATE rdv SET status = :status WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':status', $rdv->getStatus(), \PDO::PARAM_INT);
        $stmt->bindValue(':id', $rdv->getId());
        $stmt->execute();
    }
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listerRdvOcuppePraticienParDate(DateTime $debut, DateTime $fin, string $praticien_id): array {
        $stmt = $this->pdo->prepare('
            SELECT id, praticien_id, patient_id, date_heure_debut, date_heure_fin, motif_visite, status 
            FROM rdv
            WHERE praticien_id = :praticien_id 
              AND date_heure_debut >= :debut 
              AND date_heure_fin <= :fin
            ORDER BY date_heure_debut
        ');
        
        $stmt->execute([
            'praticien_id' => $praticien_id,
            'debut' => $debut->format('Y-m-d H:i:s'),
            'fin' => $fin->format('Y-m-d H:i:s')
        ]);
        
        $rdvs = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $rdvs[] = new RendezVous(
                $row['id'],
                $row['praticien_id'],
                $row['patient_id'],
                new DateTime($row['date_heure_debut']),
                new DateTime($row['date_heure_fin']),
                $row['motif_visite'],
                isset($row['status']) ? (int)$row['status'] : 0
            );
        }
        return $rdvs;
    }

    public function consulterRendezVousParId(string $id): ?RendezVous {
        $stmt = $this->pdo->prepare('
            SELECT id, praticien_id, patient_id, date_heure_debut, date_heure_fin, motif_visite, status 
            FROM rdv
            WHERE id = :id
        ');
        
        $stmt->execute(['id' => $id]);
        
        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new RendezVous(
                $row['id'],
                $row['praticien_id'],
                $row['patient_id'],
                new DateTime($row['date_heure_debut']),
                new DateTime($row['date_heure_fin']),
                $row['motif_visite'],
                isset($row['status']) ? (int)$row['status'] : 0
            );
        }
        
        return null;
    }
   
    /**
     * Créer un nouveau rendez-vous id genere dans l'aplication et non en base de donnée
     */
    public function creerRendezVous(RendezVous $rdv): RendezVous {
        $query = '
            INSERT INTO rdv (id, praticien_id, patient_id, date_heure_debut, date_heure_fin, motif_visite, status) 
            VALUES (:id, :praticien_id, :patient_id, :date_heure_debut, :date_heure_fin, :motif_visite, :status)
        ';
        if ($rdv->getId() === null) {
            $rdv->setId(Uuid::uuid4()->toString());
        }
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $rdv->getId());
            $stmt->bindValue(':praticien_id', $rdv->getPraticienId());
            $stmt->bindValue(':patient_id', $rdv->getPatientId());
            $stmt->bindValue(':date_heure_debut', $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':date_heure_fin', $rdv->getDateHeureFin()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':motif_visite', $rdv->getMotifVisite());
            $stmt->bindValue(':status', $rdv->getStatus(), \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Exception $e) {
            throw new Exception("Erreur lors de la création du rendez-vous : " . $e->getMessage(), 0, $e);
        }

        return $rdv;
    }
    public function rdvHonore(string $rdvId): void {
        $query = 'UPDATE rdv SET status = :status WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':status', 1, \PDO::PARAM_INT); // 1 pour "honoré"
        $stmt->bindValue(':id', $rdvId);
        $stmt->execute();
    }
    public function rdvRefuse(string $rdvId): void {
        $query = 'UPDATE rdv SET status = :status WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':status', 2, \PDO::PARAM_INT); // 2 pour "refusé"
        $stmt->bindValue(':id', $rdvId);
        $stmt->execute();
    }
   
}