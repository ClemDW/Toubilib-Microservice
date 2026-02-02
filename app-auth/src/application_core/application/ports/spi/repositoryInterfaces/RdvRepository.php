<?php 
 namespace toubilib\core\application\ports\spi\repositoryInterfaces;
    use toubilib\core\domain\entities\rdv\RendezVous;
    use DateTime;

    interface RdvRepository{
        /**
         * @return RendezVous[]
         */
        public function listerRdvOcuppePraticienParDate(DateTime $debut, DateTime $fin, string $practicien_id): array;
        
        /**
         * Consulter un rendez-vous par son identifiant
         */
        public function consulterRendezVousParId(string $id): ?RendezVous;

    public function creerRendezVous(RendezVous $rdv): RendezVous;

    /**
     * Sauvegarde les modifications d'un rendez-vous existant
     */
    public function sauvegarderRendezVous(RendezVous $rdv): void;

    public function rdvHonore(string $rdvId): void;
    public function rdvRefuse(string $rdvId): void;
    }