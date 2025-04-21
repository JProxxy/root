<?php
date_default_timezone_set('Asia/Manila');
include '../app/config/connection.php';

$selectedRole = $_GET['role'] ?? '';
$selectedAction = $_GET['action'] ?? '';

$selectedDateFrom = $_GET['date_from'] ?? '';
$selectedDateTo = $_GET['date_to'] ?? '';


$whereClauses = [];
$params = [];

if (!empty($selectedRole)) {
  $whereClauses[] = "u.role_id = :role";
  $params[':role'] = $selectedRole;
}

// Base queries with conditional logic for action filter
$queries = [];

// device_logs subquery
if ($selectedAction === '' || $selectedAction === 'device_logs') {
  $q = "
    SELECT 
        u.user_id, u.role_id, u.first_name, u.last_name, u.profile_picture, u.email,
        d.device_name, d.status, d.where AS location, d.last_updated AS timestamp,
        NULL AS minTemp, NULL AS maxTemp, NULL AS customizeTime,
        NULL AS minWater, NULL AS maxWater, NULL AS waterCustomizeTime,
        NULL AS gateMethod, NULL AS gateResult, NULL AS gateTimestamp
    FROM users u
    INNER JOIN device_logs d ON u.user_id = d.user_id
  ";
  if ($whereClauses)
    $q .= ' WHERE ' . implode(' AND ', $whereClauses);
  $queries[] = $q;
}

if ($selectedAction === '' || $selectedAction === 'customizeAC') {
  $q = "
    SELECT 
        u.user_id, u.role_id, u.first_name, u.last_name, u.profile_picture, u.email,
        NULL, NULL, NULL, ac.customizeTime AS timestamp,
        ac.minTemp, ac.maxTemp, ac.customizeTime,
        NULL, NULL, NULL,
        NULL, NULL, NULL
    FROM users u
    JOIN customizeAC ac ON u.user_id = ac.user_id
  ";
  if ($whereClauses)
    $q .= ' WHERE ' . implode(' AND ', $whereClauses);
  $queries[] = $q;
}

if ($selectedAction === '' || $selectedAction === 'customizeWater') {
  $q = "
    SELECT 
        u.user_id, u.role_id, u.first_name, u.last_name, u.profile_picture, u.email,
        NULL, NULL, NULL, cw.customizeTime AS timestamp,
        NULL, NULL, NULL,
        cw.minWater, cw.maxWater, cw.customizeTime,
        NULL, NULL, NULL
    FROM users u
    JOIN customizeWater cw ON u.user_id = cw.user_id
  ";
  if ($whereClauses)
    $q .= ' WHERE ' . implode(' AND ', $whereClauses);
  $queries[] = $q;
}

if ($selectedAction === '' || $selectedAction === 'gateAccess_logs') {
  $q = "
    SELECT 
        u.user_id, u.role_id, u.first_name, u.last_name, u.profile_picture, u.email,
        NULL, NULL, NULL, ga.timestamp,
        NULL, NULL, NULL,
        NULL, NULL, NULL,
        ga.method   AS gateMethod,
        ga.result   AS gateResult,
        ga.timestamp AS timestamp
        FROM gateAccess_logs ga
        LEFT JOIN users u ON u.user_id = ga.user_id        
  ";
  if ($whereClauses)
    $q .= ' WHERE ' . implode(' AND ', $whereClauses);
  $queries[] = $q;
}

// 2a) Build the raw UNION
$unionSql = implode(" UNION ALL ", $queries);

// 2b) Wrap it so we can filter on the alias “timestamp”
$query = "SELECT * FROM ({$unionSql}) AS all_logs";

// 2c) Add date filters if present
$outerClauses = [];
if (!empty($selectedDateFrom)) {
  $outerClauses[] = "all_logs.timestamp >= :date_from";
  $params[':date_from'] = $selectedDateFrom . ' 00:00:00';
}
if (!empty($selectedDateTo)) {
  $outerClauses[] = "all_logs.timestamp <= :date_to";
  $params[':date_to'] = $selectedDateTo . ' 23:59:59';
}
if ($outerClauses) {
  $query .= ' WHERE ' . implode(' AND ', $outerClauses);
}

