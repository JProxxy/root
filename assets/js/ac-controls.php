<script>
   document.addEventListener("DOMContentLoaded", function () {
    // Select the fan container and images
    const fanCont = document.querySelector(".fanCont");
    const fanHighGreen = document.querySelector(".fanHigh[src*='fanHigh-Green']");
    const fanHighWhite = document.querySelector(".fanHigh[src*='fanHigh-White']");
    const fanLowGreen = document.querySelector(".fanLow[src*='fanLow-Green']");
    const fanLowWhite = document.querySelector(".fanLow[src*='fanLow-White']");

    // Global fan state tracker (default will be updated from the database)
    let currentFanState = "High";
    // Flag to indicate if this is the initial load
    let initialFanLoad = true;

    // Get the user ID dynamically via PHP
    const userID = "<?php echo $_SESSION['user_id']; ?>";
    console.log("User ID:", userID);

    // Function to log current fan state
    function updateFanTracker() {
        console.log(`Fan State: ${currentFanState}`);
    }

    // Function to update the UI and state to "High"
    function setFanHigh() {
        if (fanHighGreen) fanHighGreen.style.display = "block";
        if (fanHighWhite) fanHighWhite.style.display = "none";
        if (fanLowGreen) fanLowGreen.style.display = "none";
        if (fanLowWhite) fanLowWhite.style.display = "block";
        currentFanState = "High";
        updateFanTracker();
        // Only send data if this is not the initial load
        if (!initialFanLoad) {
            sendFanData(currentFanState);
            sendFanStateLambda(userID, currentFanState);
        }
    }

    // Function to update the UI and state to "Low"
    function setFanLow() {
        if (fanHighGreen) fanHighGreen.style.display = "none";
        if (fanHighWhite) fanHighWhite.style.display = "block";
        if (fanLowGreen) fanLowGreen.style.display = "block";
        if (fanLowWhite) fanLowWhite.style.display = "none";
        currentFanState = "Low";
        updateFanTracker();
        // Only send data if this is not the initial load
        if (!initialFanLoad) {
            sendFanData(currentFanState);
            sendFanStateLambda(userID, currentFanState);
        }
    }

    // Function to send the fan state to the PHP backend
    function sendFanData(fanState) {
        console.log("Sending fan data for user:", userID, "State:", fanState);
        fetch("../scripts/fetch-AC-data.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                user_id: userID,
                fan: fanState,
            }),
        })
            .then((response) => response.text())
            .then((data) => {
                console.log("Response from fetch-AC-data.php:", data);
            })
            .catch((error) => {
                console.error("Error sending fan data:", error);
            });
    }

    // Function to send the fan state to the Lambda API via API Gateway
    function sendFanStateLambda(userId, fanState) {
        // Prepare the data in the required format
        const requestData = {
            data: {
                user_id: userId,
                fanstate: fanState
            }
        };

        // Make the fetch request to the API Gateway endpoint
        fetch('https://uev5bzg84f.execute-api.ap-southeast-1.amazonaws.com/dev-AcTemp/AcTemp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData) // Correctly formatted JSON
        })
            .then(response => response.json()) // Handle the response from Lambda
            .then(responseData => {
                console.log('Device control response:', responseData);
            })
            .catch(error => {
                console.error("Error updating device status:", error);
            });
    }

    // Function to fetch the fan state from the database
    function fetchFanState() {
        $.ajax({
            url: '../scripts/fetch-AC-data.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                console.log("Fetched fan state:", data.fan);
                // Update the fan state based on the database value.
                if (data.fan === "Low") {
                    setFanLow();
                } else {
                    // Default to High if the returned value is not "Low"
                    setFanHigh();
                }
                // After initial setup, allow subsequent changes to send data.
                initialFanLoad = false;
            },
            error: function (xhr, status, error) {
                console.error("Error fetching fan state:", error);
                // Even if there is an error, we want to allow user actions.
                initialFanLoad = false;
            }
        });
    }

    // Fetch the fan state from the database on page load
    fetchFanState();

    // Toggle fan state on click, but only if the controls are enabled
    fanCont.addEventListener("click", function () {
        if (fanCont.style.pointerEvents === "none") {
            return; // Do nothing if disabled
        }
        if (currentFanState === "High") {
            setFanLow();
        } else {
            setFanHigh();
        }
    });

    // Listen for the custom "modeChanged" event to disable/enable fan controls
    document.addEventListener("modeChanged", function (event) {
        const mode = event.detail.mode;
        if (mode === "Dry") {
            fanCont.style.pointerEvents = "none";
            fanCont.style.opacity = "0.5"; // Indicate disabled state
        } else {
            fanCont.style.pointerEvents = "auto";
            fanCont.style.opacity = "1";
        }
    });
});

