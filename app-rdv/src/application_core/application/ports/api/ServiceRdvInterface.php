<?php

namespace toubilib\core\application\ports\api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\core\application\ports\api\dtos\InputRendezVousDTO;

interface ServiceRdvInterface
{
    public function listerRdv(): array;
    public function creerRendezVous(InputRendezVousDTO $dto);
    public function annulerRendezVous(string $idRdv): void;
    public function agendaPraticien(string $praticienId, ?string $debut = null, ?string $fin = null): array;
    public function listerRdvPatient(string $patientId): array;
    public function getRdv(string $id): array;
    public function getRdvPraticienPeriode(string $praticienId, string $debutPeriode, string $finPeriode): array;
    public function getRdvPraticien(string $praticienId): array;
    public function marquerRdvHonore(string $idRdv): void;
    public function marquerRdvNonHonore(string $idRdv): void;
}