<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circular Progress Bar for Humidity</title>
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
            /* Reduced width */
            padding: 30px 0;
            /* Adjusted padding */
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 7px;
            background-color: #e8f0f7;
        }

        .circular-bar-temp {
            width: 160px;
            /* Reduced size */
            height: 160px;
            /* Reduced size */
            background: conic-gradient(#4285f4 1.5deg, #e8f0f7 0deg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 6px 6px 10px -1px rgba(0, 0, 0, 0.15),
                -6px -6px 10px -1px rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
            /* Adjusted margin */
            position: relative;
        }

        .circular-bar-temp::before {
            content: "";
            position: absolute;
            width: 140px;
            /* Reduced inner circle size */
            height: 140px;
            /* Reduced inner circle size */
            background: #e8f0f7;
            border-radius: 50%;
            box-shadow: inset 6px 6px 10px -1px rgba(0, 0, 0, 0.15),
                inset -6px -6px 10px -1px rgba(255, 255, 255, 0.7);
        }

        .percent-temp {
            z-index: 10;
            font-size: 20px;
            /* Reduced font size */
        }

        label {
            font-size: 16px;
            /* Reduced font size */
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="circular-bar-temp">
            <div class="percent-temp">0%</div>
        </div>
        <label>Humidity</label>
    </div>

    <script>
        let CircularBarTemp = document.querySelector(".circular-bar-temp");
        let PercentValueTemp = document.querySelector(".percent-temp");

        let InitialValueTemp = 0;
        let finaleValueTemp = 65;  // Target value for humidity
        let speedTemp = 10;  // Speed of progress

        let timerTemp = setInterval(() => {
            InitialValueTemp += 1;

            // Update circular bar with conic gradient
            CircularBarTemp.style.background = `conic-gradient(#34a853 ${InitialValueTemp / 100 * 360}deg, #e8f0f7 0deg)`;

            // Update percentage displayed in the center
            PercentValueTemp.innerHTML = InitialValueTemp + "%";

            // Stop when target value is reached
            if (InitialValueTemp >= finaleValueTemp) {
                clearInterval(timerTemp);
            }
        }, speedTemp);
    </script>

</body>

</html>