// ============== MODE LOGIC  ============== //

// Declare globally, only once
let currentMode = "Cool"; // Default mode
let currentState = 0;     // 0 = "Cool", 1 = "Dry", 2 = "Fan"

// Flag to indicate initial mode load.
let initialModeLoad = true;

// Map mode names to state values
function modeToState(mode) {
    switch (mode) {
        case "Cool": return 0;
        case "Dry": return 1;
        case "Fan": return 2;
        default: return 0; // Default to Cool if unknown
    }
}

// Function to fetch the AC mode from the database
function fetchACMode() {
    $.ajax({
        url: '../scripts/fetch-AC-data.php', // GET request will return the AC log
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            console.log("Fetched mode from DB:", data.mode);
            // If the mode exists in the DB, use it; otherwise default to "Cool"
            if (data.mode) {
                currentMode = data.mode;
            } else {
                currentMode = "Cool";
            }
            // Update the state accordingly
            currentState = modeToState(currentMode);
            // Now update the UI
            updateModeDisplay();
        },
        error: function (xhr, status, error) {
            console.error("Error fetching AC mode:", error);
            // On error, default to "Cool"
            currentMode = "Cool";
            currentState = 0;
            updateModeDisplay();
        }
    });
}

function updateModeDisplay() {
    // Select images based on their classes
    const modeCoolGreen = document.querySelector(".modeCool[src*='modeCool-Green']");
    const modeCoolWhite = document.querySelector(".modeCool[src*='modeCool-White']");
    const modeDryWhite = document.querySelector(".modeDry[src*='modeDry-White']");
    const modeDryGreen = document.querySelector(".modeDry[src*='modeDry-Green']");
    const modeFanWhite = document.querySelector(".modeFan[src*='modeFan-White']");
    const modeFanGreen = document.querySelector(".modeFan[src*='modeFan-Green']");

    // Update display based on currentState
    if (currentState === 0) { // Cool
        if (modeCoolGreen) modeCoolGreen.style.display = "block";
        if (modeCoolWhite) modeCoolWhite.style.display = "none";
        if (modeDryWhite) modeDryWhite.style.display = "block";
        if (modeDryGreen) modeDryGreen.style.display = "none";
        if (modeFanWhite) modeFanWhite.style.display = "block";
        if (modeFanGreen) modeFanGreen.style.display = "none";
        currentMode = "Cool";
    } else if (currentState === 1) { // Fan
        if (modeCoolGreen) modeCoolGreen.style.display = "none";
        if (modeCoolWhite) modeCoolWhite.style.display = "block";
        if (modeDryWhite) modeDryWhite.style.display = "block";
        if (modeDryGreen) modeDryGreen.style.display = "none";
        if (modeFanWhite) modeFanWhite.style.display = "none";
        if (modeFanGreen) modeFanGreen.style.display = "block";
        currentMode = "Fan";
    } else if (currentState === 2) { // Dry
        if (modeCoolGreen) modeCoolGreen.style.display = "none";
        if (modeCoolWhite) modeCoolWhite.style.display = "block";
        if (modeDryWhite) modeDryWhite.style.display = "none";
        if (modeDryGreen) modeDryGreen.style.display = "block";
        if (modeFanWhite) modeFanWhite.style.display = "block";
        if (modeFanGreen) modeFanGreen.style.display = "none";
        currentMode = "Dry";
    }

    console.log("Mode Tracker:", currentMode);
    // Only send data if this is not the initial load.
    if (!initialModeLoad) {
        sendModeData(currentMode);
        sendModeLambda("<?php echo $_SESSION['user_id']; ?>", currentMode);
    } else {
        // Turn off the flag after the first update.
        initialModeLoad = false;
    }

    // If the mode is Dry or Fan, force sleep mode off.
    if (currentMode === "Dry" || currentMode === "Fan") {
        const sleepWhite = document.getElementById("sleepWhite");
        const sleepGreen = document.getElementById("sleepGreen");
        if (sleepWhite && sleepGreen) {
            sleepWhite.style.display = "block";
            sleepGreen.style.display = "none";
        }
    }

    // Dispatch a custom event so other logic (like sleep and fan) knows the current mode.
    document.dispatchEvent(new CustomEvent("modeChanged", { detail: { mode: currentMode } }));
}

