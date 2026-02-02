<?php

namespace toubilib\core\application\exceptions;

use Exception;

/**
 * Exception lancée lorsqu'un motif de visite n'est pas valide pour un praticien
 */
class InvalidMotifVisiteException extends Exception
{
    public function __construct(string $motifVisite, string $praticienId)
    {
        parent::__construct("Le motif de visite '{$motifVisite}' n'est pas autorisé pour le praticien avec l'ID '{$praticienId}'.", 400);
    }
}