<?php
// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 1);

require '../vendor/autoload.php';   // Load PHPMailer
include '../app/config/connection.php';  // DB connection

// Read JSON input
$inputData = json_decode(file_get_contents('php://input'), true);
file_put_contents('php://stderr', "Received Payload: " . print_r($inputData, true) . "\n");

// Extract payload
$logId      = isset($inputData['log_id'])      ? (int) $inputData['log_id']      : 0;
$systemName = isset($inputData['system_name']) ? $inputData['system_name']      : '';
$message    = isset($inputData['message'])     ? $inputData['message']          : '';
$timestamp  = isset($inputData['timestamp'])   ? $inputData['timestamp']        : '';

// Validate
if (!$systemName || !$message) {
    echo json_encode(['status'=>'error','message'=>'Message and system name are required']);
    exit;
}

// 1) Dynamic subjects
$subjects = [
    'gateAccess_logs'  => 'Access Gate Information',
    'acControl_logs'   => 'Air Conditioning Update',
    'water_logs'       => 'Water System Log',
    'lighting_logs'    => 'Lighting Activity',
];
$subjectBase = $subjects[$systemName] ?? 'System Activity Notification';
$fullSubject = sprintf("%s - New Event @ %s", 
    $subjectBase, 
    date("h:i A", strtotime($timestamp))
);

// 2) Dynamic bodies
$bodyTemplates = [
    'gateAccess_logs' => "
        <p><strong>New Gate Access Event</strong></p>
        <p>User action: $message</p>
        <p>Time: $timestamp</p>
    ",
    'acControl_logs' => "
        <p><strong>Air Conditioning Change</strong></p>
        <p>Details: $message</p>
        <p>Time: $timestamp</p>
    ",
    'water_logs' => "
        <p><strong>Water System Update</strong></p>
        <p>Details: $message</p>
        <p>Time: $timestamp</p>
    ",
    'lighting_logs' => "
        <p><strong>Lighting Control Notification</strong></p>
        <p>Details: $message</p>
        <p>Time: $timestamp</p>
    ",
];
$bodyContent = $bodyTemplates[$systemName] 
    ?? "<p>$message</p><p>Time: $timestamp</p>";

// 3) Dynamic recipients
$recipients = [
    'gateAccess_logs'  => ['gate@rivaniot.online'],
    'acControl_logs'   => ['ac@rivaniot.online'],
    'water_logs'       => ['water@rivaniot.online'],
    'lighting_logs'    => ['lighting@rivaniot.online'],
];
// always include the superadmins as fallback
$toList = $recipients[$systemName] ?? [];
$toList[] = 'superadmin@rivaniot.online';
$toList[] = 'jpenarubia.a0001@rivaniot.online';

// Send mail
$mail = new PHPMailer(true);
try {
    // SMTP config
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'superadmin@rivaniot.online';
    $mail->Password   = 'superAdmin0507!';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // From
    $mail->setFrom('superadmin@rivaniot.online','Rivan IoT');

    // Add recipients
    foreach (array_unique($toList) as $addr) {
        $mail->addAddress($addr);
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = $fullSubject;
    $mail->Body    = <<<EOT
{$bodyContent}

<em>This is an automated message. Please do not reply to this email.</em>
<hr>

<table width="100%" cellpadding="0" cellspacing="0"
       style="background-color:#0D2153;color:#fff;padding:40px 20px;font-family:Arial,sans-serif;
              font-size:12px;line-height:1.4;">
  <tr>
    <td align="center">
      <img src="https://rivanit.com/assets/logo-DaYZ0U1G.png"
           alt="Rivan IoT Logo" width="80"
           style="display:block;margin:0 auto 10px;padding-top:40px;">
      <p style="margin:0 0 20px;">
        <strong>RivanCyber Training Institute</strong><br>
        Rivan IoT Notification System
      </p>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding-bottom:20px;">
      <a href="https://www.facebook.com/RivanInstitute"
         style="margin:0 5px;text-decoration:none;">
        <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png"
             width="24" alt="Facebook" style="display:inline-block;">
      </a>
      <a href="https://m.me/RivanInstitute"
         style="margin:0 5px;text-decoration:none;">
        <img src="https://cdn-icons-png.flaticon.com/512/2111/2111728.png"
             width="24" alt="Messenger" style="display:inline-block;">
      </a>
      <a href="https://www.instagram.com/rivancyberinstitute"
         style="margin:0 5px;text-decoration:none;">
        <img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png"
             width="24" alt="Instagram" style="display:inline-block;">
      </a>
    </td>
  </tr>
  <tr>
    <td style="padding-bottom:20px;text-align:center;">
      <p style="margin:5px 0;">Rivan Building, 18d Mola, Makati, 1200 Metro Manila</p>
      <p style="margin:5px 0;">
        <a href="mailto:teamrivan@rcvi.org" style="color:#fff;text-decoration:underline;">
          teamrivan@rcvi.org
        </a>
      </p>
      <p style="margin:5px 0;">
        <a href="tel:+639493760000" style="color:#fff;text-decoration:underline;">
          +63 949-376-0000
        </a>
      </p>
      <p style="margin:5px 0;">
        <a href="tel:+63284252848" style="color:#fff;text-decoration:underline;">
          +63 2-8425-2848
        </a>
      </p>
      <p style="margin:5px 0;">Mon–Fri 09:00 AM–05:00 PM</p>
    </td>
  </tr>
  <tr>
    <td align="center"
        style="border-top:1px solid #444;padding-top:10px;font-size:10px;color:#ccc;">
      © 2025 Rivan IoT. All rights reserved.
    </td>
  </tr>
</table>
EOT;

    $mail->send();

    // Log notification
    $stmt = $conn->prepare(
        "INSERT INTO sent_notifications (log_id, system_name, message)
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$logId, $systemName, $message]);

    echo json_encode(['status'=>'success','message'=>'Notification sent successfully']);
} catch (Exception $e) {
    echo json_encode([
      'status'=>'error',
      'message'=>"Mailer Error: {$mail->ErrorInfo}"
    ]);
}
