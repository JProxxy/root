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

                            <div class="tempbarCont">

                                <img class="tempbar" src="../assets/images/ac/tempbar.png">
                                <!-- Interactive images -->
                                <img class="tempbarLow" src="../assets/images/ac/tempbarLow.png">
                                <img class="tempbarHigh" src="../assets/images/ac/tempbarHigh.png">
                                <h1 class="actualTemp" id="ACRCTemp"></h1>
                            </div>

                            <div class="fanCont">
                                <img class="fan" src="../assets/images/ac/fan.png">
                                <img class="fanLow" src="../assets/images/ac/fanLow-White.png">
                                <img class="fanLow" src="../assets/images/ac/fanLow-Green.png" style="display: none;">
                                <img class="fanHigh" src="../assets/images/ac/fanHigh-Green.png">
                                <img class="fanHigh" src="../assets/images/ac/fanHigh-White.png" style="display: none;">
                            </div>

                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    // Select the fan container
                                    const fanCont = document.querySelector(".fanCont");

                                    // Select images
                                    const fanHighGreen = document.querySelector(".fanHigh[src*='fanHigh-Green']");
                                    const fanHighWhite = document.querySelector(".fanHigh[src*='fanHigh-White']");
                                    const fanLowGreen = document.querySelector(".fanLow[src*='fanLow-Green']");
                                    const fanLowWhite = document.querySelector(".fanLow[src*='fanLow-White']");

                                    let currentFanState = "High"; // Default fan state

                                    function setFanHigh() {
                                        fanHighGreen.style.display = "block";
                                        fanHighWhite.style.display = "none";
                                        fanLowGreen.style.display = "none";
                                        fanLowWhite.style.display = "block";
                                        currentFanState = "High";
                                        sendFanData(currentFanState);
                                    }

                                    function setFanLow() {
                                        fanHighGreen.style.display = "none";
                                        fanHighWhite.style.display = "block";
                                        fanLowGreen.style.display = "block";
                                        fanLowWhite.style.display = "none";
                                        currentFanState = "Low";
                                        sendFanData(currentFanState);
                                    }

                                    function sendFanData(fanState) {
                                        fetch("../scripts/fetch-AC-data.php", {
                                            method: "POST",
                                            headers: {
                                                "Content-Type": "application/json",
                                            },
                                            body: JSON.stringify({ fan: fanState }), // Send fan state to PHP
                                        })
                                            .then(response => response.text())
                                            .then(data => {
                                                console.log("Response from fetch-AC-data.php:", data);
                                            })
                                            .catch(error => {
                                                console.error("Error sending fan data:", error);
                                            });
                                    }

                                    // Initialize default state
                                    setFanHigh();

                                    // Toggle on click
                                    fanCont.addEventListener("click", function () {
                                        if (currentFanState === "High") {
                                            setFanLow();
                                        } else {
                                            setFanHigh();
                                        }
                                    });
                                });
                            </script>

                            <div class="modeCont">
                                <!-- Mode icon (could be used as the click target as well) -->
                                <img class="mode" src="../assets/images/ac/mode.png">

                                <!-- Mode Cool -->
                                <img class="modeCool" src="../assets/images/ac/modeCool-Green.png">
                                <img class="modeCool" src="../assets/images/ac/modeCool-White.png"
                                    style="display: none;">

                                <!-- Mode Dry -->
                                <img class="modeDry" src="../assets/images/ac/modeDry-White.png">
                                <img class="modeDry" src="../assets/images/ac/modeDry-Green.png" style="display: none;">

                                <!-- Mode Fan -->
                                <img class="modeFan" src="../assets/images/ac/modeFan-White.png">
                                <img class="modeFan" src="../assets/images/ac/modeFan-Green.png" style="display: none;">
                            </div>


                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    // Select the mode container
                                    const modeCont = document.querySelector(".modeCont");

                                    // Select images based on their classes
                                    const modeCoolGreen = document.querySelector(".modeCool[src*='modeCool-Green']");
                                    const modeCoolWhite = document.querySelector(".modeCool[src*='modeCool-White']");
                                    const modeDryWhite = document.querySelector(".modeDry[src*='modeDry-White']");
                                    const modeDryGreen = document.querySelector(".modeDry[src*='modeDry-Green']");
                                    const modeFanWhite = document.querySelector(".modeFan[src*='modeFan-White']");
                                    const modeFanGreen = document.querySelector(".modeFan[src*='modeFan-Green']");

                                    // Track the current state (0, 1, or 2)
                                    let currentState = 0;

                                    function updateModeDisplay() {
                                        let activeMode = ""; // This will store which mode is currently green

                                        if (currentState === 0) {
                                            // Default state
                                            modeCoolGreen.style.display = "block";
                                            modeCoolWhite.style.display = "none";
                                            modeDryWhite.style.display = "block";
                                            modeDryGreen.style.display = "none";
                                            modeFanWhite.style.display = "block";
                                            modeFanGreen.style.display = "none";
                                            activeMode = "Cool";
                                        } else if (currentState === 1) {
                                            // First click state
                                            modeCoolGreen.style.display = "none";
                                            modeCoolWhite.style.display = "block";
                                            modeDryWhite.style.display = "none";
                                            modeDryGreen.style.display = "block";
                                            modeFanWhite.style.display = "block";
                                            modeFanGreen.style.display = "none";
                                            activeMode = "Dry";
                                        } else if (currentState === 2) {
                                            // Second click state
                                            modeCoolGreen.style.display = "none";
                                            modeCoolWhite.style.display = "block";
                                            modeDryWhite.style.display = "block";
                                            modeDryGreen.style.display = "none";
                                            modeFanWhite.style.display = "none";
                                            modeFanGreen.style.display = "block";
                                            activeMode = "Fan";
                                        }

                                        // Send the active mode to fetch-AC-data.php
                                        sendModeData(activeMode);
                                    }

                                    function sendModeData(activeMode) {
                                        fetch("../scripts/fetch-AC-data.php", {
                                            method: "POST",
                                            headers: {
                                                "Content-Type": "application/json",  // Change to JSON
                                            },
                                            body: JSON.stringify({ activeMode }),  // Send JSON format
                                        })
                                            .then(response => response.text())
                                            .then(data => {
                                                console.log("Response from fetch-AC-data.php:", data);
                                            })
                                            .catch(error => {
                                                console.error("Error sending mode data:", error);
                                            });
                                    }


                                    // Initialize with default state
                                    updateModeDisplay();

                                    // Click event to toggle mode
                                    modeCont.addEventListener("click", function () {
                                        currentState = (currentState + 1) % 3; // Cycle through states 0 → 1 → 2 → 0
                                        updateModeDisplay();
                                    });
                                });
                            </script>





                            <div class="swingCont">
                                <!-- Click target (could also be the container itself) -->
                                <img class="swing" src="../assets/images/ac/swing.png">

                                <!-- Swing "On" images -->
                                <img class="swingOn" src="../assets/images/ac/swingOn-White.png">
                                <img class="swingOn" src="../assets/images/ac/swingOn-Green.png"
                                    style="display: noneF;">

                                <!-- Swing "Off" images -->
                                <img class="swingOff" src="../assets/images/ac/swingOff-Green.png">
                                <img class="swingOff" src="../assets/images/ac/swingOff-White.png"
                                    style="display: none;">
                            </div>

                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    // Get references to the images within the container
                                    const swingCont = document.querySelector(".swingCont");
                                    const swingOnWhite = swingCont.querySelector("img.swingOn[src*='swingOn-White']");
                                    const swingOnGreen = swingCont.querySelector("img.swingOn[src*='swingOn-Green']");
                                    const swingOffGreen = swingCont.querySelector("img.swingOff[src*='swingOff-Green']");
                                    const swingOffWhite = swingCont.querySelector("img.swingOff[src*='swingOff-White']");

                                    let swingState = "Off"; // Default state

                                    function setSwingOn() {
                                        swingOffGreen.style.display = "none";
                                        swingOffWhite.style.display = "block";
                                        swingOnWhite.style.display = "none";
                                        swingOnGreen.style.display = "block";
                                        swingState = "On";
                                        sendSwingData(swingState);
                                    }

                                    function setSwingOff() {
                                        swingOffGreen.style.display = "block";
                                        swingOffWhite.style.display = "none";
                                        swingOnWhite.style.display = "block";
                                        swingOnGreen.style.display = "none";
                                        swingState = "Off";
                                        sendSwingData(swingState);
                                    }

                                    function sendSwingData(state) {
                                        fetch("../scripts/fetch-AC-data.php", {
                                            method: "POST",
                                            headers: {
                                                "Content-Type": "application/json",
                                            },
                                            body: JSON.stringify({ swing: state }), // Send swing state to PHP
                                        })
                                            .then(response => response.text())
                                            .then(data => {
                                                console.log("Response from fetch-AC-data.php:", data);
                                            })
                                            .catch(error => {
                                                console.error("Error sending swing data:", error);
                                            });
                                    }

                                    // Initialize display
                                    setSwingOff();

                                    swingCont.addEventListener("click", function () {
                                        if (swingState === "Off") {
                                            setSwingOn();
                                        } else {
                                            setSwingOff();
                                        }
                                    });
                                });

                            </script>

                            <img id="sleepWhite" class="sleep" src="../assets/images/ac/sleep-White.png">
                            <img id="sleepGreen" class="sleep" src="../assets/images/ac/sleep-Green.png"
                                style="display: none;">

                            <script>
                              document.addEventListener("DOMContentLoaded", function () {
    const sleepWhite = document.getElementById("sleepWhite");
    const sleepGreen = document.getElementById("sleepGreen");

    let sleepState = "Off"; // Default state
    let currentMode = "Cool"; // Default AC mode

    function setSleepOn() {
        if (currentMode === "Dry" || currentMode === "Fan") return; // Prevent enabling Sleep in Dry or Fan mode
        sleepWhite.style.display = "none";
        sleepGreen.style.display = "block";
        sleepState = "On";
        sendSleepData(sleepState);
    }

    function setSleepOff() {
        sleepWhite.style.display = "block";
        sleepGreen.style.display = "none";
        sleepState = "Off";
        sendSleepData(sleepState);
    }

    function sendSleepData(state) {
        fetch("../scripts/fetch-AC-data.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ sleep: state }), // Send sleep state to PHP
        })
        .then(response => response.text())
        .then(data => {
            console.log("Response from fetch-AC-data.php:", data);
        })
        .catch(error => {
            console.error("Error sending sleep data:", error);
        });
    }

    // Function to update mode and disable/enable Sleep mode
    function updateMode(newMode) {
        currentMode = newMode;
        if (currentMode === "Dry" || currentMode === "Fan") {
            setSleepOff(); // Force sleep mode off
            sleepWhite.style.opacity = "0.5"; // Visually indicate it's disabled
            sleepGreen.style.opacity = "0.5";
            sleepWhite.style.pointerEvents = "none"; // Fully disable clicks
            sleepGreen.style.pointerEvents = "none";
        } else {
            sleepWhite.style.opacity = "1"; // Re-enable when Cool mode is active
            sleepGreen.style.opacity = "1";
            sleepWhite.style.pointerEvents = "auto"; // Enable clicks
            sleepGreen.style.pointerEvents = "auto";
        }
    }

    // Initialize default state
    setSleepOff();

    sleepWhite.addEventListener("click", setSleepOn);
    sleepGreen.addEventListener("click", setSleepOff);

    // Example: Call `updateMode()` when AC mode is changed
    document.addEventListener("modeChanged", function (event) {
        updateMode(event.detail.mode); // Listen for mode change events
    });
});


                            </script>



                            <img class="timer" id="timer" src="../assets/images/ac/timer.png">
                            <!-- Timer Donut -->
                            <div class="container">
                                <div class="progress-wrapper">
                                    <svg class="progress-circle" id="progress-circle" width="215" height="215">
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
                                        <circle class="progress-background" cx="107.5" cy="107.5" r="97" />
                                        <circle class="progress-bar" cx="107.5" cy="107.5" r="97" />
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
                <div class="acLog">
                    <div class="firstPartLog">
                        <div class="logItem">
                            <div class="titleCommand">Power</div>
                            <div class="outputCommand" id="ACpower"></div>
                        </div>

                        <div class="line-with-circleR"></div>

                        <div class="logItem">
                            <div class="titleCommand">Temp</div>
                            <div class="outputCommand" id="ACtemp">°C</div>
                        </div>

                        <div class="line-with-circleR"></div>

                        <div class="logItem">
                            <div class="titleCommand">Timer</div>
                            <div class="outputCommand" id="ACtimer"></div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="secondPartLog">
                        <div class="logItem">
                            <div class="titleCommand">Mode</div>
                            <div class="outputCommand" id="ACmode"></div>
                        </div>

                        <div class="line-with-circleL"></div>

                        <div class="logItem">
                            <div class="titleCommand">Fan</div>
                            <div class="outputCommand" id="ACfan"></div>
                        </div>

                        <div class="line-with-circleL"></div>

                        <div class="logItem">
                            <div class="titleCommand">Swing</div>
                            <div class="outputCommand" id="ACswing"></div>
                        </div>
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


            function navigateToGarage() {
                window.location.href = 'FirstFloor-Garage.php';
            }

            function navigateToOutdoor() {
                window.location.href = '../templates/FirstFloor-Outdoor.php';
            }
        </script>


        <script>
            function toggleAirconFF() {
                const switchElement = document.getElementById("airconFFSwitch");
                const remoteContainer = document.querySelector(".remote-container");
                const elementsToHide = [
                    "ACpower", "ACtemp", "ACtimer", "ACmode", "ACfan", "ACswing", "ACRCTemp"
                ];

                if (switchElement.checked) {
                    remoteContainer.classList.add("enabled"); // Enable controls
                    elementsToHide.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.style.display = "block"; // Show text
                    });
                } else {
                    remoteContainer.classList.remove("enabled"); // Disable controls

                    // Hide text elements
                    elementsToHide.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.style.display = "none"; // Hide text
                    });

                    // Stop all intervals
                    let highestInterval = setInterval(() => { }, 1000);
                    for (let i = 0; i < highestInterval; i++) {
                        clearInterval(i);
                    }

                    // Stop all requestAnimationFrame loops
                    let highestFrame = requestAnimationFrame(() => { });
                    for (let i = 0; i < highestFrame; i++) {
                        cancelAnimationFrame(i);
                    }
                }
            }

            // Ensure elements are hidden on page load
            document.addEventListener("DOMContentLoaded", function () {
                toggleAirconFF(); // Run function on page load to check initial state
            });


        </script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            function fetchACLog() {
                $.ajax({
                    url: '../scripts/fetch-AC-data.php',  // PHP script to get AC log
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        // Update UI with fetched data
                        $("#ACpower").text(data.power);
                        $("#ACtemp").text(data.temp + " °C");
                        $("#ACtimer").text(data.timer);
                        $("#ACmode").text(data.mode);
                        $("#ACfan").text(data.fan);
                        $("#ACswing").text(data.swing);
                        $("#ACRCTemp").text(data.temp);  // Updated to include room temperature

                        // Print the fetched data to the console
                        console.log("AC Log Updated:", data);
                    },
                    error: function (xhr, status, error) {
                        console.error("Error fetching AC log:", error);
                    }
                });
            }

            // Fetch data when the page loads
            $(document).ready(function () {
                fetchACLog();  // Initial fetch
                setInterval(fetchACLog, 3000);  // Fetch every 3 seconds
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const tempbarLow = document.querySelector(".tempbarLow");
                const tempbarHigh = document.querySelector(".tempbarHigh");
                const ACRCTempEl = document.getElementById("ACRCTemp");
                const ACtempDisplay = document.getElementById("ACtemp");

                // Initialize the temperature (defaulting to 31 if the element is empty)
                let currentTemp = parseInt(ACRCTempEl.textContent) || 31;
                ACRCTempEl.textContent = currentTemp;
                ACtempDisplay.textContent = currentTemp + " °C";

                // Function to send the updated temperature to the server
                function updateTempOnServer(temp) {
                    $.ajax({
                        url: '../scripts/fetch-AC-data.php', // Ensure this URL is correct
                        type: 'POST',
                        dataType: 'json',
                        contentType: 'application/json',
                        data: JSON.stringify({ temp: temp }),
                        success: function (responseData) {
                            console.log("AC Log Updated:", responseData);
                            // Optionally update the AC log display if needed:
                            ACtempDisplay.textContent = responseData.temp + " °C";
                        },
                        error: function (xhr, status, error) {
                            console.error("Error updating temperature:", error);
                        }
                    });
                }

                // Decrease temperature when clicking on tempbarLow
                tempbarLow.addEventListener("click", function () {
                    // Get current temperature from the element or fallback
                    currentTemp = parseInt(ACRCTempEl.textContent) || 31;
                    if (currentTemp > 16) {
                        currentTemp--; // Decrease by 1 degree
                        ACRCTempEl.textContent = currentTemp;
                        ACtempDisplay.textContent = currentTemp + " °C"; // Update the AC log display too
                        updateTempOnServer(currentTemp);
                        console.log("Temperature decreased to: " + currentTemp);
                    } else {
                        console.log("Minimum temperature of 16°C reached.");
                    }
                });

                // Increase temperature when clicking on tempbarHigh
                tempbarHigh.addEventListener("click", function () {
                    currentTemp = parseInt(ACRCTempEl.textContent) || 31;
                    if (currentTemp < 31) {
                        currentTemp++; // Increase by 1 degree
                        ACRCTempEl.textContent = currentTemp;
                        ACtempDisplay.textContent = currentTemp + " °C";
                        updateTempOnServer(currentTemp);
                        console.log("Temperature increased to: " + currentTemp);
                    } else {
                        console.log("Maximum temperature of 31°C reached.");
                    }
                });
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const progressBar = document.querySelector('.progress-bar');
                const timeLeftText = document.getElementById('time-left');
                const progressCircle = document.getElementById('progress-circle');


                let totalTime = 0;  // total time in seconds
                let countdownInterval;
                let isRunning = false;  // whether the countdown is active

                const maxTime = 12 * 60 * 60;  // 12 hours in seconds (43200)
                const circleCircumference = 2 * Math.PI * 97.1;  // Circumference of the progress circle

                // Updates the displayed time using a ceiling method.
                function updateTimer() {
                    let hours;
                    if (totalTime === 0) {
                        hours = 0;
                    } else if (totalTime % 3600 === 0) {
                        // Exactly a full hour (e.g., 11:00:00)
                        hours = totalTime / 3600;
                    } else {
                        // If there are leftover seconds, round up to the next full hour.
                        hours = Math.floor(totalTime / 3600) + 1;
                    }
                    // Cap the displayed hours at 12.
                    if (hours > 12) {
                        hours = 12;
                    }
                    const formattedHours = String(hours).padStart(2, '0');
                    timeLeftText.textContent = formattedHours;

                    // Update the progress circle
                    const dashoffset = circleCircumference - (circleCircumference * totalTime) / maxTime;
                    progressBar.style.strokeDashoffset = dashoffset;
                }

                function startCountdown() {
                    clearInterval(countdownInterval);  // Clear any existing countdown
                    countdownInterval = setInterval(function () {
                        if (totalTime > 0) {
                            totalTime--;  // Decrement by one second
                            updateTimer();
                        } else {
                            clearInterval(countdownInterval);
                            totalTime = 0;
                            updateTimer();
                            isRunning = false;
                        }
                    }, 1000);
                }

                progressCircle.addEventListener("click", function () {
                    if (!isRunning) {
                        isRunning = true;
                        startCountdown();
                    }

                    // Each click adds one hour (3600 seconds)
                    totalTime += 3600;

                    // If the total time exceeds 12 hours, reset to 0
                    if (totalTime > maxTime) {
                        totalTime = 0;
                    }
                    updateTimer();
                });

                updateTimer(); // Initial update
            });
        </script>


        <!-- AC REMOTE EFFECTS -->
        <script>
            // Only apply interactive behavior to images that should be interactive
            document.querySelectorAll(".remote-container img").forEach(img => {
                if (!img.classList.contains('bgRem') && !img.classList.contains('tempbar')) {
                    img.addEventListener("mousedown", (e) => {
                        e.preventDefault(); // Prevent dragging
                        img.classList.add("tapped");

                        // Create ice flakes ❄️
                        for (let i = 0; i < 10; i++) {
                            let flake = document.createElement("div");
                            flake.innerHTML = "❄️"; // Ice flake emoji
                            flake.classList.add("ice-flake");

                            // Random start position near tap point
                            let x = e.clientX + (Math.random() * 50 - 25);
                            let y = e.clientY + (Math.random() * 30 - 15);
                            flake.style.left = x + "px";
                            flake.style.top = y + "px";

                            document.body.appendChild(flake);

                            // Remove flakes after animation
                            setTimeout(() => {
                                flake.remove();
                            }, 1500);
                        }

                        setTimeout(() => {
                            img.classList.remove("tapped");
                        }, 300);
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
                    flake.style.left = Math.random() * window.innerWidth + "px";
                    flake.style.top = -Math.random() * 1000 + "px"; // Start from slightly above the screen

                    document.body.appendChild(flake);

                    // Remove flakes after animation to keep performance smooth
                    setTimeout(() => {
                        flake.remove();
                    }, 11000);
                }
            }

            // Scaling the remote to fit the screen
            function scaleRemote() {
                let container = document.querySelector(".remote-container");
                let parent = document.querySelector(".ACRMain");

                let scale = Math.min(
                    parent.clientWidth / 400,  // Scale width based on .ACRMain
                    parent.clientHeight / 800  // Scale height based on .ACRMain
                );

                container.style.transform = "scale(" + scale + ")";
            }

            window.addEventListener("resize", scaleRemote);
            scaleRemote(); // Run once on page load
        </script>


    </div>
</body>

</html>