<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/config/connection.php';

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                header("Location: ../templates/dashboard.php");
                exit();
            } else {
                $errorMessage = "Invalid username or password.";
            }
        } else {
            $errorMessage = "Account not found.";
        }
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        $errorMessage = "System error. Please try again later.";
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
    <style>
        .error {
            color: #dc3545;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            background-color: #f8d7da;
        }

        .password-strength {
            height: 4px;
            background: #eee;
            margin: 8px 0;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }
    </style>
</head>

<body>
    <div class="whiteBG">
        <div class="gridContainer">
            <div class="logInContainer" id="logInContainer">
                <form method="POST" action="">
                    <?php if (!empty($errorMessage)): ?>
                        <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                    <div class="box">
                        <h2 class="loginTitle">USER LOGIN</h2>
                        <div class="input-container">
                            <i class="fas fa-user user-icon"></i>
                            <input type="text" id="username" name="username" placeholder="Username" required
                                autocomplete="username" minlength="3" maxlength="30">
                        </div>
                        <div class="input-container">
                            <i class="fas fa-lock lock-icon"></i>
                            <input type="password" id="loginpassword" name="password" placeholder="Password" required
                                autocomplete="current-password" minlength="8">
                        </div>
                        <div class="showPasswordLabel">
                            <input type="checkbox" id="showLoginPassword" onclick="toggleLoginPassword()">
                            <label for="showLoginPassword">Show Password</label>
                        </div>

                        <div class="divider">
                            <span>OR</span>
                        </div>

                        <div id="g_id_onload"
                            data-client_id="460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com"
                            data-context="signin" data-ux_mode="popup" data-callback="handleCredentialResponse"
                            data-auto_prompt="false">
                        </div>

                        <div class="ContGoogle">
                            <div class="g_id_signin" data-type="standard" data-theme="icon" data-size="large"
                                data-shape="pill" data-text="signin_with">
                            </div>
                        </div>

                        <div class="input-container">
                            <button type="submit" class="loginButton">LOGIN</button>
                        </div>
                        <div class="link-container">
                            <a href="javascript:void(0);" onclick="toggleContainers()" class="createAcc">Create
                                Account</a>
                        </div>
                        <div class="link-container">
                            <a href="forgotPassword.php" class="forgotPass">Forgot Password?</a>
                        </div>
                    </div>



                </form>
            </div>

            <div class="signUpContainer" id="signUpContainer" style="display: none;">
                <form action="../classes/register.php" method="POST" id="signupForm">
                    <input type="hidden" name="csrf_token" value="<?php echo bin2hex(random_bytes(32)); ?>">
                    <div class="boxTwo">
                        <h2 class="signUpTitle">USER SIGN UP</h2>
                        <div class="inputsign-container">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Username" required
                                pattern="[a-zA-Z0-9_]{3,20}" title="3-20 characters (letters, numbers, underscores)">
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Email" required
                                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$">
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="phoneNumber" placeholder="Phone Number" required pattern="[0-9]{10}"
                                title="10-digit phone number">
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Password" required
                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                title="Must contain: 8+ characters, 1 uppercase, 1 lowercase, 1 number"
                                oninput="checkPasswordStrength(this.value)">
                            <div class="password-strength">
                                <div class="strength-bar"></div>
                            </div>
                            <div class="eyePosition">
                                <i class="fas fa-eye password-eye-icon"
                                    onclick="togglePasswordVisibility('password', this)"></i>
                            </div>
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="retype_password" name="retype_password"
                                placeholder="Retype Password" required oninput="validatePasswordMatch()">
                            <div class="eyePosition">
                                <i class="fas fa-eye password-eye-icon"
                                    onclick="togglePasswordVisibility('retype_password', this)"></i>
                            </div>
                            <span class="password-match-error" style="display:none;color:red;font-size:0.9em;">
                                Passwords do not match
                            </span>
                        </div>
                        <button type="submit" class="signupButton">SIGN UP</button>
                        <div class="link-container">
                            <a href="javascript:void(0);" onclick="toggleContainers()" class="backToLogin">Log In
                                Account</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="frontImg"></div>
        </div>
    </div>

    <script>
        function toggleContainers() {
            const login = document.getElementById('logInContainer');
            const signup = document.getElementById('signUpContainer');
            login.style.display = login.style.display === 'none' ? 'flex' : 'none';
            signup.style.display = signup.style.display === 'none' ? 'flex' : 'none';
        }

        function checkPasswordStrength(password) {
            const strengthBar = document.querySelector('.strength-bar');
            const hasNumber = /\d/.test(password);
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const strength = Math.min((
                (password.length >= 8 ? 25 : 0) +
                (hasNumber ? 25 : 0) +
                (hasUpper ? 25 : 0) +
                (hasLower ? 25 : 0)
            ), 100);

            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor =
                strength >= 75 ? '#28a745' :
                    strength >= 50 ? '#ffc107' :
                        '#dc3545';
        }

        function validatePasswordMatch() {
            const password = document.getElementById('password').value;
            const retype = document.getElementById('retype_password').value;
            const errorSpan = document.querySelector('.password-match-error');
            errorSpan.style.display = (password && retype && password !== retype) ? 'block' : 'none';
        }

        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye-slash');
        }

        document.getElementById('showLoginPassword').addEventListener('change', function () {
            const passwordField = document.getElementById('loginpassword');
            passwordField.type = this.checked ? 'text' : 'password';
        });

        document.getElementById('signupForm').addEventListener('submit', function (e) {
            const password = document.getElementById('password').value;
            const retype = document.getElementById('retype_password').value;
            if (password !== retype) {
                e.preventDefault();
                alert('Error: Passwords do not match!');
                document.getElementById('retype_password').focus();
            }
        });

        function handleCredentialResponse(response) {
            const jwt = response.credential;

            try {
                // Decode JWT safely
                const decoded = JSON.parse(atob(jwt.split('.')[1]));
                console.log("Decoded Google Sign-In Data:", decoded); // Log to see all data

                // Extract relevant fields
                const userInfo = {
                    email: decoded.email || '',
                    first_name: decoded.given_name || '', // First name
                    last_name: decoded.family_name || '', // Last name
                    profile_picture: decoded.picture || '', // Profile image URL
                    sub: decoded.sub || '', // Unique Google ID
                    locale: decoded.locale || '', // User's locale (e.g., "en-US")
                };

                console.log("Extracted User Info:", userInfo);

                // Send token to backend for authentication
                fetch('../scripts/google-auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: jwt })
                })
                    .then(async response => {
                        if (!response.ok) throw await response.json();
                        return response.json();
                    })
                    .then(data => {
                        if (data.redirect) {
                            window.location.href = data.redirect; // Redirect if needed
                        }
                    })
                    .catch(error => {
                        console.error("Authentication Error:", error);
                        showError(error.message);
                    });

                // **Send full user data to store in the database**
                fetch('../scripts/google-store-user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(userInfo) // Send all user details
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log("User stored:", data);
                    })
                    .catch(error => {
                        console.error("Error storing user:", error);
                    });

            } catch (err) {
                console.error("Error decoding JWT:", err);
            }
        }



        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.innerHTML = `
        <strong>Authentication Error:</strong><br>
        ${message || 'Unknown error occurred'}
    `;
            document.querySelector('.logInContainer').prepend(errorDiv);
        }


    </script>
</body>

</html>