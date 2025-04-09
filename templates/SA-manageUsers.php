<?php
session_start();
// Retrieve the current user id from session
$currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$currentUserRoleId = 0;

if ($currentUserId) {
    // Include your database connection file (using PDO)
    include '../app/config/connection.php';

    // Prepare the PDO statement to get the current user's role
    $stmt = $conn->prepare("SELECT role_id FROM users WHERE user_id = ?");
    $stmt->execute([$currentUserId]);
    $currentUserRoleId = $stmt->fetchColumn();
}

// Check if the "download_csv" flag is set in the URL and user has role_id = 1
if (isset($_GET['download_csv']) && $_GET['download_csv'] == 'true') {
    // Check if the current user has role_id 1 (only allow them to download)
    if ($currentUserRoleId != 1) {
        die("You do not have permission to download the CSV.");
    }

    // Fetch specific user data from the database (only the columns needed for export)
    $stmt = $conn->prepare("
        SELECT 
            user_id, first_name, middle_name, last_name, username, phoneNumber, email, role_id, 
            created_at, status, gender, street_address, city, postal_code, barangay, soc_med, mu_status 
        FROM users
    ");
    $stmt->execute();
    $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate the dynamic filename based on the current date and time
    $date = new DateTime();
    $formattedDate = $date->format('m-d-Y-H-i-A'); // Changed format for cleaner filename
    $filename = 'MU-' . $formattedDate . '.csv';

    // Generate CSV
    if (count($usersData) > 0) {
        // Set headers to download the CSV file with dynamic filename
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Open PHP output stream for CSV
        $output = fopen('php://output', 'w');

        // Write the table headers (titles) to the CSV
        $headers = [
            "No.",
            "First Name",
            "Middle Name",
            "Last Name",
            "Username",
            "Phone Number",
            "Email",
            "Role",
            "Created At",
            "Status",
            "Gender",
            "Street Address",
            "City",
            "Postal",
            "Barangay",
            "Social Media",
            "Action"
        ];

        // Write headers to the CSV
        fputcsv($output, $headers);

        // Write each row of user data to the CSV
        $counter = 1; // Counter for the "No." column
        foreach ($usersData as $user) {
            $csvRow = [
                $counter++,
                $user['first_name'],
                $user['middle_name'],
                $user['last_name'],
                $user['username'],
                $user['phoneNumber'],
                $user['email'],
                $user['role_id'],
                $user['created_at'],
                $user['status'],
                $user['gender'],
                $user['street_address'],
                $user['city'],
                $user['postal_code'],
                $user['barangay'],
                $user['soc_med'],
                $user['mu_status'] // This is the "Action" column
            ];

            fputcsv($output, $csvRow);
        }

        // Close the output stream
        fclose($output);
        exit;  // Stop further execution
    } else {
        echo "No user data found.";
    }
}
?>
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                    <div class="headerText" id="manageUsersBtn" style="cursor: pointer;">
                        Manage Users
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('manageUsersBtn').addEventListener('click', function () {
                    // Trigger the CSV download only if the user has role_id = 1
                    <?php if ($currentUserRoleId == 1): ?>
                        window.location.href = window.location.href + "?download_csv=true";  // Append download flag to URL
                    <?php else: ?>
                        alert("You do not have permission to download the CSV.");
                    <?php endif; ?>
                });
            </script>
            <!-- Content Wrapper (Side Panel + Profile Main) -->
            <div class="contentWrapper">
                <!-- Sidebar -->
                <div class="sidepanel">
                    <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 100%;">
                        <ul class="nav nav-pills flex-column mb-auto">
                            <br>
                            <!-- <li class="nav-item">
                                <a href="../templates/SA.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-roles.png" alt="Manage Roles" width="16" height="16"
                                        class="me-2" />
                                    Manage Roles
                                </a>
                            </li> -->
                      
                                <li>
                                    <a href="../templates/SA-manageUsers.php" class="nav-link active">
                                        <img src="../assets/images/icon-users.png" alt="Manage Users" width="16" height="16"
                                            class="me-2" />
                                        Manage Users
                                    </a>
                                </li>
                   
                            <?php if ($role_id == 1): ?>
                            <li>
                                <a href="../templates/getDeletedAccountData.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-racoontrash.png" alt="Deleted Logs" width="16"
                                        height="16" class="me-2" />
                                    Deleted User Logs
                                </a>
                            </li>
                            <?php endif; ?>
                            <br><br><br><br><br><br><br><br><br><br>
                        </ul>
                    </div>
                </div>
                <!-- Main Profile Section -->
                <div class="profile-main">
                    <div class="flex-containerOneSA">
                        <div class="headerSACont">
                            <div class="searchContainer">
                                <button onclick="performSearch()" class="searchButton">
                                    <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </button>
                                <input type="text" id="searchInputX" placeholder=" " class="searchInput" />
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
                    <!-- Dynamic Table Script -->
                    <script>
                        // Set current user's role id from the PHP query result
                        const currentUserRoleId = <?php echo json_encode($currentUserRoleId); ?>;
                        console.log("Current User Role ID:", currentUserRoleId);

                        // Global variables for holding action details
                        let currentAction = "";
                        let actionUsername = "";
                        let actionUserId = "";
                        let actionSelectElement = null;

                        document.addEventListener("DOMContentLoaded", async function () {
                            try {
                                // Fetch the JSON data from SA-globalUserAll.php
                                const response = await fetch('../scripts/SA-globalUserAll.php');
                                console.log("HTTP Status:", response.status);
                                const text = await response.text();
                                console.log("Raw Response Text:", text);
                                const result = JSON.parse(text);
                                console.log("Parsed Response:", result);
                                if (result.debug) {
                                    console.log("Backend Debug Info:", result.debug);
                                }
                                const data = result.data;

                                // Create table element and assign the CSS class
                                const table = document.createElement('table');
                                table.classList.add("custom-table");

                                // Define headers
                                const headers = [
                                    { title: "No.", class: "col-no" },
                                    { title: "First Name", class: "col-first_name" },
                                    // { title: "Middle Name", class: "col-middle_name" },
                                    { title: "Last Name", class: "col-last_name" },
                                    { title: "Username", class: "col-username" },
                                    { title: "Phone Number", class: "col-phoneNumber" },
                                    { title: "Email", class: "col-email" },
                                    { title: "Role", class: "col-role_id" },
                                    { title: "Created At", class: "col-created_at" },
                                    { title: "Status", class: "col-status" },
                                    { title: "Gender", class: "col-gender" },
                                    { title: "City", class: "col-city" },
                                    { title: "Street Address", class: "col-street_address" },
                                    { title: "Postal", class: "col-postal_code" },
                                    { title: "Barangay", class: "col-barangay" },
                                    { title: "Social Media", class: "col-soc_med" },
                                    { title: "Action", class: "col-action" }
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
                                    // Store data attributes
                                    tr.dataset.username = row.username;
                                    tr.dataset.user_id = row.user_id;

                                    headers.forEach(headerInfo => {
                                        const td = document.createElement('td');
                                        td.className = headerInfo.class;

                                        if (headerInfo.title === "No.") {
                                            td.textContent = index + 1;
                                        } else if (headerInfo.title === "Action") {
                                            const select = document.createElement('select');
                                            select.style.minWidth = "110px";
                                            select.style.padding = "4px";
                                            select.style.borderRadius = "4px";

                                            const statusOptions = ['block', 'unblock', 'delete'];
                                            const currentStatus = row.mu_status;

                                            statusOptions.forEach(optionValue => {
                                                if (optionValue === "delete" && currentUserRoleId != 1) return;
                                                const option = document.createElement('option');
                                                option.value = optionValue;
                                                option.textContent = optionValue.charAt(0).toUpperCase() + optionValue.slice(1);
                                                if (currentStatus === optionValue) {
                                                    option.selected = true;
                                                }
                                                select.appendChild(option);
                                            });

                                            // Apply color based on the selected status
                                            updateSelectStyle(select, currentStatus);

                                            td.appendChild(select);

                                        } else {
                                            const key = headerInfo.class.replace("col-", "");
                                            let value = row[key] || "";
                                            if (key === "soc_med") {
                                                try {
                                                    const socMedObj = JSON.parse(value);
                                                    value = Object.entries(socMedObj)
                                                        .map(([platform, link]) => `${platform}: ${link}`)
                                                        .join(", ");
                                                } catch (e) { /* Use raw value on failure */ }
                                            }
                                            td.textContent = value;
                                            if (key !== "email" && key !== "action") {
                                                td.title = value;
                                                td.style.whiteSpace = "nowrap";
                                                td.style.overflow = "hidden";
                                                td.style.textOverflow = "ellipsis";
                                                td.style.maxWidth = "200px";
                                            }
                                        }
                                        tr.appendChild(td);
                                    });
                                    tbody.appendChild(tr);
                                });
                                table.appendChild(tbody);

                                // Insert table in container
                                const container = document.getElementById('table-container');
                                container.innerHTML = '';
                                container.appendChild(table);

                                // Add event listeners to all dropdowns for actions
                                table.querySelectorAll('select').forEach(select => {
                                    select.addEventListener('change', function () {
                                        const action = this.value;
                                        updateSelectStyle(this, action);
                                        const tr = this.closest('tr');
                                        const username = tr.dataset.username;
                                        const userId = tr.dataset.user_id;
                                        // Store values globally for modal use
                                        currentAction = action;
                                        actionUsername = username;
                                        actionUserId = userId;
                                        actionSelectElement = this;
                                        // Determine which modal to show based on the selected action
                                        if (action === "delete") {
                                            // Show delete modal which includes the password input
                                            const deleteModal = new bootstrap.Modal(document.getElementById('deletePasswordModal'));
                                            deleteModal.show();
                                        } else {
                                            // For block and unblock, show confirmation modal
                                            const actionModal = new bootstrap.Modal(document.getElementById('actionConfirmModal'));
                                            // Update modal text with username and action details
                                            document.getElementById('actionModalText').textContent =
                                                `Are you sure you want to ${action} "${username}"?`;
                                            actionModal.show();
                                        }
                                    });
                                });
                            } catch (error) {
                                console.error("Error fetching dynamic table data:", error);
                            }

                        });
                    </script>

                    <script>

                        function updateSelectStyle(select, value) {

                            select.style.borderRadius = "10px";

                            switch (value) {
                                case "block":
                                    select.style.backgroundColor = "#FFD1A6"; // orange
                                    select.style.color = "#552900"; // black text
                                    break;
                                case "unblock":
                                    select.style.backgroundColor = "#BFFFBF"; // green
                                    select.style.color = "#002E00"; // white text
                                    break;
                                case "delete":
                                    select.style.backgroundColor = "#FFBFBF"; // red
                                    select.style.color = "#680404"; // white text
                                    break;
                                default:
                                    select.style.backgroundColor = ""; // reset
                                    select.style.color = "";
                            }
                        }

                    </script>

                </div>
            </div>
        </div>
    </div>

    <!-- Generic Action Confirmation Modal for Block/Unblock -->
    <div class="modal fade" id="actionConfirmModal" tabindex="-1" aria-labelledby="actionConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionConfirmModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="actionModalText"></p>
                </div>
                <div class="modal-footer">

                    <button id="actionConfirmBtn" type="button" class="btn btn-primary">Yes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Delete Confirmation Modal with Password Input -->
    <div class="modal fade" id="deletePasswordModal" tabindex="-1" aria-labelledby="deletePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deletePasswordForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deletePasswordModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please enter your password to confirm deletion of <strong id="modalDeleteUsername"></strong>:
                        </p>
                        <div class="mb-3">
                            <label for="passwordInput" class="form-label">Password</label>
                            <input type="password" class="form-control" id="passwordInput" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <!-- Search Script -->
    <script>
        document.getElementById("searchInputX").addEventListener("input", performSearch);
        function performSearch() {
            const filter = document.getElementById("searchInputX").value.toLowerCase();
            const table = document.querySelector("table.custom-table");
            if (!table) return;
            const rows = table.querySelectorAll("tbody tr");
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.indexOf(filter) > -1 ? "" : "none";
            });
        }
    </script>

    <!-- Modal Event Handlers -->
    <script>
        // Handle confirmation from the generic (block/unblock) modal
        document.getElementById("actionConfirmBtn").addEventListener("click", async function () {
            // Hide the modal
            const actionModalEl = document.getElementById('actionConfirmModal');
            const actionModal = bootstrap.Modal.getInstance(actionModalEl);
            actionModal.hide();

            try {
                const payload = { username: actionUsername, user_id: actionUserId, action: currentAction };
                const updateResponse = await fetch('../scripts/SA-manageUsersAction.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const updateResult = await updateResponse.json();
                if (updateResult.success) {
                    alert("Action executed successfully!");
                    window.location.reload();
                } else {
                    alert("Failed to execute action: " + updateResult.message);
                }
            } catch (error) {
                alert("An error occurred while executing the action.");
            }
            if (actionSelectElement) actionSelectElement.value = "";
        });

        // Update delete modal username when shown
        const deleteModalEl = document.getElementById('deletePasswordModal');
        deleteModalEl.addEventListener('show.bs.modal', function () {
            document.getElementById("modalDeleteUsername").textContent = actionUsername;
        });

        // Handle deletion from the delete modal form
        document.getElementById("deletePasswordForm").addEventListener("submit", async function (e) {
            e.preventDefault();
            const password = document.getElementById("passwordInput").value;
            const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
            deleteModal.hide();

            try {
                const payload = { username: actionUsername, user_id: actionUserId, action: "delete", password: password };
                const updateResponse = await fetch('../scripts/SA-manageUsersAction.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const updateResult = await updateResponse.json();
                if (updateResult.success) {
                    alert("Action executed successfully!");
                    window.location.reload();
                } else {
                    alert("Failed to execute action: " + updateResult.message);
                }
            } catch (error) {
                alert("An error occurred while executing the action.");
            }
            document.getElementById("passwordInput").value = "";
            if (actionSelectElement) actionSelectElement.value = "";
        });
    </script>
</body>

</html>


<script>
    // Attach an event listener to the search input field
    document.getElementById("searchInputX").addEventListener("input", performSearch);

    function performSearch() {
        const filter = document.getElementById("searchInputX").value.toLowerCase();
        const table = document.querySelector("table.custom-table");
        if (!table) return;
        const rows = table.querySelectorAll("tbody tr");
        rows.forEach(row => {
            // Get all the text content from each row excluding the Action column (just for searching row data)
            const rowText = row.textContent.toLowerCase();

            // Get the selected value of the action dropdown in the row
            const actionSelect = row.querySelector('select');
            const actionValue = actionSelect ? actionSelect.options[actionSelect.selectedIndex].text.toLowerCase() : '';

            // Check if the filter matches row text or the action value
            if (rowText.indexOf(filter) > -1 || actionValue.indexOf(filter) > -1) {
                row.style.display = ""; // Show row if filter matches
            } else {
                row.style.display = "none"; // Hide row if no match
            }
        });
    }
</script>