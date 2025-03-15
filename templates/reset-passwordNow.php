
<?php
session_start();
header("Content-Type: application/json");

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

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
                oninput="checkPasswordStrength(this.value); validateInput('password')">

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
            event.preventDefault(); // Prevent default form submission

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

            // Prepare data to be sent in the request
            const data = {
                password: password,
                retype_password: retypePassword
            };

            // Send the data using Fetch API (make sure the URL is correct)
            fetch('../scripts/change-passForgot.php', {
                method: 'POST', // Use POST method for sensitive data
                headers: {
                    'Content-Type': 'application/json', // Set the content type
                },
                body: JSON.stringify(data) // Convert the data to JSON format
            })
                .then(response => response.json()) // Parse the response as JSON
                .then(data => {
                    if (data.success) {
                        alert('Password changed successfully!');
                        // Redirect to login page after success
                        window.location.href = "../templates/login.php";
                    } else {
                        alert('Error changing password: ' + data.message);
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
    </script>
</body>

</html>