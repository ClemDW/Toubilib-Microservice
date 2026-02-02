<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\application\ports\api\dtos\InputPatientDTO;
use toubilib\core\domain\entities\praticien\Patient;

interface ServicePatientInterface
{
    public function createPatient(InputPatientDTO $dto): Patient;
}
