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
            <div class="degree">0°C</div>
        </div>
        <label>Temperature</label>
    </div>

    <?php
    $host = '18.139.255.32';
    $dbname = 'rivan_iot';
    $username = 'root';
    $password = 'Pa$$word1';

    try {
        // Create a PDO instance
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        // Set PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch the latest temperature for the device 'ffRoom-temp'
        $query = "SELECT temperature FROM room_data WHERE deviceName = 'ffRoom-temp' ORDER BY timestamp DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        // Get the result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $temperature = $row['temperature'];
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
    ?>

    <script>
        // Pass the temperature value fetched from PHP to JavaScript
        let currentTemperature = <?php echo $temperature; ?>;
        let previousTemperature = currentTemperature; // Store initial temperature

        console.log("Latest Temperature: " + currentTemperature);

        // Set up initial values for the progress bar
        let CircularBar = document.querySelector(".circular-bar");
        let DegreeValue = document.querySelector(".degree");

        let InitialValue = 0;  // Starting value
        let targetValue = currentTemperature; // Set the initial target value to the fetched temperature
        let speed = 30;  // Speed of value change (milliseconds)
        let transitionSpeed = 0.5; // Speed of transition (higher is slower)

        // Function to smoothly update the circular progress bar
        function updateBar() {
            // Check if the temperature has changed before updating
            if (currentTemperature !== previousTemperature) {
                targetValue = currentTemperature;
                previousTemperature = currentTemperature; // Update the previous temperature value
            }

            // Gradually update InitialValue towards the target value
            if (Math.abs(InitialValue - targetValue) > transitionSpeed) {
                if (InitialValue < targetValue) {
                    InitialValue += transitionSpeed; // Increase gradually
                } else if (InitialValue > targetValue) {
                    InitialValue -= transitionSpeed; // Decrease gradually
                }
            } else {
                // Once close enough to the target, set it exactly to avoid overshooting
                InitialValue = targetValue;
            }

            // Map the temperature directly to a portion of the circular progress bar
            // Adjust the angle (for example, 100°C corresponds to a full circle)
            let angle = (InitialValue / 100) * 360;
            CircularBar.style.background = `conic-gradient(#4285f4 ${angle}deg, #e8f0f7 0deg)`;

            // Update the temperature displayed in the center
            DegreeValue.innerHTML = Math.round(InitialValue) + "°C";
        }

        // Set an interval to fetch the latest temperature from the server and update the progress bar
        setInterval(() => {
            // Simulating fetching the latest temperature (in a real scenario, you'd fetch new data via AJAX or similar)
            // Update `currentTemperature` dynamically based on your backend API or data source
            fetch('../storage/data/roomTempBackend.php')  // Update with your actual API path
                .then(response => response.json())
                .then(data => {
                    currentTemperature = data.temperature; // Update current temperature
                    updateBar(); // Only update the bar if the temperature has changed
                })
                .catch(error => console.error('Error fetching temperature:', error));
        }, 5000); // Update every 5 seconds or based on your preferred interval
    </script>

</body>

</html>
