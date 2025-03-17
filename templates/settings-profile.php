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
$query = "SELECT first_name, last_name, phoneNumber, email, bio, gender, country, city, street_address, postal_code, barangay, profile_picture
          FROM users 
          WHERE user_id = :user_id";
$stmt = $conn->prepare($query);

// Use bindValue to bind the user_id
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

// Execute the query
$stmt->execute();

// Fetch user data
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback for null values for basic info
$first_name = isset($user_data['first_name']) ? $user_data['first_name'] : 'N/A';
$last_name = isset($user_data['last_name']) ? $user_data['last_name'] : 'N/A';
$phoneNumber = isset($user_data['phoneNumber']) ? htmlspecialchars(trim($user_data['phoneNumber'])) : 'N/A';
$email = isset($user_data['email']) ? $user_data['email'] : 'N/A';
$bio = isset($user_data['bio']) ? $user_data['bio'] : 'N/A';
$gender = isset($user_data['gender']) ? $user_data['gender'] : 'N/A';

// Fallback for address fields
$country = isset($user_data['country']) ? $user_data['country'] : 'N/A';
$city = isset($user_data['city']) ? $user_data['city'] : 'N/A';
$street_address = isset($user_data['street_address']) ? $user_data['street_address'] : 'N/A';
$postal_code = isset($user_data['postal_code']) ? $user_data['postal_code'] : 'N/A';
$barangay = isset($user_data['barangay']) ? $user_data['barangay'] : 'N/A';

