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
   <!-- Load three.js -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>

<!-- Load necessary additional files (GLTFLoader, OrbitControls, RGBELoader) -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/RGBELoader.js"></script>

<script>
 document.addEventListener('DOMContentLoaded', () => {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(90, window.innerWidth / window.innerHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ alpha: true });
    const container = document.querySelector('.dashboardDeviderLeft');

    if (!container) {
        console.error("Container element not found");
        return;
    }

    // Set renderer size and append it to the container
    const containerWidth = container.offsetWidth;
    const containerHeight = container.offsetHeight;
    renderer.setSize(containerWidth, containerHeight);
    container.appendChild(renderer.domElement);

    // Set background to transparent
    renderer.setClearColor(0x000000, 0); // Transparent background (alpha = 0)

    // Load HDRI texture using RGBELoader
    const rgbeLoader = new THREE.RGBELoader();
    rgbeLoader.load('../assets/models/HDRI/venice_dawn_1_4k.hdr', (texture) => {
        texture.mapping = THREE.EquirectangularRefractionMapping;

        // Use the HDRI for reflections and lighting, but don't set it as the scene background
        scene.environment = texture;

        // Adjust model material properties for environment map
        if (model) {
            model.traverse((child) => {
                if (child.isMesh) {
                    child.material.envMap = texture; // Apply the environment map to materials
                }
            });
        }
    });

    // Add lights to the scene with reduced intensity
    const ambientLight = new THREE.AmbientLight(0x404040, 0.5); // Lower intensity of ambient light
    scene.add(ambientLight);

    const pointLight = new THREE.PointLight(0xffffff, 0.5, 100); // Lower intensity of point light
    pointLight.position.set(5, 5, 5);
    scene.add(pointLight);

    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5); // Lower intensity of directional light
    directionalLight.position.set(0, 10, 10).normalize(); // Position it above and pointing down
    scene.add(directionalLight);

    // Load GLTF model using GLTFLoader
    const gltfLoader = new THREE.GLTFLoader();
    let model;
    gltfLoader.load('../assets/models/rivanMainBuilding.glb', (gltf) => {
        model = gltf.scene;
        scene.add(model);

        // Move the model down and adjust scale and position
        model.position.y = -22;
        model.scale.x = 1.7;
        model.position.x = 0;

        // Traverse through the model's children and adjust the material properties
        model.traverse((child) => {
            if (child.isMesh) {
                // Keep the original material, but adjust roughness and metalness
                child.material.roughness = 0.5;
                child.material.metalness = 0.1;
            }
        });
    }, undefined, (error) => {
        console.error("Error loading 3D model:", error);
    });

    // Set camera position and focus on the model
    camera.position.set(-11.34, 2.14, 20);
    camera.lookAt(0, 0, 0);

    // Enable OrbitControls for navigation
    const controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.screenSpacePanning = false;
    controls.minDistance = 2;
    controls.maxDistance = 300;

    // Animation loop
    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }

    animate();

    // Resize handler
    window.addEventListener('resize', () => {
        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;
        renderer.setSize(containerWidth, containerHeight);
        camera.aspect = containerWidth / containerHeight;
        camera.updateProjectionMatrix();
    });
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