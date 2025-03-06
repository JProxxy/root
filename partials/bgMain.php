<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once '../app/config/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's email and profile_picture from the database using PDO
$query = "SELECT email, profile_picture FROM users WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Determine which profile picture to display
if (!empty($user_data['profile_picture'])) {
    // Use the stored profile picture
    $profilePictureUrl = $user_data['profile_picture'];
} else {
    // Fallback: generate an avatar using the first letter of the email
    $email = isset($user_data['email']) ? $user_data['email'] : 'N/A';
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
    <link rel="stylesheet" href="../assets/css/mainTheme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script>
        function handleButtonClick(item) {
            alert("You clicked on " + item);
        }

        function handleLogout() {
            window.location.href = '../scripts/logout.php';
        }
    </script>
</head>

<body>
    <div class="mainContainer">
        <div class="sidePanel">
            <div class="topBar">
                <div class="topBarItem" onclick="window.location.href='dashboard.php'">
                    <img src="../assets/images/rivanLogo.png" alt="Logo" class="iconLogo" />
                </div>

                <div class="middleItems">
                    <div class="middleItem" onclick="window.location.href='officeSpace.php'">
                        <img src="../assets/images/officeSpace.png" alt="Office Space" class="icon" />
                        <span>Office<br>Space</span>
                    </div>

                    <div class="middleItem" onclick="handleButtonClick('Analytics')">
                        <img src="../assets/images/analytics.png" alt="Analytics" class="icon" />
                        <span>Analytics</span>
                    </div>
                    <div class="middleItem" onclick="handleButtonClick('Members')">
                        <img src="../assets/images/members.png" alt="Members" class="icon" />
                        <span>Members</span>
                    </div>
                </div>
                <div class="lowerBarItem" onclick="handleLogout()">
                    <img src="../assets/images/logout.png" alt="Log Out" class="iconLogout" />
                    <span>Log Out</span>
                </div>
            </div>
        </div>

        <a href="../templates/settings-profile.php">
            <div class="profile">
                <img id="profile-img" src="<?php echo $profilePictureUrl; ?>" alt="Profile Icon"
                    class="profile-image" />
            </div>
        </a>


        <div class="mainContent">

        </div>
    </div>
</body>

</html>