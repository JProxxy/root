<?php
// Start session first, but prevent output issues
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");
header("Cross-Origin-Embedder-Policy: credentialless");

// Allow OPTIONS method for preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
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
            $hashedPassword = $user['password'];
            $failedAttempts = $user['failed_attempts'];
            $lockUntil = $user['lock_until'];

            // Check if the account is locked
            if ($lockUntil && strtotime($lockUntil) > time()) {
                $remainingTime = strtotime($lockUntil) - time();

                // Prevent caching and handle redirect
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Pragma: no-cache");

                // JavaScript alert before redirect
                echo "<script>alert('Account is locked. Try again after $remainingTime seconds.'); window.location.href = '../templates/login.php';</script>";
                exit(); // Ensure no further code execution
            }



            // Check password (if user has no Google ID)
            if (!empty($user['google_id']) || (!empty($hashedPassword) && password_verify($password, $hashedPassword))) {
                // Reset failed attempts on successful login
                $stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE username = :username");
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

                header("Location: ../templates/dashboard.php");
                exit();
            } else {
                // Increase failed attempt count
                $failedAttempts++;

                if ($failedAttempts >= 3) {
                    // Lock account for 30 seconds
                    $stmt = $conn->prepare("UPDATE users SET failed_attempts = :failed_attempts, lock_until = NOW() + INTERVAL 30 SECOND WHERE username = :username");
                } else {
                    $stmt = $conn->prepare("UPDATE users SET failed_attempts = :failed_attempts WHERE username = :username");
                }

                $stmt->bindParam(':failed_attempts', $failedAttempts, PDO::PARAM_INT);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();

                $remainingAttempts = 3 - $failedAttempts;
                $errorMessage = "Invalid password. " . ($remainingAttempts > 0 ? "$remainingAttempts attempts left." : "Your account is locked for 30 seconds.");
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
    <script src="https://cdn.jsdelivr.net/npm/jwt-decode@3.1.2/build/jwt-decode.min.js"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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

                        <script>
                            function handleCredentialResponse(response) {
                                const token = response.credential;
                                console.log("Google Token:", token);

                                // Send token and other user info to google-auth.php via Fetch API
                                fetch('../scripts/google-auth.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        token: token,
                                        google_id: '', // Optionally, you may pass this if available; google-auth.php will verify via the token payload.
                                        email: '',      // Optional; can be derived from the token.
                                        first_name: '',
                                        last_name: '',
                                        profile_picture: ''
                                    })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            console.log("Login Successful:", data);
                                            window.location.href = "templates/dashboard.php"; // Redirect to dashboard
                                        } else {
                                            console.error("Login Failed:", data.message);
                                            alert("Google authentication failed: " + data.message);
                                        }
                                    })
                                    .catch(error => console.error("Error:", error));
                            }
                        </script>
                        <div class="input-container">
                            <button type="submit" class="loginButton">LOGIN</button>
                        </div>
                        <div class="link-container">
                            <a href="javascript:void(0);" onclick="toggleContainers()" class="createAcc">Create
                                Account</a>
                        </div>
                        <div class="link-container">
                            <a href="../templates/forgot-password.php" class="forgotPass">Forgot
                                Password?</a>
                        </div>

                    </div>



                </form>
            </div>

            <div class="signUpContainer" id="signUpContainer" style="display: none;">
                <form action="../classes/register.php" method="POST" id="signupForm">
                    <input type="hidden" name="csrf_token" value="<?php echo bin2hex(random_bytes(32)); ?>">
                    <input type="hidden" name="recaptcha_response" id="recaptchaResponse"> <!-- reCAPTCHA Token -->

                    <div class="boxTwo">
                        <h2 class="signUpTitle">USER SIGN UP</h2>

                        <!-- Username Field -->
                        <div class="inputsign-container">
                            <i class="fas fa-user"></i>
                            <input type="text" id="SignUpUsername" name="SignUpUsername" placeholder="Username" required
                                pattern="[a-zA-Z0-9_]{3,20}" title="3-20 characters (letters, numbers, underscores)"
                                oninput="validateInput('SignUpUsername')">
                            <span class="error-message SignUpUsername-error">Invalid username</span>
                        </div>

                        <!-- Email Field -->
                        <div class="inputsign-container">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Email" required
                                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" oninput="validateInput('email')">
                            <span class="error-message email-error">Invalid email format</span>
                        </div>

                        <!-- Phone Number Field -->
                        <div class="inputsign-container">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="Phone Number" required
                                pattern="[0-9]{11}" title="11-digit phone number"
                                oninput="validateInput('phoneNumber')">
                            <span class="error-message phone-error">Enter an 11-digit phone number</span>
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
                                <i class="fas fa-eye password-eye-icon"
                                    onclick="togglePasswordVisibility('password', this)"></i>
                            </div>
                        </div>


                        <!-- Retype Password Field -->
                        <div class="inputsign-container">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="retype_password" name="retype_password"
                                placeholder="Retype Password" required oninput="validatePasswordMatch()">
                            <div class="eyePosition">
                                <i class="fas fa-eye password-eye-icon"
                                    onclick="togglePasswordVisibility('retype_password', this)"></i>
                            </div>
                            <span class="password-match-error">Passwords do not match</span>
                        </div>

                        <button type="submit" class="signupButton">SIGN UP</button>

                        <div class="link-container">
                            <a href="javascript:void(0);" onclick="toggleContainers()" class="backToLogin">Log In
                                Account</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Include Google's reCAPTCHA v3 script -->
            <script
                src="https://www.google.com/recaptcha/api.js?render=6LcWnvEqAAAAACO3p_9pzADJIiKwPMYXiQFBRXij"></script>

            <script>
                document.getElementById('signupForm').addEventListener('submit', function (event) {
                    event.preventDefault(); // Prevent form from submitting immediately

                    grecaptcha.ready(function () {
                        grecaptcha.execute('6LcWnvEqAAAAACO3p_9pzADJIiKwPMYXiQFBRXij', { action: 'signup' }).then(function (token) {
                            document.getElementById('recaptchaResponse').value = token;
                            document.getElementById('signupForm').submit(); // Submit after token is set
                        });
                    });
                });
            </script>

            <div class="frontImg"></div>
        </div>
    </div>


