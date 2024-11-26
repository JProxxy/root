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
</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="dashboardDevider">
            <div class="dashboardDeviderLeft">
                <!-- Dropdown Menu -->
                <select id="office-select" class="officeDropdown">
                    <option value="firstFloor" selected>First Floor</option>
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
                            <select class="lightDropdown" id="lightCategory" onchange="updateLightState()">
                                <option value="FFLightOne">Lights 1</option>
                                <option value="FFLightTwo">Lights 2</option>
                                <option value="FFLightThree">Lights 3</option>
                                <option value="FFLightFour">Lights 4</option>
                                <option value="FFLightFive">Lights 5</option>
                                <option value="FFLightSix">Lights 6</option>
                            </select>
                            <p id="lightName">Lights</p>
                            <span>Room 1</span>

                            <div class="switch-container" id="switch_FFLightOne">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_FFLightOne"
                                        onchange="toggleLightSwitch('FFLightOne')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_FFLightTwo">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_FFLightTwo"
                                        onchange="toggleLightSwitch('FFLightTwo')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_FFLightThree">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_FFLightThree"
                                        onchange="toggleLightSwitch('FFLightThree')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_FFLightFour">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_FFLightFour"
                                        onchange="toggleLightSwitch('FFLightFour')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_FFLightFive">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_FFLightFive"
                                        onchange="toggleLightSwitch('FFLightFive')">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="switch-container" id="switch_FFLightSix">
                                <label class="switch">
                                    <input type="checkbox" id="lightSwitch_FFLightSix"
                                        onchange="toggleLightSwitch('FFLightSix')">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="airConditionFF">
                            <img src="../assets/images/ac.png" alt="Air Condition" class="airconImage">
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

            // Function to update light switch visibility based on the selected light
            function updateLightState() {
                const selectedLight = document.getElementById('lightCategory').value; // Get selected light ID from dropdown
                const allLightSwitches = document.querySelectorAll('.switch-container'); // Select all light switches

                // Hide all light switches
                allLightSwitches.forEach(switchContainer => {
                    switchContainer.style.display = 'none';
                });

                // Show the switch for the selected light
                const selectedSwitch = document.getElementById('switch_' + selectedLight);
                if (selectedSwitch) {
                    selectedSwitch.style.display = 'block';
                }
            }

            // Initially hide all light switches when the page loads
            document.addEventListener('DOMContentLoaded', function () {
                updateLightState(); // Hide all light switches initially
            });

            
            // Function to toggle light state
            function toggleLightSwitch(lightId) {
                const lightSwitch = document.getElementById('lightSwitch_' + lightId);
                const status = lightSwitch.checked ? 'ON' : 'OFF'; // Capture the status based on checkbox state
                console.log(lightId + " turned " + status); // Debugging in the console

                // Prepare the data to send to the Lambda API via API Gateway in the required format
                const requestData = {
                    body: JSON.stringify({
                        data: {
                            deviceName: lightId,  // Sending deviceName
                            command: status      // Sending the command (ON/OFF)
                        }
                    })
                };

                // Make the fetch request to the API Gateway endpoint to control the device
                fetch('https://y9saie9s20.execute-api.ap-southeast-1.amazonaws.com/dev/controlDevice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData) // Send the data in the required format
                })
                    .then(response => response.json()) // Handle the response from Lambda
                    .then(responseData => {
                        console.log('Device control response:', responseData);
                    })
                    .catch(error => {
                        console.error("Error updating device status:", error);
                    });
            }

            // Function to toggle aircon state
            function toggleAirconFF() {
                const airconSwitch = document.getElementById('airconFFSwitch');
                const status = airconSwitch.checked ? 'ON' : 'OFF'; // Capture air conditioner status
                console.log('Air Conditioner FF status:', status);

                // Prepare the data to send to the API Gateway in the required format
                const requestData = {
                    body: JSON.stringify({
                        data: {
                            deviceName: 'AirconFF',  // AirconFF device name
                            command: status          // Sending the command (ON/OFF)
                        }
                    })
                };

                // Send the data to Lambda API via API Gateway
                fetch('https://y9saie9s20.execute-api.ap-southeast-1.amazonaws.com/dev/controlDevice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData)
                })
                    .then(response => response.json())
                    .then(responseData => {
                        console.log('Aircon control response:', responseData);
                    })
                    .catch(error => {
                        console.error("Error updating aircon status:", error);
                    });
            }

            function navigateToGarage() {
                window.location.href = 'FirstFloor-Garage.php';
            }

            function navigateToOutdoor() {
                window.location.href = 'FirstFloor-Outdoor.php';
            }
        </script>
    </div>
</body>

</html>