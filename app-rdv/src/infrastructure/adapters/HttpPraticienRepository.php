<?php

namespace toubilib\infra\adapters;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\domain\entities\praticien\PraticienDetails;
use toubilib\core\domain\entities\praticien\Specialite;
use toubilib\core\domain\entities\praticien\Structure;
use toubilib\core\domain\entities\praticien\MotifVisite;
use toubilib\core\domain\entities\praticien\MoyenPaiement;

class HttpPraticienRepository implements PraticienRepositoryInterface
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function findAll(): array
    {
        // Not used by ServiceRdv
        return [];
    }

    public function findById(string $id)
    {
        // ServiceRdv might use findDetailsById instead, but let's see. 
        // If findById is expected to return Praticien (not Details), we should implement it if needed.
        // But for now ServiceRdv uses findDetailsById explicitly.
        return null;
    }

    public function search(string $specialite, string $ville): array
    {
        // Not used by ServiceRdv
        return [];
    }

    public function findDetailsById(string $id): ?PraticienDetails
    {
        try {
            $response = $this->client->request('GET', "/praticiens/{$id}");
            $data = json_decode($response->getBody()->getContents(), true);

            if (!$data) {
                return null;
            }

            // Map Specialite
            $specialiteData = $data['specialite'] ?? [];
            $specialite = new Specialite(
                $specialiteData['id'] ?? 0,
                $specialiteData['libelle'] ?? '',
                $specialiteData['description'] ?? ''
            );

            // Map Structure
            $structure = null;
            if (!empty($data['structure'])) {
                $sData = $data['structure'];
                $structure = new Structure(
                    $sData['id'] ?? '',
                    $sData['nom'] ?? '',
                    $sData['adresse'] ?? '',
                    $sData['ville'] ?? '',
                    $sData['codePostal'] ?? '', // JSON key is camelCase
                    $sData['telephone'] ?? ''
                );
            }

            // Map Motifs
            $motifs = [];
            if (!empty($data['motifs'])) {
                foreach ($data['motifs'] as $mData) {
                    $motifs[] = new MotifVisite(
                        $mData['id'],
                        $mData['specialiteId'] ?? 0,
                        $mData['libelle'] ?? ''
                    );
                }
            }

            // Map Moyens
            $moyens = [];
            if (!empty($data['moyens'])) {
                foreach ($data['moyens'] as $mData) {
                    $moyens[] = new MoyenPaiement(
                        $mData['id'],
                        $mData['libelle'] ?? ''
                    );
                }
            }

            return new PraticienDetails(
                $data['id'],
                $data['nom'] ?? '',
                $data['prenom'] ?? '',
                $data['titre'] ?? '',
                $data['email'] ?? '',
                $data['telephone'] ?? '',
                $data['ville'] ?? '',
                $data['rppsId'] ?? null,
                $data['organisation'] ?? false,
                $data['nouveauPatient'] ?? false,
                $specialite,
                $structure,
                $motifs,
                $moyens
            );

        } catch (GuzzleException $e) {
            // Log error ?
            // For now return null as if not found
            return null;
        }
    }
}
