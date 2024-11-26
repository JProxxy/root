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
            <div class="dashboardDeviderLeft">
                <img src="../assets/images/overviewBuilding.png" alt="Overview Building of Rivan" class="mainBuilding">
            </div>

            <div class="dashboardDeviderRight">
                <h2>Welcome to Rivan!</h2>
                <p>Harness our Smart Building Automation System for easy control and comfort</p>

                <div class="forecast">
                    <div class="forecastLeft">
                        <div class="temperature">
                            <span class="tempValue">--</span>
                            <div class="itemGroup">
                                <span class="tempUnits">°C</span><br>
                                <img src="../assets/images/cloud.png" alt="Cloud icon" class="cloudIcon" />
                            </div>
                        </div>
                        <div class="weatherDescription">
                            <span class="description">Loading...</span>
                        </div>
                    </div>

                    <div class="forecastRight">
                        <div class="weather">
                            <div class="precipitation">Precipitation: --%</div>
                            <br>
                            <div class="humidity">Humidity: --%</div>
                            <br>
                            <div class="wind">Wind: -- km/h</div>
                        </div>
                    </div>
                </div>

                <div class="dashboardLog">
                    <div class="headerLog">
                        <p>User Activity Log</p>
                        <img src="../assets/images/next.png" alt="next icon" class="next" />
                    </div>

                    <table class="userLogTable">
                        <tr>
                            <th>User Name</th>
                            <th>Timestamp</th>
                        </tr>
                        <tr>
                            <td class="userLog">
                                <img src="../assets/images/defaultProfile.png" alt="User Icon" class="userIcon" />
                                <span class="userName">John Doe</span>
                            </td>
                            <td class="userTime">
                                <span class="logTime">10:45 AM</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="userLog">
                                <img src="../assets/images/defaultProfile.png" alt="User Icon" class="userIcon" />
                                <span class="userName">Jane Smith</span>
                            </td>
                            <td class="userTime">
                                <span class="logTime">10:50 AM</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="userLog">
                                <img src="../assets/images/defaultProfile.png" alt="User Icon" class="userIcon" />
                                <span class="userName">Michael Johnson</span>
                            </td>
                            <td class="userTime">
                                <span class="logTime">10:55 AM</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="userLog">
                                <img src="../assets/images/defaultProfile.png" alt="User Icon" class="userIcon" />
                                <span class="userName">Emily Davis</span>
                            </td>
                            <td class="userTime">
                                <span class="logTime">11:00 AM</span>
                            </td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </table>
                </div>
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