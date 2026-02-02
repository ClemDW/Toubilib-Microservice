<?php
namespace toubilib\infra\repositories;
use PDO;
use toubilib\core\domain\entities\praticien\PraticienDetails;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\domain\entities\praticien\Praticien;
use toubilib\core\domain\entities\praticien\Specialite;
use toubilib\core\domain\entities\praticien\Structure;
use toubilib\core\domain\entities\praticien\MotifVisite;
use toubilib\core\domain\entities\praticien\MoyenPaiement;
use toubilib\core\application\ports\api\dtos\SpecialiteDTO;
use toubilib\core\application\ports\api\dtos\StructureDTO;
use toubilib\core\application\ports\api\dtos\MotifVisiteDTO;
use toubilib\core\application\ports\api\dtos\MoyenPaiementDTO;


class PraticienRepository implements PraticienRepositoryInterface{
  
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
      }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM praticien');
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $praticiens = [];
        foreach ($results as $row) {
            

            $temp = new Praticien(
                $row['id'],
                $row['nom'],
                $row['prenom'],
                $row['ville'],
                $row['email'],
                $row['telephone'],
                $row['rpps_id'],
                $row['titre'],
                (bool)$row['nouveau_patient'],
                (bool)$row['organisation'],
                $row['specialite_id']
            );
                
                $praticiens[] = $temp;
                

        }
        return $praticiens;
    }