function sendModeData(mode) {
    const userID = "<?php echo $_SESSION['user_id']; ?>";
    fetch("../scripts/fetch-AC-data.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            user_id: userID,
            mode: mode,
        }),
    })
        .then((response) => response.text())
        .then((data) => {
            console.log("Response from fetch-AC-data.php:", data);
        })
        .catch((error) => {
            console.error("Error sending mode data:", error);
        });
}

// New function to send the mode to the Lambda API via API Gateway
function sendModeLambda(userId, mode) {
    // Prepare the data in the required format (no extra wrapping)
    const requestData = {
        data: {
            user_id: userId,
            mode: mode
        }
    };

    fetch('https://uev5bzg84f.execute-api.ap-southeast-1.amazonaws.com/dev-AcTemp/AcTemp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
        .then(response => response.json())
        .then(responseData => {
            console.log('Lambda mode response:', responseData);
        })
        .catch(error => {
            console.error("Error updating mode on Lambda:", error);
        });
}

// When the page loads, fetch the current mode from the database.
document.addEventListener("DOMContentLoaded", function () {
    fetchACMode();

    // Click event to toggle mode.
    const modeCont = document.querySelector(".modeCont");
    modeCont.addEventListener("click", function () {
        currentState = (currentState + 1) % 3; // Cycle through states 0,1,2,0
        updateModeDisplay();
    });
});

    // ============== SLEEP LOGIC ============== //

    // Global sleep state tracker (default: "Off")
    let sleepState = "Off";

    // Function to set sleep to "On"
    function setSleepOn() {
        // Prevent enabling sleep if mode is Dry or Fan
        if (typeof currentMode !== "undefined" && (currentMode === "Dry" || currentMode === "Fan")) {
            console.log("Sleep cannot be enabled in Dry or Fan mode.");
            return;
        }

        const sleepWhite = document.getElementById("sleepWhite");
        const sleepGreen = document.getElementById("sleepGreen");

        // Hide the "off" image and show the "on" image
        if (sleepWhite) sleepWhite.style.display = "none";
        if (sleepGreen) sleepGreen.style.display = "block";

        sleepState = "On";
        console.log("Sleep: On");
        sendSleepData(sleepState);
        sendSleepLambda("<?php echo $_SESSION['user_id']; ?>", sleepState);
    }

    // Function to set sleep to "Off"
    function setSleepOff() {
        const sleepWhite = document.getElementById("sleepWhite");
        const sleepGreen = document.getElementById("sleepGreen");

        // Show the "off" image and hide the "on" image
        if (sleepWhite) sleepWhite.style.display = "block";
        if (sleepGreen) sleepGreen.style.display = "none";

        sleepState = "Off";
        console.log("Sleep: Off");
        sendSleepData(sleepState);
        sendSleepLambda("<?php echo $_SESSION['user_id']; ?>", sleepState);
    }

    // Attach click events to both sleep images so clicking toggles the state.
    document.addEventListener("DOMContentLoaded", function () {
        const sleepWhite = document.getElementById("sleepWhite");
        const sleepGreen = document.getElementById("sleepGreen");

        // Clicking the "off" image toggles sleep mode on, if allowed.
        if (sleepWhite) {
            sleepWhite.addEventListener("click", function () {
                if (sleepState === "Off") {
                    setSleepOn();
                } else {
                    setSleepOff();
                }
            });
        }

        // Clicking the "on" image toggles sleep mode off.
        if (sleepGreen) {
            sleepGreen.addEventListener("click", function () {
                if (sleepState === "On") {
                    setSleepOff();
                } else {
                    setSleepOn();
                }
            });
        }
    });

    // Function to send the sleep state to the PHP backend
    function sendSleepData(state) {
        const userID = "<?php echo $_SESSION['user_id']; ?>"; // Dynamic user ID from session
        fetch("../scripts/fetch-AC-data.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                user_id: userID,
                sleep: state
            }),
        })
            .then(response => response.text())
            .then(data => {
                console.log("Sleep update response:", data);
            })
            .catch(error => {
                console.error("Error sending sleep data:", error);
            });
    }

    // New function to send the sleep state to the Lambda API via API Gateway
    function sendSleepLambda(userId, state) {
        // Prepare the JSON payload
        const requestData = {
            data: {
                user_id: userId,
                sleep: state
            }
        };

        // Make the fetch request to the Lambda API endpoint
        fetch('https://uev5bzg84f.execute-api.ap-southeast-1.amazonaws.com/dev-AcTemp/AcTemp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
            .then(response => response.json())
            .then(responseData => {
                console.log('Sleep Lambda response:', responseData);
            })
            .catch(error => {
                console.error("Error updating sleep status on Lambda:", error);
            });
    }



    // ============== SWING LOGIC ============== //
    // Global swing state tracker (default: "Off")
    let currentSwingState = "Off";

    // Function to set swing to "On"
    function setSwingOn() {
        const swingOnGreen = document.querySelector(".swingOn[src*='swingOn-Green']");
        const swingOnWhite = document.querySelector(".swingOn[src*='swingOn-White']");
        const swingOffGreen = document.querySelector(".swingOff[src*='swingOff-Green']");
        const swingOffWhite = document.querySelector(".swingOff[src*='swingOff-White']");

        // Update UI: show "On" images and adjust "Off" images as desired
        if (swingOnGreen) swingOnGreen.style.display = "block";
        if (swingOnWhite) swingOnWhite.style.display = "none";
        if (swingOffGreen) swingOffGreen.style.display = "none";
        if (swingOffWhite) swingOffWhite.style.display = "block";  // Changed from "none" to "block"

        currentSwingState = "On";
        console.log("Swing: On");
        sendSwingData(currentSwingState);
        sendSwingLambda("<?php echo $_SESSION['user_id']; ?>", currentSwingState);
    }

    // Function to set swing to "Off"
    function setSwingOff() {
        const swingOffGreen = document.querySelector(".swingOff[src*='swingOff-Green']");
        const swingOffWhite = document.querySelector(".swingOff[src*='swingOff-White']");
        const swingOnGreen = document.querySelector(".swingOn[src*='swingOn-Green']");
        const swingOnWhite = document.querySelector(".swingOn[src*='swingOn-White']");

        // Update UI: show "Off" images and hide "On" images
        if (swingOffGreen) swingOffGreen.style.display = "block";
        if (swingOffWhite) swingOffWhite.style.display = "none";
        if (swingOnGreen) swingOnGreen.style.display = "none";
        if (swingOnWhite) swingOnWhite.style.display = "block";

        currentSwingState = "Off";
        console.log("Swing: Off");
        sendSwingData(currentSwingState);
        sendSwingLambda("<?php echo $_SESSION['user_id']; ?>", currentSwingState);
    }

    // Function to update swing display based on provided state
    function updateSwingDisplay(state) {
        if (state === "On") {
            setSwingOn();
        } else {
            setSwingOff();
        }
    }

    // Function to send the swing state to the PHP backend
    function sendSwingData(state) {
        const userID = "<?php echo $_SESSION['user_id']; ?>"; // Dynamic user ID from session
        fetch("../scripts/fetch-AC-data.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                user_id: userID,
                swing: state
            }),
        })
            .then(response => response.text())
            .then(data => {
                console.log("Swing update response:", data);
            })
            .catch(error => {
                console.error("Error sending swing data:", error);
            });
    }

    // New function to send the swing state to the Lambda API via API Gateway
    function sendSwingLambda(userId, state) {
        // Prepare the data in the required format
        const requestData = {
            data: {
                user_id: userId,
                swing: state
            }
        };

        // Make the fetch request to the Lambda API endpoint
        fetch('https://uev5bzg84f.execute-api.ap-southeast-1.amazonaws.com/dev-AcTemp/AcTemp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
            .then(response => response.json())
            .then(responseData => {
                console.log('Swing Lambda response:', responseData);
            })
            .catch(error => {
                console.error("Error sending swing lambda data:", error);
            });
    }

    // When the page loads, attach the click event to the swing container
    document.addEventListener("DOMContentLoaded", function () {
        const swingCont = document.querySelector(".swingCont");
        if (swingCont) {
            swingCont.addEventListener("click", function () {
                // Toggle the swing state on click
                if (currentSwingState === "On") {
                    setSwingOff();
                } else {
                    setSwingOn();
                }
            });
        }
    });


    // ----------------------- timer ng bonak 
    document.addEventListener("DOMContentLoaded", function () {
    const progressBar = document.querySelector(".progress-bar");
    const timeLeftText = document.getElementById("time-left");
    const progressCircle = document.getElementById("progress-circle");
    const airconSwitch = document.getElementById("airconFFSwitch");

    let totalTime = 0; // Total time in hours
    let countdownInterval;
    let isRunning = false;
    let zeroCount = false; // Reset by clicking (exceeding 12 hours)
    let zeroTimer = false; // Timer naturally finished
    let timerStatus = "stopped"; // Default status
    const maxTime = 12; // Maximum time in hours
    const circleCircumference = 2 * Math.PI * 97.1;

    // Flag to indicate initial page load.
    let initialLoad = true;

    // Load stored timer from localStorage if available.
    if (localStorage.getItem("totalTime")) {
        totalTime = parseInt(localStorage.getItem("totalTime"), 10);
    }

    // Fetch the timer and status from the database.
    fetch("../scripts/fetch-AC-data.php?fetchTimer=1")
        .then(response => response.json())
        .then(data => {
            if (data.timer !== undefined && data.timer !== null) {
                totalTime = parseInt(data.timer, 10);
            }
            if (data.timer_status) {
                timerStatus = data.timer_status;
            }

            // Resume countdown if the timer was running.
            if (timerStatus === "running" && totalTime > 0) {
                isRunning = true;
                airconSwitch.checked = true;
                startCountdown();
            }
            // Call updateTimer once after fetching data.
            updateTimer();
        })
        .catch(error => console.error("Error fetching timer:", error));

    function updateTimer() {
        let displayHours = totalTime;
        if (displayHours > maxTime) displayHours = maxTime;
        timeLeftText.textContent = String(displayHours).padStart(2, "0");

        // Update progress bar.
        const dashoffset = circleCircumference - (circleCircumference * totalTime) / maxTime;
        progressBar.style.strokeDashoffset = dashoffset;

        console.log(`Timer Set: ${displayHours} hour(s), Status: ${timerStatus}`);

        // Only send data to DB and Lambda if not the initial load.
        if (!initialLoad) {
            updateTimerToDatabase(displayHours, timerStatus);
            sendTimerLambda("<?php echo $_SESSION['user_id']; ?>", displayHours, timerStatus);
        } else {
            // Turn off the flag after the first update.
            initialLoad = false;
        }

        localStorage.setItem("totalTime", totalTime);
    }

    // Countdown function.
    function startCountdown() {
        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            if (totalTime > 0) {
                totalTime--;
                updateTimer();
            } else {
                clearInterval(countdownInterval);
                totalTime = 0;
                isRunning = false;
                zeroTimer = true;
                timerStatus = "stopped";
                console.log("Timer Finished - turning off AC");

                airconSwitch.checked = false;
                toggleAirconFF();

                updateTimer();
            }
        }, 3600000); // 1 hour interval (3600000 ms)
    }

    // Click on progress circle adds 1 hour.
    progressCircle.addEventListener("click", function () {
        if (!isRunning) {
            isRunning = true;
            timerStatus = "running";
            airconSwitch.checked = true;
            startCountdown();
        }
        totalTime += 1;
        if (totalTime > maxTime) {
            totalTime = 0;
            zeroCount = true;
            clearInterval(countdownInterval);
            isRunning = false;
            timerStatus = "stopped";
            airconSwitch.checked = false;
            console.log("Timer reset via click - stopping AC");
        } else {
            zeroCount = false;
        }
        updateTimer();
    });

    // Listen for a custom event to reset the timer when AC is turned off.
    document.addEventListener("airconOff", function () {
        totalTime = 0;
        clearInterval(countdownInterval);
        isRunning = false;
        timerStatus = "stopped";
        console.log("Timer Reset due to AC Off");
        updateTimer();
    });

    // Removed the redundant updateTimer() call here.
    // Function to send timer data to the backend.
    function updateTimerToDatabase(hoursValue, status) {
        fetch("../scripts/fetch-AC-data.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                timer: hoursValue,
                timer_status: status,
                user_id: "<?php echo $_SESSION['user_id']; ?>"
            }),
        })
            .then(response => response.json())
            .then(data => console.log("Timer updated in DB:", data))
            .catch(error => console.error("Error updating timer:", error));
    }

    // Function to send timer data to AWS Lambda.
    function sendTimerLambda(userId, hoursValue, status) {
        const requestData = {
            data: {
                user_id: userId,
                timer: hoursValue,
                timer_status: status
            }
        };

        fetch("https://uev5bzg84f.execute-api.ap-southeast-1.amazonaws.com/dev-AcTemp/AcTemp", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(requestData),
        })
            .then(response => response.json())
            .then(responseData => console.log("Timer Lambda response:", responseData))
            .catch(error => console.error("Error updating timer on Lambda:", error));
    }
});


    //  ============== POWER ON?OFF  ============== //

    function updatePowerStatus() {
        const switchElement = document.getElementById("airconFFSwitch");
        const powerStatus = switchElement.checked ? "On" : "Off";

        fetch("../scripts/fetch-AC-data.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                user_id: "<?php echo $_SESSION['user_id']; ?>",
                power: powerStatus
            }),
        })
            .then((response) => response.text())
            .then((data) => {
                console.log("Power status updated:", data);
                // Optionally, you can call a function here to refresh the UI based on the database.
                // For example: fetchACLog();
            })
            .catch((error) => {
                console.error("Error updating power status:", error);
            });
    }











    //  ============== AC REMOTE EFFECTS  ============== //

    // Only apply interactive behavior to images that should be interactive
    document.querySelectorAll(".remote-container img").forEach((img) => {
        if (!img.classList.contains("bgRem") && !img.classList.contains("tempbar")) {
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
        if (
            e.target.tagName === "IMG" &&
            !e.target.classList.contains("bgRem") &&
            !e.target.classList.contains("tempbar")
        ) {
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
            parent.clientWidth / 400, // Scale width based on .ACRMain
            parent.clientHeight / 800 // Scale height based on .ACRMain
        );

        container.style.transform = "scale(" + scale + ")";
    }

    window.addEventListener("resize", scaleRemote);
    scaleRemote(); // Run once on page load
</script>