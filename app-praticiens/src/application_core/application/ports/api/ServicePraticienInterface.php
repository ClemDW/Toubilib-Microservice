<?php

namespace toubilib\core\application\ports\api;

interface ServicePraticienInterface
{

  public function getAllPraticiens();

  public function getPraticienDetail(string $id);

  public function searchPraticiens(string $specialite, string $ville): array;

}