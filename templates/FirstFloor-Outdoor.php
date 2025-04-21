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
    <link rel="stylesheet" href="../assets/css/FirstFloor-Outdoor.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script type="module" src="https://unpkg.com/@google/model-viewer"></script>

</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>

        <div class="dashboardDevider">
            <div class="dashboardDeviderLeft">
                <div class="dropdownCont">


                    <div class="custom-dropdown">
                        <div class="dropdown-btn">
                            <span id="dropdownText">First Floor</span>
                            <div class="iconddcont">
                                <img src="../assets/images/icon-dropdown.png" alt="IconDropDown" class="IconDropDown">
                            </div>
                        </div>
                        <div class="dropdown-list">
                            <div class="dropdown-item" data-value="firstFloor">
                                First Floor
                            </div>
                            <div class="dropdown-item" data-value="secondFloor">
                                Second Floor
                            </div>
                            <div class="dropdown-item" data-value="thirdFloor">
                                Third Floor
                            </div>
                            <div class="dropdown-item" data-value="fourthFloor">
                                Fourth Floor
                            </div>
                            <div class="dropdown-item" data-value="fifthFloor">
                                Roof Top
                            </div>
                        </div>
                    </div>

                    <script>
                        // Toggle dropdown visibility
                        document.querySelector('.dropdown-btn').addEventListener('click', function () {
                            this.parentElement.classList.toggle('open');
                        });

                        // Handle item selection and update text dynamically
                        document.querySelectorAll('.dropdown-item').forEach(function (item) {
                            item.addEventListener('click', function () {
                                var selectedValue = this.getAttribute('data-value');
                                var floorLinks = {
                                    "firstFloor": "../templates/FirstFloor-Outdoor.php",
                                    "secondFloor": "../templates/secondFloor.php",
                                    "thirdFloor": "../templates/thirdFloor.php",
                                    "fourthFloor": "../templates/fourthFloor.php",
                                    "fifthFloor": "../templates/fifthFloor.php"
                                };

                                // Update dropdown text with the selected floor name
                                var dropdownText = {
                                    "firstFloor": "First Floor",
                                    "secondFloor": "Second Floor",
                                    "thirdFloor": "Third Floor",
                                    "fourthFloor": "Fourth Floor",
                                    "fifthFloor": "Fifth Floor"
                                };

                                // Set the selected floor text in the dropdown
                                document.getElementById("dropdownText").innerText = dropdownText[selectedValue] || "Select Floor";

                                // Redirect to the corresponding URL
                                if (floorLinks[selectedValue]) {
                                    window.location.href = floorLinks[selectedValue];
                                }
                            });
                        });
                    </script>

                </div>
                <div class="firstFloor" id="firstfloor3d">
                    <!-- The model-viewer will be added here via JavaScript -->
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        const container = document.getElementById("firstfloor3d");
                        const modelPath = "<?php echo '../assets/models/firstFloor.glb'; ?>"; // Ensure this path is correct

                        fetch(modelPath)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! Status: ${response.status}`);
                                }
                                return response.blob();
                            })
                            .then(blob => {
                                const url = URL.createObjectURL(blob);

                                // Create the <model-viewer> element dynamically
                                const modelViewer = document.createElement("model-viewer");
                                modelViewer.setAttribute("src", url);
                                modelViewer.setAttribute("auto-rotate", "");
                                modelViewer.setAttribute("camera-controls", "");
                                modelViewer.setAttribute("shadow-intensity", "1");
                                modelViewer.setAttribute("exposure", ".45");
                                modelViewer.setAttribute("environment-image", "neutral");
                                modelViewer.setAttribute("ar", "");
                                modelViewer.setAttribute("disable-tap", "");
                                modelViewer.style.width = "100%";
                                modelViewer.style.height = "600px";
                                modelViewer.style.position = 'relative'; // Ensure it's positioned relative for glitter effect positioning

                                // Append it to the div
                                container.appendChild(modelViewer);

                                // Function to create glowing light trails
                                const createGlowEffect = (x, y) => {
                                    const glow = document.createElement('div');
                                    const size = Math.random() * 6 + 4; // Random size for each glow particle (between 4 and 10px)
                                    const color = `rgba(255, 255, 255, 0.8)`; // White glowing color
                                    const animationDuration = Math.random() * 0.4 + 0.5; // Random animation duration for each glow

                                    glow.style.position = 'absolute';
                                    glow.style.left = `${x - size / 2}px`;  // Adjust for center position
                                    glow.style.top = `${y - size / 2}px`;   // Adjust for center position
                                    glow.style.width = `${size}px`;
                                    glow.style.height = `${size}px`;
                                    glow.style.backgroundColor = color;
                                    glow.style.borderRadius = '50%';
                                    glow.style.pointerEvents = 'none';
                                    glow.style.animation = `glowAnimation ${animationDuration}s ease-out forwards`; // Glowing effect animation
                                    modelViewer.appendChild(glow);

                                    // Remove glow after animation
                                    setTimeout(() => {
                                        glow.remove();
                                    }, animationDuration * 1000);
                                };

                                // Add mousemove event to create glowing light trail
                                modelViewer.addEventListener('mousemove', (event) => {
                                    const modelViewerRect = modelViewer.getBoundingClientRect();
                                    const mouseX = event.clientX - modelViewerRect.left; // Get mouse position within model viewer
                                    const mouseY = event.clientY - modelViewerRect.top;

                                    // Trigger glowing light trail effect
                                    createGlowEffect(mouseX, mouseY);
                                });
                            })
                            .catch(error => {
                                console.error("Error loading model:", error);
                            });
                    });
                </script>

                <style>
                    /* Keyframe animation for glowing effect */
                    @keyframes glowAnimation {
                        0% {
                            transform: scale(0);
                            opacity: 1;
                        }

                        50% {
                            transform: scale(1.5);
                            opacity: 0.7;
                        }

                        100% {
                            transform: scale(0);
                            opacity: 0;
                        }
                    }
                </style>
                <div class="room">
                    <!-- "Garage" button starts with the activeButton class to indicate it's the default -->
                    <button onclick="navigateToGarage('../templates/FirstFloor-Garage.php')" class="roomButton"
                        id="garageButton">Garage</button>
                    <button onclick="navigateToOutdoor()" class="roomButton activeButton"
                        id="outdoorButton">Outdoor</button>
                </div>

            </div>
            <div class="dashboardDeviderRight">
                <!-- Search Bar with Icon Inside -->
                <!-- <div class="searchContainer">
                    <input type="text" id="searchInput" placeholder=" " class="searchInput">
                    <button onclick="performSearch()" class="searchButton">
                        <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div> -->

                <div class="firstFloorLog">
                    <button id="downloadExcel" class="downloadBtn">
                        <span class="material-icons">download</span>
                    </button>

                    <!-- Notifications will be inserted here dynamically -->
                </div>

                <div class="deviceControl">
                    <p class="devTitle">Devices</p>
                    <div class="devices">
                        <div class="accessGate">

                            <img src="../assets/images/accessGate.png" alt="Access Gate" class="accessGateImage">

                            <p>Access Gate</p>
                            <span>Outdoor</span>

                            <div class="switch-container">
  <label class="switch">
    <input type="checkbox" id="accessGateSwitch">
    <span class="slider"></span>
  </label>
</div>

<script>
  const gateSwitch = document.getElementById('accessGateSwitch');

  gateSwitch.addEventListener('change', function() {
    // only when user turns it ON:
    if (!this.checked) return;

    // disable so you can't spam‑click
    this.disabled = true;

    // 1) Notify AWS
    fetch('https://vw2oxci132.execute-api.ap-southeast-1.amazonaws.com/dev-accessgate/accessgate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'open',
        user_id: sessionStorage.getItem('user_id') || 'unknown_user'
      })
    })
    .then(r => r.json())
    .then(d => console.log('Gate API response:', d))
    .catch(e => console.error('Gate API error:', e));

    // 2) Log locally
    fetch('../scripts/log_access_gate.php', {
      method: 'POST',
      credentials: 'include'
    })
    .then(r => r.json())
    .then(j => {
      if (j.success) console.log('Logged locally, id=', j.insertedId);
      else           console.error('Local log failed:', j.error);
    })
    .catch(e => console.error('Logging error:', e));

    // 3) After 2 seconds, reset visually & re-enable
    setTimeout(() => {
      gateSwitch.checked = false;  // this flips the UI slider back
      gateSwitch.disabled = false; // allow new clicks
      console.log('Switch reset to OFF automatically');
    }, 2000);
  });
</script>




                        </div>


                        <div class="cameraFF">
                            <a href="http://172.20.10.2">
                                <img src="../assets/images/camera.png" alt="Camera" class="cameraImage">
                            </a>
                            <p>Camera</p>
                            <span>Outdoor</span>

                            <!-- <div class="switch-container">
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script>


        <script>
            // Navigation functions
            function navigateToOutdoor() {
                document.getElementById("outdoorButton").classList.add("activeButton");
                document.getElementById("garageButton").classList.remove("activeButton");
            }

            // Consider renaming one of these if you need both behaviors
            function navigateToGarage() {
                // This function will redirect to the garage page.
                window.location.href = "../templates/FirstFloor-Garage.php";
            }

            // Fetch user ID from PHP and store it in sessionStorage
            fetch('../scripts/getUserId.php')
                .then(response => response.json())
                .then(data => {
                    sessionStorage.setItem('user_id', data.user_id);
                    console.log("Fetched user ID:", data.user_id);
                })
                .catch(error => console.error('Error fetching user ID:', error));

            function toggleAccessGate() {
                const sw = document.getElementById('accessGateSwitch');
                const action = sw.checked ? 'open' : 'close';
                console.log("Access Gate toggled:", action);

                // 1) Notify AWS (unchanged)
                fetch('https://vw2oxci132.execute-api.ap-southeast-1.amazonaws.com/dev-accessgate/accessgate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: action,
                        user_id: sessionStorage.getItem('user_id') || 'unknown_user'
                    })
                })
                    .then(res => res.json())
                    .then(data => console.log('Gate API response:', data))
                    .catch(err => console.error('Gate API error:', err));

                // 2) Log locally in your MySQL table
                fetch('../scripts/log_access_gate.php', {
                    method: 'POST',
                    credentials: 'include'  // so PHP can read the session
                })
                    .then(res => res.json())
                    .then(json => {
                        if (json.success) {
                            console.log('Logged to gateAccess_logs, new id:', json.insertedId);
                        } else {
                            console.error('Logging failed:', json.error);
                        }
                    })
                    .catch(err => console.error('Logging fetch error:', err));
            }
        </script>


        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


        <script>
            function loadNotifications() {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", "../scripts/logs_firstFloor_Garage.php", true);

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const data = JSON.parse(xhr.responseText);
                        const logContainer = document.querySelector(".firstFloorLog");

                        // Ensure the button exists and is properly placed
                        let downloadButton = document.getElementById("downloadExcel");
                        if (!downloadButton) {
                            downloadButton = document.createElement("button");
                            downloadButton.id = "downloadExcel";
                            downloadButton.className = "downloadBtn";
                            downloadButton.innerHTML = '<span class="material-icons">download</span>';
                            logContainer.appendChild(downloadButton);
                        }
                        downloadButton.onclick = downloadExcel;

                        // Build the log table
                        let logHTML = '<table class="ffLogTable">';
                        data.reverse().forEach((notif, index) => {
                            logHTML += `
                        <tr>
                        <td class="ffuserTime">
    <span class="fflogTime">${formatTime(notif.time)}</span>
