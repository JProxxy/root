<?php
session_start(); // Ensure session starts

// Check if user is logged in by checking if 'user_id' session variable is set
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php");
    exit();
}

// Get the role_id if the user is logged in
$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : null;
echo "Role ID: " . $_SESSION['role_id']; 
// Now you can use $role_id to conditionally display content
if ($role_id == 1) {
    // Show content for role 1
    echo "You are role 1";
} else {
    // Show content for other roles or deny access
    echo "Access denied for this role.";
}
// $user_id = $_SESSION['user_id'];
// echo "Welcome, " . $_SESSION['user_name']; // Debug: Check if session works
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Load three.js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script type="module" src="https://unpkg.com/@google/model-viewer"></script>


</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="dashboardDevider">
            <div class="dashboardDeviderLeft" id="dashboardDevider3d">
                <!-- 3D Model will be rendered here -->
            </div>
            <div id="dashboardDevider3d"></div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const container = document.getElementById("dashboardDevider3d");

                    // PHP variable to get the model path
                    const modelPath = "<?php echo '../assets/models/MainBuilding.glb'; ?>";

                    // Check if the modelPath is correct
                    console.log('Model Path:', modelPath);

                    // Check if the model path is valid
                    fetch(modelPath)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            const url = URL.createObjectURL(blob);

                            // Create the <model-viewer> element dynamically
                            const modelViewer = document.createElement("model-viewer");
                            modelViewer.setAttribute("src", url);
                            modelViewer.setAttribute("auto-rotate", "");
                            modelViewer.setAttribute("camera-controls", "");
                            modelViewer.setAttribute("shadow-intensity", "1");
                            modelViewer.setAttribute("exposure", ".45");
                            modelViewer.setAttribute("environment-image", "neutral");
                            modelViewer.setAttribute("ar", "");
                            modelViewer.setAttribute("disable-tap", "");
                            modelViewer.style.width = "100%";
                            modelViewer.style.height = "820px";
                            modelViewer.style.position = 'relative'; // Ensure it's positioned relative for glow effect positioning

                            // Append the model-viewer to the container
                            container.appendChild(modelViewer);

                            // Function to create glowing light trails
                            const createGlowEffect = (x, y) => {
                                const glow = document.createElement('div');
                                const size = Math.random() * 6 + 4; // Random size between 4 and 10px
                                const color = `rgba(255, 255, 255, 0.8)`; // White glow
                                const animationDuration = Math.random() * 0.4 + 0.5; // Random duration between 0.5 and 0.9 seconds

                                glow.style.position = 'absolute';
                                glow.style.left = `${x - size / 2}px`;
                                glow.style.top = `${y - size / 2}px`;
                                glow.style.width = `${size}px`;
                                glow.style.height = `${size}px`;
                                glow.style.backgroundColor = color;
                                glow.style.borderRadius = '50%';
                                glow.style.pointerEvents = 'none';
                                glow.style.animation = `glowAnimation ${animationDuration}s ease-out forwards`;
                                modelViewer.appendChild(glow);

                                // Remove the glow element after the animation completes
                                setTimeout(() => {
                                    glow.remove();
                                }, animationDuration * 1000);
                            };

                            // Add mousemove event to create glowing light trails
                            modelViewer.addEventListener('mousemove', (event) => {
                                const rect = modelViewer.getBoundingClientRect();
                                const mouseX = event.clientX - rect.left;
                                const mouseY = event.clientY - rect.top;
                                createGlowEffect(mouseX, mouseY);
                            });
                        })
                        .catch(error => {
                            console.error("Error loading model:", error);
                        });
                });
            </script>

            <div class="dashboardDeviderRight">
                <h2>Welcome to Rivan!</h2>
                <p>Harness our Smart Building Automation System for easy control and comfort</p>

                <div class="scrollable" id="style-2">
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

                    <div class="notifPage">
                        <h3>Notification</h3>

                        <div class="notifCont">

                        </div>

                    </div>

                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3 && $_SESSION['role_id'] != 4): ?>
                        <div class="dashboardLog">
                            <div class="headerLog">
                                <p>User Activity Log</p>
                                <a href="../templates/userActLogPage.php">
                                    <img src="../assets/images/next.png" alt="next icon" class="next" />
                                </a>
                            </div>

                            <table class="userLogTable">
                                <tr>
                                    <th>User Name</th>
                                    <th>Timestamp</th>
                                </tr>
                                <tbody id="latestUserActivities">
                                    <!-- Latest 4 user activities will be inserted here dynamically -->
                                </tbody>
                            </table>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                fetch('../scripts/userActLogDASHBOARD.php') // Replace with actual PHP file path
                                    .then(response => response.json())
                                    .then(data => {
                                        if (Array.isArray(data)) {
                                            const activityContainer = document.getElementById('latestUserActivities');
                                            activityContainer.innerHTML = ''; // Clear previous data

                                            data.forEach(user => {
                                                const row = document.createElement('tr');
                                                row.innerHTML = `
                            <td class="userLog">
                                <img src="${user.profile_picture}" alt="User Icon" class="userIcon" />
                                <span class="userName">${user.username}</span>
                            </td>
                            <td class="userTime">
                                <span class="logTime">${user.timestamp}</span>
                            </td>
                        `;
                                                activityContainer.appendChild(row);
                                            });
                                        } else {
                                            console.error("Error: Invalid data format", data);
                                        }
                                    })
                                    .catch(error => console.error('Error fetching user activities:', error));
                            });
                        </script>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            let isWebkit = navigator.userAgent.toLowerCase().includes("webkit");

            if (!isWebkit) {
                $('.wrapper').html('<p>Sorry! Non-webkit users. :(</p>');
            }
        });

    </script>

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

    <script>
        $(document).ready(function () {
            function loadNotifications() {
                $.ajax({
                    url: '../scripts/fetch_notifs.php', // Ensure correct PHP file path
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (!response || response.length === 0) {
                            return;
                        }

                        // Clear container if you want to remove all old notifications on every load
                        // $('.notifCont').empty();

                        // Process notifications in reverse order so the newest appears on top
                        response.reverse().forEach((notif) => {
                            // Use the type provided from backend; default to 'info' if missing
                            let type = notif.type ? notif.type : 'info';
                            let notifClass = `notif-item ${type}`;
                            let notifHtml = `
                        <div class="${notifClass}" style="display: none;"> 
                            <span class="close-btn">&times;</span> 
                            <strong>${notif.title}</strong>
                            <p>${notif.message}</p>
                        </div>
                    `;

                            // Check if the notification already exists to avoid duplicates
                            if (!$('.notifCont').find(`.notif-item:contains("${notif.message}")`).length) {
                                let $newNotif = $(notifHtml);
                                // Prepend so new notifications appear on top
                                $('.notifCont').prepend($newNotif);
                                // Animate the notification (slide down)
                                if (!$newNotif.is(':visible')) {
                                    $newNotif.slideDown(400);
                                }
                            }
                        });

                        // Keep only the latest 10 notifications (if more than 10, remove the extras)
                        $('.notif-item').slice(10).fadeOut(300, function () { $(this).remove(); });
                    },
                    error: function () {
                        console.error("Failed to load notifications.");
                    }
                });
            }

            // Close button functionality: click to fade out and remove the notification
            $('.notifCont').on('click', '.close-btn', function () {
                $(this).parent().fadeOut(300, function () {
                    $(this).remove();
                });
            });

            // Initial load of notifications
            loadNotifications();
            // Fetch new notifications every 10 seconds
            setInterval(loadNotifications, 10000);
        });
    </script>



</body>

</html>