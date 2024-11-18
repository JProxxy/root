<?php
//FirstFloor-Garage.php
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
    <link rel="stylesheet" href="../assets/css/FirstFloor-Garage.css">




</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="dashboardDevider">
            <div class="dashboardDeviderLeft">
                <!-- Dropdown Menu -->
                <select id="office-select" class="officeDropdown">
                    <option value="firstFloor" selected>First Floor</option> <!-- Set as default -->
                    <option value="secondFloor">Second Floor</option>
                    <option value="thirdFloor">Third Floor</option>
                    <option value="roofTop">Roof Top</option>
                </select>

                <div class="firstFloor">
                    <img src="../assets/images/firstFloor.png" alt="firstFloor" class="firstFloor">
                </div>

                <div class="room">
                    <!-- "Garage" button starts with the activeButton class to indicate it's the default -->
                    <button onclick="navigateToGarage()" class="roomButton activeButton"
                        id="garageButton">Garage</button>
                    <button onclick="navigateToOutdoor()" class="roomButton" id="outdoorButton">Outdoor</button>
                </div>

            </div>
            <div class="dashboardDeviderRight">
                <!-- Search Bar with Icon Inside -->
                <div class="searchContainer">
                    <input type="text" id="searchInput" placeholder=" " class="searchInput">
                    <button onclick="performSearch()" class="searchButton">
                        <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>

                <div class="firstFloorLog">
                    <table class="ffLogTable">
                        <tr>
                            <td class="ffuserTime">
                                <span class="fflogTime">10:45 AM</span>
                            </td>
                            <td class="ffuserLog">
                                <span class="ffuserDid">Access gate was Closed</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="line-with-circle"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="ffuserTime">
                                <span class="fflogTime">10:50 AM</span>
                            </td>
                            <td class="ffuserLog">
                                <span class="ffuserDid">Camera was Opened</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="line-with-circle"></div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="deviceControl">
                    <p>Devices</p>
                    <div class="devices">

                        <div class="lights">
                            <img src="../assets/images/lights.png" alt="Lights" class="lightsImage">

                            <!-- Dropdown for Light Categories -->
                            <select class="lightDropdown" id="lightCategory">
                                <option value="lights1">Lights 1</option>
                                <option value="lights2">Lights 2</option>
                                <option value="lights3">Lights 3</option>
                                <option value="lights4">Lights 4</option>
                                <option value="lights5">Lights 5</option>
                                <option value="lights6">Lights 6</option>
                            </select>

                            <p id="lightName">Lights</p>
                            <span>Room 1</span>

                            <!-- Switch containers, all initially hidden except the selected one -->
                            <div class="switch-container" id="switch_lights1">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_lights1"
                                        onchange="toggleLightSwitch('lights1')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_lights2">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_lights2"
                                        onchange="toggleLightSwitch('lights2')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_lights3">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_lights3"
                                        onchange="toggleLightSwitch('lights3')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_lights4">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_lights4"
                                        onchange="toggleLightSwitch('lights4')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_lights5">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_lights5"
                                        onchange="toggleLightSwitch('lights5')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_lights6">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_lights6"
                                        onchange="toggleLightSwitch('lights6')">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>





                        <div class="airConditionFF">
                            <img src="../assets/images/ac.png" alt="Camera" class="airconImage">
                            <p>Air Condition</p>
                            <span>Room 1</span>

                            <div class="switch-container">
                                <label class="switch">
                                    <input type="checkbox" id="airconFFSwitch" onchange="toggleAirconFF()">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script>
            // Function to load the light states from localStorage
            function loadLightState() {
                const lightStates = JSON.parse(localStorage.getItem('lightStates')) || {
                    lights1: false,
                    lights2: false,
                    lights3: false,
                    lights4: false,
                    lights5: false,
                    lights6: false
                };
                return lightStates;
            }

            // Function to save the light states to localStorage
            function saveLightState(lightStates) {
                localStorage.setItem('lightStates', JSON.stringify(lightStates));
            }

            // Update the light state and the light name
            function updateLightState() {
                const dropdown = document.getElementById('lightCategory');
                const selectedLight = dropdown.value;

                // Format the light name to "Lights #1", "Lights #2", etc.
                const formattedLightName = Lights #${selectedLight.charAt(6)};  // Extracts the number and formats it

                // Update the <p> tag or element where the light name should appear
                const lightNameElement = document.querySelector('.lights p');
                if (lightNameElement) {
                    lightNameElement.textContent = formattedLightName;  // Update text to "Lights #1", "Lights #2", etc.
                }

                // Load the saved light states
                const lightStates = loadLightState();

                // Hide all switch containers initially
                const allSwitches = document.querySelectorAll('.switch-container');
                allSwitches.forEach(switchContainer => {
                    switchContainer.style.display = 'none';
                    switchContainer.style.textAlign = 'left';  // Reset to default position
                });

                // Show the switch container corresponding to the selected light category
                const switchToShow = document.getElementById('switch_' + selectedLight);
                if (switchToShow) {
                    switchToShow.style.display = 'block';
                    switchToShow.style.textAlign = 'right';  // Align switch to the right

                    const switchElement = switchToShow.querySelector('input');
                    switchElement.checked = lightStates[selectedLight];
                }
            }

            // Function to toggle the light switch and save the new state
            function toggleLightSwitch(lightCategory) {
                const switchElement = document.getElementById('lightSwitch_' + lightCategory);
                if (!switchElement) return;  // Prevent errors if the switch doesn't exist

                const lightStates = loadLightState();
                lightStates[lightCategory] = switchElement.checked;
                saveLightState(lightStates);
            }

            // Initialize the light states when the page loads
            document.addEventListener('DOMContentLoaded', () => {
                const dropdown = document.getElementById('lightCategory');
                if (dropdown) {
                    updateLightState();  // Set the initial state of the light name and switches
                    dropdown.addEventListener('change', updateLightState);  // Add event listener for dropdown changes
                }
            });
        </script>




        <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script>


        <script>
            function navigateToOutdoor() {
                // Change background color of Outdoor button
                document.getElementById("garageButton").classList.add("activeButton");
                document.getElementById("outdoorButton").classList.remove("activeButton");
            }

            function navigateToGarage() {
                // Reset Outdoor button and change background for Garage button
                document.getElementById("outdoorButton").classList.add("activeButton");
                document.getElementById("garageButton").classList.remove("activeButton");
            }

            function navigateToOutdoor(url) {
                window.location.href = "../templates/OfficeSpace.php";
            }


            // Set MQTT connection parameters
            const endpoint = 'wss://a36m8r0b5lz7mq-ats.iot.ap-southeast-1.amazonaws.com/mqtt';  // Replace with your IoT endpoint
            const client = mqtt.connect(endpoint, {
                clientId: 'webClient_' + Math.floor(Math.random() * 1000),  // Ensure unique client ID
                clean: true,
                reconnectPeriod: 1000,
                username: '',  // Optional if using Cognito
                password: '',  // Optional if using Cognito
                ca: '../assets/certificate/AmazonRootCA1.pem',  // Path to your root certificate
                cert: '../assets/certificate/Device Certificate.crt',  // Path to your device certificate
                key: '../assets/certificate/Private Key.key',  // Path to your private key
            });

            // Connect to the broker
            client.on('connect', function () {
                console.log('Connected to MQTT broker');
            });

            // Subscribe to a topic (optional, if you want to receive updates)
            const topic = 'home/office/accessGate';  // The topic ESP32 is subscribed to
            client.subscribe(topic, function (err) {
                if (err) {
                    console.error('Failed to subscribe:', err);
                }
            });

            // Listen for messages on the topic
            client.on('message', function (topic, message) {
                console.log('Received message:', topic, message.toString());
                // You can update the UI or trigger events based on the message
            });

            // Function to publish state change when the access gate switch is toggled
            async function toggleLightSwitch() {
                const lightSwitch = document.getElementById('lightSwitch');
                const newState = lightSwitch.checked ? 1 : 0;

                // Publish the state change to the MQTT topic
                client.publish('home/office/accessGate', JSON.stringify({ state: newState }));

                console.log('Access Gate state:', newState);
            }

            // Add event listener to trigger the toggleLightSwitch function when the checkbox changes
            document.getElementById('lightSwitch').addEventListener('change', toggleLightSwitch);

        </script>



    </div>
</body>

</html>

