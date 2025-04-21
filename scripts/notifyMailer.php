<?php
// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';  // Adjust the path if necessary

// Include DB connection (only if you need it)
require_once '../app/config/connection.php';

// Get the log ID sent from JS
$inputData = json_decode(file_get_contents('php://input'), true);
$logId = isset($inputData['log_id']) ? (int)$inputData['log_id'] : 0;

if ($logId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid log ID']);
    exit;
}

// Format the log message based on the event (you can customize this based on the log_id or any other logic)
$logMessage = "A new event occurred with log ID: {$logId}";

// Prepare the email
$mail = new PHPMailer(true);

try {
    // Set PHPMailer to use SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.mailtrap.io'; // Replace with your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'superadmin@rivaniot.online'; // Your SMTP username
    $mail->Password = 'superAdmin0507!'; // Your SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('superadmin@rivaniot.online', 'Rivan IoT');
    $mail->addAddress('superadmin@rivaniot.online'); // Super admin email
    $mail->addAddress('jpenarubia.a0001@rivaniot.online'); // Another recipient email

    // Subject and Body
    $mail->Subject = 'New Event Log';
    $mail->Body = "A new log event has been detected with the following details:\n\n" . $logMessage;

    // Send the email
    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Notification sent successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}

?>
