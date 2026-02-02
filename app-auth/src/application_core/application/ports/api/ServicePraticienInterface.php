<?php
namespace toubilib\core\application\ports\api;

use toubilib\core\domain\entities\praticien\Praticien;



interface ServicePraticienInterface
{
    public function listerPraticiens(): array;
}