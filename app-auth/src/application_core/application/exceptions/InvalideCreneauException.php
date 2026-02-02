<?php

namespace toubilib\core\application\exceptions;

use Exception;
use DateTime;

/**
 * Exception lancée lorsqu'un créneau horaire n'est pas valide
 */
class InvalideCreneauException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, 400);
    }

    public static function pourweekend(DateTime $date): self
    {
        return new self("Le rendez-vous ne peut pas être pris le week-end. Date demandée : " . $date->format('Y-m-d H:i'));
    }

    public static function pourheuresinvalides(DateTime $date): self
    {
        return new self("Le rendez-vous doit être pris entre 8h et 19h. Date demandée : " . $date->format('Y-m-d H:i'));
    }

    public static function pourDuréeInvalide(DateTime $debut, DateTime $fin): self
    {
        return new self("L'heure de fin doit être postérieure à l'heure de début. Début : " . $debut->format('Y-m-d H:i') . ", Fin : " . $fin->format('Y-m-d H:i'));
    }
}