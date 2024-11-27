<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Gauge | CSS Animation</title>
    <style>
        :root {
            --gray: rgb(34, 48, 64);
            --blue: rgb(0, 122, 253);
            --skyblue: rgb(92, 225, 230);
            --lightblue: rgb(0, 151, 178);
            --white: rgb(253, 251, 252);
            --transparent: rgba(255, 255, 255, 0);
            --border-color: rgb(200, 200, 200);
            /* Border color for the rectangle */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .rectangle-container {
            position: relative;
            width: 470px;
            /* Width of the rectangle */
            height: 200px;
            /* Height of the rectangle */
            padding: 10px;
            background-color: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .gauge {
            position: relative;
            width: 200px;
            height: 200px;
        }

        .progress {
            position: absolute;
            width: 100%;
            height: 100%;
            border-bottom: .0rem solid var(--white);
            border-radius: 50%;
            outline-offset: .4rem;
            overflow: hidden;
        }

        .progress::before {
            position: absolute;
            content: '';
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            background-color: var(--white);
            border-radius: 50%;
            z-index: 200;
        }

        .progress::after {
            position: absolute;
            content: '';
            top: 50%;
            width: 100%;
            height: 50%;
            background-color: var(--white);
        }

        .bar {
            position: absolute;
            width: 50%;
            height: 100%;
            background-color: var(--lightblue);
            transform: rotate(90deg);
            transform-origin: center right;
            animation: rotate 2s ease-in-out;
        }

        .needle {
            position: absolute;
            width: 100%;
            height: 50%;
            background-color: var(--skyblue);
            clip-path: polygon(50% 0, 50% 0, 52% 100%, 48% 100%);
            transform: rotate(90deg);
            transform-origin: bottom center;
            animation: rotate 2s ease-in-out;
            z-index: 300;
        }

        @keyframes rotate {
            0% {
                background-color: var(--lightblue);
                transform: rotate(-90deg);
            }

            80% {
                background-color: var(--lightblue);
            }
        }
    </style>
</head>

<body>
    <div class="rectangle-container">
        <div class="gauge">
            <div class="progress">
                <div class="bar"></div>
                <div class="needle"></div>
            </div>
        </div>
    </div>

    <script>
        // Function to fetch the latest WaterPercentage
        async function getWaterPercentage() {
            try {
                const response = await fetch('../storage/data/waterPercentage.php'); // Fetch data from PHP script
                const data = await response.json();
                const waterPercentage = data.WaterPercentage;

                // Log the waterPercentage to the console
                console.log('Water Percentage:', waterPercentage);

                // Update the gauge based on the fetched WaterPercentage
                updateGauge(waterPercentage);
            } catch (error) {
                console.error('Error fetching water percentage:', error);
            }
        }

        // Update the gauge chart based on the WaterPercentage
        function updateGauge(percentage) {
            const bar = document.querySelector('.bar');
            const needle = document.querySelector('.needle');

            // Rotate the progress bar according to the percentage
            bar.style.transform = `rotate(${percentage * 1.8 - 90}deg)`; // Rotate bar from 0 to 180 degrees

            // Rotate the needle based on the percentage
            needle.style.transform = `rotate(${percentage * 1.8 - 90}deg)`; // Adjust needle position
        }

        // Fetch and update the gauge on page load
        getWaterPercentage();
    </script>

</body>

</html>