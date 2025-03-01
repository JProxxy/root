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

// Fetch user data
$user_id = $_SESSION['user_id']; // Assuming you are storing the user_id in session
$query = "SELECT first_name, last_name, phoneNumber, email, role_id, gender FROM users WHERE user_id = :user_id";
$stmt = $conn->prepare($query); // Use $conn here instead of $pdo

// Use bindValue to bind the user_id
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

// Execute the query
$stmt->execute();

// Fetch user data
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback for null values
$first_name = isset($user_data['first_name']) ? $user_data['first_name'] : 'N/A';
$last_name = isset($user_data['last_name']) ? $user_data['last_name'] : 'N/A';
$phoneNumber = isset($user_data['phoneNumber']) ? htmlspecialchars(trim($user_data['phoneNumber'])) : 'N/A';
$email = isset($user_data['email']) ? $user_data['email'] : 'N/A';
$role = isset($user_data['role_id']) ? $user_data['role_id'] : 'N/A';
$gender = isset($user_data['gender']) ? $user_data['gender'] : 'N/A';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
                                <a href="../templates/settings-profile.php" class="nav-link active">
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
                        <h6 class="myProf">My Profile</h6>
                        <div class="left-up">

                            <div class="profile-picture">
                                <!-- Profile image that will display the uploaded image -->
                                <img id="profile-img" src="" alt="Profile Picture" onerror="setDefaultProfile()">

                                <!-- Camera icon for editing the profile picture -->
                                <div class="edit-icon">
                                    <img src="../assets/images/camera-emoji.png" alt="Edit"
                                        onclick="triggerFileInput()">
                                </div>

                                <!-- Hidden file input for selecting the profile picture -->
                                <input type="file" id="file-input" style="display: none;" accept="image/*"
                                    onchange="uploadFile(event)">
                            </div>




                        </div>

                        <div class="left-down">
                            <span>Links</span>
                            <img src="../assets/images/links-emoji.png" alt="Icon" width="20" height="20">
                        </div>
                    </div>

                    <div class="flex-containerTwo">
                        <div class="top-right">
                            <span id="full-name">
                                <?php
                                // Check if the PHP variables are set, otherwise show default values
                                echo isset($first_name) ? $first_name : 'FirstName';
                                echo isset($middle_name) ? ' ' . $middle_name : ' M.';
                                echo isset($last_name) ? ' ' . $last_name : ' LastName';
                                echo isset($title) ? ' (' . $title . ')' : ' (Title)';
                                ?>
                            </span>
                            <hr style="margin-left: 10%; width: 87%;">
                        </div>





                        <!-- MIDDLE RIGHT -->
                        <div class="middle-right">
                            <form id="personalInfoForm" action="update_profile.php" method="post">
                                <div class="headerPIA">
                                    <h6 class="personalInfoAdd">Personal Information</h6>
                                    <!-- Image button for Edit -->
                                    <img src="../assets/images/button-edit.png" alt="edit" class="edit-btn"
                                        id="editPI-btn" onclick="toggleEditPersonalInfo()">
                                </div>

                                <table class="styled-table">
                                    <tr class="tr-title">
                                        <td>First Name</td>
                                        <td>Last Name</td>
                                        <td>Role</td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td><input type="text" name="first_name"
                                                value="<?php echo htmlspecialchars($first_name); ?>" disabled /></td>
                                        <td><input type="text" name="last_name"
                                                value="<?php echo htmlspecialchars($last_name); ?>" disabled /></td>
                                        <td><input type="text" name="role"
                                                value="<?php echo htmlspecialchars($role); ?>" disabled /></td>
                                    </tr>
                                    <tr class="tr-title">
                                        <td>Email Address</td>
                                        <td>Phone</td>
                                        <td>Gender</td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td><input type="email" name="email"
                                                value="<?php echo htmlspecialchars($email); ?>" disabled /></td>
                                        <td><input type="text" name="phoneNumber"
                                                value="<?php echo htmlspecialchars($phoneNumber); ?>" disabled /></td>
                                        <td>
                                            <select name="gender" disabled>
                                                <option value="N/A" <?php echo ($gender == 'N/A') ? 'selected' : ''; ?>>
                                                    N/A</option>
                                                <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>
                                                    Male</option>
                                                <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo ($gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                        <hr style="margin-left: 10%; width: 87%;">

                        <!-- BOTTOM RIGHT -->

                        <div class="bottom-right">
                            <form action="update_profile.php" method="post">

                                <div class="headerPIA">
                                    <h6 class="personalInfoAdd">Address</h6>
                                    <!-- Image button for Edit -->
                                    <img src="../assets/images/button-edit.png" alt="edit" class="edit-btn"
                                        id="editAdd-btn" onclick="toggleEditAddress()">
                                </div>

                                <table class="styled-table">
                                    <tr class="tr-title">
                                        <td>Country</td>
                                        <td>City</td>
                                        <td>Street</td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td><input type="text" name="country"
                                                value="<?php echo isset($user_data['country']) ? $user_data['country'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                        <td><input type="text" name="city"
                                                value="<?php echo isset($user_data['city']) ? $user_data['city'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                        <td><input type="text" name="street"
                                                value="<?php echo isset($user_data['street']) ? $user_data['street'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                    </tr>

                                    <tr class="tr-title">
                                        <td>Postal Code</td>
                                        <td>Barangay</td>

                                    </tr>
                                    <tr class="tr-content">
                                        <td><input type="email" name="postalCode"
                                                value="<?php echo isset($user_data['postalCode']) ? $user_data['postalCode'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                        <td><input type="text" name="barangay"
                                                value="<?php echo isset($user_data['barangay']) ? $user_data['barangay'] : 'N/A'; ?>"
                                                disabled />
                                        </td>

                                    </tr>
                                </table>

                            </form>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>





</body>

</html>

<script>
    // Function to trigger the file input when the camera icon is clicked
    function triggerFileInput() {
        document.getElementById('file-input').click();
    }

    // Function to upload the selected file
    function uploadFile(event) {
        const file = event.target.files[0];

        if (file) {
            // Check if the file is an image (basic client-side check)
            const fileType = file.type;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

            if (!allowedTypes.includes(fileType)) {
                alert('Only JPG, PNG, and JPEG images are allowed.');
                return; // Stop if the file type is not allowed
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('profile-img').src = e.target.result;
            }
            reader.readAsDataURL(file);

            // Create FormData to send file via POST request
            const formData = new FormData();
            formData.append('file', file);

            // Send the file to the PHP backend (uploadPP.php)
            fetch('../scripts/uploadPP.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json()) // Expecting JSON response with file URL
                .then(data => {
                    if (data.url) {
                        // Successfully uploaded; set profile image
                        document.getElementById('profile-img').src = data.url;
                    } else {
                        alert('Error uploading file');
                    }
                })
                .catch(error => {
                    console.error('Error uploading file:', error);
                    alert('Error uploading file');
                });
        }
    }

    // Default profile picture (in case of an error or no image)
    function setDefaultProfile() {
        document.getElementById('profile-img').src = '../assets/images/default-profile.png';
    }
</script>

<script>
    function setDefaultProfile() {
        document.getElementById("profile-img").src = "../assets/images/defaultProfile.png";
    }

    // Toggle edit/save for Personal Info
    function toggleEditPersonalInfo() {
        // Get the form elements
        const inputs = document.querySelectorAll('.middle-right input');
        const selects = document.querySelectorAll('.middle-right select');
        const editBtn = document.getElementById('editPI-btn');
        const form = document.querySelector('.middle-right form');

        // Check if the form is already editable
        const isEditable = inputs[0].disabled;

        // Toggle disabled state
        inputs.forEach(input => input.disabled = !isEditable);
        selects.forEach(select => select.disabled = !isEditable);

        // Toggle the button image
        if (isEditable) {
            // Change to "Save" button
            editBtn.src = '../assets/images/button-confirm.png';
            editBtn.setAttribute('onclick', 'savePersonalInfo()');
        } else {
            // Change back to "Edit" button
            editBtn.src = '../assets/images/button-edit.png';
            editBtn.setAttribute('onclick', 'toggleEditPersonalInfo()');
        }
    }

    // Save Personal Info
    function savePersonalInfo() {
        // Collect form data
        const form = document.getElementById('personalInfoForm');
        const formData = new FormData(form);

        // Use AJAX to send data to the server
        fetch('../scripts/update_profile.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Personal info has been saved.');
                    toggleEditPersonalInfo();
                } else {
                    alert('There was an error saving the info.');
                }
            })
            .catch(error => console.error('Error:', error));
    }


    // Toggle edit/save for Address
    function toggleEditAddress() {
        // Get the form elements
        const inputs = document.querySelectorAll('.bottom-right input');
        const editBtn = document.getElementById('editAdd-btn');
        const form = document.querySelector('.bottom-right form');

        // Check if the form is already editable
        const isEditable = inputs[0].disabled;

        // Toggle disabled state
        inputs.forEach(input => input.disabled = !isEditable);

        // Toggle the button image
        if (isEditable) {
            // Change to "Save" button
            editBtn.src = '../assets/images/button-confirm.png';
            editBtn.setAttribute('onclick', 'saveAddress()');
        } else {
            // Change back to "Edit" button
            editBtn.src = '../assets/images/button-edit.png';
            editBtn.setAttribute('onclick', 'toggleEditAddress()');
        }
    }

    // Save Address Info
    function saveAddress() {
        // Add save functionality here (submit form or send AJAX request)
        alert('Address info has been saved.');

        // After saving, switch back to edit mode
        toggleEditAddress();
    }


</script>