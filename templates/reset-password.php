<?php
session_start();
if (!isset($_SESSION['reset_email'])) {
    header("Location: ../templates/forgot-password.php"); // Redirect if email is missing
    exit();
}

$email = $_SESSION['reset_email']; // Get email

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/forgot-password.css">
    <style>
        .otp-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            margin: 5px;
            border: 2px solid #333;
            border-radius: 5px;
        }

        .verify-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .verify-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <img class="backgroundImageForgot" src="../assets/images/bg-forgotpass.png" alt="Forgot Password Background">

    <div class="leftCont">
        <div class="spillOne">
            HAVING TROUBLE REMEMBERING YOUR PASSWORD?
        </div>
        <div class="spillTwo">
            We will send a secure code to your email to enhance your privacy and security.
        </div>

        <!-- OTP Input Fields -->
        <div class="otp-container">
            <input type="text" class="otp-input" maxlength="1">
            <input type="text" class="otp-input" maxlength="1">
            <input type="text" class="otp-input" maxlength="1">
            <input type="text" class="otp-input" maxlength="1">
            <input type="text" class="otp-input" maxlength="1">
        </div>

        <!-- Verify Button -->
        <button class="verify-btn" onclick="verifyOTP()">Verify OTP</button>

        <p id="message" style="text-align: center; color: red;"></p>
    </div>

    <script>
        // Auto-focus to the next input
        const inputs = document.querySelectorAll(".otp-input");

        inputs.forEach((input, index) => {
            input.addEventListener("input", (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener("keydown", (e) => {
                if (e.key === "Backspace" && index > 0 && !e.target.value) {
                    inputs[index - 1].focus();
                }
            });
        });

        function verifyOTP() {
            let enteredOTP = "";
            inputs.forEach(input => enteredOTP += input.value);

            if (enteredOTP.length !== 5) {
                document.getElementById("message").textContent = "Please enter a complete OTP.";
                return;
            }

            fetch("../scripts/verify-otp.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "otp=" + encodeURIComponent(enteredOTP)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("OTP verified successfully! Redirecting to login page...");
                        window.location.href = "../templates/login.php";
                    } else {
                        document.getElementById("message").textContent = "Invalid OTP. Please try again.";
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    document.getElementById("message").textContent = "An error occurred. Please try again.";
                });
        }
    </script>
</body>

</html>