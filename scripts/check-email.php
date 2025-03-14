<?php
header("Content-Type: application/json"); // Ensure JSON response
include '../app/config/connection.php'; // Include PDO connection
require '../vendor/autoload.php'; // Adjust the path as needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging (Remove this in production)
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Get email from POST request
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
        // Generate a 5-digit OTP
        $otp = rand(10000, 99999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes")); // OTP expires in 10 mins

        // Optional: Store OTP in the database
        $updateStmt = $conn->prepare("UPDATE users SET otp_code = :otp, otp_expiry = :expiry WHERE email = :email");
        $updateStmt->bindParam(":otp", $otp, PDO::PARAM_INT);
        $updateStmt->bindParam(":expiry", $expiry, PDO::PARAM_STR);
        $updateStmt->bindParam(":email", $email, PDO::PARAM_STR);
        $updateStmt->execute();

        // Send OTP via email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'jproxxyv1@gmail.com'; // Your SMTP username
            $mail->Password = '!!!Newaccount@@@'; // Your SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('jproxxyv1@gmail.com', 'RivanIOT');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = "Your OTP for password reset is: <b>$otp</b>. This code expires in 10 minutes.";

            $mail->send();
            echo json_encode(["success" => true, "message" => "OTP sent successfully"]);
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
