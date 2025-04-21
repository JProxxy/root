<?php
// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 1);

require '../vendor/autoload.php';  // Load PHPMailer

// Include DB connection
include '../app/config/connection.php';

// Read JSON input
$inputData = json_decode(file_get_contents('php://input'), true);

// Log the incoming payload for debugging
file_put_contents('php://stderr', "Received Payload: " . print_r($inputData, true) . "\n");

// Extract data from payload
$logId = isset($inputData['log_id']) ? (int)$inputData['log_id'] : 0;
$systemName = isset($inputData['system_name']) ? $inputData['system_name'] : '';
$message = isset($inputData['message']) ? $inputData['message'] : '';
$timestamp = isset($inputData['timestamp']) ? $inputData['timestamp'] : '';

// Validation
if (empty($message) || empty($systemName)) {
    echo json_encode(['status' => 'error', 'message' => 'Message and system name are required']);
    exit;
}

// Prepare the email
$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'superadmin@rivaniot.online';
    $mail->Password = 'superAdmin0507!';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('superadmin@rivaniot.online', 'Rivan IoT');
    $mail->addAddress('superadmin@rivaniot.online');
    $mail->addAddress('jpenarubia.a0001@rivaniot.online');

    // Email content
    $mail->Subject = 'New Event Log';
    $mail->Body = "A new log event has been detected:\n\n" . $message . "\nTimestamp: " . $timestamp;

    // Send the email
    $mail->send();

    // ✅ Insert into sent_notifications table
    $stmt = $conn->prepare("INSERT INTO sent_notifications (log_id, system_name) VALUES (?, ?)");
    $stmt->execute([$logId, $systemName]);

    echo json_encode(['status' => 'success', 'message' => 'Notification sent successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>
