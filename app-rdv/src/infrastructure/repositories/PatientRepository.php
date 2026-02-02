<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\domain\entities\praticien\Patient;
use PDO;

class PatientRepository implements PatientRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Patient
    {
        $stmt = $this->pdo->prepare("SELECT * FROM patient WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $dateNaissance = $data['date_naissance'] 
          ? new \DateTime($data['date_naissance']) 
          : null;

      return new Patient(
          $data['id'],
          $data['nom'],
          $data['prenom'],
          $dateNaissance,
          $data['adresse'] ?? null,
          $data['code_postal'] ?? null,
          $data['ville'] ?? null,
          $data['email'],
          $data['telephone']
      );
    }

    public function findByEmail(string $email): ?Patient
    {
        $stmt = $this->pdo->prepare("SELECT * FROM patient WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new Patient(
            $data['id'],
            $data['nom'],
            $data['prenom'],
            $data['date_naissance'],
            $data['adresse'],
            $data['code_postal'],
            $data['ville'],
            $data['email'],
            $data['telephone']
        );
    }

    public function save(Patient $patient): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO patient (id, nom, prenom, date_naissance, adresse, code_postal, ville, email, telephone)
            VALUES (:id, :nom, :prenom, :date_naissance, :adresse, :code_postal, :ville, :email, :telephone)
        ");

        $stmt->execute([
            'id'            => $patient->getId(),
            'nom'           => $patient->getNom(),
            'prenom'        => $patient->getPrenom(),
            'date_naissance'=> $patient->getDateNaissance() ? $patient->getDateNaissance()->format('Y-m-d') : null,
            'adresse'       => $patient->getAdresse(),
            'code_postal'   => $patient->getCodePostal(),
            'ville'         => $patient->getVille(),
            'email'         => $patient->getEmail(),
            'telephone'     => $patient->getTelephone()
        ]);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM patient WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM patient");
        $patients = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $patients[] = new Patient(
                $data['id'],
                $data['nom'],
                $data['prenom'],
                $data['date_naissance'],
                $data['adresse'],
                $data['code_postal'],
                $data['ville'],
                $data['email'],
                $data['telephone']
            );
        }

        return $patients;
    }
}
