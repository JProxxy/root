<?php
header("Content-Type: application/json");
include '../app/config/connection.php'; 
require '../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set("display_errors", 1);

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $otp = rand(10000, 99999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $updateStmt = $conn->prepare("UPDATE users SET otp_code = :otp, otp_expiry = :expiry WHERE email = :email");
        $updateStmt->bindParam(":otp", $otp, PDO::PARAM_INT);
        $updateStmt->bindParam(":expiry", $expiry, PDO::PARAM_STR);
        $updateStmt->bindParam(":email", $email, PDO::PARAM_STR);
        $updateStmt->execute();

        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->SMTPDebug = 0; // Debugging enabled
            $mail->Debugoutput = 'html'; // Debug output format
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jproxxyv1@gmail.com';  
            $mail->Password = 'cohw zplj ztag sifm'; // Use an App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('jproxxyv1@gmail.com', 'RivanIOT');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = "Your OTP for password reset is: <b>$otp</b>. This code expires in 10 minutes.";

            if ($mail->send()) {
                echo json_encode(["success" => true, "message" => "OTP sent successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Email sending failed"]);
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
