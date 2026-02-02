<?php

namespace toubilib\mail\application;

use toubilib\mail\domain\MailerInterface;


class NotificationService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }


    public function handleRdvEvent(array $data): void
    {
        $eventType = $data['event_type'] ?? '';
        $destinataires = $data['destinataires'] ?? [];
        $rdv = $data['rdv'] ?? [];
        $praticien = $data['praticien'] ?? [];
        $patient = $data['patient'] ?? [];

        foreach ($destinataires as $destinataire) {
            $email = $destinataire['email'] ?? null;
            if (!$email) {
                continue;
            }

            $type = $destinataire['type'] ?? 'unknown';
            $nom = ($destinataire['prenom'] ?? '') . ' ' . ($destinataire['nom'] ?? '');

            switch ($eventType) {
                case 'rdv.created':
                    $this->sendRdvCreatedEmail($email, $nom, $type, $rdv, $praticien, $patient);
                    break;
                case 'rdv.cancelled':
                    $this->sendRdvCancelledEmail($email, $nom, $type, $rdv, $praticien, $patient);
                    break;
                default:
                    echo "[NOTIFICATION] Type d'événement inconnu: {$eventType}\n";
            }
        }
    }

    private function sendRdvCreatedEmail(string $to, string $toName, string $recipientType, array $rdv, array $praticien, array $patient): void
    {
        $dateRdv = $rdv['date_heure_debut'] ?? 'N/A';
        $duree = $rdv['duree'] ?? 'N/A';
        $motif = $rdv['motif'] ?? 'N/A';
        $praticienNom = ($praticien['prenom'] ?? '') . ' ' . ($praticien['nom'] ?? '');
        $specialite = $praticien['specialite'] ?? 'N/A';
        $patientNom = ($patient['prenom'] ?? '') . ' ' . ($patient['nom'] ?? '');

        $subject = "Confirmation de rendez-vous - Toubilib";

        if ($recipientType === 'patient') {
            $htmlBody = $this->buildPatientCreatedEmail($toName, $dateRdv, $duree, $motif, $praticienNom, $specialite);
        } else {
            $htmlBody = $this->buildPraticienCreatedEmail($toName, $dateRdv, $duree, $motif, $patientNom);
        }

        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        if ($this->mailer->send($to, $subject, $htmlBody, $textBody)) {
            echo "[EMAIL ENVOYÉ] RDV créé → {$to} ({$recipientType})\n";
        }
    }

    private function sendRdvCancelledEmail(string $to, string $toName, string $recipientType, array $rdv, array $praticien, array $patient): void
    {
        $dateRdv = $rdv['date_heure_debut'] ?? 'N/A';
        $praticienNom = ($praticien['prenom'] ?? '') . ' ' . ($praticien['nom'] ?? '');
        $patientNom = ($patient['prenom'] ?? '') . ' ' . ($patient['nom'] ?? '');

        $subject = "Annulation de rendez-vous - Toubilib";

        if ($recipientType === 'patient') {
            $htmlBody = $this->buildPatientCancelledEmail($toName, $dateRdv, $praticienNom);
        } else {
            $htmlBody = $this->buildPraticienCancelledEmail($toName, $dateRdv, $patientNom);
        }

        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        if ($this->mailer->send($to, $subject, $htmlBody, $textBody)) {
            echo "[EMAIL ENVOYÉ] RDV annulé → {$to} ({$recipientType})\n";
        }
    }

    private function buildPatientCreatedEmail(string $patientName, string $date, string $duree, string $motif, string $praticienNom, string $specialite): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Confirmation de rendez-vous</h2>
    <p>Bonjour {$patientName},</p>
    <p>Votre rendez-vous a été confirmé.</p>
    <ul>
        <li><strong>Date :</strong> {$date}</li>
        <li><strong>Durée :</strong> {$duree} minutes</li>
        <li><strong>Motif :</strong> {$motif}</li>
        <li><strong>Praticien :</strong> Dr. {$praticienNom} ({$specialite})</li>
    </ul>
    <p>Cordialement,<br>Toubilib</p>
</body>
</html>
HTML;
    }

    private function buildPraticienCreatedEmail(string $praticienName, string $date, string $duree, string $motif, string $patientNom): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Nouveau rendez-vous</h2>
    <p>Bonjour Dr. {$praticienName},</p>
    <p>Un nouveau rendez-vous a été pris.</p>
    <ul>
        <li><strong>Date :</strong> {$date}</li>
        <li><strong>Durée :</strong> {$duree} minutes</li>
        <li><strong>Motif :</strong> {$motif}</li>
        <li><strong>Patient :</strong> {$patientNom}</li>
    </ul>
    <p>Cordialement,<br>Toubilib</p>
</body>
</html>
HTML;
    }

    private function buildPatientCancelledEmail(string $patientName, string $date, string $praticienNom): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Rendez-vous annulé</h2>
    <p>Bonjour {$patientName},</p>
    <p>Votre rendez-vous a été annulé.</p>
    <ul>
        <li><strong>Date :</strong> {$date}</li>
        <li><strong>Praticien :</strong> Dr. {$praticienNom}</li>
    </ul>
    <p>Cordialement,<br>Toubilib</p>
</body>
</html>
HTML;
    }

    private function buildPraticienCancelledEmail(string $praticienName, string $date, string $patientNom): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Rendez-vous annulé</h2>
    <p>Bonjour Dr. {$praticienName},</p>
    <p>Un rendez-vous a été annulé.</p>
    <ul>
        <li><strong>Date :</strong> {$date}</li>
        <li><strong>Patient :</strong> {$patientNom}</li>
    </ul>
    <p>Cordialement,<br>Toubilib</p>
</body>
</html>
HTML;
    }
}
