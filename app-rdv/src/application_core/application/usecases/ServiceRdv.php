<?php

namespace toubilib\core\application\usecases;

use DomainException;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\IndisponibiliteRepositoryInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\domain\entities\praticien\Rdv;
use toubilib\core\domain\entities\praticien\PraticienDetails;
use toubilib\core\domain\entities\praticien\Patient;
use toubilib\core\domain\entities\praticien\Indisponibilite;
use toubilib\core\application\ports\api\dtos\InputRendezVousDTO;
use toubilib\core\application\ports\api\dtos\InputIndisponibiliteDTO;
use Exception;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ServiceRdv implements ServiceRdvInterface 
{
    private RdvRepositoryInterface $rdvRepository;
    private PraticienRepositoryInterface $praticienRepository;
    private PatientRepositoryInterface $patientRepository;
    private IndisponibiliteRepositoryInterface $indisponibiliteRepository;

    
    public function __construct(
        RdvRepositoryInterface $rdvRepository,
        PraticienRepositoryInterface $praticienRepository,
        PatientRepositoryInterface $patientRepository,
        IndisponibiliteRepositoryInterface $indisponibiliteRepository
    ) {
        $this->rdvRepository       = $rdvRepository;
        $this->praticienRepository = $praticienRepository;
        $this->patientRepository   = $patientRepository;
        $this->indisponibiliteRepository = $indisponibiliteRepository;
    }

    public function getCreneauxOccupes(string $praticienId, \DateTime $dateDebut, \DateTime $dateFin): array
    {
        return $this->rdvRepository->getCreneauxOccupes(
            $praticienId,
            $dateDebut->format('Y-m-d H:i:s'),
            $dateFin->format('Y-m-d H:i:s')
        );
    }


    public function getRdvById(string $rdvId): ?Rdv {
        return $this->rdvRepository->findById($rdvId);
    }

    public function getPraticienDetails(string $id): ?PraticienDetails {
        return $this->praticienRepository->findDetailsById($id);
    }

    public function getPatientById(string $id): ?Patient {
        return $this->patientRepository->findById($id);
    }

    public function creerRendezVous(InputRendezVousDTO $dto): Rdv
    {
        // 1. Transformer DTO en DateTime
        $dateDebut = $dto->dateHeureDebut;
        $dateFin   = (clone $dateDebut)->modify("+{$dto->duree} minutes");

        // 2. Vérifier que praticien existe
        $praticien = $this->praticienRepository->findDetailsById($dto->praticienId);
        if (!$praticien) {
            throw new DomainException("Praticien introuvable.");
        }

        // 3. Vérifier que patient existe
        $patient = $this->patientRepository->findById($dto->patientId);
        if (!$patient) {
            throw new DomainException("Patient introuvable.");
        }

        // 4. Vérifier que le motif fait partie des motifs du praticien
        $motifs = $praticien->getMotifs();
        $motifValide = false;

        foreach ($motifs as $motif) {
            if ($motif->getLibelle() === $dto->motifVisite) {
                $motifValide = true;
                break;
            }
        }
        if (!$motifValide) {
            throw new DomainException("Motif de visite invalide pour ce praticien.");
        }

        // 5. Vérifier créneau horaire valide (lundi → vendredi, 8h → 19h)
        $jourSemaine = (int)$dateDebut->format('N');
        if ($jourSemaine > 5) {
            throw new DomainException("Les rendez-vous doivent être pris du lundi au vendredi.");
        }

        $heureDebut = (int)$dateDebut->format('H');
        $heureFin   = (int)$dateFin->format('H');

        if ($heureDebut < 8 || $heureFin >= 19) {
            throw new DomainException("Les rendez-vous doivent être entre 08h00 et 19h00.");
        }

        // 6. Vérifier disponibilité du praticien (RDV existants)
        $creneaux = $this->rdvRepository->getCreneauxOccupes(
            $dto->praticienId,
            $dateDebut->format('Y-m-d H:i:s'),
            $dateFin->format('Y-m-d H:i:s')
        );

        foreach ($creneaux as $creneau) {
            $existDebut = $creneau->getDateHeureDebut();
            $existFin   = $creneau->getDateHeureFin();

            if ($dateDebut < $existFin && $dateFin > $existDebut) {
                throw new DomainException("Le praticien n’est pas disponible sur ce créneau (RDV).");
            }
        }

        // 6b. Vérifier indisponibilités
        $indisponibilites = $this->indisponibiliteRepository->getIndisponibilitesByPraticienAndPeriode(
            $dto->praticienId,
            $dateDebut,
            $dateFin
        );

        foreach ($indisponibilites as $indispo) {
            $existDebut = $indispo->getDateDebut();
            $existFin   = $indispo->getDateFin();

            if ($dateDebut < $existFin && $dateFin > $existDebut) {
                throw new DomainException("Le praticien est indisponible sur ce créneau.");
            }
        }

        // 7. Construire l'entité RDV
        $rdv = new Rdv(
            \Ramsey\Uuid\Uuid::uuid4()->toString(),
            $dto->praticienId,
            $dto->patientId,
            $patient->getEmail(),
            $dateDebut,
            0, // statut par défaut
            $dto->duree,
            $dateFin,
            new \DateTimeImmutable(),
            $dto->motifVisite
        );

        // 8. Sauvegarder en base
        $this->rdvRepository->save($rdv);

        // 9. Envoyer un message RabbitMQ pour notification email
        $this->publishRdvEvent('rdv.created', $rdv, $praticien, $patient);

        return $rdv;
    }

    /**
     * Publie un événement RDV sur RabbitMQ
     */
    private function publishRdvEvent(string $routingKey, Rdv $rdv, PraticienDetails $praticien, Patient $patient): void
    {
        $host = 'rabbitmq';
        $port = 5672;
        $user = 'toubi';
        $pass = 'toubi';
        $exchangeName = 'toubilib.events';

        try {
            $connection = new AMQPStreamConnection($host, $port, $user, $pass);
            $channel = $connection->channel();

            // Déclarer l'exchange (topic pour routing flexible)
            $channel->exchange_declare($exchangeName, 'topic', false, true, false);

            // Construire le message avec les infos du RDV
            $messageData = [
                'event_type' => $routingKey,
                'timestamp' => (new \DateTimeImmutable())->format('c'),
                'destinataires' => [
                    [
                        'type' => 'patient',
                        'email' => $patient->getEmail(),
                        'nom' => $patient->getNom(),
                        'prenom' => $patient->getPrenom()
                    ],
                    [
                        'type' => 'praticien',
                        'email' => $praticien->getEmail(),
                        'nom' => $praticien->getNom(),
                        'prenom' => $praticien->getPrenom()
                    ]
                ],
                'rdv' => [
                    'id' => $rdv->getId(),
                    'date_heure_debut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                    'date_heure_fin' => $rdv->getDateHeureFin()->format('Y-m-d H:i:s'),
                    'duree' => $rdv->getDuree(),
                    'motif' => $rdv->getMotifVisite(),
                    'statut' => $rdv->getStatus()
                ],
                'praticien' => [
                    'id' => $praticien->getId(),
                    'nom' => $praticien->getNom(),
                    'prenom' => $praticien->getPrenom(),
                    'specialite' => $praticien->getSpecialite()->getLibelle()
                ],
                'patient' => [
                    'id' => $patient->getId(),
                    'nom' => $patient->getNom(),
                    'prenom' => $patient->getPrenom(),
                    'email' => $patient->getEmail()
                ]
            ];

            $message = new AMQPMessage(
                json_encode($messageData),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]
            );
            $channel->basic_publish($message, $exchangeName, $routingKey);

            $channel->close();
            $connection->close();

        } catch (Exception $e) {
            error_log("Erreur RabbitMQ: " . $e->getMessage());
        }
    }


    public function annulerRendezVous(string $idRdv): void
    {
        $rdv = $this->rdvRepository->findById($idRdv);

        if (!$rdv) {
            throw new Exception("Rendez-vous introuvable.");
        }

        // Récupérer les infos praticien et patient pour la notification
        $praticien = $this->praticienRepository->findDetailsById($rdv->getPraticienId());
        $patient = $this->patientRepository->findById($rdv->getPatientId());

        $rdv->annuler();
        $this->rdvRepository->update($rdv);

        // Publier l'événement d'annulation
        if ($praticien && $patient) {
            $this->publishRdvEvent('rdv.cancelled', $rdv, $praticien, $patient);
        }
    }

    public function consulterAgenda(string $praticienId, \DateTime $dateDebut, \DateTime $dateFin): array
    {
        $rdvs = $this->rdvRepository->getCreneauxOccupes(
            $praticienId,
            $dateDebut->format('Y-m-d H:i:s'),
            $dateFin->format('Y-m-d H:i:s')
        );

        $indisponibilites = $this->indisponibiliteRepository->getIndisponibilitesByPraticienAndPeriode(
            $praticienId,
            \DateTimeImmutable::createFromMutable($dateDebut),
            \DateTimeImmutable::createFromMutable($dateFin)
        );

        return array_merge($rdvs, $indisponibilites);
    }

    public function getHistoriquePatient(string $patientId): array
    {
        return $this->rdvRepository->findRdvsByPatientId($patientId);
    }

    public function creerIndisponibilite(InputIndisponibiliteDTO $dto): string
    {
        $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $dateDebut = new \DateTimeImmutable($dto->dateDebut);
        $dateFin = new \DateTimeImmutable($dto->dateFin);

        if ($dateFin <= $dateDebut) {
            throw new DomainException("La date de fin doit être postérieure à la date de début.");
        }

        $indispo = new Indisponibilite(
            $id,
            $dto->praticienId,
            $dateDebut,
            $dateFin,
            $dto->motif
        );

        $this->indisponibiliteRepository->save($indispo);

        return $id;
    }

    public function marquerRdvHonore(string $idRdv): void
    {
        $rdv = $this->rdvRepository->findById($idRdv);
        if (!$rdv) {
            throw new DomainException("Rendez-vous introuvable.");
        }
        $rdv->marquerCommeHonore();
        $this->rdvRepository->update($rdv);
    }

    public function marquerRdvNonHonore(string $idRdv): void
    {
        $rdv = $this->rdvRepository->findById($idRdv);
        if (!$rdv) {
            throw new DomainException("Rendez-vous introuvable.");
        }
        $rdv->marquerCommeNonHonore();
        $this->rdvRepository->update($rdv);
    }
}
