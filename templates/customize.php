<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize</title>
    <link rel="stylesheet" href="../assets/css/customize.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>
    <div class="bgMain">
        <?php include "../partials/bgMain.php"; ?>

        <div class="dashboardDevider">
            <div class="h1">Customize</div>
            <div class="customControls">

                <div class="waterTankControl">
                    <div class="control-content">
                        <img class="control-icon" src="../assets/images/waterTank.png" alt="Water Tank">
                        <h3>Water Tank</h3>

                        <div class="switch-container">
                            <label class="switch" id="waterSwitch">
                                <input type="checkbox">
                                <span class="slider"></span>

                            </label>
                        </div>
                    </div>

                    <div class="setwaterLevel">Set Water Levels</div>
                    <div class="sliderWaterCont">
                        <div class="minimumGroup">
                            <span>
                                Minimum Level
                            </span>
                            <div class="range-slider">
                                <input type="range" min="0" max="50" value="25" class="waterMinimumLevel" id="minWater">
                                <div class="tooltip">25</div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">50</div>
                            </div>

                            <span>
                                Maximum Level
                            </span>
                            <div class="range-slider">
                                <input type="range" min="50" max="100" value="75" class="waterMaximumLevel"
                                    id="maxWater">
                                <div class="tooltip">75</div>
                                <div class="start-tooltip">50</div>
                                <div class="end-tooltip">100</div>
                            </div>

                            <span>
                                Current Level
                            </span>
                            <div class="range-slider non-sliding">
                                <div id="showCurrent" class="slider-bar" style="width: <?php echo $currentLevel; ?>%;">
                                </div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">100</div>
                            </div>


                            <script>
                                // Function to fetch and update current water level
                                function updateCurrentWaterLevel() {
                                    fetch('../scripts/customize_waterTank.php')
                                        .then(response => {
                                            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                                            return response.text();
                                        })
                                        .then(data => {
                                            const currentLevel = Math.round(Number(data));

                                            if (isNaN(currentLevel) || currentLevel < 0 || currentLevel > 100) {
                                                throw new Error(`Invalid value received: ${data}`);
                                            }

                                            const currentBar = document.getElementById('showCurrent');
                                            // Set CSS variable for both element and pseudo-element
                                            currentBar.style.setProperty('--current-level', `${currentLevel}%`);
                                        })
                                        .catch(error => {
                                            console.error('Water level update failed:', error);
                                            // Optional: Show user-friendly error message
                                        });
                                }
                                // Update immediately and every 5 seconds
                                updateCurrentWaterLevel();
                                setInterval(updateCurrentWaterLevel, 5000);
                            </script>
                        </div>
                    </div>
                </div>

                <div class="airconditioningControl">
                    <div class="control-content">
                        <img class="control-icon" src="../assets/images/ac.png" alt="Air Conditioning">
                        <h3>Air Conditioning</h3>



                        <!-- LAMBDA NG TEMP -->
                        <?php
                        include '../app/config/connection.php';

                        try {
                            // Retrieve the power value from the acRemote table.
                            $stmt = $conn->prepare("SELECT power FROM acRemote LIMIT 1");
                            $stmt->execute();
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            // Determine power status: default to "off" if not found.
                            $powerStatus = ($row && strtolower(trim($row['power'])) === 'on') ? 'on' : 'off';
                        } catch (PDOException $e) {
                            // In case of an error, default to "off"
                            $powerStatus = 'off';
                        }
                        ?>

                        <div class="switch-container">
                            <label class="switch" id="acSwitch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <script>
                            // Make powerStatus global.
                            const powerStatus = <?php echo json_encode($powerStatus); ?>;
                            // Global flag to prevent sending multiple alerts until conditions reset.
                            let lambdaAlertSent = false;

                            document.addEventListener("DOMContentLoaded", function () {
                                // Select elements
                                const acSwitch = document.querySelector("#acSwitch input[type='checkbox']");
                                const acMinSlider = document.querySelector(".acMinimumLevel");
                                const acMaxSlider = document.querySelector(".acMaximumLevel");

                                // If power is "off", disable the acSwitch and sliders.
                                if (powerStatus === "off") {
                                    acSwitch.disabled = true;
                                    acMinSlider.disabled = true;
                                    acMaxSlider.disabled = true;
                                }
                            });
                        </script>

                        <script>
                            // Combined update: fetch temperature and then thresholds.
                            function updateCurrentRoomTempAndThresholds() {
                                // Fetch current temperature from customize_AC.php
                                fetch('../scripts/customize_AC.php')
                                    .then(response => {
                                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                                        return response.json();
                                    })
                                    .then(tempData => {
                                        const temperature = parseFloat(tempData.temperature);
                                        if (isNaN(temperature) || temperature < 0 || temperature > 50) {
                                            throw new Error(`Invalid temperature value received: ${tempData.temperature}`);
                                        }
                                        // Now fetch the minTemp and maxTemp from fetch_customize.php
                                        fetch('../scripts/fetch_customize.php')
                                            .then(response => {
                                                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                                                return response.json();
                                            })
                                            .then(thresholdData => {
                                                const minTemp = parseFloat(thresholdData.minTemp);
                                                const maxTemp = parseFloat(thresholdData.maxTemp);
                                                if (isNaN(minTemp) || isNaN(maxTemp)) {
                                                    console.error("Received invalid threshold values. Using fallback defaults.");
                                                }
                                                // Fallback defaults in case thresholds are missing:
                                                const finalMinTemp = !isNaN(minTemp) ? minTemp : 35;
                                                const finalMaxTemp = !isNaN(maxTemp) ? maxTemp : 50;

                                                // Update UI with current temperature.
                                                const percentage = (temperature / 50) * 100;
                                                const sliderBar = document.getElementById('showCurrentTemp');
                                                if (sliderBar) {
                                                    sliderBar.style.setProperty('--current-level', percentage + '%');
                                                }
                                                const roomTempDiv = document.querySelector('.range-slider.non-slidingAC .roomTemp');
                                                if (roomTempDiv) {
                                                    roomTempDiv.innerText = temperature + '°C';
                                                }

                                                // Now update Lambda alert logic.
                                                updateACValuesLambda(temperature, finalMinTemp, finalMaxTemp);
                                            })
                                            .catch(error => {
                                                console.error('Threshold fetch failed:', error);
                                            });
                                    })
                                    .catch(error => {
                                        console.error('Room temperature update failed:', error);
                                    });
                            }

                            // AWS Lambda Update: Send alert only once if temperature is out of range.
                            // Reset the alert flag when temperature returns within range.
                            function updateACValuesLambda(currentTemp, minTemp, maxTemp) {
                                // If power is on, do not send any alert.
                                if (powerStatus === "on") {
                                    console.log("Power is on; lambda alert will not be sent.");
                                    return;
                                }

                                console.log(`Current Temperature: ${currentTemp}°C, Min: ${minTemp}°C, Max: ${maxTemp}°C`);

                                // If temperature is out-of-range and alert hasn't been sent, send alert.
                                if ((currentTemp < minTemp || currentTemp > maxTemp) && !lambdaAlertSent) {
                                    const payload = {
                                        body: JSON.stringify({
                                            alert: `Temperature (${currentTemp}°C) is out of range!`,
                                            minTemp: minTemp,
                                            maxTemp: maxTemp,
                                            currentTemp: currentTemp
                                        })
                                    };

                                    fetch('https://y9saie9s20.execute-api.ap-southeast-1.amazonaws.com/dev/controlDevice', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify(payload)
                                    })
                                        .then(response => response.json())
                                        .then(data => {
                                            console.log('AWS Lambda Alert Sent:', data);
                                            lambdaAlertSent = true; // Mark that alert has been sent.
                                        })
                                        .catch(error => console.error('Lambda Alert Error:', error));
                                }
                                // If temperature returns to normal, reset the alert flag so a new alert can be sent later.
                                else if (currentTemp >= minTemp && currentTemp <= maxTemp) {
                                    if (lambdaAlertSent) {
                                        console.log("Temperature is back within range. Resetting alert flag.");
                                        lambdaAlertSent = false;
                                    }
                                    console.log(`Temperature (${currentTemp}°C) is within the allowed range.`);
                                }
                            }

                            // INITIALIZE: Call the combined update function on page load and every 5 seconds.
                            updateCurrentRoomTempAndThresholds();
                            setInterval(updateCurrentRoomTempAndThresholds, 5000);
                        </script>




                    </div>

                    <div class="settemprange">Set Temperature Range</div>
                    <div class="sliderACCont">
                        <div class="minimumGroup">
                            <span>
                                Minimum Room Temperature
                            </span>
                            <!-- AC Minimum Temperature Slider -->
                            <div class="range-slider"
                                style="display: flex; align-items: center; justify-content: space-between;">
                                <input type="range" min="0" max="35" class="acMinimumLevel" style="flex-grow: 1;">
                                <div class="maxTempRight" style="margin-left: 10px;">35°C</div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">35</div>
                            </div>
                            <span>
                                Maximum Room Temperature
                            </span>
                            <!-- AC Maximum Temperature Slider -->
                            <div class="range-slider"
                                style="display: flex; align-items: center; justify-content: space-between;">
                                <input type="range" min="36" max="50" value="37" class="acMaximumLevel"
                                    style="flex-grow: 1;">
                                <div class="maxTempLeft" style="margin-left: 10px;">50°C</div>
                                <div class="start-tooltip">36</div>
                                <div class="end-tooltip">50</div>
                            </div>


                            <span>
                                Current Room Temperature
                            </span>

                            <div class="range-slider non-slidingAC"
                                style="display: flex; align-items: center; justify-content: space-between;">
                                <div id="showCurrentTemp" class="slider-barTemp" style="flex-grow: 1;"></div>
                                <div class="maxTempRight" style="margin-left: 10px;">50°C</div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">50</div>
                            </div>



                            <!-- OFF AND ON CONNECTION -->
                            <br>
                            <?php
                            include '../app/config/connection.php';

                            try {
                                // Retrieve the 'power' value from the acRemote table. Adjust the query as needed.
                                $stmt = $conn->prepare("SELECT power FROM acRemote LIMIT 1");
                                $stmt->execute();
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                                if ($row && strtolower(trim($row['power'])) === 'on') {
                                    echo '<span>AC status: <span style="color: red;">ON</span></span>';
                                } else {
                                    echo '<span>AC status: OFF</span>';
                                }
                            } catch (PDOException $e) {
                                // In case of an error, you can either log it or show a default status.
                                echo '<span>AC status: Unknown</span>';
                            }
                            ?>


                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>


    </div>

    </div>
    </div>

