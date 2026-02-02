<?php

namespace toubilib\core\application\ports\spi\repositoryInterfaces;

/**
 * Interface pour la gestion des patients
 */
interface PatientRepository
{
    /**
     * Vérifier si un patient existe par son ID
     */
    public function patientExists(string $patientId): bool;
}