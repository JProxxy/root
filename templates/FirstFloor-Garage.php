<?php
// FirstFloor-Garage.php

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
    <title>First Floor</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/FirstFloor-Garage.css">

    <!-- Include the MQTT logic -->
    <script src="../assets/js/mqttwss.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aws-iot-device-sdk/2.3.3/aws-iot-device-sdk.min.js"></script>

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
                    <button onclick="navigateToGarage()" class="roomButton activeButton"
                        id="garageButton">Garage</button>
                    <button onclick="navigateToOutdoor()" class="roomButton" id="outdoorButton">Outdoor</button>
                </div>

            </div>
            <div class="dashboardDeviderRight">
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
                    <!-- Sample log data -->
                    <table class="ffLogTable">
                        <tr>
                            <td class="ffuserTime"><span class="fflogTime">10:45 AM</span></td>
                            <td class="ffuserLog"><span class="ffuserDid">Access gate was Closed</span></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="line-with-circle"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="ffuserTime"><span class="fflogTime">10:50 AM</span></td>
                            <td class="ffuserLog"><span class="ffuserDid">Camera was Opened</span></td>
                        </tr>
                    </table>
                </div>

                <div class="deviceControl">
                    <p>Devices</p>
                    <div class="devices">
                        <div class="lights">
                            <img src="../assets/images/lights.png" alt="Lights" class="lightsImage">
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
                            <!-- Add more light switches as needed -->
                        </div>

                        <div class="airConditionFF">
                            <img src="../assets/images/ac.png" alt="Camera" class="airconImage">
                            <p>Air Condition</p>
                            <span>Room 1</span>

                            <div class="switch-containerTwo">
                                <label class="switchTwo">
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
            // Your existing JavaScript logic for toggling devices
            function navigateToOutdoor() {
                document.getElementById("outdoorButton").classList.remove("activeButton");
                document.getElementById("garageButton").classList.add("activeButton");
            }

            function navigateToGarage() {
                document.getElementById("garageButton").classList.remove("activeButton");
                document.getElementById("outdoorButton").classList.add("activeButton");
            }

            function updateLightState() {
                const dropdown = document.getElementById('lightCategory');
                const selectedLight = dropdown.value;

                // Format the light name to "Lights #1", "Lights #2", etc.
                const formattedLightName = `Lights #${selectedLight.charAt(6)}`;  // Extracts the number and formats it

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
                    switchContainer.style.display = 'none';  // Hide all switches initially
                    switchContainer.style.textAlign = 'left';  // Reset alignment
                });

                // Show the switch container corresponding to the selected light category
                const switchToShow = document.getElementById('switch_' + selectedLight);
                if (switchToShow) {
                    switchToShow.style.display = 'block';  // Show the switch
                    switchToShow.style.textAlign = 'right';  // Align switch to the right

                    const switchElement = switchToShow.querySelector('input');
                    switchElement.checked = lightStates[selectedLight];
                }
            }


            // Toggle light state and publish MQTT message
            function toggleLightSwitch(lightId) {
                const lightSwitch = document.getElementById(`lightSwitch_${lightId}`);
                const message = lightSwitch.checked ? "Light is ON" : "Light is OFF";
                publishMessage("esp32/pub", message);
                console.log(`Toggled ${lightId}: ${message}`);
            }

            // Toggle air conditioning state and publish MQTT message
            function toggleAirconFF() {
                const airconSwitch = document.getElementById('airconFFSwitch');
                const state = airconSwitch.checked ? "ON" : "OFF";
                const message = JSON.stringify({ aircon: "FF", state: state });
                publishMessage("esp32/pub", message);
                console.log(`Aircon FF: ${state}`);
            }

            // Function to update the light state dropdown value
            function updateLightState() {
                const selectedLight = document.getElementById('lightCategory').value;
                console.log(`Selected light: ${selectedLight}`);
            }

            // Publish message to MQTT topic using mqttwss.js
            function publishMessage(topic, message) {
                // Assuming mqttClient is already connected and available globally
                if (mqttClient && mqttClient.connected) {
                    mqttClient.publish(topic, message, (err) => {
                        if (err) {
                            console.error("Publish failed:", err);
                        } else {
                            console.log(`Message published to topic ${topic}: ${message}`);
                        }
                    });
                } else {
                    console.error("MQTT client is not connected!");
                }
            }

        </script>
    </div>
</body>

</html>