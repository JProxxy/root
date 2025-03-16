<?php
session_start();



if (!isset($_SESSION['reset_email'])) {
    header("Location: ../templates/forgot-password.php"); // Redirect if email is missing
    exit();
}

$email = $_SESSION['reset_email']; // Get email


include '../app/config/connection.php';  // Include database connection
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password?</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/forgot-password.css">
</head>

<body>
    <img class="backgroundImageForgot" src="../assets/images/bg-forgotpass.png" alt="Forgot Password Background">

    <div class="leftCont">
        <div class="spillOne">
            SET YOUR PASSWORD
        </div>
        <div class="spillTwo">
            Ensure your password has uppercase and lowercase letters, is at least 12 characters long, and uses only
            letters, numbers, and common punctuation.
        </div>

        <!-- Password Field -->
        <div class="inputsign-container">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Password" required
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                title="Must contain: 8+ characters, 1 uppercase, 1 lowercase, 1 number"
                oninput="checkPasswordStrength(this.value);">

            <!-- Strength Bar -->
            <div class="password-strength">
                <div class="strength-bar"></div>
            </div>

            <!-- Error Message -->
            <span class="password-strength-message error-message" style="display: none;">
                Weak password
            </span>

            <!-- Show/Hide Password Icon -->
            <div class="eyePosition">
                <i class="fas fa-eye password-eye-icon" onclick="togglePasswordVisibility('password', this)"></i>
            </div>
        </div>

        <!-- Retype Password Field -->
        <div class="inputsign-container">
            <i class="fas fa-lock"></i>
            <input type="password" id="retype_password" name="retype_password" placeholder="Retype Password" required
                oninput="validatePasswordMatch()">
            <div class="eyePosition">
                <i class="fas fa-eye password-eye-icon" onclick="togglePasswordVisibility('retype_password', this)"></i>
            </div>
            <span class="password-match-error">Passwords do not match</span>
        </div>

        <button type="submit" class="forgotPassButton" id="changePass">Change Password</button>
    </div>

    <script>
        // Toggle the visibility of the password
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            const type = input.type === "password" ? "text" : "password";
            input.type = type;
            icon.classList.toggle("fa-eye-slash"); // Toggle the icon between 'eye' and 'eye-slash'
        }

        document.getElementById('changePass').addEventListener('click', function (event) {
            event.preventDefault(); // Prevent form submission

            const password = document.getElementById('password').value;
            const retypePassword = document.getElementById('retype_password').value;

            // Check if passwords match
            if (password !== retypePassword) {
                alert('Passwords do not match. Please try again.');
                return;
            }

            // Validate password format
            if (!validatePasswordFormat(password)) {
                return; // If password is invalid, stop further actions
            }

            // Prepare data to be sent
            const data = {
                password: password,
                retype_password: retypePassword
            };

            // Send data using Fetch API
            fetch('../scripts/change-passForgot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Ensure correct content type
                },
                body: JSON.stringify(data) // Convert data to JSON
            })
                .then(response => response.json()) // Parse response as JSON
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        window.location.href = "../templates/login.php"; // Redirect to login on success
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
        });

        // Validate password format
        function validatePasswordFormat(password) {
            const minLength = 12;
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{12,}$/;

            if (!regex.test(password)) {
                alert('Password must be at least 12 characters long, include uppercase and lowercase letters, and use only letters, numbers, and common punctuation.');
                return false;
            }
            return true;
        }

        // Validate password format
        function validatePasswordFormat(password) {
            const minLength = 12;
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{12,}$/;

            if (!regex.test(password)) {
                alert('Password must be at least 12 characters long, include uppercase and lowercase letters, and use only letters, numbers, and common punctuation.');
                return false;
            }
            return true;
        }

        // Function to check password strength
        function checkPasswordStrength(password) {
            const strengthBar = document.querySelector(".strength-bar");
            const message = document.querySelector(".password-strength-message");

            let strength = 0;

            // Criteria for strength evaluation
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

            // Update strength bar based on strength
            strengthBar.style.width = (strength * 20) + "%";

            if (strength < 3) {
                message.textContent = "Weak password";
                message.style.color = "red";
            } else if (strength < 4) {
                message.textContent = "Moderate password";
                message.style.color = "orange";
            } else {
                message.textContent = "Strong password";
                message.style.color = "green";
            }

            message.style.display = "block"; // Show message
        }

    </script>
</body>

</html>