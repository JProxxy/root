<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Logs</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/userActLogPage.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <?php include '../partials/bgMain.php'; ?>
    <div class="dashboardDevider">
        <div class="userActLogCont">
            <nav>
                <ul>
                    <li> <img src="../assets/images/back.png" alt="Logo" width="50" height="50"> </li>
                    <li>User Activity Log</li>
                    <li>
                        <div class="searchContainer">
                            <input type="text" id="searchInputX" placeholder=" " class="searchInput">
                            <button onclick="performSearch()" class="searchButton">
                                <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </button>
                        </div>
                    </li>
                    <li>
                        <div class="filterCont">
                            <img src="../assets/images/icon-filter.png" alt="Logo" width="20" height="25"
                                style="margin-right: 10px;">
                            <span> Filter </span>
                        </div>
                        <div class="dateCont">
                            <img src="../assets/images/icon-date.png" alt="Logo" width="25" height="25"
                                style="margin-right: 10px;">
                            <span> Date </span>
                        </div>
                        <div class="sortCont">
                            <img src="../assets/images/icon-sort.png" alt="Logo" width="25" height="25"
                                style="margin-right: 10px;">
                            <span> Sort </span>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="actLogTable">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Name</th>
                            <th>Action Taken</th>
                            <th>Timestamp</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Data will be inserted here -->
                    </tbody>
                </table>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    function fetchUserActivityLogs() {
                        fetch("../scripts/getUserActLogPage.php")
                            .then(response => response.json()) // Convert response to JSON
                            .then(data => populateTable(data)) // Call function to populate table
                            .catch(error => console.error("Error fetching user activity logs:", error));
                    }

                    function populateTable(data) {
                        const tableBody = document.getElementById("tableBody");
                        tableBody.innerHTML = ""; // Clear previous data

                        data.forEach(user => {
                            const row = document.createElement("tr");
                            row.innerHTML = `
                <td>${user.id}</td>
                <td>
                    <img src="${user.profile_picture}" alt="Profile Picture" class="profile-pic">
                    ${user.name}
                </td>
                <td>${user.action}</td>
                <td>${user.timestamp}</td>
                <td>
                    <div class="${user.status === 'authorized' ? 'status-authorized' : 'status-unauthorized'}">
                        ${user.status}
                    </div>
                </td>
            `;
                            tableBody.appendChild(row);
                        });
                    }

                    // Fetch data when page loads
                    fetchUserActivityLogs();
                });

            </script>
        </div>
    </div>
</body>

</html>