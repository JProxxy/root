<?php
// Include the database connection using PDO
include '../app/config/connection.php';

try {
    // Fetch all users' role_id, first_name, last_name, profile_picture, and email
    $query = "
    SELECT user_id, role_id, first_name, last_name, profile_picture, email, rfid 
    FROM users
  ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Define custom order for roles
    $roleOrder = [
        1 => 0, // Super Admin
        2 => 1, // Admin
        3 => 2, // Staff Member
        4 => 3  // Student
    ];

    // Sort users based on the custom role order
    usort($users, function ($a, $b) use ($roleOrder) {
        $roleA = $roleOrder[$a['role_id']] ?? PHP_INT_MAX;
        $roleB = $roleOrder[$b['role_id']] ?? PHP_INT_MAX;
        return $roleA <=> $roleB;
    });

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

// Optional: define a URL existence checker (useful for profile_picture validation)
function url_exists(string $url): bool
{
    $headers = @get_headers($url);
    return is_array($headers) && preg_match('#^HTTP/.*\s+200\s#i', $headers[0] ?? '');
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
            table-layout: fixed;
            /* Prevents overly wide columns */
        }

        table.custom-table th,
        table.custom-table td {
            border: none;
            padding: 8px;
            text-align: center;
        }

        /* RFID column: narrow with ellipsis */
        .custom-table th:nth-child(4),
        .custom-table td:nth-child(4) {
            width: 100px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Show full RFID on hover (optional) */
        .custom-table td:nth-child(4):hover {
            overflow: visible;
            white-space: normal;
            background-color: #f9f9f9;
            z-index: 10;
            position: relative;
        }

        /* Improve layout of User Name cell */
        .custom-table td.username-cell {
            display: flex;
            align-items: center;
            padding-left: 20px;
            text-align: left;
            white-space: nowrap;
        }

        /* Capitalize and style usernames */
        .custom-table td.username-cell span {
            text-transform: capitalize;
            font-weight: 500;
            font-size: 15px;
            color: #333;
        }

        .custom-table th:nth-child(1),
        .custom-table td:nth-child(1) {
            width: 50px;
            text-align: center;
        }

        /* Action icons (edit, delete) */
        .action-icons i {
            margin-right: 10px;
            cursor: pointer;
        }

        /* Avatar next to username */
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
            vertical-align: middle;
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
                            <?php if ($role_id !== 2): ?>
                                <li id="deletedUserLogsMenu">
                                    <a href="../templates/getDeletedAccountData.php" class="nav-link active">
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

                                                // Determine username: use last_name & first_name initial or fallback to email
                                                if (!empty($user['last_name']) && !empty($user['first_name'])) {
                                                    $userName = strtolower($user['last_name']) . ", " . strtolower(substr($user['first_name'], 0, 1));
                                                } else {
                                                    $emailLocal = strstr($user['email'], '@', true); // part before @
                                        
                                                    if ($emailLocal === "superadmin") {
                                                        $userName = "superadmin";
                                                    } elseif (preg_match('/^([a-zA-Z])([a-zA-Z]+)(?:\.\w+)?$/', $emailLocal, $matches)) {
                                                        $firstInitial = $matches[1];
                                                        $lastName = $matches[2];
                                                        $userName = strtolower($lastName) . ", " . strtolower($firstInitial);
                                                    } else {
                                                        // fallback for unmatched patterns
                                                        $userName = strtolower($emailLocal) . ", x";
                                                    }
                                                }

                                                // Determine CSS class for role color
                                                $roleClass = '';
                                                switch ($user['role_id']) {
                                                    case 1:
                                                        $roleClass = 'role-superadmin';
                                                        break;
                                                    case 2:
                                                        $roleClass = 'role-admin';
                                                        break;
                                                    case 3:
                                                        $roleClass = 'role-staff';
                                                        break;
                                                    case 4:
                                                        $roleClass = 'role-student';
                                                        break;
                                                }

                                                // Build avatar URL (stored or initials fallback)
                                                if (!empty($user['profile_picture'])) {
                                                    $picUrl = $user['profile_picture'];
                                                } else {
                                                    // Use initials from name if available, otherwise fall back to email
                                                    if (!empty($user['first_name']) && !empty($user['last_name'])) {
                                                        $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                                                    } elseif (!empty($user['email'])) {
                                                        $local = strstr($user['email'], '@', true);
                                                        $initials = strtoupper(substr($local, 0, 2)); // First 2 letters of email prefix
                                                    } else {
                                                        $initials = "U"; // Default fallback
                                                    }
                                                    $picUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=random&color=fff";
                                                }




                                                echo "<tr data-userid='{$user['user_id']}'>";
                                                echo "<td>{$count}</td>";
                                                echo "<td><span class='{$roleClass}'>" . htmlspecialchars($role) . "</span></td>";
                                                echo "<td style='text-align:left; padding-left:50px;'>
                                                        <img src='{$picUrl}' class='user-avatar' />
                                                        {$userName}
                                                      </td>";
                                                // Show current RFID
                                                echo "<td class='rfid-cell'>" . htmlspecialchars($user['rfid']) . "</td>";
                                                // Edit icon with data attributes
                                                echo "<td class='action-icons'>
                                                <i 
                                                  class='fa fa-edit edit-btn' 
                                                  title='Edit' 
                                                  data-userid='{$user['user_id']}' 
                                                  data-role='" . htmlspecialchars($role) . "' 
                                                  data-username='" . htmlspecialchars($userName) . "' 
                                                  data-rfid='" . htmlspecialchars($user['rfid']) . "' 
                                                  data-profilepic='" . htmlspecialchars($picUrl) . "' 
                                                ></i>
                                                <i 
                                                  class='fa fa-trash delete-btn' 
                                                  title='Delete RFID' 
                                                  data-userid='{$user['user_id']}'
                                                ></i>
                                              </td>";

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
    <script>
        // Attach a search event listener to the search input field
        document.getElementById("searchInputX").addEventListener("input", performSearch);

        function performSearch() {
            const filter = document.getElementById("searchInputX").value.toLowerCase();
            const table = document.querySelector("table.custom-table");
            if (!table) return;
            const rows = table.querySelectorAll("tbody tr");
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().indexOf(filter) > -1 ? "" : "none";
            });
        }



    </script>



    <!-- Edit RFID Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content p-4" id="editRfidForm">


                <div class="modal-body pt-2">
                    <input type="hidden" id="modalUserId" name="user_id">

                    <!-- Role -->
                    <div class="row align-items-center mb-3">
                        <div class="col-sm-2 text-end">
                            <label for="modalRole" class="col-form-label">Role</label>
                        </div>
                        <div class="col-sm-2"><!-- empty spacer --></div>
                        <div class="col-sm-8">
                            <span id="modalRole" class="role-badge"></span>
                        </div>
                    </div>

                    <!-- Username -->
                    <div class="row align-items-center mb-3">
                        <!-- Label -->
                        <label class="col-sm-2 text-end col-form-label">Username</label>
                        <!-- Content: img + name in one flexbox -->
                        <div class="col-sm-10 d-flex align-items-center">
                            <img src="" id="modalProfilePic" alt="Profile" class="rounded-circle" width="32"
                                height="32">
                            <span class="ms-2" id="modalUsername">peñarubia, j</span>
                        </div>
                    </div>



                    <!-- RFID -->
                    <div class="row align-items-center mb-3">
                        <!-- Label -->
                        <div class="col-sm-2 text-end">
                            <label for="modalRfid" class="col-form-label">RFID</label>
                        </div>
                        <div class="col-sm-2"><!-- spacer --></div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="modalRfid" name="rfid" required>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editModalEl = document.getElementById('editModal');
            const modal = new bootstrap.Modal(editModalEl); // ⬅️ move here
            let currentRow;

            // Edit RFID
            document.querySelectorAll('.edit-btn').forEach(btn =>
                btn.addEventListener('click', () => {
                    const userId = btn.dataset.userid;
                    const role = btn.dataset.role;
                    const username = btn.dataset.username;
                    const profilePic = btn.dataset.profilepic;
                    const rfid = btn.dataset.rfid;

                    const profilePicUrl = `${profilePic}?_=${Date.now()}`;

                    document.getElementById('modalUserId').value = userId;

                    const modalRole = document.getElementById('modalRole');
                    modalRole.textContent = role;
                    modalRole.className = '';
                    modalRole.classList.add('role-badge');
                    switch (role.toLowerCase()) {
                        case 'super admin':
                            modalRole.classList.add('role-superadmin');
                            break;
                        case 'admin':
                            modalRole.classList.add('role-admin');
                            break;
                        case 'staff member':
                            modalRole.classList.add('role-staff');
                            break;
                        case 'student':
                            modalRole.classList.add('role-student');
                            break;
                        default:
                            modalRole.classList.add('bg-secondary', 'text-white');
                    }

                    document.getElementById('modalUsername').textContent = username;
                    document.getElementById('modalProfilePic').src = profilePicUrl;
                    document.getElementById('modalRfid').value = rfid;

                    currentRow = btn.closest('tr');
                    modal.show(); // ✅ Now it's accessible
                })
            );

            // Delete RFID
            document.querySelectorAll('.delete-btn').forEach(btn =>
                btn.addEventListener('click', async () => {
                    const userId = btn.dataset.userid;

                    if (confirm("Are you sure you want to delete this RFID?")) {
                        try {
                            const resp = await fetch('../scripts/update_rfid.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ user_id: userId })
                            });
                            const data = await resp.json();

                            if (data.success) {
                                btn.closest('tr').querySelector('.rfid-cell').textContent = '';
                            } else {
                                alert('Error: ' + (data.message || 'Could not delete RFID'));
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Network error, please try again');
                        }
                    }
                })
            );

            // Save RFID
            document.getElementById('editRfidForm').addEventListener('submit', async e => {
                e.preventDefault();
                const { user_id, rfid } = Object.fromEntries(new FormData(e.target));

                try {
                    const resp = await fetch('../scripts/update_rfid.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ user_id, rfid })
                    });
                    const data = await resp.json();

                    if (data.success) {
                        currentRow.querySelector('.rfid-cell').textContent = rfid;
                        modal.hide();
                    } else {
                        alert('Error: ' + (data.message || 'Could not save RFID'));
                    }
                } catch (err) {
                    console.error(err);
                    alert('Network error, please try again');
                }
            });
        });

    </script>


</body>

</html>