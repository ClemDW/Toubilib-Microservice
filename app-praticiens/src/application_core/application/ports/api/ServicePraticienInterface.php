<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\application\ports\api\dtos\PraticienDTO;

interface ServicePraticienInterface
{
    public function listerPraticiens(): array;
    public function afficherPraticien(string $id): PraticienDTO;
}