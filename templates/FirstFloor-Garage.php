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
    <!-- <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script>
    <script src="../assets/js/mqttwss.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aws-iot-device-sdk/2.3.3/aws-iot-device-sdk.min.js"></script> -->

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
                                <option value="FFLightOne">Lights 1</option>
                                <option value="FFLightTwo">Lights 2</option>
                                <option value="FFLightThree">Lights 3</option>
                                <option value="FFLightFour">Lights 4</option>
                                <option value="FFLightFive">Lights 5</option>
                                <option value="FFLightSix">Lights 6</option>
                            </select>
                            <p id="lightName">Lights</p>
                            <span>Room 1</span>

                            <div class="switch-container" id="switch_lights1">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_lights1"
                                        onchange="toggleLightSwitch('FFLightOne')">
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
            // Function to navigate between rooms
            function navigateToOutdoor() {
                document.getElementById("outdoorButton").classList.remove("activeButton");
                document.getElementById("garageButton").classList.add("activeButton");
            }

            function navigateToGarage() {
                document.getElementById("garageButton").classList.remove("activeButton");
                document.getElementById("outdoorButton").classList.add("activeButton");
            }

            // Function to toggle light state
            function toggleLightSwitch(lightId) {
                const lightSwitch = document.getElementById('lightSwitch_' + lightId);
                const status = lightSwitch.checked ? 'ON' : 'OFF'; // Capture the status based on checkbox state

                console.log(lightId + " turned " + status); // Debugging in the console

                // Prepare the data to send to the server
                const data = new FormData();
                data.append('device_id', lightId); // Pass the light ID
                data.append('status', status); // Pass the status (ON/OFF)

                // Make the AJAX request to the update_device_status.php script
                fetch('../app/update_device_status.php', {
                    method: 'POST',
                    body: data
                })
                    .then(response => response.text()) // Handle the response from PHP
                    .then(responseText => {
                        console.log(responseText); // Display response from the server
                    })
                    .catch(error => {
                        console.error("Error updating device status:", error);
                    });
            }


            // Function to load light states (could be from backend or local storage)
            function loadLightState() {
                return {
                    "FFLightOne": false,
                    "FFLightTwo": true,
                    "FFLightThree": false,
                    "FFLightFour": true,
                    "FFLightFive": false,
                    "FFLightSix": true
                };
            }

            // Function to update the light display based on selected light
            function updateLightState() {
                const dropdown = document.getElementById('lightCategory');
                const selectedLight = dropdown.value;

                const formattedLightName = selectedLight.replace("FFLight", "Lights ").replace("One", "1").replace("Two", "2").replace("Three", "3").replace("Four", "4").replace("Five", "5").replace("Six", "6");

                const lightNameElement = document.querySelector('.lights p');
                if (lightNameElement) {
                    lightNameElement.textContent = formattedLightName;
                }

                const lightStates = loadLightState();

                const allSwitches = document.querySelectorAll('.switch-container');
                allSwitches.forEach(switchContainer => {
                    switchContainer.style.display = 'none';
                    switchContainer.style.textAlign = 'left';
                });

                const switchToShow = document.getElementById('switch_' + selectedLight);
                if (switchToShow) {
                    switchToShow.style.display = 'block';
                    switchToShow.style.textAlign = 'right';

                    const switchElement = switchToShow.querySelector('input');
                    switchElement.checked = lightStates[selectedLight];
                }
            }
        </script>

    </div>
</body>

</html>