// Determine which profile image to display
if (!empty($user_data['profile_picture'])) {
    // Use the stored profile picture path from the database
    $profilePictureUrl = $user_data['profile_picture'];
} else {
    // Fallback to a generated avatar using the first letter of the email
    $initial = strtoupper(substr($email, 0, 1));
    $profilePictureUrl = "https://ui-avatars.com/api/?name=" . urlencode($initial) . "&background=random&color=fff";
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
                                <img id="profile-img" src="<?php echo $profilePictureUrl; ?>" alt="Profile Picture">
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

                        <!-- Button that opens the modal -->
                        <div class="left-down">
                            <div class="headerLinks">
                                <h5>Links</h5>
                                <img src="../assets/images/links-emoji.png" alt="Icon" width="20" height="20"
                                    id="openModal">
                            </div>

                            <br>
                            <div class="socmeds">
                                <div class="social-box facebook">
                                    <img src="../assets/images/icon-facebook.png" alt="Facebook Logo" width="30"
                                        height="30">
                                    <a id="fbLink" href="#" target="_blank">Facebook</a>
                                </div>

                                <div class="social-box linkedin">
                                    <img src="../assets/images/icon-linkedin.png" alt="LinkedIn Logo" width="30"
                                        height="30">
                                    <a id="liLink" href="#" target="_blank">LinkedIn</a>
                                </div>

                                <div class="social-box telegram">
                                    <img src="../assets/images/icon-telegram.png" alt="Telegram Logo" width="30"
                                        height="30">
                                    <a id="tgLink" href="#" target="_blank">Telegram</a>
                                </div>
                            </div>

                        </div>
                    </div>



                    <div class="flex-containerTwo">
                        <div class="top-right">
                            <span id="full-name">
                                <?php
                                // Check if the PHP variables are set, otherwise show default values
                                echo isset($first_name) ? $first_name : ' ';
                                echo isset($last_name) ? ' ' . $last_name : ' ';
                                echo isset($bio) ? ' (' . $bio . ')' : ' ';
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
                                        <td>Bio</td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td><input type="text" id="firstName" name="first_name"
                                                value="<?php echo htmlspecialchars($first_name); ?>" disabled
                                                title="<?php echo htmlspecialchars($first_name); ?>" /></td>
                                        <td><input type="text" id="lastName" name="last_name"
                                                value="<?php echo htmlspecialchars($last_name); ?>" disabled
                                                title="<?php echo htmlspecialchars($last_name); ?>" /></td>
                                        <td><input type="text" id="bio" name="bio"
                                                value="<?php echo htmlspecialchars($bio); ?>" disabled
                                                title="<?php echo htmlspecialchars($bio); ?>" /></td>
                                    </tr>
                                    <tr class="tr-title">
                                        <td>Email Address</td>
                                        <td>Phone</td>
                                        <td>Gender</td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td><input type="email" id="emailInput" name="email"
                                                value="<?php echo htmlspecialchars($email); ?>" disabled
                                                title="<?php echo htmlspecialchars($email); ?>" /></td>
                                        <td><input type="text" id="phoneNumber" name="phoneNumber"
                                                value="<?php echo htmlspecialchars($phoneNumber); ?>" disabled
                                                title="<?php echo htmlspecialchars($phoneNumber); ?>" /></td>
                                        <td>
                                            <select id="genderSelect" name="gender" disabled>
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
                                        <td>Street Address</td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td>
                                            <input type="text" id="country" name="country"
                                                value="<?php echo htmlspecialchars($country); ?>" disabled
                                                title="<?php echo htmlspecialchars($country); ?>" />
                                        </td>
                                        <td>
                                            <input type="text" id="city" name="city"
                                                value="<?php echo htmlspecialchars($city); ?>" disabled
                                                title="<?php echo htmlspecialchars($city); ?>" />
                                        </td>
                                        <td>
                                            <input type="text" id="street_address" name="street_address"
                                                value="<?php echo htmlspecialchars($street_address); ?>" disabled
                                                title="<?php echo htmlspecialchars($street_address); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="tr-title">
                                        <td>Postal Code</td>
                                        <td>Barangay</td>
                                        <td></td>
                                    </tr>
                                    <tr class="tr-content">
                                        <td>
                                            <input type="text" name="postal_code"
                                                value="<?php echo htmlspecialchars($postal_code); ?>" disabled
                                                title="<?php echo htmlspecialchars($postal_code); ?>" />
                                        </td>
                                        <td>
                                            <input type="text" name="barangay"
                                                value="<?php echo htmlspecialchars($barangay); ?>" disabled
                                                title="<?php echo htmlspecialchars($barangay); ?>" />
                                        </td>
                                        <td></td>
                                    </tr>
                                </table>



                            </form>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>




    <!-- Modal structure -->
    <div id="socialMediaModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h5>Enter Your Social Media Links</h5>
            <br>
            <input type="text" id="facebook" placeholder="Facebook URL">
            <input type="text" id="linkedin" placeholder="LinkedIn URL">
            <input type="text" id="telegram" placeholder="Telegram URL">
            <br>
            <button id="confirmLinks">Confirm</button>
        </div>
    </div>
</body>

</html>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetch("../scripts/getSocialMediaLinks.php")
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Helper function to extract username from URL
                function extractUsername(url, platform) {
                    try {
                        let urlObj = new URL(url);
                        let segments = urlObj.pathname.split('/').filter(Boolean); // remove empty segments
                        if (platform === "facebook") {
                            // e.g. "/JProxxyV2" becomes "JProxxyV2"
                            return segments[0] || "Facebook";
                        } else if (platform === "linkedin") {
                            // e.g. "/in/username" -> if first segment is 'in', use second segment
                            return (segments[0] === "in" && segments[1]) ? segments[1] : segments[0] || "LinkedIn";
                        } else if (platform === "telegram") {
                            // e.g. "/username" becomes "username"
                            return segments[0] || "Telegram";
                        } else {
                            return "";
                        }
                    } catch (e) {
                        return "";
                    }
                }

                // Validate and update Facebook box
                if (data.facebook && data.facebook.trim() !== "") {
                    const fbUrl = data.facebook;
                    const fbUsername = extractUsername(fbUrl, "facebook");
                    document.querySelector(".social-box.facebook").style.display = "block";
                    document.getElementById("fbLink").href = fbUrl;
                    document.getElementById("fbLink").innerText = fbUsername;
                } else {
                    document.querySelector(".social-box.facebook").style.display = "none";
                }

                // Validate and update LinkedIn box
                if (data.linkedin && data.linkedin.trim() !== "") {
                    const liUrl = data.linkedin;
                    const liUsername = extractUsername(liUrl, "linkedin");
                    document.querySelector(".social-box.linkedin").style.display = "block";
                    document.getElementById("liLink").href = liUrl;
                    document.getElementById("liLink").innerText = liUsername;
                } else {
                    document.querySelector(".social-box.linkedin").style.display = "none";
                }

                // Validate and update Telegram box
                if (data.telegram && data.telegram.trim() !== "") {
                    const tgUrl = data.telegram;
                    const tgUsername = extractUsername(tgUrl, "telegram");
                    document.querySelector(".social-box.telegram").style.display = "block";
                    document.getElementById("tgLink").href = tgUrl;
                    document.getElementById("tgLink").innerText = tgUsername;
                } else {
                    document.querySelector(".social-box.telegram").style.display = "none";
                }
            })
            .catch(error => {
                console.error("Error fetching social media links:", error);
                alert("An error occurred while fetching your social media links.");
            });
    });

</script>

<!-- GET LINKS ON MODAL -->
<script>
    // Fetch social media links from the database when modal opens
    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("openModal").addEventListener("click", function () {
            fetch("../scripts/getSocialMediaLinks.php")
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Error from server:", data.error);
                        alert("Failed to fetch social media links.");
                        return;
                    }
                    document.getElementById("facebook").value = data.facebook || "";
                    document.getElementById("linkedin").value = data.linkedin || "";
                    document.getElementById("telegram").value = data.telegram || "";
                })
                .catch(error => {
                    console.error("Error fetching social media links:", error);
                    alert("An error occurred while fetching your social media links.");
                });
        });
    });


