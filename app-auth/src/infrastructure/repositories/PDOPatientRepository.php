<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepository;


class PDOPatientRepository implements PatientRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function patientExists(string $patientId): bool
    {
        if (empty($patientId)) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM patient WHERE id = :id');
        $stmt->execute(['id' => $patientId]);
        $count = $stmt->fetchColumn();

        return $count > 0;
    }
}