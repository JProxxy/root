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
                                    <input type="checkbox" id="accessGateSwitch" onchange="toggleAccessGate()">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>


                        <div class="cameraFF">
                            <a href="../templates/FirstFloorOD-Camera.php">
                                <img src="../assets/images/camera.png" alt="Camera" class="cameraImage">
                            </a>
                            <p>Camera</p>
                            <span>Outdoor</span>

                            <div class="switch-container">
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/mqtt/dist/mqtt.min.js"></script>


        <script>
            function navigateToOutdoor() {
                // Change background color of Outdoor button
                document.getElementById("outdoorButton").classList.add("activeButton");
                document.getElementById("garageButton").classList.remove("activeButton");
            }

            function navigateToGarage() {
                // Reset Outdoor button and change background for Garage button
                document.getElementById("garageButton").classList.add("activeButton");
                document.getElementById("outdoorButton").classList.remove("activeButton");
            }

            function navigateToGarage(url) {
                window.location.href = "../templates/FirstFloor-Garage.php";
            }



            function toggleAccessGate() {
                const accessGateSwitch = document.getElementById('accessGateSwitch');
                // Determine command: when checked, the gate is unlocked; when unchecked, locked.
                const status = accessGateSwitch.checked ? 'UNLOCK' : 'LOCK';
                console.log("Access Gate toggled: " + status);
            
                // Prepare the payload in the format expected by your Lambda function:
                // It will have deviceName "GateAccess" and the command ("UNLOCK" or "LOCK")
                const payload = {
                    data: {
                        deviceName: "GateAccess",  // Ensures Lambda processes this as gate access
                        command: status
                    }
                };
            
                // Send the payload to your API Gateway endpoint which triggers your Lambda
                fetch('arn:aws:execute-api:ap-southeast-1:207567780437:y9saie9s20/*/*/accessgate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(responseData => {
                    console.log('Gate access response:', responseData);
                })
                .catch(error => {
                    console.error("Error updating gate access status:", error);
                });
            }
            
            
            

        </script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


        <script>
            document.addEventListener("DOMContentLoaded", function () {
                loadNotifications();
            });

            function loadNotifications() {
                fetch("../scripts/logs_firstFloor_Garage.php")
                    .then(response => response.json())
                    .then(data => {
                        let logContainer = document.querySelector(".firstFloorLog");

                        // Ensure the button exists and is properly placed
                        let downloadButton = document.getElementById("downloadExcel");
                        if (!downloadButton) {
                            downloadButton = document.createElement("button");
                            downloadButton.id = "downloadExcel";
                            downloadButton.className = "downloadBtn";
                            downloadButton.innerHTML = '<span class="material-icons">download</span>';
                            logContainer.appendChild(downloadButton);
                        }

                        // Make sure the event listener is attached
                        downloadButton.addEventListener("click", downloadExcel);

                        let logHTML = '<table class="ffLogTable">';
                        data.reverse().forEach((notif, index) => {
                            logHTML += `
                <tr>
                    <td class="ffuserTime"><span class="fflogTime">${notif.time}</span></td>
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

                        // Remove old logs but keep the button
                        let existingLogs = logContainer.querySelector("table");
                        if (existingLogs) {
                            existingLogs.remove();
                        }
                        logContainer.insertAdjacentHTML("beforeend", logHTML);
                    })
                    .catch(error => console.error("Error fetching notifications:", error));
            }

            function downloadExcel() {
                fetch("../scripts/logs_firstFloor_Garage.php")
                    .then(response => response.json())
                    .then(data => {
                        let worksheetData = [["Time", "Message"]]; // Header row
                        data.forEach(log => worksheetData.push([log.time, log.message]));

                        let ws = XLSX.utils.aoa_to_sheet(worksheetData);
                        let wb = XLSX.utils.book_new();
                        XLSX.utils.book_append_sheet(wb, ws, "Logs_FirstFloor_Garage");

                        XLSX.writeFile(wb, "Logs_FirstFloor_Garage.xlsx");
                    })
                    .catch(error => console.error("Error fetching data:", error));
            }
        </script>

    </div>
</body>

</html>