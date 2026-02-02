<?php

namespace toubilib\mail\domain;

/**
 * Interface pour l'envoi de mails
 * Permet de changer facilement d'implémentation
 */
interface MailerInterface
{
    /**
     * Envoie un email
     *
     * @param string $to Adresse email du destinataire
     * @param string $subject Sujet du mail
     * @param string $htmlBody Corps du mail en HTML
     * @param string|null $textBody Corps du mail en texte brut (optionnel)
     * @return bool True si l'envoi a réussi
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): bool;
}
