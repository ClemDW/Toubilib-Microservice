<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;

use toubilib\core\domain\entities\praticien\Patient;

interface PatientRepositoryInterface
{
    public function findById(string $id): ?Patient;

    public function findByEmail(string $email): ?Patient;

    public function save(Patient $patient): void;

    public function delete(string $id): void;

    public function findAll(): array;
}