// 2d) Finally order
$query .= " ORDER BY all_logs.timestamp DESC";


$stmt = $conn->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


$roleMapping = [
  1 => "Super Admin",
  2 => "Admin",
  3 => "Staff Member",
  4 => "Student"
];



// Helper function for checking if the URL exists
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
  <title>Activity Log</title>
  <link rel="stylesheet" href="../assets/css/dashboard.css" />
  <link rel="stylesheet" href="../assets/css/settings.css" />
  <link rel="stylesheet" href="../assets/css/settings-profile.css" />
  <link rel="stylesheet" href="../assets/css/settings-password.css" />
  <link rel="stylesheet" href="../assets/css/userActLogPage.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    /* Add your table and other CSS styles here */
    table.custom-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
      table-layout: fixed;
    }

    table.custom-table th,
    table.custom-table td {
      padding: 8px;
      height: 50px;
      line-height: 1.2;
      text-align: center;
      vertical-align: middle;
    }

    .custom-table th:nth-child(1),
    .custom-table td:nth-child(1) {
      width: 180px;
      /* Adjust this value as needed */
      text-align: center;
      padding-left: 0;
      /* Reset extra padding if needed */
    }


    .custom-table th:nth-child(2),
    .custom-table td:nth-child(2) {
      width: 230px;
      /* Adjust this value as needed */
      text-align: center;
      padding-left: 1 0;
      /* Reset extra padding if needed */
    }

    .custom-table td:nth-child(2) {
      text-align: left;
      padding-left: 40px;
    }

    .custom-table th:nth-child(3),
    .custom-table td:nth-child(3) {
      width: 100px;
      /* Adjust this value as needed */
      text-align: center;
      padding-left: 0;
      /* Reset extra padding if needed */
    }


    .custom-table td.username-cell {
      display: flex;
      align-items: center;
      white-space: nowrap;
    }

    .custom-table td.username-cell span {
      text-transform: capitalize;
      font-weight: 500;
      font-size: 15px;
      color: #333;
      overflow: hidden;
      text-overflow: ellipsis;
      display: inline-block;
    }

    .user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 8px;
    }

    /* Pagination styles */
    .pagination {
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }

    .pagination a,
    .pagination span {
      padding: 8px 16px;
      margin: 0 5px;
      border: 1px solid #ddd;
      border-radius: 4px;
      text-decoration: none;
      color: #007bff;
    }

    .pagination a:hover {
      background-color: #ddd;
    }

    .pagination span {
      background-color: #f1f1f1;
      color: #6c757d;
    }
  </style>
</head>

