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
    
    <p>If this action was not expected, please verify it through the 
    <a href="https://rivaniot.online/dashboard">admin dashboard</a>.</p>
    
    <hr>
    
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 12px; color: #555;">
      <tr>
        <td align="center">
          <img src="https://rivaniot.online/assets/images/rivanLogo.png" alt="Rivan IoT Logo" width="100" style="margin-bottom: 10px;">
          <p style="margin: 5px 0;"><strong>Rivan IoT Notification System</strong><br>
          Smart Automation for Smarter Living</p>
          <a href="https://rivaniot.online" style="color: #007BFF;">https://rivaniot.online</a><br><br>
          <em>This is an automated message. Please do not reply to this email.</em>
        </td>
      </tr>
    </table>
    
    <!-- Footer Start -->
    <footer class="bg-[#0D2153] text-white pt-16 pb-6 px-4 relative">
      <div class="container mx-auto max-w-6xl">
        <div class="flex flex-col md:flex-row justify-between items-start">
          <div class="md:w-1/2 mb-6 md:mb-0">
            <h2 class="text-xl font-semibold mb-2">RivanCyber Training Institute</h2>
            <p class="text-gray-200 mb-4">Rivan Cyber Institute is a Network Engineering Bootcamp that caters not just to people around the IT industry but also career shifters.</p>
            <div class="flex space-x-4">
              <a href="https://www.facebook.com/RivanInstitute" target="_blank" rel="noreferrer" class="bg-white rounded-full p-2 transition hover:shadow-lg">
                <!-- Facebook Icon -->
              </a>
              <a href="https://m.me/RivanInstitute" target="_blank" rel="noreferrer" class="bg-white rounded-full p-2 transition hover:shadow-lg">
                <!-- Messenger Icon -->
              </a>
              <a href="https://www.instagram.com/rivancyberinstitute" target="_blank" rel="noreferrer" class="bg-white rounded-full p-2 transition hover:shadow-lg">
                <!-- Instagram Icon -->
              </a>
            </div>
          </div>
          <div class="md:w-1/2 flex flex-col items-start md:items-end text-gray-200">
            <h2 class="text-lg font-semibold mb-2">Contact us</h2>
            <p class="flex items-center mb-1">
              <!-- Address Icon -->
              Rivan Building, 18d Mola, Makati, 1200 Metro Manila
            </p>
            <p class="flex items-center mb-1">
              <!-- Email Icon -->
              <a href="mailto:teamrivan@rcvi.org@gmail.com" class="hover:text-blue-400 transition">teamrivan@rcvi.org</a>
            </p>
            <p class="flex items-center mb-1">
              <!-- Phone Icon -->
              <a href="tel:+639493760000" class="hover:text-blue-400 transition">+63 949-376-0000</a>
            </p>
            <p class="flex items-center mb-1">
              <!-- Landline Icon -->
              <a href="tel:+63284252848" class="hover:text-blue-400 transition">+63 2-8425-2848 (Landline)</a>
            </p>
            <p class="flex items-center mb-1">
              <!-- Working Hours Icon -->
              Mon-Fri 9:00AM - 5:00PM
            </p>
          </div>
        </div>
        <div class="mt-6 border-t border-gray-600 pt-4 text-center text-sm">
          © 2025 All Rights Reserved. Design by Leigh.
        </div>
      </div>
      <button class="fixed bottom-6 right-6 bg-white rounded-full p-3 shadow-lg transition hover:bg-gray-200 cursor-pointer">
        <!-- Scroll to Top Icon -->
      </button>
    </footer>
    <!-- Footer End -->
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