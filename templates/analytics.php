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
    <link rel="stylesheet" href="../assets/css/analytics.css">

</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="containerPart">
            <h2>Analytics</h2>

            <div class="firstPart">
                <p>Water Tank Level</p>
                <div class="textContainer">
                    <div class="weekWater">
                        <div class="noteWeek"></div>
                        <p>Ave. Weekly Water Usage</p>
                        <span>500 L</span>
                    </div>

                    <div class="monthWater">
                        <div class="noteMonth"></div>
                        <p>Ave. Monthly Water Usage</p>
                        <span>3,500 L</span>
                    </div>
                </div>
                <div class="waterGauge">
                    <?php include '../partials/gaugeChart.php'; ?>
                </div>

                <div class="deviderFirst"> </div>

                <div class="roomTempSelect">
                    <span class="roomTitle">Select Room Temperature:</span>
                    <select id="roomTempDropdown" class="dropdown">
                        <option value="1stFloor">Room Temp - 1st Floor</option>
                        <option value="2ndFloor">Room Temp - 2nd Floor</option>
                        <option value="3rdFloor">Room Temp - 3rd Floor</option>
                        <option value="4thFloor">Room Temp - 4th Floor</option>
                    </select>
                </div>

                <div class="chartRoomCont">
                    <div class="roomTemp">
                        <?php include '../partials/roomTempBar.php'; ?>
                    </div>

                    <div class="roomHum">
                        <?php include '../partials/roomHumBar.php'; ?>
                    </div>
                </div>
            </div>




            <div class="secondPart">

            </div>
            <div class="thirdPart">

            </div>
        </div>
    </div>

    </div>

</body>

</html>