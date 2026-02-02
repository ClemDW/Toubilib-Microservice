<?php
/**
 * Script de test pour envoyer un message à RabbitMQ
 * À exécuter dans le conteneur service-rdv.toubilib
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

echo "=== Test envoi message RabbitMQ ===\n\n";

// Configuration
$host = 'rabbitmq';
$port = 5672;
$user = 'toubi';
$pass = 'toubi';
$exchangeName = 'toubilib.events';
$queueName = 'rdv_notifications';
$routingKey = 'rdv.created';

try {
    // Connexion à RabbitMQ
    echo "[1] Connexion à RabbitMQ ($host:$port)...\n";
    $connection = new AMQPStreamConnection($host, $port, $user, $pass);
    $channel = $connection->channel();
    echo "[✓] Connecté!\n\n";

    // Déclaration de l'exchange (type: topic)
    echo "[2] Déclaration de l'exchange '$exchangeName' (type: topic)...\n";
    $channel->exchange_declare($exchangeName, 'topic', false, true, false);
    echo "[✓] Exchange créé!\n\n";

    // Déclaration de la queue
    echo "[3] Déclaration de la queue '$queueName'...\n";
    $channel->queue_declare($queueName, false, true, false, false);
    echo "[✓] Queue créée!\n\n";

    // Binding: lier la queue à l'exchange avec les routing keys
    echo "[4] Binding de la queue à l'exchange...\n";
    $channel->queue_bind($queueName, $exchangeName, 'rdv.created');
    $channel->queue_bind($queueName, $exchangeName, 'rdv.cancelled');
    echo "[✓] Bindings créés (rdv.created, rdv.cancelled)!\n\n";

    // Créer un message de test
    $messageData = [
        'event' => 'rdv.created',
        'timestamp' => date('Y-m-d H:i:s'),
        'rdv' => [
            'id' => 'test-123',
            'dateHeureDebut' => '2026-01-25 10:00',
            'motifVisite' => 'Consultation de test',
            'praticien' => [
                'id' => 'p1',
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'dr.dupont@test.fr',
                'specialite' => 'Médecine générale'
            ],
            'patient' => [
                'id' => 'pat1',
                'nom' => 'Martin',
                'prenom' => 'Sophie',
                'email' => 'sophie.martin@test.fr'
            ]
        ]
    ];

    // Publier le message
    echo "[5] Publication du message...\n";
    $msg = new AMQPMessage(
        json_encode($messageData, JSON_PRETTY_PRINT),
        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
    );
    
    $channel->basic_publish($msg, $exchangeName, $routingKey);
    echo "[✓] Message publié!\n\n";

    echo "=== Message envoyé ===\n";
    echo json_encode($messageData, JSON_PRETTY_PRINT) . "\n\n";

    // Fermer la connexion
    $channel->close();
    $connection->close();
    
    echo "[✓] Test terminé avec succès!\n";
    echo "\nVérifiez le panneau RabbitMQ: http://localhost:15672\n";
    echo "Login: toubi / toubi\n";

} catch (\Exception $e) {
    echo "[✗] Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
