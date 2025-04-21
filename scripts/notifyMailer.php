<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
include '../app/config/connection.php';

$inputData = json_decode(file_get_contents('php://input'), true);
file_put_contents('php://stderr', "Received Payload: " . print_r($inputData, true) . "\n");

// Ensure input is always an array of events
$events = is_array($inputData) && isset($inputData[0]) ? $inputData : [$inputData];

foreach ($events as $event) {
    $logId      = isset($event['log_id'])      ? (int) $event['log_id'] : 0;
    $systemName = isset($event['system_name']) ? $event['system_name']  : '';
    $message    = isset($event['message'])     ? $event['message']      : '';
    $timestamp  = isset($event['timestamp'])   ? $event['timestamp']    : '';

    if (!$systemName || !$message) {
        continue;
    }

    // Dynamic subject
    $subjects = [
        'gateAccess_logs' => 'Access Gate Information',
        'acControl_logs'  => 'Air Conditioning Update',
        'water_logs'      => 'Water System Log',
        'lighting_logs'   => 'Lighting Activity',
    ];
    $base = $subjects[$systemName] ?? 'System Activity Notification';
    $fullSubject = sprintf("%s - New Event @ %s", $base, date("h:i A", strtotime($timestamp)));

    // Extract user/action
    preg_match('/^(.+?)\s(opened the gate|was denied access)/i', $message, $m);
    if (isset($m[1], $m[2])) {
        $userPart   = $m[1];
        $actionPart = ucfirst($m[2]);
    } else {
        $userPart   = 'Unknown';
        $actionPart = $message;
    }

    // Recipients
    $toList = [
        'superadmin@rivaniot.online',
        'jpenarubia.a0001@rivaniot.online',
    ];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'superadmin@rivaniot.online';
        $mail->Password   = 'superAdmin0507!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('superadmin@rivaniot.online', 'Rivan IoT');
        foreach (array_unique($toList) as $addr) {
            $mail->addAddress($addr);
        }

        $mail->isHTML(true);
        $mail->Subject = $fullSubject;
        $mail->Body    = <<<EOT
<p><strong>User:</strong> $userPart</p>
<p><strong>Action:</strong> $actionPart</p>
<p><strong>Time:</strong> $timestamp</p>

<p><em>This is an automated message. Please do not reply to this email.</em></p>
<hr>

<table width="100%" cellpadding="0" cellspacing="0"
       style="background-color:#0D2153;color:#fff;padding:20px;font-family:Arial,sans-serif;
              font-size:12px;line-height:1.4;">
  <tr>
    <td align="center" style="padding-top:40px;">
      <img src="https://rivanit.com/assets/logo-DaYZ0U1G.png"
           alt="Rivan IoT Logo" width="80"
           style="display:block;margin:0 auto 10px;">
      <p style="margin:0 0 20px;">
        <strong>RivanCyber Training Institute</strong><br>
        Rivan IoT Notification System
      </p>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding-bottom:20px;">
      <a href="https://www.facebook.com/RivanInstitute" style="margin:0 5px;">
        <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png"
             width="24" alt="Facebook">
      </a>
      <a href="https://m.me/RivanInstitute" style="margin:0 5px;">
        <img src="https://cdn-icons-png.flaticon.com/512/2111/2111728.png"
             width="24" alt="Messenger">
      </a>
      <a href="https://www.instagram.com/rivancyberinstitute" style="margin:0 5px;">
        <img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png"
             width="24" alt="Instagram">
      </a>
    </td>
  </tr>
  <tr>
    <td style="padding-bottom:20px;text-align:center;">
      <p style="margin:5px 0;">Rivan Building, 18d Mola, Makati, 1200 Metro Manila</p>
      <p style="margin:5px 0;">
        <a href="mailto:teamrivan@rcvi.org" style="color:#fff;">teamrivan@rcvi.org</a>
      </p>
      <p style="margin:5px 0;">
        <a href="tel:+639493760000" style="color:#fff;">+63 949-376-0000</a>
      </p>
      <p style="margin:5px 0;">
        <a href="tel:+63284252848" style="color:#fff;">+63 2-8425-2848</a>
      </p>
      <p style="margin:5px 0;">Mon to Fri from 09:00 AM to 05:00 PM</p>
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

        // Log successful notification
        $stmt = $conn->prepare("
          INSERT INTO sent_notifications (log_id, system_name, message)
          VALUES (?, ?, ?)
        ");
        $stmt->execute([$logId, $systemName, $message]);

        echo json_encode(['status' => 'success', 'message' => "Notification sent for log_id: $logId"]);
    } catch (Exception $e) {
        echo json_encode([
            'status'  => 'error',
            'log_id'  => $logId,
            'message' => "Mailer Error: {$mail->ErrorInfo}"
        ]);
    }
}