</body>



<script>
    function toggleContainers() {
        const login = document.getElementById('logInContainer');
        const signup = document.getElementById('signUpContainer');
        login.style.display = login.style.display === 'none' ? 'flex' : 'none';
        signup.style.display = signup.style.display === 'none' ? 'flex' : 'none';

    }



    // Validate Password and Retype Password Match
    function validatePassword() {
        const password = document.getElementById("password").value;
        const retype = document.getElementById("retype").value;
        const errorSpan = document.getElementById("passwordError");

        errorSpan.style.display = password && retype && password !== retype ? "block" : "none";
    }

    // Toggle Password Visibility
    function togglePasswordVisibility(inputId, icon) {
        const input = document.getElementById(inputId);
        const isPassword = input.type === "password";

        input.type = isPassword ? "text" : "password";
        icon.classList.toggle("fa-eye-slash", isPassword);
        icon.classList.toggle("fa-eye", !isPassword);
    }

    // Show/Hide Login Password
    function toggleLoginPassword() {
        const passwordInput = document.getElementById("loginpassword");
        passwordInput.type = passwordInput.type === "password" ? "text" : "password";
    }


</script>

</html>


<script>
    // Real-time validation for Username
    document.querySelector('input[name="SignUpUsername"]').addEventListener('input', function () {
        const SignUpUsername = this.value;
        const regex = /^[a-zA-Z0-9]{3,12}$/; // Only letters and numbers, 3-12 characters
        const errorSpan = this.nextElementSibling;

        if (!regex.test(SignUpUsername)) {
            errorSpan.textContent = "Invalid username. Only letters & numbers (3-12 characters).";
            errorSpan.style.display = "block";
        } else {
            errorSpan.style.display = "none";
        }
    });

    // Real-time validation for Email
    document.querySelector('input[name="email"]').addEventListener('input', function () {
        const email = this.value;
        const regex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/; // Standard email pattern
        const errorSpan = this.nextElementSibling;

        if (!regex.test(email)) {
            errorSpan.textContent = "Invalid email format.";
            errorSpan.style.display = "block";
        } else {
            errorSpan.style.display = "none";
        }
    });

    // Real-time validation for Phone Number
    document.querySelector('input[name="phoneNumber"]').addEventListener('input', function () {
        const phone = this.value;
        const regex = /^[0-9]{11}$/; // Must be 11-digit numeric
        const errorSpan = this.nextElementSibling;

        if (!regex.test(phone)) {
            errorSpan.textContent = "Invalid phone number. Must be 11 digits.";
            errorSpan.style.display = "block";
        } else {
            errorSpan.style.display = "none";
        }
    });


    // Real-time validation for Password
    document.getElementById('password').addEventListener('input', function () {
        checkPasswordStrength(this.value);
        validatePasswordMatch();
    });

    // Password Strength Checker
    function checkPasswordStrength(password) {
        const strengthBar = document.querySelector('.strength-bar');
        const errorSpan = document.querySelector('.password-strength-message');

        const hasNumber = /\d/.test(password);
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);

        const strength = Math.min(
            (password.length >= 8 ? 25 : 0) +
            (hasNumber ? 25 : 0) +
            (hasUpper ? 25 : 0) +
            (hasLower ? 25 : 0), 100
        );

        // Update strength bar width and color
        strengthBar.style.width = strength + '%';
        strengthBar.style.backgroundColor =
            strength >= 75 ? '#28a745' : // Green (Strong)
                strength >= 50 ? '#ffc107' : // Yellow (Medium)
                    '#dc3545'; // Red (Weak)

        // Show/hide error message based on strength
        if (errorSpan) {
            errorSpan.textContent = strength < 50 ? "Weak password" : "";
            errorSpan.style.display = strength < 50 ? "block" : "none";
        }
    }


    // Validate Password Match
    function validatePasswordMatch() {
        const password = document.getElementById('password').value;
        const retype = document.getElementById('retype_password').value;
        const errorSpan = document.querySelector('.password-match-error');

        if (password && retype && password !== retype) {
            errorSpan.textContent = "Passwords do not match!";
            errorSpan.style.display = "block";
        } else {
            errorSpan.style.display = "none";
        }
    }

    // Show/Hide Password
    function togglePasswordVisibility(inputId, icon) {
        const input = document.getElementById(inputId);
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('fa-eye-slash');
    }
</script>