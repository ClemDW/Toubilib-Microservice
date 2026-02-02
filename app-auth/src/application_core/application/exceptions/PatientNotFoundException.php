<?php

namespace toubilib\core\application\exceptions;

use Exception;

/**
 * Exception lancée lorsqu'un patient n'est pas trouvé
 */
class PatientNotFoundException extends Exception
{
    public function __construct(string $patientId)
    {
        parent::__construct("Patient avec l'ID '{$patientId}' non trouvé.", 404);
    }
}