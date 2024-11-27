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

        .percent {
            z-index: 10;
            font-size: 20px;
        }

        label {
            font-size: 16px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="circular-bar">
            <div class="percent">0%</div>
        </div>
        <label>Temperature</label>
    </div>

    <script>
        let CircularBar = document.querySelector(".circular-bar");
        let PercentValue = document.querySelector(".percent");

        let InitialValue = 0;  // Starting value
        let targetValue = getRandomValue(); // Initial target value
        let speed = 50;  // Speed of value change (milliseconds)
        let transitionSpeed = 1; // Speed of transition (higher is slower)

        // Function to generate a random value between 0 and 100
        function getRandomValue() {
            return Math.floor(Math.random() * 101); // Random number between 0 and 100
        }

        // Function to smoothly update the circular progress bar
        function updateBar() {
            // Gradually update InitialValue towards the target value
            if (InitialValue < targetValue) {
                InitialValue += transitionSpeed; // Increase gradually
            } else if (InitialValue > targetValue) {
                InitialValue -= transitionSpeed; // Decrease gradually
            }

            // Update the circular progress bar with a smooth transition
            CircularBar.style.background = `conic-gradient(#4285f4 ${InitialValue / 100 * 360}deg, #e8f0f7 0deg)`;

            // Update the percentage displayed in the center
            PercentValue.innerHTML = Math.round(InitialValue) + "%";

            // If the value has reached the target value, generate a new random target value
            if (Math.abs(InitialValue - targetValue) < transitionSpeed) {
                targetValue = getRandomValue();
            }
        }

        // Update the progress bar every 'speed' milliseconds
        setInterval(updateBar, speed);
    </script>

</body>

</html>
