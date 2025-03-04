<?php

// In google-auth.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../app/config/connection.php';
// if ($conn) {
//     echo "Database connection established.<br>";
// } else {
//     echo "Database connection failed.<br>";
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Prepare a statement to fetch user based on provided username and password
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify if user exists and check password (passwords are hashed)
        if ($user && password_verify($password, $user['password'])) {
            // Start the session and set user data
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../templates/dashboard.php"); // Redirect to dashboard or appropriate page
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
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/signup.css">

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>User Login</title>
</head>

<body>
    <div class="whiteBG">
        <div class="gridContainer">
            <!-- Login Container -->
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
                            <a href="javascript:void(0);" onclick="toggleContainers()" class="createAcc">Create
                                Account</a>
                        </div>
                        <div class="link-container">
                            <a href="forgotPassword.php" class="forgotPass">Forgot Password?</a>
                        </div>
                    </div>

                    <div id="g_id_onload"
                        data-client_id="460368018991-8r0gteoh0c639egstdjj7tedj912j4gv.apps.googleusercontent.com"
                        data-context="signin" data-ux_mode="popup" data-callback="handleCredentialResponse"
                        data-auto_prompt="false">
                    </div>

                    <div class="g_id_signin" data-type="standard"></div>

                    <script>
                        function handleCredentialResponse(response) {
                            fetch('../scripts/google-auth.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ token: response.credential })
                            })
                                .then(async res => {
                                    if (!res.ok) {
                                        const text = await res.text();
                                        throw new Error(`Server error: ${text}`);
                                    }
                                    return res.json();
                                })
                                .then(data => {
                                    console.log("Success:", data);
                                    // Redirect or handle success here
                                })
                                .catch(error => {
                                    console.error("Error:", error);
                                });
                        }
                    </script>
                </form>
            </div>

            <!-- Signup Container -->
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
                            <input type="password" id="password" name="password" placeholder="Password" required
                                autocomplete="off">
                            <div class="eyePosition">
                                <i class="fas fa-eye password-eye-icon"
                                    onclick="togglePasswordVisibility('password', this)"></i>
                            </div>
                        </div>
                        <div class="inputsign-container">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="retype_password" name="retype_password"
                                placeholder="Retype Password" required autocomplete="off">
                            <div class="eyePosition">
                                <i class="fas fa-eye password-eye-icon"
                                    onclick="togglePasswordVisibility('retype_password', this)"></i>
                            </div>
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
            const logInContainer = document.getElementById('logInContainer');
            const signUpContainer = document.getElementById('signUpContainer');

            // Toggle display for the login and signup containers
            if (logInContainer.style.display === 'none' || logInContainer.style.display === '') {
                logInContainer.style.display = 'flex'; // Set to 'flex' to maintain layout
                signUpContainer.style.display = 'none'; // Hide signup container
            } else {
                logInContainer.style.display = 'none'; // Hide login container
                signUpContainer.style.display = 'flex'; // Set to 'flex' for signup
            }
        }

        function togglePasswordVisibility(inputId, icon) {
            const passwordInput = document.getElementById(inputId);
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;

            // Change the icon based on the password visibility
            icon.classList.toggle('fa-eye'); // Show the eye icon
            icon.classList.toggle('fa-eye-slash'); // Show the eye-slash icon
        }

        // This is for login password show
        function toggleLoginPassword() {
            const loginPasswordInput = document.getElementById('loginpassword');
            const showLoginPasswordCheckbox = document.getElementById('showLoginPassword'); // Updated ID
            const type = showLoginPasswordCheckbox.checked ? 'text' : 'password';
            loginPasswordInput.type = type;
        }

        // This is for signup password show
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const retypePasswordInput = document.getElementById('retype_password');
            const showPasswordCheckbox = document.getElementById('showPassword'); // Use the same ID for signup
            const type = showPasswordCheckbox.checked ? 'text' : 'password';
            passwordInput.type = type;
            retypePasswordInput.type = type;
        }


    </script>

</body>

</html>