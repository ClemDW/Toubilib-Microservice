<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;

use toubilib\core\domain\entities\Praticien;

interface PraticienRepositoryInterface
{
    public function findAll(): array;
    public function get(string $id): Praticien;
    public function getSpecialite(int $id):string;
    public function getMotifs(string $id): array;
    public function getMoyens(string $id): array;
}
