<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\api\AuthzRdvServiceInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\domain\entities\auth\User;
use toubilib\core\domain\exceptions\AuthorizationException;


class AuthzRdvService implements AuthzRdvServiceInterface
{
    private RdvRepositoryInterface $rdvRepository;

    public function __construct(RdvRepositoryInterface $rdvRepository)
    {
        $this->rdvRepository = $rdvRepository;
    }

  
    public function canAccessPraticienAgenda(User $user, string $praticienId): bool
    {
        // Seuls les praticiens peuvent accéder aux agendas
        if (!$user->isPraticien()) {
            throw new AuthorizationException("Accès refusé : seuls les praticiens peuvent consulter les agendas");
        }

        // Un praticien ne peut consulter que son propre agenda
        if ($user->getId() !== $praticienId) {
            throw new AuthorizationException("Accès refusé : vous ne pouvez consulter que votre propre agenda");
        }

        return true;
    }


    public function canAccessRendezVousDetail(User $user, string $rdvId): bool
    {
        // Récupération des détails du rendez-vous
        $rdv = $this->rdvRepository->findById($rdvId);
        
        if (!$rdv) {
            throw new AuthorizationException("Rendez-vous non trouvé");
        }

        if ($user->isPraticien()) {
            // Un praticien peut voir les RDV où il est le praticien
            if ($user->getId() === $rdv->getPraticienId()) {
                return true;
            }
            throw new AuthorizationException("Accès refusé : ce rendez-vous ne vous concerne pas");
        }

        if ($user->isPatient()) {
            // Un patient peut voir ses propres RDV
            if ($user->getId() === $rdv->getPatientId()) {
                return true;
            }
            throw new AuthorizationException("Accès refusé : ce rendez-vous ne vous concerne pas");
        }

        throw new AuthorizationException("Rôle utilisateur non reconnu");
    }


    public function canCreateRendezVous(User $user, array $rdvData): bool
    {
        if ($user->isPatient()) {
            // Un patient ne peut créer des RDV que pour lui-même
            $patientId = $rdvData['patientId'] ?? $rdvData['patient_id'] ?? null;
            if ($patientId && $patientId !== $user->getId()) {
                throw new AuthorizationException("Accès refusé : vous ne pouvez créer des rendez-vous que pour vous-même");
            }
            return true;
        }

        if ($user->isPraticien()) {
            // Un praticien ne peut créer des RDV que pour lui-même comme praticien
            $praticienId = $rdvData['praticienId'] ?? $rdvData['praticien_id'] ?? null;
            if ($praticienId && $praticienId !== $user->getId()) {
                throw new AuthorizationException("Accès refusé : vous ne pouvez créer des rendez-vous que pour vous-même en tant que praticien");
            }
            return true;
        }

        throw new AuthorizationException("Rôle utilisateur non reconnu");
    }
}
