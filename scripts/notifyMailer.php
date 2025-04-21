<?php
// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 1);


require '../vendor/autoload.php';  // Load PHPMailer

// Include DB connection (only if you need it)
include '../app/config/connection.php';

// Assuming you are already reading the body as JSON
$inputData = json_decode(file_get_contents('php://input'), true);

// Log the incoming payload for debugging
file_put_contents('php://stderr', "Received Payload: " . print_r($inputData, true) . "\n");

// Check if the required fields exist
$logId = isset($inputData['log_id']) ? (int)$inputData['log_id'] : 0;
$message = isset($inputData['message']) ? $inputData['message'] : '';
$timestamp = isset($inputData['timestamp']) ? $inputData['timestamp'] : '';

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message is required']);
    exit;
}

// Prepare the email
$mail = new PHPMailer(true);

try {
    // Set PHPMailer to use SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'superadmin@rivaniot.online'; // Your full Hostinger email address
    $mail->Password = 'superAdmin0507!'; // Your Hostinger email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('superadmin@rivaniot.online', 'Rivan IoT');
    $mail->addAddress('superadmin@rivaniot.online'); // Super admin email
    $mail->addAddress('jpenarubia.a0001@rivaniot.online'); // Another recipient email

    // Subject and Body
    $mail->Subject = 'New Event Log';
    $mail->Body = "A new log event has been detected:\n\n" . $message . "\nTimestamp: " . $timestamp;

    // Send the email
    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Notification sent successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}

?>
