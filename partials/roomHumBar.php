<?php
// Simple humidity value for demonstration
$humidity = 75;  // Replace this with the actual humidity value you want to display
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Humidity Circular Progress Bar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }

        .wrapper {
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .circular-bar {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: conic-gradient(#34a853 0%, #e8f0f7 0%);
            margin-bottom: 20px;
        }

        .circular-bar::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 15px;
            width: 120px;
            height: 120px;
            background-color: #fff;
            border-radius: 50%;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .percent {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        label {
            font-size: 16px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="circular-bar" id="circular-bar">
        <div class="percent" id="percent">0%</div>
    </div>
    <label>Humidity</label>
</div>

<script>
    // Get the humidity value (this would be from PHP or any other source)
    let humidity = <?php echo $humidity; ?>;
    console.log("Humidity: ", humidity); // Check the value passed from PHP

    let circularBar = document.getElementById('circular-bar');
    let percentDisplay = document.getElementById('percent');

    let initialValue = 0;
    let finalValue = humidity;  // Target humidity value
    let speed = 10;  // Speed of the progress animation

    // Update the circular bar and percentage
    let interval = setInterval(() => {
        if (initialValue < finalValue) {
            initialValue += 1; // Increment by 1% each interval
        }

        // Update the circular progress bar with a conic gradient
        circularBar.style.background = `conic-gradient(#34a853 ${initialValue / 100 * 360}deg, #e8f0f7 0deg)`;

        // Display the percentage in the center
        percentDisplay.innerText = initialValue + '%';

        // Stop the interval when the target is reached
        if (initialValue >= finalValue) {
            clearInterval(interval);
        }
    }, speed);
</script>

</body>
</html>
