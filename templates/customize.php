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

                        <div class="switch-container">
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settemprange">Set Temperature Range</div>
                    <div class="sliderACCont">
                        <div class="minimumGroup">
                            <span>
                                Minimum Level
                            </span>
                            <div class="range-slider">
                                <input type="range" min="0" max="100" value="50" class="acMinimumLevel">
                                <div class="tooltip">50</div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">100</div>
                            </div>

                            <span>
                                Maximum Level
                            </span>
                            <div class="range-slider">
                                <input type="range" min="0" max="100" value="50" class="acMaximumLevel">
                                <div class="tooltip">50</div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">100</div>
                            </div>

                            <span>
                                Current Level
                            </span>
                            <div class="range-slider non-slidingAC">
                                <div class="slider-bar"></div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">100</div>
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
        const minSlider = document.querySelector(".acMinimumLevel");
        const maxSlider = document.querySelector(".acMaximumLevel");
        const minTooltip = document.querySelector(".acMinimumLevel + .tooltip");
        const maxTooltip = document.querySelector(".acMaximumLevel + .tooltip");

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