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
    <title>Outdoor Camera</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/FirstFloor-Outdoor.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script type="module" src="https://unpkg.com/@google/model-viewer"></script>

</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="dashboardDevider">
            <div class="dashboardDeviderLeft">
                <div class="dropdownCont">


                    <div class="custom-dropdown">
                        <div class="dropdown-btn">
                            <span id="dropdownText">First Floor</span>
                            <div class="iconddcont">
                                <img src="../assets/images/icon-dropdown.png" alt="IconDropDown" class="IconDropDown">
                            </div>
                        </div>
                        <div class="dropdown-list">
                            <div class="dropdown-item" data-value="firstFloor">
                                First Floor
                            </div>
                            <div class="dropdown-item" data-value="secondFloor">
                                Second Floor
                            </div>
                            <div class="dropdown-item" data-value="thirdFloor">
                                Third Floor
                            </div>
                            <div class="dropdown-item" data-value="fourthFloor">
                                Fourth Floor
                            </div>
                            <div class="dropdown-item" data-value="fifthFloor">
                                Roof Top
                            </div>
                        </div>
                    </div>

                    <script>
                        // Toggle dropdown visibility
                        document.querySelector('.dropdown-btn').addEventListener('click', function () {
                            this.parentElement.classList.toggle('open');
                        });

                        // Handle item selection and update text dynamically
                        document.querySelectorAll('.dropdown-item').forEach(function (item) {
                            item.addEventListener('click', function () {
                                var selectedValue = this.getAttribute('data-value');
                                var floorLinks = {
                                    "firstFloor": "../templates/FirstFloor-Outdoor.php",
                                    "secondFloor": "../templates/secondFloor.php",
                                    "thirdFloor": "../templates/thirdFloor.php",
                                    "fourthFloor": "../templates/fourthFloor.php",
                                    "fifthFloor": "../templates/fifthFloor.php"
                                };

                                // Update dropdown text with the selected floor name
                                var dropdownText = {
                                    "firstFloor": "First Floor",
                                    "secondFloor": "Second Floor",
                                    "thirdFloor": "Third Floor",
                                    "fourthFloor": "Fourth Floor",
                                    "fifthFloor": "Fifth Floor"
                                };

                                // Set the selected floor text in the dropdown
                                document.getElementById("dropdownText").innerText = dropdownText[selectedValue] || "Select Floor";

                                // Redirect to the corresponding URL
                                if (floorLinks[selectedValue]) {
                                    window.location.href = floorLinks[selectedValue];
                                }
                            });
                        });
                    </script>

                </div>
                <div class="firstFloor">

                </div>



            </div>
            <div class="dashboardDeviderRight">
                <!-- Search Bar with Icon Inside -->
                <!-- <div class="searchContainer">
                    <input type="text" id="searchInput" placeholder=" " class="searchInput">
                    <button onclick="performSearch()" class="searchButton">
                        <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div> -->

                <div class="firstFloorLog">
                    <button id="downloadExcel" class="downloadBtn">
                        <span class="material-icons">download</span>
                    </button>

                    <!-- Notifications will be inserted here dynamically -->
                </div>

                <div class="deviceControl">
                    <p class="devTitle">Devices</p>
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
                            <a href="http://192.168.100.36/">
                                <img src="../assets/images/camera.png" alt="Camera" class="cameraImage">
                            </a>

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

            function navigateToGarage(url) {
                window.location.href = "../templates/FirstFloor-Garage.php";
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

        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


        <script>
            document.addEventListener("DOMContentLoaded", function () {
                loadNotifications();
            });

            function loadNotifications() {
                fetch("../scripts/logs_firstFloor_Garage.php")
                    .then(response => response.json())
                    .then(data => {
                        let logContainer = document.querySelector(".firstFloorLog");

                        // Ensure the button exists and is properly placed
                        let downloadButton = document.getElementById("downloadExcel");
                        if (!downloadButton) {
                            downloadButton = document.createElement("button");
                            downloadButton.id = "downloadExcel";
                            downloadButton.className = "downloadBtn";
                            downloadButton.innerHTML = '<span class="material-icons">download</span>';
                            logContainer.appendChild(downloadButton);
                        }

                        // Make sure the event listener is attached
                        downloadButton.addEventListener("click", downloadExcel);

                        let logHTML = '<table class="ffLogTable">';
                        data.reverse().forEach((notif, index) => {
                            logHTML += `
                <tr>
                    <td class="ffuserTime"><span class="fflogTime">${notif.time}</span></td>
                    <td class="ffuserLog"><span class="ffuserDid">${notif.message}</span></td>
                </tr>`;

                            if (index !== data.length - 1) {
                                logHTML += `
                <tr>
                    <td colspan="2">
                        <div class="line-with-circle"></div>
                    </td>
                </tr>`;
                            }
                        });

                        logHTML += "</table>";

                        // Remove old logs but keep the button
                        let existingLogs = logContainer.querySelector("table");
                        if (existingLogs) {
                            existingLogs.remove();
                        }
                        logContainer.insertAdjacentHTML("beforeend", logHTML);
                    })
                    .catch(error => console.error("Error fetching notifications:", error));
            }

            function downloadExcel() {
                fetch("../scripts/logs_firstFloor_Garage.php")
                    .then(response => response.json())
                    .then(data => {
                        let worksheetData = [["Time", "Message"]]; // Header row
                        data.forEach(log => worksheetData.push([log.time, log.message]));

                        let ws = XLSX.utils.aoa_to_sheet(worksheetData);
                        let wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, "Logs_FirstFloor_Garage");

                        XLSX.writeFile(wb, "Logs_FirstFloor_Garage.xlsx");
                    })
                    .catch(error => console.error("Error fetching data:", error));
            }
        </script>

    </div>
</body>

</html>