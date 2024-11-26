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
            // Function to toggle light state
            function toggleLightSwitch(lightId) {
                const lightSwitch = document.getElementById('lightSwitch_' + lightId);
                const status = lightSwitch.checked ? 'ON' : 'OFF'; // Capture the status based on checkbox state
                console.log(lightId + " turned " + status); // Debugging in the console

                // Prepare the data to send to the Lambda API via API Gateway
                const data = {
                    lightId: lightId,
                    status: status
                };

                // Make the fetch request to the API Gateway endpoint to control the device
                fetch('https://y9saie9s20.execute-api.ap-southeast-1.amazonaws.com/dev/controlDevice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data) // Send the data as a JSON string
                })
                    .then(response => response.json()) // Handle the response from Lambda
                    .then(responseData => {
                        console.log('Device control response:', responseData);
                    })
                    .catch(error => {
                        console.error("Error updating device status:", error);
                    });
            }

            // Function to load light states from the backend
            function loadLightState() {
                // Fetch the current light states from the backend via API Gateway
                return fetch('https://y9saie9s20.execute-api.ap-southeast-1.amazonaws.com/dev/getDeviceStates') // Assuming you have an endpoint for this
                    .then(response => response.json()) // Try to parse the JSON response
                    .then(data => {
                        if (data.error) {
                            console.error("Error loading device states:", data.error);
                            return {}; // Return an empty object if there's an error
                        }
                        return data; // If no error, return the device states
                    })
                    .catch(error => {
                        console.error("Error loading device states:", error);
                        return {}; // Return an empty object if there's an error
                    });
            }

            // Function to update the light display based on selected light
            async function updateLightState() {
                const dropdown = document.getElementById('lightCategory');
                const selectedLight = dropdown.value;

                const formattedLightName = selectedLight.replace("FFLight", "Lights ").replace("One", "1").replace("Two", "2").replace("Three", "3").replace("Four", "4").replace("Five", "5").replace("Six", "6");

                const lightNameElement = document.querySelector('.lights p');
                if (lightNameElement) {
                    lightNameElement.textContent = formattedLightName;
                }

                // Wait for the light states to load
                const lightStates = await loadLightState(); // Wait for the data from the server

                const allSwitches = document.querySelectorAll('.switch-container');
                allSwitches.forEach(switchContainer => {
                    switchContainer.style.display = 'none'; // Hide all switches initially
                    switchContainer.style.textAlign = 'left';
                });

                // Display the selected switch and set its state based on the loaded data
                const switchToShow = document.getElementById('switch_' + selectedLight);
                if (switchToShow) {
                    switchToShow.style.display = 'block';
                    switchToShow.style.textAlign = 'right';

                    const switchElement = switchToShow.querySelector('input');
                    switchElement.checked = lightStates[selectedLight]; // Set the switch state based on the loaded data
                }
            }

            // Call the function to load the light states when the page loads
            updateLightState();

            // Load the initial state
            updateLightState();
        </script>


    </div>
</body>

</html>