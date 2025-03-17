<?php

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../templates/login.php");
    exit();
}

// Include the database connection
require_once '../app/config/connection.php';

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <link rel="stylesheet" href="../assets/css/settings-profile.css">
    <link rel="stylesheet" href="../assets/css/settings-password.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">


</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>
        <div class="containerPart">
            <!-- Header -->
            <div class="headbackCont">
                <div class="imgBack">
                    <img src="../assets/images/back.png" alt="back" class="backIcon">
                </div>
                <div class="headerText">Account Settings</div>
            </div>

            <!-- Content Wrapper (Side Panel + Profile Main) -->
            <div class="contentWrapper">
                <!-- Sidebar -->
                <div class="sidepanel">
                    <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 100%;">

                        <ul class="nav nav-pills flex-column mb-auto">
                            <br>
                            <li class="nav-item">
                                <a href="../templates/settings-profile.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/user-emoji.png" alt="Profile" width="16" height="16"
                                        class="me-2">
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a href="../templates/settings-password.php" class="nav-link active">
                                    <img src="../assets/images/password-emoji.png" alt="Password" width="16" height="16"
                                        class="me-2">
                                    Password
                                </a>
                            </li>
                            <li>
                                <a href="../templates/settings-verification.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/verification-emoji.png" alt="Verification" width="16"
                                        height="16" class="me-2">
                                    Verification
                                </a>
                            </li>
                            <br>
                            <br>
                            <br>
                            <li>
                                <span class="me-2" style="display: inline-block; width: 16px; height: 16px;"></span>
                                <span style="color: red;">Delete Account</span>
                            </li>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                        </ul>
                    </div>
                </div>

                <!-- Main Profile Section -->
                <div class="profile-main">

                    <div class="flex-containerOne">
                        <h2>Security</h2>
                        <span>Password</span>
                        <p>To ensure the security of your account, please provide your current password in order to
                            proceed with changing it.</p>

                        <label for="current-password">Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="current-password" name="current-password" class="password-input"
                                placeholder="Current Password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('current-password')"></i>
                        </div>

                        <label for="new-password">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="new-password" name="new-password" class="password-input"
                                placeholder="New Password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('new-password')"></i>
                        </div>

                        <label for="confirm-password">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm-password" name="confirm-password" class="password-input"
                                placeholder="Confirm Password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm-password')"></i>
                        </div>

                        <div class="errorNewPass"></div>
                    </div>



                    <div class="flex-containerTwo">
                        <div class="buttonCont">
                            <button type="button" class="update-password-btn" onclick="submitForm()">Save</button>
                            <button type="button" class="discard-password-btn">Discard</button>
                        </div>

                        <div class="passwordGuidCont">
                            <span>Password Guidelines</span>
                            <br>
                            <br>

                            <p>
                                Following password guidelines protects your accounts and sensitive information by
                                reducing the risk of hacking and unauthorized access.
                            </p>

                            <ul>
                                <li>Include a mix of uppercase and lowercase letters.</li>
                                <li>Ensure the password is between 8 to 16 characters in length.</li>
                                <li>Use only letters, numbers, and common punctuation marks.</li>
                            </ul>
                        </div>
                    </div>

                </div>

                <script>
                    function submitForm() {
                        document.getElementById("password-form").submit();
                    }

                    function togglePassword(fieldId) {
                        const passwordField = document.getElementById(fieldId);
                        const icon = passwordField.nextElementSibling;

                        if (passwordField.type === "password") {
                            passwordField.type = "text";
                            icon.classList.remove("fa-eye");
                            icon.classList.add("fa-eye-slash");
                        } else {
                            passwordField.type = "password";
                            icon.classList.remove("fa-eye-slash");
                            icon.classList.add("fa-eye");
                        }
                    }


                </script>

            </div>
        </div>
    </div>





</body>

</html>