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
    <link rel="stylesheet" href="../assets/css/SA-manageUsers.css" />
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
                                <a href="../templates/SA.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-roles.png" alt="Manage Roles" width="16" height="16"
                                        class="me-2" />
                                    Manage Roles
                                </a>
                            </li>
                            <li>
                                <a href="../templates/SA-manageUsers.php" class="nav-link active">
                                    <img src="../assets/images/icon-users.png" alt="Manage Users" width="16" height="16"
                                        class="me-2" />
                                    Manage Users
                                </a>
                            </li>
                            <li>
                                <a href="../storage/logs/globalLogs.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-logs.png" alt="Logs" width="16" height="16"
                                        class="me-2" />
                                    Logs
                                </a>
                            </li>
                            <li>
                                <a href="../classes/getDeletedAccountData.php" class="nav-link link-body-emphasis">
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
                            <div id="table-container" class="table-container">
                                <!-- The dynamic table will be inserted here -->
                            </div>
                        </div>
                    </div>
                    <!-- Dynamic Table Script with Debug Logging -->
                    <script>
                        document.addEventListener("DOMContentLoaded", async function () {
                            try {
                                // Fetch the JSON data from SA-globalUserAll.php (which includes the role_id for each user)
                                const response = await fetch('../scripts/SA-globalUserAll.php');
                                console.log("HTTP Status:", response.status);

                                // Read and log the raw response text
                                const text = await response.text();
                                console.log("Raw Response Text:", text);

                                // Parse the response JSON
                                const result = JSON.parse(text);
                                console.log("Parsed Response:", result);

                                // Log backend debug info to the console if available
                                if (result.debug) {
                                    console.log("Backend Debug Info:", result.debug);
                                }

                                // Use the "data" field for table construction
                                const data = result.data;

                                // Create table element and assign the CSS class
                                const table = document.createElement('table');
                                table.classList.add("custom-table");

                                // Define headers based on the provided columns plus a dropdown column at the end.
                                const headers = [
                                    { title: "No.", class: "col-no" },
                                    { title: "User ID", class: "col-user_id" },
                                    { title: "First Name", class: "col-first_name" },
                                    { title: "Middle Name", class: "col-middle_name" },
                                    { title: "Last Name", class: "col-last_name" },
                                    { title: "Username", class: "col-username" },
                                    { title: "Phone Number", class: "col-phoneNumber" },
                                    { title: "Email", class: "col-email" },
                                    { title: "Role ID", class: "col-role_id" },
                                    { title: "Created At", class: "col-created_at" },
                                    { title: "Status", class: "col-status" },
                                    { title: "Gender", class: "col-gender" },
                                    { title: "City", class: "col-city" },
                                    { title: "Street Address", class: "col-street_address" },
                                    { title: "Postal Code", class: "col-postal_code" },
                                    { title: "Country", class: "col-country" },
                                    { title: "Barangay", class: "col-barangay" },
                                    { title: "Bio", class: "col-bio" },
                                    { title: "Social Media", class: "col-soc_med" },
                                    { title: "Google ID", class: "col-google_id" },
                                    { title: "Email Verified", class: "col-isEmailVerified" },
                                    { title: "Role", class: "col-role" } // This column contains the dropdown
                                ];

                                // Build table header
                                const thead = document.createElement('thead');
                                const headerRow = document.createElement('tr');
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
                                    // Store the username in a data attribute for use in updating roles
                                    tr.dataset.username = row.username;

                                    // Build each cell based on the headers
                                    headers.forEach(headerInfo => {
                                        const td = document.createElement('td');
                                        td.className = headerInfo.class;

                                        // For "No." column
                                        if (headerInfo.title === "No.") {
                                            td.textContent = index + 1;
                                        }
                                        // For "Role" column, create a dropdown
                                        else if (headerInfo.title === "Role") {
                                            const select = document.createElement('select');

                                            // Define available roles with their numeric role_id values
                                            const roles = [
                                                { value: 1, text: "Super Admin", disabled: true },
                                                { value: 2, text: "First Floor Admin" },
                                                { value: 3, text: "Second Floor Admin" },
                                                { value: 4, text: "Third Floor Admin" },
                                                { value: 5, text: "Fourth Floor Admin" },
                                                { value: 6, text: "Fifth Floor Admin" },
                                                { value: 7, text: "General User" },
                                                { value: 8, text: "Guest User" },
                                                { value: 9, text: "Maintenance Staff" },
                                                { value: 10, text: "Security Admin" },
                                                { value: 11, text: "IoT Technician" },
                                                { value: 12, text: "Pending User" },
                                                { value: 13, text: "Blocked User" }
                                            ];

                                            roles.forEach(role => {
                                                const option = document.createElement('option');
                                                option.value = role.value; // Numeric role_id
                                                option.textContent = role.text;
                                                if (role.disabled) {
                                                    option.disabled = true;
                                                }
                                                // If the row's role_id (from the database) matches this role's numeric value, select it.
                                                if (row.role_id == role.value) {
                                                    option.selected = true;
                                                }
                                                select.appendChild(option);
                                            });
                                            td.appendChild(select);
                                        }
                                        // For all other columns, use the key derived from the header class (removing "col-")
                                        else {
                                            const key = headerInfo.class.replace("col-", "");
                                            td.textContent = row[key] || "";
                                        }
                                        tr.appendChild(td);
                                    });

                                    tbody.appendChild(tr);
                                });
                                table.appendChild(tbody);

                                // Insert the table into the container (clearing any existing content)
                                const container = document.getElementById('table-container');
                                container.innerHTML = '';
                                container.appendChild(table);

                                // Add change event listeners to all dropdowns in the table
                                table.querySelectorAll('select').forEach(select => {
                                    select.addEventListener('change', async function () {
                                        const newRole = this.value;
                                        // Retrieve the username from the row's data attribute
                                        const tr = this.closest('tr');
                                        const username = tr.dataset.username;

                                        // Prepare the payload with the username and the new numeric role_id
                                        const payload = {
                                            username: username,
                                            role: newRole
                                        };

                                        try {
                                            // Send a POST request to update the role using SA-manageUsersRole.php
                                            const updateResponse = await fetch('../scripts/SA-manageUsersRole.php', {
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