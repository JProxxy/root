<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/config/connection.php';

// Traditional login handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../templates/dashboard.php");
            exit();
        } else {
            $errorMessage = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $errorMessage = "An error occurred. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/signup.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="whiteBG">
        <div class="gridContainer">
            <div class="logInContainer" id="logInContainer">
                <form method="POST" action="">
                    <?php if (isset($errorMessage)): ?>
                        <div class="error"><?php echo $errorMessage; ?></div>
                    <?php endif; ?>
                    <div class="box">
                        <h2 class="loginTitle">USER LOGIN</h2>
                        <div class="input-container">
                            <i class="fas fa-user user-icon"></i>
                            <input type="text" id="username" name="username" placeholder="Username" required>
                        </div>
                        <div class="input-container">
                            <i class="fas fa-lock lock-icon"></i>
                            <input type="password" id="loginpassword" name="password" placeholder="Password" required>
                        </div>
                        <div class="showPasswordLabel">
                            <input type="checkbox" id="showLoginPassword" onclick="toggleLoginPassword()">
                            <label for="showLoginPassword">Show Password</label>
                        </div>
                        <div class="input-container">
                            <button type="submit" class="loginButton">LOGIN</button>
                        </div>
                        <div class="link-container">
                            <a href="javascript:void(0);" onclick="toggleContainers()" class="createAcc">Create Account</a>
                        </div>
                        <div class="link-container">
                            <a href="forgotPassword.php" class="forgotPass">Forgot Password?</a>
                        </div>
                    </div>

                    <!-- Google Sign-In -->
                    <div id="g_id_onload"
                        data-client_id="460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com"
                        data-context="signin"
                        data-ux_mode="popup"
                        data-callback="handleCredentialResponse"
                        data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin" data-type="standard"></div>
                </form>
            </div>

            <div class="signUpContainer" id="signUpContainer" style="display: none;">
                <form action="../classes/register.php" method="POST">
                    <div class="boxTwo">
                        <h2 class="signUpTitle">USER SIGN UP</h2>
                        <div class="inputsign-container">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-phone"></i>
                            <input type="text" name="phoneNumber" placeholder="Phone Number" required>
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Password" required autocomplete="off">
                            <div class="eyePosition">
                                <i class="fas fa-eye password-eye-icon" onclick="togglePasswordVisibility('password', this)"></i>
                            </div>
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="retype_password" name="retype_password" placeholder="Retype Password" required autocomplete="off">
                            <div class="eyePosition">
                                <i class="fas fa-eye password-eye-icon" onclick="togglePasswordVisibility('retype_password', this)"></i>
                            </div>
                        </div>
                        <button type="submit" class="signupButton">SIGN UP</button>
                        <div class="link-container">
                            <a href="javascript:void(0);" onclick="toggleContainers()" class="backToLogin">Log In Account</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="frontImg"></div>
        </div>
    </div>

    <script>
        // Container Toggle
        function toggleContainers() {
            const login = document.getElementById('logInContainer');
            const signup = document.getElementById('signUpContainer');
            [login, signup].forEach(el => el.style.display = 
                el.style.display === 'none' ? 'flex' : 'none');
        }

        // Password Visibility
        function toggleVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye-slash');
        }

        // Google Sign-In Handler
        function handleCredentialResponse(response) {
            fetch('../scripts/google-auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: response.credential })
            })
            .then(async res => {
                const data = await res.json();
                if (data.redirect) window.location.href = data.redirect;
                if (data.error) throw new Error(data.error);
            })
            .catch(error => {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error';
                errorDiv.textContent = error.message;
                document.querySelector('.logInContainer').prepend(errorDiv);
            });
        }

        // Login Password Toggle
        document.getElementById('showLoginPassword').addEventListener('change', function() {
            const passwordField = document.getElementById('loginpassword');
            passwordField.type = this.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>