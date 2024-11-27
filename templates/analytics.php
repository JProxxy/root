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

    <script>
        // Set the percentages for humidity and temperature
        const humidityPercentage = 45; // Example value
        const temperaturePercentage = 26; // Example value

        // Get the chart elements
        const humidityChart = document.getElementById('humidityChart');
        const temperatureChart = document.getElementById('temperatureChart');

        // Update the humidity chart
        humidityChart.style.setProperty('--chart-color', '#3498db'); // Blue for humidity
        humidityChart.style.setProperty('--bg-color', '#e6e6e6');
        humidityChart.style.background = `conic-gradient(
        #3498db ${humidityPercentage * 3.6}deg,
        #e6e6e6 ${humidityPercentage * 3.6}deg 360deg
    )`;

        // Update the temperature chart
        temperatureChart.style.setProperty('--chart-color', '#e74c3c'); // Red for temperature
        temperatureChart.style.setProperty('--bg-color', '#e6e6e6');
        temperatureChart.style.background = `conic-gradient(
        #e74c3c ${temperaturePercentage * 3.6}deg,
        #e6e6e6 ${temperaturePercentage * 3.6}deg 360deg
    )`;

        // Update the circle values
        document.getElementById('humidityValue').textContent = `${humidityPercentage}%`;
        document.getElementById('temperatureValue').textContent = `${temperaturePercentage}°C`;
    </script>

</body>

</html>