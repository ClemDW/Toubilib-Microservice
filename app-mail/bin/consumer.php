<?php

/**
 * RabbitMQ Message Consumer
 * Consomme les messages de la queue rdv_notifications et envoie des emails
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use toubilib\mail\infrastructure\SymfonyMailerAdapter;
use toubilib\mail\application\NotificationService;

// Configuration RabbitMQ 
$rabbitHost = getenv('RABBITMQ_HOST');
$rabbitPort = getenv('RABBITMQ_PORT');
$rabbitUser = getenv('RABBITMQ_USER');
$rabbitPass = getenv('RABBITMQ_PASS');
$exchangeName = 'toubilib.events';
$queueName = 'rdv_notifications';
$routingKey = 'rdv.*';

// Configuration Mail 
$mailHost = getenv('MAIL_HOST');
$mailPort = getenv('MAIL_PORT');
$mailDsn = "smtp://{$mailHost}:{$mailPort}";
$mailFrom = getenv('MAIL_FROM');
$mailFromName = getenv('MAIL_FROM_NAME');

echo "[Consumer] Démarrage du consommateur RabbitMQ...\n";
echo "[Consumer] RabbitMQ: {$rabbitHost}:{$rabbitPort}\n";
echo "[Consumer] Mail: {$mailDsn}\n";

// Initialiser le service de notification avec l'adaptateur Symfony Mailer
$mailer = new SymfonyMailerAdapter($mailDsn, $mailFrom, $mailFromName);
$notificationService = new NotificationService($mailer);

try {
    // Connexion à RabbitMQ
    $connection = new AMQPStreamConnection($rabbitHost, $rabbitPort, $rabbitUser, $rabbitPass);
    $channel = $connection->channel();

    echo "[Consumer] Connecté à RabbitMQ\n";

    // Déclarer l'exchange (topic)
    $channel->exchange_declare($exchangeName, 'topic', false, true, false);
    echo "[Consumer] Exchange '{$exchangeName}' déclaré\n";

    // Déclarer la queue
    $channel->queue_declare($queueName, false, true, false, false);
    echo "[Consumer] Queue '{$queueName}' déclarée\n";

    // Binder la queue à l'exchange avec la routing key
    $channel->queue_bind($queueName, $exchangeName, $routingKey);
    echo "[Consumer] Queue liée à l'exchange avec la routing key '{$routingKey}'\n";

    // Fonction de callback pour traiter les messages
    $callback = function (AMQPMessage $message) use ($notificationService) {
        echo "\n";
        echo "=====================================\n";
        echo "[MESSAGE REÇU] " . date('Y-m-d H:i:s') . "\n";
        echo "=====================================\n";

        // Décoder le message JSON
        $messageBody = $message->getBody();
        $data = json_decode($messageBody, true);

        if ($data === null) {
            echo "[ERREUR] Impossible de décoder le JSON\n";
            echo "Body: " . $messageBody . "\n";
            $message->ack();
            return;
        }

        // Afficher le contenu du message
        echo "Type d'événement: " . ($data['event_type'] ?? 'N/A') . "\n";
        echo "Timestamp: " . ($data['timestamp'] ?? 'N/A') . "\n";

        if (isset($data['destinataires']) && is_array($data['destinataires'])) {
            echo "Destinataires:\n";
            foreach ($data['destinataires'] as $destinataire) {
                echo "  - Type: " . ($destinataire['type'] ?? 'N/A') . "\n";
                echo "    Email: " . ($destinataire['email'] ?? 'N/A') . "\n";
                echo "    Nom: " . ($destinataire['prenom'] ?? '') . " " . ($destinataire['nom'] ?? '') . "\n";
            }
        }

        if (isset($data['rdv'])) {
            echo "Rendez-vous:\n";
            echo "  - ID: " . ($data['rdv']['id'] ?? 'N/A') . "\n";
            echo "  - Début: " . ($data['rdv']['date_heure_debut'] ?? 'N/A') . "\n";
            echo "  - Fin: " . ($data['rdv']['date_heure_fin'] ?? 'N/A') . "\n";
            echo "  - Durée: " . ($data['rdv']['duree'] ?? 'N/A') . " min\n";
            echo "  - Motif: " . ($data['rdv']['motif'] ?? 'N/A') . "\n";
            echo "  - Statut: " . ($data['rdv']['statut'] ?? 'N/A') . "\n";
        }

        if (isset($data['praticien'])) {
            echo "Praticien:\n";
            echo "  - ID: " . ($data['praticien']['id'] ?? 'N/A') . "\n";
            echo "  - Nom: " . ($data['praticien']['prenom'] ?? '') . " " . ($data['praticien']['nom'] ?? '') . "\n";
            echo "  - Spécialité: " . ($data['praticien']['specialite'] ?? 'N/A') . "\n";
        }

        if (isset($data['patient'])) {
            echo "Patient:\n";
            echo "  - ID: " . ($data['patient']['id'] ?? 'N/A') . "\n";
            echo "  - Nom: " . ($data['patient']['prenom'] ?? '') . " " . ($data['patient']['nom'] ?? '') . "\n";
            echo "  - Email: " . ($data['patient']['email'] ?? 'N/A') . "\n";
        }

        echo "-------------------------------------\n";
        echo "[ENVOI DES EMAILS]\n";

        // Envoyer les notifications par email
        $notificationService->handleRdvEvent($data);

        echo "=====================================\n";

        // Acquitter le message
        $message->ack();
        echo "[OK] Message traité et acquitté\n";
    };

    // Configuration du consommateur
    $channel->basic_qos(null, 1, null);
    $channel->basic_consume($queueName, '', false, false, false, false, $callback);

    echo "[Consumer] En attente de messages...\n";

    // Boucle de consommation
    while (count($channel->callbacks)) {
        $channel->wait();
    }

} catch (Exception $e) {
    echo "[ERREUR] " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($channel)) {
        $channel->close();
    }
    if (isset($connection)) {
        $connection->close();
    }
    echo "\n[Consumer] Connexion fermée\n";
}
