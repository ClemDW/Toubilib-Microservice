<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;

use toubilib\core\domain\entities\Patient;

interface PatientRepositoryInterface
{
    public function findById(string $id): ?Patient;
    public function createPatient(Patient $patient): void;
}