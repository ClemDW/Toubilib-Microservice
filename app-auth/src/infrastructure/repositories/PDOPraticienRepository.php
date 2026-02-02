<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepository ;
use toubilib\core\domain\entities\praticien\Specialite;

class PDOPraticienRepository implements PraticienRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function specialiteParId(int $id): ?\toubilib\core\domain\entities\praticien\Specialite {
        $stmt = $this->pdo->prepare('SELECT * FROM specialite WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            return new \toubilib\core\domain\entities\praticien\Specialite(
                $data['id'],
                $data['libelle'],
                $data['description']
            );
        }

        return null;
    }
    public function motifsVisiteParPraticienId(string $praticien_id): array {
        $stmt = $this->pdo->prepare('
            SELECT mv.* 
            FROM motif_visite mv 
            INNER JOIN praticien2motif p2m ON mv.id = p2m.motif_id 
            WHERE p2m.praticien_id = :praticien_id
        ');
        $stmt->execute(['praticien_id' => $praticien_id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $motifs = [];
        foreach ($results as $data) {
            $motifs[] = new \toubilib\core\domain\entities\praticien\MotifVisite(
                $data['id'],
                $data['specialite_id'],
                $data['libelle']
            );
        }
        
        return $motifs;
    }


    public function moyenPaiementParPraticienId(string $praticien_id): array {
        $stmt = $this->pdo->prepare('
            SELECT mp.* 
            FROM moyen_paiement mp INNER JOIN praticien2moyen p2mp ON mp.id = p2mp.moyen_id 
            WHERE p2mp.praticien_id = :praticien_id
        ');
        $stmt->execute(['praticien_id' => $praticien_id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $moyens = [];
        foreach ($results as $data) {
            $moyens[] = new \toubilib\core\domain\entities\praticien\MoyenPaiement(
                $data['id'],
                $data['libelle']
            );
        }
        
        return $moyens;
    }
    public function listerPraticiens(): array {
        $stmt = $this->pdo->query('SELECT * FROM praticien');
        $praticiensData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $praticiens = [];
        foreach ($praticiensData as $data) {
            $specialite = $this->specialiteParId((int)$data['specialite_id']);
            $motifVisite = $this->motifsVisiteParPraticienId((string)$data['id']);
            $moyenPaiement = $this->moyenPaiementParPraticienId((string)$data['id']);
            
            $praticien = new \toubilib\core\domain\entities\praticien\Praticien(
                $data['id'],
                $data['nom'],
                $data['prenom'],
                $data['ville'] ,
                $data['email'],
                $specialite,
                $motifVisite,
                $moyenPaiement
            );
            $praticiens[] = $praticien;
        }

        return $praticiens;
    }

    public function praticienParId(string $id): ?\toubilib\core\domain\entities\praticien\Praticien {
        $stmt = $this->pdo->prepare('SELECT * FROM praticien WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $specialite = $this->specialiteParId((int)$data['specialite_id']);
            $motifVisite = $this->motifsVisiteParPraticienId((string)$data['id']);
            $moyenPaiement = $this->moyenPaiementParPraticienId((string)$data['id']);

            return new \toubilib\core\domain\entities\praticien\Praticien(
                $data['id'],
                $data['nom'],
                $data['prenom'],
                $data['ville'],
                $data['email'],
                $specialite,
                $motifVisite,
                $moyenPaiement
            );
        }
   

        return null;
    }
    public function praticienParSpecialite(string $specialiteLibelle): array {
        $stmt = $this->pdo->prepare('
            SELECT p.* 
            FROM praticien p 
            INNER JOIN specialite s ON p.specialite_id = s.id 
            WHERE s.libelle ILIKE :libelle
        ');
        $stmt->execute(['libelle' => '%' . $specialiteLibelle . '%']);
        $praticiensData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $praticiens = [];
        foreach ($praticiensData as $data) {
            $specialite = $this->specialiteParId((int)$data['specialite_id']);
            $motifVisite = $this->motifsVisiteParPraticienId((string)$data['id']);
            $moyenPaiement = $this->moyenPaiementParPraticienId((string)$data['id']);
            
            $praticien = new \toubilib\core\domain\entities\praticien\Praticien(
                $data['id'],
                $data['nom'],
                $data['prenom'],
                $data['ville'],
                $data['email'],
                $specialite,
                $motifVisite,
                $moyenPaiement
            );
            $praticiens[] = $praticien;
        }

        return $praticiens;
       
}
    public function praticienParville (string $ville): array {
        $stmt = $this->pdo->prepare('
            SELECT * FROM praticien WHERE ville ILIKE :ville
        ');
        $stmt->execute(['ville' => '%' . $ville . '%']);
        $praticiensData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $praticiens = [];
        foreach ($praticiensData as $data) {
            $specialite = $this->specialiteParId((int)$data['specialite_id']);
            $motifVisite = $this->motifsVisiteParPraticienId((string)$data['id']);
            $moyenPaiement = $this->moyenPaiementParPraticienId((string)$data['id']);
            
            $praticien = new \toubilib\core\domain\entities\praticien\Praticien(
                $data['id'],
                $data['nom'],
                $data['prenom'],
                $data['ville'],
                $data['email'],
                $specialite,
                $motifVisite,
                $moyenPaiement
            );
            $praticiens[] = $praticien;
        }

        return $praticiens;
    }
    
}