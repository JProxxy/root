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





<style>
  .pendingCont {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    pointer-events: none;
    z-index: 2147483647 !important; /* Very high z-index */
  }

  /* Background image covers entire page */
  .pendingCont img:nth-of-type(1) {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    pointer-events: none;
  }

  /* Pending modal image: smaller and centered */
  .pendingCont .pending-modal {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 600px; /* Adjust size as needed */
    height: auto;
    pointer-events: none;
  }

  /* Pending logout image: smaller, centered, and slightly below the modal */
  .pendingCont .pending-logout {
    position: absolute;
    top: calc(50% + 60px); /* Adjust vertical offset as needed */
    left: 50%;
    transform: translate(-50%, -50%);
    width: 150px; /* Adjust size as needed */
    height: auto;
    z-index: 2147483648 !important; /* Even higher than container */
    pointer-events: auto; /* Allow clicks on the logout image */
  }
</style>

<?php


// Fetch user's mu_status (block or not) from the database using PDO
$query = "SELECT mu_status FROM users WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Only output the block overlay if the user has mu_status = 'block'
if ($user_data && $user_data['mu_status'] === 'block') {
    ?>
    <div class="pendingCont">
        <img src="../assets/images/ban_bg.png" alt="ban Background" />
        <img src="../assets/images/ban-modal.png" alt="Blocked Modal" class="pending-modal" />
        <img src="../assets/images/pending-logout.png" alt="Pending Logout" class="pending-logout"
            onclick="logoutUser()" />
    </div>

    <script>
        function logoutUser() {
            // Redirect to the logout script
            window.location.href = '../scripts/logout.php';
        }
    </script>
    <?php
}
?>




<style>
  .pendingCont {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    pointer-events: none;
    z-index: 2147483647 !important; /* Very high z-index */
  }

  /* Background image covers entire page */
  .pendingCont img:nth-of-type(1) {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    pointer-events: none;
  }

  /* Pending modal image: smaller and centered */
  .pendingCont .pending-modal {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 600px; /* Adjust size as needed */
    height: auto;
    pointer-events: none;
  }

  /* Pending logout image: smaller, centered, and slightly below the modal */
  .pendingCont .pending-logout {
    position: absolute;
    top: calc(50% + 60px); /* Adjust vertical offset as needed */
    left: 50%;
    transform: translate(-50%, -50%);
    width: 150px; /* Adjust size as needed */
    height: auto;
    z-index: 2147483648 !important; /* Even higher than container */
    pointer-events: auto; /* Allow clicks on the logout image */
  }
</style>

    <div class="mainContainer">
        <div class="sidePanel">
            <div class="topBar">
                <div class="topBarItem" onclick="window.location.href='../templates/dashboard.php'">
                    <img src="../assets/images/rivanLogo.png" alt="Logo" class="iconLogo" />
                </div>

                <div class="middleItems">
                    <div class="middleItem" onclick="window.location.href='../templates/FirstFloor-Outdoor.php'">
                        <img src="../assets/images/officeSpace.png" alt="Office Space" class="icon" />
                        <span>Office<br>Space</span>
                    </div>

                    <div class="middleItem" onclick="window.location.href='../templates/analytics.php'">
                        <img src="../assets/images/analytics.png" alt="Analytics" class="icon" />
                        <span>Analytics</span>
                    </div>
                    <div class="middleItem" onclick="window.location.href='../templates/members.php'">
                        <img src="../assets/images/members.png" alt="Members" class="icon" />
                        <span>Members</span>
                    </div>
                    <div class="middleItem" onclick="window.location.href='../templates/customize.php'">
                        <img src="../assets/images/customize.png" alt="Members" class="icon" />
                        <span>Customize</span>
                    </div>


                </div>

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

                // Fetch user's role_id from the database using PDO
                $query = "SELECT role_id FROM users WHERE user_id = :user_id";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

                $role_id = $user_data['role_id'] ?? null; // Fetch role_id or set to null if not found
                
                // Show the sysad block only if role_id is 1
                if ($role_id === 1 || $role_id === 2): ?>
                    <a href="../templates/SA-manageUsers.php">
                        <div class="sysad">
                            <img src="../assets/images/sysad.png" alt="sysad" class="sysadButton" />
                        </div>
                    </a>
                <?php endif; ?>

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