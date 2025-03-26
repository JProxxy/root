<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css" />
    <link rel="stylesheet" href="../assets/css/settings.css" />
    <link rel="stylesheet" href="../assets/css/settings-profile.css" />
    <link rel="stylesheet" href="../assets/css/settings-password.css" />
    <link rel="stylesheet" href="../assets/css/sa.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>

<body>
    <div class="bgMain">
        <?php include '../partials/bgMain.php'; ?>
        <div class="containerPart">
            <!-- Header -->
            <div class="headbackCont">
                <div class="imgBack">
                    <a href="../templates/dashboard.php">
                        <img src="../assets/images/back.png" alt="back" class="backIcon" />
                    </a>
                </div>
                <div class="headerGroup">
                    <div class="headerText">Admin</div>
                   
                </div>
            </div>
            <!-- Content Wrapper (Side Panel + Profile Main) -->
            <div class="contentWrapper">
                <!-- Sidebar -->
                <div class="sidepanel">
                    <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 100%;">
                        <ul class="nav nav-pills flex-column mb-auto">
                            <br>
                            <li class="nav-item">
                                <a href="../templates/SA.php" class="nav-link active">
                                    <img src="../assets/images/icon-roles.png" alt="Manage Roles" width="16" height="16"
                                        class="me-2" />
                                    Manage Roles
                                </a>
                            </li>
                            <li>
                                <a href="../templates/SA-manageUsers.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-users.png" alt="Manage Users" width="16" height="16"
                                        class="me-2" />
                                    Manage Users
                                </a>
                            </li>
                            <!-- <li>
                                <a href="../templates/globalLogs.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-logs.png" alt="Logs" width="16" height="16"
                                        class="me-2" />
                                    Logs
                                </a>
                            </li> -->
                            <li>
                                <a href="../templates/getDeletedAccountData.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-racoontrash.png" alt="Deleted Logs" width="16"
                                        height="16" class="me-2" />
                                    Deleted User Logs
                                </a>
                            </li>
                            <br><br><br><br><br><br><br><br><br><br>
                        </ul>
                    </div>
                </div>
                <!-- Main Profile Section -->
                <div class="profile-main">
                    <div class="flex-containerOneSA">
                        <div class="headerSACont">
                            <div class="searchContainer">
                                <input type="text" id="searchInputX" placeholder=" " class="searchInput" />
                                <button onclick="performSearch()" class="searchButton">
                                    <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="whiteLine"></div>
                        <!-- Roles Container with Dynamic Table -->
                        <div class="rolesCont">
                            <div id="table-container">
                                <!-- The dynamic table will be inserted here -->
                            </div>
                        </div>
                    </div>
                    <!-- Dynamic Table Script with Debug Logging -->
                    <script>
                        document.addEventListener("DOMContentLoaded", async function () {
                            try {
                                // Fetch the JSON data from SA-rolePicker.php
                                const response = await fetch('../scripts/SA-rolePicker.php');
                                console.log("HTTP Status:", response.status);

                                // Read and log the raw response text
                                const text = await response.text();
                                console.log("Raw Response Text:", text);

                                // Parse the response JSON
                                const result = JSON.parse(text);
                                console.log("Parsed Response:", result);

                                // Log backend debug info to the console
                                if (result.debug) {
                                    console.log("Backend Debug Info:", result.debug);
                                }

                                // Use the "data" field for table construction
                                const data = result.data;

                                // Create table element and assign the CSS class
                                const table = document.createElement('table');
                                table.classList.add("custom-table");

                                // Build table header
                                const thead = document.createElement('thead');
                                const headerRow = document.createElement('tr');
                                const headers = [
                                    { title: "No.", class: "col-no" },
                                    { title: "Username", class: "col-username" },
                                    { title: "Email", class: "col-email" },
                                    { title: "Phone Number", class: "col-phone" },
                                    { title: "Role", class: "col-role" }
                                ];
                                headers.forEach(headerInfo => {
                                    const th = document.createElement('th');
                                    th.className = headerInfo.class;
                                    th.textContent = headerInfo.title;
                                    headerRow.appendChild(th);
                                });
                                thead.appendChild(headerRow);
                                table.appendChild(thead);

                                // Build table body
                                const tbody = document.createElement('tbody');
                                data.forEach((row, index) => {
                                    const tr = document.createElement('tr');
                                    // Add a data attribute to store the username for later use
                                    tr.dataset.username = row.username;

                                    // No. Column
                                    const tdNo = document.createElement('td');
                                    tdNo.className = "col-no";
                                    tdNo.textContent = index + 1;
                                    tr.appendChild(tdNo);

                                    // Username Column
                                    const tdUsername = document.createElement('td');
                                    tdUsername.className = "col-username";
                                    tdUsername.textContent = row.username;
                                    tr.appendChild(tdUsername);

                                    // Email Column
                                    const tdEmail = document.createElement('td');
                                    tdEmail.className = "col-email";
                                    tdEmail.textContent = row.email;
                                    tr.appendChild(tdEmail);

                                    // Phone Number Column
                                    const tdPhone = document.createElement('td');
                                    tdPhone.className = "col-phone";
                                    tdPhone.textContent = row.phoneNumber;
                                    tr.appendChild(tdPhone);

                                    // Role Column with dropdown
                                    const tdRole = document.createElement('td');
                                    tdRole.className = "col-role";
                                    const select = document.createElement('select');

                                    // Define available roles
                                    const roles = [
                                        { value: "super_admin", text: "Super Admin", disabled: true },
                                        { value: "first_floor_admin", text: "First Floor Admin" },
                                        { value: "second_floor_admin", text: "Second Floor Admin" },
                                        { value: "third_floor_admin", text: "Third Floor Admin" },
                                        { value: "fourth_floor_admin", text: "Fourth Floor Admin" },
                                        { value: "fifth_floor_admin", text: "Fifth Floor Admin" },
                                        { value: "general_user", text: "General User" },
                                        { value: "guest_user", text: "Guest User" },
                                        { value: "maintenance_staff", text: "Maintenance Staff" },
                                        { value: "security_admin", text: "Security Admin" },
                                        { value: "iot_technician", text: "IoT Technician" },
                                        { value: "pending_user", text: "Pending User" },
                                        { value: "blocked_user", text: "Blocked User" }
                                    ];

                                    roles.forEach(role => {
                                        const option = document.createElement('option');
                                        option.value = role.value;
                                        option.textContent = role.text;
                                        if (role.disabled) {
                                            option.disabled = true;
                                        }
                                        // If the row already has a role, select that one.
                                        // Otherwise, if no role is provided, default to "pending_user".
                                        if ((row.role && row.role === role.value) || (!row.role && role.value === "pending_user")) {
                                            option.selected = true;
                                        }
                                        select.appendChild(option);
                                    });
                                    tdRole.appendChild(select);
                                    tr.appendChild(tdRole);

                                    tbody.appendChild(tr);
                                });
                                table.appendChild(tbody);

                                // Insert the table into the container (clearing any existing content)
                                const container = document.getElementById('table-container');
                                container.innerHTML = '';
                                container.appendChild(table);

                                // Add change event listeners to all select elements
                                table.querySelectorAll('select').forEach(select => {
                                    select.addEventListener('change', async function () {
                                        const newRole = this.value;
                                        // Retrieve the username from the parent row's data attribute
                                        const tr = this.closest('tr');
                                        const username = tr.dataset.username;

                                        // Prepare the payload with the username and new role
                                        const payload = {
                                            username: username,
                                            role: newRole
                                        };

                                        try {
                                            // Send a POST request to update the role (assuming update endpoint is SA-roleUpdate.php)
                                            const updateResponse = await fetch('../scripts/updateUserRole.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json'
                                                },
                                                body: JSON.stringify(payload)
                                            });

                                            const updateResult = await updateResponse.json();
                                            if (updateResult.success) {
                                                console.log("Role updated successfully for user:", username);
                                                alert("Role updated successfully!");
                                                window.location.reload();
                                            } else {
                                                console.error("Failed to update role:", updateResult.message);
                                                alert("Failed to update role: " + updateResult.message);
                                            }
                                        } catch (error) {
                                            console.error("Error updating role:", error);
                                            alert("An error occurred while updating the role.");
                                        }
                                    });
                                });
                            } catch (error) {
                                console.error("Error fetching dynamic table data:", error);
                            }
                        });
                    </script>

                </div>
            </div>
        </div>
    </div>
</body>

</html>

<script>
    // Attach an event listener to the search input field
    document.getElementById("searchInputX").addEventListener("input", performSearch);

    function performSearch() {
        // Get the search query in lowercase
        const filter = document.getElementById("searchInputX").value.toLowerCase();

        // Select the table; make sure it exists
        const table = document.querySelector("table.custom-table");
        if (!table) return;

        // Get all rows in the table body
        const rows = table.querySelectorAll("tbody tr");

        // Loop through all rows and hide those that don't match the query
        rows.forEach(row => {
            // Combine the text content of all cells in this row into one string
            const rowText = row.textContent.toLowerCase();

            // If the row text includes the filter, display the row; otherwise hide it.
            if (rowText.indexOf(filter) > -1) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

</script>