</td>

<script>
    function formatTime(timeStr) {
        const date = new Date(timeStr);
        const hours = date.getHours();
        const minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        // Convert 24-hour to 12-hour format
        const formattedHours = hours % 12 || 12;  // Convert 0 to 12 (midnight)
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;

        return `${formattedHours}:${formattedMinutes} ${ampm}`;
    }
</script>

                            <td class="ffuserLog"><span class="ffuserDid">${notif.message}</span></td>
                        </tr>`;
                            if (index !== data.length - 1) {
                                logHTML += `
                        <tr>
                            <td colspan="2">
                                <div class="line-with-circle"></div>
                            </td>
                        </tr>`;
                            }
                        });
                        logHTML += "</table>";

                        // Swap old table for new
                        const existingTable = logContainer.querySelector("table");
                        if (existingTable) existingTable.remove();
                        logContainer.insertAdjacentHTML("beforeend", logHTML);
                    }
                };

                xhr.onerror = function () {
                    console.error("Error fetching notifications.");
                };

                xhr.send();
            }

            function downloadExcel() {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", "../scripts/logs_firstFloor_Garage.php", true);

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const data = JSON.parse(xhr.responseText);
                        const worksheetData = [["Time", "Message"]];
                        data.forEach(log => worksheetData.push([log.time, log.message]));

                        const ws = XLSX.utils.aoa_to_sheet(worksheetData);
                        const wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, "Logs_FirstFloor_Garage");

                        XLSX.writeFile(wb, "Logs_FirstFloor_Garage.xlsx");
                    }
                };

                xhr.onerror = function () {
                    console.error("Error fetching data for Excel.");
                };

                xhr.send();
            }

            // Refresh logs immediately and then every 3 seconds
            document.addEventListener('DOMContentLoaded', () => {
                loadNotifications();
                setInterval(loadNotifications, 3000);
            });
        </script>


    </div>
</body>

</html>