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
                    <a href="../templates/dashboard.php">
                        <img src="../assets/images/back.png" alt="back" class="backIcon">
                    </a>
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
                                <a href="../templates/settings-password.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/password-emoji.png" alt="Password" width="16" height="16"
                                        class="me-2">
                                    Password
                                </a>
                            </li>
                            <li>
                                <a href="../templates/settings-verification.php" class="nav-link active">
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
                                <span id="deleteAccount" style="color: red; cursor: pointer;">Delete Account</span>

                                <!-- Delete Account Modal -->
                                <div id="delete-modal" class="modal" style="display:none;">
                                    <div class="modal-content">
                                        <span class="close-btn" onclick="closeDeleteModal()">&times;</span>
                                        <h2>Delete Account</h2>
                                        <p>Are you sure you want to delete your account? This action is irreversible.
                                        </p>
                                        <input type="password" id="delete-password" class="text-input"
                                            placeholder="Enter your password" required>
                                        <input type="text" id="delete-confirm-text" class="text-input"
                                            placeholder="Type 'delete' to confirm" required>
                                        <button class="confirm-btnDelete" onclick="deleteAccount()">Confirm
                                            Delete</button>
                                    </div>
                                </div>

                                <script>
                                    // Function to open the delete modal
                                    function openDeleteModal() {
                                        document.getElementById("delete-modal").style.display = "block";
                                    }

                                    // Function to close the delete modal
                                    function closeDeleteModal() {
                                        document.getElementById("delete-modal").style.display = "none";
                                        document.getElementById("delete-password").value = "";
                                        document.getElementById("delete-confirm-text").value = "";
                                    }

                                    // Attach click event to the Delete Account text
                                    document.getElementById("deleteAccount").addEventListener("click", openDeleteModal);

                                    async function deleteAccount() {
                                        const password = document.getElementById("delete-password").value.trim();
                                        const confirmText = document.getElementById("delete-confirm-text").value.trim();

                                        if (!password) {
                                            alert("Please enter your password.");
                                            return;
                                        }
                                        if (confirmText.toLowerCase() !== "delete") {
                                            alert("Please type 'delete' to confirm.");
                                            return;
                                        }

                                        try {
                                            const response = await fetch('../scripts/delete_account.php', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json' },
                                                body: JSON.stringify({
                                                    password: password,
                                                    confirm: confirmText
                                                })
                                            });
                                            const result = await response.json();
                                            console.log('Debug info:', result.debug); // Logs the debug info if provided

                                            if (result.success) {
                                                alert("Your account has been deleted.");
                                                window.location.href = "../templates/login.php";
                                            } else {
                                                alert(result.message || "Error deleting account.");
                                            }
                                        } catch (error) {
                                            console.error("Error:", error);
                                            alert("An unexpected error occurred.");
                                        }
                                    }

                                </script>

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
                    <div class="flex-containerOneVer">
                        <div class="headerCont">
                            <h5>Verification</h5>
                            <br>
                            <p>
                                Verifying your account is an important step to ensure the security and integrity of your
                                profile.
                            </p>
                        </div>




                        <div class="veriCont">
                            <div class="input-wrapper">
                                <input type="text" id="verify-email" name="verify-email" class="text-input"
                                    placeholder="Verify Email Address" readonly>
                                <button class="verify-btn" type="button">Setup</button>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const emailInput = document.getElementById('verify-email');
                                    const verifyBtn = document.querySelector('.verify-btn');

                                    // Fetch verification status from server
                                    fetch('../scripts/isVerified.php')
                                        .then(response => {
                                            if (!response.ok) throw new Error('Network error');
                                            return response.json();
                                        })
                                        .then(data => {
                                            if (data.isVerified) {
                                                emailInput.value = data.email;
                                                verifyBtn.innerHTML = `
                    <svg class="verified-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                `;
                                                verifyBtn.disabled = true;
                                                verifyBtn.classList.add('verified');
                                            } else {
                                                verifyBtn.addEventListener('click', () => {
                                                    // Initiate verification process
                                                    window.location.href = '/verify-email';
                                                });
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            // Handle errors (show message to user)
                                        });
                                });
                            </script>


                            <div class="input-wrapper">
                                <input type="text" id="verify-phone" name="verify-phone" class="text-input"
                                    placeholder="Verify Phone Number" readonly>
                                <button class="verify-btn" type="button">Setup</button>
                            </div>
                            <!-- <div class="input-wrapper">
                                <input type="text" id="two-factor" name="two-factor" class="text-input"
                                    placeholder="Two-Factor Authentication" readonly>
                                <button class="verify-btn" type="button">Setup</button>
                            </div> -->
                        </div>



                        <!-- MODAL -->

                        <div id="verification-modal" class="modal">
                            <div class="modal-content">
                                <span class="close-btn" onclick="closeModal()">&times;</span>
                                <h2 id="modal-title">Verify</h2>
                                <label id="modal-label" for="verification-input"></label>
                                <input type="text" id="verification-input" class="text-input"
                                    placeholder="Enter Details" required>

                                <label for="otp">OTP Code</label>
                                <input type="text" id="otp" class="text-input" placeholder="Enter OTP" required>

                                <button class="verify-btnSet" onclick="verifyAction()">Verify</button>
                            </div>
                        </div>

                        <!-- JavaScript -->
                        <script>
                            document.addEventListener("DOMContentLoaded", function () {
                                const modal = document.getElementById("verification-modal");
                                const modalTitle = document.getElementById("modal-title");
                                const modalLabel = document.getElementById("modal-label");
                                const verificationInput = document.getElementById("verification-input");
                                const otpInput = document.getElementById("otp");

                                // Open Modal with dynamic content
                                function openModal(type) {
                                    modal.style.display = "block";
                                    if (type === "email") {
                                        modalTitle.textContent = "Verify Email";
                                        modalLabel.textContent = "Enter Email Address";
                                        verificationInput.placeholder = "Enter Email";
                                        verificationInput.setAttribute("name", "email");
                                    } else if (type === "phone") {
                                        modalTitle.textContent = "Verify Phone";
                                        modalLabel.textContent = "Enter Phone Number";
                                        verificationInput.placeholder = "Enter Phone";
                                        verificationInput.setAttribute("name", "phone");
                                    } else if (type === "two-factor") {
                                        modalTitle.textContent = "Two-Factor Authentication";
                                        modalLabel.textContent = "Authenticator App Code";
                                        verificationInput.placeholder = "Enter Authenticator Code";
                                        verificationInput.setAttribute("name", "authenticator");
                                    }
                                }

                                // Close Modal
                                window.closeModal = function () {
                                    modal.style.display = "none";
                                    verificationInput.value = "";
                                    otpInput.value = "";
                                };

                                // Attach event listeners to setup buttons
                                document.querySelectorAll(".verify-btn").forEach(button => {
                                    button.addEventListener("click", function () {
                                        if (this.previousElementSibling.id === "verify-email") {
                                            openModal("email");
                                        } else if (this.previousElementSibling.id === "verify-phone") {
                                            openModal("phone");
                                        } else if (this.previousElementSibling.id === "two-factor") {
                                            openModal("two-factor");
                                        }
                                    });
                                });

                                // Combined function for sending and verifying OTP (if using a manual button)
                                window.verifyAction = function () {
                                    const inputType = verificationInput.getAttribute("name");
                                    const inputValue = verificationInput.value.trim();
                                    const otpValue = otpInput.value.trim();

                                    if (!inputValue) {
                                        alert("Please enter your " + inputType + ".");
                                        return;
                                    }

                                    if (!otpValue) {
                                        // Send OTP if OTP field is empty
                                        let sendEndpoint = inputType === "email" ? "../scripts/check-email.php" : "../scripts/check-phone.php";
                                        fetch(sendEndpoint, {
                                            method: "POST",
                                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                            body: `${inputType}=${encodeURIComponent(inputValue)}`
                                        })
                                            .then(response => response.json())
                                            .then(data => {
                                                alert(data.message); // OTP sent message
                                            })
                                            .catch(error => console.error("Error:", error));
                                    } else {
                                        // Verify OTP if OTP field is filled
                                        let verifyEndpoint = inputType === "email" ? "../scripts/verify-email-otp.php" : "../scripts/verify-phone-otp.php";
                                        fetch(verifyEndpoint, {
                                            method: "POST",
                                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                            body: `${inputType}=${encodeURIComponent(inputValue)}&otp=${encodeURIComponent(otpValue)}`
                                        })
                                            .then(response => response.json())
                                            .then(data => {
                                                alert(data.message);
                                                if (data.success) {
                                                    closeModal();
                                                    location.reload();
                                                }
                                            })
                                            .catch(error => console.error("Error:", error));
                                    }
                                };

                                // Listen for real-time input on the OTP field
                                otpInput.addEventListener("input", function () {
                                    const otpValue = otpInput.value.trim();
                                    // Automatically verify OTP when 5 digits are entered
                                    if (otpValue.length === 5) {
                                        fetch('../scripts/settings-verify-otp.php', {
                                            method: "POST",
                                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                            body: "email=" + encodeURIComponent(verificationInput.value) +
                                                "&otp=" + encodeURIComponent(otpValue)
                                        })
                                            .then(response => response.text())
                                            .then(text => {
                                                console.log("Raw response:", text);
                                                return JSON.parse(text);
                                            })
                                            .then(data => {
                                                alert(data.message);
                                                if (data.success) {
                                                    closeModal();
                                                    location.reload();
                                                }
                                            })
                                            .catch(error => console.error("Error:", error));
                                    }
                                });
                            });
                        </script>


                    </div>

                </div>
                <script>
                    // Remove the old submitForm that was trying to submit a non-existent form.
                    // Instead, we call updatePassword() directly.
                    function submitForm() {
                        updatePassword();
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

<!-- JavaScript to handle password update -->
<script>
    async function updatePassword() {
        const currentPassword = document.getElementById("current-password").value;
        const newPassword = document.getElementById("new-password").value;
        const confirmPassword = document.getElementById("confirm-password").value;
        const errorDiv = document.querySelector('.errorNewPass');

        // Clear any previous error messages and hide the div
        errorDiv.innerText = "";
        errorDiv.style.display = "none";

        // Ensure the new passwords match
        if (newPassword !== confirmPassword) {
            errorDiv.innerText = "New passwords do not match!";
            errorDiv.style.display = "block"; // Show error
            return;
        }

        const data = {
            currentPassword: currentPassword,
            newPassword: newPassword
        };

        try {
            const response = await fetch('../scripts/update_settingsPassword.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            // Try to parse the JSON response
            const result = await response.json();

            if (result.success) {
                errorDiv.style.color = "green";
                errorDiv.style.backgroundColor = "#DEF2E3"; // Set background color to DEF2E3
                errorDiv.innerText = "Password updated successfully!";
                errorDiv.style.display = "block"; // Show success message
                // Clear the input fields
                document.getElementById("current-password").value = "";
                document.getElementById("new-password").value = "";
                document.getElementById("confirm-password").value = "";
            } else {
                errorDiv.style.backgroundColor = "#f2dede";
                errorDiv.style.color = "red";
                errorDiv.innerText = result.message || "Error updating password";
                errorDiv.style.display = "block"; // Show error
            }
        } catch (error) {
            console.error("Error:", error);
            errorDiv.style.color = "red";
            errorDiv.innerText = "An unexpected error occurred.";
            errorDiv.style.display = "block"; // Show error
        }
    }

</script>