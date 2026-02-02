<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\api\ServicePatientInterface;
use toubilib\core\application\ports\api\dtos\InputPatientDTO;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\domain\entities\praticien\Patient;
use Ramsey\Uuid\Uuid;

class ServicePatient implements ServicePatientInterface
{
    private PatientRepositoryInterface $patientRepository;

    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function createPatient(InputPatientDTO $dto): Patient
    {
       

        $id = Uuid::uuid4()->toString();
        $dateNaissance = $dto->dateNaissance ? new \DateTime($dto->dateNaissance) : null;

        $patient = new Patient(
            $id,
            $dto->nom,
            $dto->prenom,
            $dateNaissance,
            $dto->adresse,
            $dto->codePostal,
            $dto->ville,
            $dto->email,
            $dto->telephone
        );

        $this->patientRepository->save($patient);

        return $patient;
    }
}
