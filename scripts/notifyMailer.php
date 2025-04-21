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

    $mail->isHTML(true);
    $mail->Subject = $fullSubject;
    $mail->Body = <<<EOT
    <p><strong>New Gate Access Event</strong></p>
    
    <p>
    User: <strong>jpenarubia.a0001</strong><br>
    Action: Opened the gate using website<br>
    Time: 2025-04-21 14:43:55
    </p>
    
  
    <em>This is an automated message. Please do not reply to this email.</em>
    
    <hr>

    
    <!-- Footer Start -->
    <?php
    echo '<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0D2153;color:#fff;padding:40px 20px;font-family:Arial,sans-serif;font-size:12px;line-height:1.4;">
      <tr>
        <td align="center">
          <img src="https://yourdomain.com/assets/logo.png" alt="Your Logo" width="80" style="display:block;margin:0 auto 10px;">
          <p><strong>Your Company</strong><br>Your Tagline</p>
        </td>
      </tr>
      <tr>
        <td align="center" style="padding-bottom:20px;">
          <!-- Social Media Links -->
          <a href="https://www.facebook.com/YourPage" style="margin:0 5px;text-decoration:none;">
            <img src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/svgs/brands/facebook-square.svg" alt="Facebook" width="24" style="display:inline-block;">
          </a>
          <a href="https://www.instagram.com/YourProfile" style="margin:0 5px;text-decoration:none;">
            <img src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/svgs/brands/instagram-square.svg" alt="Instagram" width="24" style="display:inline-block;">
          </a>
          <a href="https://twitter.com/YourHandle" style="margin:0 5px;text-decoration:none;">
            <img src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/svgs/brands/twitter-square.svg" alt="Twitter" width="24" style="display:inline-block;">
          </a>
        </td>
      </tr>
      <tr>
        <td style="padding-bottom:20px;text-align:center;">
          <!-- Contact Info -->
          <p style="margin:5px 0;">Your Address</p>
          <p style="margin:5px 0;"><a href="mailto:youremail@domain.com" style="color:#fff;text-decoration:underline;">youremail@domain.com</a></p>
          <p style="margin:5px 0;"><a href="tel:+1234567890" style="color:#fff;text-decoration:underline;">+1 234-567-890</a></p>
          <p style="margin:5px 0;">Mon–Fri 9:00 AM – 5:00 PM</p>
        </td>
      </tr>
      <tr>
        <td align="center" style="border-top:1px solid #444;padding-top:10px;font-size:10px;color:#ccc;">
          © 2025 Your Company. All rights reserved.
        </td>
      </tr>
    </table>';
    ?>
    <!-- Footer End -->
    

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