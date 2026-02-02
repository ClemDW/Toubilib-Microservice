<?php

namespace toubilib\mail\infrastructure;

use toubilib\mail\domain\MailerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Exception;


class SymfonyMailerAdapter implements MailerInterface
{
    private Mailer $mailer;
    private string $fromEmail;
    private string $fromName;

    public function __construct(string $dsn, string $fromEmail, string $fromName)
    {
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): bool
    {
        try {
            $email = (new Email())
                ->from("{$this->fromName} <{$this->fromEmail}>")
                ->to($to)
                ->subject($subject)
                ->html($htmlBody);

            if ($textBody !== null) {
                $email->text($textBody);
            }

            $this->mailer->send($email);
            return true;

        } catch (Exception $e) {
            echo "[ERREUR MAIL] " . $e->getMessage() . "\n";
            return false;
        }
    }
}
