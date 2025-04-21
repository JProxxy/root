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
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');

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

                            <li>
                                <a href="../templates/SA-manageUsers.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-users.png" alt="Manage Users" width="16" height="16"
                                        class="me-2" />
                                    Manage Users
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="../templates/SA.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/rfid.png" alt="Manage RFID" width="16" height="16"
                                        class="me-2" />
                                    Manage RFID
                                </a>
                            </li>
                            <li>
                                <a href="../templates/getDeletedAccountData.php" class="nav-link active">
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
                            <br>
                            Deleted Accounts Backups
                            <br><br>
                            Any accounts that are deleted will remain in a deactivated state for up to 30 days. After
                            this period, they will be permanently deleted and cannot be recovered.

                        </div>

                        <div class="delete-all-container">
                            <button type="button" class="btn btn-delete-all" onclick="deleteAllFiles()">Delete
                                all</button>
                        </div>

                        <br>

                        <!-- Roles Container with Dynamic Table -->
                        <div class="rolesCont">
                            <?php
                            // Define the backup directory (adjust the path as needed)
                            $backupDir = __DIR__ . '/../storage/user/deleted_userAccounts/';

                            // Check if the backup directory exists
                            if (!is_dir($backupDir)) {
                                die("Backup directory not found.");
                            }

                            // Get all files in the backup directory (excluding . and ..)
                            $files = array_diff(scandir($backupDir), array('.', '..'));

                            // Get current time
                            $currentTime = time();

                            // right after you scan $files:
                            $pattern = '/^(\d+)_([^_]+)_([\w]+)_(\d{2}-\d{2}-\d{4}-\d{2}-\d{2}-(?:AM|PM))\.csv$/i';
                            $oldFiles = [];
                            $currentTime = time();

                            foreach ($files as $file) {
                                if (preg_match($pattern, $file, $m)) {
                                    $prettyDate = $m[4];
                                    // convert "04-21-2025-07-27-AM" → timestamp
                                    $ts = strtotime(str_replace('-', ' ', $prettyDate));
                                    if (($currentTime - $ts) > 30 * 24 * 60 * 60) {
                                        $oldFiles[] = [
                                            'file' => $file,
                                            'account' => strtolower("{$m[2]}_{$m[3]}") . '@rivaniot.online',
                                            'date' => str_replace('-', ' ', $prettyDate),
                                        ];
                                    }
                                }
                            }

                            // Check if the backup directory exists
                            if (!is_dir($backupDir)) {
                                die("Backup directory not found.");
                            }

                            // Get all files in the backup directory (excluding . and ..)
                            $files = array_diff(scandir($backupDir), array('.', '..'));

                            if (empty($files)): ?>
                                <p>No backup files found.</p>
                            <?php else: ?>
                                <style>
                                    .scrollable-table-container {
                                        max-height: 400px;
                                        /* or any height you want */
                                        overflow-y: auto;
                                    }
                                </style>
                                <div class="scrollable-table-container"> <!-- Add the scrollable container -->
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Date</th>
                                                <th class="action-column">Action</th> <!-- Add a class for centering -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($files as $index => $file): ?>
                                                <?php
                                                /* 
                                                  Expected filename format:
                                                  [user_id]_[table]___[emailPart]_[timestamp].csv
                                                  Example: 51_users___eaquierdojeraldine_04-09-2025-07-21-PM.csv
                                                */
                                                $pattern = '/^(\d+)_([^_]+)_(.+?)_(?:(\d{2}-\d{2}-\d{4}-\d{2}-\d{2}-(?:AM|PM))|(\d{10}))\.csv$/i';
                                                if (preg_match($pattern, $file, $matches)) {
                                                    // Build the account email as "table_emailPart@rivaniot.online"
                                                    $tableName = $matches[2];          // e.g., "users"
                                                    $emailPart = $matches[3];          // e.g., "eaquierdojeraldine"
                                                    $account = strtolower($tableName . '_' . $emailPart) . '@rivaniot.online';

                                                    // Format the timestamp (replace '-' with space)
                                                    $rawTimestamp = $matches[4];       // e.g., "04-09-2025-07-21-PM"
                                                    $formattedDate = str_replace('-', ' ', $rawTimestamp);  // "04 09 2025 07 21 PM"
                                                } else {
                                                    // For files that don't match, skip this iteration.
                                                    continue;
                                                }
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($account); ?></td>
                                                    <td><?php echo htmlspecialchars($formattedDate); ?></td>
                                                    <td class="action-column">
                                                        <!-- Download button -->
                                                        <a href="/storage/user/deleted_userAccounts/<?php echo urlencode($file); ?>"
                                                            class="btn btn-download"
                                                            download="<?php echo htmlspecialchars($file); ?>">
                                                            Download
                                                        </a>
                                                        <!-- Recover button (calls your JS modal for recovery) -->
                                                        <button type="button" class="btn btn-recover"
                                                            onclick="showRecoverModal('<?php echo htmlspecialchars($account); ?>', '<?php echo htmlspecialchars($file); ?>')">
                                                            Recover
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div> <!-- End of the scrollable container -->
                            <?php endif; ?>
                        </div>

                        <style>
                            /* Add a solid line below the titles of Account, Date, Action, but make it shorter on the right */
                            .table thead tr {
                                position: relative;
                                /* To position the pseudo-element relative to the row */
                            }

                            .table thead tr::after {
                                content: '';
                                position: absolute;
                                bottom: 0;
                                left: 0px;
                                width: 95%;
                                /* Adjust this to control how short the line is */
                                border-bottom: 2px solid #797979;
                            }

                            /* Reset Bootstrap's table striped effect */
                            /* Remove Bootstrap table striped effect */
                            .table-striped tbody tr:nth-of-type(odd) {
                                background-color: transparent !important;
                            }

                            /* Reset padding and other styles for table cells */
                            .table>:not(caption)>*>* {
                                padding: 0.5rem !important;
                                background-color: transparent !important;
                                color: inherit !important;
                                box-shadow: none !important;
                            }

                            /* Set the background color of table headers and cells to white */
                            .table th,
                            .table td {
                                background-color: #FFFFFF !important;
                                /* White background for all cells */
                                border: 1px solid #FFFFFF !important;
                                /* White border to blend with background */
                                padding: 8px 16px;

                            }

                            .table thead th {
                                font-weight: 600;
                                /* Set the font weight to 400 (normal) */
                            }

                            /* Sticky header styling */
                            .table th {
                                position: sticky;
                                top: 0;
                                background-color: #FFFFFF !important;
                                /* Sticky header stays white */
                                z-index: 1;
                                /* Ensure the header stays on top */
                            }

                            /* Ensure the Action column is centered */
                            .action-column {
                                text-align: center !important;
                                /* Force content to be centered */
                            }

                            .action-column button,
                            .action-column a {
                                margin: 0 5px;
                                /* Space between buttons */
                            }

                            /* Styles for the Download button */
                            .btn-download {
                                background-color: #CFDBDD;
                                color: #0B3236;
                                padding: 8px 16px;
                                border: none;
                                border-radius: 40px;
                                font-size: 14px;
                                text-decoration: none;
                                cursor: pointer;
                                transition: background-color 0.3s ease;
                            }

                            .btn-download:hover {
                                background-color: #B9C6C8;
                                /* Darker green on hover */
                            }

                            /* Styles for the Recover button */
                            .btn-recover {
                                background-color: #95C0C5;
                                color: #FFFFFF;
                                padding: 8px 20px;
                                border: none;
                                border-radius: 40px;
                                font-size: 14px;
                                cursor: pointer;
                                transition: background-color 0.3s ease;
                            }

                            .btn-recover:hover {
                                background-color: #739A9E;
                                /* Darker blue on hover */
                            }

                            .delete-all-container {
                                text-align: right;
                                margin-right: 6.5%;

                            }

                            .btn-delete-all {
                                background-color: #F89F89;
                                color: #511D1B;
                                padding: 8px 16px;
                                border: none;
                                border-radius: 25px;
                                cursor: pointer;
                                font-size: 14px;
                                transition: background-color 0.3s ease;
                            }

                            .btn-delete-all:hover {
                                background-color: #D9836E;
                            }
                        </style>


                    </div>
                    <!-- Dynamic Table Script -->


                </div>
            </div>
        </div>
    </div>

    <!-- Delete Files Modal -->
    <div class="modal fade" id="deleteFilesModal" tabindex="-1" aria-labelledby="deleteFilesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteFilesModalLabel">Select Files to Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="deleteFilesForm">
                        <?php if (empty($oldFiles)): ?>
                            <p class="text-muted">No files older than 30 days to delete.</p>
                        <?php else: ?>
                            <ul id="deleteFilesList" class="list-group">
                                <?php foreach ($oldFiles as $f): ?>
                                    <li class="list-group-item">
                                        <input type="checkbox" name="filesToDelete[]"
                                            value="<?= htmlspecialchars($f['file'], ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($f['account'], ENT_QUOTES) ?>– <?= htmlspecialchars($f['date'], ENT_QUOTES) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmDeletion()">Delete Selected
                        Files</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Confirmation Modal for Deletion -->
    <div class="modal fade" id="passwordConfirmationModal" tabindex="-1"
        aria-labelledby="passwordConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="passwordConfirmationForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="passwordConfirmationModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please enter your password to confirm the deletion of the selected files.</p>
                        <div class="mb-3">
                            <label for="adminPasswordInput" class="form-label">Password</label>
                            <input type="password" class="form-control" id="adminPasswordInput" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Deletion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>

        // Define the deleteAllFiles function
        function deleteAllFiles() {
            // Show the modal for selecting files to delete
            const deleteFilesModal = new bootstrap.Modal(document.getElementById('deleteFilesModal'));
            deleteFilesModal.show(); // Display the modal to select files for deletion
        }


        function confirmDeletion() {
            // Show the password confirmation modal
            const deleteFilesModal = new bootstrap.Modal(document.getElementById('deleteFilesModal'));
            deleteFilesModal.hide(); // Close the first modal

            const passwordConfirmationModal = new bootstrap.Modal(document.getElementById('passwordConfirmationModal'));
            passwordConfirmationModal.show(); // Show the second modal
        }

        // Handle form submission for password confirmation
        document.getElementById('passwordConfirmationForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const password = document.getElementById('adminPasswordInput').value;
            const selectedFiles = Array.from(document.querySelectorAll('input[name="filesToDelete[]"]:checked'))
                .map(checkbox => checkbox.value);

            if (selectedFiles.length === 0) {
                alert('Please select at least one file to delete.');
                return;
            }

            // Send password and selected files to the server for validation and deletion
            try {
                const response = await fetch('../scripts/deleteAllFiles.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ password, files: selectedFiles })
                });
                const result = await response.json();

                if (result.success) {
                    alert('Deletion successful!');
                    window.location.reload();
                } else {
                    alert('Failed to delete files: ' + result.message);
                }
            } catch (error) {
                alert('An error occurred while deleting files.');
            }
        });

    </script>

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


    <!-- Recover Confirmation Modal with Password Input -->
    <div class="modal fade" id="recoverPasswordModal" tabindex="-1" aria-labelledby="recoverPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="recoverPasswordForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="recoverPasswordModalLabel">Confirm Recovery</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please enter your password to confirm recovery of <strong id="modalRecoverEmail"></strong>:
                        </p>
                        <div class="mb-3">
                            <label for="recoverPasswordInput" class="form-label">Password</label>
                            <input type="password" class="form-control" id="recoverPasswordInput" required />
                            <input type="hidden" id="recoverFilename" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Recovery</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



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

        function showRecoverModal(email, file) {
            console.log("showRecoverModal called with:", { email, file });
            document.getElementById("modalRecoverEmail").textContent = email;
            document.getElementById("recoverPasswordInput").value = "";
            document.getElementById("recoverFilename").value = file;

            const recoverModal = new bootstrap.Modal(document.getElementById('recoverPasswordModal'));
            recoverModal.show();
        }

        document.getElementById("recoverPasswordForm").addEventListener("submit", async function (e) {
            e.preventDefault();
            const password = document.getElementById("recoverPasswordInput").value;
            const file = document.getElementById("recoverFilename").value;

            console.log("Recover form submitted.", { password, file });

            const formData = new FormData();
            formData.append("password", password);
            formData.append("file", file);

            // Debug: List all keys in the formData
            for (let pair of formData.entries()) {
                console.log("Form Data:", pair[0], pair[1]);
            }

            try {
                const response = await fetch('../scripts/Recover.php', {
                    method: 'POST',
                    body: formData
                });

                console.log("Fetch response status:", response.status);
                const text = await response.text();
                console.log("Server response text:", text);

                if (response.ok && text.includes("successfully")) {
                    alert("Recovery successful.");
                    window.location.reload();
                } else {
                    console.error("Recovery failed:", text);
                    // Get the file path (we only have the file name here; if you want full path information, it must be provided from the server)
                    alert("⚠️ Recovery failed.\n\nFile path: " + file + "\n\nDetails from server:\n" + text);
                }
            } catch (error) {
                console.error("Error during fetch:", error);
                alert("An error occurred while recovering the account: " + error.message);
            }

            const recoverModalEl = document.getElementById('recoverPasswordModal');
            const modal = bootstrap.Modal.getInstance(recoverModalEl);
            modal.hide();
        });

    </script>
</body>

</html>