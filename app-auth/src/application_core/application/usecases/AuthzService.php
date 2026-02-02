<?php

namespace toubilib\core\application\usecases;

use toubilib\core\domain\entities\auth\AuthzServiceInterface;
use toubilib\core\domain\entities\auth\UserProfile;
use toubilib\core\domain\exceptions\AuthorizationException;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepository;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepository;

/**
 * Service d'autorisation pour les rendez-vous
 * Implémente les politiques d'autorisation métier
 */
class AuthzService implements AuthzServiceInterface
{
    private RdvRepository $rdvRepository;
    private PraticienRepository $praticienRepository;

    public function __construct(
        RdvRepository $rdvRepository,
        PraticienRepository $praticienRepository
    ) {
        $this->rdvRepository = $rdvRepository;
        $this->praticienRepository = $praticienRepository;
    }

    /**
     * Politique d'autorisation pour l'accès à l'agenda d'un praticien :
     * - Les praticiens peuvent voir leur propre agenda
     * - Les patients ne peuvent pas voir les agendas des praticiens
     */
    public function canAccessPraticienAgenda(UserProfile $userProfile, string $praticienId): bool
    {
        // Seuls les praticiens peuvent accéder aux agendas
        if (!$userProfile->isPraticien()) {
            throw new AuthorizationException("Accès refusé : seuls les praticiens peuvent consulter les agendas");
        }

        // Un praticien ne peut consulter que son propre agenda
        if ($userProfile->getId() !== $praticienId) {
            throw new AuthorizationException("Accès refusé : vous ne pouvez consulter que votre propre agenda");
        }

        return true;
    }

    /**
     * Politique d'autorisation pour l'accès au détail d'un rendez-vous :
     * - Les praticiens peuvent voir les RDV où ils sont le praticien
     * - Les patients peuvent voir leurs propres RDV
     */
    public function canAccessRendezVousDetail(UserProfile $userProfile, string $rdvId): bool
    {
        // Récupération des détails du rendez-vous
        $rdvDetails = $this->getRdvDetails($rdvId);
        
        if (!$rdvDetails) {
            throw new AuthorizationException("Rendez-vous non trouvé");
        }

        if ($userProfile->isPraticien()) {
            // Un praticien peut voir les RDV où il est le praticien
            if ($userProfile->getId() === $rdvDetails['praticien_id']) {
                return true;
            }
            throw new AuthorizationException("Accès refusé : ce rendez-vous ne vous concerne pas");
        }

        if ($userProfile->isPatient()) {
            // Un patient peut voir ses propres RDV
            if ($userProfile->getId() === $rdvDetails['patient_id']) {
                return true;
            }
            throw new AuthorizationException("Accès refusé : ce rendez-vous ne vous concerne pas");
        }

        throw new AuthorizationException("Rôle utilisateur non reconnu");
    }

    /**
     * Politique d'autorisation pour la création d'un rendez-vous :
     * - Les patients peuvent créer des RDV pour eux-mêmes
     * - Les praticiens peuvent créer des RDV pour leurs patients
     */
    public function canCreateRendezVous(UserProfile $userProfile, array $rdvData): bool
    {
        if ($userProfile->isPatient()) {
            // Un patient ne peut créer des RDV que pour lui-même
            if (isset($rdvData['patient_id']) && $rdvData['patient_id'] !== $userProfile->getId()) {
                throw new AuthorizationException("Accès refusé : vous ne pouvez créer des rendez-vous que pour vous-même");
            }
            return true;
        }

        if ($userProfile->isPraticien()) {
            
            return true;
        }

        throw new AuthorizationException("Rôle utilisateur non reconnu");
    }

    /**
     * Politique d'autorisation pour l'annulation d'un rendez-vous :
     * - Les patients peuvent annuler leurs propres RDV
     * - Les praticiens peuvent annuler les RDV où ils sont impliqués
     */
    public function canCancelRendezVous(UserProfile $userProfile, string $rdvId): bool
    {
        // Récupération des détails du rendez-vous
        $rdvDetails = $this->getRdvDetails($rdvId);
        
        if (!$rdvDetails) {
            throw new AuthorizationException("Rendez-vous non trouvé");
        }

        if ($userProfile->isPraticien()) {
            // Un praticien peut annuler les RDV où il est le praticien
            if ($userProfile->getId() === $rdvDetails['praticien_id']) {
                return true;
            }
            throw new AuthorizationException("Accès refusé : vous ne pouvez annuler que vos propres rendez-vous");
        }

        if ($userProfile->isPatient()) {
            // Un patient peut annuler ses propres RDV
            if ($userProfile->getId() === $rdvDetails['patient_id']) {
                return true;
            }
            throw new AuthorizationException("Accès refusé : vous ne pouvez annuler que vos propres rendez-vous");
        }

        throw new AuthorizationException("Rôle utilisateur non reconnu");
    }

    /**
     * Méthode utilitaire pour récupérer les détails d'un rendez-vous
     */
    private function getRdvDetails(string $rdvId): ?array
    {
        try {
            $rdv = $this->rdvRepository->consulterRendezVousParId($rdvId);
            if (!$rdv) {
                return null;
            }
            
            // Conversion en array pour faciliter l'accès aux propriétés
            return [
                'id' => $rdv->getId(),
                'praticien_id' => $rdv->getPraticienId(),
                'patient_id' => $rdv->getPatientId(),
                'status' => $rdv->getStatus()
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}