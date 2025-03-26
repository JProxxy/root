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
  <!-- Load jQuery from CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="wrapper">
  <div class="circular-bar" id="circular-bar">
    <div class="percent" id="percent">0%</div>
  </div>
  <label>Humidity</label>
</div>

<script>
  // Function to fetch humidity data from the PHP file using jQuery AJAX
  function fetchHumidity() {
    $.ajax({
      url: '../storage/data/roomHumidityBackend.php', // Ensure this path is correct
      method: 'GET',
      dataType: 'json',
      success: function(data) {
        let humidity = data.humidity;
        console.log("Humidity from PHP:", humidity);  // Debugging output

        let $circularBar = $('#circular-bar');
        let $percentDisplay = $('#percent');

        let initialValue = 0;
        let finalValue = humidity;  // Target humidity value
        let speed = 10;  // Speed of the progress animation in milliseconds

        // Update the circular bar and percentage gradually
        let interval = setInterval(() => {
          if (initialValue < finalValue) {
            initialValue += 1; // Increment by 1% each interval
          }

          // Calculate the angle for the conic gradient (100% = 360 degrees)
          let angle = (initialValue / 100) * 360;
          $circularBar.css('background', `conic-gradient(#34a853 ${angle}deg, #e8f0f7 0deg)`);

          // Update the percentage display
          $percentDisplay.text(initialValue + '%');

          // Stop the interval when the target value is reached
          if (initialValue >= finalValue) {
            clearInterval(interval);
          }
        }, speed);
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("Error fetching humidity data:", textStatus, errorThrown);
      }
    });
  }

  // Fetch humidity on page load using jQuery's document ready
  $(document).ready(function() {
    fetchHumidity();
  });
</script>

</body>
</html>
