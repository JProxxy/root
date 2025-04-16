<?php
// Include the database connection using PDO
include '../app/config/connection.php';

try {
    // Fetch all users' role_id, first_name, and last_name
    $query = "SELECT role_id, first_name, last_name FROM users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Define role mapping array
    $roleMapping = [
        1 => "Super Admin",
        2 => "Admin",
        3 => "Staff Member",
        4 => "Student"
    ];
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
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
    <link rel="stylesheet" href="../assets/css/sa.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        table.custom-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table.custom-table th,
        table.custom-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table.custom-table th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .action-icons i {
            margin-right: 10px;
            cursor: pointer;
        }
    </style>
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
                            <li>
                                <a href="../templates/SA-manageUsers.php" class="nav-link link-body-emphasis">
                                    <img src="../assets/images/icon-users.png" alt="Manage Users" width="16" height="16"
                                        class="me-2" />
                                    Manage Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="../templates/SA.php" class="nav-link active">
                                    <img src="../assets/images/rfid.png" alt="Manage RFID" width="16" height="16"
                                        class="me-2" />
                                    Manage RFID
                                </a>
                            </li>
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
                        <!-- Roles Container with PHP Generated Table -->
                        <div class="rolesCont">
                            <div id="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Role</th>
                                            <th>User Name</th>
                                            <th>RFID</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (count($users) > 0) {
                                            $count = 1;
                                            foreach ($users as $user) {
                                                // Map role_id to its name based on the mapping
                                                $role = isset($roleMapping[$user['role_id']]) ? $roleMapping[$user['role_id']] : "Unknown";

                                                // Format user name: lastname in lowercase, then a comma, space and the first character of first_name in lowercase
                                                $userName = strtolower($user['last_name']) . ", " . strtolower(substr($user['first_name'], 0, 1));

                                                echo "<tr>";
                                                echo "<td>" . $count . "</td>";
                                                echo "<td>" . htmlspecialchars($role) . "</td>";
                                                echo "<td>" . htmlspecialchars($userName) . "</td>";
                                                echo "<td></td>"; // RFID column left blank
                                                echo "<td class='action-icons'>";
                                                echo "<i class='fa fa-edit' title='Edit'></i>";
                                                echo "<i class='fa fa-trash' title='Delete'></i>";
                                                echo "</td>";
                                                echo "</tr>";
                                                $count++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='5'>No users found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<script>
    // Attach a search event listener to the search input field
    document.getElementById("searchInputX").addEventListener("input", performSearch);

    function performSearch() {
        // Retrieve and convert the search query to lowercase
        const filter = document.getElementById("searchInputX").value.toLowerCase();

        // Locate the table; exit if not found
        const table = document.querySelector("table.custom-table");
        if (!table) return;

        // Get all rows in the table body
        const rows = table.querySelectorAll("tbody tr");

        // For each row, check if its text content matches the search query. Hide if no match.
        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            row.style.display = rowText.indexOf(filter) > -1 ? "" : "none";
        });
    }
</script>