<body>
  <div class="bgMain">
    <?php include '../partials/bgMain.php'; ?>
    <div class="containerPart">
      <div class="headbackCont">
        <div class="imgBack">
          <a href="../templates/dashboard.php">
            <img src="../assets/images/back.png" alt="back" class="backIcon" />
          </a>
        </div>
        <div class="headerGroup">
          <div class="headerText">Activity Log</div>
        </div>
        <div class="headerSACont">
          <div class="searchContainer">
            <input type="text" id="searchInputX" placeholder=" " class="searchInput" />
            <button onclick="performSearch()" class="searchButton">
              <svg class="searchIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
            </button>
          </div>
        </div>

        <!-- Filter Icon -->
        <img src="../assets/images/filter.png" alt="filter" class="topRightOptions" onclick="openFilterModal()"
          style="cursor:pointer;" />

        <!-- Filter Modal -->
        <div id="filterModal"
          style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:1000;">
          <div
            style="background:#fff; width:400px; max-width:90%; padding:20px; border-radius:10px; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);">

            <h4 style="margin-top: 10px; color: #666;">Filter</h4>

            <!-- Role Filter -->
            <!-- Role Filter -->
            <label for="roleFilter">Role</label>
            <select id="roleFilter" style="width:100%; padding:8px; margin-bottom:15px;">
              <option value="">All</option>
              <option value="4" <?= $selectedRole == '4' ? 'selected' : '' ?>>Student (level 1)</option>
              <option value="3" <?= $selectedRole == '3' ? 'selected' : '' ?>>Staff Member (level 2)</option>
              <option value="2" <?= $selectedRole == '2' ? 'selected' : '' ?>>Admin (level 3)</option>
              <option value="1" <?= $selectedRole == '1' ? 'selected' : '' ?>>Super Admin (level 4)</option>
            </select>

            <!-- Action Filter -->
            <label for="actionFilter">Action Taken</label>
            <select id="actionFilter" style="width:100%; padding:8px; margin-bottom:20px;">
              <option value="" <?= $selectedAction == '' ? 'selected' : '' ?>>All</option>
              <option value="customizeAC" <?= $selectedAction == 'customizeAC' ? 'selected' : '' ?>>Air Conditioning System
              </option>
              <option value="gateAccess_logs" <?= $selectedAction == 'gateAccess_logs' ? 'selected' : '' ?>>Gate Access
                Control</option>
              <option value="device_logs" <?= $selectedAction == 'device_logs' ? 'selected' : '' ?>>Lighting Control
              </option>
              <option value="customizeWater" <?= $selectedAction == 'customizeWater' ? 'selected' : '' ?>>Water Tank
                Management</option>
            </select>



            <!-- Buttons -->
            <div style="text-align:right;">
              <button onclick="applyFilter()" style="margin-right:10px;">Apply</button>
              <button onclick="closeFilterModal()">Cancel</button>
            </div>
          </div>
        </div>

        <!-- JavaScript -->
        <script>
          function openFilterModal() {
            document.getElementById('filterModal').style.display = 'block';
          }

          function closeFilterModal() {
            document.getElementById('filterModal').style.display = 'none';
          }

          function applyFilter() {
            const role = document.getElementById('roleFilter').value;
            const action = document.getElementById('actionFilter').value;

            const params = new URLSearchParams();
            if (role) params.append('role', role);
            if (action) params.append('action', action);

            window.location.href = window.location.pathname + '?' + params.toString();
          }


        </script>

        <img src="../assets/images/date.png" alt="date" class="topRightOptions" onclick="openDateModal()"
          style="cursor:pointer;" />


        <!-- Date Filter Modal -->
        <div id="dateModal" style="display:none;
            position:fixed;
            top:0; left:0;
            width:100%; height:100%;
            background:rgba(0,0,0,0.4);
            z-index:1000;">
          <div style="background:#fff;
              width:400px; max-width:90%;
              padding:20px;
              border-radius:10px;
              position:fixed;
              top:50%; left:50%;
              transform:translate(-50%, -50%);">
            <h4 style="margin-top:10px; color:#666;">Filter by Date</h4>

            <label for="dateFrom">From</label>
            <input type="date" id="dateFrom" value="<?= htmlspecialchars($selectedDateFrom) ?>"
              style="width:100%; padding:8px; margin-bottom:15px;" />

            <label for="dateTo">To</label>
            <input type="date" id="dateTo" value="<?= htmlspecialchars($selectedDateTo) ?>"
              style="width:100%; padding:8px; margin-bottom:20px;" />

            <div style="text-align:right;">
              <button onclick="applyDateFilter()" style="margin-right:10px;">Apply</button>
              <button onclick="closeDateModal()">Cancel</button>
            </div>
          </div>
        </div>

        <script>
          function openDateModal() {
            document.getElementById('dateModal').style.display = 'block';
          }
          function closeDateModal() {
            document.getElementById('dateModal').style.display = 'none';
          }
          function applyDateFilter() {
            const from = document.getElementById('dateFrom').value;
            const to = document.getElementById('dateTo').value;
            const params = new URLSearchParams(window.location.search);

            if (from) params.set('date_from', from);
            else params.delete('date_from');

            if (to) params.set('date_to', to);
            else params.delete('date_to');

            // preserve role/action if set
            if (params.toString()) {
              window.location.search = params.toString();
            } else {
              window.location.search = '';
            }
          }

        </script>

        <img src="../assets/images/csv.png" alt="csv" class="topRightOptions" onclick="downloadCSV()"
          style="cursor:pointer;" />

        <script>
          function downloadCSV() {
            const table = document.querySelector("table.custom-table");
            if (!table) return;

            // Gather only visible rows: include header + body
            const rows = Array.from(table.querySelectorAll("thead tr, tbody tr"))
              .filter(r => r.style.display !== "none");
            const csv = rows.map(row => {
              return Array.from(row.querySelectorAll("th, td"))
                .map(cell => {
                  // Escape quotes and wrap in quotes
                  const txt = cell.textContent.trim().replace(/"/g, '""');
                  return `"${txt}"`;
                })
                .join(",");
            }).join("\r\n");

            // Build a blob & download link
            const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            const today = new Date().toISOString().slice(0, 10);
            a.href = url;
            a.download = `activity_log_${today}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
          }

        </script>

      </div>
      <br>

      <div class="contentWrapper">
        <div class="profile-main">
          <div class="flex-containerOneSA">
            <div class="whiteLine"></div>
            <div class="rolesCont">
              <div id="table-container">
                <table class="custom-table">
                  <thead>
                    <tr>
                      <th>Role</th>
                      <th>User Name</th>
                      <th>Floor</th>
                      <th>Action Taken</th>
                      <th>Timestamp</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if (count($logs) > 0) {
                      foreach ($logs as $log) {
                        // ─── 1) Normalize all possible fields ─────────────────────────────────────
                        $dbUserId = isset($log['user_id']) ? (int) $log['user_id'] : 0;
                        $firstName = $log['first_name'] ?? '';
                        $lastName = $log['last_name'] ?? '';
                        $email = $log['email'] ?? 'unknown@example.com';
                        $profilePic = $log['profile_picture'] ?? null;
                        $deviceName = $log['device_name'] ?? null;
                        $status = $log['status'] ?? null;
                        $locationRaw = $log['location'] ?? null;
                        $minTemp = $log['minTemp'] ?? null;
                        $maxTemp = $log['maxTemp'] ?? null;
                        $minWater = $log['minWater'] ?? null;
                        $maxWater = $log['maxWater'] ?? null;
                        $gateMethod = $log['gateMethod'] ?? null;
                        $gateResult = $log['gateResult'] ?? null;
                        $timestampRaw = $log['timestamp'] ?? date('Y-m-d H:i:s');

                        // ─── 2) Determine if this truly is an “invalid RFID tag” ─────────────────
                        $isBadRFID = ($dbUserId === 0)
                          && strtolower($gateMethod) === 'rfid'
                          && strtolower($gateResult) === 'denied';

                        // ─── 3) Build userName, roleName, roleClass, avatar ──────────────────────
                        if ($isBadRFID) {
                          // FORCED anonymous for invalid RFID
                          $userName = 'Anonymous';
                          $roleName = 'Anonymous';
                          $roleClass = 'role-anonymous';
                          $picUrl = 'https://ui-avatars.com/api/?name=AN&background=999&color=fff';
                        } else {
                          // REAL user or non‑RFID log
                          if ($firstName && $lastName) {
                            $userName = strtolower($lastName) . ', ' . strtolower(substr($firstName, 0, 1));
                          } else {
                            $userName = strstr($email, '@', true);
                          }

                          $roleName = $roleMapping[$log['role_id']] ?? 'Unknown';
                          $roleClass = match ($roleName) {
                            'Super Admin' => 'role-superadmin',
                            'Admin' => 'role-admin',
                            'Staff Member' => 'role-staff',
                            'Student' => 'role-student',
                            default => '',
                          };

                          $picUrl = $profilePic
                            ? $profilePic
                            : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=random&color=fff';
                        }

                        // ─── 4) Build “Action Taken” ─────────────────────────────────────────────
                        if ($isBadRFID) {
                          $actionTaken = 'Invalid RFID tag scanned';
                        } elseif (!empty($gateMethod)) {
                          // ANY other gateAccess_logs (granted or real‑user denied)
                          $methodUc = ucfirst($gateMethod);
                          $res = (strtolower($gateResult) === 'open') ? 'granted' : 'denied';
                          $actionTaken = "Gate Access Method: {$methodUc}, Result: {$res}";
                        } elseif (!is_null($minTemp) && !is_null($maxTemp)) {
                          // customizeAC
                          $min = rtrim(rtrim(number_format($minTemp, 1), '0'), '.');
                          $max = rtrim(rtrim(number_format($maxTemp, 1), '0'), '.');
                          $actionTaken = "Set the MinTemp({$min}°C) and MaxTemp({$max}°C)";
                        } elseif (!is_null($minWater) && !is_null($maxWater)) {
                          // customizeWater
                          $minW = rtrim(rtrim(number_format($minWater, 1), '0'), '.');
                          $maxW = rtrim(rtrim(number_format($maxWater, 1), '0'), '.');
                          $actionTaken = "Set the MinWater({$minW}%) and MaxWater({$maxW}%)";
                        } else {
                          // device_logs (lighting control)
                          $deviceMap = [
                            'FFLightOne' => 'Front Gate',
                            'FFLightTwo' => 'Front Garage',
                            'FFLightThree' => 'Rear Garage'
                          ];
                          $locationMap = [
                            '/building/1/lights' => 'Website',
                            '/building/1/status' => 'Switch'
                          ];
                          $dn = $deviceMap[$deviceName] ?? $deviceName;
                          $loc = $locationMap[$locationRaw] ?? $locationRaw;
                          $actionTaken = "{$loc} - {$dn} Lights was set to {$status}";
                        }

                        // ─── 5) Format timestamp ────────────────────────────────────────────────
// assume $timestampRaw is in UTC; convert it to Manila
                        $ts = new DateTime($timestampRaw, new DateTimeZone('UTC'));
                        $ts->setTimezone(new DateTimeZone('Asia/Manila'));
                        $formattedTimestamp = $ts->format('M d y - h:i a');

                        // ─── 6) Echo the row ───────────────────────────────────────────────────
                        echo "<tr>";
                        echo "<td><span class='{$roleClass}'>" . htmlspecialchars($roleName) . "</span></td>";
                        echo "<td class='username-cell'>
              <img src='{$picUrl}' class='user-avatar' />
              <span>{$userName}</span>
            </td>";
                        echo "<td>1st</td>";
                        echo "<td>{$actionTaken}</td>";
                        echo "<td>{$formattedTimestamp}</td>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='5'>No logs found</td></tr>";
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

  <script>
    document.getElementById("searchInputX").addEventListener("input", performSearch);

    function performSearch() {
      const filter = document.getElementById("searchInputX").value.toLowerCase();
      const table = document.querySelector("table.custom-table");
      if (!table) return;
      const rows = table.querySelectorAll("tbody tr");

      rows.forEach(row => {
        // Get Action Taken text content
        const actionTakenCell = row.querySelector('td:nth-child(4)'); // Action Taken is 4th column
        const actionTakenText = actionTakenCell ? actionTakenCell.textContent.toLowerCase() : '';

        // Check if the filter term matches any part of the row's text content or Action Taken
        if (row.textContent.toLowerCase().includes(filter) || actionTakenText.includes(filter)) {
          row.style.display = ""; // Show the row if there's a match
        } else {
          row.style.display = "none"; // Hide the row if no match
        }
      });
    }


  </script>
</body>

</html>