</script>

<!-- SAVE LINKS ON MODAL -->
<script>
    document.getElementById("openModal").addEventListener("click", function () {
        document.getElementById("socialMediaModal").style.display = "block";
    });

    document.getElementById("closeModal").addEventListener("click", function () {
        document.getElementById("socialMediaModal").style.display = "none";
    });

    document.getElementById("confirmLinks").addEventListener("click", function () {
        let facebook = document.getElementById("facebook").value.trim();
        let linkedin = document.getElementById("linkedin").value.trim();
        let telegram = document.getElementById("telegram").value.trim();

        // Function to add "https://" if missing
        function formatURL(url) {
            if (url === "") return ""; // Allow empty fields
            if (!/^https?:\/\//i.test(url)) {
                return "https://" + url; // Add https:// if missing
            }
            return url;
        }

        // Format links before sending
        facebook = formatURL(facebook);
        linkedin = formatURL(linkedin);
        telegram = formatURL(telegram);

        // Function to validate profile URLs
        function isValidProfileURL(url, platform) {
            if (url === "") return true; // Allow empty fields
            let patterns = {
                facebook: /^https?:\/\/(www\.)?facebook\.com\/[\w.-]+\/?$/,
                linkedin: /^https?:\/\/(www\.)?linkedin\.com\/in\/[\w-]+\/?$/,
                telegram: /^https?:\/\/t\.me\/[\w-]+\/?$/
            };
            return patterns[platform].test(url);
        }

        // Validate each social media link
        if (!isValidProfileURL(facebook, "facebook")) {
            alert("Invalid Facebook link. Enter your full profile URL (e.g., facebook.com/juan).");
            return;
        }
        if (!isValidProfileURL(linkedin, "linkedin")) {
            alert("Invalid LinkedIn link. Enter your full profile URL (e.g., linkedin.com/in/juan).");
            return;
        }
        if (!isValidProfileURL(telegram, "telegram")) {
            alert("Invalid Telegram link. Enter your full profile URL (e.g., t.me/juan).");
            return;
        }

        // If all fields are empty, show an error
        if (!facebook && !linkedin && !telegram) {
            alert("Please enter at least one social media link.");
            return;
        }

        // Store empty fields as empty strings
        let socialMedia = {
            facebook: facebook || "",
            linkedin: linkedin || "",
            telegram: telegram || ""
        };

        fetch("../scripts/save_social_media.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(socialMedia)
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (error) {
                    throw new Error("Invalid JSON response: " + text);
                }
            })
            .then(data => {
                if (data.error) {
                    alert("Error: " + data.error);
                } else {
                    alert(data.message);
                    document.getElementById("socialMediaModal").style.display = "none";
                    window.location.reload();
                }
            })
            .catch(error => console.error("Error:", error));
    });

</script>

<!-- UPDATE PROFILE PICTURE -->
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
                .then(response => response.text()) // Change to .text() for debugging
                .then(text => {
                    console.log('Server response:', text);
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        alert('Invalid JSON response: ' + text);
                        return;
                    }

                    if (data.url) {
                        document.getElementById('profile-img').src = data.url;
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error uploading file:', error);
                    alert('Error uploading file');
                })
        }
</script>

<!-- SHOW PROFILE/ EDIT PERSONAL INFO-->
<script>

        // Toggle edit/save for Personal Info
        function toggleEditPersonalInfo() {
            // Get the form elements by their IDs (excluding email)
            const inputsToToggle = [
                document.getElementById('firstName'),
                document.getElementById('lastName'),
                document.getElementById('bio'),
                document.getElementById('phoneNumber')
            ];
            const selectsToToggle = [
                document.getElementById('genderSelect')
            ];
            const editBtn = document.getElementById('editPI-btn');

            // Determine editability by checking one of the inputs (e.g., firstName)
            const isEditable = inputsToToggle[0].disabled;

            // Toggle disabled state for selected inputs
            inputsToToggle.forEach(input => {
                input.disabled = !isEditable;
            });
            selectsToToggle.forEach(select => {
                select.disabled = !isEditable;
            });

            // Toggle button appearance and onclick attribute
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
                        window.location.reload();
                    } else {
                        alert('There was an error saving the info.');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Save Address Info using AJAX
        function saveAddress() {
            // Collect form data from the address form
            const form = document.querySelector('.bottom-right form');
            const formData = new FormData(form);

            // Send the data to update_address.php via a POST request
            fetch('../scripts/update_address.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Address info has been saved.');
                        toggleEditAddress(); // Switch back to non-editable mode
                        window.location.reload();
                    } else {
                        alert('There was an error saving the address.');
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



</script>