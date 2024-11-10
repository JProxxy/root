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
    <link rel="stylesheet" href="../assets/css/officeSpace.css">


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
                    <button onclick="navigateToGarage()" class="roomButton" id="garageButton">Garage</button>
                    <button onclick="navigateToOutdoor()" class="roomButton activeButton"
                        id="outdoorButton">Outdoor</button>
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
                        <div class="accessGate">
                            <img src="../assets/images/accessGate.png" alt="Access Gate" class="accessGateImage">
                            <p>Access Gate</p>
                            <span>Outdoor</span>

                            <div class="switch-container">
                                <label class="switch">
                                    <input type="checkbox" id="accessGateSwitch" onchange="toggleAccessGate()">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>


                        <div class="cameraFF">
                            <img src="../assets/images/camera.png" alt="Camera" class="cameraImage">
                            <p>Camera</p>
                            <span>Outdoor</span>

                            <div class="switch-container">
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script>


        <script>
            function navigateToOutdoor() {
                // Change background color of Outdoor button
                document.getElementById("outdoorButton").classList.add("activeButton");
                document.getElementById("garageButton").classList.remove("activeButton");
            }

            function navigateToGarage() {
                // Reset Outdoor button and change background for Garage button
                document.getElementById("garageButton").classList.add("activeButton");
                document.getElementById("outdoorButton").classList.remove("activeButton");
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
            async function toggleAccessGate() {
                const accessGateSwitch = document.getElementById('accessGateSwitch');
                const newState = accessGateSwitch.checked ? 1 : 0;

                // Publish the state change to the MQTT topic
                client.publish('home/office/accessGate', JSON.stringify({ state: newState }));

                console.log('Access Gate state:', newState);
            }

            // Add event listener to trigger the toggleAccessGate function when the checkbox changes
            document.getElementById('accessGateSwitch').addEventListener('change', toggleAccessGate);

        </script>



    </div>
</body>

</html>