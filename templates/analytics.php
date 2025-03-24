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
    <link rel="stylesheet" href="../assets/css/analytics.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="containerPart">
            <h2>Analytics</h2>

            <div class="firstPart">
                <p>Water Tank Level</p>
                <div class="textContainer">
                    <div class="weekWater">
                        <div class="noteWeek"></div>
                        <p>Ave. Weekly Water Usage</p>
                        <span>500 L</span>
                    </div>

                    <div class="monthWater">
                        <div class="noteMonth"></div>
                        <p>Ave. Monthly Water Usage</p>
                        <span>3,500 L</span>
                    </div>
                </div>
                <div class="waterGauge">
                    <?php include '../partials/gaugeChart.php'; ?>
                </div>

                <div class="deviderFirst"> </div>

                <div class="roomTempSelect">
                    <span class="roomTitle">Select Room Temperature:</span>
                    <select id="roomTempDropdown" class="dropdown">
                        <option value="1stFloor">Room Temp - 1st Floor</option>
                        <option value="2ndFloor">Room Temp - 2nd Floor</option>
                        <option value="3rdFloor">Room Temp - 3rd Floor</option>
                        <option value="4thFloor">Room Temp - 4th Floor</option>
                    </select>
                </div>

                <div class="chartRoomCont">
                    <div class="roomTemp">
                        <?php include '../partials/roomTempBar.php'; ?>
                    </div>

                    <div class="roomHum">
                        <?php include '../partials/roomHumBar.php'; ?>
                    </div>
                </div>
            </div>

            <!-- SMART LIGHTS -->
            <div class="secondPart">
                <p>Smart Lights</p>
                <div class="textContainer">
                    <div class="overallAveLightsOn">
                        <div class="noteOAL"></div>
                        <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Overall Ave. Lights On</p>
                        <span class="OALpercen">
                            75%
                        </span>
                    </div>
                    <div class="peakUsageHours">
                        <div class="notePUH"></div>
                        <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Peak Usage Hours</p>
                        <span class="PUHtime">
                            9AM to 4PM
                        </span>
                    </div>
                </div>
                <div class="linechartlights">
                    <canvas id="yearlyUsageChart" width="400" height="250"></canvas>
                </div>
            </div>

            <!-- AIRCON -->
            <div class="thirdPart">
                <div class="acHeaderCont">
                    <p>Airconditioners</p>
                    <select id="floorDropdown" class="acDD">
                        <option value="first" selected>First Floor</option>
                        <option value="second">Second Floor</option>
                        <option value="third">Third Floor</option>
                        <option value="fourth">Fourth Floor</option>
                        <option value="fifth">Fifth Floor</option>
                    </select>
                </div>
                <div class="areaChartAC">
                    <canvas id="airconChart" width="200" height="125"></canvas>
                </div>
            </div>

            <div class="fourthPart">
                <div class="pieWrapper">
                    <div class="pieCont">
                        <p>Usage by Area</p>
                        <div class="legendsCont">
                            <div class="legend-item ffCont">
                                <div class="legend-top">
                                    <div class="noteffCont"></div>
                                    <p>First Floor</p>
                                </div>
                                <span>7%</span>
                            </div>
                            <div class="legend-item sfCont">
                                <div class="legend-top">
                                    <div class="notesfCont"></div>
                                    <p>Second Floor</p>
                                </div>
                                <span>7%</span>
                            </div>
                            <div class="legend-item tfCont">
                                <div class="legend-top">
                                    <div class="notetfCont"></div>
                                    <p>Third Floor</p>
                                </div>
                                <span>7%</span>
                            </div>
                            <div class="legend-item fofCont">
                                <div class="legend-top">
                                    <div class="notefofCont"></div>
                                    <p>Fourth Floor</p>
                                </div>
                                <span>7%</span>
                            </div>
                            <div class="legend-item fifCont">
                                <div class="legend-top">
                                    <div class="notefifCont"></div>
                                    <p>Fifth Floor</p>
                                </div>
                                <span>7%</span>
                            </div>
                        </div>
                    </div>
                    <div class="pieChartLightFloors">
                        <canvas id="myPieChartUnique"></canvas>
                        <div id="customLegendContainer"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    </div>



</body>

</html>

