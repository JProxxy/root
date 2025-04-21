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
$logId = isset($inputData['log_id']) ? (int) $inputData['log_id'] : 0;
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
    // Prepare dynamic subject based on system
    $subjectPrefix = [
        'gateAccess_logs' => 'Access Gate Information',
        'acControl_logs' => 'Air Conditioning Update',
        'water_logs' => 'Water System Log',
        'lighting_logs' => 'Lighting Activity'
    ];

    // Use default if systemName is not in the array
    $subject = isset($subjectPrefix[$systemName])
        ? $subjectPrefix[$systemName]
        : 'System Activity Notification';

    // Add context (like time or alert summary)
    $fullSubject = "$subject - New Event @ " . date("h:i A", strtotime($timestamp));

    // Email content
    $mail->Subject = $fullSubject;
    $mail->Body = "A new log event has been detected in [$systemName]:\n\n" . $message . "\nTimestamp: " . $timestamp;

    // Build the professional email body
    $mail->Body = <<<EOT
📢 *New Gate Access Event*

Dear Team,

We would like to inform you of a new access event logged by the system:

$message

⏱ Timestamp: {$timestamp}

If this activity seems suspicious or unexpected, please review it immediately through the admin dashboard.

—

Kind regards,  
**Rivan IoT Notification System**  
Smart Automation for Smarter Living  
🌐 https://rivaniot.online  

---

*This is an automated notification. Please do not reply to this email.*
EOT;


    // Send the email
    $mail->send();

    // Insert into sent_notifications table
    $stmt = $conn->prepare("INSERT INTO sent_notifications (log_id, system_name, message) VALUES (?, ?, ?)");
    $stmt->execute([$logId, $systemName, $message]);

    echo json_encode(['status' => 'success', 'message' => 'Notification sent successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>