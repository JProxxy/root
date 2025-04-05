<?php
session_start();
header("Content-Type: application/json");

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include '../app/config/connection.php';  // Include database connection
require '../vendor/autoload.php';  // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get email from request
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit();
}

try {
    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $otp = rand(10000, 99999); // Generate 5-digit OTP
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Save OTP in database
        $updateStmt = $conn->prepare("UPDATE users SET otp_code = :otp, otp_expiry = :expiry WHERE email = :email");
        $updateStmt->bindParam(":otp", $otp, PDO::PARAM_INT);
        $updateStmt->bindParam(":expiry", $expiry, PDO::PARAM_STR);
        $updateStmt->bindParam(":email", $email, PDO::PARAM_STR);
        $updateStmt->execute();

        // Store email in session for OTP verification
        $_SESSION['reset_email'] = $email;

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jpenarubia.a0001@rivaniot.online';  // Replace with your Gmail
            $mail->Password = 'ExcelAltH0103!'; // Use an App Password (NOT your real password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('jproxxyv1@gmail.com', 'RivanIOT');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Your OTP for password reset is: <b>$otp</b>. This code expires in 10 minutes.";

            if ($mail->send()) {
                echo json_encode(["success" => true, "message" => "OTP sent successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to send OTP"]);
            }
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No account found with this email"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