<!-- SMART LIGHTS -->
<script>
    // JavaScript for SMART LIGHTS chart
    let yearlyUsageChart; // Global variable for the chart instance

    function renderYearlyUsageChart() {
        const ctx = document.getElementById('yearlyUsageChart').getContext('2d');

        // Destroy the chart if it already exists
        if (yearlyUsageChart) {
            yearlyUsageChart.destroy();
        }

        // Create the chart
        yearlyUsageChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'],
                datasets: [{
                    label: 'Usage (%)',
                    data: [70, 75, 72, 80, 78, 82, 85, 80, 77, 79, 74, 81],
                    fill: false,
                    borderColor: 'rgb(1, 155, 173)',
                    pointBackgroundColor: 'rgb(11, 50, 54)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Usage Percentage' }
                    },
                    x: {
                        title: { display: true, text: 'Month' }
                    }
                }
            }
        });
    }

    renderYearlyUsageChart();

</script>

<!-- AIRCON -->
<script>
    let airconChart; // Global variable for the aircon chart

    function renderAirconChart() {
        const ctx = document.getElementById('airconChart').getContext('2d');

        // Destroy previous instance if it exists
        if (airconChart) {
            airconChart.destroy();
        }

        const labels = Array.from({ length: 8 }, (_, i) => `Hour ${i + 1}`);
        const data = {
            labels: labels,
            datasets: [{
                label: 'Aircon Consumption (kWh)',
                data: Array(8).fill(1.5),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                scales: {
                    x: {
                        stacked: true,
                        title: { display: true, text: 'Hour of Usage' }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: { display: true, text: 'Energy Consumption (kWh)' }
                    }
                }
            }
        };

        airconChart = new Chart(ctx, config);
    }

    renderAirconChart();
</script>

<!-- PIE CHART -->
<script>
    // Global variable to store the chart instance
    let myPieChart;

    function renderPieChart() {
        const ctx = document.getElementById('myPieChartUnique').getContext('2d');

        // Destroy existing chart instance if it exists to prevent conflict
        if (myPieChart) {
            myPieChart.destroy();
        }

        // Data for the pie chart with 5 entries
        const data = {
            labels: ['First Floor', 'Second Floor', 'Third  Floor', 'Fourth  Floor', 'Fifth  Floor'],
            datasets: [{
                data: [10, 20, 30, 25, 15],
                backgroundColor: [
                    'rgba(231, 243, 183, 1)',
                    'rgba(217, 237, 141, 1)',
                    'rgba(185, 189, 4, 1)',
                    'rgba(185, 189, 4, 1)',
                    'rgba(123, 125, 4, 1)'
                ],
                borderColor: [
                    'rgba(231, 243, 183, 1)',
                    'rgba(217, 237, 141, 1)',
                    'rgba(185, 189, 4, 1)',
                    'rgba(185, 189, 4, 1)',
                    'rgba(123, 125, 4, 1)'
                ],
                borderWidth: 1
            }]
        };

        // Chart configuration with custom legend callback
        const config = {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Hide the default legend
                    },
                    title: {
                        display: true,
                        text: ''
                    }
                }
            },
            // Create a custom legend using the legendCallback option
            plugins: [{
                beforeInit: function (chart) {
                    chart.options.legendCallback = function (chart) {
                        let text = ['<ul class="my-custom-legend">'];
                        // Define the custom labels for the legend
                        const floorLabels = ["First Floor", "Second Floor", "Third Floor", "Fourth Floor", "Fifth Floor"];
                        const data = chart.data;
                        if (data.datasets.length) {
                            floorLabels.forEach((label, index) => {
                                text.push(
                                    '<li class="legend-item">' +
                                    '<span class="legend-box" style="background-color:' + data.datasets[0].backgroundColor[index] + '"></span>' +
                                    '<span class="legend-text">' + label + '</span>' +
                                    '</li>'
                                );
                            });
                        }
                        text.push('</ul>');
                        return text.join("");
                    };
                }
            }]
        };

        // Initialize the chart and store its instance globally
        myPieChart = new Chart(ctx, config);

        // Generate and insert the custom legend HTML into the designated container
        document.getElementById('customLegendContainer').innerHTML = myPieChart.generateLegend();
    }

    // Call the function to render the chart
    renderPieChart();
</script>