</body>

</html>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const waterSwitch = document.getElementById("waterSwitch").querySelector("input[type='checkbox']");
        const minWaterSlider = document.getElementById("minWater");
        const maxWaterSlider = document.getElementById("maxWater");
        const minTooltip = document.querySelector(".waterMinimumLevel + .tooltip");
        const maxTooltip = document.querySelector(".waterMaximumLevel + .tooltip");

        // Define updateWaterValues here so it's in scope for later events.
        function updateWaterValues() {
            const payload = {
                minWater: parseFloat(minWaterSlider.value),
                maxWater: parseFloat(maxWaterSlider.value)
            };

            fetch('../scripts/update_customizeWater.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(response => response.text())
                .then(result => console.log('Water values updated in DB:', result))
                .catch(error => console.error('Error updating water values in DB:', error));
        }

        // Update water values in AWS Lambda using current slider values
        function updateWaterValuesLambda() {
            // Prepare the payload in the expected nested JSON format
            const payload = {
                body: JSON.stringify({
                    data: {
                        minWater: minWaterSlider.value,
                        maxWater: maxWaterSlider.value
                    }
                })
            };

            fetch('https://y9saie9s20.execute-api.ap-southeast-1.amazonaws.com/dev/controlDevice', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(responseData => {
                    console.log('Water values updated via Lambda:', responseData);
                })
                .catch(error => {
                    console.error("Error updating water values via Lambda:", error);
                });
        }

        // Function to update slider appearance and tooltip
        function updateSlider(slider, tooltip) {
            const min = Number(slider.getAttribute("min")) || 0;
            const max = Number(slider.getAttribute("max")) || 100;
            const value = Number(slider.value);
            const percentage = ((value - min) / (max - min)) * 100;
            slider.style.backgroundSize = `${percentage}% 100%`;
            tooltip.textContent = value;
            const thumbPosition = (slider.offsetWidth * percentage) / 100;
            tooltip.style.left = `${thumbPosition}px`;
        }

        // Fetch water settings from the database and update sliders and tooltips
        function fetchWaterValues() {
            fetch('../scripts/fetch_customizeWater.php')
                .then(response => response.json())
                .then(data => {
                    if (data.minWater !== undefined && data.maxWater !== undefined) {
                        minWaterSlider.value = data.minWater;
                        maxWaterSlider.value = data.maxWater;
                        // Force a reflow
                        void minWaterSlider.offsetWidth;
                        void maxWaterSlider.offsetWidth;
                        updateSlider(minWaterSlider, minTooltip);
                        updateSlider(maxWaterSlider, maxTooltip);
                        // Dispatch an input event to mimic user interaction
                        minWaterSlider.dispatchEvent(new Event('input'));
                        maxWaterSlider.dispatchEvent(new Event('input'));
                    }
                })
                .catch(error => console.error('Error fetching water values:', error));
        }

        // Initialize by fetching current water settings from the database
        fetchWaterValues();

        // When the switch is toggled
        waterSwitch.addEventListener('change', function () {
            if (waterSwitch.checked) {
                // Switch ON: Enable the sliders for adjustments
                minWaterSlider.disabled = false;
                maxWaterSlider.disabled = false;
            } else {
                // Switch OFF: Disable the sliders and send the current values to the database and Lambda
                minWaterSlider.disabled = true;
                maxWaterSlider.disabled = true;
                updateWaterValues();
                updateWaterValuesLambda();
            }
        });

        // Update slider visuals on user input (if switch is on)
        minWaterSlider.addEventListener('input', function () {
            if (waterSwitch.checked) {
                updateSlider(minWaterSlider, minTooltip);
            }
        });
        maxWaterSlider.addEventListener('input', function () {
            if (waterSwitch.checked) {
                updateSlider(maxWaterSlider, maxTooltip);
            }
        });
    });

