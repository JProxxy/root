<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../templates/login.php");
    exit();
}
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
                                <img id="profile-img" src="" alt="Profile Picture"
                                    onerror="setDefaultProfile()">
                                <div class="edit-icon">
                                    <img src="../assets/images/camera-emoji.png" alt="Edit" onerror="setDefaultProfile()"> 
                                </div>
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
                            <form action="update_profile.php" method="post">

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
                                                value="<?php echo isset($user_data['first_name']) ? $user_data['first_name'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                        <td><input type="text" name="last_name"
                                                value="<?php echo isset($user_data['last_name']) ? $user_data['last_name'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                        <td><input type="text" name="role"
                                                value="<?php echo isset($user_data['role']) ? $user_data['role'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                    </tr>

                                    <tr class="tr-title">
                                        <td>Email Address</td>
                                        <td>Phone</td>
                                        <td>Gender</td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td><input type="email" name="email"
                                                value="<?php echo isset($user_data['email']) ? $user_data['email'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                        <td><input type="text" name="phone"
                                                value="<?php echo isset($user_data['phone']) ? $user_data['phone'] : 'N/A'; ?>"
                                                disabled />
                                        </td>
                                        <td>
                                            <select name="gender" disabled>
                                                <option value="N/A" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'N/A') ? 'selected' : ''; ?>>N/A</option>
                                                <option value="Male" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female
                                                </option>
                                                <option value="Other" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
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
    // Add save functionality here (submit form or send AJAX request)
    alert('Personal info has been saved.');
    
    // After saving, switch back to edit mode
    toggleEditPersonalInfo();
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