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
    <link rel="stylesheet" href="../assets/css/acRemote.css">


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
                                Fifth Floor
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

                    <div class="ACRMain">
                        <div class="remote-container">
                            <img class="bgRem" src="../assets/images/ac/bgRem.png">
                            <img class="timer" src="../assets/images/ac/timer.png">
                            <img class="fan" src="../assets/images/ac/fan.png">
                            <img class="fanLow" src="../assets/images/ac/fanLow.png">
                            <img class="fanHigh" src="../assets/images/ac/fanHigh.png">
                            <img class="mode" src="../assets/images/ac/mode.png">
                            <img class="modeCool" src="../assets/images/ac/modeCool.png">
                            <img class="modeDry" src="../assets/images/ac/modeDry.png">
                            <img class="modeFan" src="../assets/images/ac/modeFan.png">
                            <img class="swing" src="../assets/images/ac/swing.png">
                            <img class="swingOn" src="../assets/images/ac/swingOn.png">
                            <img class="swingOff" src="../assets/images/ac/swingOff.png">
                            <img class="sleep" src="../assets/images/ac/sleep.png">
                            <img class="tempbar" src="../assets/images/ac/tempbar.png">
                            <img class="tempbarLow" src="../assets/images/ac/tempbarLow.png">
                            <img class="tempbarHigh" src="../assets/images/ac/tempbarHigh.png">
                        </div>
                    </div>
                    <script>
                        document.querySelectorAll(".remote-container img").forEach(img => {
                            img.addEventListener("mousedown", (e) => {
                                e.preventDefault(); // Prevents dragging

                                img.classList.add("tapped");

                                setTimeout(() => {
                                    img.classList.remove("tapped");
                                }, 300); // Remove effect after animation
                            });
                        });
                        document.querySelectorAll(".remote-container img").forEach(img => {
                            img.addEventListener("mousedown", (e) => {
                                e.preventDefault(); // Prevents dragging

                                img.classList.add("tapped");

                                // Create ice flakes ❄️
                                for (let i = 0; i < 10; i++) {
                                    let flake = document.createElement("div");
                                    flake.innerHTML = "❄️"; // Ice flake emoji
                                    flake.classList.add("ice-flake");

                                    // Random start position near tap point
                                    let x = e.clientX + (Math.random() * 50 - 25);
                                    let y = e.clientY + (Math.random() * 30 - 15);
                                    flake.style.left = `${x}px`;
                                    flake.style.top = `${y}px`;

                                    document.body.appendChild(flake);

                                    // Remove flakes after animation
                                    setTimeout(() => {
                                        flake.remove();
                                    }, 1500);
                                }

                                setTimeout(() => {
                                    img.classList.remove("tapped");
                                }, 300); // Remove tap effect
                            });
                        });


                        document.querySelector(".remote-container").addEventListener("click", (e) => {
                            if (e.target.tagName === "IMG") { // Only trigger when clicking images inside remote-container
                                triggerSnowstorm();
                            }
                        });

                        function triggerSnowstorm() {
                            let numFlakes = 1000; // More flakes for a real snowstorm!

                            for (let i = 0; i < numFlakes; i++) {
                                let flake = document.createElement("div");
                                flake.innerHTML = "❄️"; // Ice flake emoji
                                flake.classList.add("snowstorm-flake");

                                // Random start position across the whole screen
                                flake.style.left = `${Math.random() * window.innerWidth}px`;
                                flake.style.top = `-${Math.random() * 1000}px`; // Start from slightly above the screen

                                document.body.appendChild(flake);

                                // Remove flakes after animation to keep performance smooth
                                setTimeout(() => {
                                    flake.remove();
                                }, 11000); // Snow lasts longer for max chaos
                            }
                        }










                        // serious part
                        function scaleRemote() {
                            let container = document.querySelector(".remote-container");
                            let parent = document.querySelector(".ACRMain");

                            let scale = Math.min(
                                parent.clientWidth / 400,  // Scale width based on `.ACRMain`
                                parent.clientHeight / 800  // Scale height based on `.ACRMain`
                            );

                            container.style.transform = `scale(${scale})`;
                        }

                        window.addEventListener("resize", scaleRemote);
                        scaleRemote(); // Run once on page load
                    </script>
                </div>

                <div class="room">
                    <!-- "Garage" button starts with the activeButton class to indicate it's the default -->
                    <button onclick="navigateToGarage('../templates/FirstFloor-Garage.php')" class="roomButton"
                        id="garageButton">Room 1</button>
                    <button onclick="navigateToOutdoor()" class="roomButton activeButton" id="outdoorButton">Room
                        2</button>
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



    </div>
</body>

</html>