</script>


<!-- AIRCON -->


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const minSlider = document.querySelector(".waterMinimumLevel");
        const maxSlider = document.querySelector(".waterMaximumLevel");
        const minTooltip = document.querySelector(".waterMinimumLevel + .tooltip");
        const maxTooltip = document.querySelector(".waterMaximumLevel + .tooltip");

        // Function to update the slider appearance and progress
        function updateSlider(slider, tooltip) {
            const min = slider.min ? slider.min : 0;
            const max = slider.max ? slider.max : 100;
            const val = slider.value;

            // Calculate percentage of the filled portion
            const percentage = ((val - min) / (max - min)) * 100;

            // Set the background gradient to shift with the thumb
            slider.style.backgroundSize = `${percentage}% 100%`;

            // Calculate the tooltip position to be centered on the thumb
            const thumbPosition = (percentage / 100) * slider.offsetWidth;

            // Update tooltip position to center it over the thumb
            tooltip.style.left = `calc(${thumbPosition}px - 0px)`;  // Adjust this to center the tooltip
            tooltip.textContent = val;
        }

        // Update the slider when its value changes
        minSlider.addEventListener("input", function () {
            updateSlider(minSlider, minTooltip);
        });
        maxSlider.addEventListener("input", function () {
            updateSlider(maxSlider, maxTooltip);
        });

        // Optionally show/hide the tooltip on hover
        minSlider.addEventListener("mouseover", () => minTooltip.style.display = "block");
        minSlider.addEventListener("mouseout", () => minTooltip.style.display = "none");

        maxSlider.addEventListener("mouseover", () => maxTooltip.style.display = "block");
        maxSlider.addEventListener("mouseout", () => maxTooltip.style.display = "none");

        // Initialize slider state
        updateSlider(minSlider, minTooltip);
        updateSlider(maxSlider, maxTooltip);
    });

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // 1. Element Selection
        const acSwitch = document.querySelector("#acSwitch input[type='checkbox']");
        const acMinSlider = document.querySelector(".acMinimumLevel");
        const acMaxSlider = document.querySelector(".acMaximumLevel");

        // 2. Tooltip Selection (using the adjacent sibling approach)
        const acMinTooltip = acMinSlider.nextElementSibling;
        const acMaxTooltip = acMaxSlider.nextElementSibling;

        // 3. Function to Update Slider Appearance and Tooltip Position
        function updateSlider(slider, tooltip) {
            const min = parseFloat(slider.min);
            const max = parseFloat(slider.max);
            const value = parseFloat(slider.value);
            const percentage = ((value - min) / (max - min)) * 100;

            slider.style.backgroundSize = `${percentage}% 100%`;
            tooltip.textContent = value;

            // Adjust tooltip to center it over the slider thumb
            const thumbPosition = (slider.offsetWidth * percentage) / 100;
            tooltip.style.left = `${thumbPosition - 10}px`;
        }

        // 4. Update AC Values in Local Database
        function updateACValues() {
            const payload = {
                minTemp: acMinSlider.value,
                maxTemp: acMaxSlider.value
            };

            fetch('../scripts/update_customizeAC.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) throw new Error('Update failed');
                    return response.text();
                })
                .then(data => {
                    console.log("Local DB update:", data);
                })
                .catch(error => console.error('DB error:', error));
        }

        // 5. Handle Slider Input Events
        function handleSliderInput() {
            updateSlider(acMinSlider, acMinTooltip);
            updateSlider(acMaxSlider, acMaxTooltip);
            updateACValues(); // Update local database on every change
        }

        // 6. Modified AC Switch Handler
        acSwitch.addEventListener('change', function () {
            const isActive = this.checked;
            acMinSlider.disabled = !isActive;
            acMaxSlider.disabled = !isActive;

            // When turning OFF the AC, update the local database only.
            if (!isActive) {
                updateACValues();
            }
        });

        // 7. Add Event Listeners for the Sliders
        acMinSlider.addEventListener('input', handleSliderInput);
        acMaxSlider.addEventListener('input', handleSliderInput);

        // 8. Fetch Initial Values from Server
        function fetchACValues() {
            fetch('../scripts/fetch_customizeAC.php')
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    acMinSlider.value = data.minTemp || 28;
                    acMaxSlider.value = data.maxTemp || 37;
                    handleSliderInput(); // Initialize UI
                })
                .catch(error => console.error('Fetch error:', error));
        }

        // 9. Initialize on Page Load
        fetchACValues();
    });
</script>