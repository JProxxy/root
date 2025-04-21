<?php
// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';  // Adjust the path if necessary

// Include DB connection
require_once '../app/config/connection.php';

// Get the log ID sent from JS
$inputData = json_decode(file_get_contents('php://input'), true);
$logId = isset($inputData['log_id']) ? (int)$inputData['log_id'] : 0;

if ($logId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid log ID']);
    exit;
}

// Fetch the log details from the database
$stmt = $conn->prepare("SELECT * FROM logs WHERE id = :logId");
$stmt->bindParam(':logId', $logId, PDO::PARAM_INT);
$stmt->execute();
$log = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$log) {
    echo json_encode(['status' => 'error', 'message' => 'Log not found']);
    exit;
}

// Format the log message based on the event
$logMessage = formatLogMessage($log);

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
    $mail->Body = "A new log event has been detected:\n\n" . $logMessage;

    // Send the email
    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Notification sent successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}

// Helper function to format the log message based on event type
function formatLogMessage($log) {
    switch ($log['event_type']) {
        case 'gate_access':
            $user = getUser($log['user_id']);
            return "{$user['name']} accessed the gate at {$log['timestamp']}";
        case 'ac_control':
            return "AC was adjusted at {$log['timestamp']}";
        case 'water_usage':
            return "Water usage recorded at {$log['timestamp']}";
        default:
            return "Unknown event at {$log['timestamp']}";
    }
}

// Function to get user data (for user_id)
function getUser($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
