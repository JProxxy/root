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
include '../app/config/connection.php';


$stmt = $conn->prepare("SELECT power FROM acRemote WHERE user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$acData = $stmt->fetch(PDO::FETCH_ASSOC);

$power = isset($acData['power']) ? $acData['power'] : 'Off';
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

                            <div class="modeCont">
                                <!-- Mode icon (could be used as the click target as well) -->
                                <img class="mode" src="../assets/images/ac/mode.png">

                                <!-- Mode Cool -->
                                <img class="modeCool" src="../assets/images/ac/modeCool-Green.png">
                                <img class="modeCool" src="../assets/images/ac/modeCool-White.png"
                                    style="display: none;">

                                <!-- Mode Fan -->
                                <img class="modeFan" src="../assets/images/ac/modeFan-White.png">
                                <img class="modeFan" src="../assets/images/ac/modeFan-Green.png" style="display: none;">

                                <!-- Mode Dry -->
                                <img class="modeDry" src="../assets/images/ac/modeDry-White.png">
                                <img class="modeDry" src="../assets/images/ac/modeDry-Green.png" style="display: none;">
                            </div>

                            <div class="swingCont">
                                <!-- Click target (could also be the container itself) -->
                                <img class="swing" src="../assets/images/ac/swing.png">

                                <!-- Swing "On" images -->
                                <img class="swingOn" src="../assets/images/ac/swingOn-White.png">
                                <img class="swingOn" src="../assets/images/ac/swingOn-Green.png" style="display: none;">

                                <!-- Swing "Off" images -->
                                <img class="swingOff" src="../assets/images/ac/swingOff-Green.png">
                                <img class="swingOff" src="../assets/images/ac/swingOff-White.png"
                                    style="display: none;">
                            </div>

                            <!-- Sleep images -->
                            <img id="sleepWhite" class="sleep" src="../assets/images/ac/sleep-White.png">
                            <img id="sleepGreen" class="sleep" src="../assets/images/ac/sleep-Green.png"
                                style="display: none;">

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

                <!-- Your AC log container -->
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
                                    <input type="checkbox" id="airconFFSwitch" onchange="toggleAirconFF()" <?php echo ($power === "On") ? "checked" : ""; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                        </div>

                        <div class="cameraFF">
                            <img src="../assets/images/camera.png" alt="Camera" class="cameraImage">
                            <p>Camera</p>
                            <span>Outdoor</span>

                            <!-- <div class="switch-containerTwo">
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div> -->
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
            // Global flag to indicate if the power state is being set on initial load.
            let initialPowerLoad = true;

            // Revised POWER TOGGLE FUNCTION with persistence and timer UI reset when off
            function toggleAirconFF() {
                const switchElement = document.getElementById("airconFFSwitch");
                const remoteContainer = document.querySelector(".remote-container");
                const elementsToHide = ["ACpower", "ACtemp", "ACtimer", "ACmode", "ACfan", "ACswing", "ACRCTemp"];

                // Get current power state
                const powerState = switchElement.checked ? "On" : "Off";

                // Persist power state in localStorage
                localStorage.setItem("powerState", powerState);

                // Only update UI visibility - DON'T reset values (except for timer UI when off)
                if (powerState === "On") {
                    remoteContainer.classList.add("enabled");
                    remoteContainer.classList.remove("disabled");
                    elementsToHide.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.style.display = "block";
                    });
                } else {
                    remoteContainer.classList.remove("enabled");
                    remoteContainer.classList.add("disabled");
                    elementsToHide.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.style.display = "none";
                    });
                    // Reset timer UI when power is off
                    localStorage.setItem("totalTime", 0);
                    const timeLeftText = document.getElementById("time-left");
                    const progressBar = document.querySelector(".progress-bar");
                    const circleCircumference = 2 * Math.PI * 97.1;
                    if (timeLeftText) {
                        timeLeftText.textContent = "00";
                    }
                    if (progressBar) {
                        progressBar.style.strokeDashoffset = circleCircumference;
                    }
                }

                // Only send power state to backend and Lambda if not during the initial load.
                if (!initialPowerLoad) {
                    updatePowerStatus(powerState);
                    sendPowerStateLambda("<?php echo $_SESSION['user_id']; ?>", powerState);
                } else {
                    // Disable the flag after the initial update.
                    initialPowerLoad = false;
                }
            }

            // On page load, check localStorage for a saved power state and apply it.
            document.addEventListener("DOMContentLoaded", function () {
                const switchElement = document.getElementById("airconFFSwitch");
                const savedPowerState = localStorage.getItem("powerState");

                // If a saved state exists, update the switch element accordingly.
                if (savedPowerState) {
                    switchElement.checked = savedPowerState === "On";
                }

                // Now trigger the toggle function to update the UI to match the saved state.
                toggleAirconFF();
            });

            // ============== SIMPLIFIED POWER STATUS UPDATE ============== //
            function updatePowerStatus(powerState) {
                fetch("../scripts/fetch-AC-data.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        user_id: "<?php echo $_SESSION['user_id']; ?>",
                        power: powerState // Only power field sent
                    })
                }).catch(error => console.error("Power update error:", error));
            }

            // ============== MODIFIED LAMBDA INTEGRATION ============== //
            function sendPowerStateLambda(userId, powerState) {
                fetch('https://uev5bzg84f.execute-api.ap-southeast-1.amazonaws.com/dev-AcTemp/AcTemp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        data: {
                            user_id: userId,
                            power: powerState // Only power state sent
                        }
                    })
                });
            }
        </script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            // ============== ADD THESE GLOBAL VARIABLES ============== //
            let previousACData = null;
            let isFetching = false;

            // ============== REPLACE fetchACLog WITH THIS ============== //
            async function optimizedPoll() {
                if (isFetching) return;
                isFetching = true;

                try {
                    const response = await fetch(`../scripts/fetch-AC-data.php?cache=${Date.now()}`);
                    const newData = await response.json();

                    // Update UI only if data changed
                    if (!previousACData || JSON.stringify(newData) !== JSON.stringify(previousACData)) {
                        updateUI(newData);
                        previousACData = newData;
                    }
                } catch (error) {
                    console.error("Polling error:", error);
                } finally {
                    isFetching = false;
                    setTimeout(optimizedPoll, 1000); // 1-second polling
                }
            }

            // ============== MODIFIED UI UPDATE FUNCTION ============== //
            function updateUI(data) {
                // Update all UI elements
                $("#ACpower").text(data.power);
                $("#ACtemp").text(data.temp + " °C");
                $("#ACtimer").text(data.timer);
                $("#ACmode").text(data.mode);
                $("#ACfan").text(data.fan);
                $("#ACswing").text(data.swing);
                $("#ACRCTemp").text(data.temp);

                // Update power switch
                const switchElement = document.getElementById("airconFFSwitch");
                if (switchElement) {
                    switchElement.checked = (data.power === "On");
                }

                // Maintain global power state
                window.currentPower = data.power;
            }

            // ============== MODIFIED DOCUMENT READY ============== //
            $(document).ready(function () {
                // Initial fetch
                optimizedPoll();

                // Update interactive elements
                const interactiveSelectors = [
                    ".tempbarLow",
                    ".tempbarHigh",
                    ".fanCont",
                    ".modeCont",
                    ".swingCont",
                    ".sleep"
                ];

                document.querySelectorAll(interactiveSelectors.join(", ")).forEach(el => {
                    el.addEventListener("click", () => {
                        // Refresh faster after interaction
                        setTimeout(optimizedPoll, 100);
                    });
                });
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const tempbarLow = document.querySelector(".tempbarLow");
                const tempbarHigh = document.querySelector(".tempbarHigh");
                const ACRCTempEl = document.getElementById("ACRCTemp");
                const ACtempDisplay = document.getElementById("ACtemp");

                // Initialize the temperature (defaulting if the element is empty)
                let currentTemp = parseInt(ACRCTempEl.textContent) || 16;
                ACRCTempEl.textContent = currentTemp;
                ACtempDisplay.textContent = currentTemp + " °C";

                // Function to send the updated AC settings to the server, including power status
                function updateACSettings(temp, fan, mode, swing, timer, power) {
                    $.ajax({
                        url: '../scripts/fetch-AC-data.php',
                        type: 'POST',
                        dataType: 'json',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            user_id: "<?php echo $_SESSION['user_id']; ?>", // Dynamic user ID from session
                            temp: temp,
                            fan: fan,
                            mode: mode,
                            swing: swing,
                            timer: timer,
                            power: power // Use the dynamic power value ("On" or "Off")
                        }),
                        success: function (response) {
                            if (response.success) {
                                $("#ACtemp").text(response.temp + " °C"); // Update UI with temperature
                                console.log("AC Settings Updated:", response);
                            } else {
                                console.error("Error updating AC settings:", response.error);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", xhr.responseText);
                        }
                    });
                }

                // Function to send temperature update to AWS Lambda for high temperature adjustments.
                function sendTempLambdaHigh(userId, currentTemp) {
                    const payload = {
                        data: {
                            user_id: userId,
                            tembarHigh: currentTemp
                        }
                    };
                    fetch("https://uev5bzg84f.execute-api.ap-southeast-1.amazonaws.com/dev-AcTemp/AcTemp", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(payload)
                    })
                        .then(response => response.json())
                        .then(data => console.log("Lambda High Response:", data))
                        .catch(error => console.error("Error updating Lambda (High):", error));
                }


                // Function to send temperature update to AWS Lambda for low temperature adjustments.
                function sendTempLambdaLow(userId, currentTemp) {
                    const payload = {
                        data: {
                            user_id: userId,
                            tembarLow: currentTemp
                        }
                    };
                    fetch("https://uev5bzg84f.execute-api.ap-southeast-1.amazonaws.com/dev-AcTemp/AcTemp", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(payload)
                    })
                        .then(response => response.json())
                        .then(data => console.log("Lambda Low Response:", data))
                        .catch(error => console.error("Error updating Lambda (Low):", error));
                }


                // Decrease temperature when clicking on tempbarLow
                tempbarLow.addEventListener("click", function () {
                    currentTemp = parseInt(ACRCTempEl.textContent) || 16;
                    if (currentTemp > 16) {
                        currentTemp--; // Decrease by 1 degree
                        ACRCTempEl.textContent = currentTemp;
                        ACtempDisplay.textContent = currentTemp + " °C";

                        // Update AC settings on server
                        updateACSettings(currentTemp, "High", "Cool", "On", "0");
                        // Send low temperature update to Lambda
                        sendTempLambdaLow("<?php echo $_SESSION['user_id']; ?>", currentTemp);

                        console.log("Temperature decreased to: " + currentTemp);
                    } else {
                        console.log("Minimum temperature of 16°C reached.");
                    }
                });

                // Increase temperature when clicking on tempbarHigh
                tempbarHigh.addEventListener("click", function () {
                    currentTemp = parseInt(ACRCTempEl.textContent) || 32;
                    if (currentTemp < 32) {
                        currentTemp++; // Increase by 1 degree
                        ACRCTempEl.textContent = currentTemp;
                        ACtempDisplay.textContent = currentTemp + " °C";

                        // Update AC settings on server
                        updateACSettings(currentTemp, "High", "Cool", "On", "0");
                        // Send high temperature update to Lambda
                        sendTempLambdaHigh("<?php echo $_SESSION['user_id']; ?>", currentTemp);

                        console.log("Temperature increased to: " + currentTemp);
                    } else {
                        console.log("Maximum temperature of 32°C reached.");
                    }
                });
            });

        </script>

    </div>
    <?php include '../assets/js/ac-controls.php'; ?>

</body>

</html>