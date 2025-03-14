<?php
session_start(); // Must be at the very top
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
                document.addEventListener("DOMContentLoaded", async function () {
                    const container = document.getElementById("dashboardDevider3d");

                    // PHP variable to get the model path
                    const modelPath = "<?php echo '../assets/models/MainBuilding.glb'; ?>";
                    console.log('Model Path:', modelPath);

                    try {
                        // Use caching to try and speed up the request
                        const response = await fetch(modelPath, { cache: 'force-cache' });
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        const blob = await response.blob();

                        // Create a local URL for the model file
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
                        // Add a poster image to display while the model loads (replace with your image)
                        modelViewer.setAttribute("poster", "../assets/images/loading-model.jpg");
                        modelViewer.style.width = "100%";
                        modelViewer.style.height = "820px";
                        modelViewer.style.position = 'relative'; // For glow effect positioning

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
                    } catch (error) {
                        console.error("Error loading model:", error);
                    }
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

                        response.reverse().forEach((notif) => { // Reverse to maintain order
                            let notifClass = `notif-item ${notif.type}`;
                            let notifHtml = `
                        <div class="${notifClass}" style="display: none;"> 
                            <span class="close-btn">&times;</span> 
                            <strong>${notif.title}</strong>
                            <p>${notif.message}</p>
                        </div>
                    `;

                            // **Check if notification already exists**
                            if (!$('.notifCont').find(`.notif-item:contains("${notif.message}")`).length) {
                                let $newNotif = $(notifHtml);

                                // **Add new notification on top**
                                $('.notifCont').prepend($newNotif);

                                // **Animate only if it’s a new notification**
                                if (!$newNotif.is(':visible')) {
                                    $newNotif.slideDown(400); // **Smooth slide-in animation**
                                }
                            }
                        });

                        // **Keep only the latest 10 notifications**
                        $('.notif-item').slice(10).fadeOut(300, function () { $(this).remove(); });
                    },
                    error: function () {
                        console.error("Failed to load notifications.");
                    }
                });
            }

            // **Close button animation**
            $('.notifCont').on('click', '.close-btn', function () {
                $(this).parent().fadeOut(300, function () {
                    $(this).remove();
                });
            });

            // **Initial load without animation**
            loadNotifications();

            // **Fetch new notifications every 10 seconds**
            setInterval(loadNotifications, 10000);
        });


    </script>



</body>

</html>