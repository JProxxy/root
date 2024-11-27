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

       


</body>

</html>
