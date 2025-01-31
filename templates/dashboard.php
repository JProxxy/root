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
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer();
            const container = document.querySelector('.dashboardDeviderLeft');

            // Add lights to the scene
            const ambientLight = new THREE.AmbientLight(0x404040, 2); // Soft white light
            scene.add(ambientLight);

            const pointLight = new THREE.PointLight(0xffffff, 1, 100); // Point light
            pointLight.position.set(5, 5, 5); // Position of the light
            scene.add(pointLight);

            // Ensure the container is found
            if (!container) {
                console.error("Container element not found");
                return;
            }

            // Resize renderer to fit the container size
            const containerWidth = container.offsetWidth;
            const containerHeight = container.offsetHeight;
            renderer.setSize(containerWidth, containerHeight);
            container.appendChild(renderer.domElement);

            // Check if GLTFLoader is loaded and accessible
            if (typeof THREE.GLTFLoader === 'undefined') {
                console.error("GLTFLoader is not available");
                return;
            }

            // Load a 3D model (e.g., .glb or .gltf)
            const loader = new THREE.GLTFLoader();
            loader.load('../assets/models/rivanMainBuilding.glb', (gltf) => {
                scene.add(gltf.scene);
            }, undefined, (error) => {
                console.error("Error loading 3D model:", error);
            });

            // Adjust camera's position to move it further back from the object
            camera.position.z = 10;  // Move the camera back

            // Animation loop
            function animate() {
                requestAnimationFrame(animate);

                // Commented out rotation to stop the model from rotating
                // scene.rotation.x += 0.01;
                // scene.rotation.y += 0.01;

                renderer.render(scene, camera);
            }

            animate();

            // Adjust the renderer size when the window is resized
            window.addEventListener('resize', () => {
                const containerWidth = container.offsetWidth;
                const containerHeight = container.offsetHeight;
                renderer.setSize(containerWidth, containerHeight);
                camera.aspect = containerWidth / containerHeight;
                camera.updateProjectionMatrix();
            });

            // Optional: Add OrbitControls for better interaction
            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;  // Smooth movement
            controls.dampingFactor = 0.25;  // Adjust damping speed
            controls.screenSpacePanning = false;  // Disable panning
            controls.maxPolarAngle = Math.PI / 2;  // Limit vertical rotation
        });

    </script>


</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="dashboardDevider">
            <div class="dashboardDeviderLeft">
                <!-- 3D Model will be rendered here -->
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