public function findById(string $id)
{
    // Récupérer le praticien
    $stmt = $this->pdo->prepare('SELECT * FROM praticien WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $praticienRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$praticienRow) {
        return null;
    }

    // Récupérer la spécialité (entité domaine)
    $stmtSpec = $this->pdo->prepare('SELECT * FROM specialite WHERE id = :id');
    $stmtSpec->execute(['id' => $praticienRow['specialite_id']]);
    $specialiteRow = $stmtSpec->fetch(PDO::FETCH_ASSOC);

    $specialite = new Specialite(
        $specialiteRow['id'],
        $specialiteRow['libelle'],
        $specialiteRow['description']
    );

    // Récupérer la structure (peut être null)
    $structure = null;
    if (!empty($praticienRow['structure_id'])) {
        $stmtStruct = $this->pdo->prepare('SELECT * FROM structure WHERE id = :id');
        $stmtStruct->execute(['id' => $praticienRow['structure_id']]);
        $structureRow = $stmtStruct->fetch(PDO::FETCH_ASSOC);

        if ($structureRow) {
            $structure = new Structure(
                $structureRow['id'],
                $structureRow['nom'],
                $structureRow['adresse'],
                $structureRow['ville'],
                $structureRow['code_postal'],
                $structureRow['telephone']
            );
        }
    }

    // Récupérer les motifs de visite liés au praticien (entités)
    $stmtMotifs = $this->pdo->prepare(
        'SELECT mv.* FROM motif_visite mv
         JOIN praticien2motif p2m ON mv.id = p2m.motif_id
         WHERE p2m.praticien_id = :praticien_id'
    );
    $stmtMotifs->execute(['praticien_id' => $id]);
    $motifsRows = $stmtMotifs->fetchAll(PDO::FETCH_ASSOC);

    $motifsEntities = [];
    foreach ($motifsRows as $motifRow) {
        $motifsEntities[] = new MotifVisite(
            $motifRow['id'],
            $motifRow['specialite_id'],
            $motifRow['libelle']
        );
    }

    // Récupérer les moyens de paiement liés au praticien (entités)
    $stmtMoyens = $this->pdo->prepare(
        'SELECT mp.* FROM moyen_paiement mp
         JOIN praticien2moyen p2m ON mp.id = p2m.moyen_id
         WHERE p2m.praticien_id = :praticien_id'
    );
    $stmtMoyens->execute(['praticien_id' => $id]);
    $moyensRows = $stmtMoyens->fetchAll(PDO::FETCH_ASSOC);

    $moyensEntities = [];
    foreach ($moyensRows as $moyenRow) {
        $moyensEntities[] = new MoyenPaiement(
            $moyenRow['id'],
            $moyenRow['libelle']
        );
    }

    // Retourner l'entité domaine PraticienDetails
    return new PraticienDetails(
        $praticienRow['id'],
        $praticienRow['nom'],
        $praticienRow['prenom'],
        $praticienRow['titre'],
        $praticienRow['email'],
        $praticienRow['telephone'],
        $praticienRow['ville'],
        $praticienRow['rpps_id'],
        (bool)$praticienRow['organisation'],
        (bool)$praticienRow['nouveau_patient'],
        $specialite,
        $structure,
        $motifsEntities,
        $moyensEntities
    );
}
public function findDetailsById(string $id): ?PraticienDetails
{
    // Charger praticien avec spécialité
    $stmt = $this->pdo->prepare("
        SELECT p.*, s.id AS specialite_id, s.libelle AS specialite_libelle, s.description AS specialite_description
        FROM praticien p
        LEFT JOIN specialite s ON p.specialite_id = s.id
        WHERE p.id = :id
    ");
    $stmt->execute(['id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        return null;
    }

    // Charger structure
    $structure = null;
    if (!empty($data['structure_id'])) {
        $stmtStructure = $this->pdo->prepare("SELECT * FROM structure WHERE id = :id");
        $stmtStructure->execute(['id' => $data['structure_id']]);
        $structureData = $stmtStructure->fetch(PDO::FETCH_ASSOC);
        if ($structureData) {
            $structure = new Structure(
                $structureData['id'],
                $structureData['nom'],
                $structureData['adresse'],
                $structureData['ville'],
                $structureData['code_postal'],
                $structureData['telephone']
            );
        }
    }

    // Charger motifs
    $stmtMotifs = $this->pdo->prepare("
        SELECT mv.id, mv.libelle, mv.specialite_id
        FROM motif_visite mv
        JOIN praticien2motif pm ON mv.id = pm.motif_id
        WHERE pm.praticien_id = :id
    ");
    $stmtMotifs->execute(['id' => $id]);
    $motifsData = $stmtMotifs->fetchAll(PDO::FETCH_ASSOC);
    $motifs = array_map(fn($m) => new MotifVisite(
        (int)$m['id'],
        (int)$m['specialite_id'],
        $m['libelle']
    ), $motifsData);

    // Charger moyens
    $stmtMoyens = $this->pdo->prepare("
        SELECT mp.id, mp.libelle
        FROM moyen_paiement mp
        JOIN praticien2moyen pm ON mp.id = pm.moyen_id
        WHERE pm.praticien_id = :id
    ");
    $stmtMoyens->execute(['id' => $id]);
    $moyensData = $stmtMoyens->fetchAll(PDO::FETCH_ASSOC);
    $moyens = array_map(fn($m) => new MoyenPaiement(
        (int)$m['id'],
        $m['libelle']
    ), $moyensData);

    // Créer spécialité
    $specialite = new Specialite(
        (int)$data['specialite_id'],
        $data['specialite_libelle'] ?? '',
        $data['specialite_description'] ?? null
    );

    return new PraticienDetails(
        $data['id'],
        $data['nom'],
        $data['prenom'],
        $data['titre'],
        $data['email'],
        $data['telephone'],
        $data['ville'],
        $data['rpps_id'] ?? null,
        (bool)$data['organisation'],
        (bool)$data['nouveau_patient'],
        $specialite,
        $structure,
        $motifs,
        $moyens
    );
}

    public function search(string $specialite, string $ville): array
    {
        $query = 'SELECT p.* FROM praticien p JOIN specialite s ON p.specialite_id = s.id WHERE 1=1';
        $params = [];

        if (!empty($specialite)) {
            $query .= ' AND s.libelle ILIKE :specialite';
            $params['specialite'] = '%' . $specialite . '%';
        }

        if (!empty($ville)) {
            $query .= ' AND p.ville ILIKE :ville';
            $params['ville'] = '%' . $ville . '%';
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $praticiens = [];
        foreach ($results as $row) {
            $praticiens[] = new Praticien(
                $row['id'],
                $row['nom'],
                $row['prenom'],
                $row['ville'],
                $row['email'],
                $row['telephone'],
                $row['rpps_id'],
                $row['titre'],
                (bool)$row['nouveau_patient'],
                (bool)$row['organisation'],
                $row['specialite_id']
            );
        }
        return $praticiens;
    }
}