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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                <div class="firstFloor" id="firstfloor3d">
                    <div class="ACRMain">
                        <div class="remote-container">
                            <!-- Non-interactive design images -->
                            <img class="bgRem" src="../assets/images/ac/bgRem.png">

                            <img class="tempbar" src="../assets/images/ac/tempbar.png">

                            <!-- Interactive images -->
                            <img class="tempbarLow" src="../assets/images/ac/tempbarLow.png">
                            <img class="tempbarHigh" src="../assets/images/ac/tempbarHigh.png">

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

                            <img class="timer" id="timer" src="../assets/images/ac/timer.png">
                            <!-- Timer Donut -->
                            <div class="container">
                                <div class="progress-wrapper">
                                    <svg class="progress-circle" id="progress-circle" width="225" height="300">
                                        <defs>
                                            <linearGradient class="progress-gradient" id="progress-gradient" x1="0%"
                                                y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" style="stop-color: #2D4446; stop-opacity: 1" />
                                                <stop offset="24.4%" style="stop-color: #497A80; stop-opacity: 1" />
                                                <stop offset="51.48%" style="stop-color: #3E8B94; stop-opacity: 1" />
                                                <stop offset="59.41%" style="stop-color: #3796A2; stop-opacity: 1" />
                                                <stop offset="65.02%" style="stop-color: #5EA3AB; stop-opacity: 1" />
                                                <stop offset="71.79%" style="stop-color: #65B1BA; stop-opacity: 1" />
                                            </linearGradient>
                                        </defs>
                                        <circle class="progress-background" cx="120" cy="120" r="95" />
                                        <circle class="progress-bar" cx="120" cy="120" r="95" />
                                    </svg>
                                    <div class="time-left" id="time-left">--:--</div>
                                    <span class="hrs">hrs</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="room">
                    <button onclick="navigateToGarage()" class="roomButton activeButton"
                        id="garageButton">Garage</button>
                    <button onclick="navigateToOutdoor()" class="roomButton" id="outdoorButton">Outdoor</button>
                </div>
            </div>
            <div class="dashboardDeviderRight">
                <!-- <div class="searchContainer">
                    <input type="text" id="searchInputX" placeholder=" " class="searchInput">
                    <button onclick="performSearch()" class="searchButton">
                        <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div> -->
                <div class="acLog">
                    <div class="firstPartLog">
                        <div class="logItem">
                            <div class="titleCommand">Power</div>
                            <div class="outputCommand">On</div>
                        </div>

                        <div class="line-with-circleR"></div>

                        <div class="logItem">
                            <div class="titleCommand">Temp</div>
                            <div class="outputCommand">16°C</div>
                        </div>

                        <div class="line-with-circleR"></div>

                        <div class="logItem">
                            <div class="titleCommand">Timer</div>
                            <div class="outputCommand">8 hrs</div>
                        </div>
                        <div class="line-with-circleR"></div>
                    </div>

                    <!-- Vertical Divider -->
                    <div class="divider"></div>

                    <div class="secondPartLog">
                        <div class="logItem">
                            <div class="titleCommand">Mode</div>
                            <div class="outputCommand">Fan</div>
                        </div>

                        <div class="line-with-circleL"></div>

                        <div class="logItem">
                            <div class="titleCommand">Fan</div>
                            <div class="outputCommand">High</div>
                        </div>

                        <div class="line-with-circleL"></div>

                        <div class="logItem">
                            <div class="titleCommand">Swing</div>
                            <div class="outputCommand">Off</div>
                        </div>
                        <div class="line-with-circleL"></div>
                    </div>
                </div>






                <div class="deviceControl">
                    <p class="devTitle">Devices</p>
                    <div class="devices">
                        <div class="lights">
                            <div class="imageandlightscont">
                                <img src="../assets/images/lights.png" alt="Lights" class="lightsImage">
                                <select class="lightDropdown" id="lightCategory" onchange="updateLightState()">
                                    <option value="FFLightOne">Front Gate</option>
                                    <option value="FFLightTwo">Front Garage</option>
                                    <option value="FFLightThree">Rear Garage</option>
                                    <option value="FFLightFour">Kitchen Wet</option>
                                    <option value="FFLightFive">Fridge Space</option>
                                    <option value="FFLightSix">Bath Area</option>
                                </select>
                            </div>
                            <p id="lightName">Lights</p>
                            <span>Room 1</span>

                            <div class="switch-containerTwo">
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
                        </div>

                        <div class="airConditionFF">
                            <a href="../templates/acRemote.php">
                                <img src="../assets/images/ac.png" alt="Air Condition" class="airconImage">
                            </a>
                            <p>Air Condition</p>
                            <span>Room 1</span>

                            <div class="switch-containerTwo">
                                <label class="switchTwo">
                                    <input type="checkbox" id="airconFFSwitch" onchange="toggleAirconFF()">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="cameraFF">
                            <img src="../assets/images/camera.png" alt="Camera" class="cameraImage">
                            <p>Camera</p>
                            <span>Outdoor</span>

                            <div class="switch-containerTwo">
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
                    selectedSwitch.style.textAlign = 'right';
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
                window.location.href = '../templates/FirstFloor-Outdoor.php';
            }
        </script>

        <!-- TIMER AC REMOTE -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const progressBar = document.querySelector('.progress-bar');
                const timeLeftText = document.getElementById('time-left');
                const progressCircle = document.getElementById('progress-circle');

                let totalTime = 0;  // Start with 0 seconds (0:00)
                let countdownInterval;
                let isRunning = false;  // Track if the countdown is running

                const maxTime = 12 * 60 * 60;  // Maximum time of 12 hours in seconds (43200 seconds)
                const circleCircumference = 2 * Math.PI * 95;  // Circumference of the circle

                // Function to update the progress bar and time left
                function updateTimer() {
                    const hours = Math.floor(totalTime / 3600); // Total hours
                    const minutes = Math.floor((totalTime % 3600) / 60); // Remaining minutes

                    // Ensure hours and minutes are always 2 digits (e.g., 01:09, 05:09, 11:00)
                    const formattedHours = String(hours).padStart(2, '0');
                    const formattedMinutes = String(minutes).padStart(2, '0');

                    // Display the time with 2 digits for hours and minutes
                    timeLeftText.textContent = `${formattedHours}:${formattedMinutes}`;

                    // Calculate strokeDashoffset based on the remaining time
                    const dashoffset = circleCircumference - (circleCircumference * totalTime) / maxTime;
                    progressBar.style.strokeDashoffset = dashoffset;
                }

                // Function to start the countdown
                function startCountdown() {
                    clearInterval(countdownInterval);  // Clear any existing intervals
                    countdownInterval = setInterval(function () {
                        if (totalTime > 0) {
                            totalTime--;  // Decrease by 1 second
                            updateTimer();
                        } else {
                            // Reset everything when it reaches 0:00
                            clearInterval(countdownInterval);  // Clear the countdown interval
                            totalTime = 0;  // Reset the timer to 0
                            updateTimer();  // Reset the progress bar
                            isRunning = false;  // Stop the countdown
                        }
                    }, 1000);
                }

                // Click event to add 1 hour or reset the timer
                progressCircle.addEventListener("click", function () {
                    if (!isRunning) {
                        // If the countdown is not already running, start it
                        isRunning = true;
                        startCountdown();
                    }

                    // Add 1 hour (3600 seconds) if the time is less than 12 hours
                    totalTime += 60 * 60;  // Add 1 hour (3600 seconds)

                    // If the time exceeds 12 hours, reset it to 0
                    if (totalTime >= maxTime) {
                        totalTime = 0;  // Reset the time to 0 seconds (0:00)
                    }

                    // Update the progress bar and timer immediately
                    updateTimer();
                });

                // Initial setup for the timer
                updateTimer();
            });
        </script>

        <!-- AC REMOTE EFFECTS -->
        <script>
            // Only apply interactive behavior to images that should be interactive
            document.querySelectorAll(".remote-container img").forEach(img => {
                // Exclude bgRem and tempbar from interaction
                if (!img.classList.contains('bgRem') && !img.classList.contains('tempbar')) {
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
                }
            });

            // Handle click event for interactive images only
            document.querySelector(".remote-container").addEventListener("click", (e) => {
                if (e.target.tagName === "IMG" && !e.target.classList.contains("bgRem") && !e.target.classList.contains("tempbar")) {
                    triggerSnowstorm();
                }
            });

            // Snowstorm effect function for interactive images
            function triggerSnowstorm() {
                let numFlakes = 50; // More flakes for a real snowstorm!

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

            // Serious part - scaling the remote to fit the screen
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
</body>

</html>