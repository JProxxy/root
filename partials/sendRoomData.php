<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../app/config/connection.php';
require_once '../scripts/notifyMailer.php'; // Adjust if your mailer script is in a different location

$systemName = 'room_data';

try {
    // 1. Fetch the only row from room_data
    $stmt = $conn->query("SELECT * FROM room_data LIMIT 1");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(['sent' => false, 'message' => 'No data found']);
        exit;
    }

    // 2. Build the message
    $message = sprintf(
        "Room Report:\nDevice: %s\nTemperature: %.2f°C\nHumidity: %.2f%%\nTimestamp: %s",
        $data['deviceName'],
        $data['temperature'],
        $data['humidity'],
        $data['timestamp']
    );

    // 3. Send the email
    $payload = [
        'log_id'      => 0,
        'system_name' => $systemName,
        'message'     => $message,
        'timestamp'   => $data['timestamp']
    ];

    // Send the email (assuming notifyMailer.php accepts this payload as POST)
    $ch = curl_init('../scripts/notifyMailer.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    echo json_encode(['sent' => true, 'response' => $response]);

} catch (Exception $e) {
    echo json_encode(['sent' => false, 'error' => $e->getMessage()]);
}
