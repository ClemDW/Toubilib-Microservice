<?php

namespace toubilib\core\application\ports\api;

interface ServicePraticienInterface
{
    public function getPraticienById(string $id): array;
}