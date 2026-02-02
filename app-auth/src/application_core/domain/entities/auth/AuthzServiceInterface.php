<?php

namespace toubilib\core\domain\entities\auth;

use toubilib\core\domain\exceptions\AuthorizationException;

/**
 * Interface pour le service d'autorisation des rendez-vous
 */
interface AuthzServiceInterface
{
    /**
     * Vérifie si l'utilisateur peut accéder à l'agenda d'un praticien
     * 
     * @param UserProfile $userProfile Profil de l'utilisateur authentifié
     * @param string $praticienId ID du praticien dont on veut consulter l'agenda
     * @return bool True si l'accès est autorisé
     * @throws AuthorizationException Si l'accès est refusé
     */
    public function canAccessPraticienAgenda(UserProfile $userProfile, string $praticienId): bool;

    /**
     * Vérifie si l'utilisateur peut accéder au détail d'un rendez-vous
     * 
     * @param UserProfile $userProfile Profil de l'utilisateur authentifié
     * @param string $rdvId ID du rendez-vous
     * @return bool True si l'accès est autorisé
     * @throws AuthorizationException Si l'accès est refusé
     */
    public function canAccessRendezVousDetail(UserProfile $userProfile, string $rdvId): bool;

    /**
     * Vérifie si l'utilisateur peut créer un rendez-vous
     * 
     * @param UserProfile $userProfile Profil de l'utilisateur authentifié
     * @param array $rdvData Données du rendez-vous à créer
     * @return bool True si l'action est autorisée
     * @throws AuthorizationException Si l'action est refusée
     */
    public function canCreateRendezVous(UserProfile $userProfile, array $rdvData): bool;

    /**
     * Vérifie si l'utilisateur peut annuler un rendez-vous
     * 
     * @param UserProfile $userProfile Profil de l'utilisateur authentifié
     * @param string $rdvId ID du rendez-vous à annuler
     * @return bool True si l'action est autorisée
     * @throws AuthorizationException Si l'action est refusée
     */
    public function canCancelRendezVous(UserProfile $userProfile, string $rdvId): bool;
}