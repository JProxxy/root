<?php
// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';  // Adjust the path if necessary

// Include DB connection (only if you need it)
require_once '../app/config/connection.php';
// Assuming you are already reading the body as JSON
$inputData = json_decode(file_get_contents('php://input'), true);

// Check if the required fields exist
$logId = isset($inputData['log_id']) ? (int)$inputData['log_id'] : 0;
$message = isset($inputData['message']) ? $inputData['message'] : '';
$timestamp = isset($inputData['timestamp']) ? $inputData['timestamp'] : '';

if ($logId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid log ID']);
    exit;
}

// Fetch the log details from the database using the log_id
$stmt = $conn->prepare("SELECT * FROM logs WHERE id = :logId");
$stmt->bindParam(':logId', $logId, PDO::PARAM_INT);
$stmt->execute();
$log = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$log) {
    echo json_encode(['status' => 'error', 'message' => 'Log not found']);
    exit;
}

// Now you have both the log data and the additional message/timestamp
$logMessage = $message . "\nTimestamp: " . $timestamp;

// Prepare the email
$mail = new PHPMailer(true);

try {
    // Set PHPMailer to use SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
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
    $mail->Body = "A new log event has been detected:\n\n" . $logMessage;

    // Send the email
    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Notification sent successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}


?>
