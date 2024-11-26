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
</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="dashboardDevider">
         
            </div>

            <div class="dashboardDeviderRight">
            
            </div>
        </div>
    </div>

    <script>
        async function fetchWeatherData() {
            try {
                const apiKey = 'f75719d97895581d48b332dfc95e479b';
                const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=Makati&appid=${apiKey}&units=metric`);

                // Check if the response is OK (status code 200)
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();

                // Update HTML only if data is defined and in the expected format
                if (data.main && data.weather) {
                    document.querySelector(".tempValue").textContent = data.main.temp;
                    document.querySelector(".description").textContent = data.weather[0].description;
                    document.querySelector(".humidity").textContent = `Humidity: ${data.main.humidity}%`;
                    document.querySelector(".wind").textContent = `Wind: ${data.wind.speed} km/h`;
                    document.querySelector(".precipitation").textContent = `Precipitation: ${data.clouds.all}%`;
                } else {
                    console.error("Unexpected data format:", data);
                }
            } catch (error) {
                console.error("Error fetching weather data:", error);
            }
        }

        // Call function on page load
        fetchWeatherData();

    </script>
</body>

</html>