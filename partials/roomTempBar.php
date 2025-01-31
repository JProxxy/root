<?php
// Include the connection.php file to use the existing database connection
require '../app/config/connection.php'; // Make sure the path to connection.php is correct

try {
    // Fetch the latest temperature for the device 'ffRoom-temp'
    $query = "SELECT temperature FROM room_data WHERE deviceName = 'ffRoom-temp' ORDER BY timestamp DESC LIMIT 1";
    $stmt = $conn->prepare($query); // Use the $conn object from connection.php
    $stmt->execute();

    // Get the result
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $temperature = isset($row['temperature']) ? $row['temperature'] : null;

} catch (PDOException $e) {
    // Handle connection errors or query issues
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circular Progress Bar</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            font-family: 'Poppins', Arial;
            box-sizing: border-box;
        }

        .wrapper {
            box-shadow: 6px 6px 10px -1px rgba(0, 0, 0, 0.15),
                -6px -6px 10px -1px rgba(255, 255, 255, 0.7);
            width: 240px;
            padding: 30px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 7px;
            background-color: #e8f0f7;
        }

        .circular-bar {
            width: 160px;
            height: 160px;
            background: conic-gradient(#4285f4 1.5deg, #e8f0f7 0deg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 6px 6px 10px -1px rgba(0, 0, 0, 0.15),
                -6px -6px 10px -1px rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
            position: relative;
        }

        .circular-bar::before {
            content: "";
            position: absolute;
            width: 140px;
            height: 140px;
            background: #e8f0f7;
            border-radius: 50%;
            box-shadow: inset 6px 6px 10px -1px rgba(0, 0, 0, 0.15),
                inset -6px -6px 10px -1px rgba(255, 255, 255, 0.7);
        }

        .degree {
            z-index: 10;
            font-size: 20px;
            font-weight: bold;
        }

        label {
            font-size: 16px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="circular-bar">
            <div class="degree"><?php echo isset($temperature) ? $temperature : '0'; ?>°C</div>
        </div>
        <label>Temperature</label>
    </div>

    <script>
        // Pass the temperature value directly from PHP to JavaScript
        let currentTemperature = <?php echo $temperature; ?>;

        // Set up initial values for the progress bar
        let CircularBar = document.querySelector(".circular-bar");
        let DegreeValue = document.querySelector(".degree");

        // Function to smoothly update the circular progress bar
        function updateBar() {
            // Map the temperature directly to a portion of the circular progress bar
            // Adjust the angle (e.g., 100°C = 360 degrees)
            let angle = currentTemperature * 3.6; // 1°C = 3.6 degrees
            CircularBar.style.background = `conic-gradient(#4285f4 ${angle}deg, #e8f0f7 0deg)`;

            // Update the temperature displayed in the center
            DegreeValue.innerHTML = Math.round(currentTemperature) + "°C";
        }

        // Call updateBar once to display the temperature initially
        updateBar();

        // Set an interval to fetch the latest temperature from the server and update the progress bar
        setInterval(() => {
            // Fetch the latest temperature using AJAX from the server-side PHP script
            fetch('../storage/data/roomTempBackend.php') // Ensure this path is correct
                .then(response => response.json())
                .then(data => {
                    currentTemperature = data.temperature; // Update current temperature
                    updateBar(); // Update the bar only if the temperature has changed
                })
                .catch(error => console.error('Error fetching temperature:', error));
        }, 5000); // Update every 5 seconds
    </script>

</body>

</html>
