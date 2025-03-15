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
                            <label class="switch">
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
                                <input type="range" min="0" max="100" value="50" class="waterMinimumLevel">
                                <div class="tooltip">50</div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">100</div>
                            </div>

                            <span>
                                Maximum Level
                            </span>
                            <div class="range-slider">
                                <input type="range" min="0" max="100" value="50" class="waterMaximumLevel">
                                <div class="tooltip">50</div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">100</div>
                            </div>

                            <span>
                                Current Level
                            </span>
                            <div class="range-slider non-sliding">
                                <div class="slider-bar"></div>
                                <div class="start-tooltip">0</div>
                                <div class="end-tooltip">100</div>
                            </div>
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