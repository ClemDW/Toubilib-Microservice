<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\domain\entities\auth\User;
use toubilib\core\domain\exceptions\AuthorizationException;

/**
 * Interface pour le service d'autorisation des rendez-vous
 */
interface AuthzRdvServiceInterface
{
    
    public function canAccessPraticienAgenda(User $user, string $praticienId): bool;

    
    public function canAccessRendezVousDetail(User $user, string $rdvId): bool;

    
    public function canCreateRendezVous(User $user, array $rdvData): bool;
}
