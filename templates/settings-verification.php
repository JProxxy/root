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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>
        <div class="containerPart">
            <div class="headbackCont">
                <div class="imgBack">
                    <img src="../assets/images/back.png" alt="back" class="backIcon">
                </div>
                <div class="headerText">Account Settings</div>
            </div>

            <div class="sidepanel">
                <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 280px;">

                    <ul class="nav nav-pills flex-column mb-auto">


                        <br>
                        <li class="nav-item">
                            <a href="../templates/settings-profile.php" class="nav-link link-body-emphasis" aria-current="page">
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
                            <span style="color: red;">Delete Account</span>
                        </li>

                        <br>
                        <br>
                        <br>
                        <br>
                        <br>

                    </ul>


                </div>
            </div>

        </div>


        <div class="sideOption">

        </div>
    </div>





</body>

</html>