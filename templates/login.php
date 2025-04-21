<?php
// Start session first, but prevent output issues
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: text/html; charset=UTF-8");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");
header("Cross-Origin-Embedder-Policy: credentialless");
header("Cross-Origin-Resource-Policy: cross-origin");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../app/config/connection.php';

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['identifier'])) {
    $identifier = trim($_POST['identifier']);
    $password = trim($_POST['password']);

    try {
        $identifier = trim($_POST['identifier']);

        // 2) QUERY EITHER COLUMN
        $stmt = $conn->prepare(
            "SELECT * FROM users 
             WHERE username = :ident 
                OR email    = :ident
             LIMIT 1"
        );
        $stmt->bindParam(':ident', $identifier, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if the user's role is allowed (role_id must equal 1)
            if ($user['role_id'] != 3 && $user['role_id'] != 4) {
                // Display alert and redirect if role is not permitted
                echo "<script>
                    alert('Access denied. Your role does not allow login through this portal.');
                    window.location.href = '../templates/login.php';
                </script>";
                exit();
            }

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
            if ((!empty($hashedPassword) && password_verify($password, $hashedPassword)) || (!empty($user['google_id']) && empty($hashedPassword))) {
                // Reset failed attempts on successful login
                // On successful login:
                $stmt = $conn->prepare("
UPDATE users 
SET failed_attempts = 0, lock_until = NULL 
WHERE user_id = :uid
");
                $stmt->bindParam(':uid', $user['user_id'], PDO::PARAM_INT);
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
                    $stmt = $conn->prepare("
                      UPDATE users 
                      SET failed_attempts = :fa, lock_until = NOW() + INTERVAL 30 SECOND 
                      WHERE user_id = :uid
                    ");
                } else {
                    $stmt = $conn->prepare("
                      UPDATE users 
                      SET failed_attempts = :fa 
                      WHERE user_id = :uid
                    ");
                }
                $stmt->bindParam(':fa', $failedAttempts, PDO::PARAM_INT);
                $stmt->bindParam(':uid', $user['user_id'], PDO::PARAM_INT);
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

                    <div class="toggle-container">
                        <!-- Radio buttons (hidden) -->
                        <input type="radio" name="toggle" id="user" checked>
                        <input type="radio" name="toggle" id="admin">

                        <!-- The pill toggle itself -->
                        <div class="toggle-pill">
                            <!-- Labels that act like buttons -->
                            <label for="user" class="option">User</label>
                            <label for="admin" class="option">Admin</label>
                            <!-- The slider that moves between options -->
                            <span class="slider"></span>
                        </div>
                    </div>

                    <!-- User Login Container -->
                    <div class="box">
                        <div class="input-container">
                            <i class="fas fa-user user-icon"></i>
                            <input type="text" id="identifier" name="identifier" placeholder="Username or Email"
                                required>
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
                        <div class="input-container">
                            <button type="submit" class="loginButton">LOGIN</button>
                        </div>
                        <!-- <div class="link-container">
                            <a href="javascript:void(0);" onclick="toggleContainers()" class="createAcc">Create
                                Account</a>
                        </div> -->
                        <div class="link-container">
                            <a href="../templates/forgot-password.php" class="forgotPass">Forgot Password?</a>
                        </div>
                    </div>

                    <!-- Admin Login Container -->
                    <div class="admin-box hidden">
                        <div class="input-container admin-input-container">
                            <i class="fas fa-envelope icon-inside"></i>
                            <input type="email" id="adminEmailVer" name="adminEmailVer" class="adminEmailVer"
                                placeholder="Admin Email">
                            <i class="fas fa-arrow-right arrow-button"></i>
                        </div>
                    </div>

                    <script>
                        document.querySelector(".arrow-button").addEventListener("click", function () {
                            let emailInput = document.querySelector("#adminEmailVer").value;

                            fetch("../scripts/admin_verification.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                body: `adminEmailVer=${encodeURIComponent(emailInput)}`
                            })
                                .then(response => response.json())
                                .then(data => {
                                    alert(data.message); // Show success or error message
                                })
                                .catch(error => console.error("Error:", error));
                        });

                    </script>


                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.getElementById('user').addEventListener('change', toggleLoginType);
                        document.getElementById('admin').addEventListener('change', toggleLoginType);

                        function toggleLoginType() {
                            if (document.getElementById('admin').checked) {
                                document.querySelector('.box').classList.add('hidden');
                                document.querySelector('.admin-box').classList.remove('hidden');
                            } else {
                                document.querySelector('.box').classList.remove('hidden');
                                document.querySelector('.admin-box').classList.add('hidden');
                            }
                        }

                        // Optionally, run on page load to ensure correct container is shown
                        toggleLoginType();
                    });

                </script>
            </div>

            <div class="signUpContainer" id="signUpContainer" style="display: none;">
                <form action="../classes/register.php" method="POST" id="signupForm">
                    <input type="hidden" name="csrf_token" value="<?php echo bin2hex(random_bytes(32)); ?>">
                    <input type="hidden" name="recaptcha_response" id="recaptchaResponse"> <!-- reCAPTCHA Token -->

                    <div class="boxTwo">
                        <h2 class="signUpTitle">CREATE USER ACCOUNT</h2>

                        <!-- Email Field -->
                        <div class="inputsign-container">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Email" required
                                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" oninput="validateInput('email')">
                            <span class="error-message email-error">Invalid email format</span>
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


                        <div class="input-container admin-input-container">
                            <!-- Left Icon -->
                            <img src="../assets/images/icon-select_role.png" class="icon-inside" alt="Role Icon">

                            <!-- Custom Dropdown -->
                            <div class="custom-dropdown">
                                <div class="selected">
                                    <span>Select Role</span>
                                    <i class="fas fa-chevron-down arrow-button"></i>
                                </div>
                                <ul class="dropdown-menu">
                                    <li data-value="admin">Administrator</li>
                                    <li data-value="staff">Staff Member</li>
                                    <li data-value="student">Student</li>
                                </ul>
                            </div>

                            <!-- Hidden input to store the selected value -->
                            <input type="hidden" id="roleSelect" name="roleSelect">
                        </div>


                        <script>
                            document.addEventListener("DOMContentLoaded", function () {
                                const dropdown = document.querySelector(".custom-dropdown");
                                const selected = dropdown.querySelector(".selected span");
                                const menu = dropdown.querySelector(".dropdown-menu");
                                const input = document.getElementById("roleSelect");

                                dropdown.addEventListener("click", () => {
                                    dropdown.classList.toggle("active");
                                });

                                menu.querySelectorAll("li").forEach(item => {
                                    item.addEventListener("click", function () {
                                        selected.textContent = this.textContent;
                                        input.value = this.getAttribute("data-value");
                                        dropdown.classList.remove("active");
                                    });
                                });

                                // Close dropdown when clicking outside
                                document.addEventListener("click", (e) => {
                                    if (!dropdown.contains(e.target)) {
                                        dropdown.classList.remove("active");
                                    }
                                });
                            });

                        </script>


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


<!-- THIS IS FOR SIGNUP -->
<script>
    function toggleContainers() {
        const login = document.getElementById('logInContainer');
        const signup = document.getElementById('signUpContainer');
        login.style.display = login.style.display === 'none' ? 'flex' : 'none';
        signup.style.display = signup.style.display === 'none' ? 'flex' : 'none';

    }

    function validateInput(inputType) {
        let inputElement = document.getElementById(inputType);
        let errorMessage = document.querySelector(`.${inputType}-error`);

        if (!inputElement.checkValidity()) {
            errorMessage.style.display = "block"; // Show error message
        } else {
            errorMessage.style.display = "none"; // Hide error message
        }
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

<!-- THIS IS FOR CREATING USER ACCOUNT AS ADMIN -->
<script>
    // Real-time validation for Email
    document.querySelector('input[name="email"]').addEventListener('input', function () {
        const email = this.value;
        const errorSpan = this.nextElementSibling;

        if (email === "") {
            // If the input is empty, remove the error message
            errorSpan.style.display = "none";
        } else {
            // Only accept emails ending with @rivaniot.online
            const regex = /^[a-z0-9._%+-]+@rivaniot\.online$/;
            if (!regex.test(email)) {
                errorSpan.textContent = "Invalid email format. Only @rivaniot.online emails are accepted.";
                errorSpan.style.display = "block";
            } else {
                errorSpan.style.display = "none";
            }
        }
    });

    // Real-time validation for Password
    document.getElementById('password').addEventListener('input', function () {
        const password = this.value;

        // Only check password strength if password is not empty
        if (password !== "") {
            checkPasswordStrength(password);
        } else {
            // If the password is empty, hide any strength message
            const errorSpan = document.querySelector('.password-strength-message');
            errorSpan.style.display = "none";

            // Hide the strength bar
            const strengthBar = document.querySelector('.strength-bar');
            strengthBar.style.width = "0%";
        }

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

        if (password === "" || retype === "") {
            // If either field is empty, hide the error
            errorSpan.style.display = "none";
        } else if (password !== retype) {
            // If the passwords do not match, show the error
            errorSpan.textContent = "Passwords do not match!";
            errorSpan.style.display = "block";
        } else {
            // If passwords match, hide the error
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