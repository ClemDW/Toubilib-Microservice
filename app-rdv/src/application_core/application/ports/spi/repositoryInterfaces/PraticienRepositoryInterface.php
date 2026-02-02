<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;
use toubilib\core\domain\entities\praticien\PraticienDetails;

interface PraticienRepositoryInterface
{
    public function findAll(): array;

    public function findById(string $id);

    public function findDetailsById(string $id) : ?PraticienDetails;

    public function search(string $specialite, string $ville): array;
}